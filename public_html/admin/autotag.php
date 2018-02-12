<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | autotag.php                                                              |
// |                                                                          |
// | Autotag management console                                               |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009-2017 by the following authors:                        |
// |                                                                          |
// | Mark A. Howard         mark AT usable-web DOT com                        |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// +--------------------------------------------------------------------------+
// | Based upon the fine work of:                                             |
// |                                                                          |
// | Joe Mucchiello         joe AT throwingdice DOT com                       |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

// this is the autotag administrative interface

require_once '../lib-common.php';
require_once 'auth.inc.php';

// load functions not needed by core, but useful for admin or user access
// this also has a basic autotag.user security feature check

require_once $_CONF['path_system'].'lib-autotag.php';

// ensure the current user has rights to administer autotags

if (!SEC_hasRights('autotag.admin')) {
    COM_accessLog ("User {$_USER['username']} tried to access the Autotag Manager administration screen without the proper permissions.");
    $display = COM_siteHeader('menu')
        . COM_showMessageText($LANG_AM['access_denied_msg'],$LANG_AM['access_denied'],true,'error')
        . COM_siteFooter();
    echo $display;
    exit;
}

// return an array of all of the currently available autotags.
// used to check whether a newly created tag is unique or not

function AT_mergeAllTags()
{
    global $_AUTOTAGS, $_AM_CONF;

    $A = PLG_collectTags();
    $A = array_keys($A);
    $A = array_diff($A, array_keys($_AUTOTAGS));
    return array_merge($A, $_AM_CONF['disallow']);
}

// returns html to create the autotag admin form

function AT_adminForm($A, $error = false, $editOrNew = 0)
{
    global $_CONF, $LANG_AM, $_AM_CONF, $LANG_ADMIN;
    global $cc_url;

    USES_lib_admin();

    $editMode = false;

    if ($error) {
        $retval = $error . '<br/><br/>';
    } else {

        if ( $editOrNew) $editMode = true;

        if ( $editMode ) {
            $lang_create_edit = 'Edit Custom'; // $LANG_ADMIN['create_new'];
        } else {
            $lang_create_edit = $LANG_ADMIN['create_new'];
        }

        $form = new Template($_CONF['path_layout'] .'admin/autotag/');
        $form->set_file('form', 'autotag.thtml');

        $menu_arr = array (
            array('url' => $_CONF['site_admin_url'] . '/autotag.php', 'text' => $LANG_AM['public_title']),
            array('url' => $_CONF['site_admin_url'] . '/autotag.php?list=x','text' => $LANG_ADMIN['custom_autotag']),
            array('url' => $_CONF['site_admin_url'] . '/autotag.php' . '?edit=x', 'text' => $lang_create_edit,'active' => true),
            array('url' => $_CONF['site_admin_url'] . '/autotag.php?perm=x', 'text' => $LANG_AM['perm_editor']),
            array('url' => $cc_url, 'text' => $LANG_ADMIN['admin_home']),
        );
        $form->set_var(array(
                'start_block_editor'=> COM_startBlock($LANG_AM['autotag_editor'], '', COM_getBlockTemplate('_admin_block', 'header')),
                'end_block'         => COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')),
                'delete_option'     => '<input type="submit" value="' . $LANG_AM['delete'] . '" name="delete" />',
                'tag'               => $A['tag'],
                'old_tag'           => $A['old_tag'],
                'description'       => $A['description'],
                'replacement'       => isset($A['replacement']) ? $A['replacement'] : '',
                'gltoken_name'      => CSRF_TOKEN,
                'gltoken'           => SEC_createToken(),
                'phpfn_replace'     => $LANG_AM['phpfn_replace'],
                'phpfn_must_exist'  => $LANG_AM['phpfn_must_exist'],
                'html_allowed'      => $LANG_AM['html_allowed'],
                'lang_save'         => $LANG_AM['save'],
                'lang_cancel'       => $LANG_AM['cancel'],
                'lang_function'     => $LANG_AM['function'],
                'lang_tag'          => $LANG_AM['tag'],
                'lang_desc'         => $LANG_AM['description'],
                'lang_enabled'      => $LANG_AM['enabled'],
                'lang_replacement'  => $LANG_AM['replacement'],
                'lang_replace_explain'  => $LANG_AM['replace_explain'],
                'admin_menu' => ADMIN_createMenu($menu_arr, $LANG_AM['instructions_edit'],$_CONF['layout_url'] . '/images/icons/autotag.png'),
        ));

        if (isset($A['is_enabled']) && $A['is_enabled'] == 1) {
            $form->set_var('is_enabled_checked', 'checked="checked"');
            $form->set_var('enabled_msg', $LANG_AM['click_to_disable']);
        }
        else {
            $form->set_var('is_enabled_checked', '');
            $form->set_var('enabled_msg', $LANG_AM['click_to_enable']);
        }

        if ($_AM_CONF['allow_php'] == 1) {
            $is_function_checkbox = '<td><input id="is_function" type="checkbox" name="is_function"';
            $is_function_checkbox .= (isset($A['is_function']) && $A['is_function'] == 1) ? ' checked="checked"' : '';
            $is_function_checkbox .= (SEC_hasRights('autotag.PHP')) ? '' : ' disabled';
            $is_function_msg = $LANG_AM['php_msg_enabled'];
            $is_function_msg .= (SEC_hasRights('autotag.PHP')) ? '' : $LANG_AM['php_msg_norights'];
            $is_function_checkbox .= '/>&nbsp;&nbsp;</td>';
            $form->set_var('is_function_checkbox', $is_function_checkbox);
            $form->set_var('php_msg', $is_function_msg);
        } else {
            $form->set_var('php_msg', $LANG_AM['php_msg_disabled']);
        }
        $retval = $form->parse('output','form');
    }

    return $retval;
}

