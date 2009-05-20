<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | simpleviewer.php                                                         |
// |                                                                          |
// | Generates XML feed for Flash SimpleViewer                                |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2009 by the following authors:                        |
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

function MG_getAlbumList ()
{
	global $MG_albums;
	$aid = COM_applyFilter($_REQUEST['aid']);
	$retval = '';

	if ( isset($MG_albums[$aid]->id ) ) {
	    $retval .="    <album>\n";
	    $retval .= "        <title><![CDATA[" . $MG_albums[$aid]->title . "]]></title>\n";
		$retval .= "        <parentId><![CDATA[" . $MG_albums[$aid]->parent . "]]></parentId>\n";
		$retval .= "        <owner><![CDATA[" . $MG_albums[$aid]->owner_id . "]]></owner>\n";
		$retval .= "        <id><![CDATA[" . $MG_albums[$aid]->id . "]]></id>\n";
		$retval .="    </album>\n";
		$children = $MG_albums[$aid]->getChildren();
        foreach($children as $child) {
            if ( $MG_albums[$child]->access >= 1 ) {
			    $retval .="    <album>\n";
			    $retval .= "        <title><![CDATA[" . $MG_albums[$child]->title . "]]></title>\n";
				$retval .= "        <parentId><![CDATA[" . $MG_albums[$child]->parent . "]]></parentId>\n";
				$retval .= "        <owner><![CDATA[" . $MG_albums[$child]->owner_id . "]]></owner>\n";
				$retval .= "        <id><![CDATA[" . $MG_albums[$child]->id . "]]></id>\n";
				$retval .="    </album>\n";
            }
		}
	}
	return $retval;
}

function MG_getItems ($mode='sv')
{
    global $MG_albums, $_TABLES, $_MG_CONF;

    $retval = '';

    if ( isset($_REQUEST['aid']) ) {
	    $aid = COM_applyFilter($_REQUEST['aid'],true);
	} else {
	    $aid = 0;
	}
	if ( isset($_REQUEST['src']) ) {
	    $src = COM_applyFilter($_REQUEST['src']);
	} else {
	    $src = 'tn';
	}
	if ( isset($_REQUEST['type']) ) {
	    $type = COM_applyFilter($_REQUEST['type']);
	} else {
	    $type = 'mini';
	}

	if ( $src != 'disp' && $src != 'orig' ) {
		$src = 'tn';
	}
	if ( $type != 'full' || $type != 'mini' ) {
	    $type = 'mini';
	}

	if ( isset($MG_albums[$aid]->id ) ) {
        if ( $MG_albums[$aid]->access >= 1 ) {
			$orderBy = MG_getSortOrder($aid, 0);

			$sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
			        " ON ma.media_id=m.media_id WHERE ma.album_id=" . intval($aid) . " " . $orderBy;

			$result = DB_query( $sql );
			$nRows  = DB_numRows( $result );
			$mediaRows = 0;
			if ( $nRows > 0 ) {
			    while ( $row = DB_fetchArray($result)) {
			    	if ( $row['media_type'] == 0 ) {
                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                            if ( file_exists($_MG_CONF['path_mediaobjects'] . $src . '/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
            		            $PhotoURL  = $_MG_CONF['mediaobjects_url'] . '/' . $src . "/" . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
            		            $PhotoPath = $_MG_CONF['path_mediaobjects'] . $src . "/" . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
            		            $RelativePath = $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
                                break;
                            }
                        }
        				if ( $row['remote_url'] != '' ) {
        				    $viewURL = $row['remote_url'];
        				} else {
        				    $viewURL   = $_MG_CONF['site_url']  . "/media.php?s=" . $row['media_id'];
        				}
			            $imgsize   = @getimagesize($PhotoPath);
        				if ( $imgsize == false ) {
            				continue;
        				}

        				if ($mode == 'av') {
        				    $RelativePath = '/mediagallery/mediabojects/disp/' . $RelativePath;
        				}

						$retval .= "<image>\r\n";
					    $retval .= "<filename>" . $RelativePath . "</filename>\r\n";
					    $retval .= "<caption>" . strip_tags($row['media_title']) . "</caption>\r\n";
						$retval .= "</image>\r\n";
			    	}
			    }
			}
        }
		return $retval;
    }
}

function MG_xml() {
	global $MG_albums,$_MG_CONF,$LANG_CHARSET;

    if ( isset($_REQUEST['aid']) ) {
	    $aid = COM_applyFilter($_REQUEST['aid'],true);
	} else {
	    $aid = 0;
	}

    $mode = 'sv';

    switch ( $MG_albums[$aid]->display_image_size ) {
        case 0 :
            $dImageWidth = 500;
            $dImageHeight = 375;
            break;
        case 1 :
            $dImageWidth = 600;
            $dImageHeight = 450;
            break;
        case 2 :
            $dImageWidth = 620;
            $dImageHeight = 465;
            break;
        case 3 :
            $dImageWidth = 720;
            $dImageHeight = 540;
            break;
        case 4 :
            $dImageWidth = 800;
            $dImageHeight = 600;
            break;
        case 5 :
            $dImageWidth = 912;
            $dImageHeight = 684;
            break;
        case 6 :
            $dImageWidth = 1024;
            $dImageHeight = 768;
            break;
        case 7 :
            $dImageWidth = 1152;
            $dImageHeight = 804;
            break;
        case 8 :
            $dImageWidth = 1280;
            $dImageHeight = 1024;
            break;
        case 9 :
            $dImageWidth = $_MG_CONF['custom_image_width'];
            $dImageHeight = $_MG_CONF['custom_image_height'];
            break;
        default :
            $dImageWidth  = 620;
            $dImageHeight = 465;
            break;
    }

    $dImageWidth = $dImageWidth - 70;

    $title = strip_tags($MG_albums[$aid]->title);

	$xml = '';
	header("Content-type: text/xml; charset=" . $LANG_CHARSET );
	echo "<?xml version=\"1.0\" encoding=\"" . $LANG_CHARSET . "\"?>\n";
    $xml .= '<simpleviewerGallery maxImageWidth="' . $dImageWidth .
            '" maxImageHeight="' . $dImageHeight .
            '" textColor="' . $_MG_CONF['simpleviewer']['textcolor'] .
            '" frameColor="' . $_MG_CONF['simpleviewer']['framecolor'] .
            '" frameWidth="' . $_MG_CONF['simpleviewer']['framewidth'] .
            '" stagePadding="' . $_MG_CONF['simpleviewer']['stagepadding'] .
            '" thumbnailColumns="' . $_MG_CONF['simpleviewer']['thumbnailcolumns'] .
            '" thumbnailRows="' . $_MG_CONF['simpleviewer']['thumbnailrows'] .
            '" navPosition="' . $_MG_CONF['simpleviewer']['navposition'] .
            '" title="' . $title .
            '" enableRightClickOpen="' . $_MG_CONF['simpleviewer']['enablerightclickopen'] .
            '" backgroundImagePath="" ' .
            ' thumbPath="' . $_MG_CONF['mediaobjects_url'] . '/tn/" ' .
            ' imagePath="' . $_MG_CONF['mediaobjects_url'] . '/disp/">';
    $xmlend = '</simpleviewerGallery>';

	$xml .= MG_getItems('sv');
	$xml .= $xmlend;

	echo $xml;
}

MG_xml();
?>