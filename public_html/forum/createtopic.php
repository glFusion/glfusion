<?php
// +--------------------------------------------------------------------------+
// | Forum Plugin for glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | createtopic.php                                                          |
// |                                                                          |
// | Main program to create topics and posts in the forum                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Blaine Lang       - blaine AT portalparts DOT com               |
// |                              www.portalparts.com                         |
// | Version 1.0 co-developer:    Matthew DeWyer, matt@mycws.com              |
// | Prototype & Concept :        Mr.GxBlock, www.gxblock.com                 |
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

if (!in_array('forum', $_PLUGINS)) {
    COM_404();
    exit;
}

USES_forum_functions();
USES_forum_format();
USES_forum_topic();
USES_forum_upload();

define('PREVIEW_VIEW',  1);
define('ERROR_VIEW',    2);

/**
 * General check to ensure anonymous users can post
 * and if the IP is banned.  If either fail, this
 * function displays the appropriate message and exits.
 */
forum_chkUsercanPost();

/**
 * Initialize variables
 */

$viewMode = false;
$mode    = '';
$expectedModes = array('newtopic','savetopic','newreply','savereply','edittopic','saveedit');

/**
 * Get the mode and validate
 */

if ( isset($_POST['mode']) ) {
    $mode = COM_applyFilter($_POST['mode']);
} else if ( isset($_GET['mode']) ) {
    $mode = COM_applyFilter($_GET['mode']);
}
if ( !in_array($mode,$expectedModes) ) {
    echo COM_refresh($_CONF['site_url']);
    exit;
}

/**
 * Build the referer URL and validate it
 */

if ( isset($_POST['referer']) ) {
    $referer = $_POST['referer'];
    $sLength = strlen($_CONF['site_url']);
    if ( substr($referer,0,$sLength) != $_CONF['site_url'] ) {
        $referer = $_CONF['site_url'].'/forum/index.php';
    }
} else {
    if ( isset($_SERVER['HTTP_REFERER'] ) ) {
        $referer = COM_sanitizeUrl($_SERVER['HTTP_REFERER']);
    } else {
        $referer = '';
    }
}
$sLength = strlen($_CONF['site_url']);
if ( substr($referer,0,$sLength) != $_CONF['site_url'] ) {
    $referer = $_CONF['site_url'].'/forum/index.php';
}
$referer = htmlentities($referer,ENT_COMPAT, COM_getEncodingt());
if ( strstr($referer,'moderation.php') !== false ) {
    if ( isset($_REQUEST['id']) ) {
        $referer = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.COM_applyFilter($_REQUEST['id'],true);
    }
}

/**
 * If the user has pressed CANCEL, we need to redirect
 * back to where they started the new post
 */

if ( isset($_POST['cancel']) ) {
    echo COM_refresh($referer);
    exit;
}

/**
 * If the user has pressed PREVIEW, we need to set the mode
 * (and validate it) and set the preview flag to true.
 */

if ( isset($_POST['preview']) ) {
    $viewMode = PREVIEW_VIEW;
    $mode = isset($_POST['action']) ? COM_applyFilter($_POST['action']) : 'newtopic';
    if ( !in_array($mode,$expectedModes) ) {
        echo COM_refresh($_CONF['site_url']);
        exit;
    }
}

$id    = isset($_REQUEST['id']) ? COM_applyFilter($_REQUEST['id'],true) : 0;
$forum = isset($_REQUEST['forum']) ? COM_applyFilter($_REQUEST['forum'],true) : 0;
$page  = isset($_REQUEST['page']) ? COM_applyFilter($_REQUEST['page'],true) : 0;

if ( (int) $forum == 0 && (int) $id != 0 ) {
    $forum = DB_getItem($_TABLES['ff_topic'],'forum','id='.(int) $id);
}
$result = DB_query("SELECT forum_id AS forum,forum_cat,is_readonly,grp_id,rating_post FROM {$_TABLES['ff_forums']} WHERE forum_id=".(int) $forum);
if ( DB_numRows($result) == 0 ) {
    _ff_accessError();
}
$forumData = DB_fetchArray($result,false);

$forumData['referer'] = $referer;
$forumData['page']    = $page;

/**
 * Validate if user can post to this specific forum
 */

if ( !_ff_canPost($forumData) ) {
    _ff_accessError();
}

$body = '';

if ( COM_isAnonUser() ) {
    $uid = 1;
} else {
    $uid = $_USER['uid'];
}
// purge any tokens we created for the advanced editor
$urlfor = 'advancededitor';
if ( $uid == 1 ) {
    $urlfor = 'advancededitor'.md5($REMOTE_ADDR);
}
DB_query("DELETE FROM {$_TABLES['tokens']} WHERE owner_id=".(int) $uid." AND urlfor='".$urlfor."'",1);

