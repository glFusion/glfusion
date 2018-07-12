<?php
/**
* glFusion CMS
*
* Links Plugin Configuration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$linksConfigData = array(
    array(
        'name' => 'fs_public',
        'default_value' => 'N',
        'type' => 'fieldset',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'fieldset' => 0
    ),

    array(
        'name'          => 'linksloginrequired',
        'default_value' => 0,
        'type'          => 'select',
        'group'    => 'links',
        'subgroup'      => 0,
        'selection_array' => 0,
        'sort'          => 10,
        'set'           => TRUE,
        'fieldset'      => 0
    ),

    array(
        'name' => 'linkcols',
        'default_value' => 3,
        'type' => 'text',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 0,
        'sort' => 20,
        'set' => TRUE,
        'fieldset' => 0
    ),

    array(
        'name' => 'linksperpage',
        'default_value' => 10,
        'type' => 'text',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 0,
        'sort' => 30,
        'set' => TRUE,
        'fieldset' => 0
    ),

    array(
        'name' => 'show_top10',
        'default_value' => TRUE,
        'type' => 'select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 1,
        'sort' => 40,
        'set' => TRUE,
        'fieldset' => 0
    ),

    array(
        'name' => 'show_category_descriptions',
        'default_value' => TRUE,
        'type' => 'select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 1,
        'sort' => 50,
        'set' => TRUE,
        'fieldset' => 0
    ),

    array(
        'name' => 'displayblocks',
        'default_value' => 0,
        'type' => 'select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 13,
        'sort' => 60,
        'set' => TRUE,
        'fieldset' => 0
    ),

// links admin tab

    array(
        'name' => 'fs_admin',
        'default_value' => 'N',
        'type' => 'fieldset',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'fieldset' => 1
    ),

    array(
        'name' => 'target_blank',
        'default_value' => 1,
        'type' => 'select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 0,
        'sort' => 10,
        'set' => TRUE,
        'fieldset' => 1
    ),

    array(
        'name' => 'hidenewlinks',
        'default_value' => 0,
        'type' => 'select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 0,
        'sort' => 20,
        'set' => TRUE,
        'fieldset' => 1
    ),

    array(
        'name' => 'newlinksinterval',
        'default_value' => 1209600,
        'type' => 'text',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => NULL,
        'sort' => 30,
        'set' => TRUE,
        'fieldset' => 1
    ),
    array(
        'name' => 'hidelinksmenu',
        'default_value' => FALSE,
        'type' => 'select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 0,
        'sort' => 40,
        'set' => TRUE,
        'fieldset' => 1
    ),

    array(
        'name' => 'submission',
        'default_value' => 2,
        'type' => 'select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 14,
        'sort' => 50,
        'set' => TRUE,
        'fieldset' => 1
    ),

    array(
        'name' => 'linksubmission',
        'default_value' => 1,
        'type' => 'select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 0,
        'sort' => 60,
        'set' => TRUE,
        'fieldset' => 1
    ),

    array(
        'name' => 'notification',
        'default_value' => 0,
        'type' => 'select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 0,
        'sort' => 70,
        'set' => TRUE,
        'fieldset' => 1
    ),
    array(
        'name' => 'delete_links',
        'default_value' => 0,
        'type' => 'select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 0,
        'sort' => 80,
        'set' => TRUE,
        'fieldset' => 1
    ),
    array(
        'name' => 'aftersave',
        'default_value' => 'list',
        'type' => 'select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 9,
        'sort' => 90,
        'set' => TRUE,
        'fieldset' => 1
    ),
    array(
        'name' => 'root',
        'default_value' => 'site',
        'type' => 'text',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => NULL,
        'sort' => 100,
        'set' => TRUE,
        'fieldset' => 1
    ),

// permissions tab

    array(
        'name' => 'fs_permissions',
        'default_value' => 'N',
        'type' => 'fieldset',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'fieldset' => 2
    ),

    array(
        'name' => 'default_permissions',
        'default_value' => array(3,2,2,2),
        'type' => '@select',
        'group' => 'links',
        'subgroup' => 0,
        'selection_array' => 12,
        'sort' => 10,
        'set' => TRUE,
        'fieldset' => 2
    )
);