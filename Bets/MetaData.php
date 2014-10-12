<?php

namespace ManiaLivePlugins\eXpansion\Bets;

use ManiaLivePlugins\eXpansion\Core\types\config\types\BoundedInt;

/**
 * Description of MetaData
 *
 * @author Petri
 *
 */
class MetaData extends \ManiaLivePlugins\eXpansion\Core\types\config\MetaData
{

	public function onBeginLoad()
	{
		parent::onBeginLoad();
		$this->setName("Bet Planets");
		$this->setDescription('Enables the famous bets for playres to compete for planets');

		$configInstance = Config::getInstance();

		$var = new BoundedInt("timeoutSetBet", "Bet Accept timeout (in seconds)", $configInstance, false, false);
		$var->setMin(20);		
		$var->setDefaultValue(60);
		$this->registerVariable($var);
		
	}

}

?>