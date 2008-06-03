<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                              |
// |                                                                           |
// | Author:                                                                   |
// | Tim Strehle                - tim@digicol.de                               |
// | André Basse                - andre@digicol.de                             |
// |                                                                           |
// | Adapted for Media Gallery by                                              |
// | Mark R. Evans              - mark@gllabs.org                              |
// |                                                                           |
// | Based on + inspired by the Gallery (http://gallery.menalto.com/)          |
// | XP Publishing Wizard implementation, written by                           |
// |        Demian Johnston                                                    |
// |        Bharat Mediratta                                                   |
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
require_once('../../../lib-common.php');
require_once($_CONF['path_system']  . 'lib-user.php');
require_once($_MG_CONF['path_html'] . 'classAlbum.php');
require_once($_MG_CONF['path_html'] . 'lib-upload.php');

function MG_xpPubHeader( ) {
    // Send no-cache headers

    @header('Expires: Mon, 26 Jul 2002 05:00:00 GMT');              // Date in the past
    @header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    @header('Cache-Control: no-cache="set-cookie", private');       // HTTP/1.1
    @header('Pragma: no-cache');                                    // HTTP/1.0

    // Send character set header

    @header('Content-Type: text/html; charset=iso-8859-1');

    echo    '<html><head><title>' . $LANG_MG06['title'] .
            '</title><style type="text/css">
                body,a,p,span,td,th,input,select,textarea {
                    font-family:verdana,arial,helvetica,geneva,sans-serif,serif;
                    font-size:10px;
                }
            </style></head><body>';
}

// General configuration

$protocol = 'http';
if (isset($_SERVER[ 'HTTPS' ]))
  if ($_SERVER[ 'HTTPS' ] == 'on')
    $protocol .= 's';

$cfg = array(
    'wizardheadline'    => $_CONF['site_name'],
    'wizardbyline'      => $_CONF['site_slogan'],
    'finalurl'          => $protocol . '://' . $_SERVER[ 'HTTP_HOST' ] . '/',
    'registrykey'       => strtr($_SERVER[ 'HTTP_HOST' ], '.:', '__'),
    'wizardname'        => $_SERVER[ 'HTTP_HOST' ],
    'wizarddescription' => $LANG_MG06['title']
);

$allsteps = array( 'login', 'info', 'options', 'check', 'docheck', 'addit', 'create','upload', 'reg', 'regvista', 'process_login', 'logout' );

$step = 'login';

if (isset($_REQUEST[ 'step' ])) {
    if (in_array($_REQUEST[ 'step' ], $allsteps)) {
        $step = $_REQUEST[ 'step' ];
    }
}
COM_errorLog($step);

// Special registry file download mode:
// Call this script in your browser and set ?step=reg to download a .reg file for registering
// your server with the Windows XP Publishing Wizard

if ($step == 'reg') {
    header('Content-Type: application/octet-stream; name="xppubwiz.reg"');
    header('Content-disposition: attachment; filename="xppubwiz.reg"');

    echo
        'Windows Registry Editor Version 5.00' . "\n\n" .
        '[HKEY_CURRENT_USER\\Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\PublishingWizard\\PublishingWizard\\Providers\\' . $cfg[ 'registrykey' ] . ']' . "\n" .
        '"displayname"="' . $cfg[ 'wizardname' ] . '"' . "\n" .
        '"description"="' . $cfg[ 'wizarddescription' ] . '"' . "\n" .
        '"href"="' . $protocol . '://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'PHP_SELF' ] . '"' . "\n" .
        '"icon"="' . $protocol . '://' . $_SERVER[ 'HTTP_HOST' ] . dirname($_SERVER[ 'PHP_SELF' ]) . '/favicon.ico"';
    exit;
}

