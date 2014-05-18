<?php

namespace ManiaLivePlugins\eXpansion\Widgets_BestCheckpoints;

use ManiaLivePlugins\eXpansion\Core\types\config\types\String;
use ManiaLivePlugins\eXpansion\Core\types\config\types\BasicList;
use ManiaLivePlugins\eXpansion\Core\types\config\types\Boolean;
use ManiaLivePlugins\eXpansion\Core\types\config\types\Int;
use ManiaLivePlugins\eXpansion\Core\types\config\types\BoundedInt;
use ManiaLivePlugins\eXpansion\Core\types\config\types\Float;
use ManiaLivePlugins\eXpansion\Core\types\config\types\BoundedFloat;

/**
 * Description of MetaData
 *
 * @author Petri
 */
class MetaData extends \ManiaLivePlugins\eXpansion\Core\types\config\MetaData {

    public function onBeginLoad() {
	parent::onBeginLoad();
	$this->setName("Best Checkpoints widget (top of screen)");
	$this->setDescription("Provides Best checkpoints widget");

	$this->addTitleSupport("TM");
	$this->addTitleSupport("Trackmania");
	//Important for all eXpansion plugins.
	$this->addGameModeCompability(\Maniaplanet\DedicatedServer\Structures\GameInfos::GAMEMODE_ROUNDS);
	$this->addGameModeCompability(\Maniaplanet\DedicatedServer\Structures\GameInfos::GAMEMODE_TIMEATTACK);
	$this->addGameModeCompability(\Maniaplanet\DedicatedServer\Structures\GameInfos::GAMEMODE_TEAM);
	$this->addGameModeCompability(\Maniaplanet\DedicatedServer\Structures\GameInfos::GAMEMODE_LAPS);
	$this->addGameModeCompability(\Maniaplanet\DedicatedServer\Structures\GameInfos::GAMEMODE_CUP);
	$this->addGameModeCompability(\Maniaplanet\DedicatedServer\Structures\GameInfos::GAMEMODE_SCRIPT, 'TeamAttack.Script.txt');
    }

}
