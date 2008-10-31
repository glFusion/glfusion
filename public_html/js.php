<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | js.php                                                                   |
// |                                                                          |
// | JavaScript Consolidator                                                  |
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


header('Content-Type: text/javascript; charset=utf-8');
js_out();

/**
 * Output all needed JavaScript
 */
function js_out(){
    global $_CONF, $_PLUGINS, $themeAPI;

    $cacheID = 'js';

    /*
     * Static list of standard JavaScript used by glFusion...
     */

    $files = array(
        $_CONF['path_html'] . 'javascript/mootools/mootools-release-1.11.packed.js',
        $_CONF['path_html'] . 'fckeditor/fckeditor.js',
        $_CONF['path_html'] . 'javascript/common.js',
        $_CONF['path_html'] . 'javascript/fValidator.js',
        $_CONF['path_html'] . 'javascript/mootools/gl_mooreflection.js',
        $_CONF['path_html'] . 'javascript/mootools/gl_moomenu.js',

    );

    if ( $themeAPI < 2 ) {
        $files[] = $_CONF['path_html'] . 'javascript/sitetailor_ie6vertmenu.js';
    }
    /*
     * Check to see if the theme has any JavaScript to include...
     */

    $function = $_CONF['theme'] . '_themeJS';

    if( function_exists( $function ))
    {
        $jTheme = $function( );
        if ( is_array($jTheme) ) {
            foreach($jTheme AS $item => $file) {
                $files[] = $file;
            }
        }
    }

    /*
     * Let the plugins add their JavaScript needs here...
     */

    foreach ( $_PLUGINS as $pi_name ) {
        if ( function_exists('plugin_getheaderjs_'.$pi_name) ) {
            $function = 'plugin_getheaderjs_'.$pi_name;
            $pHeader = array();
            $pHeader = $function();
            if ( is_array($pHeader) ) {
                foreach($pHeader AS $item => $file) {
                    $files[] = $file;
                }
            }
        }
    }

    /*
     * Let the plugins add any global JS variables
     */
    foreach ( $_PLUGINS as $pi_name ) {
        if ( function_exists('plugin_getglobaljs_'.$pi_name) ) {
            $function = 'plugin_getglobaljs_'.$pi_name;
            $globalJS = array();
            $globalJS = $function();
            if ( is_array($globalJS) ) {
                foreach($globalJS AS $name => $value) {
                    $pluginJSvars[$name] = $value;
                }
            }
        }
    }

    // check cache age & handle conditional request
    header('Cache-Control: public, max-age=3600');
    header('Pragma: public');

    $cache_time = CACHE_get_instance_update($cacheID);
    if (js_cacheok($cache_time,$files)){
        http_conditionalRequest($cache_time);
        $js = CACHE_check_instance($cacheID);
        echo $js . LB;
        flush();
        exit;
    } else {
        http_conditionalRequest(time());
    }

    // start output buffering and build the script
    ob_start();

    // add some global variables
    print "var glfusionEditorBaseUrl = '".$_CONF['site_url']."';";
    print "var glfusionLayoutUrl     = '".$_CONF['layout_url']."';";
    print "var glfusionStyleCSS      = '".$_CONF['site_url']."/css.php';";

    // send any global plugin JS vars

    if ( is_array($pluginJSvars) ) {
        foreach ($pluginJSvars AS $name => $value) {
            print "var " . $name . " = '".$value."';";
        }
    }

    // load files
    foreach($files as $file){
        js_load($file);
    }

    // end output buffering and get contents
    $js = ob_get_contents();
    ob_end_clean();

    $js .= "\n"; // https://bugzilla.mozilla.org/show_bug.cgi?id=316033

    // save cache file
    CACHE_create_instance($cacheID, $js, 0);

    // finally send output
    print $js;
    unset($js);
    ob_flush();
    flush();
    exit;
}

/**
 * Load the given file, handle include calls and print it
 */
function js_load($file){
    if (!@file_exists($file))
        return;

    $js = readfile($file);

    return;
}

/**
 * Checks if a JavaScript Cache file still is valid
 *
 */
function js_cacheok($ctime,$files)
{
    if(!$ctime) return false; //There is no cache

    // now walk the files
    foreach($files as $file){
        if(@filemtime($file) > $ctime){
            return false;
        }
    }
    return true;
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

//Setup VIM: ex: et ts=4 enc=utf-8 :
?>