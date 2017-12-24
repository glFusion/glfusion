<?php
/**
* glFusion CMS
*
* Two Factor Authentication Controller
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2016-2017 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once 'lib-common.php';

if ( COM_isAnonUser() ) die('invalid request');
if ( !isset($_CONF['enable_twofactor']) || $_CONF['enable_twofactor'] == 0 ) die('invalid request');

$rc = 0;
$errorCode = 0;
$errors    = 0;
$retval = array();

$page = '';
$action = '';
if (isset($_POST['action'])) {
    $action = COM_applyFilter($_POST['action']);
}
if ( $action == '' ) {
    if ( isset($_GET['action'])) {
        $action = COM_applyFilter($_GET['action']);
    }
}

switch ($action) {
    case 'enroll' :
        $ajaxHandler = new \ajaxHandler();
        $page = tfaEnroll();
        echo $page;exit;
        $ajaxHandler->setResponse( 'panel', $page );
        break;

    case 'verify' :
        $ajaxHandler = new \ajaxHandler();
        $rc = tfaVerify();
        if ( $rc !== true ) {
            $rc = 1;
            $ajaxHandler->setMessage($LANG_TFA['error_verify_failed']);
        } else {
            $page = tfaConfirmPage();
            $rc = 0;
            $ajaxHandler->setResponse( 'panel', $page );
        }
        break;

    case 'regenerate' :
        $ajaxHandler = new \ajaxHandler();
        $backupCodes = tfaRegenerate();
        $ajaxHandler->setResponse( 'list', $backupCodes );

        $rc = 0;
        break;

    case 'disable' :
        $ajaxHandler = new \ajaxHandler();
        $rc = tfa_disable($ajaxHandler);
        break;

    case 'download_tfa_codes' :
        tfa_downloadBackupCode(); // does not return
        break;

    default :
        die();
        break;
}
$ajaxHandler->setErrorCode( $rc );
$ajaxHandler->sendResponse();

/**
 * returns array of new backup codes
 *
 * @return array
 */
function tfaRegenerate()
{
    global $_USER;

    $tfa = \TwoFactor::getInstance($_USER['uid']);
    $backupCodes = $tfa->createBackupCodes();
    return $backupCodes;

}

/**
 * Downloads current set of active backup codes
 *
 * @return none
 */
function tfaDownload()
{
    global $_CONF, $_TABLES, $_USER;

    $retval = array();

    $sql = "SELECT code FROM {$_TABLES['tfa_backup_codes']} "
        . "WHERE (uid = {$this->uid}) AND (used = 0) "
        . "ORDER BY code";
    $result = DB_query($sql);

    if (!DB_error()) {
        while (($A = DB_fetchArray($result, false)) !== false) {
            $retval[] = $A['code'];
        }
    }
}

/**
 * builds the TFA enroll panel
 *
 * @return HTML - tfa enroll panel
 */
function tfaEnroll()
{
    global $_CONF, $_USER, $_TABLES, $LANG_TFA;

    $tfa = \TwoFactor::getInstance($_USER['uid']);
    $secret = $tfa->createSecret();
    if ( $secret == null ) {
        tfaError();
    }
    $qrcode = $tfa->getQRCodeImageAsDataURI($secret, $_USER['username']);
    if ( $qrcode == null ) {
        COM_errorLog("ERROR: Two Factor Authentication could not generate the QR code");
        tfaError();
    }

    $T = new Template ($_CONF['path_layout'] . 'preferences');
    $T->set_file('page','tfa-enroll.thtml');

    $secToken = SEC_createToken();
    // ajax hack
    $urlFor = $_CONF['site_url'].'/usersettings.php?mode=edit';
    $sql = "UPDATE {$_TABLES['tokens']} SET urlfor='".DB_escapeString($urlFor)."' WHERE token='".DB_escapeString($secToken)."'";
    DB_query($sql,1);

    $T->set_var(array(
        'tfa-secret' => $secret,
        'tfa-qrcode' => $qrcode,
        'lang_enroll_title' => $LANG_TFA['enroll_tfa'],
        'lang_scan_qrcode' => $LANG_TFA['scan_qrcode'],
        'lang_enter_secret' => $LANG_TFA['enter_secret'],
        'lang_enroll_enter_code' => $LANG_TFA['enroll_enter_code'],
        'lang_auth_code' => $LANG_TFA['auth_code'],
        'lang_verify' => $LANG_TFA['verify'],
        'token_name'    => "_sectoken",
        'token_value'   => $secToken,
    ));

    $retval = $T->finish ($T->parse ('output', 'page'));

    return $retval;
}

/**
 * Verifies entered 2fa code if successful enabled tfa on user account
 *
 * @return boolean
 */