// displays the autotag editor panel.  parameter combinations invoke the actions:
//
// isset($tag) AND $action == 'edit': editing an existing autotag (refreshed from db)
// empty($tag) AND $action == 'edit': creating a new autotag (default values set)
// isset($tag) AND empty($action)   : refresh after failed validation, $_POST data retained

function AT_edit($tag, $action = '')
{
    global $_TABLES;

    $editOrNew = false;

    if (!empty($tag) && $action == 'edit') {
        // edit an existing autotag
        $query = DB_query("SELECT * FROM {$_TABLES['autotags']} WHERE tag = '".DB_escapeString($tag)."'");
        $A = DB_fetchArray($query);
        $A['old_tag'] = $A['tag'];
        $editOrNew = true;
    } elseif ($action == 'edit') {
        // create a new autotag
        $A['tag'] = '';
        $A['old_tag'] = '';
        $A['is_enabled'] = '0';
        $A['description'] = '';
        $A['replacement'] = '';
        $A['is_function'] = 0;
    } else {
        // this is a refresh
        $A = $_POST;
        $A['tag'] = COM_applyFilter($A['tag']);
    }
    return AT_adminForm($A,'',$editOrNew);
}

// performs server-side field validation, and saves autotag data to db

function AT_save($tag, $old_tag, $description, $is_enabled, $is_function, $replacement)
{
    global $_CONF, $LANG_AM, $_AM_CONF, $_TABLES;

    $retval = '';

    $old_tag = COM_applyFilter($old_tag);

    // Check for unique tag ID
    $duplicate_tag = false;
    $delete_old_tag = false;

    if (DB_count($_TABLES['autotags'], 'tag', DB_escapeString($tag)) > 0) {
        if ($tag != $old_tag) {
            $duplicate_tag = true; // whoops, this tag is already in use
        }
    } elseif (!empty ($old_tag)) {
        if ($tag != $old_tag) {
            $delete_old_tag = true; // ok, we're changing the name of the tag
        }
    }

    $is_function = ($is_function == 'on') ? 1 : 0;

    // If user does not have php edit perms, then set php flag to 0.
    if (($_AM_CONF['allow_php'] != 1) || !SEC_hasRights ('autotag.PHP')) {
        $is_function = 0;
    }

    // if this is a function, then there is no replacement string
    if ($is_function == 1) {
        $replacement = '';
    }

    // ok now let's do some server-side validation

    if ($duplicate_tag) {

        // this a duplicate autotag, output error msg (screen+log) and reenter editor
        // retain the current tag field values to allow name change

        $error = $LANG_AM['duplicate_tag'];
        COM_errorLog($error);

        $retval = COM_showMessageText($error,'',true,'error') . AT_edit($tag);

    } elseif (!empty($tag) && in_array($tag, AT_mergeAllTags())) {

        // this is a reserved autotag, output error msg (screen+log) and reenter editor
        // zap the current tag field values, and start over
        $error = $LANG_AM['disallowed_tag'];
        COM_errorLog($LANG_AM['disallowed_tag']);
        $retval = COM_showMessageText($error,'',true,'error') . AT_edit('');

    } elseif (!empty($tag) && ($is_function == 1) && !@file_exists($_CONF['path_system'].'autotags/'.$tag.'.class.php' ) ) {
        // an attempt was made to define a PHP autotag for which a function was
        // not yet available to support.  this is likely to cause an error, and
        // therefore we should warn the user to create the function first.

        $error = sprintf($LANG_AM['phpfn_missing'],$tag).' '.$LANG_AM['phpfn_must_exist'];
        COM_errorLog($error);
        $retval = COM_showMessageText($error,'',true,'error');
        $retval .= AT_edit('');

    } elseif (!empty($tag) && (!empty($replacement) || $is_function == 1)) {

        // validation passed!  save the autotag

        if ($is_enabled == 'on') {
            $is_enabled = 1;
        } else {
            $is_enabled = 0;
        }
        $tag = DB_escapeString($tag);
        $description = DB_escapeString($description);
        $replacement = DB_escapeString($replacement);

        DB_save($_TABLES['autotags'],
            'tag,description,is_enabled,is_function,replacement',
            "'$tag','$description',$is_enabled,$is_function,'$replacement'"
        );

        // delete old tag if necessary
        if ($delete_old_tag && !empty($old_tag)) {
            DB_delete($_TABLES['autotags'], 'tag', DB_escapeString($old_tag));
        }
        // refresh to ourself
        echo COM_refresh($_CONF['site_admin_url'] . '/autotag.php'.'?list=x');
    } else {
        // failed validation - required field missing
        $error = $LANG_AM['no_tag_or_replacement'];
        COM_errorLog($error);
        $retval = COM_showMessageText($error,'',true,'error') . AT_edit($tag);
    }

    return $retval;
}

