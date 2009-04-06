<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// | Set configuration options for Media Gallery Plugin.                       |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2005-2008 by the following authors:                         |
// |                                                                           |
// | Mark R. Evans              - mark@gllabs.org                              |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

require_once '../../../lib-common.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_versionCheck() {
    global $_MG_CONF, $LANG_MG01;

    $this_version_info = explode(".",$_MG_CONF['version']);
    $this_head_revision = (int) $this_version_info[0];
    $this_branch_revision = (int) $this_version_info[1];
    $this_minor_revision  = $this_version_info[2];

    $errno = 0;
    $errstr = $version_info = '';

    $version_info = MG_readRemoteURL("http://www.glfusion.org/updatecheck/16x.txt");
    if ( $version_info == false ) {
        $version_msg = $LANG_MG01['no_version_info'];
        return $version_msg;
    }
    $version_info = explode("\n", $version_info);
    $latest_head_revision   = (int) $version_info[0];
    $latest_branch_revision = (int) $version_info[1];
    $latest_minor_revision  = $version_info[2];
    $latest_version = (int) $version_info[0] . '.' . (int) $version_info[1] . '.' . $version_info[2];

    $version_msg = '<p style="color:green">' . $LANG_MG01['up_to_date'] . '</p>';

    $yVersion = $latest_head_revision . '.' . $latest_branch_revision . '.' . $latest_minor_revision;

    if ( $this_head_revision < $latest_head_revision) {
        $version_msg = sprintf("<p style=\"color:red\">" . $LANG_MG01['out_of_date'] . "</p>",$yVersion, $_MG_CONF['version']);
    }
    if ( ($this_head_revision == $latest_head_revision) && ($this_branch_revision < $latest_branch_revision) ) {
        $version_msg = sprintf("<p style=\"color:red\">" . $LANG_MG01['out_of_date'] . "</p>",$yVersion, $_MG_CONF['version']);
    }
    if ( ($this_head_revision == $latest_head_revision) && ($this_branch_revision == $latest_branch_revision) && ($this_minor_revision < $latest_minor_revision)  ) {
        $version_msg = sprintf("<p style=\"color:red\">" . $LANG_MG01['out_of_date'] . "</p>",$yVersion, $_MG_CONF['version']);

    }
    $retval = $version_msg;
    return $retval;
}

function MG_adminBox($mode) {
    global $_MG_CONF, $LANG_MG08;

    $retval = '';

    if ( $mode == 'install' ) {
        $retval .= '<h3>' . $LANG_MG08['success'] . '</h3><br' . XHTML . '>' . $LANG_MG08['review'] . '<br' . XHTML . '><br' . XHTML . '>';
    }

    $retval .= $LANG_MG08['support'] . '<br' . XHTML . '><br' . XHTML . '>';

    return $retval;
}

function mg_readRemoteURL( $url ) {

	$fopen_url = ini_get('allow_url_fopen');

	// make sure curl is installed
	if (function_exists('curl_init')) {
	   // initialize a new curl resource
	   $ch = curl_init();

	   // set the url to fetch
	   curl_setopt($ch, CURLOPT_URL, $url);

	   // don't give me the headers just the content
	   curl_setopt($ch, CURLOPT_HEADER, 0);

	   // return the value instead of printing the response to browser
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	   // use a user agent to mimic a browser
	   curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');

	   $content = @curl_exec($ch);

	   // remember to always close the session and free all resources

	   curl_close($ch);

	   return $content;

	} else if ( $fopen_url == 1 ) {
		$content = @file_get_contents($url);
		if ($content !== false) {
	   		// do something with the content
	   		return $content;
		} else {
			return false;
	   		// an error happened
		}
	} else {
	   // get the host name and url path
	   $parsedUrl = parse_url($url);
	   $host = $parsedUrl['host'];
	   if (isset($parsedUrl['path'])) {
	      $path = $parsedUrl['path'];
	   } else {
	      // the url is pointing to the host like http://www.mysite.com
	      $path = '/';
	   }

	   if (isset($parsedUrl['query'])) {
	      $path .= '?' . $parsedUrl['query'];
	   }

	   if (isset($parsedUrl['port'])) {
	      $port = $parsedUrl['port'];
	   } else {
	      // most sites use port 80
	      $port = '80';
	   }

	   $timeout = 5;
	   $response = '';

	   // connect to the remote server
	   $fp = @fsockopen($host, '80', $errno, $errstr, $timeout );

	   if( !$fp ) {
	      return false;
	   } else {
	      // send the necessary headers to get the file
	      fputs($fp, "GET $path HTTP/1.0\r\n" .
	                 "Host: $host\r\n" .
	                 "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.3) Gecko/20060426 Firefox/1.5.0.3\r\n" .
	                 "Accept: */*\r\n" .
	                 "Accept-Language: en-us,en;q=0.5\r\n" .
	                 "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n" .
	                 "Keep-Alive: 300\r\n" .
	                 "Connection: keep-alive\r\n" .
	                 "Referer: " . $_MG_CONF['site_url'] . "\r\n\r\n");

	      // retrieve the response from the remote server
	      while ( $line = fread( $fp, 4096 ) ) {
	         $response .= $line;
	      }

	      fclose( $fp );

	      // strip the headers
	      $pos      = strpos($response, "\r\n\r\n");
	      $response = substr($response, $pos + 4);
	   }

	   // return the file content
	   return $response;
	}
}

// main menu for media gallery administration

$display = '';

if ( isset($_GET['mode']) ) {
    $mode = COM_applyFilter($_GET['mode']);
} else {
    $mode = '';
}

if (isset($_GET['msg']) ) {
    $msg = COM_applyFilter($_GET['msg'],true);
} else {
    $msg = 0;
}

$display = COM_siteHeader();


if ( $msg > 0 ) {
    $statusMsg = $LANG_MG09[$msg];
} else {
    $statusMsg = '';
}

USES_lib_admin();

$T = new Template($_MG_CONF['template_path']);
$T->set_file (array ('admin' => 'administration.thtml'));

$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
//    'admin_body'        => MG_adminBox($mode),
    'status_msg'        => $statusMsg,
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['pi_version'],
    'admin_body'             => plugin_showstats_mediagallery(0),
    'xhtml'             => XHTML,
));
    if ( $_MG_CONF['disable_version_check'] == 0 ) {
        $T->set_var('version_msg','<h1>' . $LANG_MG01['version_info'] . '</h1>' . MG_versionCheck() . '<hr' . XHTML . '>');
    }


$T->parse('output', 'admin');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;

?>