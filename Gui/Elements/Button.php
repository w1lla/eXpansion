<?php

namespace ManiaLivePlugins\eXpansion\Gui\Elements;

use ManiaLivePlugins\eXpansion\Gui\Config;

class Button extends \ManiaLive\Gui\Control {

    private $label;
    private $button;
    private $value;
    private $isActive = false;
    private $activeFrame;
    private $color = '$000';
    private $text;
    private $quad;

    /**
     * Button
     * 
     * @param int $sizeX = 24
     * @param intt $sizeY = 6
     */
    function __construct($sizeX = 24, $sizeY = 6) {
        $config = Config::getInstance();

        $this->activeFrame = new \ManiaLib\Gui\Elements\Quad($sizeX + 2, $sizeY + 2.5);
        $this->activeFrame->setPosition(-1, 0);
        $this->activeFrame->setAlign('left', 'center');
        $this->activeFrame->setStyle("Icons128x128_Blink");
        $this->activeFrame->setSubStyle("ShareBlink");

        $this->quad = new \ManiaLib\Gui\Elements\Quad($sizeX, $sizeY);
        $this->quad->setAlign('left', 'center2');
        $this->quad->setImage($config->button, true);
        //$this->quad->setImageFocus($config->buttonActive, true);
        $this->quad->setScriptEvents(true);


        $this->label = new \ManiaLib\Gui\Elements\Label($sizeX, $sizeY);
        $this->label->setAlign('center', 'center2');
        $this->label->setStyle("TextChallengeNameMedium");
        $this->label->setTextSize(3);
        //$this->label->setScriptEvents(true);
        $this->label->setFocusAreaColor1("0000");
        $this->label->setFocusAreaColor2("fff3");

        $this->sizeX = $sizeX + 2;
        $this->sizeY = $sizeY + 2;
        $this->setSize($sizeX + 2, $sizeY + 2);
    }

    protected function onResize($oldX, $oldY) {
        //$this->label->setSize($this->sizeX - 2, $this->sizeY - 1);
        $this->label->setPosX(($this->sizeX - 2) / 2);
        $this->label->setPosZ($this->posZ);
        $this->setScale(0.7);
    }

    function onDraw() {
        $this->clearComponents();

        if ($this->isActive)
            $this->addComponent($this->activeFrame);

        $this->addComponent($this->quad);

        $this->label->setText($this->text);
        $this->addComponent($this->label);
    }

    function getText() {
        return $this->text;
    }

    function setText($text) {
        $this->text = $text;
    }

    function setActive($bool = true) {
        $this->isActive = $bool;
    }

    function getValue() {
        return $this->value;
    }

    /**
     * Colorize the button background     
     * @param string $value 3-digit RGBa code
     */
    function colorize($value) {
        $outval = $value;

        if (strlen($value) == 4) {
            $this->quad->setOpacity(substr($value, 3, 1));
            $outval = substr($value, 0, 3);
        }
        $this->quad->setModulateColor($outval);
    }

    /**
     * Sets text color 
     * @param string $value 4-digit RGBa code
     */
    function setTextColor($textcolor) {
        $this->label->setTextColor($textcolor);
    }

    function setValue($text) {
        $this->value = $text;
    }

    function setAction($action) {
        $this->label->setAction($action);
    }

}

?>