// displays the administrative interface, including the list of manageable tags

function AT_adminList()
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG_AM, $cc_url, $public_url;

    USES_lib_admin();

    $retval = COM_startBlock($LANG_AM['title'], '', COM_getBlockTemplate('_admin_block', 'header'));

    // render the menu

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/autotag.php', 'text' => $LANG_AM['public_title']),
        array('url' => $_CONF['site_admin_url'] . '/autotag.php?list=x','text' => $LANG_ADMIN['custom_autotag'],'active' => true),
        array('url' => $_CONF['site_admin_url'] . '/autotag.php' . '?edit=x', 'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'] . '/autotag.php?perm=x', 'text' => $LANG_AM['perm_editor']),
        array('url' => $cc_url, 'text' => $LANG_ADMIN['admin_home']),
    );

    $retval .= ADMIN_createMenu($menu_arr, $LANG_AM['instructions'],
                $_CONF['layout_url'] . '/images/icons/autotag.png');

    $retval .= AT_showUploadForm();

    // render the autotag manager list

    $header_arr = array(
        array('text' => $LANG_AM['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_AM['tag'], 'field' => 'tag', 'sort' => true, 'align' => 'left'),
        array('text' => $LANG_AM['function'], 'field' => 'is_function', 'sort' => true, 'align' => 'center', 'nowrap' => true),
        array('text' => $LANG_AM['description'], 'field' => 'description', 'sort' => true),
        array('text' => $LANG_AM['enabled'], 'field' => 'is_enabled', 'sort' => true, 'align' => 'center'),
        array('text' => $LANG_AM['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center'),
    );

    $defsort_arr = array('field' => 'tag', 'direction' => 'asc');

    $text_arr = array('has_extras'   => true, 'form_url' => $_CONF['site_admin_url'].'/autotag.php?list=x');

    $query_arr = array('table' => 'autotags',
                       'sql' => "SELECT * FROM {$_TABLES['autotags']} WHERE 1 ",
                       'query_fields' => array('tag'),
                       'default_filter' => "");

    $token = SEC_createToken();

    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"/>',
        'bottom' => '<input type="hidden" name="tagenabler" value="true">'
    );

    $retval .= ADMIN_list("autotag", "AT_getListField", $header_arr, $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;

}

// generate a list of all active autotags with module, type and description

function AT_list()
{
    global $_CONF, $LANG_AM, $LANG_ADMIN;

    USES_lib_admin();

    $retval = '';

    // if an autotag admin is using this page, offer navigation to the admin page(s)

    if (SEC_hasRights('autotag.admin')) {
        $menu_arr = array (
            array('url' => $_CONF['site_admin_url'] . '/autotag.php', 'text' => $LANG_AM['public_title'], 'active' => true),
            array('url' => $_CONF['site_admin_url'] . '/autotag.php?list=x','text' => $LANG_ADMIN['custom_autotag']),
            array('url' => $_CONF['site_admin_url'] . '/autotag.php?perm=x', 'text' => $LANG_AM['perm_editor']),
            array('url' => $_CONF['site_admin_url'] . '/index.php', 'text' => $LANG_ADMIN['admin_home']),
        );
    } else {
        $menu_arr = array();
    }

    // display the header and instructions

    $retval .= COM_startBlock($LANG_AM['public_title'], '', COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, $LANG_AM['public_instructions'],
                $_CONF['layout_url'] . '/images/icons/autotag.png');

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    // default sort array and direction

    $defsort_arr = array('field' => 'tag', 'direction' => 'asc');

    // render the list of autotags, including module, type and description

    $header_arr = array(
        array('text' => 'Perm', 'field' => 'pedit', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_AM['tag'], 'field' => 'tag', 'sort' => true),
        array('text' => $LANG_AM['module'], 'field' => 'module', 'sort' => true),
        array('text' => $LANG_AM['type'], 'field' => 'type', 'nowrap' => true, 'sort' => true),
        array('text' => $LANG_AM['description'], 'field' => 'description' ),
    );

    $data_arr = AT_collectTags();

    $retval .= ADMIN_listArray('autotag-list', 'AT_getListField', $header_arr, $text_arr = '',
                                $data_arr, $defsort_arr, '', $extra = 'dummy', $options_arr = '', $form_arr = '');

    return $retval;
}

/**
* Shows the autotag upload form
*
*/
function AT_showUploadForm()
{
    global $_CONF,$LANG_ADMIN,$LANG32;

    $retval = '';

    $T = new Template($_CONF['path_layout'] . 'admin/autotag');
    $T->set_file('form','autotag_upload_form.thtml');

    $T->set_var(array(
        'form_action_url'   =>  $_CONF['site_admin_url'] .'/autotag_upload.php',
        'lang_upload_plugin' => $LANG32[85],
    ));

    $retval .= $T->parse('output', 'form');

    return $retval;
}

// generate field data for the ADMIN_list and ADMIN_simpleList presentations

function AT_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token='')
{
    global $_CONF, $_AM_CONF, $LANG_AM, $LANG_ACCESS, $LANG_ADMIN;

    $allow_php = $_AM_CONF['allow_php'];
    $isfunction = (isset($A['is_function']) && $A['is_function'] == 1) ? true : false;
    $isenabled = ((!$isfunction && isset($A['is_enabled']) && $A['is_enabled'] == 1) OR ($isfunction AND $allow_php AND $A['is_enabled'])) ? true : false;
    $phprights = SEC_hasRights('autotag.PHP');

    switch($fieldname) {

        case "edit":
            if ($A['is_function'] && (!$phprights || $_AM_CONF['allow_php'] == 0)) {
                $retval = '';
            } else {
                $attr['title'] = $LANG_ADMIN['edit'];
                $retval = COM_createLink($icon_arr['edit'], $_CONF['site_admin_url'].'/autotag.php'. '?edit=x&amp;tag=' . $A['tag'], $attr );
            }
            break;

        case 'pedit':
            $url = $_CONF['site_admin_url'] . '/autotag.php?pedit=x&amp;autotag_id=' . $A['tag'];
            $attr['title'] = $LANG_ADMIN['edit'];
            $retval = COM_createLink($icon_arr['edit'], $url, $attr);
            break;

        case "description":
            $retval = $A['description'];
            $retval = ($isenabled) ? $retval : '<span class="disabledfield">' . $retval . '</span>';
            break;

        case "type":
            switch ($A['type']) {
                case 'C':
                    $retval = 'Core';
                    break;
                case 'P':
                    $retval = 'Plugin';
                    break;
                case 'F':
                    $retval = 'PHPfn';
                    break;
                case 'U':
                    $retval = 'User';
                    break;
            }
            $retval = ($isenabled) ? $retval : '<span class="disabledfield">' . $retval . '</span>';
            break;

        case 'is_function':
            $retval = ($isfunction) ? (($isenabled) ? $icon_arr['check'] : $icon_arr['greycheck']) : '';
            break;

        case 'is_enabled':
            if ($isfunction AND !$allow_php) {
                $retval = '';
            } elseif ($isfunction AND $allow_php AND !$phprights) {
                $retval = ($isenabled) ? $icon_arr['check'] : '';
            } else {
                if ($isenabled) {
                    $switch = 'checked="checked"';
                    $title = 'title="' . $LANG_ADMIN['disable'] . '" ';
                } else {
                    $switch = '';
                    $title = 'title="' . $LANG_ADMIN['enable'] . '" ';
                }
                $retval = '<input type="checkbox" name="enabledtags[' . $A['tag'] . ']" ' . $title
                    . 'onclick="submit()" value="' . $A['tag'] . '"' . $switch .'>';
                $retval .= '<input type="hidden" name="tagarray[' . $A['tag'] . ']" value="1" ' .'>';
            }
            break;

        case 'delete':
            if (!$isfunction OR ($isfunction AND $phprights)) {
                $attr['title'] = $LANG_ADMIN['delete'];
                $attr['onclick'] = 'return confirm(\'' . $LANG_AM['confirm'] . '\');';
                $retval = COM_createLink($icon_arr['delete'],
                    $_CONF['site_admin_url'].'/autotag.php' . '?delete=x&amp;tag=' . $A['tag']
                    . '&amp;' . CSRF_TOKEN . '=' . $token, $attr );
            } else {
                $retval = '';
            }
            break;
        case 'usage_allowed' :
            $retval = '<input type="checkbox" name="perms[]" value="'. $A['usage_id'] . '"';
            if ($A['usage_allowed'] == 1 ) {
                $retval .= 'checked="checked"';
            }
            $retval .= '>';
            break;
        case 'usage_namespace' :
            $retval = $A['usage_namespace'];
            break;
        case 'usage_operation' :
            $retval = $A['usage_operation'];
            break;

        default:
            $retval = ($isenabled) ? $fieldvalue: '<span class="disabledfield">' . $fieldvalue . '</span>';

            break;
    }
    return $retval;
}

// sort an associative array

function AT_sortArray(&$data, $field, $dir='')
{
    $dir = strtolower($dir);
    $dir = (($dir == 'asc') OR ($dir == 'desc')) ? $dir : 'asc';
    usort($data,at_build_sorter($dir,$field));
}

function at_build_sorter($dir, $key) {
    if ( $dir == 'asc' ) {
        return function ($a, $b) use ($key) {
            return strnatcmp($a[$key], $b[$key]);
        };
    } else {
        return function ($a, $b) use ($key) {
            return -strnatcmp($a[$key], $b[$key]);
        };
    }
}


// toggle a tag's is_enabled status

function AT_toggleStatus($enabledtags, $tagarray)
{
    global $_AUTOTAGS, $_TABLES, $AM_CONF;

    $sizeofenabledtags = sizeof($enabledtags);
    $sizeoftagarray = sizeof($tagarray);

    if (isset($tagarray) AND is_array($tagarray)) {
        foreach ($tagarray as $tag => $junk) {
            $tag = COM_applyFilter($tag);
            if (isset($enabledtags[$tag])) {
                DB_query("UPDATE {$_TABLES['autotags']} set is_enabled = '1' WHERE tag='".DB_escapeString($tag)."'");
            } else {
                DB_query("UPDATE {$_TABLES['autotags']} set is_enabled = '0' WHERE tag='".DB_escapeString($tag)."'");
            }
        }
    }
    return;
}

/**
* Shows the autotag permission form
*
* @param    string      $autotag_id     ID of group to edit
* @return   string      HTML for group editor
*
*/
function ATP_edit($autotag_id = '')
{
    global $_TABLES, $_CONF, $_USER, $LANG01, $LANG_ACCESS, $LANG_ADMIN, $LANG_AM, $MESSAGE,
           $LANG28, $VERBOSE;

    USES_lib_admin();

    $retval   = '';
    $form_url = '';
    $token    = SEC_createToken();

    $admin_list = new Template($_CONF['path_layout'] .'admin/autotag/');
    $admin_list->set_file('admin', 'autotag_perm.thtml');

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/autotag.php','text' => $LANG_AM['public_title']),
        array('url' => $_CONF['site_admin_url'] . '/autotag.php?perm=x', 'text' => $LANG_AM['perm_editor']),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG01['autotag_perms'], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_AM['autotagpermmsg'],
        $_CONF['layout_url'] . '/images/icons/autotag.png'
    );

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    $header_arr = array(      # dislay 'text' and use table field 'field'
        array('text' => $LANG_AM['allowed'],     'field' => 'usage_allowed',   'sort' => false, 'align' => 'left'),
        array('text' => $LANG_AM['namespace'],   'field' => 'usage_namespace', 'sort' => true, 'align' => 'left'),
        array('text' => $LANG_AM['operation'],   'field' => 'usage_operation', 'sort' => true, 'align' => 'left'),
    );

    $text_arr = array('has_menu'  => false,
                      'title'     => $LANG_AM['autotag'].':&nbsp;'.$autotag_id,
                      'help_url'  => '',
                      'no_data'   => 'No data to display',
//                      'form_url'  => $_CONF['site_admin_url'].'/autotag.php?pedit=x&amp;autotag_id='.$autotag_id,
    );

    $defsort_arr = array('field' => 'usage_namespace', 'direction' => 'asc');

    $form_arr = array();
    $options = array();
    $extra   = array();

    $tagUsage = PLG_collectAutotagUsage();

    $sql  = "SELECT * FROM {$_TABLES['autotag_perm']} JOIN {$_TABLES['autotag_usage']} ON ";
    $sql .= "{$_TABLES['autotag_perm']}.autotag_id = {$_TABLES['autotag_usage']}.autotag_id ";
    $sql .= "WHERE {$_TABLES['autotag_perm']}.autotag_id = '".DB_escapeString($autotag_id)."' ORDER BY usage_namespace ASC";

    $result = DB_query($sql);

    $autoTagPerms = array();

    while ($row = DB_fetchArray($result) ) {
        $autoTagPerms[] = $row['autotag_name'].'.'.$row['usage_namespace'].'.'.$row['usage_operation'];
        $autotagPermissions[] = $row;
    }

    $autoTags = PLG_collectTags();

    foreach ( $autoTags AS $autotag_name => $namespace ) {
        if ( $autotag_name != $autotag_id) {
            continue;
        }
        foreach ( $tagUsage AS $usage ) {
            $allowed = 1; // default is to allow
            $needle = $autotag_name .'.'.$usage['namespace'].'.'.$usage['usage'];
            $pointer = array_search($needle,$autoTagPerms);
            if ( $pointer !== FALSE ) {
                $allowed = $autotagPermissions[$pointer]['autotag_allowed'];
            }
            $final[$needle] = array(
                    'usage_id'        => $needle,
                    'autotag_name'    => $autotag_name,
                    'usage_namespace' => $usage['namespace'],
                    'usage_operation' => $usage['usage'],
                    'usage_allowed'   => $allowed
            );
        }
    }

    $FinalList = ADMIN_listArray('autotag-list', 'AT_getListField', $header_arr, $text_arr,
                                $final, $defsort_arr, '', $extra, $options, $form_arr );

    $admin_list->set_var('admin_list',$FinalList);
    $admin_list->set_var('input_csrf_token',CSRF_TOKEN);
    $admin_list->set_var('var_token',$token);
    $admin_list->set_var('var_autotag_id',$autotag_id);
    $retval .= $admin_list->parse('output','admin');

    return $retval;
}


