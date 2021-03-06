<?php

namespace ManiaLivePlugins\eXpansion\Widgets_MapSuggestion;

use ManiaLive\Gui\ActionHandler;
use ManiaLivePlugins\eXpansion\Core\types\ExpPlugin;
use ManiaLivePlugins\eXpansion\Widgets_MapSuggestion\Gui\Widgets\MapSuggestionButton;

class Widgets_MapSuggestion extends ExpPlugin
{

	private $action;
	
	public function exp_onReady()
	{
		$ahandler = ActionHandler::getInstance();
		$this->action = $ahandler->createAction(array($this, "invoke"));

		$button = MapSuggestionButton::Create();
		$button->setActions($this->action);
		$button->setPosition(120,-60);
		$button->setSize(10.0, 10.0);
		$button->show();
	}

	
	public function invoke($login) {
		$this->callPublicMethod("\\ManiaLivePlugins\\eXpansion\\MapSuggestion\\MapSuggestion", "showMapWishWindow", $login);
	}
	
	
	public function exp_onUnload()
	{
		$ahandler = ActionHandler::getInstance();
		$ahandler->deleteAction($this->action);
		$this->action = null;
		MapSuggestionButton::EraseAll();
	}

}
