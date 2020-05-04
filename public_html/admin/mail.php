<?php
/**
* glFusion CMS
*
* glFusion mail administration page.
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2010-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*   Mark A. Howard  mark AT usable-web DOT com
*
*  Based on prior work Copyright (C) 2001-2008 by the following authors:
*  Authors: Tony Bibbs        - tony AT tonybibbs DOT com
*           Dirk Haun         - dirk AT haun-online DOT de
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Database\Database;
use \glFusion\Log\Log;

$display = '';

// Make sure user has rights to access this page
if (!SEC_hasrights ('user.mail')) {
    Log::logAccessViolation('Mail Administration');
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[39],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

/**
* Shows the form the admin uses to send glFusion members a message. Now you
* can email a user or an entire group depending upon whether uid or grp_id is
* set.  if both arguments are >0, the group send function takes precedence
*
* @return   string      HTML for the email form
*
*/
function MAIL_displayForm( $uid=0, $grp_id=0, $from='', $replyto='', $subject='', $message='' )
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG31, $LANG03, $LANG_ADMIN;

    USES_lib_admin();

    $retval = '';

    $db = Database::getInstance();

    if ( isset($_POST['postmode'] ) ) {
        $postmode = COM_applyFilter($_POST['postmode']);
        if ( $postmode != 'html' || $postmode != 'plaintext' ) {
            $postmode = $_CONF['postmode'];
        }
    } else {
        $postmode = $_CONF['postmode'];
    }

    $mail_templates = new Template ($_CONF['path_layout'] . 'admin/mail');
    $mail_templates->set_file('form','mailform.thtml');

    if ($postmode == 'html') {
        $mail_templates->set_var ('show_htmleditor', true);
    } else {
        $mail_templates->unset_var ('show_htmleditor');
    }
    $mail_templates->set_var('postmode',$postmode);
    $mail_templates->set_var('lang_postmode', $LANG03[2]);
    $mail_templates->set_var('postmode_options', COM_optionList($_TABLES['postmodes'],'code,name',$postmode));

    $mail_templates->set_var ('startblock_email', COM_startBlock ($LANG31[1],
            '', COM_getBlockTemplate ('_admin_block', 'header')));
    $mail_templates->set_var ('php_self', $_CONF['site_admin_url']
                                          . '/mail.php');

    $usermode = ($uid > 0 && $grp_id == 0) ? true : false;
    $send_to_group = ($usermode) ? '' : '1';
    $mail_templates->set_var ('send_to_group', $send_to_group);

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/mail.php',
              'text' => $LANG01[105],'active' => true),
        array('url' => $_CONF['site_admin_url'] . '/user.php',
              'text' => $LANG_ADMIN['admin_users']),
        array('url' => $_CONF['site_admin_url'] . '/group.php',
              'text' => $LANG_ADMIN['admin_groups']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );
    $instructions = ($usermode) ? $LANG31[28] : $LANG31[19];
    $icon = $_CONF['layout_url'] . '/images/icons/mail.png';
    $admin_menu = ADMIN_createMenu( $menu_arr, $instructions, $icon);
    $mail_templates->set_var ('admin_menu', $admin_menu);

    if ($usermode) {
        // we're sending e-Mail to a specific user
        $mail_templates->set_var ('lang_instructions', $LANG31[28]);
        $mail_templates->set_var ('lang_to', $LANG31[18]);
        $to_user = '';
        $lang_warning = $LANG31[29];
        $warning = '';

        // get the user data, and check the privacy settings
        $stmt = $db->conn->executeQuery(
                    "SELECT u.username, u.fullname, u.email, up.emailfromadmin
                      FROM `{$_TABLES['users']}` AS u LEFT JOIN `{$_TABLES['userprefs']}` AS up ON u.uid=up.uid
                      WHERE u.uid = ?",
                    array($uid),
                    array(Database::INTEGER)
        );
        if ($stmt !== false && $stmt !== null) {
            $record = $stmt->fetch(Database::ASSOCIATIVE);
            $username = ($_CONF['show_fullname']) ? $record['fullname'] : $record['username'];
            $to_user = $username . ' (' . $record['email'] . ')';
            $warning = ($record['emailfromadmin'] == 1) ? '' : $LANG31[30];
        }
        $mail_templates->set_var(array(
            'to_user'       => $to_user,
            'to_uid'        => $uid,
            'lang_warning'  => $lang_warning,
            'warning'       => $warning
        ));
    } else {
        // we're sending e-Mail to a group of users
        $mail_templates->set_var ('lang_instructions', $LANG31[19]);
        $mail_templates->set_var ('lang_to', $LANG31[27]);
        $mail_templates->set_var ('lang_selectgroup', $LANG31[25]);
        // build group options select, allow for possibility grp_id has been supplied
        $group_options = '';

        $stmt = $db->conn->query("SELECT grp_id, grp_name FROM `{$_TABLES['groups']}` WHERE grp_name <> 'All Users'");
        while ($A = $stmt->fetch(Database::ASSOCIATIVE)) {
            $groups[$A['grp_id']] = ucwords ($A['grp_name']);
        }
        asort ($groups);

        foreach ($groups as $groupID => $groupName) {
            if (SEC_inGroup('Root') || (SEC_inGroup($groupName) && ($groupName <> 'Logged-in Users') && ($groupName <> 'Mail Admin'))) {
                $group_options .= '<option value="' . $groupID . '"';
                $group_options .= ($groupID == $grp_id) ? ' selected="selected"' : '';
                $group_options .= '>' . $groupName . '</option>';
            }
        }
        $mail_templates->set_var ('group_options', $group_options);
    }

    $mail_templates->set_var ('lang_from', $LANG31[2]);
    $frm = (empty($from)) ? $_CONF['site_name'] : $from;
    $mail_templates->set_var ('site_name', $frm);

    $mail_templates->set_var ('lang_replyto', $LANG31[3]);
    $rto = (empty($replyto)) ? $_CONF['site_mail'] : $replyto;
    $mail_templates->set_var ('site_mail', $rto);

    $mail_templates->set_var ('lang_subject', $LANG31[4]);
    $mail_templates->set_var ('subject', $subject);

    $mail_templates->set_var ('lang_body', $LANG31[5]);
    $mail_templates->set_var ('message_text', $message);
    $mail_templates->set_var ('message_html', $message);
    $mail_templates->set_var ('lang_sendto', $LANG31[6]);
    $mail_templates->set_var ('lang_allusers', $LANG31[7]);
    $mail_templates->set_var ('lang_admin', $LANG31[8]);
    $mail_templates->set_var ('lang_options', $LANG31[9]);
    $mail_templates->set_var ('lang_HTML', $LANG31[10]);
    $mail_templates->set_var ('lang_urgent', $LANG31[11]);
    $mail_templates->set_var ('lang_ignoreusersettings', $LANG31[14]);
    $mail_templates->set_var ('lang_send', $LANG31[12]);
    $mail_templates->set_var ('end_block', COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer')));
    $mail_templates->set_var('gltoken_name', CSRF_TOKEN);
    $mail_templates->set_var('gltoken', SEC_createToken());
    PLG_templateSetVars('contact',$mail_templates);
    $mail_templates->parse ('output', 'form');
    $retval = $mail_templates->finish ($mail_templates->get_var ('output'));

    SEC_setCookie ($_CONF['cookie_name'].'adveditor', SEC_createTokenGeneral('advancededitor'),
                   time() + 1200, $_CONF['cookie_path'],
                   $_CONF['cookiedomain'], $_CONF['cookiesecure'],false);

    return $retval;
}

