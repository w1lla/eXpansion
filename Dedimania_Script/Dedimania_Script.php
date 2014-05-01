<?php

namespace ManiaLivePlugins\eXpansion\Dedimania_Script;

use ManiaLivePlugins\eXpansion\Dedimania\Classes\Connection as DediConnection;
use ManiaLivePlugins\eXpansion\Dedimania\Events\Event as DediEvent;
use ManiaLive\DedicatedApi\Callback\Event as Event;
use ManiaLivePlugins\eXpansion\Dedimania\Structures\DediPlayer;
use ManiaLivePlugins\eXpansion\Dedimania\Structures\DediRecord;
use \ManiaLive\Event\Dispatcher;
use \ManiaLive\Utilities\Console;

class Dedimania_Script extends \ManiaLivePlugins\eXpansion\Core\types\ExpPlugin implements \ManiaLivePlugins\eXpansion\Dedimania\Events\Listener {

    /** @var DediConnection */
    private $dedimania;

    /** @var Config */
    private $config;

    /** @var Structures\DediRecord[] $records */
    private $records = array();

    /** @var array */
    private $rankings = array();

    /** @var string */
    private $vReplay = "";

    /** @var string */
    private $gReplay = "";

    /** @var Structures\DediRecord */
    private $lastRecord;

    /* @var integer $recordCount */
    private $recordCount = 30;

    /* @var bool $warmup */
    private $wasWarmup = false;
    private $previousEnvironment = "";
    private $autoFetchRecords = true;

    public function exp_onInit() {
	$this->setVersion(0.1);
	$this->config = Config::getInstance();
    }

    public function exp_onLoad() {
	$helpText = "\n\nPlease correct your config with these instructions: \nEdit and add following configuration lines to manialive config.ini\n\n ManiaLivePlugins\\eXpansion\\Dedimania_Script\\Config.login = 'your_server_login_here' \n ManiaLivePlugins\\eXpansion\\Dedimania_Script\\Config.code = 'your_server_code_here' \n\n Visit http://dedimania.net/tm2stats/?do=register to get code for your server.";
	if (empty($this->config->login)) {
	    $this->dumpException("Server login is not configured for dedimania plugin!" . $helpText, new \Exception("Server login missing."));
	    die();
	}
	if (empty($this->config->code)) {
	    $this->dumpException("Server code is not configured for dedimania plugin!" . $helpText, new \Exception("Server code missing."));
	    die();
	}
	Dispatcher::register(DediEvent::getClass(), $this);
	$this->dedimania = DediConnection::getInstance();
	$this->config->newRecordMsg = exp_getMessage($this->config->newRecordMsg);
	$this->config->noRecordMsg = exp_getMessage($this->config->noRecordMsg);
	$this->config->recordMsg = exp_getMessage($this->config->recordMsg);

	$this->exp_addTitleSupport("TMStadium");
	$this->exp_addTitleSupport("TMValley");
	$this->exp_addTitleSupport("TMCanyon");
	$this->exp_addTitleSupport("Trackmania_2@nadeolabs");
	$this->exp_setSoftTitleCheck(false);

	$this->console(\ManiaLivePlugins\eXpansion\Core\Core::$titleId);

	$this->exp_addGameModeCompability(\Maniaplanet\DedicatedServer\Structures\GameInfos::GAMEMODE_SCRIPT, "TimeAttack.Script.txt");
	$this->exp_addGameModeCompability(\Maniaplanet\DedicatedServer\Structures\GameInfos::GAMEMODE_SCRIPT, "Rounds.Script.txt");
	$this->exp_addGameModeCompability(\Maniaplanet\DedicatedServer\Structures\GameInfos::GAMEMODE_SCRIPT, "Cup.Script.txt");
	$this->exp_addGameModeCompability(\Maniaplanet\DedicatedServer\Structures\GameInfos::GAMEMODE_SCRIPT, "Team.Script.txt");
	$this->exp_addGameModeCompability(\Maniaplanet\DedicatedServer\Structures\GameInfos::GAMEMODE_SCRIPT, "Laps.Script.txt");
	$this->exp_setScriptCompatibilityMode(false);
    }

