<?php
/**
* glFusion CMS - Polls Plugin
*
* Administration Page
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2015-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs        tony@tonybibbs.com
*  Mark Limburg      mlimburg@users.sourceforge.net
*  Jason Whittenburg jwhitten@securitygeeks.com
*  Dirk Haun         dirk@haun-online.de
*
*/

// Set this to true if you want to log debug messages to error.log
$_POLL_VERBOSE = false;

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

use \glFusion\Log\Log;
use \glFusion\FieldList;

USES_lib_admin();

$display = '';

if (!SEC_hasRights ('polls.edit')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_startBlock ($MESSAGE[30], '',
                                COM_getBlockTemplate ('_msg_block', 'header'));
    $display .= $MESSAGE[36];
    $display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
    $display .= COM_siteFooter ();
    Log::write('system',Log::WARNING, 'User ' . $_USER['username'] . 'attempted to access the poll administration page');
    echo $display;
    exit;
}

/**
* Shows poll editor
*
* Diplays the poll editor form
*
* @param    string  $pid    ID of poll to edit
* @return   string          HTML for poll editor form
*
*/
function POLLS_edit($pid = '')
{
    global $_CONF, $_PO_CONF, $_GROUPS, $_TABLES, $_USER, $LANG25, $LANG_ACCESS,
           $LANG_ADMIN, $MESSAGE, $LANG_POLLS;

    $retval = '';

    if (!empty ($pid)) {
        $lang_create_or_edit = $LANG_ADMIN['edit'];
    } else {
        $lang_create_or_edit = $LANG_ADMIN['create_new'];
    }

    // writing the menu on top

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/polls/index.php',
              'text' => $LANG_ADMIN['list_all']),
        array('url' => $_CONF['site_admin_url'] . '/plugins/polls/index.php?edit=x',
              'text' => $lang_create_or_edit,'active'=>true),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home']));

    $retval .= COM_startBlock ($LANG25[5], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG_POLLS['editinstructions'],
        plugin_geticon_polls()
    );

    $poll_templates = new Template ($_CONF['path']
                                    . 'plugins/polls/templates/admin/');
    $poll_templates->set_file (array ('editor' => 'polleditor.thtml',
                                      'question' => 'pollquestions.thtml',
                                      'answer' => 'pollansweroption.thtml'));
    if (!empty ($pid)) {
        $topic = DB_query("SELECT * FROM {$_TABLES['polltopics']} WHERE pid='".DB_escapeString($pid)."'");
        $T = DB_fetchArray($topic);

        // Get permissions for poll
        $access = SEC_hasAccess($T['owner_id'],$T['group_id'],$T['perm_owner'],$T['perm_group'],$T['perm_members'],$T['perm_anon']);
        if ($access == 0 OR $access == 2) {
            // User doesn't have access...bail
            $retval .= COM_startBlock ($LANG25[21], '',
                               COM_getBlockTemplate ('_msg_block', 'header'));
            $retval .= $LANG25[22];
            $retval .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
            COM_accessLog("User {$_USER['username']} tried to illegally submit or edit poll $pid.");
            return $retval;
        }
    }

    if (!empty ($pid) AND ($access == 3) AND !empty ($T['owner_id'])) {
        $delbutton = '<input type="submit" value="' . $LANG_ADMIN['delete']
                   . '" name="delete"%s>';
        $jsconfirm = ' onclick="return confirm(\'' . $MESSAGE[76] . '\');"';
        $poll_templates->set_var ('delete_option',
                                  sprintf ($delbutton, $jsconfirm));
        $poll_templates->set_var ('delete_option_no_confirmation',
                                  sprintf ($delbutton, ''));
        $poll_templates->set_var(array(
                        'delete_button' => true,
                        'lang_delete'   => $LANG_ADMIN['delete'],
                        'lang_delete_confirm' => $MESSAGE[76]
        ));
    } else {
        $T['pid'] = COM_makeSid ();
        $T['topic'] = '';
        $T['description'] = '';
        $T['voters'] = 0;
        $T['display'] = 1;
        $T['is_open'] = 1;
        $T['login_required'] = 0;
        $T['hideresults'] = 0;
        $T['owner_id'] = $_USER['uid'];
        if (isset ($_GROUPS['Polls Admin'])) {
            $T['group_id'] = $_GROUPS['Polls Admin'];
        } else {
            $T['group_id'] = SEC_getFeatureGroup ('polls.edit');
        }
        SEC_setDefaultPermissions ($T, $_PO_CONF['default_permissions']);
        $T['statuscode'] = 0;
        $T['commentcode'] = $_CONF['comment_code'];
        $access = 3;
    }

    $poll_templates->set_var('lang_pollid', $LANG25[6]);
    $poll_templates->set_var('poll_id', $T['pid']);
    $poll_templates->set_var('lang_donotusespaces', $LANG25[7]);
    $poll_templates->set_var('lang_topic', $LANG25[9]);
    $poll_templates->set_var('poll_topic', htmlspecialchars ($T['topic']));
    $poll_templates->set_var('poll_description', htmlspecialchars($T['description']));
    $poll_templates->set_var('lang_mode', $LANG25[1]);

    $poll_templates->set_var('lang_description', $LANG_POLLS['description']);

    $poll_templates->set_var('status_options', COM_optionList ($_TABLES['statuscodes'], 'code,name', $T['statuscode']));
    $poll_templates->set_var('comment_options', COM_optionList($_TABLES['commentcodes'],'code,name',$T['commentcode']));

    $poll_templates->set_var('lang_appearsonhomepage', $LANG25[8]);
    $poll_templates->set_var('lang_openforvoting', $LANG25[33]);
    $poll_templates->set_var('lang_login_required', $LANG25[43]);
    $poll_templates->set_var('lang_hideresults', $LANG25[37]);
    $poll_templates->set_var('poll_hideresults_explain', $LANG25[38]);
    $poll_templates->set_var('poll_topic_info', $LANG25[39]);

    if ($T['display'] == 1) {
        $poll_templates->set_var('poll_display', 'checked="checked"');
    }

    if ($T['is_open'] == 1) {
        $poll_templates->set_var('poll_open', 'checked="checked"');
    }
    if ( $T['login_required'] == 1 ) {
        $poll_templates->set_var('poll_login_required', 'checked="checked"');
    }
    if ($T['hideresults'] == 1) {
        $poll_templates->set_var('poll_hideresults', 'checked="checked"');
    }
    // user access info
    $poll_templates->set_var('lang_accessrights', $LANG_ACCESS['accessrights']);
    $poll_templates->set_var('lang_owner', $LANG_ACCESS['owner']);
    $ownername = COM_getDisplayName ($T['owner_id']);
    $poll_templates->set_var('owner_username', DB_getItem($_TABLES['users'],
                             'username', "uid = {$T['owner_id']}"));
    $poll_templates->set_var('owner_name', $ownername);
    $poll_templates->set_var('owner', $ownername);
    $poll_templates->set_var('owner_id', $T['owner_id']);
    $poll_templates->set_var('lang_group', $LANG_ACCESS['group']);
    $poll_templates->set_var('group_dropdown',
                             SEC_getGroupDropdown ($T['group_id'], $access));
    $poll_templates->set_var('lang_permissions', $LANG_ACCESS['permissions']);
    $poll_templates->set_var('lang_permissionskey', $LANG_ACCESS['permissionskey']);
    $poll_templates->set_var('permissions_editor', SEC_getPermissionsHTML($T['perm_owner'],$T['perm_group'],$T['perm_members'],$T['perm_anon']));
    $poll_templates->set_var('lang_permissions_msg', $LANG_ACCESS['permmsg']);
    $poll_templates->set_var('lang_answersvotes', $LANG25[10]);
    $poll_templates->set_var('lang_save', $LANG_ADMIN['save']);
    $poll_templates->set_var('lang_cancel', $LANG_ADMIN['cancel']);

    // repeat for several questions

    $question_sql = "SELECT question,qid "
        . "FROM {$_TABLES['pollquestions']} WHERE pid='$pid' ORDER BY qid;";
    $questions = DB_query($question_sql);
    $navbar = new navbar;

    $poll_templates->set_block('editor','questiontab','qt');

    for ($j=0; $j<$_PO_CONF['maxquestions']; $j++) {
        $display_id = $j+1;
        if ($j > 0) {
            $poll_templates->set_var('style', 'style="display:none;"');
        } else {
            $poll_templates->set_var('style', '');
        }

        $poll_templates->set_var('question_tab', $LANG25[31] . " $display_id");

        $navbar->add_menuitem(
            $LANG25[31] . " $display_id",
            "showhidePollsEditorDiv(\"$j\",$j,{$_PO_CONF['maxquestions']});return false;",
            true
        );
        $Q = DB_fetchArray ($questions);
if (!is_array($Q)) {
    $Q = array();
$Q['question'] = '';
$Q['answer'] = '';

}
        $poll_templates->set_var('question_text', $Q['question']);
        $poll_templates->set_var('question_id', $j);
        $poll_templates->set_var('lang_question', $LANG25[31] . " $display_id");
        $poll_templates->set_var('lang_saveaddnew', $LANG25[32]);

        if ( $Q['question'] != '' ) {
            $poll_templates->set_var('hasdata',true);
        } else {
            $poll_templates->unset_var('hasdata');
        }
        $poll_templates->parse('qt','questiontab',true);

        // answers
        $answer_sql = "SELECT answer,aid,votes,remark "
            . "FROM {$_TABLES['pollanswers']} WHERE qid='$j' AND pid='$pid' ORDER BY aid";
        $answers = DB_query($answer_sql);

        for ($i=0; $i<$_PO_CONF['maxanswers']; $i++) {
            if (isset ($answers)) {
                $A = DB_fetchArray ($answers);
if (!is_array($A)) {
    $A = array();
    $A['answer'] = '';
    $A['remark'] = '';
    $A['votes'] = 0;
}                
                $poll_templates->set_var ('answer_text',
                                          htmlspecialchars ($A['answer']));
                $poll_templates->set_var ('answer_votes', $A['votes']);
                $poll_templates->set_var ('remark_text',
                                        htmlspecialchars($A['remark']));

            } else {
                $poll_templates->set_var ('answer_text', '');
                $poll_templates->set_var ('answer_votes', '');
                $poll_templates->set_var ('remark_text', '');

            }
            $poll_templates->parse ('answer_option', 'answer', true);
        }
        $poll_templates->parse ('question_list', 'question', true);
        $poll_templates->clear_var ('answer_option');
    }
    $navbar->set_selected($LANG25[31] . " 1");
    $poll_templates->set_var ('navbar', $navbar->generate());
    $poll_templates->set_var('sectoken_name', CSRF_TOKEN);
    $poll_templates->set_var('gltoken_name', CSRF_TOKEN);
    $token = SEC_createToken();
    $poll_templates->set_var('sectoken', $token);
    $poll_templates->set_var('gltoken', $token);

    $poll_templates->parse('output','editor');
    $retval .= $poll_templates->finish($poll_templates->get_var('output'));

    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    return $retval;
}


