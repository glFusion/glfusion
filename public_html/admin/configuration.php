<?php
/**
* glFusion CMS
*
* Loads the administration UI and sends input to config.class
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2007-2008 by the following authors:
*  Authors: Aaron Blankstein  - kantai AT gmail DOT com
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

$conf_group = array_key_exists('conf_group', $_POST) ? COM_applyFilter($_POST['conf_group']) : 'Core';
$config =& config::get_instance();

// MAIN
if (array_key_exists('set_action', $_POST) && SEC_checkToken()){
    if (SEC_inGroup('Root')) {
        if ($_POST['set_action'] == 'restore') {
            $name = COM_applyFilter($_POST['name']);
            $config->restore_param($name, $conf_group);
        } elseif ($_POST['set_action'] == 'unset') {
            $name = COM_applyFilter($_POST['name']);
            $config->unset_param($name, $conf_group);
        }
    }
}
//var_dump($_POST);exit;
if (array_key_exists('form_submit', $_POST) && SEC_checkToken()) {
    $result = null;
    if (! array_key_exists('form_reset', $_POST)) {
        $result = $config->updateConfig($_POST, $conf_group);
        CACHE_clear();
        $c = \glFusion\Cache\Cache::getInstance();
        $c->deleteItemsByTags(array('menu'));
    }
    $sub_group = array_key_exists('sub_group', $_POST) ? COM_applyFilter($_POST['sub_group']) : null;
    $activeTab = array_key_exists('activetab',$_POST) ? COM_applyFilter($_POST['activetab']) : '';
    echo $config->get_ui($conf_group, $sub_group, $activeTab,$result);
} else {
    $sub_group = array_key_exists('subgroup', $_POST) ? COM_applyFilter($_POST['subgroup']) : null;
    $activeTab = array_key_exists('activetab',$_POST) ? COM_applyFilter($_POST['activetab']) : '';
    echo $config->get_ui($conf_group, $sub_group,$activeTab);
}
?>
