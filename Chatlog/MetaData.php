<?php

namespace ManiaLivePlugins\eXpansion\Chatlog;

use \ManiaLivePlugins\eXpansion\Core\types\config\types\Int;

/**
 * Description of MetaData
 *
 * @author Petri
 */
class MetaData extends \ManiaLivePlugins\eXpansion\Core\types\config\MetaData {

    public function onBeginLoad() {
	parent::onBeginLoad();
	$this->setName("Chat log & history");
	$this->setDescription("Logs chat and provides ingame command /chatlog for viewing chat history");

	$config = Config::getInstance();
	$var = new Int("historyLenght", "Chatlog history lenght", $config);
	$var->setGroup("Chat Messages");
	$var->setCanBeNull(false)
		->setDefaultValue(100);
	$this->registerVariable($var);
    }

}
