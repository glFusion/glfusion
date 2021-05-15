<?php
/**
* glFusion CMS
*
* glFusion group administration page.
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*   Mark Howard     mark AT usable-web DOT com
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs         - tony AT tonybibbs DOT com
*  Mark Limburg       - mlimburg AT users DOT sourceforge DOT net
*  Jason Whittenburg  - jwhitten AT securitygeeks DOT com
*  Dirk Haun          - dirk AT haun-online DOT de
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Log\Log;
use \glFusion\Admin\AdminAction;
use \glFusion\FieldList;

$display = '';

// Make sure user has rights to access this page
if (!SEC_hasRights ('group.edit')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[37],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    Log::logAccessViolation('Group Administration');
    echo $display;
    exit;
}

/**
* Shows the group editor form
*
* @param    string      $grp_id     ID of group to edit
* @return   string      HTML for group editor
*
*/
function GROUP_edit($grp_id = '')
{
    global $_TABLES, $_CONF, $_USER, $LANG_ACCESS, $LANG_ADMIN, $MESSAGE,
           $LANG28, $VERBOSE, $_IMAGE_TYPE;

    USES_lib_admin();

    $db = Database::getInstance();

    $editMode = false;

    $retval = '';
    $form_url = '';

    $thisUsersGroups = SEC_getUserGroups();
    if (!SEC_ingroup(1) && !empty ($grp_id) && ($grp_id > 0) && !in_array ($grp_id, $thisUsersGroups) &&
    !SEC_groupIsRemoteUserAndHaveAccess($grp_id, $thisUsersGroups)) {
        $grpName = $db->getItem($_TABLES['groups'],'grp_name',array('grp_id' => $grp_id),array(Database::INTEGER));
        if (!SEC_inGroup ('Root') && $grpName == 'Root') {
            $eMsg = $LANG_ACCESS['canteditroot'];
            Log::write('system',Log::ERROR,"User {$_USER['username']} tried to edit the Root group with insufficient privileges.");
        } else {
            $eMsg = $LANG_ACCESS['canteditgroup'];
        }
        $retval .= COM_showMessageText($eMsg,$LANG_ACCESS['groupeditor'],true);
        return $retval;
    }

    $retval .= COM_startBlock ($LANG_ACCESS['groupeditor'], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $group_templates = new Template($_CONF['path_layout'] . 'admin/group');
    $group_templates->set_file('editor','groupeditor.thtml');

    $A['grp_gl_core'] = 0;
    $A['grp_default'] = 0;
    $A['grp_name'] = '';

    if (!empty ($grp_id) && $grp_id != 0 ) {
        $editMode = true;

        $row = $db->conn->fetchAssoc(
                "SELECT grp_id,grp_name,grp_descr,grp_gl_core,grp_default
                  FROM `{$_TABLES['groups']}`
                  WHERE grp_id = ?",
                array($grp_id),
                array(Database::INTEGER)
        );
        if ($row !== false && $row !== null) {
            $A = $row;
            if ($A['grp_gl_core'] > 0) {
                $group_templates->set_var ('chk_adminuse', 'checked="checked"');
            }
            if ($A['grp_default'] != 0) {
                $group_templates->set_var('chk_defaultuse', 'checked="checked"');
            }
        }
    }

    if ( $A['grp_name'] == 'Non-Logged-in Users' || $A['grp_name'] == 'Logged-in Users' || $A['grp_name'] == 'All Users' || $A['grp_name'] == 'Root' ) {
        $disable_edits = 1;
    } else {
        $disable_edit = 0;
    }
    if ( $editMode ) {
        $lang_create_edit = $LANG_ADMIN['edit'];
    } else {
        $lang_create_edit = $LANG_ADMIN['create_new'];
    }
    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/group.php',
              'text' => $LANG28[38]),
        array('url' => $_CONF['site_admin_url'] . '/group.php?edit=x',
              'text' => $lang_create_edit,'active' => true),
        array('url' => $_CONF['site_admin_url'] . '/user.php',
              'text' => $LANG_ADMIN['admin_users']),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home'])
    );
    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_ACCESS['groupeditmsg'],
        $_CONF['layout_url'] . '/images/icons/group.' . $_IMAGE_TYPE
    );

    $group_templates->set_var(array(
        'site_url'              => $_CONF['site_url'],
        'layout_url'            => $_CONF['layout_url'],
        'lang_save'             => $LANG_ADMIN['save'],
        'lang_cancel'           => $LANG_ADMIN['cancel'],
        'lang_admingroup'       => $LANG28[49],
        'lang_admingrp_msg'     => $LANG28[50],
        'lang_defaultgroup'     => $LANG28[88],
        'lang_defaultgrp_msg'   => $LANG28[89],
        'lang_applydefault_msg' => $LANG28[90],
        'lang_groupname'        => $LANG_ACCESS['groupname'],
        'lang_description'      => $LANG_ACCESS['description'],
        'lang_securitygroups'   => $LANG_ACCESS['securitygroups'],
        'lang_rights'           => $LANG_ACCESS['rights']
    ));

    $showall = (isset($_GET['chk_showall'])) ? COM_applyFilter ($_GET['chk_showall'], true) : 0;
    $group_templates->set_var('show_all', $showall);


    if (!empty($grp_id) && $grp_id != 0 ) {
        // Groups tied to glFusion's functionality shouldn't be deleted
        if ($A['grp_gl_core'] != 1) {
            $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                       . '" name="delete"%s />';
            $jsconfirm = ' onclick="return confirm(\'' . $LANG_ACCESS['confirm1'] . '\');"';
            $group_templates->set_var ('delete_option',
                                       sprintf ($delbutton, $jsconfirm));
            $group_templates->set_var ('delete_option_no_confirmation',
                                       sprintf ($delbutton, ''));

            $group_templates->set_var ('group_core', 0);
        } else {
            $group_templates->set_var ('group_core', 1);
        }
        $group_templates->set_var ('group_id', $A['grp_id']);
    } else {
        $group_templates->set_var ('group_core', 0);
    }

    $group_templates->set_var('lang_groupname', $LANG_ACCESS['groupname']);

    // if the group name is set, do not allow it to change ...  we need to do this better in the future ...
    if (isset($A['grp_name']) && $A['grp_name'] != '') {
        $group_templates->set_var('group_name', $A['grp_name']);

        // determine whether the group offers the option to make it a 'default group' for new users ...
        switch ($A['grp_name']) {
            case 'All Users':
            case 'Logged-in Users':
            case 'Non-Logged-in Users' :
            case 'Remote Users':
            case 'Root':
                $group_templates->set_var('hide_defaultoption',' style="display:none;"');
                break;
            default:
                $group_templates->set_var('hide_defaultoption', '');
                break;
        }
        $group_templates->set_var('groupname_inputtype', 'hidden');
        $group_templates->set_var('groupname_static', $A['grp_name']);
    } else {
        $group_templates->set_var('groupname_inputtype', 'text');
        $group_templates->set_var('group_name', '');
    }

    if (isset($A['grp_descr'])) {
        $group_templates->set_var('group_description', htmlspecialchars($A['grp_descr'],ENT_QUOTES,COM_getEncodingt()));
    } else {
        $group_templates->set_var('group_description', '');
    }

    $selected = '';
    if (!empty($grp_id)) {

        $num_groups = 0;
        $grpAssignments = array();
        $loopCounter = 0;

        $stmt = $db->conn->executeQuery(
            "SELECT ug_main_grp_id FROM `{$_TABLES['group_assignments']}` WHERE ug_grp_id = ?",
            array($grp_id),
            array(Database::INTEGER)
        );

        if ($stmt !== false && $stmt !== null) {
            $grpAssignments = $stmt->fetchAll(Database::ASSOCIATIVE);
            $num_groups = count($grpAssignments);
        }

        foreach($grpAssignments AS $G) {
            if ($loopCounter > 0) {
                $selected .= ' ' . $G['ug_main_grp_id'];
            } else {
                $selected .= $G['ug_main_grp_id'];
            }
            $loopCounter++;
        }
    }

    $groupoptions = '';

    $group_templates->set_var('lang_securitygroupmsg',$LANG_ACCESS['groupmsg']);
    $group_templates->set_var('hide_adminoption', '');

    if (empty($groupoptions)) {
        // make sure to list only those groups of which the Group Admin
        // is a member
        if (!SEC_inGroup(1)) {
            $whereGroups = '(grp_id IN (' . implode (',', $thisUsersGroups) . '))';
        } else {
            $whereGroups = '1=1';
        }

        $header_arr = array(
                    array('text' => $LANG28[86], 'field' => 'checkbox', 'sort' => false, 'align' => 'center'),
                    array('text' => $LANG_ACCESS['groupname'], 'field' => 'grp_name', 'sort' => true),
                    array('text' => $LANG_ACCESS['description'], 'field' => 'grp_descr', 'sort' => true)
        );

        $defsort_arr = array('field' => 'grp_name', 'direction' => 'asc');

        $form_url = $_CONF['site_admin_url'].'/group.php?edit=x&amp;grp_id=' . urlencode($grp_id);

        $text_arr = array('has_menu' => false,
                          'has_extras' => false,
                          'title' => '',
                          'instructions' => '',
                          'icon' => '' );

        $xsql = '';
        if (! empty($grp_id)) {
            $xsql = " AND (grp_id <> $grp_id)";
        }
        $sql = "SELECT grp_id, grp_name, grp_descr FROM `{$_TABLES['groups']}` WHERE (grp_name <> 'Root')" . $xsql . ' AND ' . $whereGroups;
        $query_arr = array('table' => 'groups',
                           'sql' => $sql,
                           'query_fields' => array('grp_name'),
                           'default_filter' => '',
                           'query' => '',
                           'query_limit' => 0);

        $groupoptions = ADMIN_list('groups', 'GROUP_getListField2',
                                   $header_arr, $text_arr, $query_arr,
                                   $defsort_arr, '', explode(' ', $selected));
    }
    $group_templates->set_var('group_options', $groupoptions);
    $group_templates->set_var('lang_rights', $LANG_ACCESS['rights']);

    if ($A['grp_gl_core'] == 1) {
        $group_templates->set_var('lang_rightsmsg', $LANG_ACCESS['rightsdescr']);
    } else {
        $group_templates->set_var('lang_rightsmsg', $LANG_ACCESS['rightsdescr']);
    }

    $group_templates->set_var('rights_options',
                              GROUP_displayRights($grp_id, $A['grp_gl_core']));

    $group_templates->set_var('gltoken_name', CSRF_TOKEN);
    $group_templates->set_var('gltoken', SEC_createToken());
    $group_templates->parse('output','editor');
    $retval .= $group_templates->finish($group_templates->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    return $retval;
}


