<?php
/**
* glFusion CMS
*
* Display user's PGP key
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once 'lib-common.php';

use \glFusion\Database\Database;

$db = Database::getInstance();

$uid = (int) filter_input(INPUT_GET,'uid',FILTER_SANITIZE_NUMBER_INT);
if (is_numeric ($uid) && ($uid > 1)) {

    $A = $db->conn->fetchAssoc(
                "SELECT u.uid,username,fullname,regdate,lastlogin,homepage,about,location,pgpkey,photo,email,status,showonline
                FROM `{$_TABLES['userinfo']}` AS ui,`{$_TABLES['userprefs']}` AS up,
                     `{$_TABLES['users']}` AS u
                WHERE ui.uid = u.uid AND ui.uid = up.uid AND u.uid = ?",
           array($uid),
           array(Database::INTEGER)
    );
    if ($A === false || $A === null) {
        die('Invalid request');
    }
    if ($A['status'] == USER_ACCOUNT_DISABLED && !SEC_hasRights ('user.edit')) {
        die("Invalid Request");
    }

    if (empty($A['pgpkey'])) {
        $A['pgpkey'] = 'No PGP Key Defined';
    }

    $display = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>' . $LANG04[8] . '</title>
</head>
<body>
	<pre>'.$A['pgpkey'].'</pre>
</body>
</html>';
    if( empty( $LANG_CHARSET )) {
        $charset = $_CONF['default_charset'];
        if( empty( $charset )) {
            $charset = 'iso-8859-1';
        }
    } else {
        $charset = $LANG_CHARSET;
    }
    header ('Content-Type: text/html; charset=' . $charset);
    echo $display;
    exit;
}
?>