/**
* This function actually sends the messages to the specified group
*
* @param    array   $vars   Same as $_POST, holds all the email info
* @return   string          HTML with success or error message
*
*/
function MAIL_sendMessages($vars)
{
    global $_CONF, $_TABLES, $LANG31;

    USES_lib_user();

    $retval = '';

    $db = Database::getInstance();

    $html = 0;
    $message = $vars['message'];
    if ( $vars['postmode'] == 'html' ) {
        $html = true;
    }

    if ( !isset($vars['to_uid'])) $vars['to_uid'] = 0;
    if ( !isset($vars['to_group'])) $vars['to_group'] = 0;

    $usermode = ((int) $vars['to_uid'] > 0 && (int) $vars['to_group'] == 0) ? true : false;

    if (empty ($vars['fra']) OR empty ($vars['fraepost']) OR
            empty ($vars['subject']) OR empty ($message) OR
            ( empty ($vars['to_group']) && empty($vars['to_uid']) )) {

        $retval .= COM_showMessageText($LANG31[26],$LANG31[1],true,'error');

        $msg = htmlspecialchars($vars['message'],ENT_COMPAT,COM_getEncodingt());
        $subject = htmlspecialchars($vars['subject'],ENT_COMPAT,COM_getEncodingt());
        $fra     = htmlspecialchars($vars['fra'],ENT_COMPAT,COM_getEncodingt());
        $fraepost = htmlspecialchars($vars['fraepost'],ENT_COMPAT,COM_getEncodingt());
        $retval .= MAIL_displayForm( $vars['to_uid'], $vars['to_group'], $fra, $fraepost, $subject, $msg );
        return $retval;
    }

    // Urgent message!
    if (isset ($vars['priority'])) {
        $priority = 1;
    } else {
        $priority = 0;
    }
    $toUsers = array();
    if ($usermode) {

        $record = $db->conn->fetchAssoc(
                    "SELECT email,username FROM `{$_TABLES['users']}`
                     WHERE uid=?",
                    array( (int) $vars['to_uid']),
                    array(Database::INTEGER)
        );
        if ($record !== false && $record !== null) {
            $toUsers[] = COM_formatEmailAddress ($record['username'],$record['email']);
            $tmp = COM_formatEmailAddress ($record['username'],$record['email']);
            $toUsers[] = array('email' => $tmp[0], 'name' => $tmp[1]);
        }
    } else {
        $groupList = implode (',', USER_getChildGroups((int) COM_applyFilter($vars['to_group'],true)));


        $groupArray = USER_getChildGroups((int) COM_applyFilter($vars['to_group'],true));

        // and now mail it
        if (isset ($vars['overstyr'])) {


            $sql = "SELECT DISTINCT username, fullname, email
                    FROM `{$_TABLES['users']}` AS u,`{$_TABLES['group_assignments']}` AS ga
                    WHERE uid > 1 AND u.status = 3
                      AND ((u.email is not null) AND (u.email != ''))
                      AND u.uid = ga.ug_uid
                      AND ga.ug_main_grp_id IN (?)";

            $stmt = $db->conn->prepare($sql);
            $stmt->bindValue(1,$groupList,Database::PARAM_INT_ARRAY);
        } else {

            $sql = "SELECT DISTINCT username, fullname, email, emailfromadmin
                    FROM `{$_TABLES['users']}` AS u,`{$_TABLES['userprefs']}` AS up,`{$_TABLES['group_assignments']}` AS ga
                    WHERE u.uid > 1 AND u.status = 3 AND ((u.email is not null) and (u.email != ''))
                      AND u.uid = up.uid AND up.emailfromadmin = 1
                      AND ug_uid = u.uid AND ga.ug_main_grp_id IN (?)";
            $stmt = $db->conn->prepare($sql);
            $stmt->bindValue(1,$groupList,Database::PARAM_INT_ARRAY);
        }

        $stmt->execute();
        while ($record = $stmt->fetch(Database::ASSOCIATIVE)) {
            if (empty ($record['fullname'])) {
                $toUsers[] = array('email' => $record['email'], 'name' => $record['username']);
            } else {
                $toUsers[] = array('email' => $record['email'], 'name' => $record['fullname']);
            }
        }
    }

    $subject = $vars['subject'];

    // Loop through and send the messages!
    $successes = array ();
    $failures = array ();

    $msgData['subject'] = htmlspecialchars($subject);
    if ($html) {
        $msgData['htmlmessage'] = $message;
    } else {
        $msgData['textmessage'] = $message;
    }
    $msgData['to'] = $toUsers;

    $msgData['from']['email'] = $vars['fraepost'];
    $msgData['from']['name'] = $vars['fra'];

    $ret = COM_emailNotification( $msgData );

    $retval .= COM_startBlock ($LANG31[1]);

    $successcount = count ($toUsers);
    $retval .= sprintf ($LANG31[20], $successcount,$_CONF['site_admin_url'],$_CONF['site_admin_url']);
    $retval .= COM_endBlock ();

    return $retval;
}

