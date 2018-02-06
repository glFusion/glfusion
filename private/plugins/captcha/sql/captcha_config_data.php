<?php
/**
* glFusion CMS
*
* CAPTCHA Plugin Configuration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

$captchaConfigData = array(
    array(
        'name' => 'sg_main',
        'default_value' => NULL,
        'type' => 'subgroup',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'cp_public',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'gfxDriver',
        'default_value' => 6,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 2,
        'sort' => 10,
        'set' => TRUE,
        'group' => 'captcha'
    ),
/*
    array(
        'name' => 'imageset',
        'default_value' => 'default',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 4,
        'sort' => 20,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'gfxFormat',
        'default_value' => 'jpg',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 5,
        'sort' => 30,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'gfxPath',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 40,
        'set' => TRUE,
        'group' => 'captcha'
    ),
*/
    array(
        'name' => 'publickey',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 20,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'privatekey',
        'default_value' => '',
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 30,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'recaptcha_theme',
        'default_value' => 'light',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 6,
        'sort' => 40,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'debug',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 50,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'logging',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 60,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'expire',
        'default_value' => 900,
        'type' => 'text',
        'subgroup' => 0,
        'fieldset' => 0,
        'selection_array' => 0,
        'sort' => 70,
        'set' => TRUE,
        'group' => 'captcha'
    ),
// Integration fieldset
    array(
        'name' => 'cp_integration',
        'default_value' => NULL,
        'type' => 'fieldset',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => NULL,
        'sort' => 0,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'anonymous_only',
        'default_value' => '1',
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 10,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'remoteusers',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 20,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'enable_comment',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 30,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'enable_story',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 40,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'enable_registration',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 50,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'enable_loginform',
        'default_value' => 0,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 60,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'enable_forgotpassword',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 70,
        'set' => TRUE,
        'group' => 'captcha'
    ),


    array(
        'name' => 'enable_contact',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 80,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'enable_emailstory',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 90,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'enable_forum',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 100,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'enable_mediagallery',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 110,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'enable_links',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 120,
        'set' => TRUE,
        'group' => 'captcha'
    ),

    array(
        'name' => 'enable_calendar',
        'default_value' => 1,
        'type' => 'select',
        'subgroup' => 0,
        'fieldset' => 1,
        'selection_array' => 0,
        'sort' => 130,
        'set' => TRUE,
        'group' => 'captcha'
    )
);

?>