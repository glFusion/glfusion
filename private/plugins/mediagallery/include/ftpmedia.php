<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* FTP Upload
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

require_once $_CONF['path'] . 'plugins/mediagallery/include/lib-batch.php';

use \glFusion\Log\Log;

/**
* FTP Import
*
* @param    int     album_id    album_id upload media
* @return   string              HTML
*
*/
function MG_ftpUpload( $album_id ) {
    global $MG_albums, $_USER, $_CONF, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03;

    $retval = '';

    $T = new Template( MG_getTemplatePath($album_id) );
    $T->set_file ('mupload','ftpupload.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('album_id',$album_id);

    if ( $MG_albums[$album_id]->access == 3 || SEC_hasRights('mediagallery.admin') || ($MG_albums[$album_id]->member_uploads==1 && !COM_isAnonUser() )) {
        $T->set_var(array(
            's_form_action'     => $_MG_CONF['site_url'] .'/admin.php',
            'lang_upload_help'  => $LANG_MG03['upload_help'],
            'lang_media_ftp'    => $LANG_MG01['upload_media'],
            'lang_directory'    => $LANG_MG01['directory'],
            'lang_recurse'      => $LANG_MG01['recurse'],
            'lang_delete_files' => $LANG_MG01['delete_files'],
            'lang_caption'      => $LANG_MG01['caption'],
            'lang_file'         => $LANG_MG01['file'],
            'lang_description'  => $LANG_MG01['description'],
            'lang_save'         => $LANG_MG01['save'],
            'lang_cancel'       => $LANG_MG01['cancel'],
            'lang_reset'        => $LANG_MG01['reset'],
            'lang_yes'          => $LANG_MG01['yes'],
            'lang_no'           => $LANG_MG01['no'],
            'lang_ftp_help'     => $LANG_MG03['ftp_help'],
            'album_id'          => $album_id,
            'ftp_path'          => $_MG_CONF['ftp_path'],
            'action'            => 'ftp'
        ));


        $T->parse('output', 'mupload');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    } else {
        Log::write('system',Log::WARNING,'MediaGallery: user attempted to upload to a restricted album.');
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }
}

function MG_listDir ($dir, $album_id, $purgefiles, $recurse ) {
    global $album_selectbox, $MG_albums, $_FILES, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG02, $LANG_MG03, $_POST;
    global $destDirCount;
    global $pCount;

    // What we may do is scan for directories first, build that array
    // then scan for files and build that array, I always want the directories to be on the top!
    // array_multisort()

    $x = strlen($_MG_CONF['ftp_path']);
    $x--;
    if ( $_MG_CONF['ftp_path'][$x] == '/' || $_MG_CONF['ftp_path'][$x] == '\\' ) {
        $directory = $_MG_CONF['ftp_path'] . $dir;
    } else {
        $directory = $_MG_CONF['ftp_path'] . '/' . $dir;
    }

    if (!@is_dir($directory))
    {
        return(MG_errorHandler( $LANG_MG02['invalid_directory'] . '<br />' . $directory ));
    }
    if (!$dh = @opendir($directory))
    {
        return(MG_errorHandler( $LANG_MG02['directory_error']));
    }

    $directory = trim($directory);
    if ( $directory[strlen($directory)-1] != '/' ) {
        $directory =  $directory . '/';
    }

    /*
     * Currently we have disabled the selection of Root album.
     * This could cause a problem with the 'create the album structure' feature
     * Need to come up with a better way to handle this.
     */

    $level = 0;
    $album_selectbox  = '';
    if ( SEC_hasRights('mediagallery.admin') || ($_MG_CONF['member_albums'] == 1 && $_MG_CONF['member_album_root'] == 0 )) {
        $album_selectbox .= '<option value="0">' . $LANG_MG01['root_album'] . '</option>';
    }
    $MG_albums[0]->buildAlbumBox($album_id,3,-1,'upload');
    $album_selectbox .= '</select>';
    $rowcounter = 0;
    $retval = '';

    $T = new Template( MG_getTemplatePath($album_id) );
    $T->set_file (array ('admin' => 'filelist.thtml'));

    $T->set_var(array(
        'lang_put_files'    => $LANG_MG01['put_files'],
        'lang_into_album'   => $LANG_MG01['into_album'],
    ));

    $destDirCount++;

    $dest = sprintf("d%04d",$destDirCount);

    $T->set_block('admin', 'dirRow', 'dRow');

    if ( $dir == '' ) {
        $pdir = './';
    } else {
        $pdir = $dir;
    }

    $T->set_var(array(
        'directory'     =>  $pdir,
        'destination'   =>  '<select name="' . $dest . '">' . $album_selectbox,
        'dirdest'       =>  $dest,
    ));


    $T->set_block('admin', 'fileRow', 'fRow');

    // calculate parent directory...

    $dirParts = array();
    $dirParts = explode('/' , $dir);
    $numDirs  = count($dirParts);
    $dirPath = '';
    if ( $numDirs > 1 ) {
        for ($x=0; $x < $numDirs - 1; $x++) {
            $dirPath .= $dirParts[$x];
            if ( $x < $numDirs - 2 ) {
                $dirPath .= '/';
            }
        }

        $T->set_var(array(
            'row_class'     => ($rowcounter % 2) ? '2' : '1',
            'checkbox'      => '',
            'palbum'        => '',
            'pfile'         => '',
            'dirid'         => '',
            'filename'      => '<a href="' . $_MG_CONF['site_url'] . '/admin.php?mode=list&amp;album_id=' . $album_id . '&amp;dir=' . $dirPath . '">Parent directory</a>',
            'fullname'      => '',
            'filesize'      => '',
            'parent_select' => '',
            'color'         => '',
            'type'          => '',
        ));
        $T->parse('fRow','fileRow',true);
        $rowcounter++;
    }

    while ( ( $file = readdir($dh) ) != false ) {
        if ( $file == '..' || $file == '.' ) {
            continue;
        }
        $filename = $file;

        $filetmp = $directory . $file;

        $filename = basename($file);
        $file_extension = strtolower(substr(strrchr($filename,"."),1));

        if ( is_dir($filetmp)) {
            $isadirectory   = 1;
            $type           = 'Directory';
            $fullDir        = urlencode(($dir . '/' . $filename));
            $dirlink = '<a href="' . $_MG_CONF['site_url'] . '/admin.php?album_id=' . $album_id . '&amp;mode=list&amp;dir=' . $fullDir . '">' . $filename . '</a>';
        } else {
            $isadirectory = 0;
        }

        if ( $isadirectory == 0 ) {
            switch( $file_extension ) {
                case 'jpg':
                case 'bmp':
                case 'tif':
                case 'png':
                    $type = 'Image';
                    break;
                case 'avi':
                case 'wmv':
                case 'asf':
                case 'mov':
                    $type = 'Video';
                    break;
                case 'mp3':
                case 'ogg':
                    $type = 'Audio';
                    break;
                default:
                    $type = 'Unknown';
                    break;
            }
        }

        if ( $MG_albums[$album_id]->max_filesize != 0 && filesize($filetmp) > $MG_albums[$album_id]->max_filesize) {
            $toobig = 1;
        } else {
            $toobig = 0;
        }
        $pCount++;
        $pvalue = sprintf("i%04d", $pCount);

        $T->set_var(array(
            'row_class'     => ($rowcounter % 2) ? '2' : '1',
            'checkbox'      => '<input type="checkbox" name="pic[]" value="' . $pvalue . '"/>',
            'palbum'        => '<input type="hidden" name="album_lb_id_' . $pvalue . '" value="' . $dest . '"/>',
            'pfile'         => '<input type="hidden" name="picfile_' . $pvalue . '" value="' . $filetmp . '"/>',
            'dirid'         => '<input type="hidden" name="dest" value="' . $dest . '"/>',
            'filename'      => ($isadirectory ? $dirlink : $filename),
            'fullname'      => $filetmp,
            'filesize'      => COM_numberFormat((filesize($filetmp))/1024) . ' kB',
            'parent_select' => '<select name="parentaid">' . $album_selectbox,
            'color'         => ($toobig ? '<span style="font-color:red;">' : '<span style="font-color:black;">'),
            'type'          => $type,
        ));
        $T->parse('fRow','fileRow',true);
        $rowcounter++;
    }

    $T->parse('dRow','dirRow',true);
    closedir($dh);

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}



function MG_ftpProcess( $album_id ) {
    global $MG_albums, $_FILES, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG02, $LANG_MG03, $_POST;

    $session_description = $LANG_MG01['ftp_media'];
    $origin = $album_id == 0 ? '/index.php' : '/album.php?aid=' . $album_id;
    $session_id = MG_beginSession('ftpimport2',$_MG_CONF['site_url'] . $origin,$session_description );
    $purgefiles = COM_applyFilter($_POST['purgefiles'],true);

    $count = count($_POST['pic']);
    if ( $count < 1 ) {
        if ( $album_id == 0 ) {
            echo COM_refresh($_MG_CONF['site_url'] . '/index.php');
        } else {
            echo COM_refresh($_MG_CONF['site_url'] . '/album.php?aid=' . $album_id);
        }
        exit;
    }

    foreach ($_POST['pic'] as $pic_id) {
        $album_lb_id = COM_applyFilter($_POST['album_lb_id_' . $pic_id]);
        $aid         = COM_applyFilter($_POST[$album_lb_id],true);

        $filename    = COM_applyFilter($_POST['picfile_' . $pic_id]);        // full path and name
        $file        = basename($filename);                 // basefilename
        if ( is_dir($filename)) {
            $mid = 1;
        } else {
            $mid = 0;
        }

        DB_query("INSERT INTO {$_TABLES['mg_session_items']} (session_id,mid,aid,data,data2,data3,status)
                  VALUES('$session_id','$mid',$aid,'" . DB_escapeString($filename) . "','" . $purgefiles . "','" . DB_escapeString($file) . "',0)");
    }

    $display  = MG_siteHeader();
    $display .= MG_continueSession($session_id,0,30);
    $display .= MG_siteFooter();
    echo $display;
    exit;
}
/**
* Displays pick list of files to process...
*
* @param    int     album_id    album_id save uploaded media
* @return   string              HTML
*
*/
function MG_FTPpickFiles( $album_id, $dir, $purgefiles, $recurse ) {
    global $MG_albums, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG02, $LANG_MG03, $_POST;
    global $album_jumpbox;
    global $destDirCount;
    global $pCount;

    $destDirCount = 0;
    $pCount       = 0;

    $retval = '';
    $valid_albums = '';

    $T = new Template( MG_getTemplatePath($album_id) );
    $T->set_file (array ('admin' => 'ftpimport.thtml'));

    $T->set_var(array(
        'lang_title'        => $LANG_MG01['title'],
        'lang_description'  => $LANG_MG01['description'],
        'lang_parent_album' => $LANG_MG01['parent_album'],
        'lang_filelist'     => $LANG_MG01['file_list'],
        'lang_quick_create' => $LANG_MG01['quick_create'],
        'lang_checkall'     => $LANG_MG01['check_all'],
        'lang_uncheckall'   => $LANG_MG01['uncheck_all'],
        'dir'               => $dir,
        'purgefiles'        => $purgefiles,
        'recurse'           => $recurse,
        'album_id'          => $album_id,
    ));

    $filelist = MG_listDir ($dir, $album_id, $purgefiles, $recurse );

    $level = 0;
    $album_jumpbox  = '<select name="parentaid">';
    if ( SEC_hasRights('mediagallery.admin')) {
        $album_jumpbox .= '<option value="0">' . $LANG_MG01['root_album'] . '</option>';
    } else {
        $album_jumpbox .= '<option disabled value="0">' . $LANG_MG01['root_level'] . '</option>';
    }
    $valid_albums .= $MG_albums[0]->buildJumpBox(0,3);
    $album_jumpbox .= '</select>';

    $T->set_var(array(
        's_form_action' =>  $_MG_CONF['site_url'] . '/admin.php',
        'action'        =>  'ftpprocess',
        'lang_save'     =>  $LANG_MG01['save'],
        'lang_cancel'   =>  $LANG_MG01['cancel'],
        'parent_select' =>  $album_jumpbox,
        'filelist'      =>  $filelist
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));

    return $retval;
}
?>