<?php
/**
* glFusion CMS
*
* Performs security check of glFusion installation
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2015-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2002-2008 by the following authors:
*  Authors: Dirk Haun            - dirk AT haun-online DOT de
*           Jeffrey Schoolcraft  - dream AT dr3amscap3 DOT com
*
*/

require_once '../lib-common.php';
require_once 'auth.inc.php';

use \glFusion\Log\Log;

if (!SEC_inGroup ('Root')) {
    Log::logAccessViolation('Security Check');
    $display  = COM_siteHeader();
    $display .= COM_showMessageText($MESSAGE[46],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

$failed_tests = 0;

/**
* Send an HTTP HEAD request for the given URL
*
* @param    string  $url        URL to request
* @param    string  $errmsg     error message, if any (on return)
* @return   int                 HTTP response code or 777 on error
*
*/
function doHeadRequest ($url, &$errmsg)
{
    $http = new http_class;
    $http->timeout=0;
    $http->data_timeout=0;
    $http->debug=0;
    $http->html_debug=0;
    $http->user_agent = 'glFusion/' . GVERSION;

    $error = $http->GetRequestArguments($url,$arguments);
    $error = $http->Open($arguments);
    $error = $http->SendRequest($arguments);
    if ( $error == "" ) {
        $http->ReadReplyHeaders($headers);

        return $http->response_status;
    } else {
        $errmsg = $error;
        return 777;
    }
}

/**
* Determine the site's base URL to check
*
* @return   string      site URL or empty string (= nothing to check)
*
*/
function urlToCheck()
{
    global $_CONF;

    $url = '';
    if ($_CONF['path'] == $_CONF['path_html']) {
        // not good ...
        $url = $_CONF['site_url'];
    } else if (substr ($_CONF['path'], 0, strlen ($_CONF['path_html'])) == $_CONF['path_html']) {
        // "glfusion" dir in the document root
        $rest = substr ($_CONF['path'], -(strlen ($_CONF['path']) - strlen ($_CONF['path_html'])));
        $url = $_CONF['site_url'] . '/' . $rest;
    } else {
        // check for sites like www.example.com/glfusion
        $u = $_CONF['site_url'];
        if (substr ($u, -1) == '/') {
            $u = substr ($u, 0, -1);
        }
        $pos = strpos ($u, ':');
        if ($pos !== false) {
            $u2 = substr ($u, $pos + 3);
        } else {
            $u2 = $u;
        }
        $p = explode ('/', $u2);
        if (count ($p) > 1) {
            $cut = strlen ($p[count ($p) - 1]) + 1;
            $url = substr ($u, 0, -$cut) . '/';
        }
    }

    return $url;
}

/**
* Give an interpretation of the test result
*
* @param    int     $retcode    HTTP response code of the test
* @param    string  $msg        file or directory that was checked
* @return   string              text explaining the result of the test
*
*/
function interpretResult ($retcode, $msg)
{
    global $failed_tests;

    $retval = '';

    if ($retcode == 200) {
        $retval = 'Your <strong>' . $msg . '</strong> is reachable from the web.<br><em>This is a security risk and should be fixed!</em>';
        $failed_tests++;
    } elseif (($retcode == 401) || ($retcode == 403) || ($retcode == 404)) {
        $retval = 'Good! Your ' . $msg . ' is not reachable from the web.';
    } else if (is_numeric ($retcode)) {
        $retval = 'Got an HTTP result code ' . $retcode . ' when trying to test your ' . $msg . '. Not sure what to make of it ...';
        $failed_tests++;
    } else {
        $retval = $retcode;
    }

    return $retval;
}

/**
* Create a temporary file
*
* @param    string  $file   full path of the file to create
* @return   boolean         true: success; false: file creation failed
*
*/
function makeTempfile ($file)
{
    $retval = false;

    $tempfile = @fopen ($file, 'w');
    if ($tempfile) {
        $retval = true;
        fclose ($tempfile);
    }

    return $retval;
}

/**
* Perform a test
*
* @param    string  $baseurl        the site's base URL
* @param    string  $urltocheck     relative URL to check
* @param    string  $what           explanatory text: what is being checked
* @return   string                  test result as a list item
*
*/
function doTest ($baseurl, $urltocheck, $what)
{
    global $failed_tests;

    $retval = '';

    $retval .= '<li>';
    $retcode = doHeadRequest ($baseurl . $urltocheck, $errmsg);
    if ($retcode == 777) {
        $retval .= $errmsg;
        $failed_tests++;
    } else {
        $retval .= interpretResult ($retcode, $what);
    }
    $retval .= '</li>';


    return $retval;
}

/**
* Check for the existence of the install directory
*
* @return   string      text explaining the result of the test
*
* @note This test used to be part of the "Get Bent" block in lib-custom.php
*
*/
function checkInstallDir ()
{
    global $_CONF, $failed_tests;

    $retval = '';

    $installdir = $_CONF['path_admin'].'install';

    if (is_dir ($installdir)) {
        $retval .= '<li>You should remove the install directory <b>' . $installdir .'</b> once you have your site up and running without any errors.';
        $retval .= ' Keeping it around would allow malicious users the ability to modify your current install, take over your site, or retrieve sensitive information.</li>';
        $failed_tests++;
    } else {
        $retval .= '<li>Good! You have removed the install directory.</li>';
    }

    return $retval;
}

/**
* Check for accounts that still use the default password
*
* @return   string      text explaining the result of the test
*
* @note If one of our users is also using "password" as their password, this
*       test will also detect that, as it checks all accounts.
*
*/
function checkDefaultPassword ()
{
    global $_TABLES, $failed_tests;

    $retval = '';

    // check to see if any account still has 'password' as its password.
    $pwdRoot = 0;
    $pwdUser = 0;
    $rootUsers = 0;

    $sql = "SELECT ug_uid,passwd FROM {$_TABLES['group_assignments']} AS g LEFT JOIN {$_TABLES['users']} AS u ON g.ug_uid=u.uid WHERE g.ug_main_grp_id = 1";
    $result = DB_query($sql);
    $rootUsers = DB_numRows($result);
    if ( $rootUsers > 0 ) {
        for ($i=0; $i < $rootUsers; $i++) {
            list($uid,$hash) = DB_fetchArray($result);
            if ( SEC_check_hash('password', $hash) ) {
                $pwdRoot++;
            }
        }
    }
    if ($pwdRoot > 0) {
        $retval .= '<li>You still have not changed the <strong>default password</strong> from "password" on ' . $pwdRoot . ' Root user account(s).</li>';
        $failed_tests++;
    } else {
        $retval .= '<li>Good! You have changed the default account password.</li>';
    }

    return $retval;
}

// MAIN
$display = COM_siteHeader ('menu', 'glFusion Security Check');
$display .= '<div dir="ltr">' . LB;
$display .= COM_startBlock ('Results of the Security Check');

$privatePath = "";

$url = urlToCheck ();

if (!empty ($url)) {

    // determine private path

    $urlParts = parse_url ( $_CONF['site_url'] );
    if ( isset($urlParts['path'])) {
        $pos = strpos($_CONF['path_html'],$urlParts['path']);
        if ( $pos !== false && $urlParts['path'] != '/' ) {
            $rootPath = substr($_CONF['path_html'],0,$pos);
            $pos = strpos($_CONF['path'],$rootPath);
            if ( $pos !== false ) {
                $privatePath = substr($_CONF['path'],strlen($rootPath));
                if ( $privatePath[0] != '/') $privatePath = '/'.$privatePath;
                if ( $privatePath[strlen($privatePath)-1] != '/') $privatePath = $privatePath.'/';
            }
        }
    }

    $display .= '<ol>';

    if (strpos ($_SERVER['PHP_SELF'], 'public_html') !== false) {
        $display .= '<li>"public_html" should never be part of your site\'s URL.'
            ." You should have copied the files from the distribution archive's public_html/ directory to your web root directory on your host. "
            ." Please see the "
            . COM_createLink('installation instructions', "../docs/english/install.html")
            . ' for additional details.</li>';
        $failed_tests++;
    }

    $display .= checkInstallDir ();

    $urls = array
        (
        array ($privatePath.'db-config.php',                        'db-config.php'),
        array ($privatePath.'logs/error.log',                       'logs directory'),
        array ($privatePath.'plugins/staticpages/staticpages.php',  'plugins directory'),
        array ($privatePath.'system/lib-security.php',              'system directory')
        );

    foreach ($urls as $tocheck) {
        $display .= doTest ($url, $tocheck[0], $tocheck[1]);
    }

    // Note: We're not testing the 'sql' and 'language' directories.


    if (makeTempfile ($_CONF['backup_path'] . 'test.txt')) {
        $display .= doTest ($url, $privatePath.'backups/test.txt', 'backups directory');
        @unlink ($_CONF['backup_path'] . 'test.txt');
    } else {
        $display .= '<li>Failed to create a temporary file in your backups directory. Check your directory permissions!</li>';
    }


    if (makeTempfile ($_CONF['path_data'] . 'test.txt')) {
        $display .= doTest ($url, $privatePath.'data/test.txt', 'data directory');
        @unlink ($_CONF['path_data'] . 'test.txt');
    } else {
        $display .= '<li>Failed to create a temporary file in your data directory. Check your directory permissions!</li>';
    }

    $display .= checkDefaultPassword ();

    $display .= '</ol>';

} else {

    $resultInstallDirCheck = checkInstallDir ();
    $resultPasswordCheck = checkDefaultPassword ();

    if ($failed_tests == 0) {
        $display .= '<p>Everything seems to be in order.</p>';
    } else {
        $display .= '<ol>';
        $display .= $resultInstallDirCheck . LB . $resultPasswordCheck;
        $display .= '</ol>';
    }

}

if ($failed_tests > 0) {
    $display .= '<p class="warningsmall"><strong>Please fix the above issues before using your site!</strong></p>';

    DB_save ($_TABLES['vars'], 'name,value', "'security_check','0'");
} else {
    $display .= '<p>Please note that no site is ever 100% secure. This script can only test for obvious security issues.</p>';

    DB_save ($_TABLES['vars'], 'name,value', "'security_check','1'");
}

if (empty ($LANG_DIRECTION)) {
    $versioncheck = '<strong><a href="vercheck.php">' . $LANG01[107] . '</a></strong>';
} else {
    $versioncheck = '<strong dir="' . $LANG_DIRECTION . '"><a href="vercheck.php">' . $LANG01[107]
                  . '</a></strong>';
}

$display .= '<p>To stay informed about new glFusion releases and possible '
    . 'security issues, we suggest that you subscribe to the (low-traffic) '
    . COM_createLink('glfusion-announce', 'http://www.freelists.org/list/glfusion-announce')
    . ' mailing list and/or use the ' . $versioncheck
    . ' option in your Admin menu from time to time to check for available updates.</p>';

$display .= COM_endBlock ();
$display .= '</div>' . LB;
$display .= COM_siteFooter ();

echo $display;

?>