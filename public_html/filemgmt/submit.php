<?php
/**
* glFusion CMS - FileMgmt Plugin
*
* User File Submission
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
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
use Filemgmt\ErrorHandler;

/*include_once $_CONF['path'].'plugins/filemgmt/include/header.php';
include_once $_CONF['path'].'plugins/filemgmt/include/functions.php';
include_once $_CONF['path'].'plugins/filemgmt/include/xoopstree.php';
include_once $_CONF['path'].'plugins/filemgmt/include/errorhandler.php';
include_once $_CONF['path'].'plugins/filemgmt/include/textsanitizer.php';
 */

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
            'url_moderate'      =>  '<a href="' . $_CONF['site_admin_url'] . '/plugins/filemgmt/index.php?op=listNewDownloads">Click here to view</a>',
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

$display = '';

if ( defined('DEMO_MODE') ) {
    COM_setMsg('Uploads are disabled in demo mode', 'error');
    COM_refresh($_FM_CONF['url'] . '/index.php');
    exit;
}

if ( isset($_USER['uid']) ) {
    $uid = $_USER['uid'];
} else {
    $uid = 1;
}

if (SEC_hasRights("filemgmt.upload") OR $_FM_CONF['uploadselect']) {

    $myts = new MyTextSanitizer; // MyTextSanitizer object
    $eh = new ErrorHandler; //ErrorHandler object
    $mytree = new XoopsTree($_DB_name,$_TABLES['filemgmt_cat'],"cid","pid");
    $mytree->setGroupAccessFilter($_GROUPS);

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

    if ( isset($_POST['submit']) && SEC_checkToken()){

        if (!COM_isAnonUser() ) {
            $submitter = (int) $_USER['uid'];
        } else {
            $submitter = 1;
        }
        // Check if Title entered
        if (!isset($_POST['title']) || $_POST["title"] == '') {
            $eh->show("1001");
        }

        $File = new Filemgmt\Download;
        $File->Save($_POST);
        COM_refresh($_FM_CONF['url']);
    } else {

        $T = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
        $T->set_file('page', 'upload.thtml');

        /*$categorySelectHTML = '';
        $sql = "SELECT cid,title,grp_writeaccess FROM {$_TABLES['filemgmt_cat']} WHERE pid=0 ";
        if (count($_GROUPS) == 1) {
            $sql .= " AND grp_access = '" . current($_GROUPS) ."' ";
        } else {
            $sql .= " AND grp_access IN (" . implode(',',array_values($_GROUPS)) .") ";
        }*/
        /*$sql .= "ORDER BY cid";
        $query = DB_query($sql);
            $categorySelectHTML = $mytree->makeMySelBox("title", "title", 1, 0,"cid");
         */
        /*while (list($cid,$title,$directUploadGroup) = DB_fetchArray($query)) {
            $categorySelectHTML .= '<option value="'.$cid.'">';
            if (!SEC_inGroup($directUploadGroup)) {
                $categorySelectHTML .= "$title *";
            } else {
                $categorySelectHTML .= "$title";
            }
            $categorySelectHTML .= "</option>\n";
            $arr = $mytree->getChildTreeArray($cid);
            foreach ( $arr as $option ) {
                $option['prefix'] = str_replace(".","--",$option['prefix']);
                $catpath = $option['prefix']."&nbsp;".$myts->makeTboxData4Show($option[2]);
                $categorySelectHTML .= '<option value="'.$option[$mytree->id] . '">';
                if (!SEC_inGroup($option[5])) {
                    $categorySelectHTML .= "$catpath *";
                } else {
                    $categorySelectHTML .= "$catpath";
                }
                $categorySelectHTML .= "</option>\n";
            }
            }*/

        $T->set_var(array(
            'lang_submitnotice' => _MD_SUBMITONCE,
            'lang_allpending'   => _MD_ALLPENDING,
            'lang_dontabuse'    => _MD_DONTABUSE,
            'lang_takedays'     => _MD_TAKEDAYS,
            'lang_required'     => _MD_REQUIRED,
            'lang_filetitle'    => _MD_FILETITLE,
            'lang_filename'     => _MD_DLFILENAME,
            'lang_category'     => _MD_CATEGORY,
            'lang_approve'      => _MD_APPROVEREQ,
            'lang_homepage'     => _MD_HOMEPAGEC,
            'lang_version'      => _MD_VERSIONC,
            'lang_desc'         => _MD_DESCRIPTIONC,
            'lang_screenshot'   => _MD_SHOTIMAGE,
            'lang_commentoption'=> _MD_COMMENTOPTION,
            'lang_no'           => _MD_NO,
            'lang_yes'          => _MD_YES,
            'lang_submit'       => _MD_SUBMIT,
            'lang_cancel'       => _MD_CANCEL,
            'token_name'        => CSRF_TOKEN,
            'security_token'    => SEC_createToken(),
            'cat_select_options'=> $mytree->makeMySelBoxNoHeading("title", "title", 1, 0,"cid"),
            'uid'               => $uid,
        ));

        $display .= Filemgmt\Menu::siteHeader();
        $display .= COM_startBlock("<b>". _MD_UPLOADTITLE ."</b>");

        $T->parse('output', 'page');
        $display .= $T->finish($T->get_var('output'));

        $display .= COM_endBlock();
        $display .= Filemgmt\Menu::siteFooter();
        echo $display;

    }

} else {
    Log::write('system',Log::ERROR,'Submit.php => FileMgmt Plugin Access denied. Attempted user upload of a file, Remote address is: '.$_SERVER['REAL_ADDR']);
    COM_setMsg(_GL_ERRORNOUPLOAD, 'error');
    COM_refresh($_CONF['site_url'] . '/index.php');
}
