<?php

namespace ManiaLivePlugins\eXpansion\Adm\Gui\Windows;

use \ManiaLivePlugins\eXpansion\Gui\Elements\Button as OkButton;
use \ManiaLivePlugins\eXpansion\Gui\Elements\Inputbox;
use \ManiaLivePlugins\eXpansion\Gui\Elements\Checkbox;
use \ManiaLivePlugins\eXpansion\Gui\Elements\Ratiobutton;
use ManiaLivePlugins\eXpansion\Adm\Gui\Controls\MatchSettingsFile;
use ManiaLive\Gui\ActionHandler;

class ForceScores extends \ManiaLivePlugins\eXpansion\Gui\Windows\Window {

    private $pager;

    /** @var \Maniaplanet\DedicatedServer\Connection */
    private $connection;

    /** @var \ManiaLive\Data\Storage */
    private $storage;
    private $items = array();
    private $ok;
    private $cancel;
    private $actionOk;
    private $actionCancel;
    public static $mainPlugin;

    protected function onConstruct() {
        parent::onConstruct();
        $login = $this->getRecipient();
        $config = \ManiaLive\DedicatedApi\Config::getInstance();
        $this->connection = \Maniaplanet\DedicatedServer\Connection::factory($config->host, $config->port);
        $this->storage = \ManiaLive\Data\Storage::getInstance();

        $this->pager = new \ManiaLivePlugins\eXpansion\Gui\Elements\Pager();
        $this->mainFrame->addComponent($this->pager);
        $this->actionOk = $this->createAction(array($this, "Ok"));
        $this->actionCancel = $this->createAction(array($this, "Cancel"));

        $this->ok = new OkButton();
        $this->ok->colorize("0d0");
        $this->ok->setText(__("Apply", $login));
        $this->ok->setAction($this->actionOk);
        $this->mainFrame->addComponent($this->ok);

        $this->cancel = new OkButton();
        $this->cancel->setText(__("Cancel", $login));
        $this->cancel->setAction($this->actionCancel);
        $this->mainFrame->addComponent($this->cancel);
    }

    function onResize($oldX, $oldY) {
        parent::onResize($oldX, $oldY);
        $this->pager->setSize($this->sizeX, $this->sizeY - 8);
        $this->pager->setStretchContentX($this->sizeX);
        $this->ok->setPosition($this->sizeX - 38, -$this->sizeY + 6);
        $this->cancel->setPosition($this->sizeX - 20, -$this->sizeY + 6);
    }

    function onShow() {
        $this->populateList();
    }

    function populateList() {
        foreach ($this->items as $item)
            $item->erase();
        $this->pager->clearItems();
        $this->items = array();

        $login = $this->getRecipient();
        $x = 0;
        $rankings = $this->connection->getCurrentRanking(-1, 0);
        foreach ($rankings as $player) {
            $this->items[$x] = new \ManiaLivePlugins\eXpansion\Adm\Gui\Controls\PlayerScore($x, $player, $this->sizeX-8);
            $this->pager->addItem($this->items[$x]);
            $x++;
        }
    }

    function Ok($login, $scores) {
        $outScores = array();

        foreach ($scores as $id => $val) {
            $outScores[] = array("PlayerId" => intval($id), "Score" => intval($val));
        }
        
        $this->connection->forceScores($outScores, true);
        self::$mainPlugin->forceScoresOk();
        $this->erase($login);
    }

    function Cancel($login) {
        $this->erase($login);
    }

    function destroy() {
        foreach ($this->items as $item)
            $item->erase();

        $this->items = array();
        $this->pager->destroy();
        $this->ok->destroy();
        $this->cancel->destroy();
        $this->connection = null;
        $this->storage = null;
        $this->clearComponents();
        parent::destroy();
    }

}

?>
