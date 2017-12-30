<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | postcard.php                                                             |
// |                                                                          |
// | Allows users to send electronic postcards of images                      |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}
if ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )  {
    $display = MG_siteHeader();
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    echo $display;
    exit;
}

require_once $_CONF['path'].'plugins/mediagallery/include/init.php';
MG_initAlbums();

function MG_previewPostCard() {
    global $MG_albums, $_MG_CONF, $_CONF, $_TABLES, $_USER, $LANG_MG03, $LANG_ACCESS, $LANG_MG00;

    $mid        = COM_sanitizeID(COM_applyFilter($_POST['mid'],true));
    $toname     = COM_applyFilter($_POST['toname']);
    $toemail    = COM_applyFilter($_POST['toemail']);
    $fromname   = COM_applyFilter($_POST['fromname']);
    $fromemail  = COM_applyFilter($_POST['fromemail']);
    $subject    = strip_tags(COM_checkWords($_POST['subject']));
    $message    = nl2br(htmlspecialchars(strip_tags(COM_checkWords($_POST['message']))));
    $ccself     = isset($_POST['ccself']) ? 1 : 0;

    // do some validation
    $errMsg = '';
    if ( !COM_isEmail( $toemail )) {
        $errMsg .= $LANG_MG03['invalid_to_email'] . '<br/>';
        $toemail = '<span style="color:#ff0000;">' . $LANG_MG03['invalid_email'] . '</span>';

    }
    if ( !COM_isEmail( $fromemail) ) {
        $errMsg .= $LANG_MG03['invalid_from_email'] . '<br/>';
    }
    if (empty($subject)) {
        $errMsg .= $LANG_MG03['invalid_subject'] . '<br/>';
    }
    if (empty($message)) {
        $errMsg .= $LANG_MG03['invalid_message'] . '<br/>';
    }

    $retval = '';

    $aid  = DB_getItem($_TABLES['mg_media_albums'], 'album_id','media_id="' . DB_escapeString($mid) . '"');

    if ( $MG_albums[$aid]->access == 0 || $MG_albums[$aid]->enable_postcard == 0 || (COM_isAnonUser() && $MG_albums[$aid]->enable_postcard != 2)) {
        $retval  = MG_siteHeader();
        $retval .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
        $retval .= MG_siteFooter();
        echo $retval;
        exit;
    }

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma LEFT JOIN " . $_TABLES['mg_media'] . " as m " .
            " ON ma.media_id=m.media_id WHERE m.media_id='" . DB_escapeString($mid) . "'";
    $result = DB_query( $sql );
    $nRows = DB_numRows( $result );
    if ( $nRows < 1 ) {
        $retval  = MG_siteHeader();
        $retval .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
        $retval .= MG_siteFooter();
        echo $retval;
        exit;
    }
    $M = DB_fetchArray($result);

    // build the template...
    $T = new Template( MG_getTemplatePath($aid) );
    $T->set_file ('postcard', 'pc_preview.thtml');

    $media_image = '';
    $media_disp_image = '';
    foreach ($_MG_CONF['validExtensions'] as $ext ) {
        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $M['media_filename'][0] . '/' . $M['media_filename'] . $ext) ) {
            $media_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $M['media_filename'][0] . '/' . $M['media_filename'] . $ext;
            $media_disp_image = $_MG_CONF['mediaobjects_url'] . '/disp/' . $M['media_filename'][0] . '/' . $M['media_filename'] . $ext;
            break;
        }
    }
    $media_size        = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $M['media_filename'][0] . '/' . $M['media_filename'] . $ext);

    $T->set_var(array(
        's_form_action'     =>  $_MG_CONF['site_url'] . '/postcard.php',
        'mid'               =>  $mid,
        'alt_media_title'   =>  htmlspecialchars(strip_tags($M['media_title'])),
        'media_title'       =>  $M['media_title'],
        'media_description' =>  $M['media_desc'],
        'media_url'         =>  $_MG_CONF['site_url'] . '/media.php?s=' . $mid,
        'media_image'       =>  $media_disp_image, // $_MG_CONF['mediaobjects_url'] . '/disp/' . $M['media_filename'][0] . '/' . $M['media_filename'] . '.jpg',
        'site_url'          =>  $_MG_CONF['site_url'] . '/',
        'postcard_subject'  =>  $subject,
        'postcard_message'  =>  $message,
        'from_email'        =>  $fromemail,
        'site_name'         =>  $_CONF['site_name'],
        'site_slogan'       =>  $_CONF['site_slogan'],
        'to_name'           =>  $toname,
        'to_email'          =>  $toemail,
        'from_name'         =>  $fromname,
        'lang_postcard_preview'     => $LANG_MG03['postcard_preview'],
        'lang_postcard_greet'       => $LANG_MG03['postcard_greet'],
        'lang_to'           => $LANG_MG03['to'],
        'lang_from'         => $LANG_MG03['from'],
        'lang_subject'      => $LANG_MG03['subject'],
        'lang_visit'        => $LANG_MG03['visit'],
        'lang_ccself'       => $LANG_MG03['ccself'],
        'ccself_checked'    => isset($_POST['ccself']) ? ' checked="checked"' : '',

    ));

    $T->parse('output','postcard');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}

