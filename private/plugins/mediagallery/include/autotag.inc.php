<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Auto Tag Handling
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

function _mg_autotags ( $op, $content = '', $autotag = '') {
    global $MG_albums, $_MG_CONF, $_CONF, $_MG_USERPREFS, $_TABLES, $LANG_MG00, $LANG_MG03, $side_count, $swfjsinclude;
    global $mgAutoTagArray, $mg_installed_version;

    static $ss_count = 0;

    static $jplayerLoaded = 0;

    if ( $mg_installed_version != $_MG_CONF['pi_version'] ) {
        return $content;
    }
    $default_thumbnail = 'placeholder.svg';
    if ($op == 'parse') {
        if (!isset($mgAutoTagArray['count']) || empty($mgAutoTagArray['count'])) {
            $mgAutoTagArray['count'] = 0;
        }
        /*
         * Process the auto tag to remove any embedded &nbsp;
         */
        $tag = str_replace('&nbsp;',' ',$autotag['tagstr']);
        $parms = explode (' ', $tag);
        // Extra test to see if autotag was entered with a space
        // after the module name
        if (substr ($parms[0], -1) == ':') {
            $startpos = strlen ($parms[0]) + strlen ($parms[1]) + 2;
            $label = str_replace (']', '', substr ($tag, $startpos));
            $tagid = $parms[1];
        } else {
            $label = str_replace (']', '',
                     substr ($tag, strlen ($parms[0]) + 1));
            $parms = explode (':', $parms[0]);
            if (count ($parms) > 2) {
                // whoops, there was a ':' in the tag id ...
                array_shift ($parms);
                $tagid = implode (':', $parms);
            } else {
                $tagid = $parms[1];
            }
        }
        $autotag['parm1'] = str_replace(']','',$tagid);
        $autotag['parm2'] = $label;
        /*
         * end of tag replacement
         */

        $T = new Template($_MG_CONF['template_path']);
        // see if we have an alignment option included
        $caption = $autotag['parm2'];
        $aSet = 0;
        $skip = 0;

        // default values for parameters
        $border         = $_MG_CONF['at_border'];
        $align          = $_MG_CONF['at_align'];
        $width          = $_MG_CONF['at_width'];
        $height         = $_MG_CONF['at_height'];
        $src            = $_MG_CONF['at_src'];
        $autoplay       = $_MG_CONF['at_autoplay'];
        $enable_link    = $_MG_CONF['at_enable_link'];
        $delay          = $_MG_CONF['at_delay'];
        $transition     = 'Fade';
        $showtitle      = $_MG_CONF['at_showtitle'];
        $destination    = 'content';
        $target         = '';
        $linkID         = 0;
        $alt            = 0;
        $link_src       = 'disp';
        $classes        = '';
        $nosize         = 0;
        $tag            = '';

        if ( $align != '' ) {
            $aSet = 1;
        }

        // parameter processing - logic borrowed from
        // Dirk Haun's Flickr plugin

        $px = explode (' ', trim ($autotag['parm2']));
        if (is_array ($px)) {
            foreach ($px as $part) {
                if (substr ($part, 0, 6) == 'width:') {
                    $a = explode (':', $part);
                    $width = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 7) == 'height:') {
                    $a = explode (':', $part);
                    $height = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 7) == 'border:') {
                    $a = explode (':', $part);
                    $border = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 6) == 'align:') {
                    $a = explode(':', $part);
                    $align = $a[1];
                    $skip++;
                    $aSet = 1;
                } elseif (substr($part,0,4) == 'src:') {
                    $a = explode(':', $part);
                    $src = $a[1];
                    $skip++;
                } elseif (substr($part,0,9) == 'autoplay:') {
                    $a = explode(':', $part);
                    $autoplay = $a[1];
                    $skip++;
                } elseif (substr($part,0,5) == 'link:') {
                    $a = explode(':',$part);
                    $enable_link = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 6) == 'delay:') {
                    $a = explode (':', $part);
                    $delay = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 11) == 'transition:') {
                    $a = explode (':', $part);
                    $transition = $a[1];
                    $skip++;
                } elseif (substr ($part,0, 6) == 'title:' ) {
                    $a = explode (':',$part);
                    $showtitle = $a[1];
                    $skip++;
                } elseif (substr ($part, 0, 5) == 'dest:') {
                    $a = explode (':', $part);
                    $destination = $a[1];
                    $skip++;
                    if ( $destination != 'content' && $destination != 'block' ) {
                        $destination = 'content';
                    }
                } elseif ( substr($part,0,7) == 'linkid:' ) {
                    $a = explode (':',$part);
                    $linkID = $a[1];
                    $skip++;
                } elseif ( substr($part,0,4) == 'alt:' ) {
                    $a = explode (':',$part);
                    $alt = $a[1];
                    $skip++;
                } elseif ( substr($part,0,7) == 'target:' ) {
                    $a = explode (':',$part);
                    $target = $a[1];
                    $skip++;
                } elseif ( substr($part,0,5) == 'type:' ) {
                    $a = explode (':',$part);
                    $mp3_type = $a[1];
                    $skip++;
                } elseif ( substr($part,0,6) == 'class:' ) {
                    $a = explode (':', $part );
                    $c = explode(',',$a[1]);
                    foreach ($c AS $class ) $classes .= ' '.$class;
                    $skip++;
                } elseif ( substr($part,0,7) == 'nosize:' ) {
                    $a = explode (':', $part );
                    $nosize = $a[1];
                    $skip++;
                } elseif ( substr($part,0,4) == 'tag:' ) {
                    $a = explode (':', $part );
                    $tag = str_replace('_',' ',$a[1]);
                    $skip++;
                } elseif ( substr($part,0,8) == 'linksrc:' ) {
                    $a = explode (':',$part);
                    $link_src = $a[1];
                    if ( !in_array($link_src,array('tn','disp','orig') ) ) {
                        $link_src = 'disp';
                    }
                    $skip++;
                } else {
                    break;
                }
            }

            if ($skip != 0) {
                if (count ($px) > $skip) {
                    for ($i = 0; $i < $skip; $i++) {
                        array_shift ($px);
                    }
                    $caption = trim (implode (' ', $px));
                } else {
                    $caption = '';
                }
            }
        } else {
            $caption = trim ($autotag['parm2']);
        }

        if ( $tag == '' ) $tag = $caption;

        if ( !is_numeric($autotag['parm1'][0]) ) {
            switch ($autotag['parm1'][0] ) {
                case 'n' :
                    $align = '';
                    $aSet = 1;
                    break;
                case 'c' :
                    $align="center";
                    $aSet = 1;
                    break;
                case 'l' :
                    $align = "left";
                    $aSet = 1;
                    break;
                case 'r' :
                    $align = "right";
                    $aSet = 1;
                    break;
                case 'a' :
                    $align=(!($side_count % 2) ? "left" : "right" );
                    $side_count++;
                    $aSet = 1;
                    break;
                default :
                    $align=(!($side_count % 2) ? "left" : "right" );
                    $side_count++;
                    break;
            }
            $parm1 = COM_applyFilter(substr($autotag['parm1'],1,strlen($autotag['parm1'])-1));
        } else {
            $parm1 = COM_applyFilter($autotag['parm1']);
            if ( $aSet == 0 || $align == 'auto') {
                $align=(!($side_count % 2) ? "left" : "right" );
                $side_count++;
            }
        }
        if ( $align == 'none' ) {
            $align = '';
        }

        if ( !in_array($autotag['tag'],array('album','media','img','slideshow','fslideshow','video','audio','download','image','oimage','mlink','alink','playall'))) {
            return $content;
        }

        MG_initAlbums();

        // sanity check incase the album has been deleted or something...
        if ( $autotag['tag'] != 'media' && $autotag['tag'] != 'image' && $autotag['tag'] != 'video' && $autotag['tag'] != 'audio' && $autotag['tag'] != 'download' && $autotag['tag'] != 'oimage' && $autotag['tag'] != 'img' && $autotag['tag'] != 'mlink' && $autotag['tag'] != 'alink' && $autotag['tag'] != 'playall') {
            if ( !isset($MG_albums[$parm1]->id) ) {
                $link = '';
                $content = str_replace ($autotag['tagstr'], $link, $content);
                return $content;
            }
        }
        $ss_count = mt_rand(0,32768);
        switch( $autotag['tag'] ) {
            case 'download' :
                $side_count--;
                $sql = "SELECT ma.album_id FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . DB_escapeString($parm1) . "'";
                $result = DB_query($sql);
                if ( DB_numRows($result) > 0 ) {
                    $row = DB_fetchArray($result);
                    $aid = $row['album_id'];
                    if ( !isset($MG_albums[$aid]->id) || $MG_albums[$aid]->access == 0 ) {
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                        return $content;
                    }
                    $link = '<a href="' . $_MG_CONF['site_url'] . '/download.php?mid=' . $parm1 . '">';
                    if ( $caption != "" ) {
                        $link .= $caption;
                    } else {
                        $link .= 'download';
                    }
                    $link .= '</a>';
                    if ( $destination != 'block' ) {
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    } else {
                        $autoTagCount = $mgAutoTagArray['count'];
                        $mgAutoTagArray['tags'][$autoTagCount] = $link;
                        $mgAutoTagArray['count']++;
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    }
                    return $content;
                } else {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                break;
            case 'mlink' :
                $side_count--;
                $sql = "SELECT m.remote_url,ma.album_id FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . DB_escapeString($parm1) . "'";
                $result = DB_query($sql);
                if ( DB_numRows($result) > 0 ) {
                    $row = DB_fetchArray($result);
                    $aid = $row['album_id'];
                    if ( !isset($MG_albums[$aid]->id) || $MG_albums[$aid]->access == 0 ) {
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                        return $content;
                    }
                    if ( $alt == 1 && $row['remote_url'] != '' ) {
                        $link = '<a href="' . $row['remote_url'] . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>';
                    } else {
                        $link = '<a href="' . $_MG_CONF['site_url'] . '/media.php?f=0&amp;sort=0&amp;s=' . $parm1 . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>';
                    }
                    if ( $caption != "" ) {
                        $link .= $caption;
                    } else {
                        $link .= $LANG_MG03['click_here'];
                    }
                    $link .= '</a>';
                    if ( $destination != 'block' ) {
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    } else {
                        $autoTagCount = $mgAutoTagArray['count'];
                        $mgAutoTagArray['tags'][$autoTagCount] = $link;
                        $mgAutoTagArray['count']++;
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    }
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                } else {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                break;
            case 'playall' :
               if ( !isset($MG_albums[$parm1]->id) || $MG_albums[$parm1]->access == 0 || ( COM_isAnonUser() && $_MG_CONF['loginrequired'] == 1 )) {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                if ( $jplayerLoaded == 0 ) {
                    $outputHandle = outputHandler::getInstance();
                    $outputHandle->addLinkScript($_MG_CONF['site_url'].'/players/jplayer/jplayer/jquery.jplayer.min.js');
                    $outputHandle->addLinkScript($_MG_CONF['site_url'].'/players/jplayer/add-on/jplayer.playlist.min.js');
                    $outputHandle->addLinkStyle($_MG_CONF['site_url'].'/players/jplayer/skin/pink.flag/css/jplayer.pink.flag.min.css');
                    $jplayerLoaded++;
                }
                $V = new Template( MG_getTemplatePath(0) );
                $V->set_file (array ('xspf' => 'xspf_radio.thtml'));
                $aid = $parm1;
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
            					$image = '/mediagallery/mediaobjects/speaker.svg';
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
            			        " ON ma.media_id=m.media_id WHERE ma.album_id=" . intval($aid) . " AND m.media_type=2 AND m.mime_type='audio/mpeg' " . $orderBy;
            			$result = DB_query( $sql );
            			$nRows  = DB_numRows( $result );
            			$mediaRows = 0;
                        $V->set_block('xspf', 'playlist', 'pl');
                        $V->set_block('xspf', 'htmlplaylist', 'hpl');
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

                                $V->set_var(array (
                                    'audio_title' => $row['media_title'],
                                    'audio_url'     => $PhotoURL,
                                    'audio_poster'  => ($media_thumbnail != '') ? $media_thumbnail : $image,
                                ));
                                $V->parse('pl', 'playlist',true);
                                $V->parse('hpl', 'htmlplaylist',true);
            			    }
            			}
                    }
            	}
                $V->set_var(array(
                	'aid'				=> $parm1,
                	'site_url'			=> $_CONF['site_url'],
                	'autoplay'			=> $autoplay ? 'true' : 'false',
                	'id'				=> 'mp3radio' . rand(),
                	'id2'				=> 'mp3radio' . rand(),
                	'align'             => $align,
                ));
                $V->parse('output','xspf');
                $player = $V->finish($V->get_var('output'));

                if ( $align != '' && $align != "center") {
                    $link = '<span style="float:' . $align . ';padding:5px;">' . $player . '</span>';
                } else if ($align == "center") {
                    $link = '<span style="text-align:center;padding:5px;">' . $player . '</span>';
                } else {
                    $link = '<span style="padding:5px;">' . $player . '</span>';
                }
                if ( $destination != 'block' ) {
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                } else {
                    $autoTagCount = $mgAutoTagArray['count'];
                    $mgAutoTagArray['tags'][$autoTagCount] = $link;
                    $mgAutoTagArray['count']++;
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                }
                $content = str_replace ($autotag['tagstr'], $link, $content);
                return $content;
                break;
            case 'video' :
                $sql = "SELECT ma.album_id,m.media_id,m.mime_type,m.remote_url,m.media_filename,m.media_mime_ext,m.media_original_filename,m.media_tn_attached,m.media_resolution_x,m.media_resolution_y,m.remote_media FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . DB_escapeString($parm1) . "'";
                $result = DB_query($sql);
                if ( DB_numRows($result) > 0 ) {
                    $row = DB_fetchArray($result);
                    $aid = $row['album_id'];
                    if ( !isset($MG_albums[$aid]->id) || $MG_albums[$aid]->access == 0 ) {
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                        return $content;
                    }
                    $meta_file_name = 	$_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                    $meta = IMG_getMediaMetaData($meta_file_name);

                    if ( $meta['mime_type'] == 'video/quicktime' || $meta['mime_type'] == 'video/mp4') {
                        if ( $meta['fileformat'] == 'mp4' ) {
                            $row['mime_type'] = 'video/mp4';
                        }
                    }
                    // determine height / width and aspect
                    if ( ($width == 'auto' || (int) $width == 0 || (int) $width == -1 || $height == -1) && $row['media_resolution_x'] > 0 && $row['media_resolution_y'] > 0 ) {
                        $videoheight = $row['media_resolution_y'];
                        $videowidth  = $row['media_resolution_x'];
                    } else {
                        if ( $row['media_resolution_x'] > 0 && $row['media_resolution_y'] > 0 ) {
                            if ( $row['media_resolution_x'] >= $row['media_resolution_y'] ) {
                                // landscape
                                $ratio = $row['media_resolution_y'] / $row['media_resolution_x'];
                                $orientation = 0;
                            } else {
                                // portrait
                                $ratio = $row['media_resolution_x'] / $row['media_resolution_y'];
                                $orientation = 1;
                            }
                        } else {
                            $ratio =  0.75;
                            $orientation = 0;
                        }
                        if ( $orientation == 0 ) {
                            if ( (int) $width > 0 && $height == 0 ) {
                                $videoheight = round((int) $width * $ratio);
                                $videowidth  = $width;
                            } else if ( $width == 0 && $height == 0 ) {
                                $videoheight = 200 * $ratio;
                                $videowidth  = 200;
                            } else if ( $width == 0 && $height > 0 ) {
                                $videowidth = round($height / $ratio);
                                $videoheight = $height;
                            } else if ( $width > 0 && $height > 0 ) {
                                $videowidth = $width;
                                $videoheight = $height;
                            }
                        } else {
                            if ( $width > 0 && $height == 0 ) {
                                $videoheight = round($width / $ratio);
                                $videowidth  = $width;
                            } else if ( $width == 0 && $height == 0 ) {
                                $videoheight = 200;
                                $videowidth  = round(200 / $ratio);
                            } else if ( $width == 0 && $height > 0 ) {
                                $videowidth = round($height * $ratio);
                                $videoheight = $height;
                            } else  if ( $width > 0 && $height > 0 ) {
                                $videowidth = $width;
                                $videoheight = $height;
                            }
                        }
                    }
                    switch( $row['mime_type'] ) {
                        case 'embed' :
                            if ( preg_match("/vimeo/i", $row['remote_url']) ) {
                                $vimeo = 'vimeo, ';
                            } else {
                                $vimeo = '';
                            }
                            if ( $align != '' && $align != "center") {
                                $link = '<div class="video ['.$vimeo.'widescreen] '.$classes. '"><span style="float:' . $align . ';padding:5px;">' . $row['remote_url'] . '</span></div>';
                            } else if ( $align == 'center' ) {
                                $link = '<div class="video ['.$vimeo.'widescreen] ' . $classes. '"><span style="text-align:center;padding:5px;">' . $row['remote_url'] . '</span></div>';
                            } else {
                                $link = '<div class="video ['.$vimeo.'widescreen] '.$classes.'"><span style="padding:5px;">' . $row['remote_url'] . '</span></div>';
                            }
                            if ( $destination != 'block' ) {
                                $content = str_replace ($autotag['tagstr'], $link, $content);
                            } else {
                                $autoTagCount = $mgAutoTagArray['count'];
                                $mgAutoTagArray['tags'][$autoTagCount] = $link;
                                $mgAutoTagArray['count']++;
                                $link = '';
                                $content = str_replace ($autotag['tagstr'], $link, $content);
                            }
                            return $content;
                            break;
                        case 'video/x-ms-asf' :
                        case 'video/x-ms-asf-plugin' :
                        case 'video/avi' :
                        case 'video/msvideo' :
                        case 'video/x-msvideo' :
                        case 'video/avs-video' :
                        case 'video/x-ms-wmv' :
                        case 'video/x-ms-wvx' :
                        case 'video/x-ms-wm' :
                        case 'application/x-troff-msvideo' :
                        case 'application/x-ms-wmz' :
                        case 'application/x-ms-wmd' :
                            $V = new Template( MG_getTemplatePath(0) );
                            $V->set_file ('video','view_asf.thtml');
                            $V->set_var(array(
                                'autostart'         => $autoplay ? 'true' : 'false',
                                'enablecontextmenu' => 'true',
                                'stretchtofit'      => 'false',
                                'showstatusbar'     => 'false',
                                'showcontrols'      => 'true',
                                'showdisplay'       => 'false',
                                'height'            => $videoheight,
                                'width'             => $videowidth,
                                'bgcolor'           => '#FFFFFF',
                                'playcount'         => '9999',
                                'loop'              => 'true',
                                'movie'             => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                                'autostart0'         => $autoplay ? '1' : '0',
                                'enablecontextmenu0' => '1',
                                'stretchtofit0'      => '0',
                                'showstatusbar0'     => '0',
                                'uimode0'            => 'none',
                                'showcontrols0'      => '1',
                                'showdisplay0'       => '0',
                            ));
                            $V->parse('output','video');
                            if ( $align != '' && $align != "center") {
                                $u_image = '<span style="float:' . $align . ';padding:5px;">' . $V->finish($V->get_var('output')) . '</span>';
                            } else if ( $align == 'center' ) {
                                $u_image = '<span style="text-align:center;padding:5px;">' . $V->finish($V->get_var('output')) . '</span>';
                            } else {
                                $u_image = '<span style="padding:5px;">' . $V->finish($V->get_var('output')) . '</span>';
                            }
                            break;

                        case 'video/mp4' :
                            if ( $row['media_tn_attached'] == 1 ) {
                                $foundTN = 0;
                                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                    if ( file_exists($_MG_CONF['path_mediaobjects'] . '/orig/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                                        $thumb = $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                        $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . '/orig/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext);
                                        $foundTN = 1;
                                        break;
                                    }
                                }
                                if ( $foundTN == 0 ) {
                                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                        if ( file_exists($_MG_CONF['path_mediaobjects'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                                            $thumb = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                            $media_size_orig = $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext);
                                            break;
                                        }
                                    }
                                }
                            } else {
                                $thumb = '';//$_MG_CONF['mediaobjects_url'] . '/video-placeholder.png';
//                                $thumb = $_MG_CONF['mediaobjects_url'].'/placeholder_video_w.svg';
                            }

                            $V = new Template( MG_getTemplatePath(0) );
                            $V->set_file (array ('video' => 'view_mp4.thtml'));
                            $V->set_var(array(
                                'mime_type'     => 'video/mp4',
                                'autoref'       => 'true',
                                'autoplay'      => $autoplay ? 'true' : 'false',
                                'autoplay_text' => $autoplay ? ' autoplay ' : '',
                                'controller'    => 'true',
                                'kioskmode'     => 'true',
                                'scale'         => 'aspect',
                                'height'        => $videoheight,
                                'width'         => $videowidth,
                                'bgcolor'       => '#F0F0F0',
                                'loop'          => 'true',
                                'movie'         => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                                'thumbnail'     => $thumb,
                                'site_url'      => $_MG_CONF['site_url'],
                                'player_url'    => $_CONF['site_url'].'/javascript/addons/mediaplayer/',
                            ));

                            if ( $align != '' && $align != "center" ) {
                                $V->set_var('alignment','float:'.$align.';');
                            } else if ( $align == 'center' ) {
                                $V->set_var('alignment','text-align:center;');
                            } else {
                                $V->set_var('alignment','text-align:center;');
                            }
                            $V->parse('output','video');
                            $u_image = $V->finish($V->get_var('output'));
                            break;

                        case 'video/mpeg' :
                        case 'video/x-motion-jpeg' :
                        case 'video/quicktime' :
                        case 'video/mpeg' :
                        case 'video/x-mpeg' :
                        case 'video/x-mpeq2a' :
                        case 'video/x-qtc' :
                        case 'video/x-m4v' :
                            $V = new Template( MG_getTemplatePath(0) );
                            $V->set_file (array ('video' => 'view_quicktime.thtml'));
                            $V->set_var(array(
                                'autoref'       => 'true',
                                'autoplay'      => $autoplay ? 'true' : 'false',
                                'controller'    => 'true',
                                'kioskmode'     => 'true',
                                'scale'         => 'aspect',
                                'height'        => $videoheight,
                                'width'         => $videowidth,
                                'bgcolor'       => '#F0F0F0',
                                'loop'          => 'true',
                                'movie'         => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                            ));
                            $V->parse('output','video');
                            if ( $align != '' && $align != "center" ) {
                                $u_image = '<div style="float:' . $align . ';padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            } else if ( $align == 'center' ) {
                                $u_image = '<div style="text-align:center;padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            } else {
                                $u_image = '<div style="padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            }
                            break;
                        case 'application/x-shockwave-flash' :
                            // set the default playback options...
                            $playback_options['play']    = $_MG_CONF['swf_play'];
                            $playback_options['menu']    = $_MG_CONF['swf_menu'];
                            $playback_options['quality'] = $_MG_CONF['swf_quality'];
                            $playback_options['height']  = $_MG_CONF['swf_height'];
                            $playback_options['width']   = $_MG_CONF['swf_width'];
                            $playback_options['loop']    = $_MG_CONF['swf_loop'];
                            $playback_options['scale']   = $_MG_CONF['swf_scale'];
                            $playback_options['wmode']   = $_MG_CONF['swf_wmode'];
                            $playback_options['allowscriptaccess'] = $_MG_CONF['swf_allowscriptaccess'];
                            $playback_options['bgcolor']    = $_MG_CONF['swf_bgcolor'];
                            $playback_options['swf_version'] = $_MG_CONF['swf_version'];
                            $playback_options['flashvars']   = $_MG_CONF['swf_flashvars'];

                            $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . $row['media_id'] . "'");
                            while ( $poRow = DB_fetchArray($poResult) ) {
                                $playback_options[$poRow['option_name']] = $poRow['option_value'];
                            }
/*
                            if ( $swfjsinclude > 0 ) {
                                $u_image = '';
                            } else {
                                $S = new Template( MG_getTemplatePath(0) );
                                $S->set_file(array('swf' => 'swfobject.thtml'));
                                $S->set_var(array(
                                    'site_url'  => $_MG_CONF['site_url'],
                                ));
                                $S->parse('output','swf');
                                $u_image = $S->finish($S->get_var('output'));
                                $swfjsinclude++;
                            }
*/
                            $V = new Template( MG_getTemplatePath(0) );
                            $V->set_file (array ('video' => 'view_swf.thtml'));
                            $V->set_var(array(
                                'site_url'  => $_MG_CONF['site_url'],
                                'lang_noflash' => $LANG_MG03['no_flash'],
                                'play'      => ($autoplay ? 'true' : 'false'),
                                'menu'      => ($playback_options['menu'] ? 'true' : 'false'),
                                'loop'      => ($playback_options['loop'] ? 'true' : 'false'),
                                'scale'     => $playback_options['scale'],
                                'wmode'     => $playback_options['wmode'],
                                'flashvars' => $playback_options['flashvars'],
                                'quality'   => $playback_options['quality'],
                                'height'    => $videoheight,
                                'width'     => $videowidth,
                                'asa'       => $playback_options['allowscriptaccess'],
                                'bgcolor'   => $playback_options['bgcolor'],
                                'swf_version' => $playback_options['swf_version'],
                                'filename'  => $row['media_original_filename'],
                                'id'        => $row['media_filename'] . rand(),
                                'id2'       => $row['media_filename'] . rand(),
                                'movie'     => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                            ));
                            $V->parse('output','video');
                            if ( $align != '' && $align != "center") {
                                $u_image = '<div style="float:' . $align . ';padding:5px;">' . $V->finish($V->get_var('output'))  . '</div>';
                            } else if ($align == "center") {
                                $u_image = '<div style="text-align:center;padding:5px;">' . $V->finish($V->get_var('output'))  . '</div>';
                            } else {
                                $u_image = '<div style="padding:5px;">' . $V->finish($V->get_var('output'))  . '</div>';
                            }
                            break;
                        case 'video/x-flv' :
				            // set the default playback options...
				            $playback_options['play']    = $_MG_CONF['swf_play'];
				            $playback_options['menu']    = $_MG_CONF['swf_menu'];
				            $playback_options['quality'] = $_MG_CONF['swf_quality'];
				            $playback_options['height']  = $_MG_CONF['swf_height'];
				            $playback_options['width']   = $_MG_CONF['swf_width'];
				            $playback_options['loop']    = $_MG_CONF['swf_loop'];
				            $playback_options['scale']   = $_MG_CONF['swf_scale'];
				            $playback_options['wmode']   = $_MG_CONF['swf_wmode'];
				            $playback_options['allowscriptaccess'] = $_MG_CONF['swf_allowscriptaccess'];
				            $playback_options['bgcolor']    = $_MG_CONF['swf_bgcolor'];
				            $playback_options['swf_version'] = $_MG_CONF['swf_version'];
				            $playback_options['flashvars']   = $_MG_CONF['swf_flashvars'];

				            $poResult = DB_query("SELECT * FROM {$_TABLES['mg_playback_options']} WHERE media_id='" . $row['media_id'] . "'");
				            while ( $poRow = DB_fetchArray($poResult) ) {
				                $playback_options[$poRow['option_name']] = $poRow['option_value'];
				            }
                            $u_image = '';
/*
				            if ( $swfjsinclude > 0 ) {
				                $u_image = '';
				            } else {
				                $S = new Template( MG_getTemplatePath(0) );
				                $S->set_file(array('swf' => 'swfobject.thtml'));
				                $S->set_var(array(
				                    'site_url'  => $_MG_CONF['site_url'],
				                ));
				                $S->parse('output','swf');
				                $u_image = $S->finish($S->get_var('output'));
				                $swfjsinclude++;
				            }
*/
				            $V = new Template( MG_getTemplatePath(0) );
				    		$V->set_file('video','view_flv_light.thtml');
                            $playImageJPG = MG_getImageFile('blank_blk.jpg');
				            // now the player specific items.
				    		$F = new Template( MG_getTemplatePath(0) );
				           	$F->set_file(array('player' => 'flvfp.thtml'));
				           	$playImage = $_MG_CONF['assets_url'].'/placeholder_video_w.svg';
				        	if ( $autoplay == 1 ) {  // auto start
				        		$playButton = '';
				        	} else {
				                if ( $row['media_tn_attached'] == 1 ) {
                                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                                            $playImage = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                            break;
                                        }
                                    }
				                }
								$playButton = "{ url: '" . $playImage . "', overlayId: 'play' },";
							}
				            if ( $row['remote_media'] == 1 ) {
					            $urlParts = array();
					            $urlParts = parse_url($row['remote_url']);

					            $pathParts = array();
					            $pathParts = explode('/',$urlParts['path']);

					            $ppCount = count($pathParts);
					            $pPath = '';
					            for ($row=1; $row<$ppCount-1;$row++) {
						            $pPath .= '/' . $pathParts[$row];
					            }
					            $videoFile = $pathParts[$ppCount-1];

						        $pos = strrpos($videoFile, '.');
						        if($pos === false) {
						            $basefilename = $videoFile;
						        } else {
						            $basefilename = substr($videoFile,0,$pos);
						        }
						        $videoFile            = $basefilename;
					           	$streamingServerURL   = "streamingServerURL: '" . $urlParts['scheme'] . '://' . $urlParts['host'] . $pPath . "',";
					           	$streamingServer      = "streamingServer: 'fms',";
					           	$movie = '';
				    		} else {
				    			$streamingServerURL   = '';
				    			$streamingServer      = '';
				    			$videoFile            = urlencode($_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
				    			$movie                = $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
				  			}
				  			$width  = $videowidth;
				  			$height = $videoheight + 22;
							$resolution_x = $videowidth;
							$resolution_y = $videoheight;
				            $id  = 'id_'  . rand();
				            $id2 = 'id2_' . rand();
				            $F->set_var(array(
				                'site_url'  		=> $_MG_CONF['site_url'],
				                'lang_noflash' 		=> $LANG_MG03['no_flash'],
				                'play'          	=> ($autoplay ? 'true' : 'false'),
				                'autoplay'          => ($autoplay ? 1 : 0),
				                'menu'          	=> ($playback_options['menu'] ? 'true' : 'false'),
				                'loop'          	=> ($playback_options['loop'] ? 'true' : 'false'),
				                'scale'         	=> $playback_options['scale'],
				                'wmode'         	=> $playback_options['wmode'],
				                'width'				=> $width,
				                'height'			=> $height,
					           	'streamingServerURL'=> $streamingServerURL,
					           	'streamingServer'	=> $streamingServer,
					           	'videoFile'			=> $videoFile,
					           	'movie'             => $movie,
					           	'playButton'		=> $playButton,
				                'id'            	=> $id,
				                'id2'           	=> $id2,
				                'resolution_x'  	=> $resolution_x,
				                'resolution_y'  	=> $resolution_y,
				                'player_url'        => $_CONF['site_url'].'/javascript/addons/mediaplayer/',
				                'thumbnail'         => $playImage,
				                'tn_jpg'            => $playImageJPG,
				                'mime_type'         => 'video/x-flv',
				            ));
                            if ( $align != '' && $align != "center" ) {
                                $F->set_var('alignment','float:'.$align.';');
                            } else {
                                $F->set_var('alignment','');
                            }
				    		$F->parse('output','player');
				    		$flv_player = $F->finish($F->get_var('output'));

				    		$V->set_var(array(
				                'site_url'  	=> $_MG_CONF['site_url'],
				                'lang_noflash'  => $LANG_MG03['no_flash'],
				                'id'            => $id,
				                'id2'           => $id2,
				                'width'         => $resolution_x,
				                'height'        => $resolution_y,
				                'flv_player'	=> $flv_player,
                                'player_url'    => $_CONF['site_url'].'/javascript/addons/mediaplayer/',
							));

                            $V->parse('output','video');

                            $u_image .= $V->finish($V->get_var('output'));
/*
                            if ( $align != '' && $align != "center") {
                                $u_image .= '<span class="'.$classes.'" style="float:' . $align . ';padding:5px;">' . $V->finish($V->get_var('output'))  . '</span>';
                            } else if ($align == "center") {
                                $u_image .= '<span class="'.$classes.'" style="text-align:center;padding:5px;">' . $V->finish($V->get_var('output'))  . '</span>';
                            } else {
                                $u_image .= '<span class="'.$classes.'" style="padding:5px;">' . $V->finish($V->get_var('output'))  . '</span>';
                            }
*/
                            break;
                    }
                    $link = $u_image;
                    if ( $destination != 'block' ) {
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    } else {
                        $autoTagCount = $mgAutoTagArray['count'];
                        $mgAutoTagArray['tags'][$autoTagCount] = $link;
                        $mgAutoTagArray['count']++;
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    }
                    return $content;
                } else {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                break;
            case 'audio' :
                $sql = "SELECT ma.album_id,m.media_title,m.mime_type,m.media_tn_attached,m.media_filename,m.media_mime_ext FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . DB_escapeString($parm1) . "'";
                $result = DB_query($sql);
                if ( DB_numRows($result) > 0 ) {
                    $row = DB_fetchArray($result);
                    $aid = $row['album_id'];
                    if ( !isset($MG_albums[$aid]->id) || $MG_albums[$aid]->access == 0 ) {
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                        return $content;
                    }
                    switch( $row['mime_type'] ) {
                        case 'audio/mpeg' :
                            $playback_options['height'] = 50;;
                            $playback_options['width']  = 300;
                            $V = new Template( MG_getTemplatePath(0) );
                            if (isset($mp3_type) && $mp3_type == 'ribbon' ) {
                                $tfile = 'mp3_podcast.thtml';
                            } else {
                                if ( $jplayerLoaded == 0 ) {
                                    $outputHandle = outputHandler::getInstance();
                                    $outputHandle->addLinkScript($_MG_CONF['site_url'].'/players/jplayer/jplayer/jquery.jplayer.min.js');
                                    $outputHandle->addLinkScript($_MG_CONF['site_url'].'/players/jplayer/add-on/jplayer.playlist.min.js');
                                    $outputHandle->addLinkStyle($_MG_CONF['site_url'].'/players/jplayer/skin/pink.flag/css/jplayer.pink.flag.min.css');
                                    $jplayerLoaded++;
                                }
    			                $tfile = 'view_mp3_flv.thtml';
    			            }
			                $autostart = $autoplay ? 'play' : 'stop';
			                $autoplay = $autoplay ? 'true' : 'false';

                            if ( $row['media_tn_attached'] == 1 ) {
                                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                                        $u_tn = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                        $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext);
                                        break;
                                    }
                                }
                                $border_width = $media_size_disp[0] + 15;
                                $u_pic = '<img src="' . $u_tn . '" alt="" style="border:none;" />';
                                $playback_options['width']  = 200;
                            } else {
                                $u_pic='';
                                $playback_options['width']  = 200;
                            }

                            $V->set_file (array ('audio' => $tfile));
                            $V->set_var(array(
                                'align'             => $align,
                                'autostart'         => $autostart,
                                'autoplay'          => $autoplay,
                                'enablecontextmenu' => 'true',
                                'stretchtofit'      => 'false',
                                'showstatusbar'     => 'true',
                                'uimode'            => 'mini',
                                'height'            => $playback_options['height'],
                                'width'             => $playback_options['width'],
                                'bgcolor'           => '#FFFFFF',
                                'loop'              => 'true',
                                'u_pic'             => $u_pic,
                                'title'				=> urlencode($row['media_title']),
                                'id'				=> 'mp3' . rand(),
                                'id2'				=> 'mp3' . rand(),
                                'site_url'			=> $_MG_CONF['site_url'],
                                'movie'             => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                                'mp3_file'          => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                            ));
                            $V->parse('output','audio');
                            if ( $align != '' && $align != "center") {
                                $u_image = '<span style="float:' . $align . ';padding:5px;text-align:center;">' . $V->finish($V->get_var('output')) . '</span>';
                            } else if ($align == "center") {
                                $u_image = '<div style="text-align:center;padding:5px;text-align:center;">' . $V->finish($V->get_var('output')) . '</div>';
                            } else {
                                $u_image = '<span style="padding:5px;text-align:center;">' . $V->finish($V->get_var('output')) . '</span>';
                            }
                            break;
                        case 'audio/x-ms-wma' :
                        case 'audio/x-ms-wax' :
                        case 'audio/x-ms-wmv' :
                            $playback_options['height'] = 50;;
                            $playback_options['width']  = 300;

                            $V = new Template( MG_getTemplatePath(0) );
                            $tfile = 'view_mp3_wmp.thtml';
                            $autostart = $autoplay ? '1' : '0';
                            if ( $row['media_tn_attached'] == 1 ) {
                                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                    if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                                        $u_tn = $_MG_CONF['mediaobjects_url'] . '/tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                        $media_size_disp  = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext);
                                        break;
                                    }
                                }
                                $border_width = $media_size_disp[0] + 15;
                                $u_pic = '<div class=out style="width:' . $border_width . 'px"><div class="in ltin tpin"><img src="' . $u_tn . '" alt="" /></div></div>';
                                $playback_options['height'] = 50;
                                $playback_options['width']  = 200;
                            } else {
                                $u_pic='';
                                $playback_options['height'] = 50;
                                $playback_options['width']  = 200;
                            }

                            $V->set_file (array ('audio' => $tfile));
                            $V->set_var(array(
                                'autostart'         => $autostart, // $autoplay ? 'true' : 'false',
                                'enablecontextmenu' => 'true',
                                'stretchtofit'      => 'false',
                                'showstatusbar'     => 'true',
                                'uimode'            => 'mini',
                                'height'            => $playback_options['height'],
                                'width'             => $playback_options['width'],
                                'bgcolor'           => '#FFFFFF',
                                'loop'              => 'true',
                                'u_pic'             => $u_pic,
                                'movie'             => $_MG_CONF['mediaobjects_url'] . '/orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'],
                            ));
                            $V->parse('output','audio');
                            if ( $align != '' && $align != "center") {
                                $u_image = '<div class="'.$classes.'" style="float:' . $align . ';padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            } else if ($align == "center") {
                                $u_image = '<div class="'.$classes.'" style="text-align:center;padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            } else {
                                $u_image = '<div class="'.$classes.'" style="padding:5px;">' . $V->finish($V->get_var('output')) . '</div>';
                            }
                            break;
                    }
                    $link = $u_image;

                    if ( $destination != 'block' ) {
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    } else {
                        $autoTagCount = $mgAutoTagArray['count'];
                        $mgAutoTagArray['tags'][$autoTagCount] = $link;
                        $mgAutoTagArray['count']++;
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    }
                    return $content;
                } else {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                break;
            case 'fslideshow' :
                if ( $parm1 == '' || $parm1 == 0 ) {
                    return $content;
                }
                $aid = $parm1;
                if ( !isset($MG_albums[$parm1]->id) || $MG_albums[$parm1]->access == 0 ) {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }

                if ( $width > 0 && $height == 0 ) {
                    $height = $width * 0.75;
                } else if ( $width == 0 && $height == 0 ) {
                    $height = $width = 200;
                } else if ( $width == 0  && $height > 0 ) {
                    $width = $height * 1.3333;
                }
                // if none of the above, assume height and width both specified.

                if ($caption == '' && $_MG_CONF['autotag_caption'] && isset($aid) ) {
                    $caption = $MG_albums[$aid]->title;
                }
                $captionHTML = '<br /><span style="width:' . $width . 'px;font-style:italic;font-size: smaller;text-indent:0;">' . $caption . '</span>' . LB;
                $ss_count++;

                $T = new Template( MG_getTemplatePath(0) );
                $T->set_file(array('fslideshow' => 'fsat.thtml'));
                $T->set_var(array(
                    'site_url'  => $_MG_CONF['site_url'],
                ));
                $T->set_var(array(
                    'id'            => 'mms' . $ss_count,
                    'id2'           => 'fsid' . $ss_count,
                    'movie'         => $_MG_CONF['site_url'] . '/xml.php?aid=' . $parm1 . '%26src=' . trim($src),
                    'dropshadow'    => 'true',
                    'delay'         => $delay,
                    'nolink'        => ($MG_albums[$parm1]->hidden || $enable_link == 0) ? 'true' : 'false',
                    'showtitle'     => ( $showtitle == 'bottom' || $showtitle == 'top' ) ? '&showTitle=' . $showtitle : '',
                    'width'         => $width,
                    'height'        => $height,
                ));
                $T->parse('output','fslideshow');
                $swfobject = $T->finish($T->get_var('output'));
                $link = $swfobject . $captionHTML;

                if ( $align != '' && $align != "center") {
                    $link = '<span class="'.$classes.'" style="float:' . $align . ';padding:5px;text-align:center;">' . $link . '</span>';
                } else if ($align == "center") {
                    $link = '<center><span class="'.$classes.'" style="padding:5px;text-align:center;">' . $link . '</span></center>';
                } else {
                    $link = '<span class="'.$classes.'" style="padding:5px;text-align:center;">' . $link . '</span>';
                }
                if ( $destination != 'block' ) {
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                } else {
                    $autoTagCount = $mgAutoTagArray['count'];
                    $mgAutoTagArray['tags'][$autoTagCount] = $link;
                    $mgAutoTagArray['count']++;
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                }
                return $content;
                break;
            case 'slideshow' :
                if ( $parm1 == '' || $parm1 == 0 ) {
                    return $content;
                }
                if ( !isset($MG_albums[$parm1]->id) || $MG_albums[$parm1]->access == 0 ) {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }
                if ($caption == '' && $_MG_CONF['autotag_caption'] ) {
                    $caption = $MG_albums[$parm1]->title;
                }
                $T->set_file (array('tag'      => 'autotag_ss.thtml'));
                $aid = $parm1;
                $pics = '';
                $counter = 0;
                $maxwidth = 0;
                $maxheight = 0;
                $ss_count++;
                $sql = "SELECT m.media_filename,m.media_mime_ext,m.remote_url FROM {$_TABLES['mg_media_albums']} as ma INNER JOIN " . $_TABLES['mg_media'] . " as m " .
                       " ON ma.media_id=m.media_id WHERE ma.album_id='" . DB_escapeString($aid) . "' AND m.media_type=0 AND m.include_ss=1 ORDER BY ma.media_order DESC";
                $result = DB_query($sql);

                $T->set_block('tag','slides','ss');

                while ($row = DB_fetchArray($result)) {
                    switch ( $src ) {
                        case 'orig' :
                            $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext']);
                            $ext = $row['media_mime_ext'];
                            break;
                        case 'disp' :
                            foreach ($_MG_CONF['validExtensions'] as $tnext ) {
                                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $tnext) ) {
                                    $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $tnext);
                                    $ext = substr($tnext,1,3);
                                    break;
                                }
                            }
                            break;
                        default :
                            foreach ($_MG_CONF['validExtensions'] as $tnext ) {
                                if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $tnext) ) {
                                    $media_size = @getimagesize($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $tnext);
                                    $ext = substr($tnext,1,3);
                                    break;
                                }
                            }
                            $src = 'tn';
                            break;
                    }
                    if ( $media_size == false ) {
                        continue;
                    }

                    $counter++;
                    if ( $width > 0 && $height == 0 ) {
                        if ( $media_size[0] > $media_size[1] ) {        // landscape
                            $ratio = $media_size[0] / $width;
                            $newwidth = $width;
                            $newheight = round($media_size[1] / $ratio);
                        } else {    // portrait
                            $ratio = $media_size[1] / $width;
                            $newheight = $width;
                            $newwidth = round($media_size[0] / $ratio);
                        }
                    } else if ( $width == 0 && $height == 0 ) {
                        if ( $media_size[0] > $media_size[1] ) {        // landscape
                            $ratio = $media_size[0] / 200;
                            $newwidth = 200;
                            $newheight = round($media_size[1] / $ratio);
                        } else {    // portrait
                            $ratio = $media_size[1] / 200;
                            $newheight = 200;
                            $newwidth = round($media_size[0] / $ratio);
                        }
                    } else if ( $width == 0 && $height > 0 ) {
                        if ( $height > $media_size[1] ) {
                            $newheight = $media_size[1];
                            $newwidth = $media_size[0];
                        } else {
                            $ratio = $height / $media_size[1];
                            $newheight = $height;
                            $newwidth = round($media_size[0] * $ratio);
                        }
                    } else {
                        $newwidth = $width;
                        $newheight = $height;
                    }

                    if ( $newheight > $maxheight ) {
                        $maxheight = $newheight;
                    }
                    if ( $newwidth > $maxwidth ) {
                        $maxwidth  = $newwidth;
                    }

                    $active = '';
                    if ( $counter == 1 ) {
                        $active = ' active ';
                    }

                    if ( $MG_albums[$parm1]->hidden == 1 || $enable_link == 0 ) {
                        $pics .= '<img class="slideshowThumbnail' . $ss_count . $active . ' '.$classes.'" src="' . $_MG_CONF['mediaobjects_url'] . '/' . $src . '/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $ext . '" alt="" style="width:' . $newwidth . 'px;height:' . $newheight . 'px;border:none;position:absolute;left:0px;top:0px;" />' . LB;
                    } else {
                        $pics .= '<img class="slideshowThumbnail' . $ss_count . $active .  ' '.$classes.'". src="' . $_MG_CONF['mediaobjects_url'] . '/' . $src . '/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $ext . '" alt="" style="width:' . $newwidth . 'px;height:' . $newheight . 'px;border:none;position:absolute;left:0px;top:0px;" />' . LB;
                    }

                    $T->set_var(array(
                        'img_url'   => $_MG_CONF['mediaobjects_url'] . '/' . $src . '/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $ext,
                        'img_width' => $newwidth,
                        'img_height' => $newheight,
                    ));
                    $T->parse('ss','slides',true);

                }
                if ( $delay <= 0 ) {
                    $delay = 10;
                }
                if ( $MG_albums[$parm1]->hidden == 1 || $enable_link == 0 ) {
                    $ss_url = '';
                } else {
                    $ss_url = '<a href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $aid . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>';
                    $ss_url = $_MG_CONF['site_url'] . '/album.php?aid=' . $aid;
                }

                if ( $counter != 0 ) {
                    $T->set_var(array(
                        'align'     => $align,
                        'pics'      => $pics,
                        'caption'   => $caption,
                        'maxheight' => $maxheight,
                        'maxwidth'  => $maxwidth,
                        'width'     => $maxwidth,
                        'framewidth' => $maxwidth + 10,
                        'ss_count'  => $ss_count,
                        'delay'     => $delay * 1000,
                        'border'    => $border ? 'border: silver solid;border-width: 1px;' : '',
                        'sslink'     => $ss_url,
                    ));
                    if ( $align == 'left' || $align == 'right' ) {
                        $T->set_var('float','float: ' . $align . ';');
                    } else {
                        $T->set_var('float','float:left;');
                        $align = 'left';
                    }
                    if ( $align == 'left' ) {
                        $T->set_var('margin-right','margin-right:15px;');
                    } else {
                        $T->set_var('margin-right','');
                    }
                    $T->parse('output','tag');
                    $link = $T->finish($T->get_var('output'));
                } else {
                    $link = '';
                }
                if ( $destination != 'block' ) {
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                } else {
                    $autoTagCount = $mgAutoTagArray['count'];
                    $mgAutoTagArray['tags'][$autoTagCount] = $link;
                    $mgAutoTagArray['count']++;
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                }
                return $content;
                break;
            case 'album' :
                if ( $parm1 == '' || $parm1 == 0 ) {
                    $side_count--;
                    return $content;
                }
                if ( !isset($MG_albums[$parm1]->id) || $MG_albums[$parm1]->access == 0 ) {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    $side_count--;
                    return $content;
                }
                $ss_count++;
                if ( $border == 0 ) {
                    $T->set_file(array('tag'    => 'autotag_nb.thtml'));
                } else {
                    $T->set_file (array('tag'      => 'autotag.thtml'));
                }
                if ( $tag != '' ) {
                    $alttag = ' alt="' . $tag . '" title="' . $tag . '"';
                } else {
                    $alttag = ' alt=""';
                    if ( $_MG_CONF['autotag_caption'] ) {
                        $caption = $MG_albums[$parm1]->title;
                    }
                }
                $aid = $parm1;

                if ( $MG_albums[$parm1]->tn_attached == 1 ) {
                    foreach ($_MG_CONF['validExtensions'] as $ext ) {
                        if ( file_exists($_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $parm1 . $ext) ) {
                            $tnImage = $_MG_CONF['mediaobjects_url'] . '/covers/cover_' . $parm1 . $ext;
                            $tnFileName = $_MG_CONF['path_mediaobjects'] . 'covers/cover_' . $parm1 . $ext;
                            break;
                        }
                    }
                } else {
                    $filename = $MG_albums[$aid]->findCover();
                    if ( $filename != '' ) {
                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] .'/' . $filename . $ext) ) {
                                $tnImage = $_MG_CONF['mediaobjects_url'] . '/tn/' . $filename[0] .'/' . $filename . $ext;
                                $tnFileName = $_MG_CONF['path_mediaobjects'] . 'tn/' . $filename[0] .'/' . $filename . $ext;
                                break;
                            }
                        }
                    } else {
                        $tnImage = $_MG_CONF['assets_url'] . '/placeholder.svg';
                        $tnFileName = $_MG_CONF['path_assets'] . 'placeholder.svg';
                    }
                }
                if (!empty($tnFileName)) {
                    $media_size = @getimagesize($tnFileName);
                } else {
                    $media_size = false;
                }
                if ( $media_size == false ) {
                    $tnImage = $_MG_CONF['assets_url'] . '/placeholder.svg';
                    $tnFileName = $_MG_CONF['path_assets'] . 'placeholder.svg';
                    $media_size = array(200,200);
                }
                if ( $width > 0 && $height == 0 ) {
                    $ratio = $media_size[0] / $width;
                    $newwidth = $width;
                    $newheight = round($media_size[1] / $ratio);
                } else if ( $width == 0 && $height == 0 ) {
                    if ( $media_size[0] > $media_size[1] ) {        // landscape
                        $ratio = $media_size[0] / 200;
                        $newwidth = 200;
                        $newheight = round($media_size[1] / $ratio);
                    } else {    // portrait
                        $ratio = $media_size[1] / 200;
                        $newheight = 200;
                        $newwidth = round($media_size[0] / $ratio);
                    }
                } else if ( $width == 0 && $height > 0 ) {
                    $ratio = $height / $media_size[1];
                    $newheight = $height;
                    $newwidth = round($media_size[0] * $ratio);
                } else {
                    $newwidth = $width;
                    $newheight = $height;
                }
                $album_image = '<img src="' . $tnImage . '" ' . $alttag . ' style="';
                if ( $nosize == 0 && $height != -1 && $width != -1 ) {
                    $album_image .= 'width:' . $newwidth . 'px;height:' . $newheight . 'px;';
                }
                $album_image .= 'border:none;" />';

                $tagtext = $album_image;
                if ( $linkID == 0 ) {
                    $url = $_MG_CONF['site_url'] . '/album.php?aid=' . $parm1;
                } else {
                    if ( $linkID < 1000000 ) {
                        $url = $_MG_CONF['site_url'] . '/album.php?aid=' . $linkID;
                    } else {
                        $url = $_MG_CONF['site_url'] . '/media.php?s=' . $linkID;
                    }
                }
                if ( $enable_link == 0 ) {
                    $link = $tagtext;
                } else {
                    $link = '<a href="' . $url . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>' . $tagtext . '</a>';
                }
                $T->set_var(array(
                    'ss_count'   => $ss_count,
                    'align'      =>  $align,
                    'autotag'    => $link,
                    'caption'    => $caption,
                    'width'      => $newwidth,
                    'framewidth' => $newwidth + 10,
                    'media_thumbnail' => $tnImage,
                    'media_width' => $newwidth,
                    'media_height' => $newheight,
                    'classes' => $classes,
                    'align' => $align,
                ));
                if ( $enable_link ) {
                    $T->set_var('url',$url);
                    $T->set_var('target',$target);
                }

                if ( $align == 'left' || $align == 'right' ) {
                    $T->set_var('float','float:' . $align . ';');
                } else {
                    $T->set_var('float','');
                }
                if ( $align == 'left' ) {
                    $T->set_var('margin-right','margin-right:15px;');
                } else {
                    $T->set_var('margin-right','');
                }
                $T->parse('output','tag');
                $link = $T->finish($T->get_var('output'));
                if ( $align == 'center' ) {
                    $link = '<center>' . $link . '</center>';
                }
                if ( $destination != 'block' ) {
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                } else {
                    $autoTagCount = $mgAutoTagArray['count'];
                    $mgAutoTagArray['tags'][$autoTagCount] = $link;
                    $mgAutoTagArray['count']++;
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                }
                return $content;
                break;
            case 'alink' :
                if ( $parm1 == '' || $parm1 == 0 ) {
                    $side_count--;
                    return $content;
                }
                if ( !isset($MG_albums[$parm1]->id) || $MG_albums[$parm1]->access == 0 ) {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    $side_count--;
                    return $content;
                }
                if ( $caption == '' ) {
                    $caption = $MG_albums[$parm1]->title;
                }
                $link = '<a href="'.$_MG_CONF['site_url'] . '/album.php?aid=' . $MG_albums[$parm1]->id .'">'.$caption.'</a>';
                $content = str_replace ($autotag['tagstr'], $link, $content);
                return $content;

                break;
            case 'media' :
            /* image, oimage and img are depreciated */
            case 'image' :
            case 'oimage' :
            case 'img' :
                if ( $parm1 == '' || $parm1 == 0 ) {
                    return $content;
                }
                $direct_link = '';
                $ss_count++;
                if ( $border == 0 ) {
                    $T->set_file(array('tag'    => 'autotag_nb.thtml'));
                } else {
                    $T->set_file (array('tag'      => 'autotag.thtml'));
                }
                if ( $tag != '' ) {
                    $alttag = ' alt="' . $tag . '" title="' . $tag . '"';
                } else {
                    $alttag = ' alt=""';
                }
                $sql = "SELECT ma.album_id,m.media_title,m.media_type,m.media_filename,m.media_mime_ext,m.mime_type,m.media_tn_attached,m.remote_url FROM {$_TABLES['mg_media']} AS m LEFT JOIN {$_TABLES['mg_media_albums']} AS ma ON m.media_id=ma.media_id WHERE m.media_id='" . DB_escapeString($parm1) . "'";
                $result = DB_query($sql);
                if ( DB_numRows($result) > 0 ) {
                    $row = DB_fetchArray($result);
                    $aid = $row['album_id'];
                    if ( !isset($MG_albums[$aid]->id) || $MG_albums[$aid]->access == 0 ) {
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                        return $content;
                    }
                    if ( $caption == '' && $_MG_CONF['autotag_caption'] ) {
                        $caption = $row['media_title'];
                    }
                    switch( $row['media_type'] ) {
                        case 0 :    // standard image
                            if ($autotag['tag'] == 'oimage' ) {
                                if ( $_MG_CONF['discard_originals'] == 1 ) {
                                    $default_thumbnail = 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                                } else {
                                    $default_thumbnail = 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                                }
                            } else {
                                switch ( $src ) {
                                    case 'orig' :
                                        if ( $_MG_CONF['discard_original'] == 1 ) {
                                            $default_thumbnail = 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                                        } else {
                                            $default_thumbnail = 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                                        }
                                        break;
                                    case 'disp' :
                                        $default_thumbnail = 'disp/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];
                                        break;
                                    case 'tn' :
                                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                                                $default_thumbnail = 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                                                break;
                                            }
                                        }
                                        break;
                                    default :
                                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                                                $default_thumbnail = 'tn/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                                                break;
                                            }
                                        }
                                        break;
                                }
                                $foundImageDefaultThumbnail = false;
                                foreach ($_MG_CONF['validExtensions'] as $ext ) {
                                    if ( file_exists($_MG_CONF['path_mediaobjects'] . $link_src .'/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext) ) {
                                        $direct_link = $_MG_CONF['mediaobjects_url'] . '/'.$link_src.'/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                                        $default_thumbnail = $link_src . '/' . $row['media_filename'][0] . '/' . $row['media_filename'] . $ext;
                                        $foundImageDefaultThumbnail = true;
                                        break;
                                    }
                                }
                                if ($foundImageDefaultThumbnail === false) {
                                    $default_thumbnail = 'placeholder.svg';
                                }
                            }
                            break;
                        case 1 :    // video file
                            switch ( $row['mime_type'] ) {
                                case 'application/x-shockwave-flash' :
                                    $default_thumbnail = 'flash.png';
                                    break;
                                case 'video/quicktime' :
                                case 'video/mpeg' :
                                case 'video/x-m4v' :
                                    $default_thumbnail = 'quicktime.png';
                                    break;
                                case 'video/x-ms-asf' :
                                case 'video/x-ms-wvx' :
                                case 'video/x-ms-wm' :
                                case 'video/x-ms-wmx' :
                                case 'video/x-msvideo' :
                                case 'application/x-ms-wmz' :
                                case 'application/x-ms-wmd' :
                                    $default_thumbnail = 'wmp.png';
                                    break;
                                default :
                                    $default_thumbnail = 'video.png';
                                    break;
                            }
                            $src = 'tn';
                            break;
                        case 2 :    // music file
                            $src = 'tn';
                            $default_thumbnail = 'audio.png';
                            break;
                    }
                    if ( $row['media_tn_attached'] == 1 && ($src != 'orig' && $src != 'disp')) {
                        foreach ($_MG_CONF['validExtensions'] as $ext ) {
                            if ( file_exists($_MG_CONF['path_mediaobjects'] . 'tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext) ) {
                                $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . 'tn/'.  $row['media_filename'][0] . '/tn_' . $row['media_filename'] . $ext;
                                break;
                            }
                        }
                    } else if ($foundImageDefaultThumbnail === true) {
                        $media_thumbnail      = $_MG_CONF['mediaobjects_url'] . '/' . $default_thumbnail;
                        $media_thumbnail_file = $_MG_CONF['path_mediaobjects'] . $default_thumbnail;
                    } else {
                        $media_thumbnail      = $_MG_CONF['assets_url'] . '/' . $default_thumbnail;
                        $media_thumbnail_file = $_MG_CONF['path_assets'] . $default_thumbnail;
                    }

                    if ( $autotag['tag'] == 'img' ) {
                        if ( $align != '' && $align != 'center' ) {
                            $album_image = '<span class="'.$classes.'" style="float:' . $align . ';padding:5px;"><img src="' . $media_thumbnail . '" ' . $alttag . 'style="border:none;" /></span>';
                        } else {
                            $album_image = '<img class="'.$classes.'" src="' . $media_thumbnail . '" ' . $alttag . 'style="border:none;" />';
                        }
                    } else {
                        $album_image = '<img class="'.$classes.'" src="' . $media_thumbnail . '" ' . $alttag . 'style="border:none;" />';
                    }
                } else {
                    return $content; // no image found
                }
                $mediaSize = @getimagesize($media_thumbnail_file);
                if ( $mediaSize == false ) {
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                    return $content;
                }

                if ( $autotag['tag'] == 'oimage' ) { //|| $src == 'orig') {
                    $newwidth = $mediaSize[0];
                    $newheight = $mediaSize[1];
                } else {
                    if ( $width > 0 ) {
                        $tn_height = (int) $width;
                    } else {
                        switch ($src) {
                            case 'orig' :
                                $tn_height = $mediaSize[0];
                                break;
                            case 'disp' :
                                $tn_height = $mediaSize[0];
                                break;
                            case 'tn' :
                                $tn_height = 200;
                                break;
                            default :
                                $tn_height = 200;
                                break;
                        }
                    }

                    if ( $mediaSize[0] > $mediaSize[1] ) {
                        $ratio = $mediaSize[0] / $tn_height;
                        $newwidth = $tn_height;
                        $newheight = round($mediaSize[1] / $ratio);
                    } else {
                        $ratio = $mediaSize[1] / $tn_height;
                        $newheight = $tn_height;
                        $newwidth = round($mediaSize[0] / $ratio);
                    }
                }
                $album_image = '<img class="'.$classes.'" src="' . $media_thumbnail . '" ' . $alttag . ' style=';
                if ( $nosize == 0 && $height != -1 && $width != -1 ) {
                    $album_image .= '"width:' . $newwidth . 'px;height:' . $newheight . 'px;';
                }
                $album_image .= 'border:none;" />';

                $tagtext = $album_image;
                $link = '';
                if ( $alt == 1 && $row['remote_url'] != '' ) {
                    $url = $row['remote_url'];
                    if ( $autotag['tag'] != 'image' && $enable_link != 0 && $MG_albums[$aid]->hidden != 1 ) {
                        $link = '<a href="' . $url . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>' . $tagtext . '</a>';
                    } else {
                        $link = $tagtext;
                    }
                } else if ( $linkID == 0 ) {
                    if ( $MG_albums[$aid]->hidden != 1 ) {
                        $url = $_MG_CONF['site_url'] . '/media.php?s=' . $parm1;
                    } else {
                        $url = '';
                        $link = '';
                    }
                } else {
                    if ( $linkID < 1000000 ) {
                        if ( isset($MG_albums[$linkID]->id ) ) {
                            $url = $_MG_CONF['site_url'] . '/album.php?aid=' . $linkID;
                            if ( $autotag['tag'] != 'image' && $MG_albums[$linkID]->hidden != 1 && $enable_link != 0 ) {
                                $link = '<a href="' . $url . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>' . $tagtext . '</a>';
                             } else {
                                $link = $tagtext;
                            }
                        } else {
                            if ( $MG_albums[$aid]->hidden != 1 ) {
                                $url = $_MG_CONF['site_url'] . '/media.php?s=' . $parm1;
                            } else {
                                $url = '';
                            }
                        }
                    } else {
                        $linkAID = (int) DB_getItem($_TABLES['mg_media_albums'],'album_id','media_id="' . DB_escapeString($linkID) . '"');
                        if ( $linkAID != 0 ) {
                            $url = $_MG_CONF['site_url'] . '/media.php?s=' . $linkID;
                            if ( $autotag['tag'] != 'image' && $MG_albums[$linkAID]->hidden != 1 && $enable_link != 0 ) {
                                $link = '<a href="' . $url . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>' . $tagtext . '</a>';
                            } else {
                                $link = $tagtext;
                            }
                        } else {
                            $url = $_MG_CONF['site_url'] . '/media.php?s=' . $parm1;
                        }
                    }
                }

                $dolightbox = false;
                if ( $link == '' ) {
                    if ( $autotag['tag'] != 'image' && ($MG_albums[$aid]->hidden != 1 || $enable_link == 2) && $enable_link != 0) {
                        if ( $enable_link == 2 && $direct_link != '' ) {
                            if ( $_MG_CONF['disable_lightbox'] == true ) {
                                $link = $tagtext;
                            } else {
                                $dolightbox = true;
                                $link = '<a href="' . $direct_link . '" rel="lightbox" data-uk-lightbox title="' . strip_tags(str_replace('$','&#36;',$caption)) . '">' . $tagtext . '</a>';
                            }
                        } else {
                            $link = '<a href="' . $url . '"' . ($target=='' ? '' : ' target="' . $target . '"') . '>' . $tagtext . '</a>';
                        }
                    } else {
                        $link = $tagtext;
                    }
                }
                if ( $autotag['tag'] == 'img' ) {
                    if ( $align != '' && $align != 'center' ) {
                        $link = '<span class="'.$classes.'" style="float:' . $align . ';padding:5px;">'.$link.'</span>';
                    }
                    if ( $destination != 'block' ) {
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    } else {
                        $autoTagCount = $mgAutoTagArray['count'];
                        $mgAutoTagArray['tags'][$autoTagCount] = $link;
                        $mgAutoTagArray['count']++;
                        $link = '';
                        $content = str_replace ($autotag['tagstr'], $link, $content);
                    }
                    return $content;
                }
                $T->set_var(array(
                    'ss_count'  => $ss_count,
                    'align'     => $align,
                    'autotag'   => $link,
                    'caption'   => $caption,
                    'width'     => $newwidth,
                    'framewidth' => $newwidth + 10,

                    'media_thumbnail' => $media_thumbnail,
                    'media_width' => $newwidth,
                    'media_height' => $newheight,
                    'classes' => $classes,
                    'align' => $align,
                ));
                if ( $enable_link ) {
                    $T->set_var('url',$url);
                    $T->set_var('target',$target);
                }
                if ( $dolightbox ) {
                    $T->set_var(array(
                        'lightbox' => true,
                        'url' => $direct_link,
                    ));
                } else {
                    $T->unset_var('lightbox');
                }

                if ( $align == 'left' || $align == 'right' ) {
                    $T->set_var('float','float:' . $align . ';');
                } else {
                    $T->set_var('float','');
                }
                if ( $align == 'left' ) {
                    $T->set_var('margin-right','margin-right:15px;');
                } else {
                    $T->set_var('margin-right','');
                }
                $T->parse('output','tag');
                $link = $T->finish($T->get_var('output'));

                if ( $align == 'center' ) {
                    $link = '<center>' . $link . '</center>';
                }
                if ( $destination != 'block' ) {
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                } else {
                    $autoTagCount = $mgAutoTagArray['count'];
                    $mgAutoTagArray['tags'][$autoTagCount] = $link;
                    $mgAutoTagArray['count']++;
                    $link = '';
                    $content = str_replace ($autotag['tagstr'], $link, $content);
                }
                return $content;
                break;
        }
    }
}
?>