if ($step == 'regvista') {
    header('Content-Type: application/octet-stream; name="xppubwiz.reg"');
    header('Content-disposition: attachment; filename="xppubwiz.reg"');

    echo
        'Windows Registry Editor Version 5.00' . "\n\n" .
        '[HKEY_CURRENT_USER\\Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\PublishingWizard\\InternetPhotoPrinting\\Providers\\' . $cfg[ 'registrykey' ] . ']' . "\n" .
        '"displayname"="' . $cfg[ 'wizardname' ] . '"' . "\n" .
        '"description"="' . $cfg[ 'wizarddescription' ] . '"' . "\n" .
        '"href"="' . $protocol . '://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'PHP_SELF' ] . '"' . "\n" .
        '"icon"="' . $protocol . '://' . $_SERVER[ 'HTTP_HOST' ] . dirname($_SERVER[ 'PHP_SELF' ]) . '/favicon.ico"';
    exit;
}



// Set maximum execution time to unlimited to allow large file uploads
//  Will not work in safe_mode, but does no harm to try...

@set_time_limit(0);

// Variables for the XP wizard buttons

$WIZARD_BUTTONS = 'false,true,false';
$ONBACK_SCRIPT  = '';
$ONNEXT_SCRIPT  = '';

// Check page/step

if (empty ($_USER['username']) && $step != 'process_login')
    $step = login;
elseif ($step == 'login')
  $step = 'info';

if ($step == 'check') {
  if (! (isset($_REQUEST[ 'manifest' ]) && isset($_REQUEST[ 'dir' ]))) {
    $step = 'options';
  }
}

if ($step == 'check') {
  if (($_REQUEST[ 'manifest' ] == '') || ($_REQUEST[ 'dir' ] == '')) {
    $step = 'options';
  }
}

if ($step == 'check') {
  if (! isset($_REQUEST['dir'])) {
    $step = 'options';
  }
}

if ( $step == 'addit') {
    if ( !isset($_REQUEST['album_name']) || (strlen($_REQUEST['album_name']) < 1 )) {
        $step = 'options';
    }
}

if ($step == 'process_login' ) {
    if (isset($_REQUEST[ 'loginname' ]) && isset($_REQUEST[ 'passwd' ])) {
        $mypasswd = COM_getPassword($_REQUEST['loginname']);
        if ( md5($_REQUEST['passwd']) == $mypasswd ) {
            $userdata = SESS_getUserData($_REQUEST['loginname']);
            $_USER=$userdata;
            $sessid = SESS_newSession($_USER['uid'], $_SERVER['REMOTE_ADDR'], $_CONF['session_cookie_timeout'], $_CONF['cookie_ip']);

            SESS_setSessionCookie($sessid, $_CONF['session_cookie_timeout'], $_CONF['cookie_session'], $_CONF['cookie_path'], $_CONF['cookiedomain'], $_CONF['cookiesecure']);
            PLG_loginUser ($_USER['uid']);

            // Now that we handled session cookies, handle longterm cookie

            if (!isset($_COOKIE[$_CONF['cookie_name']]) || !isset($_COOKIE['password'])) {

                // Either their cookie expired or they are new
                $cooktime = COM_getUserCookieTimeout();
                if ($cooktime > 0) {
                    // They want their cookie to persist for some amount of time so set it now
                    setcookie ($_CONF['cookie_name'], $_USER['uid'],
                               time() + $cooktime, $_CONF['cookie_path'],
                               $_CONF['cookiedomain'], $_CONF['cookiesecure']);
                    setcookie ($_CONF['cookie_password'], md5 ($passwd),
                               time() + $cooktime, $_CONF['cookie_path'],
                               $_CONF['cookiedomain'], $_CONF['cookiesecure']);
                }
            } else {
//                $userid = $HTTP_COOKIE_VARS[$_CONF['cookie_name']];
                $userid = $_COOKIE[$_CONF['cookie_name']];

                if (empty ($userid) || ($userid == 'deleted')) {
                    unset ($userid);
                } else {
                    $userid = COM_applyFilter ($userid, true);
                    if ($userid > 1) {
                        // Create new session
                        $userdata = SESS_getUserDataFromId($userid);
                        $_USER = $userdata;
                    }
                }
            }

            // Now that we have users data see if their theme cookie is set.
            // If not set it
            setcookie ($_CONF['cookie_theme'], $_USER['theme'], time() + 31536000,
                       $_CONF['cookie_path'], $_CONF['cookiedomain'],
                       $_CONF['cookiesecure']);
            $step = 'info';
        }
    }
}