switch ( $mode ) {
    case 'newtopic' :
        $postData = array();
        $sql  = "SELECT a.forum_name,a.is_readonly,a.use_attachment_grpid,b.cat_name ";
        $sql .= "FROM {$_TABLES['ff_forums']} a ";
        $sql .= "LEFT JOIN {$_TABLES['ff_categories']} b on b.id=a.forum_cat ";
        $sql .= "WHERE a.forum_id=".(int) $forum;
        $result = DB_query($sql);
        if ( DB_numRows($result) == 0 ) {
            _ff_accessError();
        }
        $postData = DB_fetchArray($result,false);
        $postData['id']       = 0;
        $postData['pid']      = 0;
        $postData['subject']  = '';
        $postData['email']    = '';
        // ensure user can post to readonly forum.
        if ( $postData['is_readonly'] == 1 ) {
            // Check if this user has moderation rights now to allow a post to a locked topic
            if (!forum_modPermission($forumData['forum'],$uid,'mod_edit')) {
                _ff_accessError();
            }
        }
        if ( $viewMode ) {
            $postData = array_merge($postData,$_POST);
        }
        $body .= FF_postEditor( $postData,$forumData,$mode,$viewMode );
        break;

    case 'newreply' :
        $postData = array();
        $sql  = "SELECT a.forum,a.pid,a.comment,a.date,a.locked,a.subject,a.mood,a.sticky,a.uid,a.name,a.postmode,a.status,b.forum_cat,b.forum_name,b.is_readonly,c.cat_name,";
        $sql .= "b.forum_cat,b.forum_name,b.is_readonly,b.use_attachment_grpid,c.cat_name ";
        $sql .= "FROM {$_TABLES['ff_topic']} a ";
        $sql .= "LEFT JOIN {$_TABLES['ff_forums']} b ON b.forum_id=a.forum ";
        $sql .= "LEFT JOIN {$_TABLES['ff_categories']} c on c.id=b.forum_cat ";
        $sql .= "WHERE a.id=".(int) $id;
        $postData = DB_fetchArray(DB_query($sql),false);
        if ( $viewMode ) {
            $postData = array_merge($postData,$_POST);
        } else {
            $postData['id'] = (int) $id;
            $postData['comment'] = '';
            $postData['name'] = '';
            $postData['email'] = '';
            $postData['mood'] = '';
            $postData['moved'] = 0;
            $postData['replies'] = 0;
            $postData['views'] = 0;
            $postData['sticky'] = 0;
            $postData['locked'] = 0;
            $postData['status'] = 0;
            $postData['postmode'] = ($_FF_CONF['post_htmlmode'] == 1 && $_FF_CONF['allow_html'] == 1) ? 'html' : 'text';
        }
        if ( COM_isAnonUser() ) {
            $postData['uid'] = 1;
        } else {
            $postData['uid'] = $_USER['uid'];
        }
        if ( ($forumData['forum'] != 0) && $forumData['forum'] != $postData['forum'] ) {
            _ff_accessError();
        }
        if ( $postData['is_readonly'] == 1 ) {
            // Check if this user has moderation rights now to allow a post to a locked topic
            if (!forum_modPermission($forumData['forum'],$uid,'mod_edit')) {
                _ff_accessError();
            }
        }
        $body .= FF_postEditor( $postData,$forumData,$mode,$viewMode );
        break;

    case 'edittopic' :
        // we don't allow anonymous users to edit posts
        if ( COM_isAnonUser() ) {
            _ff_accessError();
        }
        $postData = array();
        $sql  = "SELECT a.forum,a.pid,a.comment,a.date,a.locked,a.subject,a.mood,a.sticky,a.uid,a.name,a.postmode,a.status,b.forum_cat,b.forum_name,b.is_readonly,c.cat_name,";
        $sql .= "b.forum_cat,b.forum_name,b.is_readonly,b.use_attachment_grpid,c.cat_name ";
        $sql .= "FROM {$_TABLES['ff_topic']} a ";
        $sql .= "LEFT JOIN {$_TABLES['ff_forums']} b ON b.forum_id=a.forum ";
        $sql .= "LEFT JOIN {$_TABLES['ff_categories']} c on c.id=b.forum_cat ";
        $sql .= "WHERE a.id=".(int) $id;
        $postData = DB_fetchArray(DB_query($sql),false);
        /**
         * Perform all necessary security checks
         */
        if ( ($forumData['forum'] != 0) && $forumData['forum'] != $postData['forum'] ) {
            _ff_accessError();
        }
        // ensure we can actually edit...
        $editAllowed = false;
        $editfailedreason = '';
        if (forum_modPermission($forumData['forum'],$_USER['uid'],'mod_edit')) {
            $editAllowed = true;
            $body .= '<input type="hidden" name="modedit" value="1"/>';
        } else {
            if ($postData['date'] > 0 AND $postData['uid'] == $_USER['uid'] ) {
                if ($_FF_CONF['allowed_editwindow'] > 0) {
                    $t2 = $_FF_CONF['allowed_editwindow'];
                    $time = time();
                    if ((time() - $t2) < $postData['date']) {
                        $editAllowed = true;
                    } else {
                        $editfailedreason = $LANG_GF02['edit_time_passed'];
                    }
                } else {
                    $editAllowed = true;
                }
            } else {
                $editfailedreason = $LANG_GF02['not_your_post'];
            }
        }
        if ( $editAllowed == false ) {
            $display  = FF_siteHeader();
            $display .= _ff_alertMessage($LANG_GF02['msg72'],$editfailedreason);
            $display .= FF_siteFooter();
            echo $display;
            exit;
        }
        if ( $viewMode ) {
            $postData = array_merge($postData,$_POST);
        } else {
            $postData['id'] = (int) $id;
        }
        // display the editor
        $body .= FF_postEditor( $postData,$forumData,$mode,$viewMode );
        break;

    case 'savetopic' :
    case 'savereply' :
    case 'saveedit' :
        $txt = '';
        $postData = $_POST;
        if ( !isset($postData['postmode']) ) {
            $postData['postmode'] = 'text';
        }
        if ( SEC_checkToken() ) {
            list($rc,$txt) = FF_saveTopic($forumData,$postData,$mode);
        } else {
            $rc   = false;
            $txt .= FF_BlockMessage('',$LANG_GF02['invalid_token'],false);
        }
        if ( $rc !== false ) {
            $body .= $txt;
        } else {
            $sql  = "SELECT a.forum_name,a.is_readonly,a.use_attachment_grpid,b.cat_name ";
            $sql .= "FROM {$_TABLES['ff_forums']} a ";
            $sql .= "LEFT JOIN {$_TABLES['ff_categories']} b on b.id=a.forum_cat ";
            $sql .= "WHERE a.forum_id=".(int) $forum;
            $result = DB_query($sql);
            if ( DB_numRows($result) == 0 ) {
                _ff_accessError();
            }
            $baseData = DB_fetchArray($result,false);
            $postData = array_merge($baseData,$postData);
            $body .= $txt . FF_postEditor( $postData,$forumData,$postData['action'],ERROR_VIEW );
        }
        break;
    default :
        echo COM_refresh($_CONF['site_url'].'/forum/index.php');
        exit;
        break;
}

// all screen io goes here

$display  = FF_siteHeader();
$display .= FF_ForumHeader($forum,0);
$display .= $body;
$display .= FF_siteFooter();
echo $display;

function _ff_accessError()
{
    global $LANG_GF01, $LANG_GF02;

    $display  = FF_siteHeader();
    $display .= '<br/>';
    $display .= FF_BlockMessage($LANG_GF01['ACCESSERROR'],$LANG_GF02['msg03'],false);
    $display .= FF_siteFooter();
    echo $display;
    exit;
}