/**
* Save a autotag permissions to the database
*
* @param    string  $autotag_id     ID of autotag permission to save
* @param    array   $perms          Permissions / usage array
* @return   string                  HTML refresh or error message
*
*/
function ATP_save($autotag_id, $perms)
{
    global $_CONF, $_TABLES, $_USER, $LANG_ACCESS, $VERBOSE;

    $tagUsage = PLG_collectAutotagUsage();
    $autoTags = PLG_collectTags();

    foreach ( $autoTags AS $autotag_name => $namespace ) {
        if ( $autotag_name != $autotag_id) {
            continue;
        }
        foreach ( $tagUsage AS $usage ) {
            $allowed = 0;
            $needle = $autotag_name .'.'.$usage['namespace'].'.'.$usage['usage'];
            $pointer = array_search($needle,$perms);
            if ( $pointer !== FALSE ) {
                $allowed = 1;
            }
            $final[$needle] = array(
                    'usage_id'        => $needle,
                    'autotag_name'    => $autotag_name,
                    'autotag_namespace' => $namespace,
                    'usage_namespace' => $usage['namespace'],
                    'usage_operation' => $usage['usage'],
                    'usage_allowed'   => $allowed
            );
        }
    }

    // remove all the old entries for this autotag
    $sql = "DELETE FROM {$_TABLES['autotag_usage']} WHERE autotag_id='".DB_escapeString($autotag_id)."'";
    DB_query($sql);
    // check to see if we exist in the main table
    $sql = "SELECT * FROM {$_TABLES['autotag_perm']} WHERE autotag_id='".DB_escapeString($autotag_id)."'";
    $result = DB_query($sql);
    if ( DB_numRows($result) < 1 ) {
        $sql = "INSERT INTO {$_TABLES['autotag_perm']} (autotag_id,autotag_namespace,autotag_name) VALUES ";
        $sql .= "('".DB_escapeString($autotag_id)."','".DB_escapeString($autoTags[$autotag_id])."','".DB_escapeString($autotag_id)."')";
        DB_query($sql);
    }

    foreach($final AS $key ) {
        $sql = "INSERT INTO {$_TABLES['autotag_usage']} (autotag_id,autotag_allowed,usage_namespace,usage_operation) VALUES ('".DB_escapeString($key['autotag_name'])."',".(int) $key['usage_allowed'].",'".DB_escapeString($key['usage_namespace'])."','".DB_escapeString($key['usage_operation'])."')";
        DB_query($sql);
    }
    CACHE_clear();
    $url = $_CONF['site_admin_url'] . '/autotag.php?msg=36';
    echo COM_refresh($url);
    exit;
}