// Step 1: Display login form

if ($step == 'login') {
    MG_xpPubHeader();

    $T = new Template($_MG_CONF['template_path']);
    $T->set_file ('wizard','xplogin.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $T->set_var(array(
        'lang_username'         => $LANG04[2],
        'lang_password'         => $LANG20[5],
        's_form_action'         => $_SERVER[ 'PHP_SELF' ],
    ));

    $T->parse('output', 'wizard');
    $display .= $T->finish($T->get_var('output'));
    echo $display;

    $ONNEXT_SCRIPT  = 'login.submit();';
    $ONBACK_SCRIPT  = 'window.external.FinalBack();';
    $WIZARD_BUTTONS = 'true,true,false';
}

if ($step == 'info') {
    MG_xpPubHeader();
?>
    <form method="post" id="info" action="<?php echo $_SERVER[ 'PHP_SELF' ]; ?>"
>
    <center>
    <h3><?php echo $LANG_MG06['welcome'] . ' '; ?> <?php echo $_CONF['site_name'
]; ?></h3>
    <table border="0">
    <tr>
        <td>
        <?php echo $LANG_MG06['info_text']; ?>
        </td>
    </tr>
    </table>
    </center>
    <input type="hidden" name="step" value="options" />
    </form>
<?php

    $ONNEXT_SCRIPT  = 'info.submit();';
    $ONBACK_SCRIPT  = 'window.external.FinalBack();';
    $WIZARD_BUTTONS = 'true,true,false';
}


// Step 2: Display options form (directory choosing)

if ($step == "options") {

    $album_count = $MG_albums[0]->getAlbumCount(3);

    if ( SEC_hasRights('mediagallery.admin') || ($_MG_CONF['member_albums'] == 1 && $_MG_CONF['member_album_root'] == 0 )) {
        $album_selectbox .= '<option value="0">------</option>';
    }
    $MG_albums[0]->buildAlbumBox($album_id,3,-1,'upload');
    $album_select = $album_selectbox;
    if ( $album_count == 0 ) {
        $album_select = $LANG_MG06['no_albums'];
    }
/* ---
    $album_jumpbox = '';
    $level = 0;
    $MG_albums[0]->buildJumpBox($album_id,3);
    $album_select = $album_jumpbox;
    if ($album_count == 0 ) {
        $album_select = $LANG_MG06['no_albums'];
    }
--- */
    MG_xpPubHeader();
    $T = new Template($_MG_CONF['template_path']);
    $T->set_file ('wizard','xplist.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $T->set_var(array(
        'album_select'          => $album_select,
        'lang_select_album'     => $LANG_MG06['select_album'],
        'lang_create_album'     => $LANG_MG01['create_album'],
        's_form_action'         => $_SERVER[ 'PHP_SELF' ],
    ));

    $T->parse('output', 'wizard');
    $display .= $T->finish($T->get_var('output'));
    echo $display;

    $ONNEXT_SCRIPT  = "options.submit();";
    $WIZARD_BUTTONS = "false,true,false";
}
?>
<div id="content"/>

</div>

<?php

if ( $step == "docheck" ) {
    $album = $_POST['dir'];
    ?>

    <form method="post" id="options" action="<?php echo $_SERVER[ 'PHP_SELF' ]; ?>">
    <input type="hidden" name="dir" value="<?php echo $album; ?>">
    <input type="hidden" name="manifest" value="" />
    <input type="hidden" name="step" value="check" />

    <?php $SCRIPT_CMD="docheck();"; ?>

    <script>

    function docheck()
    { var xml = window.external.Property('TransferManifest');
      options.manifest.value = xml.xml;
      options.submit();
    }

    </script>
    </form>
<?php

   $ONNEXT_SCRIPT  = "docheck();";
   $ONBACK_SCRIPT  = "window.location.href = \"" . $_CONF['site_admin_url'] . "/plugins/mediagallery/" . "xppubwiz.php?step=options\";";
   $WIZARD_BUTTONS = "true,true,true";
}
?>