function FF_postEditor( $postData, $forumData, $action, $viewMode )
{
    global $_CONF, $_TABLES, $_FF_CONF, $FF_userprefs, $_USER,
           $LANG_GF01, $LANG_GF02, $LANG_GF10,$REMOTE_ADDR, $LANG_ADMIN;

    $retval         = '';
    $editmoderator  = false;
    $numAttachments = 0;
    $edit_val       = '';
    $sticky_val     = '';
    $locked_val     = '';
    $notify_val     = '';

    if ( COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    // initialize defaults

    if ( $_FF_CONF['bbcode_disabled'] ) {
        $disable_bbcode_val = ' checked="checked"';
    } else {
        $disable_bbcode_val = '';
    }
    if ( $_FF_CONF['smilies_disabled'] ) {
        $disable_smilies_val = ' checked="checked"';
    } else {
        $disable_smilies_val = '';
    }
    if ( $_FF_CONF['urlparse_disabled'] ) {
        $disable_urlparse_val = ' checked="checked"';
    } else {
        $disable_urlparse_val = '';
    }

    // check postmode
    if ( isset($postData['postmode']) ) {  // this means we are editing or previewing (or both)
        if ( isset($postData['postmode_switch']) ) { // means they selected a switch
            $chkpostmode = _ff_chkpostmode($postData['postmode'],
                                        $postData['postmode_switch']);
            if ($chkpostmode != $postData['postmode']) {
                $postData['postmode'] = $chkpostmode;
                $postData['postmode_switch'] = 0;
            }
        }
    } else {
        if ( $_FF_CONF['post_htmlmode'] && $_FF_CONF['allow_html'] ) {
            $postData['postmode'] = 'html';
        } else {
            $postData['postmode'] = 'text';
        }
    }
    // verify postmode is allowed
    if ( $postData['postmode'] == 'html' || $postData['postmode'] == 'HTML' ) {
        if ($_FF_CONF['allow_html'] || SEC_inGroup( 'Root' ) || SEC_hasRights('forum.html')) {
            $postData['postmode'] = 'html';
        } else {
            $postData['postmode'] = 'text';
        }
    }
    $postData['postmode_switch'] = 0;

    // action specific setup
    if ( $action == 'edittopic' || $viewMode ) {
        // need to see what options were checked...
        $status = 0;
        // get our options...
        if ( isset($postData['disable_bbcode']) && $postData['disable_bbcode'] == 1 ) {
            $disable_bbcode_val = ' checked="checked"';
            $status += DISABLE_BBCODE;
        } else {
            if ( $viewMode != PREVIEW_VIEW ) {
                if ( $postData['status'] & DISABLE_BBCODE ) {
                    $disable_bbcode_val = ' checked="checked"';
                } else {
                    $disable_bbcode_val = '';
                }
            } else {
                $disable_bbcode_val = '';
            }
        }
        if ( isset($postData['disable_smilies']) && $postData['disable_smilies'] == 1 ) {
            $disable_smilies_val = ' checked="checked"';
            $status += DISABLE_SMILIES;
        } else {
            if ( $viewMode != PREVIEW_VIEW ) {
                if ( $postData['status'] & DISABLE_SMILIES ) {
                    $disable_smilies_val = ' checked="checked"';
                } else {
                    $disable_smilies_val = '';
                }
            } else {
                $disable_smilies_val = '';
            }
        }
        if ( isset($postData['disable_urlparse']) && $postData['disable_urlparse'] == 1 ) {
            $disable_urlparse_val = ' checked="checked"';
            $status += DISABLE_URLPARSE;
        } else {
            if ( $viewMode != PREVIEW_VIEW ) {
                if ( $postData['status'] & DISABLE_URLPARSE ) {
                    $disable_urlparse_val = ' checked="checked"';
                } else {
                    $disable_urlparse_val = '';
                }
            } else {
                $disable_urlparse_val = '';
            }
        }
        if ( (isset($postData['sticky_switch']) AND $postData['sticky_switch'] == 1)) {
            $sticky_val = 'checked="checked"';
        } else {
            if ( $viewMode != PREVIEW_VIEW ) {
                if ( $postData['sticky'] == 1 ) {
                    $sticky_val = ' checked="checked"';
                } else {
                    $sticky_val = '';
                }
            } else {
                $sticky_val = '';
            }
        }
    }
    // create our template
    $peTemplate = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $peTemplate->set_file('posteditor','posteditor.thtml');

    if ( $postData['postmode'] == 'html' ) {
        $peTemplate->set_var('html_mode',true);
    } else {
        $peTemplate->unset_var('html_mode');
    }

    if ( $viewMode == PREVIEW_VIEW ) {
        $peTemplate->set_var('preview_post',FF_previewPost( $postData, $action ));
    }

    $uniqueid = isset($postData['uniqueid']) ? COM_applyFilter($postData['uniqueid'],true) : mt_rand();
    $peTemplate->set_var('uniqueid',$uniqueid);

    if (SEC_inGroup($postData['use_attachment_grpid']) && $_FF_CONF['maxattachments'] > 0) {
        $peTemplate->set_var('use_attachments',true);
    }

    if ( $action == 'newtopic' ) {
        $peTemplate->set_var('save_button','savetopic');
        $postmessage = $LANG_GF02['PostTopic'];
        $peTemplate->set_var ('hidden_action', 'newtopic');
    }

    if ( $action == 'edittopic' ) {
        $peTemplate->set_var('save_button','saveedit');
        if (isset($postData['forum']) && forum_modPermission($postData['forum'],$_USER['uid'],'mod_edit')) {
            $editmoderator = true;
            $peTemplate->set_var ('hidden_modedit', '1');
        } else {
            $peTemplate->set_var ('hidden_modedit', '0');
            $editmoderator = false;
        }
        $postmessage = $LANG_GF02['EditTopic'];
        $peTemplate->set_var ('hidden_action', 'edittopic');
        $peTemplate->set_var ('hidden_editpost','yes');
        if ( $editmoderator ) {
            $username = $postData['name'];
        } elseif ($postData['uid'] > 1) {
            $username = COM_getDisplayName($postData['uid']);
        }

        $postData['comment'] = str_ireplace('</textarea>','&lt;/textarea&gt;',$postData['comment']);

        if ( isset($postData['pid']) ) {
            $peTemplate->set_var ('hidden_editpid', $postData['pid']);
        }
        $peTemplate->set_var ('hidden_editid',  $postData['id']);

        $edit_prompt = $LANG_GF02['msg190'] . '<br/><input type="checkbox" name="silentedit" ';
        if ((isset($postData['silentedit']) && $postData['silentedit'] == 1) OR ( !isset($postData['modedit']) AND $_FF_CONF['silent_edit_default'])) {
             $edit_prompt .= 'checked="checked" ';
             $edit_val = ' checked="checked" ';
        } else {
            $edit_val = '';
        }
        $edit_prompt .= 'value="1"/>';

        $peTemplate->set_var('attachments','<div id="fileattachlist">' . _ff_showattachments($postData['id'],'edit') . '</div>');
        $numAttachments = DB_Count($_TABLES['ff_attachments'],'topic_id',$postData['id']);
        $allowedAttachments = $_FF_CONF['maxattachments'] - $numAttachments;
        $peTemplate->set_var('fcounter',$allowedAttachments);
    } else {
        $numAttachments = (int) DB_Count($_TABLES['ff_attachments'],'topic_id',$uniqueid);
        $allowedAttachments = $_FF_CONF['maxattachments'] - $numAttachments;
        $peTemplate->set_var('fcounter',$allowedAttachments);
        $peTemplate->set_var('attachments','');
        if ( $uniqueid > 0 ) {
            $peTemplate->set_var('attachments','<div id="fileattachlist">' . _ff_showattachments($uniqueid,'edit') . '</div>');
        }
        $edit_prompt = '&nbsp;';
    }

    if ($action == 'newreply') {
        $peTemplate->set_var('save_button','savereply');
        $postmessage = $LANG_GF02['PostReply'];
        $peTemplate->set_var ('hidden_action', 'newreply');
        if ( !$viewMode ) {
            $postData['subject'] = $LANG_GF01['RE'] . $postData['subject'];
        }
        $quoteid = isset($_GET['quoteid']) ? COM_applyFilter($_GET['quoteid'],true) : 0;
        if ( !$viewMode) $postData['mood'] = '';
        if ($quoteid > 0 && !$viewMode ) {
            $quotesql = DB_query("SELECT * FROM {$_TABLES['ff_topic']} WHERE id=".(int) $quoteid);
            $quotearray = DB_fetchArray($quotesql);
            $quotearray['name'] = urldecode($quotearray['name']);
            $quotearray['comment'] = $quotearray['comment'];
            $postData['comment'] = sprintf($_FF_CONF['quoteformat'],$quotearray['name'],$quotearray['comment']);
        }
        $postData['editpid'] = $postData['id'];
    }

    if ( $_FF_CONF['use_sfs'] ) {
        $peTemplate->set_var ('usesfs',1);
    }

    if (COM_isAnonUser()) {
        if ( !$_FF_CONF['use_sfs']) {
            $postData['email'] = '';
        }
        $peTemplate->set_var ('anonymous_user',true);
        $peTemplate->set_var ('post_message', $postmessage);
        $peTemplate->set_var ('LANG_NAME', $LANG_GF02['msg33']);
        $peTemplate->set_var ('name', htmlentities(strip_tags(COM_checkWords(trim(USER_sanitizeName(isset($postData['name']) ? $postData['name'] : ''))))),ENT_COMPAT, COM_getEncodingt());
        if ( isset($postData['email']) ) {
            $peTemplate->set_var ('email',strip_tags($postData['email']));
        }
    } else {
        $peTemplate->set_var ('member_user',true);
        $peTemplate->set_var ('post_message', $postmessage);
        $peTemplate->set_var ('LANG_NAME', $LANG_GF02['msg33']);

        if (!isset($username) OR $username == '') {
            if ($action == 'edittopic') {
                if ( $editmoderator ) {
                    $username = $postData['name'];
                } else {
                    $username = COM_getDisplayName($_USER['uid']);
                }
            } else {
                $username = COM_getDisplayName($_USER['uid']);
            }
        }

        $peTemplate->set_var ('username', $username);
        $peTemplate->set_var ('xusername', urlencode($username));
    }
    $moodoptions = '';
    if ($_FF_CONF['show_moods']) {
        if (isset($postData['mood']) && $postData['mood'] != '') {
            $postData['mood'] = COM_applyFilter($postData['mood']);
        }
        if (!isset($postData['mood']) || $postData['mood'] == '') {
            $moodoptions = '<option value="" selected="selected">' . $LANG_GF01['NOMOOD'] . '</option>';
        }
        if ($dir = @opendir($_CONF['path_html'].'/forum/images/moods')) {
            while (($file = readdir($dir)) !== false) {
                if ((strlen($file) > 3) && substr(strtolower(trim($file)), -4, 4) == '.gif') {
                    $file = str_replace(array('.gif','.jpg'), array('',''), $file);
                    if(isset($postData['mood']) && $file == $postData['mood']) {
                        $moodoptions .= "<option selected=\"selected\">" . $file. "</option>";
                    } else {
                        $moodoptions .= "<option>" .$file. "</option>";
                    }
                } else {
                    $moodoptions .= '';
                }
            }
            closedir($dir);
        }
        $peTemplate->set_var ('LANG_MOOD', $LANG_GF02['msg36']);
        $peTemplate->set_var ('moodoptions', $moodoptions);
    }

    $sub_dot = '...';
    $sub_none = '';
    $postData['subject'] = str_replace($sub_dot, $sub_none, $postData['subject']);

    if ($_FF_CONF['allow_smilies']) {
        $peTemplate->set_var('smiley_enabled',true);
    }
    if ($_FF_CONF['allow_img_bbcode']) {
        $peTemplate->set_var ('allow_img_bbcode',true);
    }

    // if this is the first time showing the new submission form - then check if notify option should be on
    if ( !$viewMode ) {
        if (isset($postData['editpid']) && $postData['editpid'] > 0) {
            $notifyTopicid = $postData['editpid'];
        } else {
            $notifyTopicid = $postData['id'];
        }

        if ( !isset($postData['forum']) ) {
            $postData['forum'] = '';
        }
        if (DB_getItem($_TABLES['ff_userprefs'],'alwaysnotify', "uid=".(int) $uid) == 1 OR FF_isSubscribed( $postData['forum'], $notifyTopicid, $uid )) {
            $postData['notify'] = 'on';
            // check and see if user has un-subscribed to this topic
            $nid = -$notifyTopicid;
            if ($notifyTopicid > 0 AND (DB_getItem($_TABLES['subscriptions'],'id', "type='forum' AND category=".(int)$postData['forum']." AND id=$nid AND uid=$uid") > 1)) {
                $postData['notify'] = '';
            }
        } else {
            $postData['notify'] = '';
        }
    }

    if ( $editmoderator ) {
        if ((isset($postData['notify']) && $postData['notify'] == 'on') OR (isset($postData['notify']) && $postData['notify'] == 'on')) {
            $notify_val = 'checked="checked"';
        } else {
            $notify_val = '';
        }
        $notify_prompt = $LANG_GF02['msg38']. '<br/><input type="checkbox" name="notify" value="on" ' . $notify_val. '/>';
        // check that this is the parent topic - only able to make it skicky or locked
        if ( !isset($postData['pid']) || $postData['pid'] == 0 ) {
            if ($action == 'edittopic') {
                if( (!isset($postData['locked_switch']) AND (isset($postData['locked']) && (int) $postData['locked'] == 1)) || (isset($postData['locked_switch']) && $postData['locked_switch'] == 1) ) {
                    $locked_val = 'checked="checked"';
                } else {
                    $locked_val = '';
                }
                if( (!isset($postData['sticky_switch']) AND (isset($postData['sticky']) && $postData['sticky'] == 1)) OR (isset($postData['sticky_switch']) && $postData['sticky_switch'] == 1) ) {
                    $sticky_val = 'checked="checked"';
                } else {
                    $sticky_val = '';
                }
            }
            $locked_prompt = $LANG_GF02['msg109']. '<br/><input type="checkbox" name="locked_switch" ' .$locked_val. ' value="1"/>';
            $sticky_prompt = $LANG_GF02['msg61']. '<br/><input type="checkbox" name="sticky_switch" ' .$sticky_val. ' value="1"/>';
        } else {
            $locked_prompt = '';
            $sticky_prompt = '';
        }
    } else {
        if ($uid > 1) {
            if (isset($postData['notify']) && $postData['notify'] == 'on') {
                $notify_val = 'checked="checked"';
            } else {
                $notify_val = '';
            }
            $notify_prompt = $LANG_GF02['msg38']. '<br/><input type="checkbox" name="notify" ' .$notify_val. '/>';
            $locked_prompt = '';
        } else {
            $notify_prompt = '';
            $locked_prompt = '';
        }
    }

    if ($postData['postmode'] == 'html' || $postData['postmode'] == 'HTML') {
        $postmode_msg = $LANG_GF01['TEXTMODE'];
        $postData['postmode'] = 'html';
    } else {
        $peTemplate->unset_var('show_htmleditor');
        $postmode_msg = $LANG_GF01['HTMLMODE'];
    }

    if ($_FF_CONF['allow_html'] || SEC_inGroup( 'Root' ) || SEC_hasRights('forum.html')) {
        if ( $action == 'edittopic' ) {
            $mode_prompt = $postmode_msg. '<br/><input type="checkbox" name="postmode_switch" value="1"/><input type="hidden" name="postmode" value="' . $postData['postmode'] . '"/>';
        }
    }

    if ( $action == 'edittopic' ) {
        $peTemplate->set_var('bbcodeeditor',true);
    }

    $postData['subject'] = str_replace('&quot;','"', $postData['subject']);

    if(!$_FF_CONF['allow_smilies']) {
        $smilies = '';
    } else {
        $smilies =  forumPLG_showsmilies(0);
    }

    $disable_bbcode_prompt   = $LANG_GF01['disable_bbcode'].'&nbsp;<input type="checkbox" name="disable_bbcode" value="1" '.$disable_bbcode_val . '/>';
    if ( $_FF_CONF['allow_smilies'] ) {
        $disable_smilies_prompt  = $LANG_GF01['disable_smilies'].'&nbsp;<input type="checkbox" name="disable_smilies" value="1"'.$disable_smilies_val. ' />';
    } else {
        $disable_smilies_prompt = '';
    }
    $disable_urlparse_prompt = $LANG_GF01['disable_urlparse'].'&nbsp;<input type="checkbox" name="disable_urlparse" value="1"'.$disable_urlparse_val.' />';

    $peTemplate->set_var ('comment', @htmlspecialchars($postData['comment'],ENT_QUOTES, COM_getEncodingt()));
    $peTemplate->set_var(array(
        'edit_val'          => $edit_val,
        'sticky_val'        => $sticky_val,
        'locked_val'        => $locked_val,
        'postmode_msg'      => $postmode_msg,
        'notify_val'        => $notify_val,
        'disable_bbcode_val' => $disable_bbcode_val,
        'disable_smilies_val' => $disable_smilies_val,
        'disable_urlparse_val' => $disable_urlparse_val,
        'bbcode_prompt'     => $disable_bbcode_prompt,
        'smilies_prompt'    => $disable_smilies_prompt,
        'urlparse_prompt'   => $disable_urlparse_prompt,
        'LANG_SUBJECT'      => $LANG_GF01['SUBJECT'],
        'LANG_OPTIONS'      => $LANG_GF01['OPTIONS'],
        'mode_prompt'       => isset($mode_prompt) ? $mode_prompt : '',
        'notify_prompt'     => $notify_prompt,
        'locked_prompt'     => $locked_prompt,
        'sticky_prompt'     => isset($sticky_prompt) ? $sticky_prompt : '',
        'edit_prompt'       => $edit_prompt,
        'LANG_SUBMIT'       => $LANG_GF01['SUBMIT'],
        'LANG_PREVIEW'      => $LANG_GF01['PREVIEW'],
        'subject'           => @htmlspecialchars($postData['subject'],ENT_QUOTES, COM_getEncodingt()),
        'smilies'           => $smilies,
        'LANG_attachments'  => $LANG_GF10['attachments'],
        'LANG_maxattachments'=>sprintf($LANG_GF10['maxattachments'],$_FF_CONF['maxattachments']),
        'postmode'          => $postData['postmode'],
        'lang_timeout'      => $LANG_ADMIN['timeout_msg'],
    ));

    // Check and see if the filemgmt plugin is installed and enabled
    if (function_exists('filemgmt_buildAccessSql') && $_FF_CONF['enable_fm_integration'] == 1) {
        $peTemplate->set_var('filemgmt_category_options',gf_makeFilemgmtCatSelect($uid));
        $peTemplate->set_var('LANG_usefilemgmt',$LANG_GF10['usefilemgmt']);
        $peTemplate->set_var('LANG_description', $LANG_GF10['description']);
        $peTemplate->set_var('LANG_category', $LANG_GF10['category']);
    } else {
        $peTemplate->set_var('show_filemgmt_option','none');
    }

    if (COM_isAnonUser()) {
        $peTemplate->set_var ('hide_notify','none');
    }

    if ( function_exists('plugin_templatesetvars_captcha') ) {
        plugin_templatesetvars_captcha('forum', $peTemplate);
    } else {
        $peTemplate->set_var ('captcha','');
    }

    if ($postData['id'] > 0 ) {
        $peTemplate->set_var('topic_id',$postData['id']);
    }

    $peTemplate->set_var(array(
            'navbreadcrumbsimg' => _ff_getImage('nav_breadcrumbs'),
            'navtopicimg'       => _ff_getImage('nav_topic'),
            'form_action'       => $_CONF['site_url'] .'/forum/createtopic.php',
            'referer'           => $forumData['referer'],
            'forum_id'          => $forumData['forum'],
            'cat_name'          => $postData['cat_name'],
            'cat_id'            => $forumData['forum_cat'],
            'forum_name'        => $postData['forum_name'],
            'subject'           => @htmlspecialchars($postData['subject'],ENT_QUOTES, COM_getEncodingt()),
            'LANG_HOME'         => $LANG_GF01['HOMEPAGE'],
            'forum_home'        => $LANG_GF01['INDEXPAGE'],
            'hidden_id'         => $postData['id'],
            'page'              => $forumData['page'],
            'LANG_bhelp'        => $LANG_GF01['b_help'],
            'LANG_ihelp'        => $LANG_GF01['i_help'],
            'LANG_uhelp'        => $LANG_GF01['u_help'],
            'LANG_qhelp'        => $LANG_GF01['q_help'],
            'LANG_chelp'        => $LANG_GF01['c_help'],
            'LANG_lhelp'        => $LANG_GF01['l_help'],
            'LANG_ohelp'        => $LANG_GF01['o_help'],
            'LANG_phelp'        => $LANG_GF01['p_help'],
            'LANG_whelp'        => $LANG_GF01['w_help'],
            'LANG_ahelp'        => $LANG_GF01['a_help'],
            'LANG_shelp'        => $LANG_GF01['s_help'],
            'LANG_fhelp'        => $LANG_GF01['f_help'],
            'LANG_hhelp'        => $LANG_GF01['h_help'],
            'LANG_thelp'        => $LANG_GF01['t_help'],
            'LANG_ehelp'        => $LANG_GF01['e_help'],
            'LANG_code'         => $LANG_GF01['CODE'],
            'LANG_fontcolor'    => $LANG_GF01['FONTCOLOR'],
            'LANG_fontsize'     => $LANG_GF01['FONTSIZE'],
            'LANG_closetags'    => $LANG_GF01['CLOSETAGS'],
            'LANG_codetip'      => $LANG_GF01['CODETIP'],
            'LANG_tiny'         => $LANG_GF01['TINY'],
            'LANG_small'        => $LANG_GF01['SMALL'],
            'LANG_normal'       => $LANG_GF01['NORMAL'],
            'LANG_large'        => $LANG_GF01['LARGE'],
            'LANG_huge'         => $LANG_GF01['HUGE'],
            'LANG_default'      => $LANG_GF01['DEFAULT'],
            'LANG_dkred'        => $LANG_GF01['DKRED'],
            'LANG_red'          => $LANG_GF01['RED'],
            'LANG_orange'       => $LANG_GF01['ORANGE'],
            'LANG_brown'        => $LANG_GF01['BROWN'],
            'LANG_yellow'       => $LANG_GF01['YELLOW'],
            'LANG_green'        => $LANG_GF01['GREEN'],
            'LANG_olive'        => $LANG_GF01['OLIVE'],
            'LANG_cyan'         => $LANG_GF01['CYAN'],
            'LANG_blue'         => $LANG_GF01['BLUE'],
            'LANG_dkblue'       => $LANG_GF01['DKBLUE'],
            'LANG_indigo'       => $LANG_GF01['INDIGO'],
            'LANG_violet'       => $LANG_GF01['VIOLET'],
            'LANG_white'        => $LANG_GF01['WHITE'],
            'LANG_black'        => $LANG_GF01['BLACK'],
    ));

    $peTemplate->set_var('token_name', CSRF_TOKEN);
    $peTemplate->set_var('token', SEC_createToken());

    $peTemplate->set_var ('postmode', $postData['postmode']);
    $peTemplate->unset_var('show_htmleditor');
    if ($_FF_CONF['use_wysiwyg_editor'] && $postData['postmode'] == 'html') {
        // hook into wysiwyg here
        switch( PLG_getEditorType() ) {
            case 'ckeditor' :
                $peTemplate->set_var('show_htmleditor',true);
                PLG_requestEditor('forum','forum_entry','ckeditor_forum.thtml');
                PLG_templateSetVars('forum_entry',$peTemplate);
                break;
            case 'tinymce' :
                $peTemplate->set_var('show_htmleditor',true);
                PLG_requestEditor('forum','forum_entry','tinymce_forum.thtml');
                PLG_templateSetVars('forum_entry',$peTemplate);
                break;

            default :
                // don't support others right now
                break;
        }
    }
    $peTemplate->parse ('output', 'posteditor');
    $retval .= $peTemplate->finish($peTemplate->get_var('output'));
    $urlfor = 'advancededitor';
    if ( $uid == 1 ) {
        $urlfor = 'advancededitor'.md5($REMOTE_ADDR);
    }
    SEC_setCookie ($_CONF['cookie_name'].'adveditor', SEC_createTokenGeneral($urlfor),
                   time() + 1200, $_CONF['cookie_path'],
                   $_CONF['cookiedomain'], $_CONF['cookiesecure'],false);

    if ( !isset($_POST['editpost']) ) {
        $_POST['editpost'] = '';
    }

    if (($action != 'newtopic' && $_POST['editpost'] != 'yes') && ($action == 'newreply' || $viewMode)) {
        if ($FF_userprefs['showiframe']) {
            $retval .= "<iframe src=\"{$_CONF['site_url']}/forum/viewtopic.php?mode=preview&amp;showtopic=".$postData['id']."&amp;onlytopic=1&amp;lastpost=true\" height=\"300\" width=\"100%\"></iframe>";
        }
    }
    return $retval;
}

function FF_saveTopic( $forumData, $postData, $action )
{
    global $_CONF, $_TABLES, $_FF_CONF, $_USER, $LANG03, $LANG_GF01, $LANG_GF02;

    $retval        = '';
    $uploadErrors  = '';
    $msg           = '';
    $errorMessages = '';
    $email         = '';

    $forumfiles = array();

    $okToSave = true;
    $dt = new Date('now',$_USER['tzid']);
    $date = $dt->toUnix();

    $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];

    if (COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }

    // verify postmode is allowed
    if ( strtolower($postData['postmode']) == 'html' ) {
        if ($_FF_CONF['allow_html'] || SEC_inGroup( 'Root' ) || SEC_hasRights('forum.html')) {
            $postData['postmode'] = 'html';
        } else {
            $postData['postmode'] = 'text';
        }
    }

    // is forum readonly?
    if ( $forumData['is_readonly'] == 1 ) {
        // Check if this user has moderation rights now to allow a post to a locked topic
        if (!forum_modPermission($forumData['forum'],$uid,'mod_edit')) {
            _ff_accessError();
        }
    }
    if ( $action == 'saveedit' ) {
        // does the forum match the forum id of the posted data?
        if ( ($forumData['forum'] != 0) && $forumData['forum'] != $postData['forum'] ) {
            _ff_accessError();
        }
        $editid = COM_applyFilter($postData['editid'],true);
        $forum = COM_applyFilter($postData['forum'],true);
        $editAllowed = false;
        if (forum_modPermission($forumData['forum'],$_USER['uid'],'mod_edit')) {
            $editAllowed = true;
        } else {
            if ($_FF_CONF['allowed_editwindow'] > 0) {
                $t1 = DB_getItem($_TABLES['ff_topic'],'date',"id=".(int) $postData['id']);
                $t2 = $_FF_CONF['allowed_editwindow'];
                $time = time();
                if ((time() - $t2) < $t1) {
                    $editAllowed = true;
                }
            } else {
                $editAllowed = true;
            }
        }

        if (($postData['editpid'] < 1) && (trim($postData['subject']) == '')) {
            $retval .= FF_BlockMessage('',$LANG_GF02['msg18'],false);
            $okToSave = false;
        } elseif (!$editAllowed) {
            $link = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.(int) $postData['$id'];
            $retval.= _ff_alertMessage('',$LANG_GF02['msg189'], sprintf($LANG_GF02['msg187'],$link));
            $okToSave = false;
        }
    } else {
        if ( !COM_isAnonUser() && $_FF_CONF['use_sfs']) {
            $email = isset($_USER['email']) ? $_USER['email'] : '';
        }
    }
    if (isset($postData['name']) && $postData['name'] != '') {
        $name = _ff_preparefordb(@htmlspecialchars(strip_tags(trim(COM_checkWords(USER_sanitizeName($postData['name'])))),ENT_QUOTES,COM_getEncodingt()),'text');
        $name = urldecode($name);
    } else {
        $okToSave = false;
        $errorMessages .= $LANG_GF02['invalid_name'] . '<br />';
    }

    // speed limit check
    if ( !SEC_hasRights('forum.edit') ) {
        COM_clearSpeedlimit ($_FF_CONF['post_speedlimit'], 'forum');
        $last = COM_checkSpeedlimit ('forum');
        if ($last > 0) {
            $errorMessages .= sprintf($LANG_GF01['SPEEDLIMIT'],$last,$_FF_CONF['post_speedlimit']) . '<br/>';
            $okToSave = false;
        }
    }

    // standard edit checks
    if (strlen(trim($postData['name'])) < $_FF_CONF['min_username_length'] ||
        strlen(trim($postData['subject'])) < $_FF_CONF['min_subject_length'] ||
        strlen(trim($postData['comment'])) < $_FF_CONF['min_comment_length']) {
        $errorMessages .= $LANG_GF02['msg18'] . '<br/>';
        $okToSave = false;
    }

    // CAPTCHA check
    if ( function_exists('plugin_itemPreSave_captcha') && $okToSave == true) {
        if ( !isset($postData['captcha']) ) {
            $postData['captcha'] = '';
        }
        $msg = plugin_itemPreSave_captcha('forum',$postData['captcha']);
        if ( $msg != '' ) {
            $errorMessages .= $msg .'<br/>';
            $okToSave = false;
        }
    }

    $status = 0;
    if ( isset($postData['disable_bbcode']) && $postData['disable_bbcode'] == 1 ) {
        $status += DISABLE_BBCODE;
    }
    if ( isset($postData['disable_smilies']) && $postData['disable_smilies'] == 1 ) {
        $status += DISABLE_SMILIES;
    }
    if ( isset($postData['disable_urlparse']) && $postData['disable_urlparse'] == 1 ) {
        $status += DISABLE_URLPARSE;
    }

    // spamx check
    if ($_FF_CONF['use_spamx_filter'] == 1 && $okToSave == true) {
        SESS_unSet('spamx_msg'); // clear out the message.
        // Check for SPAM
        $spamcheck = '<h1>' . $postData['subject'] . '</h1><p>' . FF_formatTextBlock($postData['comment'],$postData['postmode'],'preview',$status) . '</p>';
        $result = PLG_checkforSpam($spamcheck, $_CONF['spamx']);
        // Now check the result and redirect to index.php if spam action was taken
        if ($result > 0) {
            // then tell them to get lost ...
            $errorMessages .= $LANG_GF02['spam_detected'];
            if ( SESS_isSet('spamx_msg')) {
                $errorMessages .= '<br>'. SESS_getVar('spamx_msg'). '<br>';
                SESS_unSet('spamx_msg');
            }
            $okToSave = false;
        }
    }

    if ( $_FF_CONF['use_sfs'] == 1 && COM_isAnonUser() && function_exists('plugin_itemPreSave_spamx')) {
       $spamCheckData = array(
            'username'  => $postData['name'],
            'email'     => $email,
            'ip'        => $REMOTE_ADDR);

        $msg = plugin_itemPreSave_spamx('forum',$spamCheckData);
        if ( $msg ) {
            $errorMessages .= $msg;
            $okToSave = false;
        }
    }

    if ( $okToSave == false ) {
        $retval .= _ff_alertMessage($errorMessages,$LANG_GF01['ERROR'],'&nbsp;');
        return array(false,$retval);
    }

    if ( $okToSave == true ) {
        if ( !isset($postData['postmode_switch']) ) {
            $postData['postmode_switch'] = 0;
        }
        $postmode   = _ff_chkpostmode($postData['postmode'],$postData['postmode_switch']);
        // validate postmode

        if ( $postmode == 'html' || $postmode == 'HTML' ) {
            if ($_FF_CONF['allow_html'] || SEC_inGroup( 'Root' ) || SEC_hasRights('forum.html')) {
                $postmode = 'html';
            } else {
                $postmode = 'text';
            }
        }
        $subject    = _ff_preparefordb(strip_tags($postData['subject']),'text');
        $comment    = _ff_preparefordb($postData['comment'],$postmode);
        $mood       = isset($postData['mood']) ? COM_applyFilter($postData['mood']) : '';
        $id         = COM_applyFilter($postData['id'],true);
        $forum      = COM_applyFilter($postData['forum'],true);
        $notify     = isset($postData['notify']) ? COM_applyFilter($postData['notify']) : '';

        // If user has moderator edit rights only
        $locked = 0;
        $sticky = 0;
        if (isset($postData['modedit']) && $postData['modedit'] == 1) {
            if (isset($postData['locked_switch']) && $postData['locked_switch'] == 1) {
                $locked = 1;
            }
            if (isset($postData['sticky_switch']) && $postData['sticky_switch'] == 1) {
                $sticky = 1;
            }
        }

        if ( $action == 'savetopic' ) {
            $fields = "forum,name,email,date,lastupdated,subject,comment,postmode,ip,mood,uid,pid,sticky,locked,status";
            $sql  = "INSERT INTO {$_TABLES['ff_topic']} ($fields) ";
            $sql .= "VALUES (".(int) $forum."," .
                    "'".DB_escapeString($name)."'," .
                    "'".DB_escapeString($email)."',".
                    "'".DB_escapeString($date)."'," .
                    "'".DB_escapeString($date)."'," .
                    "'".$subject."'," .
                    "'".$comment."'," .
                    "'".DB_escapeString($postmode)."'," .
                    "'".DB_escapeString($REMOTE_ADDR)."'," .
                    "'".DB_escapeString($mood)."'," .
                    (int) $uid."," .
                    "0," .
                    (int) $sticky."," .
                    (int) $locked."," .
                    (int) $status.")";

            DB_query($sql);

            // Find the id of the last inserted topic
            list ($lastid) = DB_fetchArray(DB_query("SELECT max(id) FROM {$_TABLES['ff_topic']} "));
            $savedPostID = $lastid;
            $topicPID    = $lastid;
            /* Check for any uploaded files - during add of new topic */
            $uploadErrors = _ff_check4files($lastid);

            // Check and see if there are no [file] bbcode tags in content and reset the show_inline value
            // This is needed in case user had used the file bbcode tag and then removed it
            $imagerecs = '';
            $imagerecs = implode(',',$forumfiles);
            $sql = "UPDATE {$_TABLES['ff_attachments']} SET show_inline = 0 WHERE topic_id=".(int) $lastid." ";
            if ($imagerecs != '') $sql .= "AND id NOT IN ($imagerecs)";
            DB_query($sql);
            // Update forums record
            DB_query("UPDATE {$_TABLES['ff_forums']} SET post_count=post_count+1, topic_count=topic_count+1, last_post_rec=".(int) $lastid." WHERE forum_id=".(int) $forum);
            if ( DB_Count($_TABLES['ff_attachments'],'topic_id',(int) $lastid) ) {
                DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=1 WHERE id=".(int) $lastid);
            }
            DB_query("DELETE FROM {$_TABLES['ff_log']} WHERE topic=".(int) $topicPID." and time > 0");
        } else if ( $action == 'savereply' ) {

            $fields = "name,email,date,subject,comment,postmode,ip,mood,uid,pid,forum,status";
            $sql  = "INSERT INTO {$_TABLES['ff_topic']} ($fields) ";
            $sql .= "VALUES  (" .
                    "'".DB_escapeString($name)."'," .
                    "'".DB_escapeString($email)."',".
                    "'".DB_escapeString($date)."'," .
                    "'$subject'," .
                    "'$comment'," .
                    "'".DB_escapeString($postmode)."'," .
                    "'".DB_escapeString($REMOTE_ADDR)."'," .
                    "'".DB_escapeString($mood)."'," .
                    (int) $uid."," .
                    (int) $id."," .
                    (int) $forum."," .
                    (int) $status.")";
            DB_query($sql);

            // Find the id of the last inserted topic
            list ($lastid) = DB_fetchArray(DB_query("SELECT max(id) FROM {$_TABLES['ff_topic']} "));
            $savedPostID = $lastid;
            $topicPID    = $id;

            /* Check for any uploaded files  - during adding reply post */
            $uploadErrors = _ff_check4files($lastid);

            // Check and see if there are no [file] bbcode tags in content and reset the show_inline value
            // This is needed in case user had used the file bbcode tag and then removed it
            $imagerecs = '';
            $imagerecs = implode(',',$forumfiles);
            $sql = "UPDATE {$_TABLES['ff_attachments']} SET show_inline = 0 WHERE topic_id=".(int) $lastid;
            if ($imagerecs != '') $sql .= " AND id NOT IN ($imagerecs)";
            DB_query($sql);
            DB_query("UPDATE {$_TABLES['ff_topic']} SET replies=replies+1, lastupdated='".DB_escapeString($date)."',last_reply_rec=".(int)$lastid." WHERE id=".(int)$id);
            DB_query("UPDATE {$_TABLES['ff_forums']} SET post_count=post_count+1, last_post_rec=".(int) $lastid." WHERE forum_id=".(int)$forum);
            if ( DB_Count($_TABLES['ff_attachments'],'topic_id',(int) $lastid) ) {
                DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=1 WHERE id=".(int) $id);
            }
            DB_query("DELETE FROM {$_TABLES['ff_log']} WHERE topic=".(int) $topicPID." and time > 0");
        } elseif ( $action == 'saveedit' ) {
            $sql = "UPDATE {$_TABLES['ff_topic']} SET " .
                   "subject='$subject'," .
                   "comment='$comment'," .
                   "postmode='".DB_escapeString($postmode)."'," .
                   "mood='".DB_escapeString($mood)."'," .
                   "sticky=".(int) $sticky."," .
                   "locked=".(int) $locked."," .
                   "status=".(int) $status." " .
                   "WHERE (id=".(int) $editid.")";
            DB_query($sql);

            /* Check for any uploaded files  - during save of edit */
            $uploadErrors = _ff_check4files($editid);

            // Check and see if there are no [file] bbcode tags in content and reset the show_inline value
            // This is needed in case user had used the file bbcode tag and then removed it
            $imagerecs = '';
            $imagerecs = implode(',',$forumfiles);
            $sql = "UPDATE {$_TABLES['ff_attachments']} SET show_inline = 0 WHERE topic_id=".(int) $editid." ";
            if ($imagerecs != '') $sql .= "AND id NOT IN ($imagerecs)";
            DB_query($sql);

            $topicPID = DB_getITEM($_TABLES['ff_topic'],"pid","id=".(int) $editid);
            if ($topicPID == 0) {
                $topicPID = $editid;
            }
            $savedPostID = $editid;
            if (!isset($postData['silentedit']) || $postData['silentedit'] != 1) {
                DB_query("UPDATE {$_TABLES['ff_topic']} SET lastupdated='".DB_escapeString($date)."',date='".DB_escapeString($date)."' WHERE id=".(int) $topicPID);
                //Remove any lastviewed records in the log so that the new updated topic indicator will appear
                DB_query("DELETE FROM {$_TABLES['ff_log']} WHERE topic=".(int) $topicPID." and time > 0");
            }
            if ( DB_Count($_TABLES['ff_attachments'],'topic_id',(int) $editid) ) {
                DB_query("UPDATE {$_TABLES['ff_topic']} SET attachments=1 WHERE id=".(int) $topicPID);
            }
            $topicparent = $topicPID;
        }
        COM_updateSpeedLimit('forum');
        PLG_itemSaved($savedPostID,'forum');
        CACHE_remove_instance('forumcb');

        if ( !COM_isAnonUser() ) {
            //NOTIFY - Checkbox variable in form set to "on" when checked and they don't already have subscribed to forum or topic
            $nid = -$topicPID;
            $currentForumNotifyRecID   = (int) DB_getItem($_TABLES['subscriptions'],'sub_id', "type='forum' AND category='".DB_escapeString($forum)."' AND id=0 AND uid=".(int) $uid);
            $currentTopicNotifyRecID   = (int) DB_getItem($_TABLES['subscriptions'],'sub_id', "type='forum' AND category='".DB_escapeString($forum)."' AND id='".DB_escapeString($topicPID)."' AND uid=".(int) $uid);
            $currentTopicUnNotifyRecID = (int) DB_getItem($_TABLES['subscriptions'],'sub_id', "type='forum' AND category='".DB_escapeString($forum)."' AND id='".DB_escapeString($nid)."' AND uid=".(int) $uid);
            $forum_name = DB_getItem($_TABLES['ff_forums'],'forum_name','forum_id='.(int)$forum);
            $topic_name = $subject;

            if ($notify == 'on' AND ($currentForumNotifyRecID < 1 AND $currentTopicNotifyRecID < 1 ) ) {
                $sql = "INSERT INTO {$_TABLES['subscriptions']} (type,category,category_desc,id,id_desc,uid,date_added) ";
                $sql .= "VALUES ('forum','".DB_escapeString($forum)."','".DB_escapeString($forum_name)."','".DB_escapeString($topicPID)."','".$subject."',".(int) $uid .",now() )";
                DB_query($sql);
            } elseif ($notify == 'on' AND $currentTopicUnNotifyRecID > 1) { // Had un-subcribed to topic and now wants to subscribe
                DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE sub_id=".(int) $currentTopicUnNotifyRecID);
            } elseif ($notify == '' AND $currentTopicNotifyRecID > 1) { // Subscribed to topic - but does not want to be notified anymore
                DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int) $uid." AND category='".DB_escapeString($forum)."' and id = '".DB_escapeString($topicPID)."'");
            } elseif ($notify == '' AND $currentForumNotifyRecID > 1) { // Subscribed to forum - but does not want to be notified about this topic
                DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int) $uid." AND category='".DB_escapeString($forum)."' and id = '".DB_escapeString($topicPID)."'");
                DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".(int) $uid." AND category='".DB_escapeString($forum)."' and id = '".DB_escapeString($nid)."'");
                DB_query("INSERT INTO {$_TABLES['subscriptions']} (type,category,category_desc,id,id_desc,uid,date_added) VALUES ('forum','".DB_escapeString($forum)."','".DB_escapeString($forum_name)."','".DB_escapeString($nid)."','".$subject."',".(int)$uid.",now() )");
            }
        }
        if ( $action != 'saveedit' ) {
            _ff_chknotifications($forum,$savedPostID,$uid);
        }

        $link = $_CONF['site_url'].'/forum/viewtopic.php?showtopic='.$topicPID.'&topic='.$savedPostID.'#'.$savedPostID;
        if ( $uploadErrors != '' ) {
            $autorefresh = false;
        } else {
            $autorefresh = true;
        }
        $retval .= FF_statusMessage($uploadErrors . $LANG_GF02['msg19'],$link,$LANG_GF02['msg19'],false,'',$autorefresh);
    } else {
        $retval .= _ff_alertMessage($LANG_GF02['msg18']);
    }
    return array(true,$retval);
}

