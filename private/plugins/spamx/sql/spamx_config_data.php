<?php
/**
* glFusion CMS
*
* glFusion Spam-X Configuration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$spamxConfigData = array(
    array(
        'name' => 'sg_main',
        'default_value' => NULL,
        'type' => 'subgroup',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 50,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'fs_main',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 50,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'logging',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 1,
        'sort' => 60,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'debug',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 1,
        'sort' => 70,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'admin_override',
        'default_value' => false,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 1,
        'sort' => 80,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'timeout',
        'default_value' => 5,
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 90,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'notification_email',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 100,
        'set' => FALSE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'action_mail',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 1,
        'sort' => 110,
        'set' => true,
        'group' => 'spamx'
    ),

//formcheck
    array(
        'name' => 'fs_formcheck',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'spamx'
    ),
    array(
        'name' => 'fc_enable',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 1,
        'sort' => 10,
        'set' => TRUE,
        'group' => 'spamx'
    ),

// stop forum spam
    array(
        'name' => 'fs_sfs',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'sfs_enable',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 1,
        'sort' => 10,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'sfs_username_check',
        'default_value' => false,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 1,
        'sort' => 20,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'sfs_email_check',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 1,
        'sort' => 30,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'sfs_ip_check',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 1,
        'sort' => 40,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'sfs_username_confidence',
        'default_value' => '99.00',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 1,
        'sort' => 50,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'sfs_email_confidence',
        'default_value' => '50.00',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 1,
        'sort' => 60,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'sfs_ip_confidence',
        'default_value' => '25.00',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 2,
        'selection_array' => 1,
        'sort' => 70,
        'set' => TRUE,
        'group' => 'spamx'
    ),

// SLC
    array(
        'name' => 'fs_slc',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 3,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'slc_enable',
        'default_value' => true,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 3,
        'selection_array' => 1,
        'sort' => 10,
        'set' => TRUE,
        'group' => 'spamx'
    ),


    array(
        'name' => 'slc_max_links',
        'default_value' => 5,
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 3,
        'selection_array' => 1,
        'sort' => 20,
        'set' => TRUE,
        'group' => 'spamx'
    ),

// Akismet
    array(
        'name' => 'fs_akismet',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 4,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'akismet_enabled',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 4,
        'selection_array' => 1,
        'sort' => 10,
        'set' => TRUE,
        'group' => 'spamx'
    ),

    array(
        'name' => 'akismet_api_key',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 4,
        'selection_array' => NULL,
        'sort' => 20,
        'set' => TRUE,
        'group' => 'spamx'
    ),

);

?>