<?php
if ( $step == "addit" ) {

    $album_name = $_POST['album_name'];
    $album_desc = $_POST['album_desc'];
    $parent_aid = $_POST['parentaid'];

    require_once($_MG_CONF['path_html'] . 'maint/albumedit.php');
    MG_quickCreate($parent_aid,$album_name,$album_desc);

    ?>

    <form method="post" id="options" action="<?php echo $_SERVER[ 'PHP_SELF' ]; ?>">
    <input type="hidden" name="dir" value="<?php echo $A['album_id']; ?>">
    <input type="hidden" name="manifest" value="" />
    <input type="hidden" name="step" value="options" />

     <?php $SCRIPT_CMD="docheck();"; ?>

    <script>

    function docheck()
    { var xml = window.external.Property('TransferManifest');
      options.manifest.value = xml.xml;
      options.submit();
    }

    </script>
    </form>
    We need to have some text here to see what we are doing????
<?php

   $ONNEXT_SCRIPT  = "docheck();";
   $WIZARD_BUTTONS = "true,true,true";
}
?>


<?php
if ($step == "create") {

    $level = 0;
    $album_selectbox  = '<select name="parentaid">';
    if ( SEC_hasRights('mediagallery.admin') || ($_MG_CONF['member_albums'] == 1 && $_MG_CONF['member_album_root'] == 0 )) {
        $album_selectbox .= '<option value="0">------</option>';
        $valid_albums = 1;
    }
    $valid_albums += $MG_albums[0]->buildAlbumBox($album_id,3,-1,'create');
    $album_selectbox .= '</select>';
    MG_xpPubHeader();
    $T = new Template($_MG_CONF['template_path']);
    $T->set_file ('wizard','xpcreate.thtml');
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $T->set_var(array(
        'album_select'          => $album_selectbox,
        'lang_create_album'     => $LANG_MG01['create_album'],
        'lang_title'            => $LANG_MG01['album_title'],
        'lang_description'      => $LANG_MG01['description'],
        'lang_parent_album'     => $LANG_MG01['parent_album'],
        's_form_action'         => $_SERVER[ 'PHP_SELF' ],
    ));

    $T->parse('output', 'wizard');
    $display .= $T->finish($T->get_var('output'));
    echo $display;

    $ONNEXT_SCRIPT  = 'newalbum.submit();';
    $ONBACK_SCRIPT  = 'window.external.FinalBack();';
    $WIZARD_BUTTONS = 'true,true,false';
}

// Step 3: Check file list + selected options, prepare file upload