    public function exp_onReady() {
	$this->enableDedicatedEvents();
	$this->enableApplicationEvents();
	$this->enableScriptEvents();
	\ManiaLive\Event\Dispatcher::register(\ManiaLivePlugins\eXpansion\Core\Events\ScriptmodeEvent::getClass(), $this);

	$this->registerChatCommand("test", "test",0,true);
	
	
	$this->config = Config::getInstance();
	$this->registerChatCommand("dedirecs", "showRecs", 0, true);
	$this->previousEnvironment = $this->storage->currentMap->environnement;
	$this->dedimania->openSession($this->storage->currentMap->environnement, $this->config);
    }

    function checkSession($login) {
	$this->dedimania->checkSession();
    }

    
    public function test($login) {
	echo " login\n";
	$ranks = $this->connection->getCurrentRankingForLogin($login);
	print_r($ranks);
	
	echo " all\n";
	$ranks = $this->connection->getCurrentRanking(-1,0);
	print_r($ranks);
	
	
	print_r($this->connection->getServerPackMask());
	
	
    }
    
    public function onPlayerConnect($login, $isSpectator) {
	$player = $this->storage->getPlayerObject($login);
	$this->dedimania->playerConnect($player, $isSpectator);
    }

    public function onPlayerDisconnect($login, $reason = null) {
	$this->dedimania->playerDisconnect($login);
    }

    public function onBeginMatch() {
	echo "beginMatch\n";
	$this->records = array();
	$this->dedimania->getChallengeRecords();
    }

    public function onBeginRound() {
	echo "beginround\n";
	$this->wasWarmup = $this->connection->getWarmUp();
    }

  /*  public function LibXmlRpc_BeginRound($number) {
	echo "script beginRound\n";
	$this->wasWarmup = $this->connection->getWarmUp();
    }

    public function LibXmlRpc_BeginMatch($number) {
	echo "script beginMatch\n";
	$this->records = array();
	$this->dedimania->getChallengeRecords();
    } */

    public function LibXmlRpc_BeginMap($number) {
	echo "script beginMap\n";
	if ($this->storage->currentMap->environnement != $this->previousEnvironment) {
	    $this->previousEnvironment = $this->storage->currentMap->environnement;
	    $this->dedimania->openSession($this->storage->currentMap->environnement, $this->config);
	}

	$this->records = array();
	$this->rankings = array();
	$this->vReplay = "";
	$this->gReplay = "";
    }

