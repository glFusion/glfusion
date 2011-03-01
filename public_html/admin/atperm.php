<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | atperm.php                                                               |
// |                                                                          |
// | glFusion autotag permission administration page.                         |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2010-2011 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark Howard            mark AT usable-web DOT com                        |
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


require_once '../lib-common.php';
require_once 'auth.inc.php';

$display = '';

// Make sure user has rights to access this page
if (!SEC_hasRights ('autotag_perm.admin')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_startBlock ($MESSAGE[30], '',
                                COM_getBlockTemplate ('_msg_block', 'header'));
    $display .= $MESSAGE[37];
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= COM_siteFooter ();
    COM_accessLog ("User {$_USER['username']} tried to illegally access the autotag permission administration screen.");
    echo $display;
    exit;
}

function ATP_adminList()
{
    global $_CONF, $_USER, $_TABLES, $_IMAGE_TYPE, $LANG_ADMIN, $LANG_ATP;

    $LANG_ATP['tag']          = 'AutoTag';
    $LANG_ATP['provider']     = 'Provided By';
    $LANG_ATP['title']        = 'AutoTag Permission Manager';
    $LANG_ATP['instructions'] = 'Select edit to change the permissions on the auto tag';

    USES_lib_admin();

    $retval = COM_startBlock($LANG_ATP['title'], '', COM_getBlockTemplate('_admin_block', 'header'));

    // render the menu

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= ADMIN_createMenu($menu_arr, $LANG_ATP['instructions'],
                   $_CONF['layout_url'] . '/images/icons/autotag.' . $_IMAGE_TYPE);

    $autoTags = PLG_collectTags();
    ksort($autoTags);
    foreach ($autoTags AS $name => $namespace) {
        $tmpArray = array('autotag_id'=>$name,'autotag_name'=> $name,'autotag_namespace'=>$namespace);
        $data_arr[] = $tmpArray;
    }

    // render the autotag manager list

    $header_arr = array(      # dislay 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG_ATP['tag'], 'field' => 'autotag_name', 'sort' => true, 'align' => 'left'),
        array('text' => $LANG_ATP['provider'], 'field' => 'autotag_namespace', 'sort' => true),
    );

    $defsort_arr = array('field' => 'autotag_name', 'direction' => 'desc');

    $text_arr = array('has_extras'   => true, 'form_url' => $_CONF['site_admin_url'].'/atperm.php');

    $token = SEC_createToken();

    $form_arr = array(
        'top'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"/>',
        'bottom' => '<input type="hidden" name="tagenabler" value="true" />'
    );

    $retval .= ADMIN_simpleList("ATP_getListField", $header_arr, $text_arr,
                           $data_arr, '', $form_arr,$token);

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}


/**
 * autotag administration panel list field function for ADMIN_list()
 *
 */
