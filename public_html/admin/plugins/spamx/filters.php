<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | filters.php                                                              |
// |                                                                          |
// | glFusion Spamx-X filter administration.                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2016-2018 by the following authors:                        |
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

if (!SEC_hasRights ('spamx.admin')) {
    $display .= COM_siteHeader ('menu');
    $display .= COM_showMessageText($LANG20[6],$LANG20[1],true);
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

USES_lib_admin();

$display = '';

function spamx_filter_list($filterid = '')
{
    global $_CONF, $_USER, $_TABLES, $LANG_SX00, $LANG_ADMIN, $_SYSTEM;

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['plugin_name']),
        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/filters.php','text' => $LANG_SX00['filters'], 'active' => true),
//        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['scan_comments']),
//        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['scan_trackbacks']),
        array('url' => $_CONF['site_admin_url'] . '/index.php','text' => $LANG_ADMIN['admin_home']),
    );

    $filter = '';
    $filter .= $LANG_SX00['filter'] . ': ';
    $filter .= '<select name="filter_id" onchange="this.form.submit()">';
    $filter .= '<option value="">'.$LANG_SX00['all'].'</option>';

    $filter .= '<option value="Personal"';
    if ( $filterid == 'Personal' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_SX00['blacklist'].'</option>';

    $filter .= '<option value="HTTPHeader"';
    if ( $filterid == 'HTTPHeader' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_SX00['http_header'].'</option>';

    $filter .= '<option value="IP"';
    if ( $filterid == 'IP' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_SX00['ip_blacklist'].'</option>';

    $filter .= '<option value="IPofUrl"';
    if ( $filterid == 'IPofUrl' ) $filter .= ' selected="selected" ';
    $filter .= '>'.$LANG_SX00['ipofurl'].'</option>';

    $filter .= '</select>';

    $retval .= COM_startBlock ($LANG_SX00['spamx_filters'], '',
    COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
    $menu_arr,
        $LANG_SX00['filter_instruction'],
        $_CONF['site_admin_url'] . '/plugins/spamx/images/spamx.png'
    );

    if (!empty ($msg)) {
        $retval .= COM_showMessage ($msg, 'spamx');
    }

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    $header_arr = array(
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center', 'width' => '5%'),
        array('text' => $LANG_SX00['type'], 'field' => 'name', 'sort' => true, 'align' => 'center', 'width' => '10%'),
        array('text' => $LANG_SX00['value'], 'field' => 'value', 'sort' => true, 'align' => 'left'),
    );

    $defsort_arr = array('field' => 'name', 'direction' => 'asc');

    $text_arr = array(
        'no_data'    => $LANG_SX00['no_filter_data'],
        'title'      => "",
        'form_url'   => $_CONF['site_admin_url'] . '/plugins/spamx/filters.php',
        'has_search'    => true,
        'has_limit'     => true,
        'has_paging'    => true,
    );
    if ( $_SYSTEM['framework'] == 'uikit' ) {
        $actions = '<button name="deletebutton" class="uk-button uk-button-mini uk-button-danger"'
            . '" title="' . $LANG_SX00['delete']
            . '" onclick="return doubleconfirm(\'' . $LANG_SX00['delete_confirm'] . '\',\'' . $LANG_SX00['delete_confirm_2'] . '\');"'
            . '/><i class="uk-icon uk-icon-remove"></i></button>&nbsp;' . $LANG_SX00['delete'];
    } else {
        $actions = '<input name="deletebutton" type="image" src="'
            . $_CONF['layout_url'] . '/images/admin/delete.png'
            . '" style="vertical-align:text-bottom;" title="' . $LANG_SX00['delete']
            . '" onclick="return doubleconfirm(\'' . $LANG_SX00['delete_confirm'] . '\',\'' . $LANG_SX00['delete_confirm_2'] . '\');"'
            . '/>&nbsp;' . $LANG_SX00['delete'];
    }

    $option_arr = array(
        'chkselect'     => true,
        'chkall'        => true,
        'chkfield'      => 'id',
        'chkname'       => 'actionitem',
        'chkactions'    => $actions,
    );

    if ( $filterid != '' ) {
        $sql = "SELECT * FROM {$_TABLES['spamx']} WHERE 1=1 AND name='".DB_escapeString($filterid)."'";
    } else {
        $sql = "SELECT * FROM {$_TABLES['spamx']} WHERE 1=1 ";
    }

    $query_arr = array(
        'table'         => 'spamx',
        'sql'           => $sql,
        'query_fields'  => array('value'),
        'default_filter'=> ''
    );

    $token = SEC_createToken();
    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'"/>',
        'bottom' => '<input type="hidden" name="mode" value="delete"/>'
                  . '<button name="add" class="uk-button uk-button-success">'.$LANG_SX00['new_entry'].'</button>'
    );

    $retval .= ADMIN_list(
        'spamx_filters', 'spamx_getBlacklistListField', $header_arr, $text_arr,
        $query_arr, $defsort_arr, $filter, $token, $option_arr, $form_arr
    );

    return $retval;
}

function spamx_getBlacklistListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG_SX00, $_SYSTEM;

    $retval = '';

    $dt = new Date('now',$_USER['tzid']);

    switch ($fieldname) {
        case 'item' :
            $retval = $fieldvalue;
            break;

        case 'edit':
            $retval = '';
            if ( $_SYSTEM['framework'] == 'uikit' ) {
                $retval = '<a href="'.$_CONF['site_admin_url'] . '/plugins/spamx/filters.php?mode=edit&amp;id=' . $A['id'].'" title="'.$LANG_ADMIN['edit'].'"><i class="uk-icon uk-icon-hover uk-icon-justify uk-icon-edit"></i></a>';
            } else {
                $attr['title'] = $LANG_ADMIN['edit'];
                $retval .= COM_createLink($icon_arr['edit'],
                    $_CONF['site_admin_url'] . '/plugins/spamx/filters.php?mode=edit&amp;id=' . $A['id'], $attr);
            }
            break;

        case 'delete' :
            $retval = '';
            $attr['title'] = $LANG_ADMIN['delete'];
            $attr['onclick'] = 'return doubleconfirm(\'' . $LANG28[104] . '\',\'' . $LANG28[109] . '\');';
            $retval .= COM_createLink($icon_arr['delete'],
            $_CONF['site_admin_url'] . '/plugins/spamx/filters.php'
            . '?delete=x&amp;ip=' . $A['ip'] . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            break;

        case 'name' :
            switch ( $fieldvalue) {
                case 'Personal' :
                    $retval = $LANG_SX00['blacklist'];
                    break;
                case 'IP' :
                    $retval = $LANG_SX00['ip_blacklist'];
                    break;
                case 'HTTPHeader' :
                    $retval = $LANG_SX00['http_header'];
                    break;
                case 'IPofUrl' :
                    $retval = $LANG_SX00['ipofurl'];
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

function spamx_filter_entry($msg = '')
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_SX00;

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['plugin_name']),
        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/filters.php','text' => $LANG_SX00['filters']),
//        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['scan_comments']),
//        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['scan_trackbacks']),
        array('url' => $_CONF['site_admin_url'] . '/index.php','text' => $LANG_ADMIN['admin_home']),
    );

    $retval .= COM_startBlock ($LANG_SX00['plugin_name'] . ' - ' . $LANG_SX00['new_filter_entry'], '',
    COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_SX00['filter_instruction'],
        $_CONF['site_admin_url'] . '/plugins/spamx/images/spamx.png'
    );

    if (!empty ($msg)) {
        $retval .= COM_showMessage ($msg, 'spamx');
    }

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    $T = new Template ($_CONF['path'] . 'plugins/spamx/templates');
    $T->set_file ('entry','filter_entry.thtml');

    $T->set_var(array(
        'msg'                       => $msg,
        's_form_action'             => $_CONF['site_admin_url'].'/plugins/spamx/filters.php',
        'mode'                      => 'addsave',
        'lang_blacklist'            => $LANG_SX00['blacklist'],
        'lang_http_header'          => $LANG_SX00['http_header'],
        'lang_ip_blacklist'         => $LANG_SX00['ip_blacklist'],
        'lang_ipofurl'              => $LANG_SX00['ipofurl'],
        'lang_blacklist_prompt'     => $LANG_SX00['blacklist_prompt'],
        'lang_http_header_prompt'   => $LANG_SX00['http_header_prompt'],
        'lang_ip_prompt'            => $LANG_SX00['ip_prompt'],
        'lang_ipofurl_prompt'       => $LANG_SX00['ipofurl_prompt'],
        'lang_content'              => $LANG_SX00['content'],
        'lang_new_filter_entry'     => $LANG_SX00['new_filter_entry'],
        'lang_submit'               => $LANG_SX00['submit'],
        'lang_cancel'               => $LANG_SX00['cancel'],
    ));

    $T->parse('output', 'entry');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function spamx_blacklist_edit($msg = '')
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_SX00;

    $retval = '';

    if ( !isset($_GET['id'])) return spamx_filter_list();

    $id = COM_applyFilter($_GET['id'],true);

    $result = DB_query("SELECT * FROM {$_TABLES['spamx']} WHERE id=".(int) $id);
    if ( $result === false || DB_numRows($result) == 0 ) {
        COM_setMsg( $LANG_SX00['invalid_item_id'],'error' );
        return spamx_filter_list();
    }
    $data = DB_fetchArray($result);

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['plugin_name']),
        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/filters.php','text' => $LANG_SX00['filters']),
//        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['scan_comments']),
//        array('url' => $_CONF['site_admin_url'] . '/plugins/spamx/index.php','text' => $LANG_SX00['scan_trackbacks']),
        array('url' => $_CONF['site_admin_url'] . '/index.php','text' => $LANG_ADMIN['admin_home']),
    );

    $retval .= COM_startBlock ($LANG_SX00['plugin_name'] . ' - ' . $LANG_SX00['edit_filter_entry'], '',
    COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_SX00['filter_instruction'],
        $_CONF['site_admin_url'] . '/plugins/spamx/images/spamx.png'
    );

    if (!empty ($msg)) {
        $retval .= COM_showMessage ($msg, 'spamx');
    }

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    $T = new Template ($_CONF['path'] . 'plugins/spamx/templates');
    $T->set_file ('entry','filter_entry.thtml');

    switch ( $data['name'] ) {
        case 'Personal'     : $T->set_var('personal_selected'      ,' selected="selected" ');break;
        case 'HTTPHeader'   : $T->set_var('httpheader_selected'      ,' selected="selected" ');break;
        case 'IP'           : $T->set_var('ip_selected'        ,' selected="selected" ');break;
        case 'IPofUrl'      : $T->set_var('ipofurl_selected'  ,' selected="selected" ');break;
    }

    if ( $data['name'] == 'HTTPHeader') {
        $colonPos = strpos($data['value'],':');
        if ( $colonPos !== 0 ) {
            $header = substr($data['value'], 0, $colonPos);
            $content = substr($data['value'], $colonPos+1);
        } else {
            $header = $data['value'];
            $content = '';
        }
    } else {
        $header = $data['value'];
        $content = '';
    }

    $T->set_var(array(
        'msg'                           => $msg,
        's_form_action'                 => $_CONF['site_admin_url'].'/plugins/spamx/filters.php',
        'mode'                          => 'editsave',
        'spamx_id'                      => $data['id'],
        'item'                          => $header,
        'http_header_content'           => $content,

       'lang_blacklist'            => $LANG_SX00['blacklist'],
        'lang_http_header'          => $LANG_SX00['http_header'],
        'lang_ip_blacklist'         => $LANG_SX00['ip_blacklist'],
        'lang_ipofurl'              => $LANG_SX00['ipofurl'],
        'lang_blacklist_prompt'     => $LANG_SX00['blacklist_prompt'],
        'lang_http_header_prompt'   => $LANG_SX00['http_header_prompt'],
        'lang_ip_prompt'            => $LANG_SX00['ip_prompt'],
        'lang_ipofurl_prompt'       => $LANG_SX00['ipofurl_prompt'],
        'lang_content'              => $LANG_SX00['content'],
        'lang_new_filter_entry'     => $LANG_SX00['new_filter_entry'],
        'lang_submit'               => $LANG_SX00['submit'],
        'lang_cancel'               => $LANG_SX00['cancel'],
    ));

    $T->parse('output', 'entry');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function spamx_process_blacklist($editsave = 0)
{
    global $_CONF, $_TABLES, $LANG_SX00, $LANG_ADMIN;

    $retval = '';
    $msg    = '';
    $errors = 0;

    $reason = '';

    if ( $editsave == true ) {
        if ( !isset($_POST['id'])) {
            return spamx_filter_list();
        }
        $spamx_id = COM_applyFilter($_POST['id'],true);
    }

    if ( isset($_POST['spamx_item']) ) {
        if ( isset($_POST['spamx_type'] ) ) {
            $spamx_type = COM_applyFilter($_POST['spamx_type']);
        } else {
            $spamx_type = 'Personal';
        }
        $spamx_item = $_POST['spamx_item'];

        if ( isset($_POST['spamx_content'] ) ) $spamx_content = $_POST['spamx_content'];

        if ( isset($_POST['spamx_type'] ) ) {
            $spamx_type = COM_applyFilter($_POST['spamx_type']);
        } else {
            $spamx_type = 'Personal';
        }

        // validation
        switch ( $spamx_type ) {
            case 'IP' :
            case 'IPofUrl' :
            if (!_validate_ip_cidr($spamx_item)) {
                $errors++;
                $msg = $LANG_SX00['ip_error'];
            }
        }

        if ( !$errors ) {

            if ( $spamx_type == 'HTTPHeader') {
                $spamx_item = $spamx_item .':'.$spamx_content;
            }

            if ( $editsave == true && $spamx_id > 0 ) {
                $sql = "UPDATE {$_TABLES['spamx']} SET name='".DB_escapeString($spamx_type)."', value='".DB_escapeString($spamx_item)."' WHERE id=".(int) $spamx_id;
            } else {
                $sql = "INSERT INTO {$_TABLES['spamx']} (name, value) VALUE ('".DB_escapeString($spamx_type)."','".DB_escapeString($spamx_item)."')";
            }
            DB_query($sql);
        }
    } else {
        $errors++;
        $msg = $LANG_SX00['no_bl_data_error'];
    }

    if ( $errors ) {
        $retval .= spamx_blacklist_entry($msg);
    } else {
        COM_setMsg( $LANG_SX00['blacklist_success_save'],'info' );
        $retval .= spamx_filter_list();
    }
    return $retval;
}