/**
* Permission editor by content area
*
* @return   string                  HTML - permission edit form
*
*/
function ATP_permEdit()
{
    global $_TABLES, $_CONF, $_USER, $LANG01, $LANG_ACCESS, $LANG_ADMIN, $LANG_AM, $MESSAGE,
           $LANG28, $VERBOSE;

    USES_lib_admin();

    $tagUsage = PLG_collectAutotagUsage();

    $autoTags = PLG_collectTags();
    ksort($autoTags);

    $retval = '';
    $autotagPermissions = array();
    $permission = '';

    // check to see if a permission pair has been passed

    if ( isset($_POST['perm'])) {
        $permission = COM_applyFilter($_POST['perm']);
    } else {
        $permission = '';
    }

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/autotag.php', 'text' => $LANG_AM['public_title']),
        array('url' => $_CONF['site_admin_url'] . '/autotag.php?list=x','text' => $LANG_ADMIN['custom_autotag']),
//        array('url' => $_CONF['site_admin_url'] . '/autotag.php' . '?edit=x', 'text' => $lang_create_edit),
        array('url' => $_CONF['site_admin_url'] . '/autotag.php?perm=x', 'text' => $LANG_AM['perm_editor'],'active' => true),
        array('url' => $_CONF['site_admin_url'], 'text' => $LANG_ADMIN['admin_home']),
    );
    $selectBox = '<select onchange="this.form.submit()" name="perm">';
    foreach ($tagUsage AS $perm ) {
        $indexIdentifier = $perm['namespace'].'.'.$perm['usage'];
        if ( $permission == '' ) $permission = $indexIdentifier;
        $selectBox .= '<option value="'.$indexIdentifier.'"';
        if ( $permission == $indexIdentifier ) $selectBox .= ' selected="selected"';
        $selectBox .= '>'.$indexIdentifier.'</option>';
    }
    $selectBox .= '</select>';

    // now pull everything for the permission we are processing
    $sqlSet = explode(".",$permission);

    $T = new Template($_CONF['path_layout'] .'admin/autotag/');
    $T->set_file('form', 'autotag_namespace_perm.thtml');

    $T->set_var(array(
        'namespace'         => $sqlSet[0],
        'usage'             => $sqlSet[1],
        'selectbox'         => $selectBox,
        'start_block_editor'=> COM_startBlock($LANG_AM['perm_header'], '', COM_getBlockTemplate('_admin_block', 'header')),
        'end_block'         => COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')),
        'admin_menu'        => ADMIN_createMenu($menu_arr, $LANG_AM['perm_instructions'],$_CONF['layout_url'] . '/images/icons/autotag.png'),
        'sec_token_name'    => CSRF_TOKEN,
        'sec_token'         => SEC_createToken(),
    ));

    // now build our data array for this specific item

    $sql  = "SELECT * FROM {$_TABLES['autotag_perm']} JOIN {$_TABLES['autotag_usage']} ON ";
    $sql .= "{$_TABLES['autotag_perm']}.autotag_id = {$_TABLES['autotag_usage']}.autotag_id ";
    $sql .= "WHERE usage_namespace='".DB_escapeString($sqlSet[0])."' AND usage_operation='".DB_escapeString($sqlSet[1])."' ORDER BY usage_namespace ASC";

    $result = DB_query($sql);
    while ($row = DB_fetchArray($result) ) {
        $identifier = $row['autotag_namespace'].'.'.$row['autotag_name'].'.'.$permission;
        $autotagPermissions[$identifier] = $row['autotag_allowed'];
    }
    // loop through all the auto tags - assigning permissions for the ones we have
    foreach ($autoTags AS $tag => $namespace) {
        $pIdentifier = $namespace .'.'.$tag.'.'.$permission;
        if ( isset($autotagPermissions[$pIdentifier]) ) {
            $enabled = $autotagPermissions[$pIdentifier];
        } else {
            $enabled = 1;
        }
        $tagarray = array(
            'permnamespace' => $perm['namespace'],
            'permusage' => $perm['usage'],
            'tagname' => $tag,
            'tagnamespace' => $namespace,
            'enabled' => $enabled,
        );
        $permissionMatrix[$permission][] = $tagarray;
    }

    // set template vars for auto tags

    $T->set_block('form', 'permItem', 'pi');
    foreach ( $permissionMatrix[$permission] AS $tags ) {
        $T->set_var(array(
            'tag_namespace' => $tags['tagnamespace'],
            'tag_name' => $tags['tagname'],
            'tag_checked' => ( $tags['enabled'] == 1 ) ? ' checked="checked"' : '',
        ));
        $T->parse('pi','permItem',true);
    }

    // render the page
    $T->parse ('output', 'form');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function ATP_permSave()
{
    global $_CONF, $_TABLES, $_USER, $LANG_ACCESS, $LANG_AM, $VERBOSE;

    $retval = '';

    $autoTags = PLG_collectTags();

    if ( isset($_POST['namespace'] ) && isset($_POST['usage'] ) ) {

         if ( !SEC_checkToken() ) {
            $page .= COM_showMessageText($MESSAGE[501],'',false,'info');
         } else {

            $usage_namespace = COM_applyFilter($_POST['namespace']);
            $usage_operation = COM_applyFilter($_POST['usage']);

            // remove all entries for this usage type first,
            // then we insert in the new ones
            $sql = "DELETE FROM {$_TABLES['autotag_usage']} WHERE usage_namespace='".
                    DB_escapeString($usage_namespace)."'";
            $sql .= " AND usage_operation='".DB_escapeString($usage_operation)."'";
            DB_query($sql);

            foreach ($autoTags AS $tag => $namespace) {
                $postIdentifier = $namespace.'_'.$tag;
                if ( !isset($_POST[$postIdentifier])) {

                    $sqlPermTable = "REPLACE INTO {$_TABLES['autotag_perm']} (autotag_id,autotag_namespace,autotag_name) VALUES ";
                    $sqlPermTable .= "('".DB_escapeString($tag)."','".DB_escapeString($namespace)."','".DB_escapeString($tag)."')";
                    DB_query($sqlPermTable,1);

                    $sqlUpdate = "INSERT INTO {$_TABLES['autotag_usage']} (autotag_id,autotag_allowed,usage_namespace,usage_operation) VALUES (";
                    $sqlUpdate .= "'".DB_escapeString($tag)."',0,'".DB_escapeString($usage_namespace)."','".DB_escapeString($usage_operation)."')";
                    DB_query($sqlUpdate);
                }
            }
            $c = glFusion\Cache::getInstance()->deleteItemsByTag('atperm');
            $retval .= COM_showMessageText($LANG_AM['perm_saved'],'',false,'info');
        }
    } else {
        $retval .= COM_showMessageText('Error saving permissions','',false,'error');
    }
    return $retval;
}