function FF_previewPost( $postData, $mode )
{
    global $_CONF, $_TABLES, $_FF_CONF, $_USER;

    $retval = '';

    $postData['name'] = @htmlspecialchars(strip_tags(COM_checkWords(trim(USER_sanitizeName(urldecode($_POST['name']))))),ENT_QUOTES,COM_getEncodingt());
    if ( !isset($postData['uid']) ) {
        if ( COM_isAnonUser() ) {
            $postData['uid'] = 1;
        } else {
            $postData['uid']  = $_USER['uid'];
        }
    }

    $status = 0;
    if ( isset($postData['disable_bbcode']) && $postData['disable_bbcode'] == 1 ) {
        $disable_bbcode_val = ' checked="checked"';
        $status += DISABLE_BBCODE;
    } else {
        $disable_bbcode_val = '';
    }
    if ( isset($postData['disable_smilies']) && $postData['disable_smilies'] == 1 ) {
        $disable_smilies_val = ' checked="checked"';
        $status += DISABLE_SMILIES;
    } else {
        $disable_smilies_val = '';
    }
    if ( isset($postData['disable_urlparse']) && $postData['disable_urlparse'] == 1 ) {
        $disable_urlparse_val = ' checked="checked"';
        $status += DISABLE_URLPARSE;
    } else {
        $disable_urlparse_val = '';
    }
    $postData['status'] = $status;
    $postData['date'] = time();
    if (isset($postData['modedit']) && $postData['modedit'] == 1) {
        if (isset($postData['locked_switch']) && $postData['locked_switch'] == 1) {
            $postData['locked'] = 1;
        }
        if (isset($postData['sticky_switch']) && $postData['sticky_switch'] == 1) {
            $postData['sticky'] = 1;
        }
    } else {
        $postData['locked'] = 0;
        $postData['sticky'] = 0;
    }
    if ( !isset($postData['pid']) ) {
        $postData['pid'] = 0;
    }
    if ( !isset($postData['views']) ) {
        $postData['views'] = 0;
    }

    /* Check for any uploaded files */
    $UploadErrors = '';
    if ( $mode == 'edittopic' ) {
        /* Check for any uploaded files */
        if ( isset($postData['id']) && $postData['id'] > 0 ) {
            $UploadErrors = _ff_check4files($postData['id']);
            $postData['numAttachments'] = DB_count($_TABLES['ff_attachments'],'topic_id',(int) $postData['id']);
        }
    } else {
        /* Check for any uploaded files */
        if ( isset($postData['uniqueid']) && $postData['uniqueid'] > 0 ) {
            $UploadErrors = _ff_check4files($postData['uniqueid'],true);
            $postData['numAttachments'] = DB_count($_TABLES['ff_attachments'],array('topic_id','tempfile'),array((int)$postData['uniqueid'],1));
        }
    }

    $previewTemplate = new Template($_CONF['path'] . 'plugins/forum/templates/');
    $previewTemplate->set_file ('preview','topic_preview.thtml');

    if ( $UploadErrors ) {
        $previewTemplate->set_var('error_msg',$UploadErrors);
    }
    if ( !isset($postData['date']) ) {
        $postData['date'] = time();
    }

    $filter = sanitizer::getInstance();
    $AllowedElements = $filter->makeAllowedElements($_FF_CONF['allowed_html']);
    $filter->setAllowedelements($AllowedElements);
    $filter->setNamespace('forum','post');
    $filter->setPostmode($postData['postmode']);

    $postData['comment'] = $filter->filterHTML($postData['comment']);
    FF_showtopic($postData,'preview',1,0,$previewTemplate);
    $previewTemplate->parse ('output', 'preview');

    $retval .= $previewTemplate->finish ($previewTemplate->get_var('output'));

    return $retval;
}

