<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | submit.php                                                               |
// |                                                                          |
// | Allow users to submit new downloads                                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2011 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
// |                                                                          |
// | Based on:                                                                |
// | myPHPNUKE Web Portal System - http://myphpnuke.com/                      |
// | PHP-NUKE Web Portal System - http://phpnuke.org/                         |
// | Thatware - http://thatware.org/                                          |
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
include_once $_CONF['path'].'plugins/filemgmt/include/header.php';
include_once $_CONF['path'].'plugins/filemgmt/include/functions.php';
include_once $_CONF['path'].'plugins/filemgmt/include/xoopstree.php';
include_once $_CONF['path'].'plugins/filemgmt/include/errorhandler.php';
include_once $_CONF['path'].'plugins/filemgmt/include/textsanitizer.php';


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
    			COM_errorLog("FileMgmt Upload: Sending notification email to: " . $row['email'] . " - " . $row['username']);
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
        	COM_errorLog("FileMgmt Upload: Error - Did not find any administrators to email");
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
    redirect_header($_CONF['site_url']."/filemgmt/index.php",10,'Uploads are disabled in demo mode');
    exit;
}

if ( isset($_USER['uid']) ) {
    $uid = $_USER['uid'];
} else {
    $uid = 1;
}

if (SEC_hasRights("filemgmt.upload") OR $mydownloads_uploadselect) {

    $logourl = '';

    // Get the number of files in the database and post it in the title.
    $_GROUPS = SEC_getUserGroups( $uid );

    $myts = new MyTextSanitizer; // MyTextSanitizer object
    $eh = new ErrorHandler; //ErrorHandler object
    $mytree = new XoopsTree($_DB_name,$_TABLES['filemgmt_cat'],"cid","pid");
    $mytree->setGroupAccessFilter($_GROUPS);

    $groupsql = filemgmt_buildAccessSql();
    $sql = "SELECT COUNT(*) FROM {$_TABLES['filemgmt_cat']} WHERE pid=0 ";
    $sql .= $groupsql;
    list($catAccessCnt) = DB_fetchArray( DB_query($sql));

    if ( $catAccessCnt < 1 ) {
        COM_errorLOG("Submit.php => FileMgmt Plugin Access denied. Attempted user upload of a file, Remote address is:{$_SERVER['REMOTE_ADDR']}");
        redirect_header($_CONF['site_url']."/index.php",1,_GL_ERRORNOUPLOAD);
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

        // Check if filename entered
        if ($_FILES['newfile']['name'] != '') {
            $name = ($_FILES['newfile']['name']);
            $url = rawurlencode($name);
            $name = DB_escapeString($name);
            $url = DB_escapeString($url);
        } else {
            $eh->show("1016");
        }

        // Check if Description entered
        if ($_POST['description'] == '') {
            $eh->show("1008");
        }

        $uploadfilename = DB_escapeString($_FILES['newfile']['name']);

        // Check if file is already on file
        if (DB_COUNT($_TABLES['filemgmt_filedetail'], 'url', $uploadfilename) > 0) {
            $eh->show("1108");
        }

        if ( !empty($_POST['cid']) ) {
            $cid = (int) COM_applyFilter($_POST['cid'],true);
        } else {
            $cid = 0;
            $eh->show("1109");
        }

        $AddNewFile = false;    // Set true if fileupload was sucessfull
        $name = DB_escapeString($name);
        $title = DB_escapeString($_POST['title']);
        $homepage = DB_escapeString($_POST['homepage']);
        $version = DB_escapeString($_POST['version']);
        $size = intval($_FILES['newfile']['size']);
        $description = $myts->makeTareaData4Save($_POST['description']);
        $comments = (int) COM_applyFilter($_POST['commentoption'],true);
        $date = time();
        $tmpfilename = randomfilename();

        // Determine write group access to this category
        $grp_writeaccess = DB_getItem($_TABLES['filemgmt_cat'],'grp_writeaccess',"cid=".(int) $cid);
        if (SEC_inGroup($grp_writeaccess)) {
            $directUploadAccess = true;
        } else {
            $directUploadAccess = false;
        }

        // Upload New file
        if ($uploadfilename != '') {
            $pos = strrpos($uploadfilename,'.') + 1;
            $fileExtension = strtolower(substr($uploadfilename, $pos));
            if (array_key_exists($fileExtension, $_FMDOWNLOAD)) {
                if ( $_FMDOWNLOAD[$fileExtension] == 'reject' ) {
                    COM_errorLOG("AddNewFile - New Upload file is rejected by config rule:$uploadfilename");
                    $eh->show("1109");
                } else {
                    $fileExtension = $_FMDOWNLOAD[$fileExtension];
                    $tmpfilename = $tmpfilename . ".$fileExtension";

                    /* Need to also rename the uploaded filename or URL that will be used for the approval name */
                    /* Grab the filename without extension and add the mapped extension */
                    $pos = strrpos($url,'.') + 1;
                    $url = strtolower(substr($url, 0,$pos)) . $fileExtension;

                    $pos2 = strrpos($name,'.') + 1;
                    $name = substr($name,0,$pos2) . $fileExtension;
                }
            } else {
                $tmpfilename = $tmpfilename . ".$fileExtension";
            }
            $tmp  = $_FILES["newfile"]['tmp_name'];    // temporary name of file in temporary directory on server
            $returnMove = false;
            if (isset($_FILES["newfile"]['_data_dir']) && file_exists($tmp)) {
                if ($directUploadAccess) {
                    $returnMove = @copy($tmp, "{$filemgmt_FileStore}{$name}");
                    @unlink($tmp);
                } else {
                    $returnMove = @copy($tmp, $filemgmt_FileStore."tmp/".$tmpfilename);
                    @unlink($tmp);
                    FM_notifyAdmins($name,$submitter,$description);
                }
            } elseif (is_uploaded_file ($tmp)) {                               // is this temporary file really uploaded?
                if ($directUploadAccess) {
                    $returnMove = move_uploaded_file($tmp, "{$filemgmt_FileStore}{$name}");             // move file to your upload directory
                } else {
                    $returnMove = move_uploaded_file($tmp, $filemgmt_FileStore."tmp/".$tmpfilename);    // move temporary file to your upload directory
                    FM_notifyAdmins($name,$submitter,$description);
                }
            }
            if (!$returnMove) {
                if ($directUploadAccess) {
                    COM_errorLOG("Filemgmt submit error: Direct upload, file could not be created: $tmp to {$filemgmt_FileStore}{$name}");
                } else {
                    COM_errorLOG("Filemgmt submit error: Temporary file could not be created: $tmp to {$filemgmt_FileStore}tmp}/{$tmpfilename}");
                }
                $eh->show("1102");
            } else {
                $AddNewFile = true;
            }
        }

        // Upload New file snapshot image  - but only is file was uploaded ok
        $uploadfilename = DB_escapeString($_FILES['newfileshot']['name']);
        if ( $uploadfilename != '' ) {
            $tmpshotname = randomfilename();
            $tmp = $_FILES['newfileshot']['tmp_name'];    // temporary name of file in temporary directory on server
            $pos = strrpos($uploadfilename,'.') + 1;
            $fileExtension = strtolower(substr($uploadfilename, $pos));
            if (array_key_exists($fileExtension, $_FMDOWNLOAD)) {
                if ( $_FMDOWNLOAD[$fileExtension] == 'reject' ) {
                    COM_errorLOG("AddNewFile - New Upload file snapshot is rejected by config rule:$uploadfilename");
                    $eh->show("1109");
                } else {
                    $fileExtension = $_FMDOWNLOAD[$fileExtension];
                    $tmpshotname = $tmpshotname . ".$fileExtension";
                    // Need to also rename the uploaded filename or URL that will be used for the approval name
                    // Grab the filename without extension and add the mapped extension
                    $pos = strrpos($logourl,'.') + 1;
                    $logourl = strtolower(substr($logourl, 0,$pos)) . $fileExtension;
                }
            } else {
                $tmpshotname = $tmpshotname . ".$fileExtension";
                $logourl = rawurlencode(DB_escapeString($tmpshotname));
            }
            if ( $uploadfilename != '' AND $AddNewFile ) {
                $upload = new upload();
                $upload->setFieldName('newfileshot');
                $upload->setFileNames($tmpshotname);
                $upload->setPath($filemgmt_SnapStore);
                $upload->setAllowAnyMimeType(false);
                $upload->setAllowedMimeTypes (array ('image/gif'   => '.gif',
                                                     'image/jpeg'  => '.jpg,.jpeg',
                                                     'image/pjpeg' => '.jpg,.jpeg',
                                                     'image/x-png' => '.png',
                                                     'image/png'   => '.png'
                                             )      );
                $upload->setAutomaticResize (true);
                if (isset ($_CONF['debug_image_upload']) && $_CONF['debug_image_upload']) {
                    $upload->setLogFile ($_CONF['path'] . 'logs/error.log');
                    $upload->setDebug (true);
                }
                $upload->setMaxDimensions (640,480);
                $upload->setAutomaticResize (true);
                $upload->setMaxFileSize(100000000);
                $upload->uploadFiles();
                if ($upload->areErrors()) {
                    $errmsg = "Upload Error: " . $upload->printErrors(false);
                    COM_errorLog($errmsg);
                    $logourl = '';
                    $AddNewFile = false;    // Set false again - in case it was set true above for actual file
                    $eh->show("1102");
                }
            }
        }

        if ($AddNewFile){
            if ($directUploadAccess) {
                $status = 1;
            } else {
                $status = 0;
            }
            $fields = 'cid,title,url,homepage,version,size,platform,logourl,submitter,status,date,hits,rating,votes,comments';
            $sql = "INSERT INTO {$_TABLES['filemgmt_filedetail']} ($fields) VALUES ";
            $sql .= "($cid,'$title','$url','$homepage','$version','$size','$tmpfilename','$logourl',$submitter,$status,'$date',0,0,0,$comments)";
            DB_query($sql) or $eh->show("0013");
            $newid = DB_insertID();
            DB_query("INSERT INTO {$_TABLES['filemgmt_filedesc']} (lid, description) VALUES ($newid, '$description')") or $eh->show("0013");
            if ($directUploadAccess) {
				PLG_itemSaved($newid,'filemgmt');
                $c = glFusion\Cache::getInstance()->deleteItemsByTag('whatsnew');
                redirect_header("index.php",2,_MD_FILEAPPROVED);
            } else {
                redirect_header("index.php",2,_MD_RECEIVED."<br>"._MD_WHENAPPROVED."");
            }
            exit();
        } else {
            redirect_header("index.php",2,_MD_ERRUPLOAD."");
            exit();
        }

    } else {

        $T = new Template($_CONF['path'] . 'plugins/filemgmt/templates');
        $T->set_file('page', 'upload.thtml');

        $categorySelectHTML = '';
        $sql = "SELECT cid,title,grp_writeaccess FROM {$_TABLES['filemgmt_cat']} WHERE pid=0 ";
        if (count($_GROUPS) == 1) {
            $sql .= " AND grp_access = '" . current($_GROUPS) ."' ";
        } else {
            $sql .= " AND grp_access IN (" . implode(',',array_values($_GROUPS)) .") ";
        }
        $sql .= "ORDER BY cid";
        $query = DB_query($sql);

        while (list($cid,$title,$directUploadGroup) = DB_fetchArray($query)) {
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
        }

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
                    'cat_select_options'=> $categorySelectHTML,
                    'uid'               => $uid,
        ));

        $display .= FM_siteHeader();
        $display .= COM_startBlock("<b>". _MD_UPLOADTITLE ."</b>");

        $T->parse('output', 'page');
        $display .= $T->finish($T->get_var('output'));

        $display .= COM_endBlock();
        $display .= FM_siteFooter();
        echo $display;

    }

} else {
    COM_errorLOG("Submit.php => FileMgmt Plugin Access denied. Attempted user upload of a file, Remote address is:{$_SERVER['REMOTE_ADDR']}");
    redirect_header($_CONF['site_url']."/index.php",1,_GL_ERRORNOUPLOAD);
}

?>
