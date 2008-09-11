<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | xml.php                                                                  |
// |                                                                          |
// | Generates XML feed of album elements                                     |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2008 by the following authors:                        |
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

function MG_getItems ()
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
			        " ON ma.media_id=m.media_id WHERE ma.album_id=" . $aid . " AND m.include_ss=1 " . $orderBy;

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
                                break;
                            }
                        }
        				if ( $type == 'mini' ) {
        				    $ThumbURL  = $_MG_CONF['mediaobjects_url'] . '/' . $src . "/" . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
        				} else {
                            foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                		            $ThumbURL  = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
                                    break;
                                }
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
						$retval .= "        <item>\n";
					    $retval .= "            <title>" . $row['media_title'] . "</title>\n";;
						$retval .= "            <id>" . $row['media_id'] . "</id>\n";
						$retval .= "            <link>" . $viewURL . "</link>\n";
					    $retval .= "            <view>" . $PhotoURL . "</view>\n";
					    $retval .= "            <thumbUrl>" . $ThumbURL . "</thumbUrl>\n";
					    $retval .= "            <width>" . $imgsize[0] . "</width>\n";
					    $retval .= "            <height>" . $imgsize[1] . "</height>\n";
						$retval .= "            <mime>" . $row['mime_type'] . "</mime>\n";
						$retval .= "            <guid isPermaLink=\"false\">" . $viewURL . "</guid>\n";
						$retval .= "            <pubDate>" . date('r', $row['media_upload_time']) . "</pubDate>\n";
						$retval .= "        </item>\n";
			    	}
			    }
			}
        }
		return $retval;
    }
}

function MG_xml() {
	global $MG_albums,$_MG_CONF,$LANG_CHARSET;

	$xml = '';
	header("Content-type: text/xml; charset=" . $LANG_CHARSET );
	echo "<?xml version=\"1.0\" encoding=\"" . $LANG_CHARSET . "\"?>\n";
	$xml .= "<rss version=\"2.0\">\n";
	$xml .= "    <channel>\n";
	$xml .= "        <title><![CDATA[ XML for Media Gallery ]]></title>\n";
	$xml .= "        <link>" . $_MG_CONF['site_url'] . "</link>\n";
	$xml .= "        <description>XML Mini SlideShow for Media Gallery</description>\n";
	$xml .= "        <language>en-us</language>\n";
	$xml .= "        <generator>Media Gallery version 1.4</generator>\n";
	$xml .= "        <lastBuildDate>" . date('r', time()) . "</lastBuildDate>\n";
	$xml .= "        <ttl>120</ttl>\n";
	$xml .= MG_getAlbumList ();
	$xml .= MG_getItems();
	$xml .= "    </channel>\n";
	$xml .= "</rss>\n";
	echo $xml;
}

MG_xml();
?>