// MAIN ========================================================================

// setup the various URL's we will use

$cc_url = $_CONF['site_admin_url'] . '/index.php';
$public_url = $_CONF['site_url'] . '/autotag/index.php';

$pageContent = '';
$pageTitle   = $LANG_AM['title'];

if (isset($_GET['msg'])) {
    $msg = (int) COM_applyFilter($_GET['msg'], true);
    if ($msg > 0) {
        $plugin = '';
        if (isset($_GET['plugin'])) {
            $plugin = COM_applyFilter($_GET['plugin']);
        }
        $pageContent .= COM_showMessage($msg, $plugin,'',0,'error');
    }
}

// process the command line

$action = '';
$expected = array('edit','pedit','perm','save','psave','permsave','delete','list','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	    $action = $provided;
    }
}

// parse parameter(s) we're likely going to use

$tag = '';
if (isset($_POST['tag'])) {
    $tag = COM_applyFilter($_POST['tag']);
} elseif (isset($_GET['tag'])) {
    $tag = COM_applyFilter($_GET['tag']);
}
$tag = COM_sanitizeFilename($tag);

if (isset($_POST['tagenabler']) && SEC_checkToken()) {
    $enabledtags = array();
    if (isset($_POST['enabledtags'])) {
        $enabledtags = $_POST['enabledtags'];
    }
    $tagarray = array();
    if ( isset($_POST['tagarray']) ) {
        $tagarray = $_POST['tagarray'];
    }
    AT_toggleStatus($enabledtags, $tagarray);
    $action = 'list';
}