function MG_editPostCard( $mode, $mid, $msg='' ) {
    global $MG_albums, $_MG_CONF, $_CONF, $_TABLES, $_USER, $LANG_MG03, $LANG_ACCESS, $LANG_MG00;

    $retval = '';
    $errMsg = '';
    $toname = '';
    $toemail = '';
    $fromname = '';
    $fromemail = '';
    $subject = '';
    $message = '';

    if ( $mode == 'edit' ) {
        $mid        = COM_applyFilter($_POST['mid']);
        $toname     = COM_applyFilter($_POST['toname']);
        $toemail    = COM_applyFilter($_POST['toemail']);
        $fromname   = COM_applyFilter($_POST['fromname']);
        $fromemail  = COM_applyFilter($_POST['fromemail']);
        $subject    = strip_tags(COM_checkWords($_POST['subject']));
        $message    = htmlspecialchars(strip_tags(COM_checkWords($_POST['message'])));
        $ccself     = isset($_POST['ccself']) ? 1 : 0;

        // do some validation
        if ( !COM_isEmail( $toemail )) {
            $errMsg .= $LANG_MG03['invalid_to_email'] . '<br/>';
        }
        if ( !COM_isEmail( $fromemail) ) {
            $errMsg .= $LANG_MG03['invalid_from_email'] . '<br/>';
        }
        if (empty($subject)) {
            $errMsg .= $LANG_MG03['invalid_subject'] . '<br/>';
        }
        if (empty($message)) {
            $errMsg .= $LANG_MG03['invalid_message'] . '<br/>';
        }
        if ( $msg != '' ) {
            $errMsg .= $msg;
        }
    } else {
        if ( isset($_USER['fullname']) && !empty($_USER['fullname']) ) {
            $fromname = $_USER['fullname'];
        } else {
            if ( !COM_isAnonUser() ) {
                $fromname = $_USER['username'];
            } else {
                $fromname = '';
            }
        }
        if ( !COM_isAnonUser() && isset($_USER['email']) && !empty($_USER['email']) ) {
            $fromemail  = $_USER['email'];
        } else {
            $fromemail = '';
        }
    }

    $aid  = DB_getItem($_TABLES['mg_media_albums'], 'album_id','media_id="' . DB_escapeString($mid) . '"');
    if ( $MG_albums[$aid]->access == 0 || $MG_albums[$aid]->enable_postcard == 0 || (COM_isAnonUser() && $MG_albums[$aid]->enable_postcard != 2)) {
        $retval = MG_siteHeader();
        $retval .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
        $retval .= MG_siteFooter();
        echo $retval;
        exit;
    }

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma LEFT JOIN " . $_TABLES['mg_media'] . " as m " .
            " ON ma.media_id=m.media_id WHERE m.media_id='" . DB_escapeString($mid) . "'";
    $result = DB_query( $sql );
    $nRows = DB_numRows( $result );
    if ( $nRows < 1 ) {
        $retval = MG_siteHeader();
        $retval .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
        $retval .= MG_siteFooter();
        echo $retval;
        exit;
    }
    $M = DB_fetchArray($result);

    // build the template...
    $T = new Template( MG_getTemplatePath($aid) );
    $T->set_file ('postcard', 'pc_edit.thtml');

    $media_image = '';
    $media_disp_image = '';
    foreach ($_MG_CONF['validExtensions'] as $ext ) {
        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $M['media_filename'][0] . '/' . $M['media_filename'] . $ext) ) {
            $media_image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $M['media_filename'][0] . '/' . $M['media_filename'] . $ext;
            $media_disp_image = $_MG_CONF['mediaobjects_url'] . '/disp/' . $M['media_filename'][0] . '/' . $M['media_filename'] . $ext;
            break;
        }
    }


    $media_size        = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $M['media_filename'][0] . '/' . $M['media_filename'] . $ext);

    $T->set_var(array(
        's_form_action'     =>  $_MG_CONF['site_url'] . '/postcard.php',
        'mid'               =>  $mid,
        'display_url'       =>  $_MG_CONF['site_url'] . '/media.php?s=' . $mid,
        'image_url'         =>  $media_disp_image, // $_MG_CONF['mediaobjects_url'] . '/disp/' . $M['media_filename'][0] . '/' . $M['media_filename'] . '.jpg',
        'image_tn'          =>  $media_image, // $_MG_CONF['mediaobjects_url'] . '/tn/' . $M['media_filename'][0] . '/' . $M['media_filename'] . '.jpg',
        'stamp_url'         =>  $_MG_CONF['site_url'] . MG_getImageFile('stamp.jpg'),
        'border_width'      =>  $media_size[0] + 15,
        'lang_send_postcard' => $LANG_MG03['send_postcard'],
        'lang_to_name'      =>  $LANG_MG03['to_name'],
        'lang_to_email'     =>  $LANG_MG03['to_email'],
        'lang_from_name'    =>  $LANG_MG03['from_name'],
        'lang_from_email'   =>  $LANG_MG03['from_email'],
        'lang_send_to'      =>  $LANG_MG03['send_to'],
        'lang_subject'      =>  $LANG_MG03['subject'],
        'lang_send'         =>  $LANG_MG03['send'],
        'lang_cancel'       =>  $LANG_MG03['cancel'],
        'lang_preview'      =>  $LANG_MG03['preview'],
        'lang_message'      =>  $LANG_MG03['message_body'],
        'lang_send_from'    =>  $LANG_MG03['send_from'],
        'postcard_subject'  =>  $subject,
        'postcard_message'  =>  $message,
        'from_name'         =>  $fromname,
        'from_email'        =>  $fromemail,
        'to_name'           =>  $toname,
        'to_email'          =>  $toemail,
        'errMsg'            =>  $errMsg,
        'lang_ccself'       =>  $LANG_MG03['ccself'],
        'ccself_checked'    =>  (isset($_POST['ccself']) ? ' checked="checked"' : ''),
    ));

    PLG_templateSetVars ('mediagallery', $T);

    $T->parse('output','postcard');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_sendPostCard() {
    global $MG_albums, $_MG_CONF, $_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG02, $LANG_MG03, $LANG_ACCESS, $_POST;
    global $LANG_DIRECTION, $LANG_CHARSET;

    $mid        = COM_sanitizeID(COM_applyFilter($_POST['mid'],true));
    $toname     = COM_applyFilter($_POST['toname']);
    $toemail    = COM_applyFilter($_POST['toemail']);
    $fromname   = COM_applyFilter($_POST['fromname']);
    $fromemail  = COM_applyFilter($_POST['fromemail']);
    $subject    = strip_tags(COM_checkWords($_POST['subject']));
    $message    = htmlspecialchars(strip_tags(COM_checkWords($_POST['message'])));
    $ccself     = (isset($_POST['ccself']) ? 1 : 0);

    $errCount = 0;
    $msg = '';
    if ( !COM_isEmail( $toemail )) {
        $errCount++;
    }
    if ( !COM_isEmail( $fromemail) ) {
        $errCount++;
    }
    if (empty($subject)) {
        $errCount++;
    }
    if (empty($message)) {
        $errCount++;
    }
    $captchaString = isset($_POST['captcha']) ? $_POST['captcha'] : '';
    $msg = PLG_itemPreSave('mediagallery', $captchaString);
    if ( $msg != '' ) {
        $errCount++;
    }

    if ( $errCount > 0 ) {
        return (MG_editPostCard('edit',$mid,$msg));
    }

    $retval = '';

    $aid  = DB_getItem($_TABLES['mg_media_albums'], 'album_id','media_id="' . DB_escapeString($mid) . '"');

    if ( $MG_albums[$aid]->access == 0 || $MG_albums[$aid]->enable_postcard == 0 || ( COM_isAnonUser() && $MG_albums[$aid]->enable_postcard != 2)) {
        $retval = MG_siteHeader();
        $retval .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
        $retval .= MG_siteFooter();
        echo $retval;
        exit;
    }

    $sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma LEFT JOIN " . $_TABLES['mg_media'] . " as m " .
            " ON ma.media_id=m.media_id WHERE m.media_id='" . DB_escapeString($mid) . "'";
    $result = DB_query( $sql );
    $nRows = DB_numRows( $result );
    if ( $nRows < 1 ) {
        $retval = MG_siteHeader();
        $retval .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_ACCESS['accessdenied'],true,'error');
        $retval .= MG_siteFooter();
        echo $retval;
        exit;
    }
    $M = DB_fetchArray($result);

    // trim the database
    $purgeDate = time() - ($_MG_CONF['postcard_retention'] * 86400);
    DB_query("DELETE FROM {$_TABLES['mg_postcard']} WHERE pc_time < " . $purgeDate);

    // save this one in the database

    $newsubject    = DB_escapeString($subject);
    $newmessage    = DB_escapeString($message);
    $pcId       = COM_makesid();
    $pc_time    = time();
    if (COM_isAnonUser() ) {
        $uid = 1;
    } else {
        $uid        = (int) $_USER['uid'];
    }

    $sql = "INSERT INTO {$_TABLES['mg_postcard']} (pc_id,mid,to_name,to_email,from_name,from_email,subject,message,pc_time,uid) VALUES ('$pcId','".DB_escapeString($mid)."','".DB_escapeString($toname)."','".DB_escapeString($toemail)."','".DB_escapeString($fromname)."','".DB_escapeString($fromemail)."','$newsubject','$newmessage',$pc_time,$uid)";
    $result = DB_query($sql);
    if ( DB_error() ) {
        COM_errorLog("Media Gallery: Error saving postcard");
    }

    COM_clearSpeedlimit($_CONF['commentspeedlimit'],'mgpostcard');
    $last = COM_checkSpeedlimit ('mgpostcard');
    if ( $last > 0 ) {
        $msg = sprintf($LANG_MG02['postcard_speedlimit'],$last);
        return(MG_errorHandler( $msg ));
    }
    $alternate_link = $_MG_CONF['site_url'] . '/getcard.php?id=' . $pcId;

    // build the template...
    $T = new Template( MG_getTemplatePath($aid) );
    $T->set_file ('postcard', 'postcard.thtml');

    $media_size        = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $M['media_filename'][0] . '/' . $M['media_filename'] . '.jpg');

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

    $T->set_var(array(
        's_form_action'     =>  $_MG_CONF['site_url'] . '/postcard.php',
        'direction'         =>  $direction,
        'charset'           =>  $charset,
        'mid'               =>  $mid,
        'media_title'       =>  $M['media_title'],
        'alt_media_title'   =>  htmlspecialchars(strip_tags($M['media_title'])),
        'media_description' =>  isset($M['media_description']) ? $M['media_description'] : '',
        'media_url'         =>  $_MG_CONF['site_url'] . '/media.php?s=' . $mid,
        'media_image'       =>  $_MG_CONF['mediaobjects_url'] . '/disp/' . $M['media_filename'][0] . '/' . $M['media_filename'] . '.jpg',
        'site_url'          =>  $_MG_CONF['site_url'] . '/',
        'postcard_subject'  =>  $subject,
        'postcard_message'  =>  nl2br($message),
        'from_email'        =>  $fromemail,
        'site_name'         =>  $_CONF['site_name'],
        'site_slogan'       =>  $_CONF['site_slogan'],
        'to_name'           =>  $toname,
        'from_name'         =>  $fromname,
        'pc_id'             =>  $pcId,
        'lang_to_name'      =>  $LANG_MG03['to_name'],
        'lang_to_email'     =>  $LANG_MG03['to_email'],
        'lang_from_name'    =>  $LANG_MG03['from_name'],
        'lang_from_email'   =>  $LANG_MG03['from_email'],
        'lang_subject'      =>  $LANG_MG03['subject'],
        'lang_send'         =>  $LANG_MG03['send'],
        'lang_cancel'       =>  $LANG_MG03['cancel'],
        'lang_preview'      =>  $LANG_MG03['preview'],
        'lang_unable_view'  =>  $LANG_MG03['unable_to_view_postcard'],
        'lang_postcard_from'=>  $LANG_MG03['postcard_from'],
        'lang_to'           =>  $LANG_MG03['to'],
        'lang_from'         =>  $LANG_MG03['from'],
        'lang_visit'        =>  $LANG_MG03['visit'],
    ));

    $T->parse('output','postcard');
    $retval .= $T->finish($T->get_var('output'));

    $msgData['subject'] = htmlspecialchars($subject);
    $msgData['htmlmessage'] = $retval;
    $msgData['textmessage'] = sprintf($LANG_MG03['text_body_email'], $fromname, $alternate_link);
    $msgData['from']['email'] = $fromemail;
    $msgData['from']['name'] = $fromname;
    $msgData['to'][] = array('email'=> $toemail,'name'=>$toname);
    if ( $ccself ) {
        $msgData['to'][] = array('email'=>$fromemail,'name'=>$fromname);
    }
    foreach ($_MG_CONF['validExtensions'] as $tnext ) {
        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $M['media_filename'][0] . '/' . $M['media_filename'] . $tnext) ) {
            $msgData['embeddedImage'][] = array(
                    'file' => $_MG_CONF['path_mediaobjects'] . 'disp/' . $M['media_filename'][0] . '/' . $M['media_filename'] . $tnext,
                    'name' => "pc-image",
                    'filename' => $M['media_original_filename'],
                    'encoding' => 'base64',
                    'mime'     => $M['mime_type']
            );
        }
    }
    $msgData['embeddedImage'][] = array(
            'file' => MG_getImageFilePath('stamp.gif'),
            'name' => "stamp",
            'filename' => 'stamp.gif',
            'encoding' => 'base64',
            'mime'     => 'image/gif'
    );
    COM_emailNotification( $msgData );
    $msgNo = 8;
    // update the sent post card database...Or maybe just log it in an error log?
    $logentry = $fromname . " sent a postcard to " . $toname . " (" . $toemail . ") using media id " . $mid;
    MG_postcardLog( $logentry );
    COM_updateSpeedlimit ('mgpostcard');

    header("Location: " . $_MG_CONF['site_url'] . '/media.php?msg=' . $msgNo . '&s=' . $mid);
    exit;
}