/**
* Saves a poll
*
* Saves a poll topic and potential answers to the database
*
* @param    string  $pid            Poll topic ID
* @param    string  $old_pid        Previous poll topic ID
* @param    array   $Q              Array of poll questions
* @param    string  $mainpage       Checkbox: poll appears on homepage
* @param    string  $topic          The text for the topic
* @param    int     $statuscode     (unused)
* @param    string  $open           Checkbox: poll open for voting
* @param    string  $login_required Checkbox: poll required login to vote
* @param    string  $hideresults    Checkbox: hide results until closed
* @param    int     $commentcode    Indicates if users can comment on poll
* @param    array   $A              Array of possible answers
* @param    array   $V              Array of vote per each answer
* @param    array   $R              Array of remark per each answer
* @param    int     $owner_id       ID of poll owner
* @param    int     $group_id       ID of group poll belongs to
* @param    int     $perm_owner     Permissions the owner has on poll
* @param    int     $perm_grup      Permissions the group has on poll
* @param    int     $perm_members   Permissions logged in members have on poll
* @param    int     $perm_anon      Permissions anonymous users have on poll
* @return   string                  HTML redirect or error message
*
*/
function POLLS_save($pid, $old_pid, $Q, $mainpage, $topic, $description, $statuscode, $open, $login_required, $hideresults,
                  $commentcode, $A, $V, $R, $owner_id, $group_id, $perm_owner,
                  $perm_group, $perm_members, $perm_anon)