$autotag_id = 0;
if (isset($_POST['autotag_id'])) {
    $autotag_id = COM_applyFilter($_POST['autotag_id']);
} elseif (isset($_GET['autotag_id'])) {
    $autotag_id = COM_applyFilter($_GET['autotag_id']);
}

switch ($action) {

    case 'edit':
        $pageContent .= AT_edit($tag,$action);
        $pageTitle   = $LANG_AM['autotag_editor'];
        break;

    case 'pedit':
        $pageContent .= ATP_edit($autotag_id);
        $pageTitle    = $LANG01['autotag_perms'];
        break;

    case 'perm' :
        $pageContent .= ATP_permEdit();
        $pageTitle    = $LANG01['autotag_perms'];
        break;

    case 'permsave' :
        $pageContent .= ATP_permSave();
        $pageContent .= ATP_permEdit();
        break;

    case 'save':
        if (SEC_checkToken()) {
            if (!empty($tag)) {
                $pageContent .= AT_save($tag,
                             COM_applyFilter($_POST['old_tag']),
                             isset($_POST['description']) ? $_POST['description'] : '',
                             isset($_POST['is_enabled']) ? COM_applyFilter($_POST['is_enabled']) : '',
                             isset($_POST['is_function']) ? COM_applyFilter($_POST['is_function']) : '',
                             isset($_POST['replacement']) ? $_POST['replacement'] : '');
            } else {
                // we shouldn't be saving an empty tag - refresh to ourselves
                echo COM_refresh($_CONF['site_admin_url'] . '/autotag.php');
            }
        } else {
            COM_accessLog('User ' . $_USER['username'] . ' tried to illegally edit autotag ' . $tag . ' and failed CSRF checks.');
            echo COM_refresh($cc_url);
        }
        break;

    case 'psave':
        if (SEC_checkToken()) {
            $perms = array();
            $perms = (isset($_POST['perms']) ? $_POST['perms'] : array());
            $pageContent .= ATP_save($autotag_id,$perms,$_CONF['site_admin_url'] . '/autotag.php');
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally edit autotag permissions for $autotag_id and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'delete':
        if (SEC_checkToken()) {
            $filename = $tag . '.class.php';
            DB_delete($_TABLES['autotags'], 'tag', DB_escapeString($tag), $_CONF['site_admin_url'] . '/autotag.php');
            // need to check and see if it is a PHP function
            @unlink($_CONF['path_system'].'autotags/' . $filename);
            exit;
        } else {
            COM_accessLog('User ' . $_USER['username'] . ' did not have permissions to delete autotag ' . $tag . ' - operation failed CSRF checks.');
            echo COM_refresh($cc_url);
        }
        break;
    case 'list' :
        $pageContent = AT_Adminlist();
        $pageTitle   = $LANG_AM['public_title'];
        break;
    default:
        $pageContent .= AT_list();

        break;
}


$display = COM_siteHeader('menu',$pageTitle);
$display .= $pageContent;
$display .= COM_siteFooter();

echo $display;

?>