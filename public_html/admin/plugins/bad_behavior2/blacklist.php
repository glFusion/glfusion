<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | blacklist.php                                                            |
// |                                                                          |
// | glFusion BB2 Ban administration.                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2016-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

if (!SEC_inGroup ('Bad Behavior2 Admin')) {
    $display .= COM_siteHeader ('menu');
    $display .= COM_showMessageText($LANG20[6],$LANG20[1],true);
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

USES_lib_admin();
require_once $_CONF['path_html'] . '/bad_behavior2/bad-behavior-glfusion.php';

$display = '';

function BB2_blacklist_list($filterid = '')
{
    global $_CONF, $_USER, $_TABLES, $LANG_BAD_BEHAVIOR, $LANG_BB2_RESPONSE, $LANG_ADMIN, $_SYSTEM;

    $retval = '';

    // writing the menu on top
    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/index.php',
              'text' => $LANG_BAD_BEHAVIOR['log_entries']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/blacklist.php',
              'text' => $LANG_BAD_BEHAVIOR['blacklist'], 'active' => true),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/whitelist.php',
              'text' => $LANG_BAD_BEHAVIOR['whitelist']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $filter = '';
    $filter .= $LANG_BAD_BEHAVIOR['filter'] . ': ';
    $filter .= '<select name="filter_id" onchange="this.form.submit()">';
    $filter .= '<option value="">'.$LANG_BAD_BEHAVIOR['select_all'].'</option>';

    $filter .= '<option value="spambots_0"';
    if ( $filterid == 'spambots_0' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_BAD_BEHAVIOR['spambots_0'].'</option>';

    $filter .= '<option value="spambots"';
    if ( $filterid == 'spambots' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_BAD_BEHAVIOR['spambots'].'</option>';

    $filter .= '<option value="spambots_regex"';
    if ( $filterid == 'spambots_regex' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_BAD_BEHAVIOR['spambots_regex'].'</option>';

    $filter .= '<option value="spambots_url"';
    if ( $filterid == 'spambots_url' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_BAD_BEHAVIOR['spambots_url'].'</option>';

    $filter .= '<option value="spambot_referer"';
    if ( $filterid == 'spambot_referer' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_BAD_BEHAVIOR['spambot_referer'].'</option>';

    $filter .= '<option value="spambot_ip"';
    if ( $filterid == 'spambot_ip' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_BAD_BEHAVIOR['spambot_ip'].'</option>';

    $filter .= '</select>';

    $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['plugin_display_name'] . ' - ' . $LANG_BAD_BEHAVIOR['blacklist_items'], '',
    COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
    $menu_arr,
        $LANG_BAD_BEHAVIOR['blacklist_info_text'],
        $_CONF['site_url'] . '/bad_behavior2/images/bad_behavior2.png'
    );

    if (!empty ($msg)) {
        $retval .= COM_showMessage ($msg, 'bad_behavior2');
    }

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    $header_arr = array(
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center', 'width' => '5%'),
        array('text' => $LANG_BAD_BEHAVIOR['type'], 'field' => 'type', 'sort' => true, 'align' => 'center', 'width' => '10%'),
        array('text' => $LANG_BAD_BEHAVIOR['reason'], 'field' => 'reason', 'sort' => false, 'align' => 'center', 'width' => '5%'),
        array('text' => $LANG_BAD_BEHAVIOR['item'], 'field' => 'item', 'sort' => true, 'align' => 'left'),
    );

    $defsort_arr = array('field' => 'type', 'direction' => 'asc');

    $text_arr = array(
        'no_data'    => $LANG_BAD_BEHAVIOR['no_data'],
        'title'      => "",
        'form_url'   => $_CONF['site_admin_url'] . '/plugins/bad_behavior2/blacklist.php',
        'has_search'    => true,
        'has_limit'     => true,
        'has_paging'    => true,
    );
    if ( $_SYSTEM['framework'] == 'uikit' ) {
        $actions = '<button name="deletebutton" class="uk-button uk-button-mini uk-button-danger"'
            . '" title="' . $LANG_BAD_BEHAVIOR['delete_info']
            . '" onclick="return doubleconfirm(\'' . $LANG_BAD_BEHAVIOR['delete_bl_confirm_1'] . '\',\'' . $LANG_BAD_BEHAVIOR['delete_confirm_2'] . '\');"'
            . '/><i class="uk-icon uk-icon-remove"></i></button>&nbsp;' . $LANG_BAD_BEHAVIOR['delete'];
    } else {
        $actions = '<input name="deletebutton" type="image" src="'
            . $_CONF['layout_url'] . '/images/admin/delete.png'
            . '" style="vertical-align:text-bottom;" title="' . $LANG_BAD_BEHAVIOR['delete_info']
            . '" onclick="return doubleconfirm(\'' . $LANG_BAD_BEHAVIOR['delete_bl_confirm_1'] . '\',\'' . $LANG_BAD_BEHAVIOR['delete_confirm_2'] . '\');"'
            . '/>&nbsp;' . $LANG_BAD_BEHAVIOR['delete'];
    }
/*
    $actions = '<input name="deletebutton" type="image" src="'
        . $_CONF['layout_url'] . '/images/admin/delete.png'
        . '" style="vertical-align:text-bottom;" title="' . $LANG_BAD_BEHAVIOR['delete_info']
        . '" onclick="return doubleconfirm(\'' . $LANG_BAD_BEHAVIOR['delete_bl_confirm_1'] . '\',\'' . $LANG_BAD_BEHAVIOR['delete_confirm_2'] . '\');"'
        . '/>&nbsp;' . $LANG_BAD_BEHAVIOR['delete'];
*/
    $option_arr = array(
        'chkselect'     => true,
        'chkall'        => true,
        'chkfield'      => 'id',
        'chkname'       => 'actionitem',
        'chkactions'    => $actions,
    );

    if ( $filterid != '' ) {
        $sql = "SELECT id,item, type, reason, autoban, timestamp FROM {$_TABLES['bad_behavior2_blacklist']} WHERE 1=1 AND type='".DB_escapeString($filterid)."'";
    } else {
        $sql = "SELECT id,item, type, reason, autoban, timestamp FROM {$_TABLES['bad_behavior2_blacklist']} WHERE 1=1 ";
    }

    $query_arr = array(
        'table'         => 'bad_behavior2_blacklist',
        'sql'           => $sql,
        'query_fields'  => array('item'),
        'default_filter'=> ''
    );

    $token = SEC_createToken();
    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'"/>',
        'bottom' => '<input type="hidden" name="mode" value="delete"/>'
                  . '<button name="newblacklist" class="uk-button uk-button-success">'.$LANG_BAD_BEHAVIOR['new_entry'].'</button>'
    );

    $retval .= ADMIN_list(
        'bad_behavior2_ban', 'BB2_getBlacklistListField', $header_arr, $text_arr,
        $query_arr, $defsort_arr, $filter, $token, $option_arr, $form_arr
    );

    return $retval;
}

function BB2_getBlacklistListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG_BAD_BEHAVIOR, $_SYSTEM;

    $retval = '';

    $dt = new Date('now',$_USER['tzid']);

    switch ($fieldname) {
        case 'item' :
            $retval = $fieldvalue;
            break;

        case 'reason' :
            $tip = $fieldvalue;
            if ( $A['autoban'] != 0 ) {
                $tip .= '<br><br>'.$LANG_BAD_BEHAVIOR['temporary_ban'];
            }

            if ( $A['timestamp'] > 0 ) {
                $dt->setTimestamp($A['timestamp']);
                $adddate = $dt->format($_CONF['shortdate'],true);
                $tip .= '<br><br>'. $LANG_BAD_BEHAVIOR['added'].': '.$adddate;
            }
            if ( $_SYSTEM['framework'] == 'uikit' ) {
                $retval = '<a class="'.COM_getTooltipStyle().'" title="'.$tip.'"><i class="uk-icon uk-icon-hover uk-icon-info-circle uk-icon-justify"></i></a>';
            } else {
                $retval = '<a class="'.COM_getTooltipStyle().'" title="'.$tip.'">'.$icon_arr['info'].'</a>';
            }
            break;

        case 'edit':
            $retval = '';
            if ( $_SYSTEM['framework'] == 'uikit' ) {
                $retval = '<a href="'.$_CONF['site_admin_url'] . '/plugins/bad_behavior2/blacklist.php?mode=edit&amp;id=' . $A['id'].'" title="'.$LANG_ADMIN['edit'].'"><i class="uk-icon uk-icon-hover uk-icon-justify uk-icon-edit"></i></a>';
            } else {
                $attr['title'] = $LANG_ADMIN['edit'];
                $retval .= COM_createLink($icon_arr['edit'],
                    $_CONF['site_admin_url'] . '/plugins/bad_behavior2/blacklist.php?mode=edit&amp;id=' . $A['id'], $attr);
            }
            break;

        case 'delete' :
            $retval = '';
            $attr['title'] = $LANG_ADMIN['delete'];
            $attr['onclick'] = 'return doubleconfirm(\'' . $LANG28[104] . '\',\'' . $LANG28[109] . '\');';
            $retval .= COM_createLink($icon_arr['delete'],
            $_CONF['site_admin_url'] . '/plugins/bad_behavior2/blacklist.php'
            . '?delete=x&amp;ip=' . $A['ip'] . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            break;

        case 'type' :
            switch ( $fieldvalue) {
                case 'spambots_0' :
                    $retval = $LANG_BAD_BEHAVIOR['type_spambots_0'];
                    break;
                case 'spambots' :
                    $retval = $LANG_BAD_BEHAVIOR['type_spambots']; // 'UA Anywhere';
                    break;
                case 'spambots_regex' :
                    $retval = $LANG_BAD_BEHAVIOR['type_spambots_regex']; // 'UA Regex';
                    break;
                case 'spambots_url' :
                    $retval = $LANG_BAD_BEHAVIOR['type_spambots_url']; // 'URL String';
                    break;
                case 'spambot_ip' :
                    $retval = $LANG_BAD_BEHAVIOR['ip_addr'];
                    break;
                case 'spambot_referer' :
                    $retval = $LANG_BAD_BEHAVIOR['type_spambot_referer']; // 'Referer';
                    break;
                default :
                    $retval = $fieldvalue;
                    break;
            }
            break;

        default:
            $retval = $fieldvalue;
            break;
    }

    return $retval;
}

function BB2_blacklist_entry($msg = '')
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_BAD_BEHAVIOR;

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/index.php',
              'text' => $LANG_BAD_BEHAVIOR['log_entries']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/blacklist.php',
              'text' => $LANG_BAD_BEHAVIOR['blacklist'], 'active' => true),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/whitelist.php',
              'text' => $LANG_BAD_BEHAVIOR['whitelist']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['plugin_display_name'] . ' - ' . $LANG_BAD_BEHAVIOR['blacklist_new'], '',
    COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_BAD_BEHAVIOR['enter_ip_info'],
        $_CONF['site_url'] . '/bad_behavior2/images/bad_behavior2.png'
    );

    if (!empty ($msg)) {
        $retval .= COM_showMessage ($msg, 'bad_behavior2');
    }

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    $T = new Template ($_CONF['path'] . 'plugins/'.BAD_BEHAVIOR_PLUGIN.'/templates');
    $T->set_file ('entry','blacklist_entry.thtml');

    $T->set_var(array(
        'msg'                       => $msg,
        's_form_action'             => $_CONF['site_admin_url'].'/plugins/bad_behavior2/blacklist.php',
        'mode'                      => 'addsave',
        'lang_spambots_0'           => $LANG_BAD_BEHAVIOR['spambots_0'],
        'lang_spambots'             => $LANG_BAD_BEHAVIOR['spambots'],
        'lang_spambots_regex'       => $LANG_BAD_BEHAVIOR['spambots_regex'],
        'lang_spambots_url'         => $LANG_BAD_BEHAVIOR['spambots_url'],
        'lang_spambot_referer'      => $LANG_BAD_BEHAVIOR['spambot_referer'],
        'lang_spambot_ip'           => $LANG_BAD_BEHAVIOR['spambot_ip'],
        'lang_spambots_0_prompt'    => $LANG_BAD_BEHAVIOR['spambots_0_prompt'],
        'lang_spambots_prompt'      => $LANG_BAD_BEHAVIOR['spambots_prompt'],
        'lang_spambots_regex_prompt'=> $LANG_BAD_BEHAVIOR['spambots_regex_prompt'],
        'lang_spambots_url_prompt'  => $LANG_BAD_BEHAVIOR['spambots_url_prompt'],
        'lang_spambot_referer_prompt' => $LANG_BAD_BEHAVIOR['spambot_referer_prompt'],
        'lang_spambot_ip_prompt'    => $LANG_BAD_BEHAVIOR['spambot_ip_prompt'],
        'lang_temp_ban'             => $LANG_BAD_BEHAVIOR['temp_ban'],
        'lang_reason'               => $LANG_BAD_BEHAVIOR['reason'],
        'lang_note'                 => $LANG_BAD_BEHAVIOR['note'],
        'lang_enter_ip'             => $LANG_BAD_BEHAVIOR['enter_ip'],
        'lang_info'                 => $LANG_BAD_BEHAVIOR['enter_ip_info'],
        'lang_submit'               => $LANG_BAD_BEHAVIOR['submit'],
        'lang_cancel'               => $LANG_BAD_BEHAVIOR['cancel'],
    ));

    $T->parse('output', 'entry');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function BB2_blacklist_edit($msg = '')
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_BAD_BEHAVIOR;

    $retval = '';

    if ( !isset($_GET['id'])) return BB2_blacklist_list();

    $id = COM_applyFilter($_GET['id'],true);

    $result = DB_query("SELECT * FROM {$_TABLES['bad_behavior2_blacklist']} WHERE id=".(int) $id);
    if ( $result === false || DB_numRows($result) == 0 ) {
        COM_setMsg( $LANG_BAD_BEHAVIOR['invalid_item_id'],'error' );
        return BB2_blacklist_list();
    }
    $data = DB_fetchArray($result);

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/index.php',
              'text' => $LANG_BAD_BEHAVIOR['log_entries']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/blacklist.php',
              'text' => $LANG_BAD_BEHAVIOR['blacklist'], 'active' => true),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/whitelist.php',
              'text' => $LANG_BAD_BEHAVIOR['whitelist']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['plugin_display_name'] . ' - ' . $LANG_BAD_BEHAVIOR['blacklist_new'], '',
    COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_BAD_BEHAVIOR['enter_ip_info'],
        $_CONF['site_url'] . '/bad_behavior2/images/bad_behavior2.png'
    );

    if (!empty ($msg)) {
        $retval .= COM_showMessage ($msg, 'bad_behavior2');
    }

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    $T = new Template ($_CONF['path'] . 'plugins/'.BAD_BEHAVIOR_PLUGIN.'/templates');
    $T->set_file ('entry','blacklist_entry.thtml');

    switch ( $data['type'] ) {
        case 'spambot_ip'           : $T->set_var('spambot_ip_selected'      ,' selected="selected" ');break;
        case 'spambots_0'           : $T->set_var('spambots_0_selected'      ,' selected="selected" ');break;
        case 'spambots'             : $T->set_var('spambots_selected'        ,' selected="selected" ');break;
        case 'spambots_regex'       : $T->set_var('spambots_regex_selected'  ,' selected="selected" ');break;
        case 'spambots_url'         : $T->set_var('spambots_url_selected'    ,' selected="selected" ');break;
        case 'spambot_referer'      : $T->set_var('spambot_referer_selected' ,' selected="selected" ');break;
    }
    if ( $data['autoban'] != 0 ) {
        $T->set_var('temp_ban' ,' checked="checked" ');
    } else {
        $T->set_var('temp_ban' ,'');
    }

    $T->set_var(array(
        'msg'                           => $msg,
        's_form_action'                 => $_CONF['site_admin_url'].'/plugins/bad_behavior2/blacklist.php',
        'mode'                          => 'editsave',
        'bl_id'                         => $data['id'],
        'item'                          => $data['item'],
        'type'                          => $data['type'],
        'reason'                        => $data['reason'],
        'autoban'                       => $data['autoban'],
        'lang_spambots_0'               => $LANG_BAD_BEHAVIOR['spambots_0'],
        'lang_spambots'                 => $LANG_BAD_BEHAVIOR['spambots'],
        'lang_spambots_regex'           => $LANG_BAD_BEHAVIOR['spambots_regex'],
        'lang_spambots_url'             => $LANG_BAD_BEHAVIOR['spambots_url'],
        'lang_spambot_referer'          => $LANG_BAD_BEHAVIOR['spambot_referer'],
        'lang_spambot_ip'               => $LANG_BAD_BEHAVIOR['spambot_ip'],
        'lang_spambots_0_prompt'        => $LANG_BAD_BEHAVIOR['spambots_0_prompt'],
        'lang_spambots_prompt'          => $LANG_BAD_BEHAVIOR['spambots_prompt'],
        'lang_spambots_regex_prompt'    => $LANG_BAD_BEHAVIOR['spambots_regex_prompt'],
        'lang_spambots_url_prompt'      => $LANG_BAD_BEHAVIOR['spambots_url_prompt'],
        'lang_spambot_referer_prompt'   => $LANG_BAD_BEHAVIOR['spambot_referer_prompt'],
        'lang_spambot_ip_prompt'        => $LANG_BAD_BEHAVIOR['spambot_ip_prompt'],
        'lang_temp_ban'                 => $LANG_BAD_BEHAVIOR['temp_ban'],
        'lang_reason'                   => $LANG_BAD_BEHAVIOR['reason'],
        'lang_note'                     => $LANG_BAD_BEHAVIOR['note'],
        'lang_enter_ip'                 => $LANG_BAD_BEHAVIOR['enter_ip'],
        'lang_info'                     => $LANG_BAD_BEHAVIOR['enter_ip_info'],
        'lang_submit'                   => $LANG_BAD_BEHAVIOR['submit'],
        'lang_cancel'                   => $LANG_BAD_BEHAVIOR['cancel'],
    ));

    $T->parse('output', 'entry');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function BB2_process_blacklist($editsave = 0)
{
    global $_CONF, $_TABLES, $LANG_BAD_BEHAVIOR, $LANG_ADMIN;

    $retval = '';
    $msg    = '';
    $errors = 0;

    $reason = '';

    if ( $editsave == true ) {
        if ( !isset($_POST['id'])) {
            return BB2_blacklist_list();
        }
        $bl_id = COM_applyFilter($_POST['id'],true);
    }

    if ( isset($_POST['bl_item']) ) {
        if ( isset($_POST['bl_type'] ) ) {
            $bl_type = COM_applyFilter($_POST['bl_type']);
        } else {
            $bl_type = 'spambot_ip';
        }
        $bl_item = $_POST['bl_item'];

        if ( isset($_POST['ban_reason'] ) ) $reason = $_POST['ban_reason'];

        if ( isset($_POST['bl_type'] ) ) {
            $bl_type = COM_applyFilter($_POST['bl_type']);
        } else {
            $bl_type = 'spambot_ip';
        }

        // validation
        switch ( $bl_type ) {
            case 'spambot_ip' :
            if (!_validate_ip_cidr($bl_item)) {
                $errors++;
                $msg = $LANG_BAD_BEHAVIOR['ip_error'];
            }
        }

        if ( !$errors ) {
            $timestamp = time();

            if ( $editsave == true && $bl_id > 0 ) {
                if ( isset($_POST['tmp_ban'] ) ) {
                    $autoban = COM_applyFilter($_POST['tmp_ban'],true);
                } else {
                    $autoban = 0;
                }
                $sql = "UPDATE {$_TABLES['bad_behavior2_blacklist']} SET item='".DB_escapeString($bl_item)."', type='".DB_escapeString($bl_type)."', reason='".DB_escapeString($reason)."', autoban=".(int) $autoban . ", timestamp=".$timestamp." WHERE id=".(int) $bl_id;
            } else {
                $sql = "INSERT INTO {$_TABLES['bad_behavior2_blacklist']} (item,type,reason,timestamp) VALUE ('".DB_escapeString($bl_item)."','".DB_escapeString($bl_type)."','".DB_escapeString($reason)."',".$timestamp.")";
            }
            DB_query($sql);
            CACHE_remove_instance('bb2_bl_data');
        }
    } else {
        $errors++;
        $msg = $LANG_BAD_BEHAVIOR['no_bl_data_error'];
    }

    if ( $errors ) {
        $retval .= BB2_blacklist_entry($msg);
    } else {
        COM_setMsg( $LANG_BAD_BEHAVIOR['blacklist_success_save'],'info' );
        $retval .= BB2_blacklist_list();
    }
    return $retval;
}