{
    global $_CONF, $_TABLES, $_USER, $LANG21, $LANG25, $MESSAGE, $_POLL_VERBOSE,
           $_PO_CONF;

    $retval = '';

    // Convert array values to numeric permission values
    list($perm_owner,$perm_group,$perm_members,$perm_anon) = SEC_getPermissionValues($perm_owner,$perm_group,$perm_members,$perm_anon);

    $pid = COM_sanitizeID($pid);
//    $topic = $topic;

    $old_pid = COM_sanitizeID($old_pid);
    if (empty($pid)) {
        if (empty($old_pid)) {
            $pid = COM_makeSid();
        } else {
            $pid = $old_pid;
        }
    }

    // check if any question was entered
    if (empty($topic) || (count($Q) == 0) || (strlen($Q[0]) == 0) || (strlen($A[0][0]) == 0)) {
//        $retval .= COM_siteHeader ('menu', $LANG25[5]);
        $retval .= COM_startBlock ($LANG21[32], '',COM_getBlockTemplate ('_msg_block', 'header'));
        $retval .= $LANG25[2];
        $retval .= COM_endBlock(COM_getBlockTemplate ('_msg_block', 'footer'));
//        $retval .= COM_siteFooter ();
        return $retval;
    }
    // check for poll id change
    if (!empty($old_pid) && ($pid != $old_pid)) {
        // check if new pid is already in use
        if (DB_count($_TABLES['polltopics'], 'pid', $pid) > 0) {
            // TBD: abort, display editor with all content intact again
            $pid = $old_pid; // for now ...
        }
    }

    // start processing the poll topic
    if ($_POLL_VERBOSE) {
        Log::write('system',Log::DEBUG, '**** Inside POLL_save() ****');
    }
    $pid = str_replace (' ', '', $pid); // strip spaces from poll id
    $access = 0;
    if (DB_count ($_TABLES['polltopics'], 'pid', $pid) > 0) {
        $result = DB_query ("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['polltopics']} WHERE pid = '{$pid}'");
        $P = DB_fetchArray ($result);
        $access = SEC_hasAccess ($P['owner_id'], $P['group_id'],
                $P['perm_owner'], $P['perm_group'], $P['perm_members'],
                $P['perm_anon']);
    } else {
        $access = SEC_hasAccess ($owner_id, $group_id, $perm_owner,
                                 $perm_group, $perm_members, $perm_anon);
    }
    if (($access < 3) || !SEC_inGroup ($group_id)) {
        $display .= COM_siteHeader ('menu', $MESSAGE[30]);
        $display .= COM_startBlock ($MESSAGE[30], '',COM_getBlockTemplate ('_msg_block', 'header'));
        $display .= $MESSAGE[31];
        $display .= COM_endBlock ();
        $display .= COM_siteFooter (COM_getBlockTemplate ('_msg_block','footer'));
        COM_accessLog("User {$_USER['username']} tried to submit or edit poll $pid.");
        echo $display;
        exit;
    }

    if (empty ($voters)) {
        $voters = 0;
    }

    if ($_POLL_VERBOSE) {
        Log::write('system',Log::DEBUG, 'Polls Admin: owner permissions: '. $perm_owner);
        Log::write('system',Log::DEBUG, 'Polls Admin: group permissions: '. $perm_group);
        Log::write('system',Log::DEBUG, 'Polls Admin: member permissions: '. $perm_members);
        Log::write('system',Log::DEBUG, 'Polls Admin: anonymous permissions: '. $perm_anon);
    }
    // we delete everything and re-create it with the input from the form
    $del_pid = $pid;
    if (!empty($old_pid) && ($pid != $old_pid)) {
        $del_pid = $old_pid; // delete by old pid, create using new pid below
    }
    DB_delete($_TABLES['polltopics'], 'pid', $del_pid);
    DB_delete($_TABLES['pollanswers'], 'pid', $del_pid);
    DB_delete($_TABLES['pollquestions'], 'pid', $del_pid);

    $topic = DB_escapeString ($topic);

    $filter = sanitizer::getInstance();
    $description = $filter->filterText($description);
    $description = DB_escapeString($description);

    $k = 0; // set up a counter to make sure we do assign a straight line of question id's
    $v = 0; // re-count votes sine they might have been changed
    // first dimension of array are the questions
    $num_questions = count($Q);
    $total_questions = 0;
    for ($i = 0; $i < $num_questions; $i++) {
        $Q[$i] = $Q[$i];
        if (strlen($Q[$i]) > 0) { // only insert questions that exist
            $total_questions++;
            $Q[$i] = DB_escapeString($Q[$i]);
            DB_save($_TABLES['pollquestions'], 'qid, pid, question',
                                               "'$k', '$pid', '$Q[$i]'");
            // within the questions, we have another dimensions with answers,
            // votes and remarks
            $num_answers = count($A[$i]);
            for ($j = 0; $j < $num_answers; $j++) {
                $A[$i][$j] = $A[$i][$j];
                if (strlen($A[$i][$j]) > 0) { // only insert answers etc that exist
                    if (!is_numeric($V[$i][$j])) {
                        $V[$i][$j] = "0";
                    }
                    $A[$i][$j] = DB_escapeString ($A[$i][$j]);
                    $R[$i][$j] = DB_escapeString ($R[$i][$j]);
                    $sql = "INSERT INTO {$_TABLES['pollanswers']} (pid, qid, aid, answer, votes, remark) VALUES "
                        . "('$pid', '$k', " . ($j+1) . ", '{$A[$i][$j]}', {$V[$i][$j]}, '{$R[$i][$j]}');";
                    DB_query($sql);
                    $v = $v + $V[$i][$j];
                }
            }
            $k++;
        }
    }
    if ( $total_questions > 0 ) {
        $numVoters = (int) ($v / $total_questions);
    } else {
        $numVoters = $v;
    }

    // save topics after the questions so we can include question count into table
    $sql = "'$pid','$topic','$description',$numVoters, $k, '" . date ('Y-m-d H:i:s');

    if ($mainpage == 'on') {
        $sql .= "',1";
    } else {
        $sql .= "',0";
    }
    if ($open == 'on') {
        $sql .= ",1";
    } else {
        $sql .= ",0";
    }
    if ($login_required == 'on') {
        $sql .= ",1";
    } else {
        $sql .= ",0";
    }
    if ($hideresults == 'on') {
        $sql .= ",1";
    } else {
        $sql .= ",0";
    }

    $sql .= ",'$statuscode','$commentcode',$owner_id,$group_id,$perm_owner,$perm_group,$perm_members,$perm_anon";

    // Save poll topic
    DB_save($_TABLES['polltopics'],"pid, topic, description,voters, questions, date, display, "
           . "is_open, login_required, hideresults, statuscode, commentcode, owner_id, group_id, "
           . "perm_owner, perm_group, perm_members, perm_anon",$sql);

    if (empty($old_pid) || ($old_pid == $pid)) {
        PLG_itemSaved($pid, 'polls');
    } else {
        DB_change($_TABLES['comments'], 'sid', DB_escapeString($pid),
                  array('sid', 'type'), array(DB_escapeString($old_pid), 'polls'));
        PLG_itemSaved($pid, 'polls', $old_pid);
    }

    if ($_POLL_VERBOSE) {
        Log::write('system',Log::DEBUG, '**** Leaving POLL_save() ****');
    }

    return PLG_afterSaveSwitch (
        $_PO_CONF['aftersave'],
        $_CONF['site_url'] . '/polls/index.php?pid=' . $pid,
        'polls',
        19
    );

    return COM_refresh($_CONF['site_admin_url'] . '/plugins/polls/index.php?msg=19');
}