/**
* Get the indirect features for a group, i.e. a list of all the features
* that this group inherited from other groups.
*
* @param    int      $grp_id   ID of group
* @return   string   comma-separated list of feature names
*
*/
function GROUP_getIndirectFeatures($grp_id)
{
    global $_TABLES;

    $db = Database::getInstance();

    $checked = array ();
    $tocheck = array ($grp_id);

    do {
        $grp = array_pop ($tocheck);

        $checked[] = $grp;

        $stmt = $db->conn->executeQuery(
                    "SELECT ug_main_grp_id
                      FROM `{$_TABLES['group_assignments']}`
                     WHERE ug_grp_id = ? AND ug_uid IS NULL",
                    array($grp),
                    array(Database::INTEGER)
        );

        while($A = $stmt->fetch(Database::ASSOCIATIVE)) {
            if (!in_array ($A['ug_main_grp_id'], $checked) && !in_array ($A['ug_main_grp_id'], $tocheck)) {
                $tocheck[] = $A['ug_main_grp_id'];
            }
        }

    } while (count($tocheck) > 0);

    $loopCounter = 0;
    $stmt = $db->conn->executeQuery(
                "SELECT DISTINCT ft_name FROM `{$_TABLES['access']}`,`{$_TABLES['features']}`
                 WHERE ft_id = acc_ft_id AND acc_grp_id IN (?)",
                array($checked),
                array(Database::PARAM_INT_ARRAY)
    );
    $retval = '';
    while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
        if ($loopCounter > 0) {
            $retval .= ',';
        }
        $loopCounter++;
        $retval .= $A['ft_name'];
    }
    return $retval;
}

