<?php
/**
* glFusion CMS
*
* glFusion caching library
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2007-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*   Joe Mucchiello  joe AT throwingdice DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Cache\Cache;
use \glFusion\Log\Log;

/* This should be the only glFusion-isms in the file. Didn't want to "infect" the class but it was necessary.
* These options are global to all templates.
*/
$TEMPLATE_OPTIONS = array(
    'path_cache'    => $_CONF['path_data'].'layout_cache/', // location of template cache
    'path_prefixes' => array(                               // used to strip directories off file names. Order is important here.
        $_CONF['path_themes'],                              // this is not path_layout. When stripping directories, you want files in different themes to end up in different directories.
        $_CONF['path'],
        '/'                                                 // this entry must always exist and must always be last
    ),
// incl_phpself_header is depreciated
    'incl_phpself_header' => true,                          // set this to true if your template cache exists within your web server's docroot.
    'cache_by_language' => true,                            // create cache directories for each language. Takes extra space but moves all $LANG variable text directly into the cached file
    'default_vars' => array(                                // list of vars found in all templates.
        'site_url' => $_CONF['site_url'],
        'site_admin_url' => $_CONF['site_admin_url'],
        'layout_url' => $_CONF['layout_url'],
        'site_name' => $_CONF['site_name'],
    ),
    'hook' => array(),
);

$TEMPLATE_OPTIONS['hook']['set_root'] = '_template_set_root';

function _template_set_root($root) {
    global $_CONF, $_USER;

    $retval = array();

    if (!is_array($root)) {
        $root = array($root);
    }

    foreach ($root as $r) {

        if (substr($r, -1) == '/') {
            $r = substr($r, 0, -1);
        }
        if ( strpos($r,"plugins") != 0 ) {
            $p = str_replace($_CONF['path'],$_CONF['path_themes'] . $_USER['theme'] . '/', $r);
            $x = str_replace("/templates", "",$p);
            $retval[] = $x;
        }
        if ( strpos($r,"autotags") != 0 ) {
            $p = str_replace($_CONF['path'],$_CONF['path_themes'] . $_USER['theme'] . '/', $r);
            $x = str_replace("/system", "",$p);
            $retval[] = $x;
        }
        if ( $r != '' ) {
            $retval[] = $r . '/custom';
            $retval[] = $_CONF['path_data'].'templates/'.$_CONF['theme'].'/';
            $retval[] = $r;
            if ( $_USER['theme'] != 'cms' ) {
                $retval[] = $_CONF['path_themes'] . 'cms/' .substr($r, strlen($_CONF['path_layout']));
            }
        }
    }
    return $retval;
}

/******************************************************************************
* Internal function used to traverse directory tree when cleaning cache
*
* usage: cache_clean_directories($plugin);
*
* @param  $path            Directory path being cleaned
* @param  $needle          String matched against cache filenames
* @access private
* @return void
*
*/
function cache_clean_directories($path, $needle = '', $since = 0)
{
    if ($dir = @opendir($path)) {
        while (false !== ($entry = readdir($dir))) {
            if ($entry == '.' || $entry == '..' || $entry == '.svn' || is_link($entry)) {
            } elseif (is_dir($path . '/' . $entry)) {
                cache_clean_directories($path . '/' . $entry, $needle);
                @rmdir($path . '/' . $entry);
            } elseif (empty($needle) || strpos($entry, $needle) !== false) {
                if (!$since || @filectime($path . '/' . $entry) <= $since) {
                    @unlink($path . '/' . $entry);
                }
            }
        }
        @closedir($dir);
    }
}

/******************************************************************************
* Removes all cached files associated with a plugin.
*
* usage: CACHE_cleanup_plugin($plugin);
*
* @param  $plugin          String containing the plugin's name
* @access public
* @return void
*
*/
function CACHE_cleanup_plugin($plugin)
{
    global $TEMPLATE_OPTIONS;

    if (!empty($plugin)) {
        $plugin = str_replace(array('..', '/', '\\'), '', $plugin);
        $plugin = '__' . $plugin . '__';
    }
    $path_cache = substr($TEMPLATE_OPTIONS['path_cache'], 0, -1);
    cache_clean_directories($path_cache, $plugin);
}

