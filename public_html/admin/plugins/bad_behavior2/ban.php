<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | ban.php                                                                  |
// |                                                                          |
// | glFusion BB2 Ban administration.                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2014-2015 by the following authors:                        |
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

/*
CREATE TABLE IF NOT EXISTS `gl_bad_behavior2_ban` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `ip` varbinary(16) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE ip (ip)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

if (!SEC_inGroup ('Bad Behavior2 Admin')) {
    $display .= COM_siteHeader ('menu');
    $display .= COM_showMessageText($LANG20[6],$LANG20[1],true);
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

// **** TEMP LANG ENTRIES - move to language file prior to prod
$LANG_BAD_BEHAVIOR['automatic_token'] = 'Automatically Added - Token misuse';

USES_lib_admin();
require_once $_CONF['path_html'] . '/bad_behavior2/bad-behavior-glfusion.php';

$display = '';

function BB2_ban_list()
{
    global $_CONF, $_USER, $_TABLES, $LANG_BAD_BEHAVIOR, $LANG_BB2_RESPONSE, $LANG_ADMIN;

    $retval = '';

    // writing the menu on top
    $menu_arr = array (
        array('url'  => $_CONF['site_admin_url'].'/plugins/bad_behavior2/ban.php?mode=add',
              'text' => $LANG_BAD_BEHAVIOR['ban_ip']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/index.php',
              'text' => $LANG_BAD_BEHAVIOR['log_entries']),
        array('url'  => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['plugin_display_name'] . ' - ' . $LANG_BAD_BEHAVIOR['block_title_list'], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_BAD_BEHAVIOR['ban_list_info'],
        $_CONF['site_url'] . '/bad_behavior2/images/bad_behavior2.png'
    );

    if (!empty ($msg)) {
        $retval .= COM_showMessage ($msg, 'bad_behavior2');
    }

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    $header_arr = array(      # display 'text' and use table field 'field'
        array('text' => $LANG_BAD_BEHAVIOR['ip_address'], 'field' => 'ip', 'sort' => false, 'align' => 'left'),
        array('text' => $LANG_BAD_BEHAVIOR['type'], 'field' => 'type', 'sort' => true, 'align' => 'left'),
        array('text' => $LANG_BAD_BEHAVIOR['date'], 'field' => 'timestamp', 'sort' => true, 'align' => 'left'),
    );

    $defsort_arr = array('field' => 'ip', 'direction' => 'asc');

    $text_arr = array(
        'no_data'    => '',
        'title'      => "",
        'form_url'   => $_CONF['site_admin_url'] . '/plugins/bad_behavior2/ban.php',
        'has_search'    => true,
        'has_limit'     => true,
        'has_paging'    => true,
    );

    $actions = '<input name="deletebutton" type="image" src="'
        . $_CONF['layout_url'] . '/images/admin/delete.png'
        . '" style="vertical-align:text-bottom;" title="' . $LANG_BAD_BEHAVIOR['delete_info']
        . '" onclick="return doubleconfirm(\'' . $LANG_BAD_BEHAVIOR['delete_confirm_1'] . '\',\'' . $LANG_BAD_BEHAVIOR['delete_confirm_2'] . '\');"'
        . '/>&nbsp;' . $LANG_BAD_BEHAVIOR['delete'];

    $option_arr = array(
        'chkselect'     => true,
        'chkall'        => true,
        'chkfield'      => 'id',
        'chkname'       => 'actionitem',
        'chkactions'    => $actions,
    );

    $query_arr = array(
        'table' => 'bad_behavior2_ban',
        'sql' => "SELECT id,INET_NTOA(ip) AS ip, type, timestamp FROM {$_TABLES['bad_behavior2_ban']} WHERE 1=1",
        'query_fields' => array('INET_NTOA(ip)'),
        'default_filter' => ''
    );

    $token = SEC_createToken();
    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'"/>',
        'bottom' => '<input type="hidden" name="mode" value="delete"/>'
    );

    $retval .= ADMIN_list(
        'bad_behavior2_ban', 'BB2_getListField', $header_arr, $text_arr,
        $query_arr, $defsort_arr, '', $token, $option_arr, $form_arr
    );

    return $retval;
}

function BB2_getListField($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG_BAD_BEHAVIOR;

    $retval = '';

    $dt = new Date('now',$_USER['tzid']);

    switch ($fieldname) {
        case 'delete' :
        case 'delete':
            $retval = '';
            $attr['title'] = $LANG_ADMIN['delete'];
            $attr['onclick'] = 'return doubleconfirm(\'' . $LANG28[104] . '\',\'' . $LANG28[109] . '\');';
            $retval .= COM_createLink($icon_arr['delete'],
                $_CONF['site_admin_url'] . '/plugins/bad_behavior2/ban.php'
                . '?delete=x&amp;ip=' . $A['ip'] . '&amp;' . CSRF_TOKEN . '=' . $token, $attr);
            break;

        case 'type' :
            switch ( $A['type'] ) {
                case 0 :
                    $retval = $LANG_BAD_BEHAVIOR['manually_added'];
                    break;
                case 2 :
                    $retval = $LANG_BAD_BEHAVIOR['automatic_captcha'];
                    break;
                case 3:
                    $retval = $LANG_BAD_BEHAVIOR['automatic_token'];
                    break;
               default :
                    $retval = $A['type'];
                    break;
            }
            break;

        case 'timestamp' :
            if ( $A['type'] == 0 ) {
                $retval = ' - ';
            } else {
                $dt = new Date($A['timestamp'], $_CONF['timezone']);
                $retval = $dt->format($_CONF['date'],true);
            }
            break;

        default:
            $retval = $fieldvalue;
            break;
    }

    return $retval;
}

function BB2_enter_ban()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_BAD_BEHAVIOR;

    $retval = '';

    // writing the menu on top
    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/index.php',
              'text' => $LANG_BAD_BEHAVIOR['log_entries']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/ban.php',
              'text' => $LANG_BAD_BEHAVIOR['list_ips']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['plugin_display_name'] . ' - ' . 'Ban IPs', '',
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


    $T = new Template ($_CONF['path'] . 'plugins/'
                               . BAD_BEHAVIOR_PLUGIN . '/templates');
    $T->set_file ('entry','ban_entry.thtml');

// populate template here if needed

    $T->set_var(array(
        's_form_action' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/ban.php',
        'mode'          => 'addsave',
        'lang_info'     => $LANG_BAD_BEHAVIOR['enter_ip_info'],
        'lang_submit'   => $LANG_BAD_BEHAVIOR['submit'],
        'lang_cancel'   => $LANG_BAD_BEHAVIOR['cancel'],
    ));

    $T->parse('output', 'entry');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function BB2_process_ban()
{
    global $_CONF, $_TABLES, $LANG_BAD_BEHAVIOR, $LANG_ADMIN;

    $retval = '';

    // writing the menu on top
    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/index.php',
              'text' => $LANG_BAD_BEHAVIOR['log_entries']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/ban.php',
              'text' => $LANG_BAD_BEHAVIOR['list_ips']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['plugin_display_name'] . ' - ' . 'Ban IPs', '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_BAD_BEHAVIOR['ban_results'],
        $_CONF['site_url'] . '/bad_behavior2/images/bad_behavior2.png'
    );

    if (!empty ($msg)) {
        $retval .= COM_showMessage ($msg, 'bad_behavior2');
    }

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    $text = trim($_POST['banips']);
    $textAr = explode("\n", $text);
    $textAr = array_filter($textAr, 'trim'); // remove any extra \r characters left behind

    foreach ($textAr as $line) {
        $ip = trim($line);
        // here we would add it to the ban table

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $sql = "INSERT INTO {$_TABLES['bad_behavior2_ban']}
                   (ip,type) VALUE (INET_ATON('".DB_escapeString($ip)."'),0)";

            $result = DB_query($sql,1);
            if ($result === false ) {
                $retval .= sprintf($LANG_BAD_BEHAVIOR['duplicate_error'].'<br>', $ip);
            }
        } else {
            $retval .= sprintf($LANG_BAD_BEHAVIOR['invalid_ip'].'<br>',$ip);
        }
    }
    return $retval;
}

function BB2_ip_delete()
{
    global $_CONF, $_TABLES;

    if (isset($_POST['actionitem']) AND is_array($_POST['actionitem'])) {
        foreach($_POST['actionitem'] as $actionitem) {
            $id = COM_applyFilter($actionitem);
            DB_query("DELETE FROM {$_TABLES['bad_behavior2_ban']} WHERE id=".(int) $id);
        }
    }
    return;
}


$validModes = array('list','add','addsave','delete');

if ( isset($_GET['mode'] ) ) {
    $mode = COM_applyFilter($_GET['mode']);
} else if (isset($_POST['mode']) ) {
    $mode = COM_applyFilter($_POST['mode']);
} else {
    $mode = 'list';
}
if ( !in_array($mode,$validModes) ) {
    $mode = 'list';
}

$pageBody = '';

switch ( $mode ) {
    case 'list' :
        $pageBody .= BB2_ban_list();
        break;
    case 'add' :
        $pageBody .= BB2_enter_ban();
        break;
    case 'addsave' :
        $pageBody .= BB2_process_ban();
        if ( $pageBody == '' ) {
            $pageBody .= BB2_ban_list();
        }
        break;
    case 'delete' :
        BB2_ip_delete();
    default :
        $pageBody .= BB2_ban_list();
        break;
}

$display .= COM_siteHeader('menu', 'BadBehavior2');
$display .= $pageBody;
$display .= COM_siteFooter();
echo $display;
?>