/**
* Delete a poll
*
* @param    string  $pid    ID of poll to delete
* @return   string          HTML redirect
*
*/
function POLLS_delete($pid)
{
    global $_CONF, $_TABLES, $_USER;

    $pid = DB_escapeString ($pid);
    $result = DB_query ("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['polltopics']} WHERE pid = '$pid'");
    $Q = DB_fetchArray ($result);
    $access = SEC_hasAccess ($Q['owner_id'], $Q['group_id'], $Q['perm_owner'],
            $Q['perm_group'], $Q['perm_members'], $Q['perm_anon']);
    if ($access < 3) {
        COM_accessLog ("User {$_USER['username']} tried to illegally delete poll $pid.");
        return COM_refresh ($_CONF['site_admin_url'] . '/plugins/polls/index.php');
    }

    DB_delete($_TABLES['polltopics'], 'pid', $pid);
    DB_delete($_TABLES['pollanswers'], 'pid', $pid);
    DB_delete($_TABLES['pollquestions'], 'pid', $pid);
    DB_delete($_TABLES['pollvoters'],'pid',$pid);
    DB_delete($_TABLES['comments'], array('sid', 'type'),
                                    array($pid,  'polls'));

    PLG_itemDeleted($pid, 'polls');

    return COM_refresh ($_CONF['site_admin_url'] . '/plugins/polls/index.php?msg=20');
}


