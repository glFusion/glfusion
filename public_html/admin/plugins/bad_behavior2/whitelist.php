<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | whitelist.php                                                            |
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

function BB2_whitelist_list($filterid = '')
{
    global $_CONF, $_USER, $_TABLES, $LANG_BAD_BEHAVIOR, $LANG_BB2_RESPONSE, $LANG_ADMIN, $_SYSTEM;

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/index.php',
              'text' => $LANG_BAD_BEHAVIOR['log_entries']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/blacklist.php',
              'text' => $LANG_BAD_BEHAVIOR['blacklist']),
        array('url' => '#',
              'text' => $LANG_BAD_BEHAVIOR['whitelist'], 'active' => true),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $filter = '';
    $filter .= $LANG_BAD_BEHAVIOR['filter'] . ': ';
    $filter .= '<select name="filter_id" onchange="this.form.submit()">';
    $filter .= '<option value="">'.$LANG_BAD_BEHAVIOR['select_all'].'</option>';
    $filter .= '<option value="ip"';
    if ( $filterid == 'ip' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_BAD_BEHAVIOR['select_iprange'].'</option>';
    $filter .= '<option value="ua"';
    if ( $filterid == 'ua' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_BAD_BEHAVIOR['select_ua'].'</option>';
    $filter .= '<option value="url"';
    if ( $filterid == 'url' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_BAD_BEHAVIOR['select_url'].'</option>';
    $filter .= '</select>';

    $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['plugin_display_name'] . ' - ' . $LANG_BAD_BEHAVIOR['whitelist_items'], '',
    COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
    $menu_arr,
        $LANG_BAD_BEHAVIOR['whitelist_info_text'],
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
        'form_url'   => $_CONF['site_admin_url'] . '/plugins/bad_behavior2/whitelist.php',
        'has_search'    => true,
        'has_limit'     => true,
        'has_paging'    => true,
    );

    if ( $_SYSTEM['framework'] == 'uikit' ) {
        $actions = '<button name="deletebutton" class="uk-button uk-button-mini uk-button-danger"'
            . '" title="' . $LANG_BAD_BEHAVIOR['delete_info']
            . '" onclick="return doubleconfirm(\'' . $LANG_BAD_BEHAVIOR['delete_wl_confirm_1'] . '\',\'' . $LANG_BAD_BEHAVIOR['delete_confirm_2'] . '\');"'
            . '/><i class="uk-icon uk-icon-remove"></i></button>&nbsp;' . $LANG_BAD_BEHAVIOR['delete'];
    } else {
        $actions = '<input name="deletebutton" type="image" src="'
            . $_CONF['layout_url'] . '/images/admin/delete.png'
            . '" style="vertical-align:text-bottom;" title="' . $LANG_BAD_BEHAVIOR['delete_info']
            . '" onclick="return doubleconfirm(\'' . $LANG_BAD_BEHAVIOR['delete_wl_confirm_1'] . '\',\'' . $LANG_BAD_BEHAVIOR['delete_confirm_2'] . '\');"'
            . '/>&nbsp;' . $LANG_BAD_BEHAVIOR['delete'];
    }

    $option_arr = array(
        'chkselect'     => true,
        'chkall'        => true,
        'chkfield'      => 'id',
        'chkname'       => 'actionitem',
        'chkactions'    => $actions,
    );

    if ( $filterid != '' ) {
        $sql = "SELECT id,item, type, reason, timestamp FROM {$_TABLES['bad_behavior2_whitelist']} WHERE 1=1 AND type='".DB_escapeString($filterid)."'";
    } else {
        $sql = "SELECT id,item, type, reason, timestamp FROM {$_TABLES['bad_behavior2_whitelist']} WHERE 1=1 ";
    }

    $query_arr = array(
        'table'         => 'bad_behavior2_whitelist',
        'sql'           => $sql,
        'query_fields'  => array('item'),
        'default_filter'=> ''
    );

    $token = SEC_createToken();
    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'"/>',
        'bottom' => '<input type="hidden" name="mode" value="delete"/>'
                  . '<button name="newwhitelist" class="uk-button uk-button-success">'.$LANG_BAD_BEHAVIOR['new_entry'].'</button>'
    );
    $retval .= ADMIN_list(
        'bad_behavior2_ban', 'BB2_getWhitelistListField', $header_arr, $text_arr,
        $query_arr, $defsort_arr, $filter, $token, $option_arr, $form_arr
    );

    return $retval;
}