/******************************************************************************
* Deletes an instance of the specified instance identifier
*
* usage: CACHE_remove_instance($iid, $glob);
*
* @param  $iid            A globally unique instance identifier.
* @access public
* @return void
* @see    check_instance, create_instance
*
*/
function CACHE_remove_instance($iid)
{
    global $TEMPLATE_OPTIONS;

    $tag = $iid;

    $iid = str_replace(array('..', '/', '\\', ':'), '', $iid);
    $iid = str_replace('-','_',$iid);
/*
    $path_cache = substr($TEMPLATE_OPTIONS['path_cache'], 0, -1);
    CACHE_clean_directories($path_cache, 'instance__'.$iid);
*/
    $c = Cache::getInstance();
    $c->delete($iid);
    $c->deleteItemsByTag($tag);
}


/******************************************************************************
* Creates a cached copy of the data passed.
*
* usage: CACHE_create_instance($iid, $data, $bypass_lang);
*
* @param  $iid            A globally unique instance identifier.
* @param  $data           The data to cache
* @param  $bypass_lang    If true, the cached data is not instanced by language
* @access public
* @return void
* @see    CACHE_check_instance, CACHE_remove_instance
*
*/
function CACHE_create_instance($iid, $data, $bypass_lang = false)
{
    global $TEMPLATE_OPTIONS, $_CONF, $_SYSTEM;

    if ( isset($_SYSTEM['disable_instance_caching']) && $_SYSTEM['disable_instance_caching'] == true ) {
        return;
    }

    $c = Cache::getInstance();
    $c->set($iid,$data);
    return;
}

/******************************************************************************
* Finds a cached copy of the referenced data.
*
* usage: $data = CACHE_check_instance($iid, $bypass_lang)
*        if (!$data === false) {
*            // generate the data
*            $data = 'stuff';
*            CACHE_create_instance($iid, $data, $bypass_lang);
*        }
*        // use the data
*
* The caching functions only work with strings. If you want to store structures
* you must serialize/unserialize the data yourself:
*
*      $data = CACHE_check_instance($iid);
*      if ($data === false) {
*          $data = new SomeObj();
*          CACHE_create_instance($iid, serialize($data));
*      } else {
*          $data = unserialize($data);
*      }
*      // use the object
*
* @param  $iid            A globally unique instance identifier.
* @param  $bypass_lang    If true, the cached data is not instanced by language
* @access public
* @return the data string or false is there is no such instance
* @see    CACHE_check_instance, CACHE_remove_instance
*
*/
function CACHE_check_instance($iid, $bypass_lang = false)
{
    global $_CONF, $_SYSTEM;

    if ( isset($_SYSTEM['disable_instance_caching']) && $_SYSTEM['disable_instance_caching'] == true ) {
        return false;
    }

    $c = Cache::getInstance();
    $str = $c->get($iid);
    if ( $str !== null ) {
        return $str === FALSE ? false : $str;
    }
    return false;
}

/******************************************************************************
* Returns the time when the referenced instance was generated.
*
* usage: $time = CACHE_get_instance_update($iid, $bypass_lang = false)
*
* @param  $iid            A globally unique instance identifier.
* @param  $bypass_lang    If true, the cached data is not instanced by language
* @access public
* @return unix_timestamp of when the instance was generated or false
* @see    CACHE_check_instance, CACHE_remove_instance
*
*/
function CACHE_get_instance_update($iid, $bypass_lang = false)
{
    global $_CONF, $_SYSTEM;

    if ( isset($_SYSTEM['disable_instance_caching']) && $_SYSTEM['disable_instance_caching'] == true ) {
        return;
    }

    $c = Cache::getInstance();
    return $c->getModificationDate($iid);
}

/******************************************************************************
* Generates a full path to the instance file. Should really only be used
* internally but there are probably reasons to use it externally.
*
* usage: $time = CACHE_instance_filename($iid, $bypass_lang = false)
*
* @param  $iid            A globally unique instance identifier.
* @param  $bypass_lang    If true, the cached data is not instanced by language
* @access public
* @return unix_timestamp of when the instance was generated or false
* @see    CACHE_create_instance, CACHE_check_instance, CACHE_remove_instance
*
*/
function CACHE_instance_filename($iid,$bypass_lang = false)
{
    global $TEMPLATE_OPTIONS, $_CONF;

    $path_cache = $TEMPLATE_OPTIONS['path_cache'];
    if (!$bypass_lang && $TEMPLATE_OPTIONS['cache_by_language']) {
        $path_cache .= $_CONF['language'] . '/';
    }
    $iid = CACHE_sanitizeFilename($iid, true);
    $filename = $path_cache.'instance__'.$iid.'.php';

    return $filename;
}