if ($step == "check")
  { /* Now we're embedding the HREFs to POST to into the transfer manifest.

    The original manifest sent by Windows XP looks like this:

    <transfermanifest>
        <filelist>
            <file id="0" source="C:\pic1.jpg" extension=".jpg" contenttype="image/jpeg" destination="pic1.jpg" size="530363">
                <metadata>
                    <imageproperty id="cx">1624</imageproperty>
                    <imageproperty id="cy">2544</imageproperty>
                </metadata>
            </file>
            <file id="1" source="C:\pic2.jpg" extension=".jpg" contenttype="image/jpeg" destination="pic2.jpg" size="587275">
                <metadata>
                    <imageproperty id="cx">1960</imageproperty>
                    <imageproperty id="cy">3008</imageproperty>
                </metadata>
            </file>
        </filelist>
    </transfermanifest>

    We will add a <post> child to each <file> section, and an <uploadinfo> child to the root element.
    */

    MG_xpPubHeader();

    // stripslashes if the evil "magic_quotes_gpc" are "on" (hint by Juan Valdez <juanvaldez123@hotmail.com>)

    if (ini_get('magic_quotes_gpc') == '1')
      $manifest = stripslashes($_REQUEST[ 'manifest' ]);
    else
      $manifest = $_REQUEST[ 'manifest' ];

    $parser = xml_parser_create();

    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);

    $xml_ok = xml_parse_into_struct($parser, $manifest, $tags, $index);

    $aid = $_REQUEST[ 'dir' ];

    $manifest = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>";

    foreach ($tags as $i => $tag) {
        if (($tag[ 'type' ] == 'open') || ($tag[ 'type' ] == 'complete')) {
            if ($tag[ 'tag' ] == 'file')
                $filedata = array(
                    'id'                => -1,
                    'source'            => '',
                    'extension'         => '',
                    'contenttype'       => '',
                    'destination'       => '',
                    'size'              => -1,
                    'imageproperty_cx'  => -1,
                    'imageproperty_cy'  => -1
                );
            $manifest .= '<' . $tag[ 'tag' ];

            if (isset($tag[ 'attributes' ]))
              foreach ($tag[ 'attributes' ] as $key => $value) {
                  if ($key == 'source' ) {
                      $value = htmlentities($value, ENT_QUOTES);
                  }
                  $manifest .= ' ' . $key . '="' . $value . '"';
                  if ($tag[ 'tag' ] == 'file')
                    $filedata[ $key ] = $value;
                }

            if (($tag[ 'type' ] == 'complete') && (! isset($tag[ 'value' ])))
              $manifest .= '/';

            $manifest .= '>';

            if (isset($tag[ 'value' ]))
              { $manifest .= htmlspecialchars($tag[ 'value' ]);

                if ($tag[ 'type' ] == 'complete')
                  $manifest .= '</' . $tag[ 'tag' ] . '>';

                if (($tag[ 'tag' ] == 'imageproperty') && isset($tag[ 'attributes' ]))
                  if (isset($tag[ 'attributes' ][ 'id' ]))
                    $filedata[ 'imageproperty_' . $tag[ 'attributes' ][ 'id' ] ] = $tag[ 'value' ];
              }
          }
        elseif ($tag[ 'type' ] == 'close') {
            if ($tag[ 'tag' ] == 'file') {
                $protocol = 'http';
                if (isset($_SERVER[ 'HTTPS' ])) {
                    if ($_SERVER[ 'HTTPS' ] == 'on') {
                        $protocol .= 's';
                    }
                }

                $manifest .=
                    '<post href="' . $protocol . '://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'PHP_SELF' ] . '" name="userfile">' .
                    '   <formdata name="MAX_FILE_SIZE">10000000</formdata>' .
                    '   <formdata name="step">upload</formdata>' .
                    '   <formdata name="todir">' . htmlspecialchars($_REQUEST[ 'dir' ]) . '</formdata>';

                foreach ($filedata as $key => $value) {
                    $manifest .= '<formdata name="' . $key . '">' . htmlspecialchars($value) . '</formdata>';
                }

                $manifest .= '</post>';
            } elseif ( $tag['tag'] == 'filelist' ) {
                $manifest .= '<file id="0" source="dummy" extension=".don" contenttype="image/gif" destination="dummy.don" size="1842"><metadata><imageproperty id="cx">50</imageproperty><imageproperty id="cy">50</imageproperty></metadata><post href="' . $_CONF['site_admin_url'] . '/plugins/mediagallery/xppubwiz.php" name="userfile">  <formdata name="MAX_FILE_SIZE">10000000</formdata><formdata name="step">logout</formdata><formdata name="todir">' . $_REQUEST[ 'dir' ] . '</formdata><formdata name="id">2</formdata><formdata name="source">dummy</formdata><formdata name="extension">.don</formdata><formdata name="contenttype">image/gif</formdata><formdata name="destination">dummy.don</formdata><formdata name="size">1842</formdata>  <formdata name="imageproperty_cx">50</formdata><formdata name="imageproperty_cy">50</formdata></post></file>';
            } elseif ($tag[ 'level' ] == 1) {
                $manifest .= '<uploadinfo><htmlui href="' . $_MG_CONF['site_url'] . '/album.php?aid=' . $aid . '"/></uploadinfo>';
            }
            $manifest .= '</' . $tag[ 'tag' ] . '>';
          }
      }


    // Check whether we created well-formed XML ...

    if (xml_parse_into_struct($parser,$manifest,$tags,$index) >= 0)
      { ?>

        <script>

        var newxml = '<?php echo str_replace('\\', '\\\\', $manifest); ?>';
        var manxml = window.external.Property('TransferManifest');

        manxml.loadXML(newxml);

        window.external.Property('TransferManifest') = manxml;
        window.external.SetWizardButtons(true,true,true);

        content.innerHtml = manxml;
        window.external.FinalNext();

        </script>

        <?php
      }
}

