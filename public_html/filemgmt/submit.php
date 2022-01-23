<?php
/**
* glFusion CMS - FileMgmt Plugin
*
* User File Submission
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2022 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by the following authors:
*   Authors: Blaine Lang            blaine@portalparts.com
*
*  Based on:
*    myPHPNUKE Web Portal System - http://myphpnuke.com/
*    PHP-NUKE Web Portal System - http://phpnuke.org/
*    Thatware - http://thatware.org/
*/

require_once '../lib-common.php';
use \glFusion\FileSystem;
use \glFusion\Log\Log;
use Filemgmt\XoopsTree;
use Filemgmt\MyTextSanitizer;
use Filemgmt\Models\Status;

function FM_notifyAdmins( $filename,$file_user_id,$description ) {
    global $LANG_DIRECTION, $LANG_CHARSET, $LANG_FM00, $_USER, $_FM_CONF, $_CONF, $_TABLES;

    $html = false;
    $altBody = '';
    $to = array();
    $body = '';

    $description = stripslashes($description); // already escaped for db

    if( empty( $LANG_DIRECTION )) {
        // default to left-to-right
        $direction = 'ltr';
    } else {
        $direction = $LANG_DIRECTION;
    }
    if( empty( $LANG_CHARSET )) {
        $charset = $_CONF['default_charset'];
        if( empty( $charset )) {
            $charset = 'iso-8859-1';
        }
    } else {
        $charset = $LANG_CHARSET;
    }

    COM_clearSpeedlimit(300,'fmnotify');
    $last = COM_checkSpeedlimit ('fmnotify');
    if ( $last == 0 ) {
        $html = true;
        $subject = $LANG_FM00['new_upload'] . $_CONF['site_name'];

        if (!isset($file_user_id) || $file_user_id < 2  ) {
            $uname = 'Anonymous';
        } else {
            $uname = DB_getItem($_TABLES['users'],'username','uid=' . intval($file_user_id));
        }
        // build the template...
        $T = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
        $T->set_file ('email', 'notifyemail.thtml');

        $T->set_var(array(
            'direction'         =>  $direction,
            'charset'           =>  $charset,
            'lang_new_upload'   =>  $LANG_FM00['new_upload_body'],
            'lang_details'      =>  $LANG_FM00['details'],
            'lang_filename'     =>  $LANG_FM00['filename'],
            'lang_uploaded_by'  =>  $LANG_FM00['uploaded_by'],
            'username'          =>  $uname,
            'filename'          =>  $filename,
            'description'       =>  $description,
            'url_moderate'      =>  '<a href="' . $_FM_CONF['admin_url'] . '/index.php?op=listNewDownloads">Click here to view</a>',
            'site_name'         =>  $_CONF['site_name'] . ' - ' . $_CONF['site_slogan'],
            'site_url'          =>  $_CONF['site_url'],
        ));
        $T->parse('output','email');
        $body .= $T->finish($T->get_var('output'));

        $altbody  = $LANG_FM00['new_upload_body'] . $_CONF['site_name'];
        $altbody .= "\n\r\n\r";
        $altbody .= $LANG_FM00['details'];
        $altbody .= "\n\r";
        $altbody .= $LANG_FM00['filename'] . ' ' . $filename . "\n\r";
        $altbody .= "\n\r";
        $altbody .= $description . "\n\r";
        $altbody .= "\n\r";
        $altbody .= $LANG_FM00['uploaded_by'] . ' ' . $uname . "\n\r";
        $altbody .= "\n\r\n\r";
        $altbody .= $_CONF['site_name'] . "\n\r";
        $altbody .= $_CONF['site_url'] . "\n\r";

        $group_id = DB_getItem($_TABLES['groups'],'grp_id','grp_name="filemgmt Admin"');

        $groups = FM_getGroupList($group_id);
        if ( count ($groups) == 0 ) {
            $groupList = '1';
        } else {
            $groupList = implode(',',$groups);
        }
	    $sql = "SELECT DISTINCT {$_TABLES['users']}.uid,username,fullname,email "
	          ."FROM {$_TABLES['group_assignments']},{$_TABLES['users']} "
	          ."WHERE {$_TABLES['users']}.uid > 1 "
	          ."AND {$_TABLES['users']}.uid = {$_TABLES['group_assignments']}.ug_uid "
	          ."AND ({$_TABLES['group_assignments']}.ug_main_grp_id IN ({$groupList}))";

        $result = DB_query($sql);
        $nRows = DB_numRows($result);
        $toCount = 0;
        for ($i=0;$i < $nRows; $i++ ) {
            $row = DB_fetchArray($result);
            if ( $row['email'] != '' ) {
    			Log::write('system',Log::ERROR,'FileMgmt Upload: Sending notification email to: ' . $row['email'] . ' - ' . $row['username']);
                $toCount++;
                $to[] = array('email' => $row['email'],'name' => $row['username']);
            }
        }
        if ( $toCount > 0 ) {
            $msgData['htmlmessage'] = $body;
            $msgData['textmessage'] = $altBody;
            $msgData['subject'] = $subject;
            $msgData['from']['email'] = $_CONF['site_mail'];
            $msgData['from']['name'] = $_CONF['site_name'];
            $msgData['to'] = $to;
            COM_emailNotification( $msgData );
    	} else {
        	Log::write('system',Log::ERROR,'FileMgmt Upload: Error - Did not find any administrators to notify of new upload');
    	}
        COM_updateSpeedlimit ('fmnotify');
    }
    return true;
}