    public function LibXmlRpc_OnWayPoint($login, $blockId, $time, $cpIndex, $isEndBlock, $lapTime, $lapNb, $isLapEnd) {
	if ($time == 0)
	    return;

	$playerinfo = \ManiaLivePlugins\eXpansion\Core\Core::$playerInfo;

	if (!$isEndBlock)
	    return;

	if ($this->storage->currentMap->nbCheckpoints == 1)
	    return;

	if (!array_key_exists($login, DediConnection::$players))
	    return;

// if player is banned from dedimania, don't send his time.
	if (DediConnection::$players[$login]->banned)
	    return;

	if (!array_key_exists($login, $this->rankings)) {
	    $this->rankings[$login] = array();
	}

	if (!array_key_exists('BestTime', $this->rankings[$login])) {
	    $this->rankings[$login] = array('Login' => $login, 'BestTime' => $time, 'BestCheckpoints' => implode(",", $playerinfo[$login]->checkpoints));
	} else {
	    if ($time < $this->rankings[$login]['BestTime']) {
		$this->rankings[$login] = array('Login' => $login, 'BestTime' => $time, 'BestCheckpoints' => implode(",", $playerinfo[$login]->checkpoints));
	    }
	}


	//print_r($this->rankings);
// if current map doesn't have records, create one.
	if (count($this->records) == 0) {
	    $player = $this->storage->getPlayerObject($login);
	    $playerinfo = \ManiaLivePlugins\eXpansion\Core\Core::$playerInfo;
	    if ($this->storage->currentMap->nbCheckpoints !== count($playerinfo[$login]->checkpoints)) {
// echo "\nplayer cp mismatch!\n";
	    }

	    $this->records[$login] = new DediRecord($login, $player->nickName, DediConnection::$players[$login]->maxRank, $time, -1, $playerinfo[$login]->checkpoints);
	    $this->reArrage($login);
	    \ManiaLive\Event\Dispatcher::dispatch(new DediEvent(DediEvent::ON_NEW_DEDI_RECORD, $this->records[$login]));
	    return;
	}

// if last record is not set, don't continue.
	if (!is_object($this->lastRecord)) {
	    return;
	}

// so if the time is better than the last entry or the count of records

	$maxrank = DediConnection::$serverMaxRank;
	if (DediConnection::$players[$login]->maxRank > $maxrank) {
	    $maxrank = DediConnection::$players[$login]->maxRank;
	}

	if ($time <= $this->lastRecord->time || count($this->records) <= $maxrank) {

//  print "times matches!";
// if player exists on the list... see if he got better time

	    $player = $this->storage->getPlayerObject($login);

	    if (array_key_exists($login, $this->records)) {


		if ($this->records[$login]->time > $time) {
		    $oldRecord = $this->records[$login];

		    $this->records[$login] = new DediRecord($login, $player->nickName, DediConnection::$players[$login]->maxRank, $time, -1, array());

// if new records count is greater than old count, and doesn't exceed the maxrank of the server
		    $oldCount = count($this->records);
		    if ((count($this->records) > $oldCount) && ( (DediConnection::$dediMap->mapMaxRank + 1 ) < DediConnection::$serverMaxRank)) {
//print "increasing maxrank! \n";
			DediConnection::$dediMap->mapMaxRank++;
			echo "new maxrank:" . DediConnection::$dediMap->mapMaxRank . " \n";
		    }
		    $this->reArrage($login);
// have to recheck if the player is still at the dedi array
		    if (array_key_exists($login, $this->records)) // have to recheck if the player is still at the dedi array
			\ManiaLive\Event\Dispatcher::dispatch(new DediEvent(DediEvent::ON_DEDI_RECORD, $this->records[$login], $oldRecord));
		    return;
		}

// if not, add the player to records table
	    } else {
		$oldCount = count($this->records);
		$this->records[$login] = new DediRecord($login, $player->nickName, DediConnection::$players[$login]->maxRank, $time, -1, array());
// if new records count is greater than old count, increase the map records limit

		if ((count($this->records) > $oldCount) && ( (DediConnection::$dediMap->mapMaxRank + 1 ) < DediConnection::$serverMaxRank)) {

		    DediConnection::$dediMap->mapMaxRank++;
		    echo "new maxrank:" . DediConnection::$dediMap->mapMaxRank . " \n";
		}
		$this->reArrage($login);

// have to recheck if the player is still at the dedi array
		if (array_key_exists($login, $this->records))
		    \ManiaLive\Event\Dispatcher::dispatch(new DediEvent(DediEvent::ON_NEW_DEDI_RECORD, $this->records[$login]));
		return;
	    }
	}
    }

    /**
     * rearrages the records list + recreates the indecies
     * @param string $login is passed to check the server,map and player maxranks for new driven record
     */
    function reArrage($login) {
// sort by time
	$this->sortAsc($this->records, "time");

	$i = 0;
	$newrecords = array();
	foreach ($this->records as $record) {
	    $i++;
	    if (array_key_exists($record->login, $newrecords))
		continue;

	    $record->place = $i;
// if record holder is at server, then we must check for additional   
	    if ($record->login == $login) {
		echo "login: $login\n";
// if record is greater than players max rank, don't allow
		if ($record->place > DediConnection::$players[$login]->maxRank) {
		    echo "record place: " . $record->place . " is greater than player max rank: " . DediConnection::$players[$login]->maxRank;
		    echo "\n not adding record.\n";
		    continue;
		}
// if player record is greater what server can offer, don't allow
		if ($record->place > DediConnection::$serverMaxRank) {
		    echo "record place: " . $record->place . " is greater than server max rank: " . DediConnection::$serverMaxRank;
		    echo "\n not adding record.\n";
		    continue;
		}
// if player record is greater than what is allowed, don't allow
		if ($record->place > DediConnection::$dediMap->mapMaxRank) {
		    echo "record place: " . $record->place . " is greater than record max count: " . DediConnection::$dediMap->mapMaxRank;
		    echo "\n not adding record.\n";
		    continue;
		}

		echo "\n DEDIRECORD ADDED.\n";
// update checkpoints for the record

		$playerinfo = \ManiaLivePlugins\eXpansion\Core\Core::$playerInfo;

		$record->checkpoints = implode(",", $playerinfo[$login]->checkpoints);

// add record
		$newrecords[$record->login] = $record;
// othervice
	    } else {
// check if some record needs to be erased from the list...
//  if ($record->place > DediConnection::$dediMap->mapMaxRank)
//  continue;

		$newrecords[$record->login] = $record;
	    }
	}

// assign  the new records
//$this->records = array_slice($newrecords, 0, DediConnection::$dediMap->mapMaxRank);

	$this->records = $newrecords;
// assign the last place
	$this->lastRecord = end($this->records);

// recreate new records entry for update_records
	$data = array('Records' => array());
	$i = 1;
	foreach ($this->records as $record) {
	    $data['Records'][] = Array("Login" => $record->login, "MaxRank" => $record->maxRank, "NickName" => $record->nickname, "Best" => $record->time, "Rank" => $i, "Checks" => $record->checkpoints);
	    $i++;
	}

	\ManiaLive\Event\Dispatcher::dispatch(new DediEvent(DediEvent::ON_UPDATE_RECORDS, $data));
    }