function BB2_blacklist_delete()
{
    global $_CONF, $_TABLES, $LANG_BAD_BEHAVIOR;

    if ( defined('DEMO_MODE') ) {
        return 'Removing Blacklisted Items is disabled in Demo Mode';
    }

    if (isset($_POST['actionitem']) AND is_array($_POST['actionitem'])) {
        foreach($_POST['actionitem'] as $actionitem) {
            $id = COM_applyFilter($actionitem);
            DB_query("DELETE FROM {$_TABLES['bad_behavior2_blacklist']} WHERE id=".(int) $id);
        }
        CACHE_remove_instance('bb2_bl_data');
        COM_setMsg( $LANG_BAD_BEHAVIOR['blacklist_success_delete'],'info' );
    }
    return;
}

$validModes = array('list','add','edit', 'editsave', 'addsave','delete','cancel');

if ( isset($_GET['mode'] ) ) {
    $mode = COM_applyFilter($_GET['mode']);
} else if (isset($_POST['mode']) ) {
    $mode = COM_applyFilter($_POST['mode']);
} else {
    $mode = 'list';
}

if ( isset($_POST['cancel']) ) $mode = 'list';

if ( isset($_POST['newblacklist'])) $mode = 'add';

if ( !in_array($mode,$validModes) ) {
    $mode = 'list';
}

if ( !isset($_CONF['bb2_ban_timeout']) ) $_CONF['bb2_ban_timeout'] = 24;

$pageBody = '';

switch ( $mode ) {
    case 'list' :
        if ( isset($_POST['filter_id'] ) ) {
            $filter_id = COM_applyFilter($_POST['filter_id']);
        } else {
            $filter_id = '';
        }
        $pageBody .= BB2_blacklist_list($filter_id);
        break;

    case 'add' :
        $pageBody .= BB2_blacklist_entry();
        break;

    case 'addsave' :
        $pageBody .= BB2_process_blacklist();
        if ( $pageBody == '' ) {
            $pageBody .= BB2_blacklist_list();
        }
        break;

    case 'edit' :
        $pageBody .= BB2_blacklist_edit();
        break;

    case 'editsave' :
        $pageBody .= BB2_process_blacklist(true);
        break;

    case 'delete' :
        BB2_blacklist_delete();
        /* fall through intentionally */
    default :
        $pageBody .= BB2_blacklist_list();
        break;
}

$display .= COM_siteHeader('menu', 'BadBehavior2');
$display .= $pageBody;
$display .= COM_siteFooter();
echo $display;
?>