/**
* Get a list (actually an array) of all groups this group belongs to.
*
* @param   basegroup   int     id of group
* @return              array   array of all groups 'basegroup' belongs to
*
*/
function FM_getGroupList ($basegroup)
{
    global $_TABLES;

    $to_check = array ();
    array_push ($to_check, $basegroup);

    $checked = array ();

    while (sizeof ($to_check) > 0) {
        $thisgroup = array_pop ($to_check);
        if ($thisgroup > 0) {
            $result = DB_query ("SELECT ug_grp_id FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = ".(int) $thisgroup);
            $numGroups = DB_numRows ($result);
            for ($i = 0; $i < $numGroups; $i++) {
                $A = DB_fetchArray ($result);
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

if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    //catch file overload error...
    $postMax = ini_get('post_max_size'); //grab the size limits...
    $uploadMax = ini_get('upload_max_filesize');

    if (intval($postMax) <= intval($uploadMax)) {
        $maxSize = $postMax;
    } else {
        $maxSize = $uploadMax;
    }

    COM_setMsg(sprintf($LANG_FILEMGMT_ERRORS['1111'],$maxSize),'error');
    echo COM_refresh($_CONF['site_url'].'/filemgmt/subit.php');
    exit;
}

$content = '';

if (defined('DEMO_MODE') ) {
    COM_setMsg('Uploads are disabled in demo mode', 'error');
    COM_refresh($_FM_CONF['url'] . '/index.php');
    exit;
}

if ( isset($_USER['uid']) ) {
    $uid = $_USER['uid'];
} else {
    $uid = 1;
}

if (Filemgmt\Download::canSubmit()) {
    $groupsql = SEC_buildAccessSql();
    $sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_cat']} WHERE pid=0 ";
    $sql .= $groupsql;
    list($catAccessCnt) = DB_fetchArray( DB_query($sql));

    if ( $catAccessCnt < 1 ) {
        Log::write('system',Log::ERROR,'Submit.php => FileMgmt Plugin Access denied. Attempted user upload of a file, Remote address is: '.$_SERVER['REAL_ADDR']);
        COM_setMsg(_GL_ERRORNOUPLOAD, 'error');
        COM_refresh($_CONF['site_url'] . '/index.php');
        exit;
    }

    if ( isset($_POST['submit']) && SEC_checkToken() ){

        if (!COM_isAnonUser() ) {
            $_POST['submitter'] = (int) $_USER['uid'];
        } else {
            $_POST['submitter'] = 1;
        }

        $File = new Filemgmt\Download;
        $status = $File->Save($_POST);
        $redirect = $_FM_CONF['url'] . '/index.php';    // default after-save
        switch($status) {
        case Status::UPL_NODEMO:
            COM_setMsg('Uploads are disabled in demo mode', 'error');
            break;
        case Status::UPL_DUPFILE:
            COM_setMsg(_MD_NEWDLADDED_DUPFILE, 'error');
            break;
        case Status::UPL_DUPSNAP:
            COM_setMsg(_MD_NEWDLADDED_DUPSNAP, 'error');
            break;
        case Status::OK:
        case Status::UPL_OK;
            COM_setMsg(_MD_NEWDLADDED,'info',1);
            break;
        case Status::UPL_PENDING:
            COM_setMsg(_MD_RECEIVED . '<br />' . _MD_WHENAPPROVED, 'success');
            break;
        case Status::UPL_MISSING:
            // Message set in Download class for missing fields.
            $content .= $File->asSubmission()->edit();
            $redirect = '';
            break;
        case Status::UPL_ERROR:
        default:
            COM_setMsg(_MD_ERRUPLOAD, 'error');
            break;
        }

        if (!empty($redirect)) {
            COM_refresh($redirect);
        }
    } else {
        // Redisplay the form if there were validation errors
        $File = new Filemgmt\Download;
        $content .= COM_startBlock("<b>". _MD_UPLOADTITLE ."</b>");
        $content .= $File->asSubmission()->edit($_POST);
        $content .= COM_endBlock();
    }

} else {
    Log::write('system',Log::ERROR,'Submit.php => FileMgmt Plugin Access denied. Attempted user upload of a file, Remote address is: '.$_SERVER['REAL_ADDR']);
    COM_setMsg(_GL_ERRORNOUPLOAD, 'error');
    COM_refresh($_CONF['site_url'] . '/index.php');
}
echo Filemgmt\Menu::siteHeader();
echo $content;
echo Filemgmt\Menu::siteFooter();