function tfaVerify()
{
    global $_CONF, $_USER, $_TABLES;

    $code = '';

    if ( isset( $_POST['tfacode'] ) ) {
        $code = $_POST['tfacode'];
    }
    if ( _sec_checkToken(true) ) {
        $tfa = TwoFactor::getInstance($_USER['uid']);
        $rc = $tfa->validateCode($code);
        if ( $rc == true ) {
            DB_query("UPDATE {$_TABLES['users']} SET tfa_enabled=1 WHERE uid=".(int) $_USER['uid']);
        }
    } else {
        COM_errorLog("Security token check failed");
        $rc = false;
    }
    return $rc;
}

/**
 * builds the TFA confirmation panel
 *
 * @return HTML - tfa confirmation panel
 */
function tfaConfirmPage()
{
    global $_CONF, $_USER, $_TABLES, $LANG_TFA;

    $retval = '';
    $T = new Template ($_CONF['path_layout'] . 'preferences');
    $T->set_file('page','tfa-confirm.thtml');

    $secToken = SEC_createToken();
    // ajax hack
    $urlFor = $_CONF['site_url'].'/usersettings.php?mode=edit';
    $sql = "UPDATE {$_TABLES['tokens']} SET urlfor='".DB_escapeString($urlFor)."' WHERE token='".DB_escapeString($secToken)."'";
    DB_query($sql,1);

    $T->set_var(array(
        'lang_enroll_success' => $LANG_TFA['enroll_success'],
        'lang_two_factor_enabled' => $LANG_TFA['two_factor_enabled'],
        'lang_download_backup'  => $LANG_TFA['download_backup'],
        'token_name'    => "_sectoken",
        'token_value'   => $secToken,
    ));

    $tfa = TwoFactor::getInstance($_USER['uid']);

    $T->set_block('page', 'backupcodes', 'buc');
    $buCode = $tfa->createBackupCodes();
    foreach ($buCode AS $code ) {
        $T->set_var('backup_code',trim($code));
        $T->parse('buc', 'backupcodes',true);
    }
    $retval = $T->finish ($T->parse ('output', 'page'));
    return $retval;
}

/**
 * Disables 2FA for the user account
 *
 * @return HTML - tfa enroll panel
 */
function tfa_disable(&$ajaxHandler)
{
    global $_CONF, $_TABLES, $_USER, $LANG_TFA;

    $rc = -1;

    if (isset($_CONF['enable_twofactor']) && $_CONF['enable_twofactor'] && !COM_isAnonUser() && isset($_USER['uid']) && ($_USER['uid'] > 1)) {
        if ( _sec_checkToken(true) ) {
            $ajaxHandler = new \ajaxHandler();
            DB_query("UPDATE {$_TABLES['users']} SET tfa_enabled=0, tfa_secret=NULL WHERE uid={$_USER['uid']}");
            DB_query("DELETE FROM {$_TABLES['tfa_backup_codes']} WHERE uid={$_USER['uid']}");
            $T = new Template ($_CONF['path_layout'] . 'preferences');
            $T->set_file('page','tfa-notenrolled.thtml');
            $T->set_var(array(
                'lang_two_factor'   => $LANG_TFA['two_factor'],
                'lang_not_enrolled' => $LANG_TFA['not_enrolled'],
                'lang_enroll_button'=> $LANG_TFA['enroll_button'],
            ));
            $retval = $T->finish ($T->parse ('output', 'page'));
            $ajaxHandler->setResponse( 'panel', $retval );
            $rc = 0;
        }
    }
    return $rc;
}

function tfa_downloadBackupCode()
{
    global $_CONF, $_TABLES, $_USER, $LANG_TFA;

    if (isset($_CONF['enable_twofactor']) && $_CONF['enable_twofactor'] && !COM_isAnonUser() && isset($_USER['uid']) && ($_USER['uid'] > 1)) {
        if ( _sec_checkToken(true) ) {
            $buCodes = array();
            $sql = "SELECT code FROM {$_TABLES['tfa_backup_codes']} WHERE (uid = ".(int)$_USER['uid'] ." AND used = 0) ORDER BY code";
            $result = DB_query($sql,1);
            if ( DB_error() ) die();
            while (($A = DB_fetchArray($result)) !== false) {
                $buCodes[] = COM_decrypt($A['code']);
            }
            $backupCodes = implode("\r\n", $buCodes);
            header('Content-Type: text/plain');
            header('Content-Length: ' . strlen($backupCodes));
            header('Content-Disposition: attachment; filename="backup_codes.txt"');
            echo $backupCodes;
        }
    }
    exit;
}

/**
 * General error response
 *
 * @return none
 */
function tfaError()
{
    global $LANG_TFA;

    print $LANG_TFA['general_error'];
    exit;
}

/*

   try {
        $tfa->ensureCorrectTime();
        echo 'Your hosts time seems to be correct / within margin';
    } catch (RobThree\Auth\TwoFactorAuthException $ex) {
        echo '<b>Warning:</b> Your hosts time seems to be off: ' . $ex->getMessage();
    }

*/

?>