    private function compare_bestTime($a, $b) {
	if ($a['BestTime'] == $b['BestTime'])
	    return 0;
	return ($a['BestTime'] < $b['BestTime']) ? -1 : 1;
    }

    private function sortAsc(&$array, $prop) {
	usort($array, function($a, $b) use ($prop) {
	    return $a->$prop > $b->$prop ? 1 : -1;
	});
    }

    /**
     * 
     * @param array $rankings
     * @param string $winnerTeamOrMap
     * 
     */
    public function onEndMatch($rankings_old, $winnerTeamOrMap) {


	if ($this->exp_isRelay())
	    return;

	usort($this->rankings, array($this, "compare_BestTime"));

	$rankings = array();
	$error = false;
	foreach ($this->rankings as $login => $rank) {
	    $checks = explode(",", $rank['BestCheckpoints']);
	    foreach ($checks as $list) {
		if ($list == 0)
		    $error = true;
	    }
	    $rank['BestCheckpoints'] = $checks;
	    $rankings[] = $rank;
	}

	if ($error) {
	    $this->console("[Dedimania] Data integrity check failed. Dedimania times not sent.");
	    return;
	}

	try {
	    if (sizeof($rankings) == 0) {
		$this->vReplay = "";
		$this->gReplay = "";
		$this->console("[Dedimania] No new times driven. Skipping dedimania sent.");
		return;
	    }
	    
	    $this->vReplay = $this->connection->getValidationReplay($rankings[0]['Login']);
	    
	    $greplay = "";
	    $grfile = sprintf('Dedimania/%s.%d.%07d.%s.Replay.Gbx', $this->storage->currentMap->uId, $this->storage->gameInfos->gameMode, $rankings[0]['BestTime'], $rankings[0]['Login']);
	    $this->connection->SaveBestGhostsReplay($rankings[0]['Login'], $grfile);
	    $this->gReplay = file_get_contents($this->connection->gameDataDirectory() . 'Replays/' . $grfile);

// Dedimania doesn't allow times sent without validation relay. So, let's just stop here if there is none.
	    if (empty($this->vReplay)) {
		$this->console("[Dedimania] Couldn't get validation replay of the first player. Dedimania times not sent.");
		return;
	    }	
	
	    $this->dedimania->setChallengeTimes($this->storage->currentMap, $rankings, $this->vReplay, $this->gReplay);
	} catch (\Exception $e) {
	    $this->console("[Dedimania] " . $e->getMessage());
	    $this->vReplay = "";
	    $this->gReplay = "";
	}
    }

    public function onDedimaniaOpenSession() {
	$players = array();
	foreach ($this->storage->players as $player) {
	    if ($player->login != $this->storage->serverLogin)
		$players[] = array($player, false);
	}
	foreach ($this->storage->spectators as $player)
	    $players[] = array($player, true);

	$this->dedimania->playerMultiConnect($players);
	if ($this->autoFetchRecords) {
	    $this->autoFetchRecords = false;
	    $this->dedimania->getChallengeRecords();
	}

	$this->rankings = array();
    }