if ( $step == 'logout' ) {
    $albums = $_REQUEST['todir'];
    MG_notifyModerators($albums);

    SESS_endUserSession ($_USER['uid']);
    PLG_logoutUser ($_USER['uid']);
    setcookie ($_CONF['cookie_session'], '', time() - 10000,
               $_CONF['cookie_path'], $_CONF['cookiedomain'],
               $_CONF['cookiesecure']);
    setcookie ($_CONF['cookie_name'], '', time() - 10000, $_CONF['cookie_path'],
               $_CONF['cookiedomain'], $_CONF['cookiesecure']);
}

// Step 4: This page will be called once for every file upload

if ($step == 'upload') {
    MG_xpPubHeader();

    if (isset($_FILES) && isset($_REQUEST[ 'todir' ]) && isset($_REQUEST[ 'destination' ])) {
        if (isset($_FILES) && isset($_REQUEST[ 'todir' ]) && isset($_REQUEST[ 'destination' ])) {
            if (isset($_FILES[ 'userfile' ]) && ($_REQUEST[ 'todir' ] != '') && ($_REQUEST[ 'destination' ] != '')) {
                // here we do our magic...
                $albums = $_REQUEST['todir'];
                $file_extension = strtolower(substr(strrchr($_FILES['userfile']['name'],"."),1));
                if ( $file_extension != 'don' ) {
                    //This will set the Content-Type to the appropriate setting for the file
                    switch( $file_extension ) {
                        case "exe":
                            $filetype="application/octet-stream";
                            break;
                        case "zip":
                            $filetype="application/zip";
                            break;
                        case "mp3":
                            $filetype="audio/mpeg";
                            break;
                        case "mpg":
                            $filetype="video/mpeg";
                            break;
                        case "avi":
                            $filetype="video/x-msvideo";
                            break;
                        case "tga":
                            $filetype="image/tga";
                            break;
                        case "psd":
                            $filetype="image/psd";
                            break;
                        case "jpg":
                            $filetype="image/jpeg";
                            break;
                        default:
                            $filetype="application/force-download";
                            break;
                    }
                    list($rc,$errorMessage) = MG_getFile( $_FILES['userfile']['tmp_name'], $_FILES['userfile']['name'],$albums, '', '', 1, 1,$filetype,0,0,'',0,0,0);
                    COM_errorLog("MG Upload: " . $_FILES['userfile']['name'] . ' ' . $errorMessage);
                }
            }
        }
    }
}
?>
<script language="javascript">

function OnBack()
{ <?php echo $ONBACK_SCRIPT; ?>
}

function OnNext()
{ <?php echo $ONNEXT_SCRIPT; ?>
}

function OnCancel()
{ // Don't know what this is good for:
  content.innerHtml+='<br>OnCancel';
}

function window.onload()
{ window.external.SetHeaderText("<?php echo strtr($cfg[ 'wizardheadline' ], '"', "'"); ?>","<?php echo strtr($cfg[ 'wizardbyline' ], '"', "'"); ?>");
  window.external.SetWizardButtons(<?php echo $WIZARD_BUTTONS; ?>);
}

    <?php echo $SCRIPT_CMD; ?>

</script>

</body>
</html>