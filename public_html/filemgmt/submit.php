<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | submit.php                                                               |
// |                                                                          |
// | Allow users to submit new downloads                                      |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the FileMgmt Plugin for Geeklog                                 |
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

    require_once $_CONF['path'].'lib/phpmailer/class.phpmailer.php';

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
        $mail = new PHPMailer();
        $mail->CharSet = $charset;
        if ($_CONF['mail_backend'] == 'smtp' ) {
            $mail->Host     = $_CONF['mail_smtp_host'] . ':' . $_CONF['mail_smtp_port'];
            $mail->SMTPAuth = $_CONF['mail_smtp_auth'];
            $mail->Username = $_CONF['mail_smtp_username'];
            $mail->Password = $_CONF['mail_smtp_password'];
            $mail->Mailer = "smtp";
        } elseif ($_CONF['mail_backend'] == 'sendmail') {
            $mail->Mailer = "sendmail";
            $mail->Sendmail = $_CONF['mail_sendmail_path'];
        } else {
            $mail->Mailer = "mail";
        }
        $mail->WordWrap = 76;
        $mail->IsHTML(true);
        $mail->Subject = $LANG_FM00['new_upload'] . $_CONF['site_name'];

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
        $mail->Body    = $body;

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

        $mail->AltBody = $altbody;

        $mail->From = $_CONF['site_mail'];
        $mail->FromName = $_CONF['site_name'];

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
                $mail->AddAddress($row['email'], $row['username']);
            }
        }
        if ( $toCount > 0 ) {
        	if(!$mail->Send()) {
            	COM_errorLog("FileMgmt Upload: Unable to send moderation email - error:" . $mail->ErrorInfo);
        	}
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

    if ( isset($_POST['submit']) ){

        if(isset($_USER['uid']) AND $_USER['uid'] > 1 ) {
            $submitter = (int) $_USER['uid'];
        }else{
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
            $name = $myts->makeTboxData4Save($name);
            $url = $myts->makeTboxData4Save($url);
        } else {
            $eh->show("1016");
        }

        // Check if Description entered
        if ($_POST['description'] == '') {
            $eh->show("1008");
        }

        $uploadfilename = $myts->makeTboxData4Save($_FILES['newfile']['name']);

        // Check if file is already on file
        if (DB_COUNT($_TABLES['filemgmt_filedetail'], 'url', $uploadfilename) > 0) {
            $eh->show("1108");
        }

        if ( !empty($_POST['cid']) ) {
            $cid = (int) COM_applyFilter($_POST['cid'],true);
        } else {
            $cid = 0;
        }

        $AddNewFile = false;    // Set true if fileupload was sucessfull
        $name = $myts->makeTboxData4Save($name);
        $title = $myts->makeTboxData4Save($_POST['title']);
        $homepage = $myts->makeTboxData4Save($_POST['homepage']);
        $version = $myts->makeTboxData4Save($_POST['version']);
        $size = intval($_FILES['newfile']['size']);
        $description = $myts->makeTareaData4Save($_POST['description']);
        $comments = (int) COM_applyFilter($_POST['commentoption'],true);
        $date = time();
        $tmpfilename = randomfilename();

        // Determine write group access to this category
        $grp_writeaccess = DB_getItem($_TABLES['filemgmt_cat'],'grp_writeaccess',"cid=$cid");
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
            if (is_uploaded_file ($tmp)) {                               // is this temporary file really uploaded?
                if ($directUploadAccess) {
                    $returnMove = move_uploaded_file($tmp, "{$filemgmt_FileStore}{$name}");             // move file to your upload directory
                } else {
                    $returnMove = move_uploaded_file($tmp, $filemgmt_FileStore."tmp/".$tmpfilename);    // move temporary file to your upload directory
                    FM_notifyAdmins($name,$submitter,$description);
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
        }

        // Upload New file snapshot image  - but only is file was uploaded ok
        $uploadfilename = $myts->makeTboxData4Save($_FILES['newfileshot']['name']);
        if ( $uploadfilename != '' AND $AddNewFile ) {
            $shotname = $uploadfilename;
            $logourl = rawurlencode($shotname);
            $shotname = $myts->makeTboxData4Save($shotname);
            $logourl = $myts->makeTboxData4Save($logourl);
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

                    /* Need to also rename the uploaded filename or URL that will be used for the approval name */
                    /* Grab the filename without extension and add the mapped extension */
                    $pos = strrpos($logourl,'.') + 1;
                    $logourl = strtolower(substr($logourl, 0,$pos)) . $fileExtension;
                }
            } else {
                $tmpshotname = $tmpshotname . ".$fileExtension";
            }
            // Append the temporary name for the file, using a ; as delimiter. We will be able to store both names in one field
            $tmpfilename .= ';'.$tmpshotname;

            if (is_uploaded_file ($tmp)) {
                if ($directUploadAccess) {
                    $returnMove = move_uploaded_file($tmp, "{$filemgmt_SnapStore}{$tmpshotname}");    // move temporary snapfile to your upload directory
                } else {
                    $returnMove = move_uploaded_file($tmp, "{$filemgmt_SnapStore}tmp/{$tmpshotname}");    // move temporary snapfile to your upload directory
                }
                if (!$returnMove) {
                    COM_errorLOG("Filemgmt submit error: Temporary file could not be created: ".$tmp." to ".$filemgmt_SnapStore."tmp/".$tmpshotname);
                    $AddNewFile = false;    // Set false again - in case it was set true above for actual file
                    $eh->show("1102");
                } else {
                    $AddNewFile = true;
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
                CACHE_remove_instance('whatsnew');
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

        $display .= FM_siteHeader();
        $display .= COM_startBlock("<b>". _MD_UPLOADTITLE ."</b>");
        $display .= "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"8\" class=\"plugin\"><tr><td style=\"padding-top:10px;padding-left:50px;\">\n";
        $display .= "<ul><li>"._MD_SUBMITONCE."<b>&nbsp;&nbsp;".'(max:'."&nbsp;" . ini_get('upload_max_filesize') . ')' . "</b> </li>\n";
        $display .= "<li>"._MD_ALLPENDING."</li>\n";
        $display .= "<li>"._MD_DONTABUSE."</li>\n";
        $display .= "<li>"._MD_TAKEDAYS."</li>\n";
        $display .= "<li>"._MD_REQUIRED."</li></ul>\n";

        $display .= "<form action=\"submit.php\" method=\"post\" enctype='multipart/form-data'> \n";
        $display .= "<table width=\"80%\"><tr>";
        $display .= "<td align=\"right\" style=\"white-space:nowrap;\"><b>"._MD_FILETITLE."</b></td><td>";
        $display .= "<input type=\"text\" name=\"title\" size=\"50\" maxlength=\"100\"" . XHTML . ">";
        $display .= "</td></tr><tr><td align=\"right\" style=\"white-space:nowrap;\"><b>"._MD_DLFILENAME."</b></td><td>";
        $display .= "<input type=\"file\" name=\"newfile\" size=\"50\" maxlength=\"100\"" . XHTML . ">";
        $display .= "</td></tr>";
        $display .= "<tr><td align=\"right\" style=\"white-space:nowrap;\"><b>"._MD_CATEGORY."</b></td><td>";

        $sql = "SELECT cid,title,grp_writeaccess FROM {$_TABLES['filemgmt_cat']} WHERE pid=0 ";
        if (count($_GROUPS) == 1) {
            $sql .= " AND grp_access = '" . current($_GROUPS) ."' ";
        } else {
            $sql .= " AND grp_access IN (" . implode(',',array_values($_GROUPS)) .") ";
        }
        $sql .= "ORDER BY cid";
        $query = DB_query($sql);
        $categorySelectHTML = '<select name="cid">';
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
        $categorySelectHTML .= '</select>';

        $display .= $categorySelectHTML;
        $display .= '<span class="pluginTinyText" style="padding-left:5px;">' ._MD_APPROVEREQ ."</span></td></tr>\n";

        $display .= "<tr><td align=\"right\" style=\"white-space:nowrap;\"><b>"._MD_HOMEPAGEC."</b></td><td>\n";
        $display .= "<input type=\"text\" name=\"homepage\" size=\"50\" maxlength=\"100\"" . XHTML . "></td></tr>\n";
        $display .= "<tr><td align=\"right\" style=\"white-space:nowrap;\"><b>"._MD_VERSIONC."</b></td><td>\n";
        $display .= "<input type=\"text\" name=\"version\" size=\"10\" maxlength=\"10\"" . XHTML . "></td></tr>\n";
        $display .= "<tr><td align=\"right\" valign=\"top\" style=\"white-space:nowrap;\"><b>"._MD_DESCRIPTIONC."</b></td><td>\n";
        $display .= "<textarea name=\"description\" cols=\"50\" rows=\"6\"></textarea>\n";
        $display .= "</td></tr>\n";
        $display .= "<tr><td align=\"right\" style=\"white-space:nowrap;\"><b>"._MD_SHOTIMAGE."</b></td><td>\n";
        $display .= "<input type=\"file\" name=\"newfileshot\" size=\"50\" maxlength=\"60\"" . XHTML . "></td></tr>\n";
        $display .= "<tr><td align=\"right\"></td><td>";
        $display .= "</td></tr><tr><td style=\"text-align:right;\"><b>"._MD_COMMENTOPTION."</b></td><td>";
        $display .= "<input type=\"radio\" name=\"commentoption\" value=\"1\" checked=\"checked\"" . XHTML . ">&nbsp;" ._MD_YES."&nbsp;";
        $display .= "<input type=\"radio\" name=\"commentoption\" value=\"0\"" . XHTML . ">&nbsp;" ._MD_NO."&nbsp;";
        $display .= "</td></tr>\n";
        $display .= "</table>\n";
        $display .= "<br" . XHTML . ">";
        $display .= "<input type=\"hidden\" name=\"submitter\" value=\"".$uid."\"". XHTML. ">";
        $display .= "<center><input type=\"submit\" name=\"submit\" class=\"button\" value=\""._MD_SUBMIT."\"" . XHTML. ">\n";
        $display .= "&nbsp;<input type=\"button\" value=\""._MD_CANCEL."\" onclick=\"javascript:history.go(-1)\"" . XHTML . "></center>\n";
        $display .= "</form>\n";
        $display .= "</td></tr></table>";
        $display .= COM_endBlock();
        $display .= FM_siteFooter();
        echo $display;

    }

} else {
    COM_errorLOG("Submit.php => FileMgmt Plugin Access denied. Attempted user upload of a file, Remote address is:{$_SERVER['REMOTE_ADDR']}");
    redirect_header($_CONF['site_url']."/index.php",1,_GL_ERRORNOUPLOAD);
}

?>