    public function onDedimaniaGetRecords($data) {
	echo "dedirecords get\n";
//print_r($data);

	$this->records = array();

	foreach ($data['Records'] as $record) {
	    $this->records[$record['Login']] = new DediRecord($record['Login'], $record['NickName'], $record['MaxRank'], $record['Best'], $record['Rank'], $record['Checks']);
	}
	$this->lastRecord = end($this->records);
	$this->recordCount = count($this->records);

	$this->debug("Dedimania get records:");
//$this->debug($data);
    }

    public function exp_onUnload() {
	$this->disableTickerEvent();
	$this->disableDedicatedEvents();
    }

    /**
     * 
     * @param type $data
     */
    public function onDedimaniaUpdateRecords($data) {
	$this->debug("Dedimania update records:");
// $this->debug($data);
    }

    /**
     * onDedimaniaNewRecord($record)
     * gets called on when player has driven a new record for the map
     * 
     * @param Structures\DediRecord $record     
     */
    public function onDedimaniaNewRecord($record) {
	try {
	    if ($this->config->disableMessages == true)
		return;

	    $recepient = $record->login;
	    if ($this->config->show_record_msg_to_all)
		$recepient = null;

	    $time = \ManiaLive\Utilities\Time::fromTM($record->time);
	    if (substr($time, 0, 3) === "0:0") {
		$time = substr($time, 3);
	    } else if (substr($time, 0, 2) === "0:") {
		$time = substr($time, 2);
	    }

	    $this->exp_chatSendServerMessage($this->config->newRecordMsg, $recepient, array(\ManiaLib\Utils\Formatting::stripCodes($record->nickname, "wos"), $record->place, $time));
	} catch (\Exception $e) {
	    $this->console("Error: couldn't show dedimania message" . $e->getMessage());
	}
    }

    /**
     * 
     * @param Structures\DediRecord $record
     * @param Structures\DediRecord $oldRecord     
     */
    public function onDedimaniaRecord($record, $oldRecord) {
	$this->debug("improved dedirecord:");
	$this->debug($record);
	try {
	    if ($this->config->disableMessages == true)
		return;
	    $recepient = $record->login;
	    if ($this->config->show_record_msg_to_all)
		$recepient = null;

	    $diff = \ManiaLive\Utilities\Time::fromTM($record->time - $oldRecord->time);
	    if (substr($diff, 0, 3) === "0:0") {
		$diff = substr($diff, 3);
	    } else if (substr($diff, 0, 2) === "0:") {
		$diff = substr($diff, 2);
	    }
	    $time = \ManiaLive\Utilities\Time::fromTM($record->time);
	    if (substr($time, 0, 3) === "0:0") {
		$time = substr($time, 3);
	    } else if (substr($time, 0, 2) === "0:") {
		$time = substr($time, 2);
	    }

	    $this->exp_chatSendServerMessage($this->config->recordMsg, $recepient, array(\ManiaLib\Utils\Formatting::stripCodes($record->nickname, "wos"), $record->place, $time, $oldRecord->place, $diff));
	    $this->debug("message sent.");
	} catch (\Exception $e) {
	    $this->console("Error: couldn't show dedimania message");
	}
    }

    public function onDedimaniaPlayerConnect($data) {
	
    }

    public function onDedimaniaPlayerDisconnect($login) {
	
    }

    public function showRecs($login) {
	\ManiaLivePlugins\eXpansion\Dedimania\Gui\Windows\Records::Erase($login);

	if (sizeof($this->records) == 0) {
	    $this->exp_chatSendServerMessage($this->config->noRecordMsg, $login);
	    return;
	}
	try {
	    $window = \ManiaLivePlugins\eXpansion\Dedimania\Gui\Windows\Records::Create($login);
	    $window->setTitle(__('Dedimania -records on a Map', $login));
	    $window->centerOnScreen();
	    $window->populateList($this->records);
	    $window->setSize(120, 100);
	    $window->show();
	} catch (\Exception $e) {
	    echo $e->getFile() . ":" . $e->getLine();
	}
    }

}

?>