/**
* Prints the features a group has access.  Please follow the comments in the
* code closely if you need to modify this function. Also right is synonymous
* with feature.
*
* @param    mixed       $grp_id     ID to print rights for
* @param    boolean     $core       indicates if group is a core glFusion group
* @return   string      HTML for rights
*
*/
function GROUP_displayRights($grp_id = '', $core = 0)
{
    global $_TABLES, $_USER, $LANG_ACCESS, $VERBOSE;

    $db = Database::getInstance();

    $grpftarray = array ();
    if (!empty($grp_id)) {

        // In many cases the features will be given to this user indirectly
        // via membership to another group.  These are not editable and must,
        // instead, be removed from that group directly
        $indirectfeatures = GROUP_getIndirectFeatures($grp_id);
        $indirectfeatures = explode (',', $indirectfeatures);

        // Build an array of indirect features
        for ($i = 0; $i < count($indirectfeatures); $i++) {
            $grpftarray[current($indirectfeatures)] = 'indirect';
            next($indirectfeatures);
        }

        // now get all the feature this group gets directly
        $stmt = $db->conn->executeQuery(
                    "SELECT acc_ft_id,ft_name
                    FROM `{$_TABLES['access']}`,`{$_TABLES['features']}`
                    WHERE ft_id = acc_ft_id AND acc_grp_id = ?",
                    array($grp_id),
                    array(Database::INTEGER)
        );

        // Build an arrray of direct features
        $grpftarray1 = array ();
        while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
            $grpftarray1[$A['ft_name']] = 'direct';
        }

        // Now merge the two arrays
        $grpftarray = array_merge ($grpftarray, $grpftarray1);
        if ($VERBOSE) {
            // this is for debugging purposes
            for ($i = 1; $i < count($grpftarray); $i++) {
                next($grpftarray);
            }
        }
    }

    if (!SEC_inGroup('Root')) {
        $GroupAdminFeatures = SEC_getUserPermissions();
        $availableFeatures = explode (',', $GroupAdminFeatures);

        $stmt = $db->conn->executeQuery(
            "SELECT ft_id, ft_name, ft_descr
             FROM `{$_TABLES['features']}`
             WHERE ft_name IN (?)
             ORDER BY ft_name",
            array($availableFeatures),
            array(Database::PARAM_STR_ARRAY)
        );
    } else {
        $stmt = $db->conn->executeQuery(
            "SELECT ft_id, ft_name, ft_descr
             FROM `{$_TABLES['features']}`
             ORDER BY ft_name",
            array(),
            array()
        );
    }

    // Loop through and print all the features giving edit rights
    // to only the ones that are direct features
    $ftcount = 0;
    $retval = '<tr>';

    while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
        if ((empty($grpftarray[$A['ft_name']]) OR ($grpftarray[$A['ft_name']] == 'direct')) ) {
            if (($ftcount > 0) && ($ftcount % 3 == 0)) {
                $retval .= '</tr>' . LB . '<tr>';
            }
            $pluginRow = sprintf('pluginRow%d', ($ftcount % 2) + 1);
            $ftcount++;

            $retval .= '<td class="' . $pluginRow . '">'
                    . '<input type="checkbox" name="features[]" value="'
                    . $A['ft_id'] . '"';
            if (!empty($grpftarray[$A['ft_name']])) {
                if ($grpftarray[$A['ft_name']] == 'direct') {
                    $retval .= ' checked="checked"';
                }
            }
            $retval .= '>&nbsp;<span title="' . $A['ft_descr'] . '">'
                    . $A['ft_name'] . '</span></td>';
        } else {
            // either this is an indirect right OR this is a core feature
            if (($ftcount > 0) && ($ftcount % 3 == 0)) {
                $retval .= '</tr>' . LB . '<tr>';
            }
            $pluginRow = sprintf('pluginRow%d', ($ftcount % 2) + 1);
            $ftcount++;

            $retval .= '<td class="' . $pluginRow . '">'
                    . '<input type="checkbox" checked="checked" '
                    . 'disabled="disabled">'
                    . '(<i title="' . $A['ft_descr'] . '">' . $A['ft_name']
                    . '</i>)</td>';
        }
    }
    if ($ftcount == 0) {
        // This group doesn't have rights to any features
        $retval .= '<td colspan="3" class="pluginRow1">'
                . $LANG_ACCESS['grouphasnorights'] . '</td>';
    }

    $retval .= '</tr>' . LB;

    return $retval;
}

/**
* Add or remove a default group to/from all existing accounts
*
* @param    int     $grp_id     ID of default group
* @param    boolean $add        true: add, false: remove
* @return   void
*
*/
function GROUP_applyDefault($grp_id, $add = true)
{
    global $_TABLES, $_GROUP_VERBOSE;

    $db = Database::getInstance();

    /**
    * In the "add" case, we have to insert one record for each user. Pack this
    * many values into one INSERT statement to save some time and bandwidth.
    */
    $_values_per_insert = 25;

    if ($add) {
        Log::write('system',Log::DVLP_DEBUG,"Adding group '$grp_id' to all user accounts");
    } else {
        Log::write('system',Log::DVLP_DEBUG,"Removing group '$grp_id' from all user accounts");
    }

    if ($add) {

        $insertCounter = 0;

        $stmt = $db->conn->query(
            "SELECT uid FROM `{$_TABLES['users']}` WHERE uid > 1"
        );

        while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
            $db->conn->insert(
                    $_TABLES['group_assignments'],
                    array(
                        'ug_main_grp_id' => $grp_id,
                        'ug_uid' => $row['uid']
                    ),
                    array(
                        Database::INTEGER,
                        Database::INTEGER
                    )
            );
        }
    } else {
        $db->conn->executeQuery(
            "DELETE FROM `{$_TABLES['group_assignments']}`
             WHERE (ug_main_grp_id = ?) AND (ug_grp_id IS NULL)",
            array($grp_id),
            array(Database::INTEGER)
        );
    }
}