function BB2_getWhitelistListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
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
            $attr['title'] = $LANG_ADMIN['edit'];

            if ( $_SYSTEM['framework'] == 'uikit' ) {
                $retval = '<a href="'.$_CONF['site_admin_url'] . '/plugins/bad_behavior2/whitelist.php?mode=edit&amp;id=' . $A['id'].'" title="'.$LANG_ADMIN['edit'].'"><i class="uk-icon uk-icon-hover uk-icon-justify uk-icon-edit"></i></a>';
            } else {
                $retval .= COM_createLink($icon_arr['edit'],
                            $_CONF['site_admin_url'] . '/plugins/bad_behavior2/whitelist.php?mode=edit&amp;id=' . $A['id'], $attr);
            }
            break;

        case 'delete' :
            $retval = '';
            $attr['title'] = $LANG_ADMIN['delete'];
            $attr['onclick'] = 'return doubleconfirm(\'' . $LANG28[104] . '\',\'' . $LANG28[109] . '\');';
            $retval .= COM_createLink($icon_arr['delete'],
            $_CONF['site_admin_url'] . '/plugins/bad_behavior2/whitelist.php'
            . '?delete=x&amp;ip=' . $A['ip'] . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            break;

        case 'type' :
            switch ( $fieldvalue) {
                case 'ip' :
                    $retval = $LANG_BAD_BEHAVIOR['ip_addr'];
                    break;
                case 'ua' :
                    $retval = $LANG_BAD_BEHAVIOR['useragent'];
                    break;
                case 'url' :
                    $retval = $LANG_BAD_BEHAVIOR['url'];
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

function BB2_whitelist_entry($msg = '')
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_BAD_BEHAVIOR;

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/index.php',
              'text' => $LANG_BAD_BEHAVIOR['log_entries']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/blacklist.php',
              'text' => $LANG_BAD_BEHAVIOR['blacklist']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/whitelist.php',
              'text' => $LANG_BAD_BEHAVIOR['whitelist'], 'active' => true),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['plugin_display_name'] . ' - ' . $LANG_BAD_BEHAVIOR['whitelist_new'], '',
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
    $T->set_file ('entry','whitelist_entry.thtml');

    // populate template here if needed

    $T->set_var(array(
        'msg'           => $msg,
        's_form_action' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/whitelist.php',
        'mode'          => 'addsave',
        'lang_reason'   => $LANG_BAD_BEHAVIOR['reason'],
        'lang_enter_ip' => $LANG_BAD_BEHAVIOR['enter_ip'],
        'lang_info'     => $LANG_BAD_BEHAVIOR['enter_ip_info'],
        'lang_submit'   => $LANG_BAD_BEHAVIOR['submit'],
        'lang_cancel'   => $LANG_BAD_BEHAVIOR['cancel'],
        'lang_ip'       => $LANG_BAD_BEHAVIOR['select_iprange'],
        'lang_ua'       => $LANG_BAD_BEHAVIOR['select_ua'],
        'lang_url'      => $LANG_BAD_BEHAVIOR['select_url'],
        'lang_ip_prompt' => $LANG_BAD_BEHAVIOR['ip_prompt'],
        'lang_ua_prompt' => $LANG_BAD_BEHAVIOR['ua_prompt'],
        'lang_url_prompt'=> $LANG_BAD_BEHAVIOR['url_prompt'],
        'lang_type'      => $LANG_BAD_BEHAVIOR['type'],
    ));

    $T->parse('output', 'entry');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function BB2_whitelist_edit($msg = '')
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_BAD_BEHAVIOR;

    $retval = '';

    if ( !isset($_GET['id'])) return BB2_whitelist_list();

    $id = COM_applyFilter($_GET['id'],true);

    $result = DB_query("SELECT * FROM {$_TABLES['bad_behavior2_whitelist']} WHERE id=".(int) $id);
    if ( $result === false || DB_numRows($result) == 0 ) {
        COM_setMsg( $LANG_BAD_BEHAVIOR['invalid_item_id'],'error' );
        return BB2_whitelist_list();
    }
    $data = DB_fetchArray($result);

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/index.php',
              'text' => $LANG_BAD_BEHAVIOR['log_entries']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/blacklist.php',
              'text' => $LANG_BAD_BEHAVIOR['blacklist']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/whitelist.php',
              'text' => $LANG_BAD_BEHAVIOR['whitelist'], 'active' => true),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['plugin_display_name'] . ' - ' . $LANG_BAD_BEHAVIOR['whitelist_new'], '',
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
    $T->set_file ('entry','whitelist_entry.thtml');

    // populate template here if needed

    switch ( $data['type'] ) {
        case 'ip' : $T->set_var('ip_selected',' selected="selected" ');break;
        case 'ua' : $T->set_var('ua_selected',' selected="selected" ');break;
        case 'url' : $T->set_var('url_selected',' selected="selected" ');break;
    }

    $T->set_var(array(
        'msg'           => $msg,
        's_form_action' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/whitelist.php',
        'mode'          => 'editsave',
        'wl_id'         => $data['id'],
        'item'          => $data['item'],
        'type'          => $data['type'],
        'reason'        => $data['reason'],

        'lang_reason'   => $LANG_BAD_BEHAVIOR['reason'],
        'lang_enter_ip' => $LANG_BAD_BEHAVIOR['enter_ip'],
        'lang_info'     => $LANG_BAD_BEHAVIOR['enter_ip_info'],
        'lang_submit'   => $LANG_BAD_BEHAVIOR['submit'],
        'lang_cancel'   => $LANG_BAD_BEHAVIOR['cancel'],
        'lang_ip'       => $LANG_BAD_BEHAVIOR['select_iprange'],
        'lang_ua'       => $LANG_BAD_BEHAVIOR['select_ua'],
        'lang_url'      => $LANG_BAD_BEHAVIOR['select_url'],
        'lang_ip_prompt' => $LANG_BAD_BEHAVIOR['ip_prompt'],
        'lang_ua_prompt' => $LANG_BAD_BEHAVIOR['ua_prompt'],
        'lang_url_prompt'=> $LANG_BAD_BEHAVIOR['url_prompt'],
        'lang_type'      => $LANG_BAD_BEHAVIOR['type'],
    ));

    $T->parse('output', 'entry');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function BB2_process_whitelist($editsave = 0)
{
    global $_CONF, $_TABLES, $LANG_BAD_BEHAVIOR, $LANG_ADMIN;

    $retval = '';
    $msg    = '';
    $errors = 0;
    $wl_id  = 0;

    $reason = '';

    if ( $editsave == true ) {
        if ( !isset($_POST['id'])) {
            return BB2_whitelist_list();
        }
        $wl_id = COM_applyFilter($_POST['id'],true);
    }

    if ( isset($_POST['wl_item']) ) {
        if ( isset($_POST['wl_type'] ) ) {
            $wl_type = COM_applyFilter($_POST['wl_type']);
        } else {
            $wl_type = 'ip';
        }
        switch ( $wl_type ) {
            case 'ip' :
                $wl_item = COM_applyFilter($_POST['wl_item']);
                break;

            case 'ua' :
                $wl_item = $_POST['wl_item'];
                break;

            case 'url' :
                $wl_item = $_POST['wl_item'];
                break;

            default :
                $wl_item = COM_applyFilter($_POST['wl_item']);
                break;
        }

        if ( isset($_POST['wl_reason'] ) ) $reason = $_POST['wl_reason'];

        switch ( $wl_type ) {
            case 'ip' :
                if (!_validate_ip_cidr($wl_item)) {
                    $errors++;
                    $msg = $LANG_BAD_BEHAVIOR['ip_error'];
                }
                break;
        }

        if ( !$errors ) {
            $timestamp = time();

            if ( $editsave == true && $wl_id > 0 ) {
                $sql = "UPDATE {$_TABLES['bad_behavior2_whitelist']} SET item='".DB_escapeString($wl_item)."', type='".DB_escapeString($wl_type)."', reason='".DB_escapeString($reason)."', timestamp=".$timestamp." WHERE id=".(int) $wl_id;
            } else {
                $sql = "INSERT INTO {$_TABLES['bad_behavior2_whitelist']} (item,type,reason,timestamp) VALUE ('".DB_escapeString($wl_item)."','".DB_escapeString($wl_type)."','".DB_escapeString($reason)."',".$timestamp.")";
            }
            DB_query($sql);
            CACHE_remove_instance('bb2_wl_data');
        }
    } else {
        $errors++;
        $msg = $LANG_BAD_BEHAVIOR['no_data_error'];
    }

    if ( $errors ) {
        $retval .= BB2_whitelist_entry($msg);
    } else {
        COM_setMsg( $LANG_BAD_BEHAVIOR['whitelist_success_save'],'info' );
        $retval .= BB2_whitelist_list();
    }
    return $retval;
}