function _ff_chknotifications($forumid,$topicid,$userid,$type='topic') {
    global $_TABLES,$LANG_GF01,$LANG_GF02,$_CONF,$_FF_CONF;

    $msgData = array();

    if (!$_FF_CONF['allow_notification']) {
        return;
    }

    $pid = DB_getItem($_TABLES['ff_topic'],'pid','id='.(int) $topicid);
    if ($pid == 0 || $pid == '') {
      $pid = $topicid;
    }
    $grp_id = DB_getItem($_TABLES['ff_forums'],'grp_id','forum_id='. (int) $forumid);
    if ( $grp_id == 0 || $grp_id == '' ) {
        $grp_id = 1;
    }

    $sql = "SELECT * FROM {$_TABLES['subscriptions']} WHERE type='forum' AND ((category=".(int)$forumid." AND id=".(int) $pid.") OR (category=".(int) $forumid." AND id=0 )) GROUP BY uid";

    $sqlresult = DB_query($sql);
    $postername = COM_getDisplayName($userid);
    $nrows = DB_numRows($sqlresult);

    $messageBody = '';
    if ( $nrows > 0 ) { // we have some subscription records, build the emails
        $topicrec = DB_query("SELECT subject,name,forum,last_reply_rec FROM {$_TABLES['ff_topic']} WHERE id=".(int)$pid);
        $A = DB_fetchArray($topicrec);
        $forum_name = DB_getItem($_TABLES['ff_forums'],'forum_name',"forum_id=". (int) $forumid);
        if ($type=='forum') {
            $digestSubject = $forum_name;
            $digestSubject .= ": ";
            $digestSubject .= $A['subject'];
            $messageBody .= sprintf($LANG_GF02['msg23a'],$A['subject'],$postername, $A['name'],$_CONF['site_name']);
            $last_reply_rec = DB_getItem($_TABLES['ff_forums'],'last_post_rec',"forum_id=".(int) $forumid);
        } else {
            if ( $A['last_reply_rec'] != '' && $A['last_reply_rec'] != 0 ) {
                $last_reply_rec = $A['last_reply_rec'];
            } else {
                $last_reply_rec = $topicid;
            }
            $digestSubject = $forum_name;
            $digestSubject .= ": RE: ";
            $digestSubject .= $A['subject'];
            $messageBody .= sprintf($LANG_GF02['msg23b'],$A['subject'],$A['name'],$forum_name, $_CONF['site_name'],$_CONF['site_url'],$pid);
//            $messageBody .= sprintf($LANG_GF02['msg23a'],$A['subject'],$postername, $A['name'],$_CONF['site_name']);
            $messageBody .= sprintf($LANG_GF02['msg23c'],$_CONF['site_url'],$pid,$last_reply_rec);
        }
        $messageBody .= $LANG_GF02['msg26'];
        $messageBody .= sprintf($LANG_GF02['msg27'],"{$_CONF['site_url']}/forum/notify.php");
        $messageBody .= "{$LANG_GF02['msg25']}{$_CONF['site_name']} {$LANG_GF01['ADMIN']}\n";
        list($digestMessage,$digestMessageText) = gfm_getoutput($last_reply_rec);
    } else {
        return;
    }
    $msgDataDigest['subject']     = $digestSubject;
    $msgDataDigest['from']        = $_CONF['noreply_mail'];
    $msgDataDigest['htmlmessage'] = $digestMessage;
    $msgDataDigest['textmessage'] = $digestMessageText;
    $toDigest = array();

    $msgDataNotify['subject'] = "{$_CONF['site_name']} {$LANG_GF02['msg22']}";
    $msgDataNotify['from']    = $_CONF['noreply_mail'];
    $msgDataNotify['textmessage'] = $messageBody;
    $toNotify = array();

    for ($i =1; $i <= $nrows; $i++) {
        $N = DB_fetchArray($sqlresult);
        // Don't need to send a notification to the user that posted this message and users with NOTIFY disabled
        if ( $N['uid'] > 1 AND $N['uid'] != $userid ) {
            // if the topic_id is 0 for this record - user has subscribed to complete forum. Check if they have opted out of this forum topic.
            if (DB_count($_TABLES['subscriptions'],array('type','uid','category','id'),array('forum',$N['uid'],$forumid,-$topicid)) == 0) {
                // Check if user does not want to receive multiple notifications for same topic and already has been notified
                $userNotifyOnceOption = DB_getItem($_TABLES['ff_userprefs'],'notify_once',"uid=".(int)$N['uid']);
                // Retrieve the log record for this user if it exists then check if user has viewed this topic yet
                // The logtime value may be 0 which indicates the user has not yet viewed the topic
                $lsql = DB_query("SELECT time FROM {$_TABLES['ff_log']} WHERE uid=".(int)$N['uid']." AND forum=".(int)$forumid." AND topic=".(int)$topicid);
                if (DB_numRows($lsql) == 1) {
                    $nologRecord = false;
                    list ($logtime) = DB_fetchArray($lsql);
                } else {
                    $nologRecord = true;
                    $logtime = 0;
                }

                if  ($userNotifyOnceOption == 0 OR ($userNotifyOnceOption == 1 AND ($nologRecord OR $logtime != 0)) ) {
                    $userrec = DB_query("SELECT username,email,status FROM {$_TABLES['users']} WHERE uid=".(int)$N['uid']);
                    $B = DB_fetchArray($userrec);

                    if ($B['status'] == USER_ACCOUNT_ACTIVE && SEC_inGroup($grp_id,(int)$N['uid'])) {
                        if ($nologRecord and $userNotifyOnceOption == 1 ) {
                            DB_query("INSERT INTO {$_TABLES['ff_log']} (uid,forum,topic,time) VALUES (".(int)$N['uid'].", ".(int) $forumid.", ".(int)$topicid.",'0') ");
                        }
                        $notifyfull = DB_getItem($_TABLES['ff_userprefs'],'notify_full',"uid=".(int)$N['uid']);
                        if ( $notifyfull ) {
                            $toDigest[] = $B['email'];
                        } else {
                            $toNotify[] = $B['email'];
                        }
                    } else {
                        // remove the watch entry since this user can no longer access the forum.
                        DB_query("DELETE FROM {$_TABLES['subscriptions']} WHERE type='forum' AND uid=".$N['uid']." AND ((id=".(int) $pid.") OR ((category=".(int) $forumid.") AND (id=0) ))");
                    }
                }
            }
        }
    }
    $msgDataDigest['to'] = $toDigest;
    $msgDataNotify['to'] = $toNotify;
    COM_emailNotification($msgDataDigest);
    COM_emailNotification($msgDataNotify);
}
?>