<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id$|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2005-2008 by the following authors:                         |
// |                                                                           |
// | Mark R. Evans              - mark@gllabs.org                              |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

require_once('../lib-common.php');

function MG_getMP3Items ($aid)
{
    global $MG_albums, $_TABLES, $_MG_CONF;

    $retval = '';

	if ( isset($MG_albums[$aid]->id ) ) {
        if ( $MG_albums[$aid]->access >= 1 ) {
			if ( $MG_albums[$aid]->cover_filename != '' && $MG_albums[$aid]->cover_filename != '0') {
			    if ( substr($MG_albums[$aid]->cover_filename,0,3) == 'tn_' ) {
			        $offset = 3;
			    } else {
			        $offset = 0;
			    }
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $MG_albums[$aid]->cover_filename[$offset] .'/' . $MG_albums[$aid]->cover_filename . $ext) ) {
                        $image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $MG_albums[$aid]->cover_filename[$offset] .'/' . $MG_albums[$aid]->cover_filename . $ext;
                        break;
                    }
                }
			} else {
				$albumCover = $MG_albums[$aid]->findCover();
				if ( $albumCover != '' ) {
			    	if ( substr($albumCover,0,3) == 'tn_' ) {
			        	$offset = 3;
			    	} else {
			        	$offset = 0;
			    	}
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $albumCover[$offset] .'/' . $albumCover . $ext) ) {
                            $image = $_MG_CONF['mediaobjects_url'] . '/tn/' . $albumCover[$offset] .'/' . $albumCover . $ext;
                            break;
                        }
                    }
				} else {
					$image = '';
				}
			}

			if ( $MG_albums[$aid]->tn_attached == 1 ) {
                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $MG_albums[$aid]->id . $ext) ) {
                        $image = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $MG_albums[$aid]->id . $ext;
                        break;
                    }
                }
			}

			$orderBy = MG_getSortOrder($aid, 0);

			$sql = "SELECT * FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
			        " ON ma.media_id=m.media_id WHERE ma.album_id=" . $aid . " AND m.media_type=2 AND m.mime_type='audio/mpeg' " . $orderBy;

			$result = DB_query( $sql );
			$nRows  = DB_numRows( $result );
			$mediaRows = 0;
			if ( $nRows > 0 ) {
			    while ( $row = DB_fetchArray($result)) {
			    	if ( $row['media_type'] == 0 ) {
                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                            if ( file_exists($_MG_CONF['path_mediaobjects'] . $src . "/" . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext) ) {
                                $PhotoURL = $_MG_CONF['mediaobjects_url'] . '/' . $src . "/" . $row['media_filename'][0] .'/' . $row['media_filename'] . $ext;
                                break;
                            }
                        }
		            } else {
		            	$PhotoURL  = $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
					}

			        if ( $row['media_tn_attached'] == 1 ) {
                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                                $media_thumbnail = $_MG_CONF['mediaobjects_url'] . '/tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . 'tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                break;
                            }
                        }
			        } else {
			            $media_thumbnail      = '';
			        }
			        if ( $media_thumbnail != '' ) {
			        	if ( !file_exists($media_thumbnail_file) ) {
			        		$medai_thumbnail = '';
			        	}
			        }

					$retval .= "        <track>\n";
				    $retval .= "            <title>" . $row['media_title'] . "</title>\n";
				    $retval .= "            <annotation>" . $row['media_title'] . "</annotation>\n";
				    if ( $row['artist'] != '' ) {
				    	$retval .= "            <creator>" . $row['artist'] . "</creator>\n";
				    }
				    if ( $row['album'] != '' ) {
				    	$retval .= "            <album>" . $row['album'] . "</album>\n";
				    }
					$retval .= "            <identifier>" . $row['media_id'] . "</identifier>\n";
				    $retval .= "            <location>" . $PhotoURL . "</location>\n";
				    if ( $media_thumbnail != '' ) {
				    	$retval .= "            <image>" . $media_thumbnail . "</image>\n";
				    } else {
    					if ( $image != '' ) {
    						$retval .= "            <image>" . $image . "</image>\n";
    					}
    				}
					$retval .= "        </track>\n";
			    }
			}
        }
		return $retval;
    }
}

function MG_xspf($aid) {
	global $MG_albums,$_MG_CONF,$LANG_CHARSET;

	$xml = '';
	header("Content-type: text/xml; charset=" . $LANG_CHARSET );
	$xml .= "<?xml version=\"1.0\" encoding=\"" . $LANG_CHARSET . "\"?>\n";
	$xml .= "<playlist version=\"1\" xmlns=\"http://xspf.org/ns/0/\">\n";
	$xml .= "<title>" . $MG_albums[$aid]->title . "</title>";
	$xml .= "    <trackList>\n";
	$xml .= MG_getMP3Items($aid);
	$xml .= "    </trackList>\n";
	$xml .= "</playlist>\n";
	echo $xml;
}

/*
 * Main processing
 */

if ( isset($_REQUEST['aid']) ) {
    $aid = COM_applyFilter($_REQUEST['aid'],true);
} else {
    $aid = 0;
}
MG_xspf($aid);
?>