function CACHE_clearCSS()
{
    global $_CONF, $_SYSTEM;

    if ( !isset($_CONF['css_cache_filename']) ) {
        $_CONF['css_cache_filename'] = 'style.cache';
    }
    if ( isset($_SYSTEM['use_direct_style_js']) && $_SYSTEM['use_direct_style_js'] ) {
        foreach (glob($_CONF['path_layout'].$_CONF['css_cache_filename']."*.*") as $filename) {
            @unlink($filename);
        }
    }
}

function CACHE_clearJS()
{
    global $_CONF, $_SYSTEM;

    if ( !isset($_CONF['js_cache_filename']) ) {
        $_CONF['js_cache_filename'] = 'js.cache';
    }
    if ( isset($_SYSTEM['use_direct_style_js']) && $_SYSTEM['use_direct_style_js'] ) {
        foreach (glob($_CONF['path_layout'].$_CONF['js_cache_filename']."*.*") as $filename) {
            @unlink($filename);
        }
    }
}

/******************************************************************************
* Generates a hash based on the current user's secutiry profile.
*
* Currently that is just a list of groups the user is a member of but if
* additional data is found to be necessary for creating a unique security
* profile, this centralized function would be the place for it.
*
* usage: $hash = CACHE_security_hash()
*        $instance = "somedata__$someid__$hash";
*        CACHE_create_instance($instance, $thedata);
*
* @access public
* @return a string uniquely identifying the user's security profile
*
*/
function CACHE_security_hash()
{
    global $_GROUPS, $_USER;

    static $hash = NULL;

    if (empty($hash)) {
        $groups = implode(',',$_GROUPS);
        $hash = strtolower(md5($groups));
        if ( !empty($_USER['tzid']) ) {
            $hash .= 'tz'.md5($_USER['tzid']);
        }
    }
    return $hash;
}

function CACHE_sanitizeFilename($filename, $allow_dots = true)
{
    if ($allow_dots) {
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
        $filename = str_replace('..', '', $filename);
    } else {
        $filename = preg_replace('/[^a-zA-Z0-9\-_]/', '', $filename);
    }

    return trim($filename);
}

function CACHE_clear($plugin='')
{
    global $TEMPLATE_OPTIONS, $_CONF, $_USER, $_SYSTEM;

    if (!defined ('CONFIG_CACHE_FILE_NAME')) {
        define('CONFIG_CACHE_FILE_NAME','$$$config$$$.cache');
    }

    if (!empty($plugin)) {
        $plugin = '__' . $plugin . '__';
    }

    CTL_clearCacheDirectories($_CONF['path_data'] . 'layout_cache/', $plugin);

    @unlink($_CONF['path_data'].'cache/'.CONFIG_CACHE_FILE_NAME);

    CACHE_clearCSS();
    CACHE_clearJS();
    $c = Cache::getInstance();
    $c->clear();

    if ( defined('DVLP_DEBUG') ) {
        if (!isset($_USER['username'])) $_USER['username'] = 'undefined';
        Log::write('system',Log::DEBUG, "All Caches has been cleared", array('user' => $_USER['username'],'IP' => $_SERVER['REAL_ADDR']));
    }
}


function CTL_clearCacheDirectories($path, $needle = '')
{
    if ( $path[strlen($path)-1] != '/' ) {
        $path .= '/';
    }
    if ($dir = @opendir($path)) {
        while ($entry = readdir($dir)) {
            if ($entry == '.' || $entry == '..' || is_link($entry) || $entry == '.svn' || $entry == '.git' || $entry == 'index.html') {
                continue;
            } elseif (is_dir($path . $entry)) {
                CTL_clearCacheDirectories($path . $entry, $needle);
                @rmdir($path . $entry);
            } elseif (empty($needle) || strpos($entry, $needle) !== false) {
                @unlink($path . $entry);
            }
        }
        @closedir($dir);
    }
}


// legacy call - backward compatibility
function CTL_clearCache($plugin='')
{
    CACHE_clear($plugin);
}

function configmanager_select_cache_driver_helper($index = '')
{
    global $_CONF;

    $retval = array();

    $retval = array(
        'Files'     => 'files'
    );
    if (extension_loaded('apcu')) {
        $retval['APCU'] = 'apcu';
    }
    if (extension_loaded('memcache') && PHP_OS == 'WINNT') {
        $retval['Memcache'] = 'memcache';
    }
    if (extension_loaded('memcached')) {
        $retval['Memcached'] = 'memcached';
    }
    if (extension_loaded('redis')) {
        $retval['Redis'] = 'redis';
    }

    $retval['Disabled'] = 'Devnull';

    return $retval;
}

?>