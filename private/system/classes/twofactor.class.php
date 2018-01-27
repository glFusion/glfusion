<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | TwoFactorAuthentication.php                                              |
// |                                                                          |
// | glFusion 2FA                                                             |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2017 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

class TwoFactor
{
    // Number of digits of two factor auth code
    const NUM_DIGITS = 6;

    // Number of bits of a secret associated with a user
    const NUM_BITS_OF_SECRET = 160;

    // Image dimensions for QR code
    const QR_CODE_SIZE = 200;

    // Number of digits of each backup code
    const NUM_DIGITS_OF_BACKUP_CODE = 12;

    // Number of backup codes in database
    const NUM_BACKUP_CODES = 5;

    /**
     * User ID
     * @var int
     */
    private $uid = 0;

    /**
     * @var TwoFactorAuth Object
     */
    private $tfa;

    /**
     * @var int
     */
    private $isAuthenticated = 0;

    /**
     * TwoFactorAuthentication constructor.
     *
     * @param  int $uid User ID
     */
    public function __construct($uid)
    {
        $this->uid = (int) $uid;
    }

	/**
	 * Returns a reference to single TwoFactor object, only creating it if it doesn't already exist.
	 *
	 * @static
     * @param  int $uid User ID
	 * @return	object	The TwoFactor object.
	 */
    public static function &getInstance($uid)
    {
        static $instance;

        if (!$instance) {
            $instance = new TwoFactor($uid);
        }

        return $instance;
    }

    public function isAuthenticated()
    {
        return $this->isAuthenticated;
    }

    /**
     * Return the single instance object of the twofactorauthenication class
     *
     * @return TwoFactorAuth
     */
    private function getTFAObject()
    {
        global $_CONF;
        static $mp;

        if (empty($this->tfa)) {
            $mp = new RobThree\Auth\Providers\Qr\glFusionQRProvider();
            $this->tfa = new RobThree\Auth\TwoFactorAuth($_CONF['site_name'], self::NUM_DIGITS,30,'sha1', $mp);
        }

        return $this->tfa;
    }


    /**
     * Return the secret code associated with the current user
     *
     * @return string
     */
    public function getUserSecret()
    {
        global $_TABLES;
        static $secret = null;

        if ($secret === null) {
            $encryptedsecret = DB_getItem($_TABLES['users'], 'tfa_secret', "uid = ".(int) $this->uid);
            $secret = COM_decrypt($encryptedsecret);
        }
        return $secret;
    }

    /**
     * Create and return a secret - stores in user's record
     *
     * @return string
     */
    public function createSecret()
    {
        global $_TABLES;

        try {
            $secret = $this->getTFAObject()->createSecret(self::NUM_BITS_OF_SECRET);
        } catch (Throwable $ex) {
            COM_errorLog(__METHOD__ . ': ' . $ex->getMessage());
            $secret = null;
        }
        if ( $secret != null ) {
            $encryptedKey = COM_encrypt($secret);
            $sql = "UPDATE {$_TABLES['users']} SET tfa_secret = '".DB_escapeString($encryptedKey)."' WHERE (uid = ".(int) $this->uid.")";
            DB_query($sql,1);
            if ( DB_error() ) {
                $secret = null;
            }
        }
        return $secret;
    }

    /**
     * Return QR code as a data URI
     *
     * @param  string $secret
     * @param  string $username
     * @return string
     */
    public function getQRCodeImageAsDataURI($secret, $username)
    {
        global $_CONF;

        try {
            return $this->getTFAObject()->getQRCodeImageAsDataUri($_CONF['site_name'].'@'.$username, $secret, self::QR_CODE_SIZE);
        } catch (RobThree\Auth\TwoFactorAuthException $ex) {
            COM_errorLog(__METHOD__ . ': ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Return backup codes stored in database
     *
     * @return array of string
     */
    public function getBackupCodesFromDatabase()
    {
        global $_TABLES;

        $retval = array();
        $sql = "SELECT code FROM {$_TABLES['tfa_backup_codes']} WHERE (uid = ".(int)$this->uid.") AND (used = 0) ORDER BY code";
        $result = DB_query($sql);
        if (!DB_error()) {
            while (($A = DB_fetchArray($result, false)) !== false) {
                $retval[] = $A['code'];
            }
        }
        return $retval;
    }

    /**
     * Scratch all the backup codes in database
     */
    public function scratchBackupCodes()
    {
        global $_TABLES;

        $sql = "UPDATE {$_TABLES['tfa_backup_codes']} SET used = 1 WHERE (uid = {$this->uid})";
        DB_query($sql);
    }

    /**
     * Create backup codes (secrets) and save them into database as encrypted values
     *
     * @return array of string
     */
    public function createBackupCodes()
    {
        global $_TABLES;

        $this->scratchBackupCodes();
        $retval = array();
        $tfa = $this->getTFAObject();

        // TwoFactorAuth::createSecret uses 5 bits for each byte
        $bitsForBackupCode = self::NUM_DIGITS_OF_BACKUP_CODE * 5;

        for ($i = 0; $i < self::NUM_BACKUP_CODES; $i++) {
            // ensure we do not create a backup code that has already been used by any user
            do {
                $code = $tfa->createSecret($bitsForBackupCode);
                $exist = (DB_count($_TABLES['tfa_backup_codes'], 'code', DB_escapeString(COM_encrypt($code))) == 0);
            } while (!$exist);
            $encryptedCode = COM_encrypt($code);
            $sql = "INSERT INTO {$_TABLES['tfa_backup_codes']} (code, uid, used) VALUES ('".DB_escapeString($encryptedCode)."', ".(int) $this->uid.", 0)";

            DB_query($sql);
            $retval[] = $code;
        }

        return $retval;
    }

    /**
     * Validate 2FA code
     *
     * @param  string $code
     * @return bool
     */
    public function validateCode($code)
    {
        $code = preg_replace('/[^0-9A-Z]/', '', $code);

        switch (strlen($code)) {
            case self::NUM_DIGITS:
                $code   = preg_replace('/[^0-9]/', '', $code);
                $secret = $this->getUserSecret();
                $retval = empty($secret) ? false : $this->getTFAObject()->verifyCode($secret, $code);
                break;

            case self::NUM_DIGITS_OF_BACKUP_CODE:
                $retval = $this->validateBackupCode($code);
                break;

            default:
                $retval = false;
                break;
        }

        if ( $retval == true ) $this->isAuthenticated = true;

        return $retval;
    }

    /**
     * Validate user backup code
     *
     * @param $code
     * @return bool
     */
    public function validateBackupCode($code)
    {
        global $_TABLES;

        $code = preg_replace('/[^0-9A-Z]/', '', $code);

        $encryptedCode = COM_encrypt($code);

        $sql = "SELECT used FROM {$_TABLES['tfa_backup_codes']} WHERE (code = '".DB_escapeString($encryptedCode)."') AND (uid = ".(int) $this->uid.")";
        $result = DB_query($sql);

        if (DB_error() || (DB_numRows($result) == 0)) {
            return false;
        }

        $row = DB_fetchArray($result, false);
        if ($row['used'] == 1) {
            // backup code has been used or invalidated previously
            return false;
        }
        // Scratch previous codes
        DB_change($_TABLES['tfa_backup_codes'],'used',1,array('code','uid'),array(DB_escapeString($encryptedCode),(int) $this->uid));

        return true;
    }
}