/**
* Save a group to the database
*
* @param    string  $grp_id         ID of group to save
* @param    string  $grp_name       Group Name
* @param    string  $grp_descr      Description of group
* @param    boolean $grp_admin      Flag that indicates this is an admin use group
* @param    boolean $grp_gl_core    Flag that indicates if this is a core glFusion group
* @param    boolean $grp_default    Flag that indicates if this is a default group
* @param    boolean $grp_applydefault  Flag that indicates whether to apply a change in $grp_default to all existing user accounts
* @param    array   $features       Features the group has access to
* @param    array   $groups         Groups this group will belong to
* @return   string                  HTML refresh or error message
*
*/
function GROUP_save($grp_id, $grp_name, $grp_descr, $grp_admin, $grp_gl_core, $grp_default, $grp_applydefault, $features, $groups)
{
    global $_CONF, $_TABLES, $_USER, $LANG_ACCESS, $LANG_ADM_ACTIONS,$VERBOSE;

    $retval = '';

    $db = Database::getInstance();

    if (!empty ($grp_name) && !empty ($grp_descr)) {
        $GroupAdminGroups = SEC_getUserGroups ();

//@TODO - Look at SEC_groupIsRemoteUserAndHaveAccess - returns true now.
        if (!empty ($grp_id) && ($grp_id > 0) && !in_array ($grp_id, $GroupAdminGroups) &&
                  !SEC_groupIsRemoteUserAndHaveAccess($grp_id, $GroupAdminGroups)) {
            Log::write('system',Log::ERROR,"User {$_USER['username']} tried to edit group '$grp_name' ($grp_id) with insufficient privileges.");
            return COM_refresh ($_CONF['site_admin_url'] . '/group.php');
        }

        if ($grp_gl_core == 1 AND !is_array ($features)) {
            Log::write('system',Log::ERROR,"No valid features were passed to this core group ($grp_id) and saving could cause problem...bailing.");
            return COM_refresh ($_CONF['site_admin_url'] . '/group.php');
        }

        $g_id = (int) $db->getItem($_TABLES['groups'],'grp_id',array('grp_name' => $grp_name));

        if ($g_id > 0) {
            if (empty ($grp_id) || ($grp_id != $g_id)) {
                // there already is a group with that name - complain
                $retval .= COM_siteHeader ('menu', $LANG_ACCESS['groupeditor']);
                $retval .= COM_showMessageText($LANG_ACCESS['groupexistsmsg'],$LANG_ACCESS['groupexists'],true,'error');
                $retval .= GROUP_edit($grp_id);
                $retval .= COM_siteFooter ();

                return $retval;
            }
        }

        $grp_applydefault_add = true;

        if (empty($grp_id)) {

            $db->conn->executeUpdate(
                "REPLACE INTO `{$_TABLES['groups']}`
                  (grp_name,grp_descr,grp_gl_core,grp_default)
                  VALUES (?,?,?,?)",
                array(
                    $grp_name,
                    $grp_descr,
                    $grp_gl_core,
                    $grp_default
                ),
                array(
                    Database::STRING,
                    Database::STRING,
                    Database::INTEGER,
                    Database::INTEGER
                )
            );

            $grp_id = $db->getItem($_TABLES['groups'],'grp_id', array('grp_name' => $grp_name));

            $new_group = true;
        } else {
            if ($grp_applydefault == 1) {
                // check if $grp_default changed

                $old_default = $db->getItem($_TABLES['groups'],'grp_default',array('grp_id' => $grp_id));

                if ($old_default == $grp_default) {
                    // no change required
                    $grp_applydefault = 0;
                } elseif ($old_default == 1) {
                    $grp_applydefault_add = false;
                }
            }

            $db->conn->executeUpdate(
                "REPLACE INTO `{$_TABLES['groups']}`
                  (grp_id,grp_name,grp_descr,grp_gl_core,grp_default)
                  VALUES (?,?,?,?,?)",
                array(
                    $grp_id,
                    $grp_name,
                    $grp_descr,
                    $grp_gl_core,
                    $grp_default
                ),
                array(
                    Database::INTEGER,
                    Database::STRING,
                    Database::STRING,
                    Database::INTEGER,
                    Database::INTEGER
                )
            );

            $new_group = false;
        }

        if (empty($grp_id) || ($grp_id < 1)) {
            // "this shouldn't happen"
            Log::write('system',Log::ERROR,"Internal error: invalid group id");
            $retval .= COM_siteHeader('menu', $LANG_ACCESS['groupeditor']);
            $retval .= COM_showMessage(95);
            $retval .= COM_siteFooter();

            return $retval;
        }

        // Use the field grp_gl_core to indicate if this is non-core GL Group is an Admin related group
        if (($grp_gl_core != 1) AND ($grp_id > 1)) {
            if ($grp_admin == 1) {
                $db->conn->update(
                    $_TABLES['groups'],
                    array('grp_gl_core' => 2),
                    array('grp_id' => $grp_id),
                    array(Database::INTEGER)
                );
            } else {
                $db->conn->update(
                    $_TABLES['groups'],
                    array('grp_gl_core' => 0),
                    array('grp_id' => $grp_id),
                    array(Database::INTEGER)
                );
            }
        }

        // now save the features

        $db->conn->delete(
                $_TABLES['access'],
                array('acc_grp_id' => $grp_id),
                array(Database::INTEGER)
        );

        $num_features = count($features);
        if (SEC_inGroup('Root')) {
            foreach ($features as $f) {
                $f = intval($f);

                $db->conn->insert(
                    $_TABLES['access'],
                    array(
                        'acc_ft_id' => $f,
                        'acc_grp_id' => $grp_id
                    ),
                    array(
                        Database::INTEGER,
                        Database::INTEGER
                    )
                );
            }
        } else {
            $GroupAdminFeatures = SEC_getUserPermissions();
            $availableFeatures = explode(',', $GroupAdminFeatures);
            foreach ($features as $f) {
                if (in_array($f, $availableFeatures)) {
                    $f = intval($f);
                    $db->conn->insert(
                        $_TABLES['access'],
                        array(
                            'acc_ft_id' => $f,
                            'acc_grp_id' => $grp_id
                        ),
                        array(
                            Database::INTEGER,
                            Database::INTEGER
                        )
                    );
                }
            }
        }

        $db->conn->delete(
            $_TABLES['group_assignments'],
            array('ug_grp_id' => $grp_id),
            array(Database::INTEGER)
        );

        if (! empty($groups)) {
            foreach ($groups as $g) {
                if (in_array($g, $GroupAdminGroups) || SEC_inGroup(1)) {
                    $db->conn->insert(
                        $_TABLES['group_assignments'],
                        array(
                            'ug_main_grp_id' => $g,
                            'ug_grp_id' => $grp_id
                        ),
                        array(
                            Database::INTEGER,
                            Database::INTEGER
                        )
                    );
                }
            }
        }

        // Make sure Root group belongs to any new group

        $ngCount = $db->getCount(
                        $_TABLES['group_assignments'],
                        array('ug_main_grp_id','ug_grp_id'),
                        array($grp_id,1),
                        array(Database::INTEGER,Database::INTEGER)
        );

        if ($ngCount == 0) {
            $db->conn->insert(
                $_TABLES['group_assignments'],
                array(
                    'ug_main_grp_id' => $grp_id,
                    'ug_grp_id'     => 1
                ),
                array(
                    Database::INTEGER,
                    Database::INTEGER
                )
            );
        }

        // make sure this Group Admin belongs to the new group
        if (!SEC_inGroup ('Root')) {
            $gaCount = $db->getCount(
                            $_TABLES['group_assignments'],
                            array('ug_uid','ug_main_grp_id'),
                            array($_USER['uid'],$grp_id),
                            array(Database::INTEGER,Database::INTEGER)
            );
            if ($gaCount == 0) {
                $db->conn->insert(
                    $_TABLES['group_assignments'],
                    array(
                        'ug_main_grp_id' => $grp_id,
                        'ug_grp_id'     => $_USER['uid']
                    ),
                    array(
                        Database::INTEGER,
                        Database::INTEGER
                    )
                );
            }
        }

        AdminAction::write('system','group_save',sprintf($LANG_ADM_ACTIONS['group_updated'],$grp_name,$grp_id));

        if ($grp_applydefault == 1) {
            GROUP_applyDefault($grp_id, $grp_applydefault_add);
        }

        if ($new_group) {
            PLG_groupChanged ($grp_id, 'new');
        } else {
            PLG_groupChanged ($grp_id, 'edit');
        }
        Cache::getInstance()->deleteItemsByTags(array('menu', 'groups', 'group_' . $grp_id));
        COM_setMessage(49);
        $url = $_CONF['site_admin_url'] . '/group.php';
        $url .= (isset($_POST['chk_showall']) && ($_POST['chk_showall'] == 1)) ? '?chk_showall=1' : '';
        echo COM_refresh($url);
    } else {
        $retval .= COM_siteHeader ('menu', $LANG_ACCESS['groupeditor']);
        $retval .= COM_showMessageText($LANG_ACCESS['missingfieldsmsg'],$LANG_ACCESS['missingfields'],true,'error');
        $retval .= GROUP_edit($grp_id);
        $retval .= COM_siteFooter ();

        return $retval;
    }
}

