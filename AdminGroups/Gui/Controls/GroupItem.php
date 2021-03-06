<?php

namespace ManiaLivePlugins\eXpansion\AdminGroups\Gui\Controls;

use ManiaLivePlugins\eXpansion\Gui\Elements\Button as myButton;
use ManiaLivePlugins\eXpansion\Gui\Elements\ListBackGround;
use ManiaLivePlugins\eXpansion\AdminGroups\Group;
use ManiaLivePlugins\eXpansion\AdminGroups\AdminGroups;
use \ManiaLivePlugins\eXpansion\AdminGroups\Permission;

/**
 * Description of GroupItem
 *
 * @author oliverde8
 */
class GroupItem extends \ManiaLive\Gui\Control {

    private $group;
    
    private $action_changePermissions;
    private $action_playerList;    
    private $action_deleteGroup;
    private $action_inherticances;
    
    private $plistButton;
    private $permiButton;
    private $deleteButton;
    private $InheritButton;

    function __construct($indexNumber, Group $group, $controller, $login) {
        $this->group = $group;
        $sizeX = 100;
        $sizeY = 6;

        $this->action_changePermissions = $this->createAction(array($controller, 'changePermission'), $group);
        $this->action_playerList = $this->createAction(array($controller, 'playerList'), $group);
        $this->action_deleteGroupf = $this->createAction(array($controller, 'deleteGroup'), $group);
        $this->action_deleteGroup = \ManiaLivePlugins\eXpansion\Gui\Gui::createConfirm($this->action_deleteGroupf);
        $this->action_inherticances = $this->createAction(array($controller, 'inheritList'), $group);

        $frame = new \ManiaLive\Gui\Controls\Frame();
        $frame->setSize($sizeX, $sizeY);
        $frame->setLayout(new \ManiaLib\Gui\Layouts\Line());
        
        $this->addComponent(new ListBackGround($indexNumber, $sizeX, $sizeY));

        $gui_name = new \ManiaLib\Gui\Elements\Label(35*(0.8/0.6), 4);
        $gui_name->setAlign('left', 'center');
        $gui_name->setText($group->getGroupName());
        $gui_name->setScale(0.6);
        $frame->addComponent($gui_name);

        $gui_nbPlayers = new \ManiaLib\Gui\Elements\Label(15*(0.8/0.6), 4);
        $gui_nbPlayers->setAlign('left', 'center');
        $gui_nbPlayers->setText(sizeof($group->getGroupUsers()));
        $gui_nbPlayers->setScale(0.6);
        $frame->addComponent($gui_nbPlayers);

        $this->plistButton = new MyButton(30, 6);
        $this->plistButton->setAction($this->action_playerList);
        $this->plistButton->setText(__(AdminGroups::$txt_playerList, $login));
        $this->plistButton->setScale(0.4);
        $frame->addComponent($this->plistButton);
        
        if (AdminGroups::hasPermission($login, Permission::admingroups_adminAllGroups) || (
                AdminGroups::hasPermission($login, Permission::admingroups_onlyOwnGroup) && AdminGroups::getAdmin($login)->getGroup()->getGroupName() == $group->getGroupName())) {

            $this->permiButton = new MyButton(40, 6);
            $this->permiButton->setAction($this->action_changePermissions);
            $this->permiButton->setText(__(AdminGroups::$txt_permissionList, $login));
            $this->permiButton->setScale(0.4);
            $frame->addComponent($this->permiButton);
            
            $this->deleteButton = new MyButton(40, 6);
            $this->deleteButton->setAction($this->action_deleteGroup);
            $this->deleteButton->setText(__(AdminGroups::$txt_deletegroup, $login));
            $this->deleteButton->setScale(0.4);
            $frame->addComponent($this->deleteButton);
        }
        
        if (AdminGroups::hasPermission($login, Permission::admingroups_adminAllGroups)){
            $this->InheritButton = new MyButton(30, 6);
            $this->InheritButton->setAction($this->action_inherticances);
            $this->InheritButton->setText(__(AdminGroups::$txt_inherits, $login));
            $this->InheritButton->setScale(0.4);
            $frame->addComponent($this->InheritButton);
        }

        $this->addComponent($frame);

        $this->sizeX = $sizeX;
        $this->sizeY = $sizeY;
        $this->setSize($sizeX, $sizeY);
    }

  // manialive 3.1 override to do nothing.
    function destroy() {

    }
    /*
     * custom function to remove contents.
     */
    public function erase() {
        if($this->permiButton != null){
            $this->permiButton->destroy();
            $this->deleteButton->destroy();
        }
        $this->plistButton->destroy();
        
        $this->permiButton=null;
        $this->plistButton=null;
        $this->deleteButton=null;
        $this->clearComponents();
	\ManiaLive\Gui\ActionHandler::getInstance()->deleteAction($this->action_deleteGroupf);
	\ManiaLive\Gui\ActionHandler::getInstance()->deleteAction($this->action_deleteGroup);

        parent::destroy();
    }

}

?>