function POLLS_list()
{
    global $_CONF, $_TABLES, $_IMAGE_TYPE, $LANG_ADMIN, $LANG25, $LANG_ACCESS;

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/polls/index.php',
              'text' => $LANG_ADMIN['list_all'],'active'=>true),
        array('url' => $_CONF['site_admin_url'] . '/plugins/polls/index.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home']));

    $retval .= COM_startBlock($LANG25[18], '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG25[19],
        plugin_geticon_polls()
    );

    // writing the actual list
    $header_arr = array(      # display 'text' and use table field 'field'
        array('text' => $LANG_ADMIN['edit'], 'field' => 'edit', 'sort' => false, 'align' => 'center', 'width' => '25px'),
        array('text' => $LANG25[9], 'field' => 'topic', 'sort' => true),
        array('text' => $LANG25[20], 'field' => 'voters', 'sort' => true, 'align' => 'center'),
        array('text' => $LANG_ACCESS['access'], 'field' => 'access', 'sort' => false, 'align' => 'center'),
        array('text' => $LANG25[3], 'field' => 'unixdate', 'sort' => true, 'align' => 'center'),
        array('text' => $LANG25[33], 'field' => 'is_open', 'sort' => true, 'align' => 'center', 'width' => '35px'),
        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center', 'width' => '35px')
    );

    $defsort_arr = array('field' => 'unixdate', 'direction' => 'desc');

    $text_arr = array(
        'has_extras'   => true,
        'instructions' => $LANG25[19],
        'form_url'     => $_CONF['site_admin_url'] . '/plugins/polls/index.php'
    );

    $query_arr = array(
        'table' => 'polltopics',
        'sql' => "SELECT *,UNIX_TIMESTAMP(date) AS unixdate "
            . "FROM {$_TABLES['polltopics']} WHERE 1=1",
        'query_fields' => array('topic'),
        'default_filter' => COM_getPermSql ('AND')
    );

    $token = SEC_createToken();

    $retval .= ADMIN_list (
        'polls', 'POLLS_getListField', $header_arr,
        $text_arr, $query_arr, $defsort_arr, '', $token
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

function POLLS_deleteVote($id)
{
    global $_CONF, $_TABLES, $_PO_CONF;

    $retval = false;

    $result = DB_query("SELECT * FROM {$_TABLES['pollvoters']} WHERE id=".(int) $id);
    if ( DB_numRows($result) == 1 ) {
        $row = DB_fetchArray($result);
        $pid = $row['pid'];

        DB_query("DELETE FROM {$_TABLES['pollvoters']} WHERE id=".(int) $id);
/* ------
        $numVotes = DB_getItem($_TABLES['polltopics'],'voters','pid="'.DB_escapeString($pid).'"');
        $numVotes--;
        DB_query("UPDATE {$_TABLES['polltopics']} SET voters=".$numVotes." WHERE pid='".DB_escapeString($pid)."'");
---- */
        $retval = true;
    }
    return $retval;
}


function POLLS_listVotes($pid)
{
    global $_CONF, $_TABLES, $_IMAGE_TYPE, $LANG_ADMIN, $LANG_POLLS, $LANG25, $LANG_ACCESS;

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/polls/index.php',
              'text' => $LANG_ADMIN['list_all']),
        array('url' => $_CONF['site_admin_url'] . '/plugins/polls/index.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'].'/index.php',
              'text' => $LANG_ADMIN['admin_home']));

    $retval .= COM_startBlock('Poll Votes for ' . $pid, '',
                              COM_getBlockTemplate('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG25[19],
        plugin_geticon_polls()
    );

    $header_arr = array(
//        array('text' => $LANG_ADMIN['delete'], 'field' => 'delete', 'sort' => false, 'align' => 'center', 'width' => '25px'),
        array('text' => $LANG_POLLS['username'], 'field' => 'username', 'sort' => true),
        array('text' => $LANG_POLLS['ipaddress'], 'field' => 'ipaddress', 'sort' => true),
        array('text' => $LANG_POLLS['date_voted'], 'field' => 'date','sort' => true),
    );

    $defsort_arr = array('field' => 'date', 'direction' => 'desc');

    $text_arr = array(
        'has_extras'   => true,
        'instructions' => $LANG25[19],
        'form_url'     => $_CONF['site_admin_url'] . '/plugins/polls/index.php?lv=x&amp;pid='.urlencode($pid)
    );

    $sql = "SELECT * FROM {$_TABLES['pollvoters']} AS voters LEFT JOIN {$_TABLES['users']} AS users ON voters.uid=users.uid WHERE voters.pid='".DB_escapeString($pid)."'";

    $query_arr = array(
        'table' => 'pollvoters',
        'sql' => $sql,
        'query_fields' => array('uid'),
        'default_filter' => ''
    );

    $token = SEC_createToken();

    $retval .= ADMIN_list (
        'polls', 'POLLS_getListFieldVoters', $header_arr,
        $text_arr, $query_arr, $defsort_arr, '', $token
    );

    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

function POLLS_getListFieldVoters($fieldname, $fieldvalue, $A, $icon_arr, $token)
{
    global $_CONF, $LANG25, $LANG_ACCESS, $LANG_ADMIN, $_USER;

    $retval = '';

        $dt = new Date('now',$_USER['tzid']);

        switch($fieldname) {

            case 'username' :
                $retval = $fieldvalue;
                if ( $fieldvalue == '' || $fieldvalue == NULL ) {
                    $retval =  'Anonymous';
                }
                break;

             case 'date':
                $dt->setTimestamp($A['date']);
                $retval = $dt->format($_CONF['date'],true);
                break;

            case 'delete':
                $retval = FieldList::delete(
                    array(
                        'delete_url' => $_CONF['site_admin_url'] . '/plugins/polls/index.php'.'?delvote=x&amp;id='.$A['id'].'&amp;'.CSRF_TOKEN.'='.$token,
                        'attr' => array(
                            'title' => LANG_ADMIN['delete'],
                            'onclick' => "return doubleconfirm('" . 'Are you sure you want to delete this vote' . "','" . 'Are you really sure you want to delete this vote?' . "');",
                        )
                    )

                );
                break;

            case 'topic' :
                $filter = sanitizer::getInstance();
                $filter->setPostmode('text');
                $retval = $filter->filterData($fieldvalue);
                break;

            default:
                $retval = $fieldvalue;
                break;
        }


    return $retval;
}

// MAIN ========================================================================


$action = '';
$expected = array('edit','save','delete','lv','delvote');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	$action = $provided;
    }
}