/**
* Get a list (actually an array) of all groups this group belongs to.
*
* @param    int     $basegroup  id of group
* @return   array               array of all groups $basegroup belongs to
*
*/
function GROUP_getGroupList($basegroup)
{
    global $_TABLES;

    $db = Database::getInstance();

    $to_check = array ();
    array_push ($to_check, $basegroup);

    $checked = array ();

    while (count($to_check) > 0) {
        $thisgroup = array_pop ($to_check);
        if ($thisgroup > 0) {
            $stmt = $db->conn->executeQuery(
                    "SELECT ug_grp_id FROM `{$_TABLES['group_assignments']}`
                      WHERE ug_main_grp_id = ?",
                    array($thisgroup),
                    array(Database::INTEGER)
            );

            while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
                if (!in_array ($A['ug_grp_id'], $checked)) {
                    if (!in_array ($A['ug_grp_id'], $to_check)) {
                        array_push ($to_check, $A['ug_grp_id']);
                    }
                }
            }

            $checked[] = $thisgroup;
        }
    }

    return $checked;
}

/**
 * group administration panel list field function for ADMIN_list()
 *
 */
function GROUP_getListField1($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $LANG_ACCESS, $LANG_ADMIN, $MESSAGE, $thisUsersGroups;

    $retval = false;

    if(! is_array($thisUsersGroups)) {
        $thisUsersGroups = SEC_getUserGroups();
    }

    $showall = (isset($_REQUEST['chk_showall']) );

    if (SEC_inGroup('Root') || in_array ($A['grp_id'], $thisUsersGroups ) ||
        SEC_groupIsRemoteUserAndHaveAccess( $A['grp_id'], $thisUsersGroups )) {

        switch($fieldname) {

        case 'edit':
            $url = $_CONF['site_admin_url'] . '/group.php?edit=x&amp;grp_id=' . $A['grp_id'];
            $url .= ($showall) ? '&amp;chk_showall=1' : '';
            $retval = FieldList::edit(
                array(
                    'url' => $url,
                    'attr' => array(
                        'title' => $LANG_ADMIN['edit']
                    )
                )
            );
            break;
        case 'grp_name':
            $retval .= ucwords($fieldvalue);
            break;

        case 'grp_gl_core':
            if ($A['grp_gl_core'] == 1) {
                $retval = FieldList::checkmark(
                    array(
                       'active' => true
                    )
                );
            }
            break;

        case 'grp_default':
            if ($A['grp_default'] != 0) {
                $retval = FieldList::checkmark(
                    array(
                       'active' => true
                    )
                );
            }
            break;

        case 'grp_admin':
            if (($A['grp_gl_core'] == 1  || $A['grp_gl_core'] == 2) && $A['grp_name'] != 'All Users' && $A['grp_name'] != 'Logged-in Users' && $A['grp_name'] != 'Non-Logged-in Users') {
                $retval = FieldList::checkmark(
                    array(
                       'active' => true
                    )
                );
            }
            break;

        case 'sendemail':
            $retval = FieldList::email(
                array(
                    'url' => $_CONF['site_admin_url'] . '/mail.php?grp_id=' . $A['grp_id'],
                    'attr' => array(
                        'title' => $LANG_ACCESS['sendemail']
                    )
                )
            );
            break;

        case 'listusers':
            $url = $_CONF['site_admin_url'] . '/user.php?grp_id=' . $A['grp_id'];
            $url .= ($showall) ? '&amp;chk_showall=1' : '';
            $attr['title'] = $LANG_ACCESS['listusers'];
            $retval = COM_createLink($icon_arr['group'], $url, $attr);
            break;

        case 'editusers':
            $retval = '';
            if (($A['grp_name'] != 'All Users') && ($A['grp_name'] != 'Logged-in Users') && $A['grp_name'] != 'Non-Logged-in Users') {
                $url = $_CONF['site_admin_url'] . '/group.php?editusers=x&amp;grp_id=' . $A['grp_id'];
                $url .= ($showall) ? '&amp;chk_showall=1' : '';

                $retval = FieldList::editusers(
                    array(
                        'url' => $url,
                        'attr' => array(
                            'title' => $LANG_ACCESS['editusers']
                        )
                    )
                );
            }
            break;

        case 'delete':
            $retval = '';
            if ($A['grp_gl_core'] <> 1) {
                $retval = FieldList::delete(
                    array(
                        'delete_url' => $_CONF['site_admin_url'] . '/group.php'.'?delete=x&amp;grp_id='.$A['grp_id'].'&amp;'.CSRF_TOKEN.'='.$token,
                        'attr' => array(
                            'title'   => $LANG_ADMIN['delete'],
                            'onclick' => "return doubleconfirm('" . $LANG_ACCESS['confirm1'] . "','" . $LANG_ACCESS['confirm2'] . "');"
                        ),
                    )
                );
            }
            break;

        default:
            $retval = $fieldvalue;
            break;
        }
    }

    return $retval;
}

/**
 * group administration panel list field function for ADMIN_list()
 *
 */
