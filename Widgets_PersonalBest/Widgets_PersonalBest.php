<?php

namespace ManiaLivePlugins\eXpansion\Widgets_PersonalBest;

use \ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\eXpansion\LocalRecords\Events\Event as LocalEvent;
use ManiaLivePlugins\eXpansion\LocalRecords\Structures\Record;
use ManiaLivePlugins\eXpansion\Widgets_PersonalBest\Gui\Widgets\PBPanel;

/**
 * Description of Widgets_PersonalBest
 *
 * @author oliverde8
 */
class Widgets_PersonalBest extends \ManiaLivePlugins\eXpansion\Core\types\ExpPlugin {

    public function exp_onLoad() {
        parent::exp_onLoad();
        Dispatcher::register(LocalEvent::getClass(), $this, LocalEvent::ON_PERSONAL_BEST);
        Dispatcher::register(LocalEvent::getClass(), $this, LocalEvent::ON_NEW_RECORD);
        Dispatcher::register(LocalEvent::getClass(), $this, LocalEvent::ON_UPDATE_RECORDS);
    }

    public function exp_onReady() {
        $this->enableDedicatedEvents();
        foreach ($this->storage->players as $player)
            $this->onPlayerConnect($player->login, false);
        foreach ($this->storage->spectators as $player)
            $this->onPlayerConnect($player->login, true);
    }

    function onPlayerConnect($login, $isSpectator) {
        if (!$isSpectator) {
            $this->displayRecordWidget($login);
        }
    }

    public function onPlayerInfoChanged($playerInfo) {
        $player = \Maniaplanet\DedicatedServer\Structures\Player::fromArray($playerInfo);
        if ($player->spectator == 1) {
            PBPanel::Erase($player->login);
        } else {
            $this->displayRecordWidget($player->login);
        }
    }

    function onUpdateRecords($record) {
        foreach ($this->storage->players as $player)
            $this->redrawWidget($player->login);
        foreach ($this->storage->spectators as $player)
            $this->redrawWidget($player->login);
    }

    public function onPersonalBestRecord(Record $record) {
        $this->redrawWidget($record->login, $record);
    }

    public function onNewRecord($records) {
        foreach ($this->storage->players as $player)
            $this->redrawWidget($player->login);
        foreach ($this->storage->spectators as $player)
            $this->redrawWidget($player->login);
    }

    public function redrawWidget($login = null) {
        $record = null;
        if ($login != null) {
            $record = $this->callPublicMethod('ManiaLivePlugins\eXpansion\\LocalRecords', 'getCurrentChallangePlayerRecord', $login);
        }
        $this->displayRecordWidget($login, $record);
    }

    /**
     * 
     * @param string $login
     * @param Record $record
     * @return 
     */
    function displayRecordWidget($login, $record = null) {
        //PBPanel::Erase($login);

        if ($login == null)
            return;

        if ($record == null)
            $record = $this->callPublicMethod('ManiaLivePlugins\eXpansion\\LocalRecords', 'getCurrentChallangePlayerRecord', $login);

        $rank = $this->callPublicMethod('ManiaLivePlugins\eXpansion\\LocalRecords', 'getPlayerRank', $login);
        if ($rank == -1)
            $rank = '--';
        if ($rank == -2)
            $rank = '';
        $rankTotal = $this->callPublicMethod('ManiaLivePlugins\eXpansion\\LocalRecords', 'getTotalRanked');

        $info = PBPanel::Create($login);
        $info->setRecord($record, $rank, $rankTotal);
        $info->setSize(30, 6);
        $info->setPosition(132, -76);
        $info->show();
    }

}

?>