function spamx_blacklist_delete()
{
    global $_CONF, $_TABLES, $LANG_SX00;

    if ( defined('DEMO_MODE') ) {
        return 'Removing Blacklisted Items is disabled in Demo Mode';
    }

    if (isset($_POST['actionitem']) AND is_array($_POST['actionitem'])) {
        foreach($_POST['actionitem'] as $actionitem) {
            $id = COM_applyFilter($actionitem);
            DB_query("DELETE FROM {$_TABLES['spamx']} WHERE id=".(int) $id);
        }
        COM_setMsg( $LANG_SX00['blacklist_success_delete'],'info' );
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

if ( isset($_POST['add'])) $mode = 'add';

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
        $pageBody .= spamx_filter_list($filter_id);
        break;

    case 'add' :
        $pageBody .= spamx_filter_entry();
        break;

    case 'addsave' :
        $pageBody .= spamx_process_blacklist();
        if ( $pageBody == '' ) {
            $pageBody .= spamx_filter_list();
        }
        break;

    case 'edit' :
        $pageBody .= spamx_blacklist_edit();
        break;

    case 'editsave' :
        $pageBody .= spamx_process_blacklist(true);
        break;

    case 'delete' :
        spamx_blacklist_delete();
        /* fall through intentionally */
    default :
        $pageBody .= spamx_filter_list();
        break;
}

$display .= COM_siteHeader('menu', 'Spam-X');
$display .= $pageBody;
$display .= COM_siteFooter();
echo $display;
?>