// MAIN ========================================================================

$action = '';
$expected = array('mail');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
    }
}

$grp_id = 0;
if (isset($_POST['grp_id'])) {
    $grp_id = COM_applyFilter($_POST['grp_id'], true);
} elseif (isset($_GET['grp_id'])) {
    $grp_id = COM_applyFilter($_GET['grp_id'], true);
}

$uid = 0;
if (isset($_POST['uid'])) {
    $uid = COM_applyFilter($_POST['uid'], true);
} elseif (isset($_GET['uid'])) {
    $uid = COM_applyFilter($_GET['uid'], true);
}

$from = '';
if (isset($_POST['from'])) {
    $from = COM_applyFilter($_POST['from']);
} elseif (isset($_GET['from'])) {
    $from = COM_applyFilter($_GET['from']);
}

$replyto = '';
if (isset($_POST['replyto'])) {
    $replyto = COM_applyFilter($_POST['replyto']);
} elseif (isset($_GET['replyto'])) {
    $replyto = COM_applyFilter($_GET['replyto']);
}

$subject = '';
if (isset($_POST['subject'])) {
    $subject = COM_applyFilter($_POST['subject']);
} elseif (isset($_GET['subject'])) {
    $subject = COM_applyFilter($_GET['subject']);
}

$display .= COM_siteHeader ('menu', $LANG31[1]);

switch ($action) {

    case 'mail':
        $display .= Mail_sendMessages($_POST);
        break;

    default:
        $display .= MAIL_displayForm( $uid, $grp_id, $from, $replyto, $subject, '' );
        break;

}

$display .= COM_siteFooter ();

echo $display;

?>
