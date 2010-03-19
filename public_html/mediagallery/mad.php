<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin - glFusion CMS                                      |
// +--------------------------------------------------------------------------+
// | mad.php                                                                  |
// |                                                                          |
// | General purpose media attribute display                                  |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2009 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on original work by:                                               |
// | James Heinrich             -   info@getid3.org                           |
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

/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
//                                                             //
// /demo/demo.browse.php - part of getID3()                     //
// Sample script for browsing/scanning files and displaying    //
// information returned by getID3()                            //
// See readme.txt for more details                             //
//                                                            ///
/////////////////////////////////////////////////////////////////

require_once '../lib-common.php';

if (!in_array('mediagallery', $_PLUGINS)) {
    COM_404();
    exit;
}

if (!SEC_hasRights('mediagallery.admin')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: $REMOTE_ADDR",1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

// only take media ids, we'll build the path

$mid = COM_applyFilter($_REQUEST['mid']);
$sql = "SELECT * FROM {$_TABLES['mg_media']} WHERE media_id='" . DB_escapeString($mid) . "'";
$result = DB_query($sql);
$numRows = DB_numRows($result);
if ($numRows > 0) {
	$row = DB_fetchArray($result);
} else {
	echo 'Invalid ID';
	exit;
}
$filename = $_MG_CONF['path_mediaobjects'] . 'orig/' . $row['media_filename'][0] . '/' . $row['media_filename'] . '.' . $row['media_mime_ext'];



/////////////////////////////////////////////////////////////////
// set predefined variables as if magic_quotes_gpc was off,
// whether the server's got it or not:
UnifyMagicQuotes(false);
/////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////


if (!function_exists('getmicrotime')) {
	function getmicrotime() {
		list($usec, $sec) = explode(' ', microtime());
		return ((float) $usec + (float) $sec);
	}
}

///////////////////////////////////////////////////////////////////////////////


$writescriptfilename = 'demo.write.php';

require_once $_CONF['path'] . '/lib/getid3/getid3.php';
// require_once('../getid3/getid3.php');

// Needed for windows only
define('GETID3_HELPERAPPSDIR', 'C:/helperapps/');

// Initialize getID3 engine
$getID3 = new getID3;
$getID3->setOption(array('encoding' => 'UTF-8'));

$getID3checkColor_Head           = 'CCCCDD';
$getID3checkColor_DirectoryLight = 'FFCCCC';
$getID3checkColor_DirectoryDark  = 'EEBBBB';
$getID3checkColor_FileLight      = 'EEEEEE';
$getID3checkColor_FileDark       = 'DDDDDD';
$getID3checkColor_UnknownLight   = 'CCCCFF';
$getID3checkColor_UnknownDark    = 'BBBBDD';


///////////////////////////////////////////////////////////////////////////////


header('Content-Type: text/html; charset=UTF-8');
ob_start();
echo '<html><head>';
echo '<title>getID3()</title>';
echo '<style>BODY,TD,TH { font-family: sans-serif; font-size: 9pt; }</style>';
echo '</head><body>';

if (isset($filename)) {

	if (!file_exists($filename)) {
		die(getid3_lib::iconv_fallback('ISO-8859-1', 'UTF-8', $filename.' does not exist'));
	}
	$starttime = getmicrotime();
	$AutoGetHashes = (bool) (filesize($filename) < 52428800); // auto-get md5_data, md5_file, sha1_data, sha1_file if filesize < 50MB

	$getID3->setOption(array(
		'option_md5_data'  => $AutoGetHashes,
		'option_sha1_data' => $AutoGetHashes,
	));
	$ThisFileInfo = $getID3->analyze($filename);
	if ($AutoGetHashes) {
		$ThisFileInfo['md5_file']  = getid3_lib::md5_file($filename);
		$ThisFileInfo['sha1_file'] = getid3_lib::sha1_file($filename);
	}


	getid3_lib::CopyTagsToComments($ThisFileInfo);

	$listdirectory = dirname(getid3_lib::SafeStripSlashes($filename));
	$listdirectory = realpath($listdirectory); // get rid of /../../ references

	if (GETID3_OS_ISWINDOWS) {
		// this mostly just gives a consistant look to Windows and *nix filesystems
		// (windows uses \ as directory seperator, *nix uses /)
		$listdirectory = str_replace('\\', '/', $listdirectory.'/');
	}

	if (strstr($filename, 'http://') || strstr($filename, 'ftp://')) {
		echo '<i>Cannot browse remote filesystems</i><br>';
	} else {
		echo "<b>File: " . $row['media_original_filename'] . "</b>";
	}

	echo table_var_dump($ThisFileInfo);
	$endtime = getmicrotime();
	echo 'File parsed in '.number_format($endtime - $starttime, 3).' seconds.<br>';

}

echo PoweredBygetID3();
echo 'Running on PHP v'.phpversion();
echo '</body></html>';
ob_end_flush();


/////////////////////////////////////////////////////////////////


function RemoveAccents($string) {
	// Revised version by markstewardרotmail*com
	// Again revised by James Heinrich (19-June-2006)
	return strtr(
		strtr(
			$string,
			"\x8A\x8E\x9A\x9E\x9F\xC0\xC1\xC2\xC3\xC4\xC5\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD1\xD2\xD3\xD4\xD5\xD6\xD8\xD9\xDA\xDB\xDC\xDD\xE0\xE1\xE2\xE3\xE4\xE5\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF1\xF2\xF3\xF4\xF5\xF6\xF8\xF9\xFA\xFB\xFC\xFD\xFF",
			'SZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy'
		),
		array(
			"\xDE" => 'TH',
			"\xFE" => 'th',
			"\xD0" => 'DH',
			"\xF0" => 'dh',
			"\xDF" => 'ss',
			"\x8C" => 'OE',
			"\x9C" => 'oe',
			"\xC6" => 'AE',
			"\xE6" => 'ae',
			"\xB5" => 'u'
		)
	);
}


function BitrateColor($bitrate, $BitrateMaxScale=768) {
	// $BitrateMaxScale is bitrate of maximum-quality color (bright green)
	// below this is gradient, above is solid green

	$bitrate *= (256 / $BitrateMaxScale); // scale from 1-[768]kbps to 1-256
	$bitrate = round(min(max($bitrate, 1), 256));
	$bitrate--;    // scale from 1-256kbps to 0-255kbps

	$Rcomponent = max(255 - ($bitrate * 2), 0);
	$Gcomponent = max(($bitrate * 2) - 255, 0);
	if ($bitrate > 127) {
		$Bcomponent = max((255 - $bitrate) * 2, 0);
	} else {
		$Bcomponent = max($bitrate * 2, 0);
	}
	return str_pad(dechex($Rcomponent), 2, '0', STR_PAD_LEFT).str_pad(dechex($Gcomponent), 2, '0', STR_PAD_LEFT).str_pad(dechex($Bcomponent), 2, '0', STR_PAD_LEFT);
}

function BitrateText($bitrate, $decimals=0, $vbr=false) {
	return '<SPAN STYLE="color: #'.BitrateColor($bitrate).($vbr ? '; font-weight: bold;' : '').'">'.number_format($bitrate, $decimals).' kbps</SPAN>';
}

function FixTextFields($text) {
	$text = getid3_lib::SafeStripSlashes($text);
	$text = htmlentities($text, ENT_QUOTES);
	return $text;
}


function string_var_dump($variable) {
	ob_start();
	var_dump($variable);
	$dumpedvariable = ob_get_contents();
	ob_end_clean();
	return $dumpedvariable;
}


function table_var_dump($variable) {
	$returnstring = '';
	switch (gettype($variable)) {
		case 'array':
			$returnstring .= '<table border="1" cellspacing="0" cellpadding="2">';
			foreach ($variable as $key => $value) {
				$returnstring .= '<tr><td valign="top"><b>'.str_replace("\x00", ' ', $key).'</b></td>';
				$returnstring .= '<td valign="top">'.gettype($value);
				if (is_array($value)) {
					$returnstring .= '&nbsp;('.count($value).')';
				} elseif (is_string($value)) {
					$returnstring .= '&nbsp;('.strlen($value).')';
				}
				if (($key == 'data') && isset($variable['image_mime']) && isset($variable['dataoffset'])) {
					$imagechunkcheck = getid3_lib::GetDataImageSize($value);
					$DumpedImageSRC = (!empty($filename) ? $filename : '.getid3').'.'.$variable['dataoffset'].'.'.getid3_lib::ImageTypesLookup($imagechunkcheck[2]);
					if ($tempimagefile = @fopen($DumpedImageSRC, 'wb')) {
						fwrite($tempimagefile, $value);
						fclose($tempimagefile);
					}
					$returnstring .= '</td><td><img src="'.$_SERVER['PHP_SELF'].'?showfile='.urlencode($DumpedImageSRC).'&md5='.md5_file($DumpedImageSRC).'" width="'.$imagechunkcheck[0].'" height="'.$imagechunkcheck[1].'"></td></tr>';
				} else {
					$returnstring .= '</td><td>'.table_var_dump($value).'</td></tr>';
				}
			}
			$returnstring .= '</table>';
			break;

		case 'boolean':
			$returnstring .= ($variable ? 'TRUE' : 'FALSE');
			break;

		case 'integer':
		case 'double':
		case 'float':
			$returnstring .= $variable;
			break;

		case 'object':
		case 'null':
			$returnstring .= string_var_dump($variable);
			break;

		case 'string':
			$variable = str_replace("\x00", ' ', $variable);
			$varlen = strlen($variable);
			for ($i = 0; $i < $varlen; $i++) {
				if (ereg('['."\x0A\x0D".' -;0-9A-Za-z]', $variable{$i})) {
					$returnstring .= $variable{$i};
				} else {
					$returnstring .= '&#'.str_pad(ord($variable{$i}), 3, '0', STR_PAD_LEFT).';';
				}
			}
			$returnstring = nl2br($returnstring);
			break;

		default:
			$imagechunkcheck = getid3_lib::GetDataImageSize($variable);
			if (($imagechunkcheck[2] >= 1) && ($imagechunkcheck[2] <= 3)) {
				$returnstring .= '<table border="1" cellspacing="0" cellpadding="2">';
				$returnstring .= '<tr><td><b>type</b></td><td>'.getid3_lib::ImageTypesLookup($imagechunkcheck[2]).'</td></tr>';
				$returnstring .= '<tr><td><b>width</b></td><td>'.number_format($imagechunkcheck[0]).' px</td></tr>';
				$returnstring .= '<tr><td><b>height</b></td><td>'.number_format($imagechunkcheck[1]).' px</td></tr>';
				$returnstring .= '<tr><td><b>size</b></td><td>'.number_format(strlen($variable)).' bytes</td></tr></table>';
			} else {
				$returnstring .= nl2br(htmlspecialchars(str_replace("\x00", ' ', $variable)));
			}
			break;
	}
	return $returnstring;
}


function NiceDisplayFiletypeFormat(&$fileinfo) {

	if (empty($fileinfo['fileformat'])) {
		return '-';
	}

	$output  = $fileinfo['fileformat'];
	if (empty($fileinfo['video']['dataformat']) && empty($fileinfo['audio']['dataformat'])) {
		return $output;  // 'gif'
	}
	if (empty($fileinfo['video']['dataformat']) && !empty($fileinfo['audio']['dataformat'])) {
		if ($fileinfo['fileformat'] == $fileinfo['audio']['dataformat']) {
			return $output; // 'mp3'
		}
		$output .= '.'.$fileinfo['audio']['dataformat']; // 'ogg.flac'
		return $output;
	}
	if (!empty($fileinfo['video']['dataformat']) && empty($fileinfo['audio']['dataformat'])) {
		if ($fileinfo['fileformat'] == $fileinfo['video']['dataformat']) {
			return $output; // 'mpeg'
		}
		$output .= '.'.$fileinfo['video']['dataformat']; // 'riff.avi'
		return $output;
	}
	if ($fileinfo['video']['dataformat'] == $fileinfo['audio']['dataformat']) {
		if ($fileinfo['fileformat'] == $fileinfo['video']['dataformat']) {
			return $output; // 'real'
		}
		$output .= '.'.$fileinfo['video']['dataformat']; // any examples?
		return $output;
	}
	$output .= '.'.$fileinfo['video']['dataformat'];
	$output .= '.'.$fileinfo['audio']['dataformat']; // asf.wmv.wma
	return $output;

}

function MoreNaturalSort($ar1, $ar2) {
	if ($ar1 === $ar2) {
		return 0;
	}
	$len1     = strlen($ar1);
	$len2     = strlen($ar2);
	$shortest = min($len1, $len2);
	if (substr($ar1, 0, $shortest) === substr($ar2, 0, $shortest)) {
		// the shorter argument is the beginning of the longer one, like "str" and "string"
		if ($len1 < $len2) {
			return -1;
		} elseif ($len1 > $len2) {
			return 1;
		}
		return 0;
	}
	$ar1 = RemoveAccents(strtolower(trim($ar1)));
	$ar2 = RemoveAccents(strtolower(trim($ar2)));
	$translatearray = array('\''=>'', '"'=>'', '_'=>' ', '('=>'', ')'=>'', '-'=>' ', '  '=>' ', '.'=>'', ','=>'');
	foreach ($translatearray as $key => $val) {
		$ar1 = str_replace($key, $val, $ar1);
		$ar2 = str_replace($key, $val, $ar2);
	}

	if ($ar1 < $ar2) {
		return -1;
	} elseif ($ar1 > $ar2) {
		return 1;
	}
	return 0;
}

function PoweredBygetID3($string='<br><HR NOSHADE><DIV STYLE="font-size: 8pt; font-face: sans-serif;">Powered by <a href="http://getid3.sourceforge.net" TARGET="_blank"><b>getID3() v<!--GETID3VER--></b><br>http://getid3.sourceforge.net</a></DIV>') {
	return str_replace('<!--GETID3VER-->', GETID3_VERSION, $string);
}


/////////////////////////////////////////////////////////////////
// Unify the contents of GPC,
// whether magic_quotes_gpc is on or off

function AddStripSlashesArray($input, $DB_escapeString=false) {
	if (is_array($input)) {

		$output = $input;
		foreach ($input as $key => $value) {
			$output[$key] = AddStripSlashesArray($input[$key]);
		}
		return $output;

	} elseif ($DB_escapeString) {
		return DB_escapeString($input);
	}
	return stripslashes($input);
}

function UnifyMagicQuotes($turnon=false) {
	global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS;

	if (get_magic_quotes_gpc() && !$turnon) {

		// magic_quotes_gpc is on and we want it off!
		$_GET    = AddStripSlashesArray($_GET,    true);
		$_POST   = AddStripSlashesArray($_POST,   true);
		$_COOKIE = AddStripSlashesArray($_COOKIE, true);

		unset($_REQUEST);
		$_REQUEST = array_merge_recursive($_GET, $_POST, $_COOKIE);

	} elseif (!get_magic_quotes_gpc() && $turnon) {

		// magic_quotes_gpc is off and we want it on (why??)
		$_GET    = AddStripSlashesArray($_GET,    true);
		$_POST   = AddStripSlashesArray($_POST,   true);
		$_COOKIE = AddStripSlashesArray($_COOKIE, true);

		unset($_REQUEST);
		$_REQUEST = array_merge_recursive($_GET, $_POST, $_COOKIE);

	}
	$HTTP_GET_VARS    = $_GET;
	$HTTP_POST_VARS   = $_POST;
	$HTTP_COOKIE_VARS = $_COOKIE;

	return true;
}
/////////////////////////////////////////////////////////////////


?>
</BODY>
</HTML>