function GROUP_getListField2($fieldname, $fieldvalue, $A, $icon_arr, $selected = '')
{
    global $_CONF, $LANG_ACCESS, $LANG_ADMIN, $MESSAGE, $thisUsersGroups;

    $retval = false;

    if(! is_array($thisUsersGroups)) {
        $thisUsersGroups = SEC_getUserGroups();
    }

    $showall = (isset($_REQUEST['chk_showall']) );

    if (in_array ($A['grp_id'], $thisUsersGroups ) ||
        SEC_groupIsRemoteUserAndHaveAccess( $A['grp_id'], $thisUsersGroups )) {

        switch($fieldname) {

        case 'checkbox':
            $retval = '<input type="checkbox" name="groups[]" value="' . $A['grp_id'] . '"';
            if (is_array($selected) && in_array($A['grp_id'], $selected)) {
                $retval .= ' checked="checked"';
            }
            $retval .= '>';
            break;

        case 'disabled-checkbox':
            $retval = '<input type="checkbox" checked="checked" '
                    . 'disabled="disabled">';
            break;

        case 'grp_name':
            $retval = ucwords($fieldvalue);
            break;

        default:
            $retval = $fieldvalue;
            break;
        }
    }

    return $retval;
}

/**
* Display a list of (all) groups
*
* @param    boolean     $show_all_groups    include admin groups if true
* @return   string                          HTML of the group list
*
*/
function GROUP_list($show_all_groups = false)
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACCESS, $LANG28, $_IMAGE_TYPE;

    USES_lib_admin();

    $retval = '';

    $header_arr = array();
    $header_arr[] = array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center', 'width' => '35px');
    $header_arr[] = array('text' => $LANG_ACCESS['groupname'], 'field' => 'grp_name', 'sort' => true, 'width' => '175px');
    $header_arr[] = array('text' => $LANG_ACCESS['description'], 'field' => 'grp_descr', 'sort' => true);

    if ($show_all_groups) {
        $header_arr[] = array('text' => $LANG_ACCESS['admingroup'], 'field' => 'grp_admin', 'sort' => false, 'align' => 'center');
    }

    $header_arr[] = array('text' => $LANG_ACCESS['coregroup'], 'field' => 'grp_gl_core', 'sort' => true, 'align' => 'center', 'width' => '40px');
    $header_arr[] = array('text' => $LANG_ACCESS['defaultgroup'], 'field' => 'grp_default', 'sort' => true, 'align' => 'center', 'width' => '40px');
    $header_arr[] = array('text' => $LANG_ACCESS['sendemail'], 'field' => 'sendemail', 'sort' => false, 'align' => 'center', 'width' => '40px');
    $header_arr[] = array('text' => $LANG_ACCESS['editusers'], 'field' => 'editusers', 'sort' => false, 'align' => 'center', 'width' => '40px');
    $header_arr[] = array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center', 'width' => '35px');

    $defsort_arr = array('field' => 'grp_name', 'direction' => 'asc');

    $form_url = $_CONF['site_admin_url'] . '/group.php';
    $form_url .= ($show_all_groups) ? '?chk_showall=1' : '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/group.php',
              'text' => $LANG28[38],'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/group.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'] . '/user.php',
              'text' => $LANG_ADMIN['admin_users']),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock($LANG_ACCESS['groupmanager'], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_ACCESS['newgroupmsg'],
        $_CONF['layout_url'] . '/images/icons/group.' . $_IMAGE_TYPE
    );

    $text_arr = array(
        'has_extras' => true,
        'form_url'   => $form_url
    );

    $filter = '<span style="padding-right:20px;">';

    $checked ='';
    if ($show_all_groups) {
        $checked = ' checked="checked"';
    }

    if (SEC_inGroup('Root')) {
        $grpFilter = '';
    } else {
        $thisUsersGroups = SEC_getUserGroups ();
        $grpFilter = 'AND (grp_id IN (' . implode (',', $thisUsersGroups) . '))';
    }

    if ($show_all_groups) {
        $filter .= '<label for="chk_showall"><input id="chk_showall" type="checkbox" name="chk_showall" value="1" onclick="this.form.submit();" checked="checked">&nbsp;';
        $query_arr = array(
            'table' => 'groups',
            'sql' => "SELECT * FROM `{$_TABLES['groups']}` WHERE 1=1",
            'query_fields' => array('grp_name', 'grp_descr'),
            'default_filter' => $grpFilter);
    } else {
        $filter .= '<label for="chk_showall">&nbsp;<input id="chk_showall" type="checkbox" name="chk_showall" value="1" onclick="this.form.submit();"' . $checked . '>&nbsp;';
        $query_arr = array(
            'table' => 'groups',
            'sql' => "SELECT * FROM `{$_TABLES['groups']}` WHERE (grp_gl_core = 0 OR grp_name IN ('All Users','Logged-in Users','Non-Logged-in Users'))",
            'query_fields' => array('grp_name', 'grp_descr'),
            'default_filter' => $grpFilter);
    }
    $filter .= $LANG28[48] . '</label></span>';

    $token = SEC_createToken();
    $form_arr = array(
        'bottom'    => '<input type="hidden" name="' . CSRF_TOKEN . '" value="'. $token .'"/>',
    );

    $retval .= ADMIN_list('groups', 'GROUP_getListField1', $header_arr,
                          $text_arr, $query_arr, $defsort_arr, $filter, $token, '', $form_arr);
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

/**
* Get list of users in a given group
*
* Effectively, this function is used twice: To get a list of all users currently
* in the given group and to get all list of all users NOT in that group.
*
* @param    int     $group_id   group id
* @param    boolean $allusers   true: return users not in the group
* @return   string              option list containing uids and user names
*
*/
function GROUP_selectUsers($group_id, $allusers = false)
{
    global $_TABLES, $_USER;

    $retval = '';

    $db = Database::getInstance();

    // Get a list of users in the Root Group and the selected group

    $sql  = "SELECT DISTINCT uid FROM `{$_TABLES['users']}` AS u
             LEFT JOIN `{$_TABLES['group_assignments']}` AS ga ON ga.ug_uid = uid
             WHERE u.uid > 1 AND (ga.ug_main_grp_id = 1 OR ga.ug_main_grp_id = ?)";

    $stmt = $db->conn->executeQuery(
                $sql,
                array($group_id),
                array(Database::INTEGER)
    );
    $filteredusers = array();
    while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
        $filteredusers[] = $A['uid'];
    }

    $params = array();
    $types  = array();

    $groups = GROUP_getGroupList ($group_id);

    $sql = "SELECT DISTINCT uid,username FROM `{$_TABLES['users']}` AS u
            LEFT JOIN `{$_TABLES['group_assignments']}` AS ga
            ON ga.ug_uid = u.uid
            WHERE u.uid > 1 AND ga.ug_main_grp_id ";

    if ($allusers) {
        $sql .= ' NOT ';
    }

    $sql .= "IN (?)";
    $params[] = $groups;
    $types[]  = Database::PARAM_INT_ARRAY;

    if ($allusers) {
        $sql .= " AND uid NOT IN (?) ";
        $params[] = $filteredusers;
        $types[] = Database::PARAM_INT_ARRAY;
    }

    $sql .= "ORDER BY username";

    $stmt = $db->conn->executeQuery(
                $sql,
                $params,
                $types
    );

    while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
        $retval .= '<option value="' . $row['uid'] . '">' . $row['username'] . '</option>';
    }

    return $retval;
}