/**
* Logs message to access.log
*
* This will print a message to the glFusion access log
*
* @param        string      $string         Message to write to access log
* @see COM_errorLog
*
*/

function MG_postcardLog( $logentry )
{
    global $_CONF, $_USER, $LANG01;

    $retval = '';

    if( !empty( $logentry )) {
        $logentry = str_replace( array( '<?', '?>' ), array( '(@', '@)' ),$logentry );

        $timestamp = strftime( '%c' );
        $logfile = $_CONF['path_log'] . 'postcard.log';

        if( !$file = fopen( $logfile, 'a' )) {
            return $LANG01[33] . $logfile . ' (' . $timestamp . ')<br>' . LB;
        }

        if ( !COM_isAnonUser() ) {
            $byuser = $_USER['uid'] . '@' . $_SERVER['REMOTE_ADDR'];
        } else {
            $byuser = 'anon@' . $_SERVER['REMOTE_ADDR'];
        }

        fputs( $file, "$timestamp ($byuser) - $logentry\n" );
    }

    return $retval;
}

// --- Main Processing Loop

$mode = COM_applyFilter ($_REQUEST['mode']);

$display = '';

if ($mode == $LANG_MG03['send'] && !empty ($LANG_MG03['send'])) {
    $display .= MG_sendPostCard();
} elseif ( $mode == 'edit' ) {
    $mid = COM_sanitizeID(COM_applyFilter($_GET['mid']));
    $display .= MG_editPostCard('create',$mid);
} elseif ($mode == $LANG_MG03['preview']) {
    $mid = COM_sanitizeID(COM_applyFilter($_POST['mid']));
    $display .= MG_previewPostCard();
    $display .= '<br/><br/><br/>';
    $display .= MG_editPostCard('edit',$mid);
} elseif ($mode == $LANG_MG03['cancel']) {
    $mid = COM_sanitizeID(COM_applyFilter($_POST['mid']));
    if (!empty($mid)) {
        echo COM_refresh ($_MG_CONF['site_url'] . '/media.php?s=' . $mid);
    } else {
        echo COM_refresh ($_MG_CONF['site_url'] . '/index.php');
    }
    exit;
} else {
    echo COM_refresh ($_MG_CONF['site_url'] . '/index.php');
    exit;
}

$d2 = MG_siteHeader();
$d2 .= $display;
$d2 .= MG_siteFooter();
echo $d2;
?>