function BB2_whitelist_delete()
{
    global $_CONF, $_TABLES, $LANG_BAD_BEHAVIOR;

    if ( defined('DEMO_MODE') ) {
        return 'Removing Whitelisted Items is disabled in Demo Mode';
    }

    if (isset($_POST['actionitem']) AND is_array($_POST['actionitem'])) {
        foreach($_POST['actionitem'] as $actionitem) {
            $id = COM_applyFilter($actionitem);
            DB_query("DELETE FROM {$_TABLES['bad_behavior2_whitelist']} WHERE id=".(int) $id);
        }
        CACHE_remove_instance('bb2_wl_data');
        COM_setMsg( $LANG_BAD_BEHAVIOR['whitelist_success_delete'],'info' );
    }
    return;
}

$validModes = array('list','add','edit','editsave','addsave','delete','cancel');

if ( isset($_GET['mode'] ) ) {
    $mode = COM_applyFilter($_GET['mode']);
} else if (isset($_POST['mode']) ) {
    $mode = COM_applyFilter($_POST['mode']);
} else {
    $mode = 'list';
}

if ( isset($_POST['cancel']) ) $mode = 'list';

if ( isset($_POST['newwhitelist'])) $mode = 'add';

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
        $pageBody .= BB2_whitelist_list($filter_id);
        break;

    case 'edit' :
        $pageBody .= BB2_whitelist_edit();
        break;

    case 'editsave' :
        $pageBody .= BB2_process_whitelist(true);
        break;

    case 'add' :
        $pageBody .= BB2_whitelist_entry();
        break;

    case 'addsave' :
        $pageBody .= BB2_process_whitelist();
        if ( $pageBody == '' ) {
            $pageBody .= BB2_whitelist_list();
        }
        break;

    case 'delete' :
        BB2_whitelist_delete();
        /* fall through intentionally */
    default :
        $pageBody .= BB2_whitelist_list();
        break;
}

$display .= COM_siteHeader('menu', 'BadBehavior2');
$display .= $pageBody;
$display .= COM_siteFooter();
echo $display;
?>