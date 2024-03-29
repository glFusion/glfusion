<?php
/**
* glFusion CMS
*
* glFusion Language Selector
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Based on prior work Copyright (C) 2006-2008 by the following authors:
*  Dirk Haun         dirk@haun-online.de
*  Euan McKay
*  LWC
*
*/

require_once 'lib-common.php';

/**
* Switch language in a URL.
*
* @param    string  $url        current URL
* @param    string  $newlang    new language to switch to
* @param    string  $oldlang    old, i.e. current language
* @return   string              new URL after the language switch
*
*/
function switch_language($url, $newlang, $oldlang)
{
    global $_CONF;

    $retval = '';

    if (empty($newlang) || empty($oldlang) ||
            (strlen($newlang) != strlen($oldlang))) {
        return $url;
    }

    $lang_len = strlen($oldlang);
    $url_rewrite = false;
    $q = false;

    if ($_CONF['url_rewrite']) {
        // check for "rewritten" URLs with a '?', e.g. search query highlighting
        $q = strpos($url, '?');
        if ($q === false) {
            $url_rewrite = true;
        } elseif (substr($url, $q - 4, 4) != '.php') {
            $url_rewrite = true;
        }
    }

    if ($url_rewrite) {
        if ($q === false) {
            $the_url = $url;
        } else {
            $the_url = substr($url, 0, $q);
        }

        // for "rewritten" URLs we assume that the first parameter after
        // the script name is the ID, e.g. /article.php/story-id-here_en
        $changed = false;
        $p = explode('/', $the_url);
        $parts = count($p);
        for ($i = 0; $i < $parts; $i++) {
            if (substr($p[$i], -4) == '.php') {
                // found the script name - assume next parameter is the ID
                if (isset($p[$i + 1])) {
                    if (substr($p[$i + 1], -($lang_len + 1)) == '_' . $oldlang) {
                        $p[$i + 1] = substr_replace($p[$i + 1], $newlang,
                                                    -$lang_len);
                        $changed = true;
                    }
                }
                break;
            }
        }

        if ($changed) {
            // merge the pieces back together
            if ($q === false) {
                $url = implode('/', $p);
            } else {
                $url = implode('/', $p) . substr($url, $q);
            }
        }

        $retval = $url;
    } else { // URL contains '?' or '&'
        $url = explode('&', $url);
        $urlpart = $url[0];
        if (count($url) > 1) {
            array_shift($url);
            $extra_vars = '&' . implode('&', $url);
        } else {
            $extra_vars = '';
        }

        if (substr($urlpart, -($lang_len + 1)) == '_' . $oldlang) {
            $urlpart = substr_replace($urlpart, $newlang, -$lang_len);
        }

        $retval = $urlpart . $extra_vars;
    }

    return $retval;
}


// MAIN
$ret_url = '';
if (isset($_SERVER['HTTP_REFERER'])) {
    if (strpos($_SERVER['HTTP_REFERER'], $_CONF['site_url']) !== false) {
        $ret_url = COM_sanitizeUrl($_SERVER['HTTP_REFERER']);
    }
}

// if not allowed, just ignore and return
if ($_CONF['allow_user_language'] == 1) {

    COM_setArgNames(array('lang'));

    $lang = strtolower(COM_applyFilter(COM_getArgument('lang')));
    $lang = preg_replace('/[^a-z0-9\-_]/', '', $lang);
    $oldlang = COM_getLanguageId();

    // do we really have a new language to switch to?
    if (!empty($lang) && array_key_exists($lang, $_CONF['language_files'])) {

        // does such a language file exist?
        $langfile = $_CONF['language_files'][$lang];
        if (is_file($_CONF['path_language'] . $langfile . '.php')) {

            // Set the language cookie.
            // Mainly used for anonymous users so the rest of their session
            // will remain in the selected language
            setcookie($_CONF['cookie_language'], $langfile, time() + 31536000,
                      $_CONF['cookie_path'], $_CONF['cookiedomain'],
                      $_CONF['cookiesecure']);

            // if user is not anonymous, store the preference in the database
            if (!COM_isAnonUser()) {
                DB_query("UPDATE {$_TABLES['users']} SET language = '{$langfile}' WHERE uid = {$_USER['uid']}");
            }
        }
    }

    // Change the language ID if needed
    if (!empty($ret_url) && !empty($lang) && !empty($oldlang)) {
        $ret_url = switch_language($ret_url, $lang, $oldlang);
    }
}

// if the user didn't come from our site, send them to our index page
if (empty($ret_url)) {
    $ret_url = $_CONF['site_url'] . '/';
}

header("Location: $ret_url");

?>