$pid = '';
if (isset($_POST['pid'])) {
    $pid = COM_sanitizeID(COM_applyFilter($_POST['pid']));
} elseif (isset($_GET['pid'])) {
    $pid = COM_sanitizeID(COM_applyFilter($_GET['pid']));
}

$msg = 0;
if (isset($_POST['msg'])) {
    $msg = COM_applyFilter($_POST['msg'], true);
} elseif (isset($_GET['msg'])) {
    $msg = COM_applyFilter($_GET['msg'], true);
}

$page = '';
$title = $LANG25[18];

switch ($action) {

    case 'delvote' :
        if ( !isset($_GET['id'])) {
            $page = POLLS_list();
        } elseif (SEC_checktoken() ) {
            $id = COM_applyFilter($_GET['id'],true);
            POLLS_deleteVote($id);
            $page = POLLS_list();
        } else {
            $page = POLLS_list();
        }
        break;

    case 'lv' :
        $title = $LANG25[5];
        $page .= POLLS_listVotes($pid);
        break;

    case 'edit':
        $title = $LANG25[5];
        $page .= POLLS_edit($pid);
        break;

    case 'save':
        if (SEC_checktoken()) {
            $old_pid = (isset($_POST['old_pid'])) ? COM_sanitizeID(COM_applyFilter($_POST['old_pid'])): '';
            if (empty($pid) && !empty($old_pid)) {
                $pid = $old_pid;
            }
            if (empty($old_pid) && (!empty($pid))) {
                $old_pid = $pid;
            }
            if (!empty ($pid)) {
                $statuscode = (isset($_POST['statuscode'])) ? COM_applyFilter($_POST['statuscode'], true) : 0;
                $mainpage = (isset($_POST['mainpage'])) ? COM_applyFilter($_POST['mainpage']) : '';
                $open = (isset($_POST['open'])) ? COM_applyFilter($_POST['open']) : '';
                $login_required = (isset($_POST['login_required'])) ? COM_applyFilter($_POST['login_required']) : '';
                $hideresults = (isset($_POST['hideresults'])) ? COM_applyFilter($_POST['hideresults']) : '';
                $page .= POLLS_save($pid, $old_pid, $_POST['question'], $mainpage, $_POST['topic'],$_POST['description'],
                    $statuscode, $open, $login_required, $hideresults,
                    COM_applyFilter($_POST['commentcode'], true),
                    $_POST['answer'], $_POST['votes'], $_POST['remark'],
                    COM_applyFilter($_POST['owner_id'], true),
                    COM_applyFilter($_POST['group_id'], true),
                    $_POST['perm_owner'], $_POST['perm_group'],
                    $_POST['perm_members'], $_POST['perm_anon']);
            } else {
                $title = $LANG25[5];
                $page .= COM_startBlock($LANG21[32], '',
                COM_getBlockTemplate('_msg_block', 'header'));
                $page .= $LANG25[17];
                $page .= COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));
                $page .= POLLS_edit ();
            }
        } else {
            COM_accessLog("User {$_USER['username']} tried to save poll $pid and failed CSRF checks.");
            $page =  COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    case 'delete':
        if (empty($pid)) {
            Log::write('system',Log::ERROR, 'Polls Admin: Ignored possibly manipulated request to delete a poll.');
            $page .= COM_refresh ($_CONF['site_admin_url'] . '/plugins/polls/index.php');
        } elseif (SEC_checktoken()) {
            $page .= POLLS_delete($pid);
        } else {
            COM_accessLog("User {$_USER['username']} tried to illegally delete poll $pid and failed CSRF checks.");
            echo COM_refresh($_CONF['site_admin_url'] . '/index.php');
        }
        break;

    default:
        $title = $LANG25[18];
        $page .= ($msg > 0) ? COM_showMessage ($msg, 'polls') : '';
        $page .= POLLS_list();
        break;
}

$display .= COM_siteHeader('menu', $title);
$display .= $page;
$display .= COM_siteFooter();
echo $display;
?>