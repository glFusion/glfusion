<?php
/**
* glFusion CMS
*
* glFusion User Story administration
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Authors: Tony Bibbs        - tony AT tonybibbs DOT com
*           Mark Limburg      - mlimburg AT users DOT sourceforge DOT net
*           Jason Whittenburg - jwhitten AT securitygeeks DOT com
*           Dirk Haun         - dirk AT haun-online DOT de
*
*/

require_once 'lib-common.php';

use \glFusion\Cache\Cache;
use \glFusion\Log\Log;
use \glFusion\Article\Article;
use \glFusion\Database\Database;
use \glFusion\Admin\AdminAction;

$display = '';

if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) || ($_CONF['submitloginrequired'] == 1))) {
    $display  = COM_siteHeader('menu', $LANG_LOGIN[1]);
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

if (SEC_hasRights ('story.edit')) {
    $topic = '';
    if (isset ($_REQUEST['topic'])) {
        $topic = '&topic=' . urlencode(COM_sanitizeID(COM_applyFilter($_REQUEST['topic'])));
    }
    echo COM_refresh ($_CONF['site_admin_url'].'/story.php?newstory=x' . $topic);
    exit;
}

/**
* Submission editor
*
* Displays the story entry form
*
* @param    Article     $article        Article Object
* @param    string      $action         current action
* @param    bool        $preview        Preview mode
* @return   string      HTML for story editor
*
*/

function SubmissionEdit(Article $article, $action = '',$preview = false)
{
    global $_CONF, $_TABLES, $_USER, $LANG12, $LANG24, $LANG_ADMIN;

    $retval = '';

    $errors = $article->getErrors();
    foreach($errors AS $errormsg) {
        $retval .= COM_showMessageText($errormsg,$LANG24[25],true,'error');
    }

    if ($_CONF['storysubmission'] == 0) {
        $topicList = COM_topicList('tid,topic,sortnum',$article->get('tid'),2,false,3);
    } elseif ( $_CONF['story_submit_by_perm_only'] ) {
        $topicList = COM_topicList('tid,topic,sortnum',$article->get('tid'),2,false,3);
    } else {
        $topicList = COM_topicList('tid,topic,sortnum',$article->get('tid'),2);
    }

    // no topics
    if ($topicList == '') {
        COM_404();
    }

    $retval .= COM_startBlock($LANG12[6]);

    $storyform = new Template($_CONF['path_layout'] . 'submit');
    $storyform->set_file('storyform','submitstory.thtml');

    if ($article->get('postmode') == 'html') {
        $storyform->set_var ('show_htmleditor', true);
    } else {
        $storyform->unset_var ('show_htmleditor');
    }

    if (!COM_isAnonUser()) {
        $storyform->set_var(array(
            'story_username'    => $_USER['username'],
            'author'            => COM_getDisplayName (),
            'status_url'        => $_CONF['site_url'].'/users.php?mode=logout',
            'lang_loginout'     => $LANG12[34]
        ));
    } else {
        $storyform->set_var('status_url', $_CONF['site_url'] . '/users.php');
        $storyform->set_var('lang_loginout', $LANG12[2]);
        if (!$_CONF['disable_new_user_registration']) {
            $storyform->set_var('seperator', ' | ');
            $storyform->set_var('create_account',
                                            COM_createLink(
                                                $LANG12[53],
                                                $_CONF['site_url'] . '/users.php?mode=new',
                                                array('rel'=>"nofollow")
                                            )
            );
        }
    }

    $sec_token_name = CSRF_TOKEN;
    $sec_token = SEC_createToken();

    $storyform->set_var(array(
        'site_admin_url'        => $_CONF['site_admin_url'],
        'lang_username'         => $LANG12[27],
        'lang_title'            => $LANG12[10],
        'lang_preview'          => $LANG12[32],
        'story_title'           => $article->getEditItem('title'),
        'lang_topic'            => $LANG12[28],
        'story_topic_options'   => $topicList,
        'lang_story'            => $LANG12[29],
        'lang_introtext'        => $LANG12[54],
        'lang_bodytext'         => $LANG12[55],
        'story_introtext'       => $article->getEditItem('introtext'),
        'story_bodytext'        => $article->getEditItem('bodytext'),
        'lang_postmode'         => $LANG12[36],
        'story_postmode_options'=> COM_optionList($_TABLES['postmodes'],'code,name',$article->get('postmode')),
        'postmode'              => $article->getEditItem('postmode'),
        'allowed_html'          => COM_allowedHTML(SEC_getUserPermissions(),false,'glfusion','story').'<br/>'.COM_allowedAutotags(SEC_getUserPermissions(),false,'glfusion','story'),
        'story_uid'             => $article->getEditItem('uid'),
        'story_sid'             => $article->getEditItem('sid'),
        'story_date'            => $article->get('date'),
        'security_token'        => $sec_token,
        'security_token_name'   => $sec_token_name,
    ));

    if ($preview == true) {
        $storyform->set_var('preview_content',$article->getDisplayArticle('p'));
        $storyform->set_var('show_preview',true);
    } else {
        $storyform->unset_var('show_preview');
    }

    PLG_templateSetVars ('story', $storyform);

    if (($_CONF['skip_preview'] == 1) || (isset($_POST['mode']) && ($_POST['mode'] == $LANG12[32]))) {
        $storyform->set_var('save_button',
                    '<input name="mode" type="submit" value="' . $LANG12[8] . '>');
    }

    $storyform->parse('theform', 'storyform');
    $retval .= $storyform->finish($storyform->get_var('theform'));
    $retval .= COM_endBlock();

    $urlfor = 'advancededitor';
    if (COM_isAnonUser()) {
        $urlfor = 'advancededitor'.md5($_SERVER['REAL_ADDR']);
    }
    $rc = @setcookie ($_CONF['cookie_name'].'adveditor', SEC_createTokenGeneral($urlfor),
               time() + 1200, $_CONF['cookie_path'],
               $_CONF['cookiedomain'], $_CONF['cookiesecure']);

    return $retval;
}


/*
 * Save a story
 */

function SubmissionSave()
{
    global $_CONF, $_TABLES, $LANG_ADM_ACTIONS;

    $article = new Article();

    $rc = $article->retrieveArticleFromVars($_POST);
    if ($rc !== Article::STORY_LOADED_OK) {
        COM_setMsg('There was an error retreiving the submitted article.','error',true);
        echo COM_refresh($_CONF['site_url'].'/index.php');
    }

    $article->set('submission',true);

    $rc = $article->saveSubmission();

    if ($rc === false) {
        // error saving story
        return SubmissionEdit($article,'new');
    }

    if (isset ($_CONF['notification']) && in_array ('story', $_CONF['notification'])) {
        sendNotification ($article);
    }

    $c = \glFusion\Cache::getInstance();
    $c->deleteItemsByTag('menu');
    $c->deleteItemsByTag('whatsnew');
    echo COM_refresh( $_CONF['site_url'] . '/index.php?msg=2' );

//    echo COM_refresh($_CONF['site_url'].'/index.php?msg=9');
}


/**
* Send an email notification for a new submission.
*
* @param    string  $table  Table where the new submission can be found
* @param    string  $story  Story object that was submitted.
*
*/
function sendNotification($article)
{
    global $_CONF, $_USER, $_TABLES, $LANG01, $LANG08, $LANG24, $LANG29, $LANG_ADMIN;

    $dt = new Date('now',$_USER['tzid']);

    $title = $article->getDisplayItem('title');
    $postmode = $article->get('postmode');
    $introtext = $article->getDisplayItem('introtext_text');
    $storyauthor = $article->getDisplayItem('author_fullname');
    $topic = $article->getDisplayItem('topic');
    $mailbody = "$LANG08[31]: {$title}\n"
              . "$LANG24[7]: {$storyauthor}\n"
              . "$LANG08[32]: " . $dt->format($_CONF['date'],true) . "\n"
              . "{$LANG_ADMIN['topic']}: {$topic}\n\n";

    if ($_CONF['emailstorieslength'] > 0) {
        if ($_CONF['emailstorieslength'] > 1) {
            $introtext = MBYTE_substr ($introtext, 0,
                    $_CONF['emailstorieslength']) . '...';
        }
        $mailbody .= $introtext . "\n\n";
    }
    $mailbody .= "$LANG01[10] <{$_CONF['site_admin_url']}/moderation.php>\n\n";

    $mailsubject = $_CONF['site_name'] . ' ' . $LANG29[35];
    $mailbody .= "\n------------------------------\n";
    $mailbody .= "\n$LANG08[34]\n";
    $mailbody .= "\n------------------------------\n";

    $to = array();
    $to = COM_formatEmailAddress('',$_CONF['site_mail']);
    COM_mail ($to, $mailsubject, $mailbody);
}

// MAIN ========================================================================

$pageTitle = '';
$pageBody = '';
$action = 'newstory';

$expected = array('newstory','save','preview','cancel');
foreach($expected as $provided) {
    if (isset($_POST[$provided])) {
        $action = $provided;
    } elseif (isset($_GET[$provided])) {
	    $action = $provided;
    }
}

if (isset($_POST['cancel'])) {
    echo COM_refresh($_CONF['site_url'].'/index.php');
}

$msg = COM_getMessage();

$db = Database::getInstance();

switch ($action) {

    case 'newstory':
        $article = new Article();
        $pageBody = SubmissionEdit($article,'new');
        break;

    case 'preview' :
        if (SEC_checkToken()) {
            $article = new Article();
            if ($article->retrieveArticleFromVars($_POST) !== Article::STORY_LOADED_OK) {
                print "error";exit;
            }
            $pageBody = SubmissionEdit($article,'new',true);
        } else {
            echo COM_refresh($_CONF['site_url'].'/index.php?msg=501');
        }
        break;

    case 'save' :
        if (SEC_checkToken()) {
            $db->conn->delete(
                $_TABLES['tokens'],
                array(
                    'owner_id' => $_USER['uid'],
                    'urlfor'   => 'advancededitor'
                ),
                array(Database::INTEGER,Database::STRING)
            );
            $pageBody = SubmissionSave();
        } else {
            echo COM_refresh($_CONF['site_url'].'/index.php?msg=501');
        }
        break;

    default:
        // purge any tokens we created for the advanced editor
        $db->conn->delete(
            $_TABLES['tokens'],
            array(
                'owner_id'  => $_USER['uid'],
                'urlfor' => 'advancededitor'
            ),
            array(
                Database::INTEGER,
                Database::STRING
            )
        );
        echo COM_refresh($_CONF['site_url'] . '/index.php');
        break;
}


$display = COM_siteHeader('menu',$pageTitle);

if(isset($msg)) {
    $display .= (is_numeric($msg)) ? COM_showMessage($msg) : COM_showMessageText( $msg );
}
$display .= $pageBody;
$display .= COM_siteFooter();
echo $display;
?>