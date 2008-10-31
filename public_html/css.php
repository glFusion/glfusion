<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | css.php                                                                  |
// |                                                                          |
// | CSS Outputter                                                            |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on work from the DokuWiki Project                                  |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Andreas Gohr      - andi AT splitbrain DOT org                  |
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

require_once 'lib-common.php';

$charSet = COM_getCharset();

header('Content-Type: text/css; charset='.$charSet);
css_out();


/**
 * Output all needed Styles
 *
 */
function css_out(){
    global $_CONF, $_PLUGINS, $_TABLES;

    $tpl = $_CONF['theme'];

    $cacheID = 'css_' . $tpl;

    // Array of needed files

    $files   = array();

    // Let's look in the custom directory first...
    if ( file_exists($_CONF['path_layout'] .'custom/style.css') ) {
        $files[] = $_CONF['path_layout'] . 'custom/style.css';
    } else {
        $files[] = $_CONF['path_layout'] . 'style.css';
    }
    if ( file_exists($_CONF['path_layout'] .'custom/style-colors.css') ) {
        $files[] = $_CONF['path_layout'] . 'custom/style-colors.css';
    } else {
        $files[] = $_CONF['path_layout'] . 'style-colors.css';
    }

    foreach ( $_PLUGINS as $pi_name ) {
        if ( function_exists('plugin_getheadercss_'.$pi_name) ) {
            $function = 'plugin_getheadercss_'.$pi_name;
            $pHeader = array();
            $pHeader = $function();
            if ( is_array($pHeader) ) {
                foreach($pHeader AS $item => $file) {
                    $files[] = $file;
                }
            }
        }
    }

    // check cache age & handle conditional request
    header('Cache-Control: public, max-age=3600');
    header('Pragma: public');
    
    if (!isset($_REQUEST['purge'])) { //support purge request
        $cache_time = CACHE_get_instance_update($cacheID, false);
        if (css_cacheok($cache_time,$files)) {
            http_conditionalRequest($cache_time, false);
            echo CACHE_check_instance($cacheID);
            return;
        }
    }

    http_conditionalRequest(time());

    // start output buffering and build the stylesheet
    ob_start();

    // load files
    foreach($files as $file) {
        css_loadfile($file);
    }

    // end output buffering and get contents
    $css = ob_get_contents();
    ob_end_clean();

    // compress whitespace and comments
    if($_CONF['compress_css']){
        $css = css_compress($css);
    }
    // save cache file
    CACHE_create_instance($cacheID, $css, false);
    $randID = rand();
    DB_save($_TABLES['vars'],'name,value',"'cacheid',$randID");
    // finally send output
    print $css;
    unset($css);
}

/**
 * Checks if a CSS Cache file still is valid
 *
 */
function css_cacheok($ctime,$files){
    if(!$ctime) { return false; } //There is no cache

    // now walk the files

    foreach($files as $file){
        if(@filemtime($file) > $ctime){
            return false;
        }
    }
    return true;
}


/**
 * Loads a given file
 */
function css_loadfile($file){
    if(!@file_exists($file)) return '';
    $css = readfile($file);
    return;
}



/**
 * Very simple CSS optimizer
 *
 */
function css_compress($css){
    //strip comments through a callback
    $css = preg_replace_callback('#(/\*)(.*?)(\*/)#s','css_comment_cb',$css);

    //strip (incorrect but common) one line comments
    $css = preg_replace('/(?<!:)\/\/.*$/m','',$css);

    // strip whitespaces
    $css = preg_replace('![\r\n\t ]+!',' ',$css);
    $css = preg_replace('/ ?([:;,{}\/]) ?/','\\1',$css);

    // shorten colors
    $css = preg_replace("/#([0-9a-fA-F]{1})\\1([0-9a-fA-F]{1})\\2([0-9a-fA-F]{1})\\3/", "#\\1\\2\\3",$css);

    return $css;
}

/**
 * Callback for css_compress()
 *
 * Keeps short comments (< 5 chars) to maintain typical browser hacks
 *
 */
function css_comment_cb($matches){
    if(strlen($matches[2]) > 4) return '';
    return $matches[0];
}

/**
 * Checks and sets HTTP headers for conditional HTTP requests
 *
 * @author   Simon Willison <swillison@gmail.com>
 * @link     http://simon.incutio.com/archive/2003/04/23/conditionalGet
 * @param    timestamp $timestamp lastmodified time of the cache file
 * @returns  void or void with previously header() commands executed
 */
function http_conditionalRequest($timestamp){
  // A PHP implementation of conditional get, see
  //   http://fishbowl.pastiche.org/archives/001132.html
  $last_modified = substr(gmdate('r', $timestamp), 0, -5).'GMT';
  $etag = '"'.md5($last_modified).'"';
  // Send the headers
  header("Last-Modified: $last_modified");
  header("ETag: $etag");
  // See if the client has provided the required headers
  if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
    $if_modified_since = stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']);
  }else{
    $if_modified_since = false;
  }

  if (isset($_SERVER['HTTP_IF_NONE_MATCH'])){
    $if_none_match = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
  }else{
    $if_none_match = false;
  }

  if (!$if_modified_since && !$if_none_match){
    return;
  }

  // At least one of the headers is there - check them
  if ($if_none_match && $if_none_match != $etag) {
    return; // etag is there but doesn't match
  }

  if ($if_modified_since && $if_modified_since != $last_modified) {
    return; // if-modified-since is there but doesn't match
  }

  // Nothing has changed since their last request - serve a 304 and exit
  header('HTTP/1.0 304 Not Modified');

  // don't produce output, even if compression is on
  ob_end_clean();
  exit;
}
?>