function ATP_getListField($fieldname, $fieldvalue, $A, $icon_arr,$token)
{
    global $_CONF, $LANG_ACCESS, $LANG_ADMIN, $MESSAGE;

    $retval = false;

    switch($fieldname) {
        case 'edit':
            $url = $_CONF['site_admin_url'] . '/atperm.php?edit=x&amp;autotag_id=' . $A['autotag_id'];
            $attr['title'] = $LANG_ADMIN['edit'];
            $retval = COM_createLink($icon_arr['edit'], $url, $attr);
            break;
        default:
            $retval = $fieldvalue;
            break;
    }
    return $retval;
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
    global $_TABLES, $_CONF, $_USER, $LANG01, $LANG_ACCESS, $LANG_ADMIN, $MESSAGE,
           $LANG28, $VERBOSE, $_IMAGE_TYPE;

    $LANG_ADMIN['autotagpermmsg'] = 'Select which features (operations) you want to allow the autotag to be used.';
    $LANG_ADMIN['autotag'] = 'AutoTag';

    USES_lib_admin();

    $retval   = '';
    $form_url = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/atperm.php',
              'text' => 'AutoTag List'),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG01['autotag_perms'], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_ADMIN['autotagpermmsg'],
        $_CONF['layout_url'] . '/images/icons/autotag.' . $_IMAGE_TYPE
    );

    $retval .= '<form action="'.$_CONF['site_admin_url'].'/atperm.php" method="post">';
    $retval .= '<table cellspacing="0" cellpadding="2" width="100%" border="0">' .LB;
    $retval .= '<tr><td class="alignleft"><h1>'.$LANG_ADMIN['autotag'].':&nbsp;'.$autotag_id.'</h1></td><td>&nbsp;</td></tr>';
    $retval .= '<tr><td colspan="2" width="100%"><table border="0" width="100%" cellpadding="0" cellspacing="0">';

    $tagUsage = PLG_collectAutotagUsage();

    $sql  = "SELECT * FROM {$_TABLES['autotag_perm']} JOIN {$_TABLES['autotag_usage']} ON ";
    $sql .= "{$_TABLES['autotag_perm']}.autotag_id = {$_TABLES['autotag_usage']}.autotag_id ";
    $sql .= "WHERE {$_TABLES['autotag_perm']}.autotag_id = '".DB_escapeString($autotag_id)."' ORDER BY usage_namespace DESC";

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

    $ftcount = 0;
    $retval .= '<tr>';
    foreach($final AS $item) {
        if ( $ftcount > 0 && $ftcount % 3 == 0 ) {
            $retval .= '</tr>'.LB.'<tr>';
        }
        $pluginRow = sprintf('pluginRow%d', ($ftcount % 2) + 1);
        $ftcount++;
        $retval .= '<td class="' . $pluginRow . '">'
                . '<input type="checkbox" name="perms[]" value="'
                . $item['usage_id'] . '"';
        if ($item['usage_allowed'] == 1 ) {
            $retval .= 'checked="checked"';
        }
        $retval .= XHTML . '><span title="' . $item['autotag_name']. '">'
                . $item['usage_namespace'].'.'.$item['usage_operation'] . '</span></td>';

    }
    if ($ftcount == 0) {
        // There are no usage items defined
        $retval .= '<td colspan="3" class="pluginRow1">'
                . 'nothing to show' . '</td>';
    }
    $retval .= '</tr></table></td></tr>';
    $retval .= '<tr><td colspan="2">';
    $retval .= '<input type="submit" value="'.$LANG_ADMIN['save'].'" name="save"/>';
    $retval .= '<input type="submit" value="'.$LANG_ADMIN['cancel'].'" name="cancel" />';
    $retval .= '<input type="hidden" name="autotag_id" value="'.$autotag_id.'"/>';
    $retval .= '<input type="hidden" name="'.CSRF_TOKEN.'" value="'.SEC_createToken().'" />';
    $retval .= '</td></tr></table>';

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

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
    CTL_clearCache();
    $url = $_CONF['site_admin_url'] . '/atperm.php?msg=36';
    echo COM_refresh($url);
    exit;
}

// MAIN ========================================================================

$action = '';
$expected = array('edit','save');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
    }
}

$autotag_id = 0;
if (isset($_POST['autotag_id'])) {
    $autotag_id = COM_applyFilter($_POST['autotag_id']);
} elseif (isset($_GET['autotag_id'])) {
    $autotag_id = COM_applyFilter($_GET['autotag_id']);
}

$validtoken = SEC_checkToken();

switch ($action) {

    case 'edit':
        $display .= COM_siteHeader('menu', $LANG01['autotag_perms']);
        $display .= ATP_edit($autotag_id);
        $display .= COM_siteFooter();
        break;

    case 'save':
        if ($validtoken) {
            $perms = array();
            $perms = (isset($_POST['perms']) ? $_POST['perms'] : array());
            $display .= ATP_save($autotag_id,$perms);
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally edit autotag permissions for $autotag_id and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default:
        $display .= COM_siteHeader('menu', $LANG01['autotag_perms']);
        $display .= COM_showMessageFromParameter();
        $display .= ATP_adminList();
        $display .= COM_siteFooter();
        break;
}

echo $display;

?>