/**
* Allow easy addition/removal of users to/from a group
*
* @param    int     $grp_id  Group ID
* @return   string          HTML form
*
*/
function GROUP_editUsers($grp_id)
{
    global $_CONF, $_TABLES, $_USER, $LANG_ACCESS, $LANG_ADMIN, $LANG28,
           $_IMAGE_TYPE;

    USES_lib_admin();

    $db = Database::getInstance();

    $retval = '';
    $thisUsersGroups = SEC_getUserGroups();

    $grp_name = $db->getItem($_TABLES['groups'], 'grp_name', array('grp_id' => $grp_id),array(Database::INTEGER));

    if ((!SEC_inGroup(1) && !empty($grp_id) && ($grp_id > 0) &&
                !in_array($grp_id, $thisUsersGroups) &&
                !SEC_groupIsRemoteUserAndHaveAccess($grp_id, $thisUsersGroups))
            || (($grp_name == 'All Users') ||
                ($grp_name == 'Non-Logged-in Users') ||
                ($grp_name == 'Logged-in Users'))) {
        if (!SEC_inGroup('Root') && ($grp_name == 'Root')) {
            $eMsg = $LANG_ACCESS['canteditroot'];
            Log::write('system',Log::ERROR,"User {$_USER['username']} tried to edit the Root group with insufficient privileges.");
        } else {
            $eMsg = $LANG_ACCESS['canteditgroup'];
        }
        $retval .= COM_showMessageText($eMsg,$LANG_ACCESS['usergroupadmin'],true);
        return $retval;
    }

    $showall = (isset($_REQUEST['chk_showall']) && ($_REQUEST['chk_showall'] == 1)) ? true : false;
    $form_url = $_CONF['site_admin_url'] . '/group.php';
    $form_url .= ($showall) ? '?chk_showall=1' : '';

    $menu_arr = array(
                    array('url'  => $form_url,
                          'text' => $LANG28[38]),
                    array('url' => $_CONF['site_admin_url'] . '/user.php',
                          'text' => $LANG_ADMIN['admin_users']),
                    array('url'  => $_CONF['site_admin_url'].'/index.php',
                          'text' => $LANG_ADMIN['admin_home'])
                );


    $retval .= COM_startBlock($LANG_ACCESS['usergroupadmin'] . ' - ' . ucwords($grp_name),
                        '', COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu($menu_arr, $LANG_ACCESS['editgroupmsg'],
                $_CONF['layout_url'] . '/images/icons/group.' . $_IMAGE_TYPE) . '<br />';

    $groupmembers = new Template($_CONF['path_layout'] . 'admin/group');
    $groupmembers->set_file(array('groupmembers'=>'groupmembers.thtml'));

    $groupmembers->set_var(array(
        'site_url'              => $_CONF['site_url'],
        'site_admin_url'        => $_CONF['site_admin_url'],
        'group_listing_url'     => $form_url,
        'layout_url'            => $_CONF['layout_url'],
        'phpself'               => $form_url,
        'lang_adminhome'        => $LANG_ACCESS['adminhome'],
        'lang_instructions'     => $LANG_ACCESS['editgroupmsg'],
        'LANG_sitemembers'      => $LANG_ACCESS['availmembers'],
        'LANG_grpmembers'       => $LANG_ACCESS['groupmembers'],
        'sitemembers'           => GROUP_selectUsers($grp_id,true),
        'group_list'            => GROUP_selectUsers($grp_id),
        'LANG_add'              => $LANG_ACCESS['add'],
        'LANG_remove'           => $LANG_ACCESS['remove'],
        'lang_save'             => $LANG_ADMIN['save'],
        'lang_cancel'           => $LANG_ADMIN['cancel'],
        'lang_grouplist'        => $LANG28[38],
        'show_all'              => $showall,
        'group_id'              => $grp_id,
        'gltoken_name'          => CSRF_TOKEN,
        'gltoken'               => SEC_createToken()
    ));
    $groupmembers->parse('output', 'groupmembers');
    $retval .= $groupmembers->finish($groupmembers->get_var('output'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

/**
* Save changes from the form to add/remove users to/from groups
*
* @param    int     $grp_id        id of the group being changed
* @param    string  $grp_members   list of group members
* @return   string                  HTML redirect
*
*/
function GROUP_saveUsers($grp_id, $grp_members)
{
    global $_CONF, $_TABLES;

    $retval = '';

    $db = Database::getInstance();

    $updateUsers = explode("|", $grp_members);
    $updateCount = count($updateUsers);
    if ($updateCount > 0) {

        // Retrieve all existing users in group so we can determine if changes
        // are needed
        $activeUsers = array();

        $stmt = $db->conn->executeQuery(
                    "SELECT ug_uid FROM `{$_TABLES['group_assignments']}` WHERE ug_main_grp_id = ?",
                    array($grp_id),
                    array(Database::INTEGER)
        );
        while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
            array_push($activeUsers, $A['ug_uid']);
        }
        if (count($activeUsers) > 0) {
            $deleteGroupUsers = array_diff($activeUsers, $updateUsers);
            $addGroupUsers = array_diff($updateUsers, $activeUsers);
            if (is_array($deleteGroupUsers) AND count($deleteGroupUsers) > 0) {
                foreach ($deleteGroupUsers as $uid) {
                    $uid = filter_var($uid, FILTER_SANITIZE_NUMBER_INT);

                    $db->conn->delete(
                        $_TABLES['group_assignments'],
                        array(
                            'ug_main_grp_id'=> $grp_id,
                            'ug_uid'        => $uid
                        ),
                        array(
                            Database::INTEGER,
                            Database::INTEGER
                        )
                    );
                }
            }
            if (is_array($addGroupUsers) AND count($addGroupUsers) > 0) {
                foreach ($addGroupUsers as $uid) {
                    $uid = filter_var($uid, FILTER_SANITIZE_NUMBER_INT);
                    $db->conn->insert(
                        $_TABLES['group_assignments'],
                        array(
                            'ug_main_grp_id' => $grp_id,
                            'ug_uid'         => $uid
                        ),
                        array(
                            Database::INTEGER,
                            Database::INTEGER
                        )
                    );
                }
            }
        } else {
            // No active users which should never occur as Root users
            // are always members
            for ($i = 0; $i < $updateCount; $i++) {
                $updateUsers[$i] = filter_var($updateUsers[$i], FILTER_SANITIZE_NUMBER_INT);
                $db->conn->insert(
                    $_TABLES['group_assignments'],
                    array(
                        'ug_main_grp_id' => $grp_id,
                        'ug_uid'         => $updateUsers[$i]
                    ),
                    array(
                        Database::INTEGER,
                        Database::INTEGER
                    )
                );
            }
        }
    }
    COM_setMessage(49);
    $url = $_CONF['site_admin_url'] . '/group.php';
    $url .= (isset($_REQUEST['chk_showall']) && ($_REQUEST['chk_showall'] == 1)) ? '?chk_showall=1' : '';
    $retval = COM_refresh($url);

    return $retval;
}

/**
* Delete a group
*
* @param    int     $grp_id     id of group to delete
* @return   string              HTML redirect
*
*/
function GROUP_delete($grp_id)
{
    global $_CONF, $_TABLES, $_USER;

    $db = Database::getInstance();

    $grp_id = (int) $grp_id;
    $is_core = (int) $db->getItem($_TABLES['groups'], 'grp_gl_core', array('grp_id' => $grp_id),array(Database::INTEGER));
    if ($is_core == 1 || !SEC_hasRights('group.delete')) {
        Log::write('system',Log::ERROR,"User {$_USER['username']} tried to delete a core group with insufficient privileges.");
        return COM_refresh ($_CONF['site_admin_url'] . '/group.php');
    }
    $db->conn->delete($_TABLES['access'], array('acc_grp_id' => $grp_id),array(Database::INTEGER));
    $db->conn->delete($_TABLES['group_assignments'], array('ug_grp_id' => $grp_id),array(Database::INTEGER));
    $db->conn->delete($_TABLES['group_assignments'], array('ug_main_grp_id' => $grp_id),array(Database::INTEGER));
    $db->conn->delete($_TABLES['groups'], array('grp_id' => $grp_id),array(Database::INTEGER));

    PLG_groupChanged ($grp_id, 'delete');
    Cache::getInstance()->deleteItemsByTags(array('menu', 'groups', 'group_' . $grp_id));

    COM_setMessage(50);
    $url = $_CONF['site_admin_url'] . '/group.php';
    $url .= (isset($_REQUEST['chk_showall']) && ($_REQUEST['chk_showall'] == 1)) ? '?chk_showall=1' : '';
    return COM_refresh($url);;
}

// MAIN ========================================================================

$action = '';

$expected = array('edit','save','delete','savegroup','editusers','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
    }
}

if (isset($_POST['cancel'])) {
    echo COM_refresh($_CONF['site_admin_url'].'/group.php');
}

$grp_id = 0;
if (isset($_POST['grp_id'])) {
    $grp_id = COM_applyFilter($_POST['grp_id'], true);
} elseif (isset($_GET['grp_id'])) {
    $grp_id = COM_applyFilter($_GET['grp_id'], true);
}

switch ($action) {

    case 'edit':
        $display .= COM_siteHeader('menu', $LANG_ACCESS['groupeditor']);
        $display .= GROUP_edit($grp_id);
        $display .= COM_siteFooter();
        break;

    case 'save':
        if (SEC_checkToken()) {
            $grp_gl_core = COM_applyFilter($_POST['grp_gl_core'], true);
            $grp_default = (isset($_POST['chk_grpdefault'])) ? 1 : 0;
            $grp_applydefault = (isset($_POST['chk_applydefault'])) ? 1 : 0;
            $chk_grpadmin = (isset($_POST['chk_grpadmin'])) ? COM_applyFilter($_POST['chk_grpadmin']) : '';
            $features = array();
            $features = (isset($_POST['features']) ? $_POST['features'] : array());
            $groups = array();
            $groups = (isset($_POST['groups']) ? $_POST['groups'] : array());

            $display .= GROUP_save($grp_id, COM_applyFilter($_POST['grp_name']),
                                  $_POST['grp_descr'], $chk_grpadmin, $grp_gl_core,
                                  $grp_default, $grp_applydefault, $features, $groups);
        } else {
            Log::write('system',Log::ERROR,"User {$_USER['username']} tried to edit group $grp_id and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
            exit;
        }
        SESS_unSet('glfusion.user_groups.'.$_USER['uid']);
        break;

    case 'delete':
        if (!isset ($grp_id) || empty ($grp_id) || ($grp_id == 0)) {
            Log::write('system',Log::ERROR,'Attempted to delete group, grp_id empty or null, value =' . $grp_id);
            $display .= COM_refresh($_CONF['site_admin_url'] . '/group.php');
        } elseif (SEC_checkToken()) {
            $display .= GROUP_delete($grp_id);
        } else {
            Log::write('system',Log::ERROR,"User {$_USER['username']} tried to delete group $grp_id and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'savegroup':
        if (SEC_checkToken()) {
            $grp_members = $_POST['groupmembers'];
            $display .= GROUP_saveUsers($grp_id, $grp_members);
        } else {
            Log::write('system',Log::ERROR,"User {$_USER['username']} tried to manage the users in group $grp_id and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'editusers':
        $display .= COM_siteHeader('menu', $LANG_ACCESS['usergroupadmin']);
        $display .= GROUP_editUsers($grp_id);
        $display .= COM_siteFooter();
        break;

    default:
        $showall = false;
        if (isset($_POST['q'])) {
            if (isset($_POST['chk_showall']) && ($_POST['chk_showall'] == 1)) {
                $showall = true;
                $_REQUEST['chk_showall'] = '';
            }
        } elseif (isset($_REQUEST['chk_showall']) && ($_REQUEST['chk_showall'] == 1)) {
            $showall = true;
        }
        $display .= COM_siteHeader('menu', $LANG28[38]);
        $display .= COM_showMessageFromParameter();
        $display .= GROUP_list($showall);
        $display .= COM_siteFooter();
        break;
}
echo $display;

?>
