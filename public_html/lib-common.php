<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | Common functions and startup code                                        |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2010 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Vincent Furia     - vinny01 AT users DOT sourceforge DOT net    |
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

// Prevent PHP from reporting uninitialized variables
error_reporting( E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR );

// this file can't be used on its own
if (strpos(strtolower($_SERVER['PHP_SELF']), 'lib-common.php') !== false) {
    die('This file can not be used on its own!');
}

// we must have PHP v5.3 or greater
if (version_compare(PHP_VERSION,'5.3.0','<')) {
    die('Sorry, glFusion requires PHP version 5.3.0 or greater.');
}

/**
* This is the common library for glFusion.  Through our code, you will see
* functions with the COM_ prefix (e.g. COM_siteHeader()).  Any such functions
* can be found in this file.
*
* --- You don't need to modify anything in this file! ---
*
* WARNING: put any custom hacks in lib-custom.php and not in here.  This file is
* modified frequently by the glFusion development team.  If you put your hacks in
* lib-custom.php you will find upgrading much easier.
*
*/

if (!defined ('GVERSION')) {
    define('GVERSION', '1.6.0');
}

define('PATCHLEVEL','.pl0');

//define('DEMO_MODE',true);

//define('DVLP_DEBUG',true);

/**
* Turn this on to get various debug messages from the code in this library
* @global Boolean $_COM_VERBOSE
*/

$_COM_VERBOSE = false;

// process all vars to handle magic_quotes_gpc
function all_stripslashes($var)
{
    if (get_magic_quotes_gpc()) {
        if (is_array($var)) {
            return array_map('all_stripslashes', $var);
        } else {
            return stripslashes($var);
        }
    } else {
        return $var;
    }
}
$_POST   = all_stripslashes($_POST);
$_GET    = all_stripslashes($_GET);
$_COOKIE = all_stripslashes($_COOKIE);
// Override the $_REQUEST setting...
$_REQUEST = array_merge($_GET, $_POST);
// set $REMOTE_ADDR
if (!isset($REMOTE_ADDR)) {
    $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
}

/**
  * Here, we shall establish an error handler. This will mean that whenever a
  * php level error is encountered, our own code handles it. This will hopefuly
  * go someway towards preventing nasties like path exposures from ever being
  * possible. That is, unless someone has overridden our error handler with one
  * with a path exposure issue...
  *
  * Must make sure that the function hasn't been disabled before calling it.
  *
  */
if ( function_exists('set_error_handler') ) {
    $defaultErrorHandler = set_error_handler('COM_handleError', error_reporting());
}

/**
  * Load the site configuration.  This is done in three steps:
  *
  * 1) siteconfig.php - instantiates _CONF & _SYSTEM arrays, sets
  * 2) config->load_baseconfig() - loads db-config.php, initializes database & config
  * 3) config->get_config('Core') - loads config pairs for group 'core'
  *
  */
require_once 'siteconfig.php' ;
require_once $_CONF['path_system'] . 'classes/config.class.php';

$config =& config::get_instance();
$config->set_configfile($_CONF['path'].'db-config.php');
$config->load_baseconfig();
$config->initConfig();

$_CONF = $config->get_config('Core');
if ( $_CONF['cookiesecure']) @ini_set('session.cookie_secure','1');

@date_default_timezone_set('America/Chicago');

if (isset($_CONF['bb2_enabled']) && $_CONF['bb2_enabled']) {
    require_once $_CONF['path_html'].'bad_behavior2/bad-behavior-glfusion.php';
}

$result = DB_query("SELECT * FROM {$_TABLES['vars']}");
while ($row = DB_fetchArray($result) ) {
    $_VARS[$row['name']] = $row['value'];
}

// set default UI styles
$uiStyles = array(
    'full_content' => array('left_class' => '',
                            'content_class' => 'gl_content-full',
                            'right_class' => ''),
    'left_content' => array('left_class' => '',
                            'content_class' => 'gl_content-wide-left',
                            'right_class' => ''),
    'left_content_right' => array('left_class' => '',
                                  'content_class' => 'gl_content',
                                  'right_class' => ''),
    'content_right' => array('left_class' => '',
                             'content_class' => 'gl_content-wide-right',
                             'right_class'  => '')
);

/**
  * Make sure some config values are set properly
  */
if ( !isset($_CONF['default_photo']) || $_CONF['default_photo'] == '' ) {
    $_CONF['default_photo'] = $_CONF['site_url'].'/images/userphotos/default.jpg';
}

if ( !isset($_SYSTEM['admin_session']) ) {
    $_SYSTEM['admin_session'] = 1200;
}

$_LOGO = array();
$result = DB_query("SELECT * FROM {$_TABLES['logo']}",1);
if ( $result ) {
    while ($row = DB_fetchArray($result)) {
        $_LOGO[$row['config_name']] = $row['config_value'];
    }
}

// Before we do anything else, check to ensure site is enabled

if (isset($_SYSTEM['site_enabled']) && !$_SYSTEM['site_enabled']) {

    if (empty($_CONF['site_disabled_msg'])) {
        header("HTTP/1.1 503 Service Unavailable");
        header("Status: 503 Service Unavailable");
        echo $_CONF['site_name'] . ' is temporarily down.  Please check back soon.';
    } else {
        // if the msg starts with http: assume it's a URL we should redirect to
        if (preg_match("/^(https?):/", $_CONF['site_disabled_msg']) === 1) {
            echo COM_refresh($_CONF['site_disabled_msg']);
        } else {
            header("HTTP/1.1 503 Service Unavailable");
            header("Status: 503 Service Unavailable");
            echo $_CONF['site_disabled_msg'];
        }
    }
    exit;
}

list($usec, $sec) = explode(' ', microtime());
mt_srand( (10000000000 * (float)$usec) ^ (float)$sec );
@date_default_timezone_set('America/Chicago');

// +--------------------------------------------------------------------------+
// | Library Includes                                                         |
// +--------------------------------------------------------------------------+

/**
* If needed, add our PEAR path to the list of include paths
*
*/
if ( !$_CONF['have_pear'] ) {
    $curPHPIncludePath = get_include_path();
    if ( defined( 'PATH_SEPARATOR' )) {
        $separator = PATH_SEPARATOR;
    } else {
        // prior to PHP 4.3.0, we have to guess the correct separator ...
        $separator = ';';
        if ( strpos( $curPHPIncludePath, $separator ) === false ) {
            $separator = ':';
        }
    }
    if ( set_include_path( $_CONF['path_pear'] . $separator.$curPHPIncludePath ) === false ) {
        COM_errorLog( 'set_include_path failed - there may be problems using the PEAR classes.', 1);
    }
}

/**
* Include page time -- used to time how fast each page was created
*
*/

require_once $_CONF['path_system'].'classes/timer.class.php';
$_PAGE_TIMER = new timerobject();
$_PAGE_TIMER->startTimer();

/**
* Include URL class
*
* This provides optional URL rewriting functionality.
*/

require_once $_CONF['path_system'].'classes/url.class.php';
$_URL = new url( $_CONF['url_rewrite'] );

/**
* This is our HTML template class.
*
*/

require_once $_CONF['path_system'].'classes/template.class.php';

/**
* This is our HTML filter / sanitization class.
*
*/
require_once $_CONF['path_system'].'classes/filter.class.php';

/**
* This is the database library.
*
*/

require_once $_CONF['path_system'].'lib-database.php';

/**
* This is the date / time library used for formatting
*
*/
require_once $_CONF['path_system'] . 'classes/date.class.php';

/**
* This is the output library used to control JS / CSS
*
*/
require_once $_CONF['path_system'].'classes/output.class.php';

/**
* Buffer all enabled plugins
*
*/

$result       = DB_query("SELECT pi_name,pi_version FROM {$_TABLES['plugins']} WHERE pi_enabled = 1");
$_PLUGINS     = array();
$_PLUGIN_INFO = array();

while ($A = DB_fetchArray($result)) {
    $_PLUGINS[] = $A['pi_name'];
    $_PLUGIN_INFO[$A['pi_name']] = $A['pi_version'];
}

/**
* This is the security library used for application security
*
*/

require_once $_CONF['path_system'].'lib-security.php';

/**
* Session management library
*
*/

require_once $_CONF['path_system'].'lib-sessions.php';

/**
* This is the syndication library used to offer (RSS) feeds.
*
*/

require_once $_CONF['path_system'].'lib-syndication.php';

/**
* This is the glFusion customization library
*
*/

require_once $_CONF['path_system'].'lib-glfusion.php';

/**
* Include plugin class.
*
*/

require_once $_CONF['path_system'].'lib-plugins.php';


/**
* Multibyte functions
*
*/
require_once $_CONF['path'].'lib/utf8/utf8.php';
// backward compatibility
require_once $_CONF['path_system'].'lib-mbyte.php';

/**
* Image processing library
*
*/

require_once $_CONF['path_system'].'imglib/lib-image.php';

/**
* Rating library
*
*/

require_once $_CONF['path_system'].'lib-rating.php';

/**
* Autotag library
*
*/

require_once $_CONF['path_system'].'lib-autotag.php';

/**
* This is the custom library.
*
* It is the sandbox for every glFusion Admin to play in.
* We will never modify this file.  This should hold all custom
* hacks to make upgrading easier.
*
*/

require_once $_CONF['path_system'].'lib-custom.php';

// Set theme

$usetheme = '';
if ( isset( $_POST['usetheme'] )) {
    $usetheme = COM_sanitizeFilename($_POST['usetheme'], true);
}
if ( $_CONF['allow_user_themes'] && !empty( $usetheme ) && is_dir( $_CONF['path_themes'] . $usetheme )) {
    $_USER['theme'] = $usetheme;
    $_CONF['path_layout'] = $_CONF['path_themes'] . $_USER['theme'] . '/';
    $_CONF['layout_url'] = $_CONF['site_url'] . '/layout/' . $_USER['theme'];
} else if ( $_CONF['allow_user_themes'] == 1 ) {
    if ( isset( $_COOKIE[$_CONF['cookie_theme']] ) ) {
        $theme = COM_sanitizeFilename($_COOKIE[$_CONF['cookie_theme']], true);
        if ( is_dir( $_CONF['path_themes'] . $theme )) {
            $_USER['theme'] = $theme;
            $_CONF['path_layout'] = $_CONF['path_themes'] . $theme . '/';
            $_CONF['layout_url'] = $_CONF['site_url'] . '/layout/' . $theme;

        }
    }

    if ( isset($_USER['theme']) && $_USER['theme'] == 'chameleon' ) {
        if (DB_count($_TABLES['plugins'], array("pi_name","pi_enabled"),array("chameleon","1")) < 1) {
            if ( $_CONF['theme'] != 'chameleon' ) {
                $_USER['theme'] = $_CONF['theme'];
            } else {
                $_USER['theme'] = 'default';
            }
        }
    }

    if ( !empty( $_USER['theme'] )) {
        if ( is_dir( $_CONF['path_themes'] . $_USER['theme'] )) {
            $_CONF['path_layout'] = $_CONF['path_themes'] . $_USER['theme'] . '/';
            $_CONF['layout_url'] = $_CONF['site_url'] . '/layout/' . $_USER['theme'];
        } else {
            $_USER['theme'] = $_CONF['theme'];
        }
    }
} else if ($_CONF['theme'] == 'chameleon' ) {
    if (DB_count($_TABLES['plugins'], array("pi_name","pi_enabled"),array("chameleon","1")) < 1) {
        $_USER['theme'] = 'default';
        $_CONF['theme'] = 'default';
        $_CONF['path_layout'] = $_CONF['path_themes'] . $_USER['theme'] . '/';
        $_CONF['layout_url'] = $_CONF['site_url'] . '/layout/' . $_USER['theme'];
    }
}
$TEMPLATE_OPTIONS['default_vars']['layout_url'] = $_CONF['layout_url'];

// Set language

if ( isset( $_COOKIE[$_CONF['cookie_language']] ) ) {
    $language = COM_sanitizeFilename($_COOKIE[$_CONF['cookie_language']]);
    if ( is_file( $_CONF['path_language'] . $language . '.php' ) &&
            ( $_CONF['allow_user_language'] == 1 )) {
        $_USER['language'] = $language;
        $_CONF['language'] = $language;
    } else {
        $_USER['language'] = $_CONF['language'];
    }
} else if ( !empty( $_USER['language'] )) {
    if ( is_file( $_CONF['path_language'] . $_USER['language'] . '.php' ) &&
            ( $_CONF['allow_user_language'] == 1 ))
    {
        $_CONF['language'] = $_USER['language'];
    }
} else if ( !empty( $_CONF['languages'] ) && !empty( $_CONF['language_files'] )) {
    $_CONF['language'] = COM_getLanguage();
}

/**
*
* Language include
*
*/

require_once $_CONF['path_language'] . $_CONF['language'] . '.php';

if (empty($LANG_DIRECTION)) {
    // default to left-to-right
    $LANG_DIRECTION = 'ltr';
}

if ( isset($LANG_LOCALE)) {
    $_CONF['iso_lang'] = $LANG_LOCALE;
} else {
    // Set the ISO 2 digit code for language
    switch ($_CONF['language']) {
        case 'afrikaans' :
        case 'afrikaans_utf-8' :
            $_CONF['iso_lang'] = 'af';
            break;
        case 'bosnian' :
        case 'bosnian_utf-8' :
            $_CONF['iso_lang'] = 'bs';
            break;
        case 'bulgarian' :
        case 'bulgarian_utf-8' :
            $_CONF['iso_lang'] = 'bg';
            break;
        case 'catalan' :
        case 'catalan_utf-8' :
            $_CONF['iso_lang'] = 'ca';
            break;
           case 'chinese_traditional' :
           case 'chinese_traditional_utf-8' :
               $_CONF['iso_lang'] = 'zh-tw';
               break;
           case 'chinese_simplified' :
           case 'chinese_simplified_utf-8' :
               $_CONF['iso_lang'] = 'zh';
               break;
        case 'croatian' :
        case 'croatian_utf-8' :
            $_CONF['iso_lang'] = 'hr';
            break;
        case 'czech' :
        case 'czech_utf-8' :
            $_CONF['iso_lang'] = 'cs';
            break;
        case 'danish' :
        case 'danish_utf-8' :
            $_CONF['iso_lang'] = 'da';
            break;
        case 'dutch' :
        case 'dutch_utf-8' :
            $_CONF['iso_lang'] = 'nl';
            break;
        case 'english' :
        case 'english_utf-8' :
            $_CONF['iso_lang'] = 'en';
            break;
        case 'estonian' :
        case 'estonian_utf-8' :
            $_CONF['iso_lang'] = 'et';
            break;
        case 'farsi' :
        case 'farsi_utf-8' :
            $_CONF['iso_lang'] = 'fa';
            break;
        case 'finnish' :
        case 'finnish_utf-8' :
            $_CONF['iso_lang'] = 'fi';
            break;
        case 'french_canada' :
        case 'french_canada_utf-8' :
        case 'french_france' :
        case 'french_france_utf-8' :
            $_CONF['iso_lang'] = 'fr';
            break;
        case 'german' :
        case 'german_utf-8' :
        case 'german_formal' :
        case 'german_formal_utf-8' :
            $_CONF['iso_lang'] = 'de';
            break;
        case 'hebrew' :
        case 'hebrew_utf-8' :
            $_CONF['iso_lang'] = 'he';
            break;
        case 'hellenic' :
        case 'hellenic_utf-8' :
            $_CONF['iso_lang'] = 'el';
            break;
           case 'indonesian' :
           case 'indonesian_utf-8' :
               $_CONF['iso_lang'] = 'id';
               break;
           case 'italian' :
           case 'italian_utf-8' :
               $_CONF['iso_lang'] = 'it';
               break;
           case 'japanese' :
           case 'japanese_utf-8' :
               $_CONF['iso_lang'] = 'ja';
               break;
        case 'korean' :
        case 'korean_utf-8' :
            $_CONF['iso_lang'] = 'ko';
            break;
           case 'norwegian' :
           case 'norwegian_utf-8' :
               $_CONF['iso_lang'] = 'no';
               break;
           case 'polish' :
           case 'polish_utf-8' :
               $_CONF['iso_lang'] = 'pl';
               break;
           case 'portuguese_brazil' :
           case 'portuguese_brazil_utf-8' :
               $_CONF['iso_lang'] = 'pt-btr';
               break;
           case 'portuguese' :
           case 'portuguese_utf-8' :
               $_CONF['iso_lang'] = 'pt';
               break;
           case 'romanian' :
           case 'romanian_utf-8' :
               $_CONF['iso_lang'] = 'ro';
               break;
           case 'russian' :
           case 'russian_utf-8' :
               $_CONF['iso_lang'] = 'ru';
               break;
           case 'slovak' :
           case 'slovak_utf-8' :
               $_CONF['iso_lang'] = 'sk';
               break;
           case 'slovenian' :
           case 'slovenian_utf-8' :
               $_CONF['iso_lang'] = 'sl';
               break;
        case 'spanish' :
        case 'spanish_utf-8' :
            $_CONF['iso_lang'] = 'es';
            break;
           case 'swedish' :
           case 'swedish_utf-8' :
               $_CONF['iso_lang'] = 'sv';
               break;
           case 'turkish' :
           case 'turkish_utf-8' :
               $_CONF['iso_lang'] = 'tr';
               break;
           case 'ukrainian' :
           case 'ukrainian_utf-8' :
               $_CONF['iso_lang'] = 'uk';
               break;
        default :
            $_CONF['iso_lang'] = 'en';
            break;
    }
}

$TEMPLATE_OPTIONS['default_vars']['iso_lang'] = $_CONF['iso_lang'];

/**
* Include theme functions file
*/

// Include theme functions file
if (file_exists($_CONF['path_layout'] . 'custom/functions.php') ) {
    require_once $_CONF['path_layout'] . 'custom/functions.php';
} elseif (file_exists($_CONF['path_layout'] . 'functions.php')) {
    require_once $_CONF['path_layout'] . 'functions.php';
}

if (!isset($_SYSTEM['framework']) ) $_SYSTEM['framework'] = 'legacy';

// ensure XHTML constant is defined to avoid problems elsewhere

if (!defined('XHTML')) {
    define('XHTML', '');
}

// themes can now specify the default image type
// fall back to 'gif' if they don't

if (empty($_IMAGE_TYPE)) {
    $_IMAGE_TYPE = 'gif';
}

COM_switchLocaleSettings();

if ( setlocale( LC_ALL, $_CONF['locale'] ) === false ) {
    setlocale( LC_TIME, $_CONF['locale'] );
}

@date_default_timezone_set($_CONF['timezone']);

/**
* Global array of groups current user belongs to
*
* @global array $_GROUPS
*
*/

if ( !COM_isAnonUser() ) {
    $_GROUPS = SEC_getUserGroups( $_USER['uid'] );
} else {
    $_GROUPS = SEC_getUserGroups( 1 );
}

/**
* Global array of current user permissions [read,edit]
*
* @global array $_RIGHTS
*
*/

$_RIGHTS = explode( ',', SEC_getUserPermissions() );

require_once $_CONF['path_system'].'lib-menu.php';

if ( isset( $_GET['topic'] )) {
    $topic = COM_applyFilter( $_GET['topic'] );
} else if ( isset( $_POST['topic'] )) {
    $topic = COM_applyFilter( $_POST['topic'] );
} else {
    $topic = '';
}

/**
* Get the name of the current language, minus the character set
*
* Strips the character set from $_CONF['language'].
*
* @return   string  language name
*
*/
function COM_getLanguageName()
{
    global $_CONF;

    $retval = '';

    $charset = '_' . strtolower(COM_getCharset());
    if (substr($_CONF['language'], -strlen($charset)) == $charset) {
        $retval = substr($_CONF['language'], 0, -strlen($charset));
    } else {
        $retval = $_CONF['language'];
    }

    return $retval;
}

/**
* Return the file to use for a block template.
*
* This returns the template needed to build the HTML for a block.  This function
* allows designers to give a block it's own custom look and feel.  If no
* templates for the block are specified, the default blockheader.html and
* blockfooter.html will be used.
*
* @param        string      $blockname      corresponds to name field in block table
* @param        string      $which          can be either 'header' or 'footer' for corresponding template
* @param        string      $position       can be 'left', 'right' or blank. If set, will be used to find a side specific override template.
* @see function COM_startBlock
* @see function COM_endBlock
* @see function COM_showBlocks
* @see function COM_showBlock
* @return   string  template name
*/
function COM_getBlockTemplate( $blockname, $which, $position='' )
{
    global $_BLOCK_TEMPLATE, $_COM_VERBOSE, $_CONF;

    if ( $_COM_VERBOSE ) {
        COM_errorLog( "_BLOCK_TEMPLATE[$blockname] = " . $_BLOCK_TEMPLATE[$blockname], 1 );
    }

    if ( !empty( $_BLOCK_TEMPLATE[$blockname] )) {
        $templates = explode( ',', $_BLOCK_TEMPLATE[$blockname] );
        if ( $which == 'header' ) {
            if ( !empty( $templates[0] )  ) {
                $template = $templates[0];
            } else {
                $template = 'blockheader.thtml';
            }
        } else {
            if ( !empty( $templates[1] )  ) {
                $template = $templates[1];
            } else {
                $template = 'blockfooter.thtml';
            }
        }
    } else {
        if ( $which == 'header' ) {
            $template = 'blockheader.thtml';
        } else {
            $template = 'blockfooter.thtml';
        }
    }

    // If we have a position specific request, and the template is not already
    // position specific then look to see if there is a position specific
    // override.
    $templateLC = strtolower($template);
    if ( !empty($position) && ( strpos($templateLC, $position) === false ) ) {
        // Trim .thtml from the end.
        $positionSpecific = substr($template, 0, strlen($template) - 6);
        $positionSpecific .= '-' . $position . '.thtml';
        if ( file_exists( $_CONF['path_layout'] . $positionSpecific ) ) {
            $template = $positionSpecific;
        }
    }

    if ( $_COM_VERBOSE ) {
        COM_errorLog( "Block template for the $which of $blockname is: $template", 1 );
    }

    return $template;
}

/**
* Gets all installed themes
*
* Returns a list of all the directory names in $_CONF['path_themes'], i.e.
* a list of all the theme names.
*
* @param    boolean $all    if true, return all themes even if users aren't allowed to change their default themes
* @return   array           All installed themes
*
*/
function COM_getThemes( $all = false )
{
    global $_CONF, $_PLUGINS;

    $index = 1;

    $themes = array();

    // If users aren't allowed to change their theme then only return the default theme

    if (( $_CONF['allow_user_themes'] == 0 ) && !$all ) {
        $themes[$index] = $_CONF['theme'];
    } else {
        $fd = opendir( $_CONF['path_themes'] );

        while (( $dir = @readdir( $fd )) == TRUE ) {
            if ( is_dir( $_CONF['path_themes'] . $dir) && $dir <> '.' && $dir <> '..' && $dir <> 'CVS' && substr( $dir, 0 , 1 ) <> '.' ) {
                clearstatcache();
                if ( $dir == 'chameleon' ) {
                    if (in_array($dir,$_PLUGINS)) {
                        $themes[$index] = $dir;
                        $index++;
                    }
                } else {
                    $themes[$index] = $dir;
                    $index++;
                }
            }
        }
    }

    return $themes;
}


/**
* Returns the site header
*
* This loads the proper templates, does variable substitution and returns the
* HTML for the site header with or without blocks depending on the value of $what
*
* Programming Note:
*
* The two functions COM_siteHeader and COM_siteFooter provide the framework for
* page display in glFusion.  COM_siteHeader controls the display of the Header
* and left blocks and COM_siteFooter controls the dsiplay of the right blocks
* and the footer.  You use them like a sandwich.  Thus the following code will
* display a glFusion page with both right and left blocks displayed.
*
* <code>
* <?php
* require_once('lib-common.php');
* $display .= COM_siteHeader(); //Change to COM_siteHeader('none') to not display left blocks
* $display .= "Here is your html for display";
* $display .= COM_siteFooter(true);  // Change to COM_siteFooter() to not display right blocks
* echo $display;
* ? >
* </code>
*
* Note that the default for the header is to display the left blocks and the
* default of the footer is to not display the right blocks.
*
* This sandwich produces code like this (greatly simplified)
* <code>
* // COM_siteHeader
* <table><tr><td colspan="3">Header</td></tr>
* <tr><td>Left Blocks</td><td>
*
* // Your HTML goes here
* Here is your html for display
*
* // COM_siteFooter
* </td><td>Right Blocks</td></tr>
* <tr><td colspan="3">Footer</td></table>
* </code>
*
* @param    string  $what       If 'none' then no left blocks are returned, if
*                               'menu' (default) then right blocks are returned
* @param    string  $pagetitle  optional content for the page's <title>
* @param    string  $headercode optional code to go into the page's <head>
* @return   string              Formatted HTML containing the site header
* @see function COM_siteFooter
*
*/

function COM_siteHeader($what = 'menu', $pagetitle = '', $headercode = '' )
{
    global $_CONF, $_SYSTEM, $_VARS, $_TABLES, $_USER, $LANG01, $LANG_BUTTONS, $LANG_DIRECTION,
           $_IMAGE_TYPE, $topic, $_COM_VERBOSE, $theme_what, $theme_pagetitle,
           $theme_headercode, $theme_layout, $blockInterface;

    if ( !isset($_USER['theme']) || $_USER['theme'] == '' ) {
        $_USER['theme'] = $_CONF['theme'];
    }

    $function = $_USER['theme'] . '_siteHeader';

    if ( function_exists( $function )) {
        return $function( $what, $pagetitle, $headercode );
    }

    $dt = new Date('now',$_USER['tzid']);

    static $headerCalled = 0;

    if ( $headerCalled == 1 ) {
        return '';
    }
    $headerCalled = 1;
    if ( is_array($what) ) {
        $theme_what = array();
    }

    $theme_pagetitle    = $pagetitle;
    $theme_headercode   = $headercode;

    if ( isset($blockInterface['left'] )) {
        $currentURL = COM_getCurrentURL();
        if ( strpos($currentURL, $_CONF['site_admin_url']) === 0 ) {
            if ( $blockInterface['left']['location'] == 'right' ||
                 $blockInterface['left']['location'] == 'left' ) {
                $theme_what = 'none';
            } else {
                $theme_what = $what;
            }
        } else {
            $theme_what = $what;
        }
    } else {
        $theme_what = $what;
    }

    $header = new Template( $_CONF['path_layout'] );
    $header->set_file('header','htmlheader.thtml');

    $cacheID = SESS_getVar('cacheID');
    if ( empty($cacheID) || $cacheID == '' ) {
        if ( !isset($_VARS['cacheid']) ) {
            $cacheID = 'css_' . md5( time() );
            $_VARS['cacheid'] = $cacheID;
        } else {
            $cacheID = $_VARS['cacheid'];
        }
        SESS_setVar('cacheID',$cacheID);
    }

    // give the theme a chance to load stuff....

    $function = $_USER['theme'] . '_headerVars';
    if ( function_exists( $function )) {
        $function( $header );
    }

    // get topic if not on home page
    if ( !isset( $_GET['topic'] )) {
        if ( isset( $_GET['story'] )) {
            $sid = COM_applyFilter( $_GET['story'] );
        } elseif ( isset( $_GET['sid'] )) {
            $sid = COM_applyFilter( $_GET['sid'] );
        } elseif ( isset( $_POST['story'] )) {
            $sid = COM_applyFilter( $_POST['story'] );
        }
        if ( empty( $sid ) && $_CONF['url_rewrite'] &&
                ( strpos( $_SERVER['PHP_SELF'], 'article.php' ) !== false )) {
            COM_setArgNames( array( 'story', 'mode' ));
            $sid = COM_applyFilter( COM_getArgument( 'story' ));
        }
        if ( !empty( $sid )) {
            $topic = DB_getItem( $_TABLES['stories'], 'tid', "sid='".DB_escapeString($sid)."'" );
        }
    } else {
        $topic = COM_applyFilter( $_GET['topic'] );
    }

    $feed_url = array();
    if ( $_CONF['backend'] == 1 ) { // add feed-link to header if applicable
        if ( SESS_isSet('feedurl') ) {
            $feed_url = unserialize(SESS_getVar('feedurl') );
        } else {
            $baseurl = SYND_getFeedUrl();

            $sql = 'SELECT format, filename, title, language FROM '
                 . $_TABLES['syndication'] . " WHERE (header_tid = 'all')";
            if ( !empty( $topic )) {
                $sql .= " OR (header_tid = '" . DB_escapeString( $topic ) . "')";
            }
            $result = DB_query( $sql );
            $numRows = DB_numRows( $result );
            for( $i = 0; $i < $numRows; $i++ ) {
                $A = DB_fetchArray( $result );
                if ( !empty( $A['filename'] )) {
                    $format = explode( '-', $A['format'] );
                    $format_type = strtolower( $format[0] );
                    $format_name = ucwords( $format[0] );

                    $feed_url[] = '<link rel="alternate" type="application/'
                              . $format_type . '+xml"'
                              . ' href="' . $baseurl . $A['filename'] . '" title="'
                              . $format_name . ' Feed: ' . $A['title'] . '"/>';
                }
            }
            SESS_setVar('feedurl',serialize($feed_url));
        }
    }
    $header->set_var( 'feed_url', implode( LB, $feed_url ));

    $relLinks = array();
    if ( !COM_onFrontpage() ) {
        $relLinks['home'] = '<link rel="home" href="' . $_CONF['site_url']
                          . '/" title="' . $LANG01[90] . '"/>';
    } else {
        CMT_updateCommentcodes();
    }
    $loggedInUser = !COM_isAnonUser();
    if ( $loggedInUser || (( $_CONF['loginrequired'] == 0 ) &&
                ( $_CONF['searchloginrequired'] == 0 ))) {
        if (( substr( $_SERVER['PHP_SELF'], -strlen( '/search.php' ))
                != '/search.php' ) || isset( $_GET['mode'] )) {
            $relLinks['search'] = '<link rel="search" href="'
                                . $_CONF['site_url'] . '/search.php" title="'
                                . $LANG01[75] . '"/>';
        }
    }
    if ( $loggedInUser || (( $_CONF['loginrequired'] == 0 ) &&
                ( $_CONF['directoryloginrequired'] == 0 ))) {
        if ( strpos( $_SERVER['PHP_SELF'], '/article.php' ) !== false ) {
            $relLinks['contents'] = '<link rel="contents" href="'
                        . $_CONF['site_url'] . '/directory.php" title="'
                        . $LANG01[117] . '"/>';
        }
    }
    if (!$_CONF['disable_webservices']) {
        $relLinks['service'] = '<link rel="service" '
                    . 'type="application/atomsvc+xml" ' . 'href="'
                    . $_CONF['site_url'] . '/webservices/atom/index.php?introspection" '
                    . 'title="' . $LANG01[130] . '"/>' . LB;
    }
    $header->set_var( 'rel_links', implode( LB, $relLinks ));

    if ( empty( $pagetitle ) && isset( $_CONF['pagetitle'] )) {
        $pagetitle = $_CONF['pagetitle'];
    }
    if ( empty( $pagetitle )) {
        if ( empty( $topic )) {
            $pagetitle = $_CONF['site_slogan'];
        } else {
            $pagetitle = DB_getItem( $_TABLES['topics'], 'topic',
                                                   "tid = '".DB_escapeString($topic)."'" );
        }
    }
    if ( !empty( $pagetitle )) {
        $header->set_var( 'page_site_splitter', ' - ');
    } else {
        $header->set_var( 'page_site_splitter', '');
    }
    $header->set_var( 'page_title', $pagetitle );
    $header->set_var( 'site_name', $_CONF['site_name']);

    if (COM_onFrontpage()) {
        $title_and_name = $_CONF['site_name'];
        if (!empty($pagetitle)) {
            $title_and_name .= ' - ' . $pagetitle;
        }
    } else {
        $title_and_name = '';
        if (!empty($pagetitle)) {
            $title_and_name = $pagetitle . ' - ';
        }
        $title_and_name .= $_CONF['site_name'];
    }
    $header->set_var('page_title_and_site_name', $title_and_name);

    $rdf = substr_replace( $_CONF['rdf_file'], $_CONF['site_url'], 0,strlen( $_CONF['path_html'] ) - 1 ) . LB;

    list($cacheFile,$style_cache_url) = COM_getStyleCacheLocation();
    list($cacheFile,$js_cache_url) = COM_getJSCacheLocation();

    $header->set_var(array(
        'site_name'     => $_CONF['site_name'],
        'site_slogan'   => $_CONF['site_slogan'],
        'rdf_file'      => $rdf,
        'rss_url'       => $rdf,
        'css_url'       => $_CONF['layout_url'] . '/style.css',
        'theme'         => $_USER['theme'],
        'style_cache_url'   => $style_cache_url,
        'js_cache_url'      => $js_cache_url,
        'charset'       => COM_getCharset(),
        'cacheid'       => $_USER['theme'].$cacheID,
        'direction'     => (empty($LANG_DIRECTION) ? 'ltr' : $LANG_DIRECTION),
        'plg_headercode'    => $headercode . PLG_getHeaderCode()
    ));

    // Call to plugins to set template variables in the header
    PLG_templateSetVars( 'header', $header );

    $header->parse( 'index_header', 'header' );
    $retval = $header->finish( $header->get_var( 'index_header' ));

    echo $retval;

    // Start caching / capturing output from glFusion / plugins
    ob_start();
    return '';
}

/**
* Returns the site footer
*
* This loads the proper templates, does variable substitution and returns the
* HTML for the site footer.
*
* @param   boolean     $rightblock     Whether or not to show blocks on right hand side default is no
* @param   array       $custom         An array defining custom function to be used to format Rightblocks
* @see function COM_siteHeader
* @return   string  Formated HTML containing site footer and optionally right blocks
*
*/
function COM_siteFooter( $rightblock = -1, $custom = '' )
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG12, $LANG_BUTTONS, $LANG_DIRECTION,
           $_IMAGE_TYPE, $topic, $_COM_VERBOSE, $_PAGE_TIMER, $theme_what,
           $theme_pagetitle, $theme_headercode, $theme_layout,
           $_LOGO,$uiStyles;

    COM_hit();

    if ( isset($blockInterface['right']) ) {
        $currentURL = COM_getCurrentURL();
        if ( strpos($currentURL, $_CONF['site_admin_url']) === 0 ) {
            if ( $blockInterface['right']['location'] == 'right' || $blockInterface['right']['location'] == 'left' ) {
                $rightblocks = -1;
            }
        }
    }

    $function = $_USER['theme'] . '_siteFooter';
    if ( function_exists( $function )) {
        return $function( $rightblock, $custom );
    }

    $dt = new Date('now',$_USER['tzid']);

    $what       = $theme_what;
    $pagetitle  = $theme_pagetitle;
    $themecode  = $theme_headercode;

    // Grab any content that was cached by the system

    $content = ob_get_contents();
    ob_end_clean();

    $theme = new Template( $_CONF['path_layout'] );
    $theme->set_file( array(
        'header'        => 'header.thtml',
        'footer'        => 'footer.thtml',
        'leftblocks'    => 'leftblocks.thtml',
        'rightblocks'   => 'rightblocks.thtml',
    ));

    $theme->set_var( 'num_search_results',$_CONF['num_search_results'] );
    // get topic if not on home page
    if ( !isset( $_GET['topic'] )) {
        if ( isset( $_GET['story'] )) {
            $sid = COM_applyFilter( $_GET['story'] );
        } elseif ( isset( $_GET['sid'] )) {
            $sid = COM_applyFilter( $_GET['sid'] );
        } elseif ( isset( $_POST['story'] )) {
            $sid = COM_applyFilter( $_POST['story'] );
        }
        if ( empty( $sid ) && $_CONF['url_rewrite'] &&
                ( strpos( $_SERVER['PHP_SELF'], 'article.php' ) !== false )) {
            COM_setArgNames( array( 'story', 'mode' ));
            $sid = COM_applyFilter( COM_getArgument( 'story' ));
        } if ( !empty( $sid )) {
            $topic = DB_getItem( $_TABLES['stories'], 'tid', "sid='".DB_escapeString($sid)."'" );
        }
    } else {
        $topic = COM_applyFilter( $_GET['topic'] );
    }

    $loggedInUser = !COM_isAnonUser();
    $theme->set_var( 'site_name', $_CONF['site_name']);
    $theme->set_var( 'background_image', $_CONF['layout_url'].'/images/bg.' . $_IMAGE_TYPE );
    $theme->set_var( 'site_mail', "mailto:{$_CONF['site_mail']}" );
    if ($_LOGO['display_site_slogan']) {
        $theme->set_var( 'site_slogan', $_CONF['site_slogan'] );
    }
    $msg = $LANG01[67] . ' ' . $_CONF['site_name'];

    if ( !empty( $_USER['username'] ) && !COM_isAnonUser()) {
        $msg .= ', ' . COM_getDisplayName( $_USER['uid'], $_USER['username'],
                                           $_USER['fullname'] );
    }

    $curtime = $dt->format($dt->getUserFormat(),true);

    $theme->set_var( 'welcome_msg', $msg );
    $theme->set_var( 'datetime', $curtime );

    if ( $_LOGO['use_graphic_logo'] == 1 && file_exists($_CONF['path_html'] . '/images/' . $_LOGO['logo_name']) ) {
        $L = new Template( $_CONF['path_layout'] );
        $L->set_file( array(
            'logo'          => 'logo-graphic.thtml',
        ));

        $imgInfo = @getimagesize($_CONF['path_html'] . '/images/' . $_LOGO['logo_name']);
        $dimension = $imgInfo[3];

        $L->set_var( 'site_name', $_CONF['site_name'] );
        $site_logo = $_CONF['site_url'] . '/images/' . $_LOGO['logo_name'];
        $L->set_var( 'site_logo', $site_logo);
        $L->set_var( 'dimension', $dimension );
        if ( $imgInfo[1] != 100 ) {
            $delta = 100 - $imgInfo[1];
            $newMargin = $delta;
            $L->set_var( 'delta', 'style="padding-top:' . $newMargin . 'px;"');
        } else {
            $L->set_var('delta','');
        }
        if ($_LOGO['display_site_slogan']) {
            $L->set_var( 'site_slogan', $_CONF['site_slogan'] );
        }
        $L->parse('output','logo');
        $theme->set_var('logo_block',$L->finish($L->get_var('output')));
    } else if ( $_LOGO['use_graphic_logo'] == 0 ) {
        $L = new Template( $_CONF['path_layout'] );
        $L->set_file( array(
            'logo'          => 'logo-text.thtml',
        ));
        $L->set_var( 'site_name', $_CONF['site_name'] );
        if ($_LOGO['display_site_slogan']) {
            $L->set_var( 'site_slogan', $_CONF['site_slogan'] );
        }
        $L->parse('output','logo');
        $theme->set_var('logo_block',$L->finish($L->get_var('output')));
    } else {
        $theme->set_var('logo_block','');
    }

    $theme->set_var( 'site_logo', $_CONF['layout_url']
                                   . '/images/logo.' . $_IMAGE_TYPE );
    $theme->set_var( array (
        'lang_login'        => $LANG01[58],
        'lang_myaccount'    => $LANG01[48],
        'lang_logout'       => $LANG01[35],
        'lang_newuser'      => $LANG12[3],
    ));

    $menu_navigation = displayMenu('navigation');
    $menu_footer     = displayMenu('footer');
    $menu_header     = displayMenu('header');

    $theme->set_var(array(
                    'menu_navigation'   => $menu_navigation,
                    'menu_footer'       => $menu_footer,
                    'menu_header'       => $menu_header,
                    'st_hmenu'          => $menu_navigation,    // depreciated
                    'st_footer_menu'    => $menu_footer,        // depreciated
                    'st_header_menu'    => $menu_header,        // depreciated
                    ));
    $lblocks = '';

    /* Check if an array has been passed that includes the name of a plugin
     * function or custom function
     * This can be used to take control over what blocks are then displayed
     */
    if ( is_array( $what )) {
        $function = $what[0];
        if ( function_exists( $function )) {
            $lblocks = $function( $what[1], 'left' );
        } else {
            $lblocks = COM_showBlocks( 'left', $topic );
        }
    } else if ( $what <> 'none' ) {
        // Now show any blocks -- need to get the topic if not on home page
        $lblocks = COM_showBlocks( 'left', $topic );
    }

    /* Now build footer */

    if ( empty( $lblocks )) {
        $theme->set_var( 'left_blocks', '' );
        $theme->set_var( 'glfusion_blocks', '' );
    } else {
        $theme->set_var( 'glfusion_blocks', $lblocks );
    }

    // Do variable assignments

    $theme->set_var( 'site_mail', "mailto:{$_CONF['site_mail']}" );
    $theme->set_var( 'site_slogan', $_CONF['site_slogan'] );

    $rdf = substr_replace( $_CONF['rdf_file'], $_CONF['site_url'], 0,
                          strlen( $_CONF['path_html'] ) - 1 ) . LB;
    $theme->set_var( 'rdf_file', $rdf );
    $theme->set_var( 'rss_url', $rdf );

    $year = date( 'Y' );
    $copyrightyear = $year;
    if ( !empty( $_CONF['copyrightyear'] ) ) {
        if ($year == $_CONF['copyrightyear']) {
            $copyrightyear = $_CONF['copyrightyear'];
        } else {
            $copyrightyear = $_CONF['copyrightyear'] . " - " . $year;
        }
    }
    $theme->set_var( 'copyright_notice', $LANG01[93] . ' &copy; '
            . $copyrightyear . ' ' . $_CONF['site_name'] . '&nbsp;&nbsp;&bull;&nbsp;&nbsp;'
            . $LANG01[94] );
    $theme->set_var( 'copyright_msg', $LANG01[93] . ' &copy; '
            . $copyrightyear . ' ' . $_CONF['site_name'] );
    $theme->set_var( 'current_year', $year );
    $theme->set_var( 'lang_copyright', $LANG01[93] );
    $theme->set_var( 'trademark_msg', $LANG01[94] );
    $theme->set_var( 'powered_by', $LANG01[95]);
    $theme->set_var( 'glfusion_url', 'http://www.glfusion.org/' );
    $theme->set_var( 'glfusion_version', GVERSION );
    $theme->set_var( 'direction',(empty($LANG_DIRECTION) ? 'ltr' : $LANG_DIRECTION));

    /* Check if an array has been passed that includes the name of a plugin
     * function or custom function.
     * This can be used to take control over what blocks are then displayed
     */
    if ( is_array( $custom )) {
        $function = $custom['0'];
        if ( function_exists( $function )) {
            $rblocks = $function( $custom['1'], 'right' );
        }
    } elseif ( $rightblock == 1 || $_CONF['show_right_blocks'] == 1 ) {
        $rblocks = '';

        $rblocks = COM_showBlocks( 'right', $topic );

        if ( empty( $rblocks )) {
            $theme->set_var( 'glfusion_rblocks', '');
            $theme->set_var( 'right_blocks','');
            if ( empty($lblocks) ) {
                // using full_content
                $theme->set_var( 'centercolumn',$uiStyles['full_content']['content_class'] );
            } else {
                // using left_content
                $theme->set_var( 'centercolumn',$uiStyles['left_content']['content_class'] );
                $theme->set_var( 'footercolumn-l',$uiStyles['left_content']['left_class']);
            }
        } else {
            $theme->set_var( 'glfusion_rblocks', $rblocks);
            if ( empty($lblocks) ) {
                // using content_right
                $theme->set_var( 'centercolumn',$uiStyles['content_right']['content_class'] );
                $theme->set_var( 'footercolumn-r',$uiStyles['content_right']['right_class']);
            } else {
                // using left_content_right
                $theme->set_var( 'centercolumn',$uiStyles['left_content_right']['content_class'] );
                $theme->set_var( 'footercolumn-l',$uiStyles['left_content_right']['left_class']);
                $theme->set_var( 'footercolumn-r',$uiStyles['left_content_right']['right_class']);
            }
        }
    } else {
        $theme->set_var( 'glfusion_rblocks', '');
        $theme->set_var( 'right_blocks', '' );
        if ( empty( $lblocks )) {
            // using full content
            $theme->set_var( 'centercolumn',$uiStyles['full_content']['content_class'] );
        } else {
            // using left_content
            $theme->set_var( 'centercolumn',$uiStyles['left_content']['content_class'] );
            $theme->set_var( 'footercolumn-l',$uiStyles['left_content']['left_class']);
        }
    }

    if ( !empty( $lblocks) ) {
        $theme->parse( 'left_blocks', 'leftblocks', true );
        $theme->set_var( 'glfusion_blocks', '');
    }
    if ( !empty ($rblocks) ) {
        $theme->parse( 'right_blocks', 'rightblocks', true );
        $theme->set_var( 'glfusion_rblocks', '');
    }

    $exectime = $_PAGE_TIMER->stopTimer();
    $exectext = $LANG01[91] . ' ' . $exectime . ' ' . $LANG01[92];

    $theme->set_var( 'execution_time', $exectime );
    $theme->set_var( 'execution_textandtime', $exectext );

    $theme->set_var('content',$content);

    // grab header data from outputHandler
    $outputHandle = outputHandler::getInstance();
    $theme->set_var(array(
                'meta-header'  => $outputHandle->renderHeader('meta'),
                'css-header'   => $outputHandle->renderHeader('style'),
                'js-header'    => $outputHandle->renderHeader('script'),
                'raw-header'   => $outputHandle->renderHeader('raw'),
    ));
    if ( SESS_isSet('glfusion.infoblock') ) {
        $msgArray = @unserialize(SESS_getVar('glfusion.infoblock'));
        $msgTxt = COM_showMessageText($msgArray['msg'], '', $persist = false, $msgArray['type']);
        $theme->set_var('info_block',$msgTxt);
        SESS_unSet('glfusion.infoblock');
    }
    // Call to plugins to set template variables in the footer
    PLG_templateSetVars( 'header', $theme );
    PLG_templateSetVars( 'footer', $theme );

    // Actually parse the template and make variable substitutions
    $theme->parse( 'index_footer', 'footer' );

    $tmp = $theme->finish($theme->parse( 'index_header', 'header' ));
    echo $tmp;  // send the header.thtml

    $retval = $theme->finish( $theme->get_var( 'index_footer' ));

    _js_out();
    _css_out();

    return $retval;
}

/**
* Prints out standard block header
*
* Prints out standard block header but pulling header HTML formatting from
* the database.
*
* Programming Note:  The two functions COM_startBlock and COM_endBlock are used
* to sandwich your block content.  These functions are not used only for blocks
* but anything that uses that format, e.g. Stats page.  They are used like
* COM_siteHeader and COM_siteFooter but for internal page elements.
*
* @param    string  $title      Value to set block title to
* @param    string  $helpfile   Help file, if one exists
* @param    string  $template   HTML template file to use to format the block
* @param    string  $name       ID of block, customarily the name of the block
* @return   string              Formatted HTML containing block header
* @see COM_endBlock
* @see COM_siteHeader
*
*/

function COM_startBlock( $title='', $helpfile='', $template='blockheader.thtml', $name='' )
{
    global $_CONF, $LANG01, $_IMAGE_TYPE;

    $block = new Template( $_CONF['path_layout'] );
    $block->set_file( 'block', $template );

    $block->set_var( 'block_title', $title );
    if ( !empty( $name ) ) {
        $block->set_var( 'block_id', 'id="' . $name . '" ' );
    }

    if ( !empty( $helpfile )) {
        $helpimg = $_CONF['layout_url'] . '/images/button_help.' . $_IMAGE_TYPE;
        $help_content = '<img src="' . $helpimg. '" alt="?"/>';
        $help_attr = array('class'=>'blocktitle');
        if ( !stristr( $helpfile, 'http://' ) && !stristr($helpfile,'https://') ) {
            $help_url = $_CONF['site_url'] . "/help/$helpfile";
        } else {
            $help_url = $helpfile;
        }
        $help = COM_createLink($help_content, $help_url, $help_attr);
        $block->set_var( 'block_help', $help );
    }

    $block->parse( 'startHTML', 'block' );

    return $block->finish( $block->get_var( 'startHTML' ));
}

/**
* Closes out COM_startBlock
*
* @param        string      $template       HTML template file used to format block footer
* @return   string  Formatted HTML to close block
* @see function COM_startBlock
*
*/
function COM_endBlock( $template='blockfooter.thtml' )
{
    global $_CONF;

    $block = new Template( $_CONF['path_layout'] );
    $block->set_file( 'block', $template );

    $block->parse( 'endHTML', 'block' );

    return $block->finish( $block->get_var( 'endHTML' ));
}


/**
* Creates a <option> list from a database list for use in forms
*
* Creates option list form field using given arguments
*
* @param        string      $table      Database Table to get data from
* @param        string      $selection  Comma delimited string of fields to pull The first field is the value of the option and the second is the label to be displayed.  This is used in a SQL statement and can include DISTINCT to start.
* @param        string/array      $selected   Value (from $selection) to set to SELECTED or default
* @param        int         $sortcol    Which field to sort option list by 0 (value) or 1 (label)
* @param        string      $where      Optional WHERE clause to use in the SQL Selection
* @see function COM_checkList
* @return   string  Formated HTML of option values
*
*/

function COM_optionList( $table, $selection, $selected='', $sortcol=1, $where='' )
{
    global $_DB_table_prefix;

    $retval = '';

    $LangTableName = '';
    if ( substr( $table, 0, strlen( $_DB_table_prefix )) == $_DB_table_prefix ) {
        $LangTableName = 'LANG_' . substr( $table, strlen( $_DB_table_prefix ));
    } else {
        $LangTableName = 'LANG_' . $table;
    }

    global $$LangTableName;

    if ( isset( $$LangTableName )) {
        $LangTable = $$LangTableName;
    } else {
        $LangTable = array();
    }

    $tmp = str_replace( 'DISTINCT ', '', $selection );
    $select_set = explode( ',', $tmp );

    $sql = "SELECT $selection FROM $table";
    if ( $where != '' ) {
        $sql .= " WHERE $where";
    }
    $sql .= " ORDER BY {$select_set[$sortcol]}";
    $result = DB_query( $sql );
    $nrows = DB_numRows( $result );

    for( $i = 0; $i < $nrows; $i++ ) {
        $A = DB_fetchArray( $result, true );
        $retval .= '<option value="' . $A[0] . '"';

        if ( is_array( $selected ) AND count( $selected ) > 0 ) {
            foreach( $selected as $selected_item ) {
                if ( $A[0] == $selected_item ) {
                    $retval .= ' selected="selected"';
                }
            }
        } elseif ( !is_array( $selected ) AND $A[0] == $selected ) {
            $retval .= ' selected="selected"';
        }

        $retval .= '>';
        if ( empty( $LangTable[$A[0]] )) {
            $retval .= $A[1];
        } else {
            $retval .= $LangTable[$A[0]];
        }
        $retval .= '</option>' . LB;
    }

    return $retval;
}

/**
* Create and return a dropdown-list of available topics
*
* This is a variation of COM_optionList() from lib-common.php. It will add
* only those topics to the option list which are accessible by the current
* user.
*
* @param        string      $selection  Comma delimited string of fields to pull The first field is the value of the option and the second is the label to be displayed.  This is used in a SQL statement and can include DISTINCT to start.
* @param        string      $selected   Value (from $selection) to set to SELECTED or default
* @param        int         $sortcol    Which field to sort option list by 0 (value) or 1 (label)
* @param        boolean     $ignorelang Whether to return all topics (true) or only the ones for the current language (false)
* @see function COM_optionList
* @return   string  Formated HTML of option values
*
*/
function COM_topicList( $selection, $selected = '', $sortcol = 1, $ignorelang = false, $access = 2 )
{
    global $_TABLES;

    $retval = '';

    $topics = COM_topicArray($selection, $sortcol, $ignorelang, $access);
    if ( is_array($topics) ) {
        foreach ($topics as $tid => $topic) {
            $retval .= '<option value="' . $tid . '"';
            if ($tid == $selected) {
                $retval .= ' selected="selected"';
            }
            $retval .= '>' . $topic;
            if ( isset($tid) ) {
                $retval .= ' (' . $tid . ')';
            }
            $retval .=  '</option>' . LB;
        }
    }

    return $retval;
}

/**
* Return a list of topics in an array
* (derived from COM_topicList - API may change)
*
* @param    string  $selection  Comma delimited string of fields to pull The first field is the value of the option and the second is the label to be displayed.  This is used in a SQL statement and can include DISTINCT to start.
* @param    int     $sortcol    Which field to sort option list by 0 (value) or 1 (label)
* @param    boolean $ignorelang Whether to return all topics (true) or only the ones for the current language (false)
* @return   array               Array of topics
* @see function COM_topicList
*
*/
function COM_topicArray($selection, $sortcol = 0, $ignorelang = false, $access = 2)
{
    global $_TABLES;

    $retval = array();

    $tmp = str_replace('DISTINCT ', '', $selection);
    $select_set = explode(',', $tmp);

    $sql = "SELECT $selection FROM {$_TABLES['topics']}";
    if ($ignorelang) {
        $sql .= COM_getPermSQL('WHERE',0,$access);
    } else {
        $permsql = COM_getPermSQL('WHERE',0,$access);
        if (empty($permsql)) {
            $sql .= COM_getLangSQL('tid');
        } else {
            $sql .= $permsql . COM_getLangSQL('tid', 'AND');
        }
    }
    $sql .=  " ORDER BY $select_set[$sortcol]";

    $result = DB_query($sql);
    $nrows = DB_numRows($result);

    if (count($select_set) > 1) {
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray($result, true);
            $retval[$A[0]] = $A[1];
        }
    } else {
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray($result, true);
            $retval[] = $A[0];
        }
    }

    return $retval;
}

/**
* Creates a <input> checklist from a database list for use in forms
*
* Creates a group of checkbox form fields with given arguments
*
* @param    string  $table      DB Table to pull data from
* @param    string  $selection  Comma delimited list of fields to pull from table
* @param    string  $where      Where clause of SQL statement
* @param    string  $selected   Value to set to CHECKED
* @param    string  $fieldname  Name to use for the checkbox array
* @return   string              HTML with Checkbox code
* @see      COM_optionList
*
*/
function COM_checkList($table, $selection, $where = '', $selected = '', $fieldname = '')
{
    global $_TABLES, $_COM_VERBOSE;

    $sql = "SELECT $selection FROM $table";

    if ( !empty( $where )) {
        $sql .= " WHERE $where";
    }

    $result = DB_query( $sql );
    $nrows = DB_numRows( $result );

    if ( !empty( $selected )) {
        if ( $_COM_VERBOSE ) {
            COM_errorLog( "exploding selected array: $selected in COM_checkList", 1 );
        }

        $S = explode( ' ', $selected );
    } else {
        if ( $_COM_VERBOSE) {
            COM_errorLog( 'selected string was empty COM_checkList', 1 );
        }

        $S = array();
    }
    $retval = '<ul class="checkboxes-list">' . LB;
    for( $i = 0; $i < $nrows; $i++ ) {
        $access = true;
        $A = DB_fetchArray( $result, true );

        if ( $table == $_TABLES['topics'] AND SEC_hasTopicAccess( $A['tid'] ) == 0 ) {
            $access = false;
        }

        if (empty($fieldname)) {
            // Not a good idea, as that will expose our table name and prefix!
            // Make sure you pass a distinct field name!
            $fieldname = $table;
        }

        if ( $access ) {
            $retval .= '<li><input type="checkbox" name="' . $fieldname . '[]" value="' . $A[0] . '"';

            $sizeS = count( $S );
            for( $x = 0; $x < $sizeS; $x++ ) {
                if ( $A[0] == $S[$x] ) {
                    $retval .= ' checked="checked"';
                    break;
                }
            }

            if (( $table == $_TABLES['blocks'] ) && isset( $A[2] ) && ( $A[2] == 'gldefault' )) {
                $retval .= '/><span class="gldefault">' .  $A[1]  . '</span></li>' . LB;
            } else {
                $retval .= '/><span>' .  $A[1]  . '</span></li>' . LB;
            }
        }
    }
    $retval .= '</ul>' . LB;

    return $retval;
}

/**
* Prints out an associative array for debugging
*
* The core of this code has been lifted from phpweblog which is licenced
* under the GPL.  This is not used very much in the code but you can use it
* if you see fit
*
* @param        array       $A      Array to loop through and print values for
* @return   string  Formated HTML List
*
*/

function COM_debug( $A )
{
    if ( !empty( $A )) {
        $retval .= LB . '<pre><p>---- DEBUG ----</p>';

        for( reset( $A ); $k = key( $A ); next( $A )) {
            $retval .= sprintf( "<li>%13s [%s]</li>\n", $k, $A[$k] );
        }

        $retval .= '<p>---------------</p></pre>' . LB;
    }

    return $retval;
}

/**
*
* Checks to see if RDF file needs updating and updates it if so.
* Checks to see if we need to update the RDF as a result
* of an article with a future publish date reaching it's
* publish time and if so updates the RDF file.
*
* NOTE: When called without parameters, this will only check for new entries to
*       include in the feeds. Pass the $updated_XXX parameters when the content
*       of an existing entry has changed.
*
* @param    string  $updated_type   (optional) feed type to update
* @param    string  $updated_topic  (optional) feed topic to update
* @param    string  $updated_id     (optional) feed id to update
*
* @see file lib-syndication.php
*
*/
function COM_rdfUpToDateCheck( $updated_type = '', $updated_topic = '', $updated_id = '' )
{
    global $_CONF, $_TABLES;

    if ( $_CONF['backend'] > 0 ) {
        if ( !empty( $updated_type ) && ( $updated_type != 'article' )) {
            // when a plugin's feed is to be updated, skip glFusion's own feeds
            $sql = "SELECT fid,type,topic,limits,update_info FROM {$_TABLES['syndication']} WHERE (is_enabled = 1) AND (type <> 'article')";
        } else {
            $sql = "SELECT fid,type,topic,limits,update_info FROM {$_TABLES['syndication']} WHERE is_enabled = 1";
        }
        $result = DB_query( $sql );
        $num = DB_numRows( $result );
        for( $i = 0; $i < $num; $i++) {
            $A = DB_fetchArray( $result );

            $is_current = true;
            if ( $A['type'] == 'article' ) {
                $is_current = SYND_feedUpdateCheck( $A['topic'],
                                $A['update_info'], $A['limits'],
                                $updated_topic, $updated_id );
            } else {
                $is_current = PLG_feedUpdateCheck( $A['type'], $A['fid'],
                                $A['topic'], $A['update_info'], $A['limits'],
                                $updated_type, $updated_topic, $updated_id );
            }
            if ( !$is_current ) {
                SYND_updateFeed( $A['fid'] );
            }
        }
    }
}


/**
*
* Logs messages to error.log or the web page or both
*
* Prints a well formatted message to either the web page, error log
* or both.
*
* @param        string      $logentry       Text to log to error log
* @param        int         $actionid       1 = write to log file, 2 = write to screen (default) both
* @see function COM_accessLog
* @return   string  If $actionid = 2 or '' then HTML formatted string (wrapped in block) else nothing
*
*/

function COM_errorLog( $logentry, $actionid = '' )
{
    global $_CONF, $LANG01, $REMOTE_ADDR;

    $retval = '';
    $timestamp = date('d M Y H:i:s');
    if ( !empty( $logentry )) {
        $logentry = str_replace( array( '<?', '?>' ), array( '(@', '@)' ),$logentry );
        $ipaddress = $REMOTE_ADDR;
        if (!isset($_CONF['path_layout']) &&
                (($actionid == 2) || empty($actionid))) {
            $actionid = 1;
        }
        if (!isset($_CONF['path_log']) && ($actionid != 2)) {
            $actionid = 3;
        }
        switch( $actionid ) {
            case 1:
                $logfile = $_CONF['path_log'] . 'error.log';

                if ( !$file = fopen( $logfile, 'a' )) {
                    $retval .= $LANG01[33] . ' ' . $logfile . ' (' . $timestamp . ')<br/>' . LB;
                } else {
                    fputs( $file, "$timestamp - $ipaddress: $logentry \n" );
                }
                break;

           case 2:
                if ( class_exists('Template') ) {
                    $retval .= COM_startBlock( $LANG01[55] . ' ' . $timestamp, '',
                                   COM_getBlockTemplate( '_msg_block', 'header' ))
                            . nl2br( $logentry )
                            . COM_endBlock( COM_getBlockTemplate( '_msg_block',
                                                                  'footer' ));
                } else {
                    $retval .= nl2br($logentry);
                }
                break;

            case 3:
                $retval = nl2br($logentry);
                break;

            default:
                $logfile = $_CONF['path_log'] . 'error.log';

                if ( !$file = fopen( $logfile, 'a' )) {
                    $retval .= $LANG01[33] . ' ' . $logfile . ' (' . $timestamp . ')<br/>' . LB;
                } else {
                    fputs( $file, "$timestamp - $ipaddress: $logentry \n" );
                    if ( class_exists('Template') ) {
                        $retval .= COM_startBlock( $LANG01[34] . ' - ' . $timestamp,
                                       '', COM_getBlockTemplate( '_msg_block',
                                       'header' ))
                                . nl2br( $logentry )
                                . COM_endBlock( COM_getBlockTemplate( '_msg_block',
                                                                      'footer' ));
                    } else {
                        $retval .= nl2br( $logentry );
                    }
                }
                break;
        }
    }

    return $retval;
}

/**
* Logs message to access.log
*
* This will print a message to the glFusion access log
*
* @param        string      $string         Message to write to access log
* @see COM_errorLog
*
*/

function COM_accessLog( $logentry )
{
    global $_CONF, $_USER, $LANG01;

    $retval = '';

    if ( !empty( $logentry )) {
        $logentry = str_replace( array( '<?', '?>' ), array( '(@', '@)' ),
                                 $logentry );

        $timestamp = strftime( '%c' );
        $logfile = $_CONF['path_log'] . 'access.log';

        if ( !$file = fopen( $logfile, 'a' )) {
            return $LANG01[33] . $logfile . ' (' . $timestamp . ')<br/>' . LB;
        }

        if ( isset( $_USER['uid'] )) {
            $byuser = $_USER['uid'] . '@' . $_SERVER['REMOTE_ADDR'];
        } else {
            $byuser = 'anon@' . $_SERVER['REMOTE_ADDR'];
        }

        fputs( $file, "$timestamp ($byuser) - $logentry\n" );
    }

    return $retval;
}

/**
* Shows all available topics
*
* Show the topics in the system the user has access to and prints them in HTML.
* This function is used to show the topics in the topics block.
*
* @param    string    $topic      ID of currently selected topic
* @return   string                HTML formatted topic list
*
*/

function COM_showTopics( $topic='' )
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $_BLOCK_TEMPLATE, $page;

    $langsql = COM_getLangSQL( 'tid' );
    if ( empty( $langsql )) {
        $op = 'WHERE';
    } else {
        $op = 'AND';
    }

    $sql = "SELECT tid,topic,imageurl FROM {$_TABLES['topics']}" . $langsql;
    if ( !COM_isAnonUser() ) {
        $tids = DB_getItem( $_TABLES['userindex'], 'tids',
                            "uid = {$_USER['uid']}" );
        if ( !empty( $tids )) {
            $sql .= " $op (tid NOT IN ('" . str_replace( ' ', "','", $tids )
                 . "'))" . COM_getPermSQL( 'AND' );
        } else {
            $sql .= COM_getPermSQL( $op );
        }
    } else {
        $sql .= COM_getPermSQL( $op );
    }
    if ( $_CONF['sortmethod'] == 'alpha' ) {
        $sql .= ' ORDER BY topic ASC';
    } else {
        $sql .= ' ORDER BY sortnum';
    }
    $result = DB_query( $sql );

    $retval = '';
    $sections = new Template( $_CONF['path_layout'] );
    if ( isset( $_BLOCK_TEMPLATE['topicoption'] )) {
        $templates = explode( ',', $_BLOCK_TEMPLATE['topicoption'] );
        $sections->set_file( array( 'option'  => $templates[0],
                                    'current' => $templates[1] ));
    } else {
        $sections->set_file( array( 'option'   => 'topicoption.thtml',
                                    'inactive' => 'topicoption_off.thtml' ));
    }

    $sections->set_var( 'block_name', str_replace( '_', '-', 'section_block' ));

    if ( $_CONF['hide_home_link'] == 0 ) {
        // Give a link to the homepage here since a lot of people use this for
        // navigating the site

        if ( COM_onFrontpage() ) {
            $sections->set_var( 'option_url', '' );
            $sections->set_var( 'option_label', $LANG01[90] );
            $sections->set_var( 'option_count', '' );
            $sections->set_var( 'topic_image', '' );
            $retval .= $sections->parse( 'item', 'inactive' );
        } else {
            $sections->set_var( 'option_url',
                                $_CONF['site_url'] . '/index.php' );
            $sections->set_var( 'option_label', $LANG01[90] );
            $sections->set_var( 'option_count', '' );
            $sections->set_var( 'topic_image', '' );
            $retval .= $sections->parse( 'item', 'option' );
        }
    }

    if ( $_CONF['showstorycount'] ) {
        $sql = "SELECT tid, COUNT(*) AS count FROM {$_TABLES['stories']} "
             . 'WHERE (draft_flag = 0) AND (date <= NOW()) '
             . COM_getPermSQL( 'AND' )
             . ' GROUP BY tid';
        $rcount = DB_query( $sql );
        while ( $C = DB_fetchArray( $rcount )) {
            $storycount[$C['tid']] = $C['count'];
        }
    }

    if ( $_CONF['showsubmissioncount'] ) {
        $sql = "SELECT tid, COUNT(*) AS count FROM {$_TABLES['storysubmission']} "
             . ' GROUP BY tid';
        $rcount = DB_query( $sql );
        while ( $C = DB_fetchArray( $rcount )) {
            $submissioncount[$C['tid']] = $C['count'];
        }
    }

    while ( $A = DB_fetchArray( $result ) ) {
        $topicname = $A['topic'];
        $sections->set_var( 'option_url', $_CONF['site_url']
                            . '/index.php?topic=' . $A['tid'] );
        $sections->set_var( 'option_label', $topicname );

        $countstring = '';
        if ( $_CONF['showstorycount'] || $_CONF['showsubmissioncount'] ) {
            $countstring .= '(';

            if ( $_CONF['showstorycount'] ) {
                if ( empty( $storycount[$A['tid']] )) {
                    $countstring .= 0;
                } else {
                    $countstring .= COM_numberFormat( $storycount[$A['tid']] );
                }
            }

            if ( $_CONF['showsubmissioncount'] ) {
                if ( $_CONF['showstorycount'] ) {
                    $countstring .= '/';
                }
                if ( empty( $submissioncount[$A['tid']] )) {
                    $countstring .= 0;
                } else {
                    $countstring .= COM_numberFormat( $submissioncount[$A['tid']] );
                }
            }

            $countstring .= ')';
        }
        $sections->set_var( 'option_count', $countstring );

        $topicimage = '';
        if ( !empty( $A['imageurl'] )) {
            $imageurl = COM_getTopicImageUrl( $A['imageurl'] );
            $topicimage = '<img src="' . $imageurl . '" alt="' . $topicname
                        . '" title="' . $topicname . '" border="0"/>';
        }
        $sections->set_var( 'topic_image', $topicimage );

        if (( $A['tid'] == $topic ) && ( $page == 1 ) && $topic != '') {
            $retval .= $sections->parse( 'item', 'inactive' );
        } else {
            $retval .= $sections->parse( 'item', 'option' );
        }
    }

    return $retval;
}

/**
* Shows the user their menu options
*
* This shows the average Joe User their menu options. This is the user block on the left side
*
* @param        string      $help       Help file to show
* @param        string      $title      Title of Menu
* @param        string      $position   Side being shown on 'left', 'right'. Though blank works not likely.
* @see function COM_adminMenu
*
*/

function COM_userMenu( $help='', $title='', $position='' )
{
    global $_TABLES, $_USER, $_CONF, $LANG01, $LANG04, $LANG29, $_BLOCK_TEMPLATE;

    $retval = '';

    if ( !COM_isAnonUser() ) {
        if ( empty( $title )) {
            $title = DB_getItem( $_TABLES['blocks'], 'title',
                                 "name='user_block'" );
        }

        // what's our current URL?
        $thisUrl = COM_getCurrentURL();

        $retval .= COM_startBlock( $title, $help,
                           COM_getBlockTemplate( 'user_block', 'header', $position ), 'user_block' );

        $menuData = getUserMenu();
        $retval .= '<div id="usermenu"><ul class="uk-list uk-list-space">';
        foreach ( $menuData as $item ) {
            $retval .= '<li><a href="'.$item['url'].'">'.$item['label'].'</a></li>';
        }
        $retval .= '</ul></div>';


        $retval .=  COM_endBlock( COM_getBlockTemplate( 'user_block', 'footer' ));
    } else {
        $retval .= COM_startBlock( $LANG01[47], $help,
                           COM_getBlockTemplate( 'login_block', 'header', $position ), 'login_block' );
        $login = new Template( $_CONF['path_layout'] );
        $login->set_file( 'form', 'loginform.thtml' );
        $login->set_var( 'lang_username', $LANG01[21] );
        $login->set_var( 'lang_password', $LANG01[57] );
        $login->set_var( 'lang_forgetpassword', $LANG01[119] );
        $login->set_var( 'lang_login', $LANG01[58] );
        if ( $_CONF['disable_new_user_registration'] == 1 ) {
            $login->set_var( 'lang_signup', '' );
        } else {
            $login->set_var( 'lang_signup', $LANG01[59] );
        }

        // 3rd party remote authentication.
        if ($_CONF['user_login_method']['3rdparty'] && !$_CONF['usersubmission']) {
            $modules = SEC_collectRemoteAuthenticationModules();
            if (count($modules) == 0) {
                $login->set_var('services', '');
            } else {
                if (!$_CONF['user_login_method']['standard'] &&
                        (count($modules) == 1)) {
                    $select = '<input type="hidden" name="service" value="'
                            . $modules[0] . '"/>' . $modules[0];
                } else {
                    // Build select
                    $select = '<select name="service" id="service">';
                    if ($_CONF['user_login_method']['standard']) {
                        $select .= '<option value="">' . $_CONF['site_name']
                                . '</option>';
                    }
                    foreach ($modules as $service) {
                        $select .= '<option value="' . $service . '">'
                                . $service . '</option>';
                    }
                    $select .= '</select>';
                }

                $login->set_file('services', 'blockservices.thtml');
                $login->set_var('lang_service', $LANG04[121]);
                $login->set_var('select_service', $select);
                $login->parse('output', 'services');
                $login->set_var('services',
                                $login->finish($login->get_var('output')));
            }
        } else {
           $login->set_var('services', '');
        }

        // OpenID remote authentication.
        if ($_CONF['user_login_method']['openid'] && ($_CONF['usersubmission'] == 0) && !$_CONF['disable_new_user_registration']) {
            $login->set_file('openid_login', 'loginform_openid.thtml');
            $login->set_var('lang_openid_login', $LANG01[128]);
            $login->set_var('input_field_size', 16);
            $login->set_var('app_url', $_CONF['site_url'] . '/users.php');
            $login->parse('output', 'openid_login');
            $login->set_var('openid_login',
                $login->finish($login->get_var('output')));
        } else {
            $login->set_var('openid_login', '');
        }

        // OAuth remote authentication.
        if ($_CONF['user_login_method']['oauth'] ) {
            $modules = SEC_collectRemoteOAuthModules();
            if (count($modules) == 0) {
                $login->set_var('oauth_login', '');
            } else {
                $html_oauth = '';
                foreach ($modules as $service) {
                    $login->set_file('oauth_login', 'loginform_oauth_block.thtml');
                    $login->set_var('oauth_service', $service);
                    // for sign in image
                    $login->set_var('oauth_sign_in_image', $_CONF['site_url'] . '/images/login-with-' . $service . '.png');
                    $login->set_var('oauth_sign_in_image_style', '');
                    $login->set_var('oauth_service_display',ucwords($service));
                    $login->parse('output', 'oauth_login');
                    $html_oauth .= $login->finish($login->get_var('output'));
                }
                $login->set_var('oauth_login', $html_oauth);
            }
        } else {
            $login->set_var('oauth_login', '');
        }

        $retval .= $login->finish($login->parse('output', 'form'));
        $retval .= COM_endBlock( COM_getBlockTemplate( 'login_block', 'footer', $position ));
    }

    return $retval;
}

/**
* Prints administration menu
*
* This will return the administration menu items that the user has
* sufficient rights to -- Admin Block on right side.
*
* @param        string      $help       Help file to show
* @param        string      $title      Menu Title
* @param        string      $position   Side being shown on 'left', 'right' or blank.
* @see function COM_userMenu
*
*/

function COM_adminMenu( $help = '', $title = '', $position = '' )
{
    global $_TABLES, $_USER, $_CONF;

    $retval = '';
    $link_array = array();

    if ( COM_isAnonUser()) {
        return $retval;
    }

    $thisUrl = COM_getCurrentURL();

    if ($_CONF['hide_adminmenu'] && @strpos($thisUrl, $_CONF['site_admin_url']) === false) {
        return '';
    }


    if ( empty( $title )) {
        $title = DB_getItem( $_TABLES['blocks'], 'title',
                             "name = 'admin_block'" );
    }

    $retval .= COM_startBlock( $title, $help,
                       COM_getBlockTemplate( 'admin_block', 'header', $position ), 'admin_block' );

    $menuData = getAdminMenu();
    $retval .= '<div id="adminmenu"><ul>';
    foreach ( $menuData as $item ) {
        $retval .= '<li><a href="'.$item['url'].'">'.$item['label'].'</a></li>';
    }
    $retval .= '</ul></div>';
    $retval .= COM_endBlock( COM_getBlockTemplate( 'admin_block', 'footer', $position ));
    return $retval;
}

/**
* Redirects user to a given URL
*
* This function does a redirect using a meta refresh. This is (or at least
* used to be) more compatible than using a HTTP Location: header.
*
* @param        string      $url        URL to send user to
* @return   string          HTML meta redirect
*
*/
function COM_refresh($url)
{
    if ( headers_sent() ) {
        return "<html><head><meta http-equiv=\"refresh\" content=\"0; URL=$url\"/></head></html>\n";
    } else {
        header("Location:".htmlspecialchars_decode($url));
        exit;
    }
}

/**
 * DEPRECIATED -- see CMT_userComments in lib-comment.php
 */
function COM_userComments( $sid, $title, $type='article', $order='', $mode='', $pid = 0, $page = 1, $cid = false, $delete_option = false ) {
    global $_CONF;

    require_once $_CONF['path_system'] . 'lib-comment.php';
    return CMT_userComments( $sid, $title, $type, $order, $mode, $pid, $page, $cid, $delete_option );
}

/**
* This censors inappropriate content
*
* This will replace 'bad words' with something more appropriate
*
* @param        string      $Message        String to check
* @see function COM_checkHTML
* @return   string  Edited $Message
*/
function COM_checkWords( $Message )
{
    $filter = new sanitizer();
    return $filter->censor($Message);
}


/**
*  Takes some amount of text and replaces all javascript events on*= with in
*
*  This script takes some amount of text and matches all javascript events, on*= (onBlur= onMouseClick=)
*  and replaces them with in*=
*  Essentially this will cause onBlur to become inBlur, onFocus to be inFocus
*  These are not valid javascript events and the browser will ignore them.
* @param    string  $Message    Text to filter
* @return   string  $Message with javascript filtered
* @see  COM_checkWords
* @see  COM_checkHTML
*
*/

function COM_killJS( $Message )
{
    return( preg_replace( '/(\s)+[oO][nN](\w*) ?=/', '\1in\2=', $Message ));
}

/**
* Handles the part within a [code] ... [/code] section, i.e. escapes all
* special characters.
*
* @param   string  $str  the code section to encode
* @return  string  $str with the special characters encoded
* @see     COM_checkHTML
*
*/
function COM_handleCode( $str )
{
    return $str;
/* -- no longer doing translation -- */
    $search  = array( '&',     '\\',    '<',    '>',    '[',     ']'     );
    $replace = array( '&amp;', '&#92;', '&lt;', '&gt;', '&#91;', '&#93;' );

    $str = str_replace( $search, $replace, $str );

    return( $str );
}

/**
* This function checks html tags.
*
* Checks to see that the HTML tags are on the approved list and
* removes them if not.
*
* @param    string  $str            HTML to check
* @param    string  $permissions    comma-separated list of rights which identify the current user as an "Admin"
* @return   string                  Filtered HTML
*
*/
function COM_checkHTML( $str, $permissions = 'story.edit' )
{
    global $_CONF;

    return COM_filterHTML($str, $permissions);
}

/**
* This function filters the HTML and attempts to clean up invalid markup.
*
* @param    string  $str            HTML to check
* @return   string                  Filtered HTML
*
*/
function COM_filterHTML( $str, $permissions = 'story.edit' )
{
    global $_CONF, $_SYSTEM;

    if ( isset( $_CONF['skip_html_filter_for_root'] ) &&
             ( $_CONF['skip_html_filter_for_root'] == 1 ) &&
            SEC_inGroup( 'Root' )) {

        return $str;
    }
    $default = explode(',',$_CONF['htmlfilter_default']);
    $comment = explode(',',$_CONF['htmlfilter_comment']);
    $story   = explode(',',$_CONF['htmlfilter_story']);
    $root    = explode(',',$_CONF['htmlfilter_root']);

    $configArray = is_array($default) ? $default : array();

    switch ( $permissions ) {
        case 'story.edit' :
            $configArray = array_merge($configArray,$story);
            break;
    }
    if ( SEC_inGroup('Root') ) {
        $configArray = array_merge($configArray,$root);
    }

    $filterArray = array_unique($configArray);
    $allowedElements = implode(',',$filterArray);

    $filter = new sanitizer();
    $filter->setAllowedelements($allowedElements);
    $filter->setPostmode('html');
    return $filter->filterHTML($str);
}


/**
* undo function for htmlspecialchars()
*
* This function translates HTML entities created by htmlspecialchars() back
* into their ASCII equivalents. Also handles the entities for $, {, and }.
*
* @param    string   $string   The string to convert.
* @return   string   The converted string.
*
*/
function COM_undoSpecialChars( $string )
{
    $string = str_replace( '&#36;',  '$', $string );
    $string = str_replace( '&#123;', '{', $string );
    $string = str_replace( '&#125;', '}', $string );
    $string = str_replace( '&gt;',   '>', $string );
    $string = str_replace( '&lt;',   '<', $string );
    $string = str_replace( '&quot;', '"', $string );
    $string = str_replace( '&nbsp;', ' ', $string );
    $string = str_replace( '&amp;',  '&', $string );

    return( $string );
}

/**
* Makes an ID based on current date/time
*
* This function creates a 17 digit sid for stories based on the 14 digit date
* and a 3 digit random number that was seeded with the number of microseconds
* (.000001th of a second) since the last full second.
* NOTE: this is now used for more than just stories!
*
* @return   string  $sid  Story ID
*
*/

function COM_makesid()
{
    $sid = date( 'YmdHis' );
    $sid .= mt_rand( 0, 999 );

    return $sid;
}

/**
* Checks to see if email address is valid.
*
* This function checks to see if an email address is in the correct from.
*
* @param    string    $email   Email address to verify
* @return   boolean            True if valid otherwise false
*
*/
function COM_isEmail( $email )
{
    global $_CONF;

    if (!class_exists('EmailAddressValidator') ) {
        require_once $_CONF['path'] . 'lib/email-address-validation/EmailAddressValidator.php';
    }

    $validator = new EmailAddressValidator;
    return ( $validator->check_email_address( $email ) ? true : false );
}


/**
* Encode a string such that it can be used in an email header
*
* @param    string  $string     the text to be encoded
* @return   string              encoded text
*
*/
function COM_emailEscape( $string )
{
    global $_CONF;

    $charset = COM_getCharset();
    if (( $charset == 'utf-8' ) && ( $string != utf8_decode( $string ))) {
        if ( function_exists( 'iconv_mime_encode' )) {
            $mime_parameters = array( 'input-charset'  => 'utf-8',
                                      'output-charset' => 'utf-8',
                                      // 'Q' encoding is more readable than 'B'
                                      'scheme'         => 'Q'
                                    );
            $string = substr( iconv_mime_encode( '', $string, $mime_parameters ), 2 );
        } else {
            $string = '=?' . $charset . '?B?' . base64_encode( $string ) . '?=';
        }
    } else if ( preg_match( '/[^0-9a-z\-\.,:;\?! ]/i', $string )) {
        $string = '=?' . $charset . '?B?' . base64_encode( $string ) . '?=';
    }

    return $string;
}

/**
* Takes a name and an email address and returns a string that vaguely
* resembles an email address specification conforming to RFC(2)822 ...
*
* @param    string  $name       name, e.g. John Doe
* @param    string  $address    email address only, e.g. john.doe@example.com
* @return   string              formatted email address
*
*/
function COM_formatEmailAddress( $name, $address )
{
    $formatted_name = COM_emailEscape( $name );
    // if the name comes back unchanged, it's not UTF-8, so preg_match is fine
    if (( $formatted_name == $name ) && preg_match( '/[^0-9a-z ]/i', $name )) {
        $formatted_name = str_replace( '"', '\\"', $formatted_name );
    }
    return array($address,$formatted_name);
}

/**
* Send an email.
*
* All emails sent by glFusion are sent through this function now.
*
* @param    string      $to         recipients name and email address
* @param    string      $subject    subject of the email
* @param    string      $message    the text of the email
* @param    string      $from       (optional) sender of the the email
* @param    boolean     $html       (optional) true if to be sent as HTML email
* @param    int         $priority   (optional) add X-Priority header, if > 0
* @param    string      $cc         (optional) other recipients (name + email)
* @param    string      $altBody    (optional) alternative message body (plain text)
* @return   boolean                 true if successful,  otherwise false
*
* @note Please note that using the $cc parameter will expose the email addresses
*       of all recipients. Use with care.
*
*/
function COM_mail( $to, $subject, $message, $from = '', $html = false, $priority = 0, $cc = '', $altBody = '' )
{
    global $_CONF;

    $subject = substr( $subject, 0, strcspn( $subject, "\r\n" ));
    $subject = COM_emailEscape( $subject );

    if ( function_exists( 'CUSTOM_mail' )) {
        return CUSTOM_mail( $to, $subject, $message, $from, $html, $priority, $cc );
    }

    require_once $_CONF['path'] . 'lib/phpmailer/class.phpmailer.php';

    $mail = new PHPMailer();
    $mail->SetLanguage('en',$_CONF['path'].'lib/phpmailer/language/');
    $mail->CharSet = COM_getCharset();
    $mail->XMailer = 'glFusion CMS v' . GVERSION . ' (http://www.glfusion.org)';
    if ($_CONF['mail_backend'] == 'smtp' ) {
        $mail->IsSMTP();
        $mail->Host     = $_CONF['mail_smtp_host'];
        $mail->Port     = $_CONF['mail_smtp_port'];
        if ( $_CONF['mail_smtp_secure'] != 'none' ) {
            $mail->SMTPSecure = $_CONF['mail_smtp_secure'];
        }
        if ( $_CONF['mail_smtp_auth'] ) {
            $mail->SMTPAuth   = true;
            $mail->Username = $_CONF['mail_smtp_username'];
            $mail->Password = $_CONF['mail_smtp_password'];
        }
        $mail->Mailer = "smtp";

    } elseif ($_CONF['mail_backend'] == 'sendmail') {
        $mail->Mailer = "sendmail";
        $mail->Sendmail = $_CONF['mail_sendmail_path'];
    } else {
        $mail->Mailer = "mail";
    }
    $mail->WordWrap = 76;
    $mail->IsHTML($html);
    if ( $html ) {
        $mail->Body = COM_filterHTML($message);
    } else {
        $mail->Body = $message;
    }

    if ( $altBody != '' ) {
        $mail->AltBody = $altBody;
    }

    $mail->Subject = $subject;

    if (is_array($from) && isset($from[0]) && $from[0] != '' ) {
        if ( $_CONF['use_from_site_mail'] == 1 ) {
            $mail->From = $_CONF['site_mail'];
            $mail->AddReplyTo($from[0]);
        } else {
            $mail->From = $from[0];
        }
    } else {
        $mail->From = $_CONF['site_mail'];
    }

    if ( is_array($from) && isset($from[1]) && $from[1] != '' ) {
        $mail->FromName = $from[1];
    } else {
        $mail->FromName = $_CONF['site_name'];
    }
    if ( is_array($to) && isset($to[0]) && $to[0] != '' ) {
        if ( isset($to[1]) && $to[1] != '' ) {
            $mail->AddAddress($to[0],$to[1]);
        } else {
            $mail->AddAddress($to[0]);
        }
    } else {
        // assume old style....
        $mail->AddAddress($to);
    }

    if ( isset($cc[0]) && $cc[0] != '' ) {
        if ( isset($cc[1]) && $cc[1] != '' ) {
            $mail->AddCC($cc[0],$cc[1]);
        } else {
            $mail->AddCC($cc[0]);
        }
    } else {
        // assume old style....
        if ( isset($cc) && $cc != '' ) {
            $mail->AddCC($cc);
        }
    }

    if ( $priority ) {
        $mail->Priority = 1;
    }

    if (!$mail->Send()) {
        COM_errorLog("Email Error: " . $mail->ErrorInfo);
        return false;
    }
    return true;
}

/*
 * A notification system that is a bit kinder to the mail server
 */
function COM_emailNotification( $msgData = array() )
{
    global $_CONF;

    // define the maximum number of emails allowed per bcc
    $maxEmailsPerSend = 10;

    // ensure we have something to send...
    if ( !isset($msgData['htmlmessage']) && !isset($msgData['textmessage']) ) {
        COM_errorLog("COM_emailNotification() - No message data provided");
        return false; // no message defined
    }
    if ( empty($msgData['htmlmessage']) && empty($msgData['textmessage']) ) {
        COM_errorLog("COM_emailNotification() - Empty message data provided");
        return false; // no text in either...
    }
    if ( !isset($msgData['subject']) || empty($msgData['subject']) ) {
        COM_errorLog("COM_emailNotification() - No subject provided");
        return false; // must have a subject
    }

    $queued = 0;

    $subject = substr( $msgData['subject'], 0, strcspn( $msgData['subject'], "\r\n" ));
    $subject = COM_emailEscape( $subject );

    require_once $_CONF['path'] . 'lib/phpmailer/class.phpmailer.php';

    $mail = new PHPMailer();
    $mail->SetLanguage('en',$_CONF['path'].'lib/phpmailer/language/');
    $mail->CharSet = COM_getCharset();
    if ($_CONF['mail_backend'] == 'smtp' ) {
        $mail->IsSMTP();
        $mail->Host     = $_CONF['mail_smtp_host'];
        $mail->Port     = $_CONF['mail_smtp_port'];
        if ( $_CONF['mail_smtp_secure'] != 'none' ) {
            $mail->SMTPSecure = $_CONF['mail_smtp_secure'];
        }
        if ( $_CONF['mail_smtp_auth'] ) {
            $mail->SMTPAuth   = true;
            $mail->Username = $_CONF['mail_smtp_username'];
            $mail->Password = $_CONF['mail_smtp_password'];
        }
        $mail->Mailer = "smtp";

    } elseif ($_CONF['mail_backend'] == 'sendmail') {
        $mail->Mailer = "sendmail";
        $mail->Sendmail = $_CONF['mail_sendmail_path'];
    } else {
        $mail->Mailer = "mail";
    }
    $mail->WordWrap = 76;

    if ( isset($msgData['htmlmessage']) && !empty($msgData['htmlmessage']) ) {
        $mail->IsHTML(true);
        $mail->Body = $msgData['htmlmessage'];
        if ( isset($msgData['textmessage']) && !empty($msgData['textmessage']) ) {
            $mail->AltBody = $msgData['textmessage'];
        }
    } else {
        $mail->IsHTML(false);
        if ( isset($msgData['textmessage']) && !empty($msgData['textmessage']) ) {
            $mail->Body = $msgData['textmessage'];
        }
    }
    $mail->Subject = $subject;

    if ( isset($msgData['embeddedImage']) && is_array($msgData['embeddedImage'])) {
        foreach ($msgData['embeddedImage'] AS $embeddedImage ) {
            $mail->AddEmbeddedImage(
                $embeddedImage['file'],
                $embeddedImage['name'],
                $embeddedImage['filename'],
                $embeddedImage['encoding'],
                $embeddedImage['mime']
            );
        }
    }

    if ( is_array($msgData['from'])) {
        $mail->From = $msgData['from']['email'];
        $mail->FromName = $msgData['from']['name'];
    } else {
        $mail->From = $msgData['from'];
        $mail->FromName = $_CONF['site_name'];
    }

    $queued = 0;
    if ( is_array($msgData['to']) ) {
        foreach ($msgData['to'] AS $to) {
            if ( is_array($to) ) {
                $mail->AddBCC($to['email'],$to['name']);
            } else {
                if ( COM_isEmail($to) ) {
                    $mail->AddBCC($to);
                }
            }

            $queued++;
            if ( $queued >= $maxEmailsPerSend ) {
                if (!$mail->Send()) {
                    COM_errorLog("Email Error: " . $mail->ErrorInfo);
                }
                $queued = 0;
                $mail->ClearBCCs();
            }
        }
    }
    if ( $queued > 0 ) {
        if ( !@$mail->Send() ) {
            COM_errorLog("Email Error: " . $mail->ErrorInfo);
        }
    }
}

/**
* Creates older stuff block
*
* Creates the olderstuff block for display.
* Actually updates the olderstuff record in the blocks table.
* @return   void
*/

function COM_olderStuff()
{
    global $_TABLES, $_CONF;

    $sql = "SELECT sid,tid,title,comments,UNIX_TIMESTAMP(date) AS day FROM {$_TABLES['stories']} WHERE (perm_anon = 2) AND (frontpage = 1) AND (date <= NOW()) AND (draft_flag = 0)" . COM_getTopicSQL( 'AND', 1 ) . " ORDER BY featured DESC, date DESC LIMIT {$_CONF['limitnews']}, {$_CONF['limitnews']}";
    $result = DB_query( $sql );
    $nrows = DB_numRows( $result );

    if ( $nrows > 0 ) {
        $dateonly = $_CONF['dateonly'];
        if ( empty( $dateonly )) {
            $dateonly = 'd-M'; // fallback: day - abbrev. month name
        }

        $day = 'noday';
        $string = '';
        $dt = new Date();
        for( $i = 0; $i < $nrows; $i++ ) {
            $A = DB_fetchArray( $result );
            $dt->setTimestamp($A['day']);
            $daycheck = $dt->format("z",true);
            if ( $day != $daycheck ) {
                if ( $day != 'noday' ) {
                    $daylist = COM_makeList($oldnews, 'list-older-stories');
                    $daylist = str_replace(array("\015", "\012"), '', $daylist);
                    $string .= $daylist; // . '<br/>';
                }
                $day2 = $dt->format($_CONF['dateonly'], true);
                $string .= '<h3>' . $dt->format('l',true) . ' <small>' . $day2
                        . '</small></h3>' . LB;
                $oldnews = array();
                $day = $daycheck;
            }
            $oldnews_url = COM_buildUrl( $_CONF['site_url'] . '/article.php?story='. $A['sid'] );

            $oldnews[] = COM_createLink(COM_truncate($A['title'],$_CONF['title_trim_length'] ,'...'),
                         $oldnews_url,array('title' => htmlspecialchars($A['title'],ENT_COMPAT,COM_getEncodingt())))
                .' (' . COM_numberFormat( $A['comments'] ) . ')';
        }

        if ( !empty( $oldnews )) {
            $daylist = COM_makeList( $oldnews, 'list-older-stories' );
            $daylist = str_replace(array("\015", "\012"), '', $daylist);
            $string .= $daylist;
            $string = DB_escapeString( $string );

            DB_query( "UPDATE {$_TABLES['blocks']} SET content = '$string' WHERE name = 'older_stories'" );
        }
    }
}

/**
* Shows a single glFusion block
*
* This shows a single block and is typically called from
* COM_showBlocks OR from plugin code
*
* @param        string      $name       Logical name of block (not same as title) -- 'user_block', 'admin_block', 'section_block', 'whats_new_block'.
* @param        string      $help       Help file location
* @param        string      $title      Title shown in block header
* @param        string      $position   Side, 'left', 'right' or empty.
* @see function COM_showBlocks
* @return   string  HTML Formated block
*
*/

function COM_showBlock( $name, $help='', $title='', $position='' )
{
    global $_CONF, $topic, $_TABLES, $_USER;

    $retval = '';

    if ( !isset( $_USER['noboxes'] )) {
        if ( !COM_isAnonUser() ) {
            $_USER['noboxes'] = DB_getItem( $_TABLES['userindex'], 'noboxes',
                                            "uid = {$_USER['uid']}" );
        } else {
            $_USER['noboxes'] = 0;
        }
    }

    switch( $name ) {
        case 'user_block':
            $retval .= COM_userMenu( $help,$title, $position );
            break;

        case 'admin_block':
            $retval .= COM_adminMenu( $help,$title, $position );
            break;

        case 'section_block':
            $retval .= COM_startBlock( $title, $help,
                               COM_getBlockTemplate( $name, 'header', $position ), $name )
                . COM_showTopics( $topic )
                . COM_endBlock( COM_getBlockTemplate( $name, 'footer', $position ));
            break;

        case 'whats_new_block':
            if ( !$_USER['noboxes'] ) {
                $retval .= COM_whatsNewBlock( $help, $title, $position );
            }
            break;
    }

    return $retval;
}


/**
* Shows glFusion blocks
*
* Returns HTML for blocks on a given side and, potentially, for
* a given topic. Currently only used by static pages.
*
* @param        string      $side       Side to get blocks for (right or left for now)
* @param        string      $topic      Only get blocks for this topic
* @param        string      $name       Block name (not used)
* @see function COM_showBlock
* @return   string  HTML Formated blocks
*
*/

function COM_showBlocks( $side, $topic='', $name='all' )
{
    global $_CONF, $_TABLES, $_USER, $LANG21, $topic, $page;

    $retval = '';

    // Get user preferences on blocks
    if ( !isset( $_USER['noboxes'] ) || !isset( $_USER['boxes'] )) {
        if ( !COM_isAnonUser() ) {
            $result = DB_query( "SELECT boxes,noboxes FROM {$_TABLES['userindex']} "
                               ."WHERE uid = {$_USER['uid']}" );
            list($_USER['boxes'], $_USER['noboxes']) = DB_fetchArray( $result );
        } else {
            $_USER['boxes'] = '';
            $_USER['noboxes'] = 0;
        }
    }

    $blocksql = "SELECT *,UNIX_TIMESTAMP(rdfupdated) AS date ";

    $commonsql = "FROM {$_TABLES['blocks']} WHERE is_enabled = 1";

    if ( $side == 'left' ) {
        $commonsql .= " AND onleft = 1";
    } else {
        $commonsql .= " AND onleft = 0";
    }

    if ( !empty( $topic )) {
        $commonsql .= " AND (tid = '".DB_escapeString($topic)."' OR tid = 'all' OR tid = 'allnhp' )";
    } else {
        if ( COM_onFrontpage() ) {
            $commonsql .= " AND (tid = 'homeonly' OR tid = 'all')";
        } else {
            $commonsql .= " AND (tid = 'all' OR tid = 'allnhp')";
        }
    }

    if ( !empty( $_USER['boxes'] )) {
        $BOXES = str_replace( ' ', ',', trim($_USER['boxes']) );

        $commonsql .= " AND (bid NOT IN ($BOXES) OR bid = '-1')";
    }

    $commonsql .= ' ORDER BY blockorder,title ASC';

    $blocksql .= $commonsql;
    $result = DB_query( $blocksql );
    $nrows = DB_numRows( $result );

    // convert result set to an array of associated arrays
    $blocks = array();
    for( $i = 0; $i < $nrows; $i++ ) {
        $blocks[] = DB_fetchArray( $result );
    }

    // Check and see if any plugins have blocks to show
    $pluginBlocks = PLG_getBlocks( $side, $topic, $name );
    $blocks = array_merge( $blocks, $pluginBlocks );

    // sort the resulting array by block order
    $column = 'blockorder';
    $sortedBlocks = $blocks;
    $num_sortedBlocks = count( $sortedBlocks );
    for( $i = 0; $i < $num_sortedBlocks - 1; $i++ ) {
        for( $j = 0; $j < $num_sortedBlocks - 1 - $i; $j++ ) {
            if ( $sortedBlocks[$j][$column] > $sortedBlocks[$j+1][$column] ) {
                $tmp = $sortedBlocks[$j];
                $sortedBlocks[$j] = $sortedBlocks[$j + 1];
                $sortedBlocks[$j + 1] = $tmp;
            }
        }
    }
    $blocks = $sortedBlocks;

    // Loop though resulting sorted array and pass associative arrays
    // to COM_formatBlock
    foreach( $blocks as $A ) {
        if ( $A['type'] == 'dynamic' or SEC_hasAccess( $A['owner_id'], $A['group_id'], $A['perm_owner'], $A['perm_group'], $A['perm_members'], $A['perm_anon'] ) > 0 ) {
           $retval .= COM_formatBlock( $A, $_USER['noboxes'] );
        }
    }

    return $retval;
}

/**
* Formats a glFusion block
*
* This shows a single block and is typically called from
* COM_showBlocks OR from plugin code
*
* @param        array     $A          Block Record
* @param        bool      $noboxes    Set to true if userpref is no blocks
* @return       string    HTML Formated block
*
*/
function COM_formatBlock( $A, $noboxes = false )
{
    global $_CONF, $_TABLES, $_USER, $LANG21;

    $retval = '';

    $lang = COM_getLanguageId();
    if (!empty($lang)) {

        $blocksql = "SELECT *,UNIX_TIMESTAMP(rdfupdated) AS date ";
        $commonsql = "FROM {$_TABLES['blocks']} WHERE name = '" . $A['name'] . '_' . $lang . "'";
        $blocksql .= $commonsql;

        $result = DB_query( $blocksql );

        if (DB_numRows($result) == 1) {
            // overwrite with data for language-specific block
            $A = DB_fetchArray($result);
        }
    }

    if ( array_key_exists( 'onleft', $A ) ) {
        if ( $A['onleft'] == 1 ) {
            $position = 'left';
        } else {
            $position = 'right';
        }
    } else {
        $position = '';
    }

    if ( $A['type'] == 'portal' ) {
        if ( COM_rdfCheck( $A['bid'], $A['rdfurl'], $A['date'], $A['rdflimit'] )) {
            $A['content'] = DB_getItem( $_TABLES['blocks'], 'content',
                                        "bid = '{$A['bid']}'");
        }
    }

    if ( $A['type'] == 'gldefault' ) {
        $retval .= COM_showBlock( $A['name'], $A['help'], $A['title'], $position );
    }

    if ( $A['type'] == 'phpblock' && !$noboxes ) {
        if ( !( $A['name'] == 'whosonline_block' AND DB_getItem( $_TABLES['blocks'], 'is_enabled', "name='whosonline_block'" ) == 0 )) {
            $function = $A['phpblockfn'];
            $matches = array();
            if (preg_match('/^(phpblock_\w*)\\((.*)\\)$/', $function, $matches) == 1) {
                $function = $matches[1];
                $args = $matches[2];
            }
            $blkheader = COM_startBlock( $A['title'], $A['help'],
                    COM_getBlockTemplate( $A['name'], 'header', $position ), $A['name'] );
            $blkfooter = COM_endBlock( COM_getBlockTemplate( $A['name'],
                    'footer', $position ));

            if ( function_exists( $function )) {
               if (isset($args)) {
                    $fretval = $function($A, $args);
               } else {
                    $fretval = $function();
               }
               if ( !empty( $fretval )) {
                    $retval .= $blkheader;
                    $retval .= $fretval;
                    $retval .= $blkfooter;
               }
            } else {
                // show error message
                $retval .= $blkheader;
                $retval .= sprintf( $LANG21[31], $function );
                $retval .= $blkfooter;
            }
        }
    }

    if ( !empty( $A['content'] ) && ( trim( $A['content'] ) != '' ) && !$noboxes ) {
        $blockcontent =  $A['content'] ;

        // Hack: If the block content starts with a '<' assume it
        // contains HTML and do not call nl2br() which would only add
        // unwanted <br> tags.

        if ( substr( $blockcontent, 0, 1 ) != '<' ) {
            $blockcontent = nl2br( $blockcontent );
        }

        // autotags are only(!) allowed in normal blocks
        if (( $A['allow_autotags'] == 1 ) && ( $A['type'] == 'normal' )) {
            $blockcontent = PLG_replaceTags( $blockcontent,'glfusion','block' );
        }
        $blockcontent = str_replace( array( '<?', '?>' ), '', $blockcontent );

        $retval .= COM_startBlock( $A['title'], $A['help'],
                       COM_getBlockTemplate( $A['name'], 'header', $position ), $A['name'] )
                . $blockcontent . LB
                . COM_endBlock( COM_getBlockTemplate( $A['name'], 'footer', $position ));
    }

    return $retval;
}


/**
* Checks to see if it's time to import and RDF/RSS block again
*
* Updates RDF/RSS block if needed
*
* @param    string  $bid            Block ID
* @param    string  $rdfurl         URL to get headlines from
* @param    string  $date           Last time the headlines were imported
* @param    string  $maxheadlines   max. number of headlines to import
* @return   void
* @see function COM_rdfImport
*
*/
function COM_rdfCheck( $bid, $rdfurl, $date, $maxheadlines = 0 )
{
    $retval = false;
    $nextupdate = $date + 3600;

    if ( $nextupdate < time() ) {
        COM_rdfImport( $bid, $rdfurl, $maxheadlines );
        $retval = true;
    }

    return $retval;
}

/**
* Syndication import function. Imports headline data to a portal block.
*
* Rewritten December 19th 2004 by Michael Jervis (mike@*censored*ingbrit.com). Now
* utilises a Factory Pattern to open a URL and automaticaly retreive a feed
* object populated with feed data. Then import it into the portal block.
*
* @param    string  $bid            Block ID
* @param    string  $rdfurl         URL to get content from
* @param    int     $maxheadlines   Maximum number of headlines to display
* @return   void
* @see function COM_rdfCheck
*
*/
function COM_rdfImport($bid, $rdfurl, $maxheadlines = 0)
{
    global $_CONF, $_TABLES, $LANG21;

    require_once $_CONF['path'].'/lib/simplepie/autoloader.php';

    $result = DB_query("SELECT rdf_last_modified, rdf_etag FROM {$_TABLES['blocks']} WHERE bid = ".(int)$bid);
    list($last_modified, $etag) = DB_fetchArray($result);

    // Load the actual feed handlers:
    $feed = new SimplePie();
    $feed->set_useragent('glFusion/' . GVERSION.' '.SIMPLEPIE_USERAGENT);
    $feed->set_feed_url($rdfurl);
    $feed->set_cache_location($_CONF['path'].'/data/layout_cache');
    $rc = $feed->init();
    if ( $rc == true ) {
        $feed->handle_content_type();
        /* We have located a reader, and populated it with the information from
         * the syndication file. Now we will sort out our display, and update
         * the block.
         */
        if ($maxheadlines == 0) {
            if (!empty($_CONF['syndication_max_headlines'])) {
                $maxheadlines = $_CONF['syndication_max_headlines'];
            }
        }

        if ( $maxheadlines == 0 ) {
            $number_of_items = $feed->get_item_quantity();
        } else{
            $number_of_items = $feed->get_item_quantity($maxheadlines);
        }
        $etag = '';
        $update = date('Y-m-d H:i:s');
        $last_modified = $update;
        $last_modified = DB_escapeString($last_modified);

        if (empty($last_modified)) {
            DB_query("UPDATE {$_TABLES['blocks']} SET rdfupdated = '$update', rdf_last_modified = NULL, rdf_etag = NULL WHERE bid = ".(int) $bid);
        } else {
            DB_query("UPDATE {$_TABLES['blocks']} SET rdfupdated = '$update', rdf_last_modified = '$last_modified', rdf_etag = '$etag' WHERE bid = ".(int) $bid);
        }

        for ( $i = 0; $i < $number_of_items; $i++ ) {
            $item = $feed->get_item($i);
            $title = $item->get_title();
            if (empty($title)) {
                $title = $LANG21[61];
            }
            $link      = $item->get_permalink();
            $enclosure = $item->get_enclosure();

            if ($link != '') {
                $content = COM_createLink($title, $link, $attr = array('target' => '_blank'));
            } elseif ($enclosure != '') {
                $content = COM_createLink($title, $enclosure, $attr = array('target' => '_blank'));
            } else {
                $content = $title;
            }
            $articles[] = $content;
        }

        // build a list
        $content = COM_makeList($articles, 'list-feed');
        $content = str_replace(array("\015", "\012"), '', $content);

        if (strlen($content) > 65000) {
            $content = $LANG21[68];
        }

        // Standard theme based function to put it in the block
        $result = DB_change($_TABLES['blocks'], 'content',
                            DB_escapeString($content), 'bid', (int) $bid);
    } else {
        $err = $feed->error();
        COM_errorLog($err);
        $content = DB_escapeString($err);
        DB_query("UPDATE {$_TABLES['blocks']} SET content = '$content', rdf_last_modified = NULL, rdf_etag = NULL WHERE bid = ".(int) $bid);
    }
}


/**
* Returns what HTML is allowed in content
*
* Returns what HTML tags the system allows to be used inside content.
* You can modify this by changing $_CONF['user_html'] in the configuration
* (for admins, see also $_CONF['admin_html']).
*
* @param    string  $permissions    comma-separated list of rights which identify the current user as an "Admin"
* @param    boolean $list_only      true = return only the list of HTML tags
* @param    string  $namespace      Optional Namespace or plugin name collecting tag info
* @param    string  $operation      Optional Operation being performed
* @return   string  HTML <span> enclosed string
* @see function COM_checkHTML
*/
function COM_allowedHTML( $permissions = 'story.edit', $list_only = false, $namespace='',$operation='' )
{
    global $_CONF;

    $filter = sanitizer::getInstance();
    $allowedHTML = $filter->getAllowedHTML();
    return $allowedHTML;
}

function COM_allowedAutotags( $permissions = 'story.edit', $list_only = false, $namespace='glfusion',$operation='' )
{
    global $_CONF,$LANG01;

    $retval = '';
    $allow_page_break = false;
    if ( $_CONF['allow_page_breaks'] && $operation == 'story')
        $allow_page_break = true;

    if ( !$list_only ) {
        $retval .= '<span class="warningsmall"><strong>' . $LANG01[31] . '</strong> ';
    }
    if ( $allow_page_break ) {
        $retval .= '[page_break],&nbsp;';
    }
    $retval .= '[code]';
    // list autolink tags
    $autotags = PLG_collectTags($namespace,$operation);
    foreach( $autotags as $tag => $module ) {
        $retval .= ', [' . $tag . ':]';
    }
    $retval .= '</span>';
    return $retval;
}

/**
* Return the password for the given username
*
* Fetches a password for the given user
*
* @param    string  $loginname  username to get password for
* @return   string              Password or ''
*
*/

function COM_getPassword( $loginname )
{
    global $_TABLES, $LANG01;

    $result = DB_query( "SELECT passwd FROM {$_TABLES['users']} WHERE username='".DB_escapeString($loginname)."'" );
    $tmp = DB_error();
    $nrows = DB_numRows( $result );

    if (( $tmp == 0 ) && ( $nrows == 1 )) {
        $U = DB_fetchArray( $result );
        return $U['passwd'];
    } else {
        $tmp = $LANG01[32] . ": '" . $loginname . "'";
        COM_errorLog( $tmp, 1 );
    }

    return '';
}


/**
* Return the username or fullname for the passed member id (uid)
*
* Allows the siteAdmin to determine if loginname (username) or fullname
* should be displayed.
*
* @param    int     $uid        site member id
* @param    string  $username   Username, if this is set no lookup is done.
* @param    string  $fullname   Users full name.
* @param    string  $remoteusername  Username on remote service
* @param    string  $remoteservice   Remote login service.
* @return   string  Username, fullname or username@Service
*
*/
function COM_getDisplayName( $uid = '', $username='', $fullname='', $remoteusername='', $remoteservice='' )
{
    global $_CONF, $_TABLES, $_USER;

    static $cache = array();

    if ($uid == '') {
        if (COM_isAnonUser()) {
            $uid = 1;
        } else {
            $uid = $_USER['uid'];
        }
    } else {
        $uid = (int) $uid;
    }

    if (isset($cache[$uid]) )  {
        return $cache[$uid];
    }

    if (empty($username)) {
        $query = DB_query("SELECT username, fullname, remoteusername, remoteservice FROM {$_TABLES['users']} WHERE uid=".(int)$uid);
        list($username, $fullname, $remoteusername, $remoteservice) = DB_fetchArray($query);
    }
    $ret = $username;
    if (!empty($fullname) && ($_CONF['show_fullname'] == 1)) {
        $ret = $fullname;
    } else if (($_CONF['user_login_method']['3rdparty'] || $_CONF['user_login_method']['openid']) && !empty($remoteusername)) {
        if (!empty($username)) {
            $remoteusername = $username;
        }

        if ($_CONF['show_servicename']) {
            $ret = "$remoteusername@$remoteservice";
        } else {
            $ret = $remoteusername;
        }
    }

    $cache[$uid] = $ret;
    return $ret;
}


/**
* Adds a hit to the system
*
* This function is called in the footer of every page and is used to
* track the number of hits to the glFusion system.  This information is
* shown on stats.php
*
*/

function COM_hit()
{
    global $_TABLES;

    DB_change($_TABLES['vars'], 'value', 'value + 1', 'name', 'totalhits', '', true);
}

/**
* This will email new stories in the topics that the user is interested in
*
* In account information the user can specify which topics for which they
* will receive any new article for in a daily digest.
*
* @return   void
*/

function COM_emailUserTopics()
{
    global $_CONF, $_USER, $_VARS, $_TABLES, $LANG04, $LANG08, $LANG24;

    if ($_CONF['emailstories'] == 0) {
        return;
    }

    $storytext = '';
    $storytext_text = '';

    USES_lib_story();

    $subject = strip_tags( $_CONF['site_name'] . $LANG08[30] . strftime( '%Y-%m-%d', time() ));

    $authors = array();

    // Get users who want stories emailed to them
    $usersql = "SELECT username,email,etids,{$_TABLES['users']}.uid AS uuid, status "
        . "FROM {$_TABLES['users']}, {$_TABLES['userindex']} "
        . "WHERE {$_TABLES['users']}.uid > 1 AND {$_TABLES['userindex']}.uid = {$_TABLES['users']}.uid AND status=".USER_ACCOUNT_ACTIVE." AND (etids <> '-' OR etids IS NULL) ORDER BY {$_TABLES['users']}.uid";

    $users = DB_query( $usersql );
    $nrows = DB_numRows( $users );

    if ( !isset($_VARS['lastemailedstories']) ) {
        $_VARS['lastemailedstories'] = 0;
    }
    $lastrun = $_VARS['lastemailedstories'];

    // For each user, pull the stories they want and email it to them
    for( $x = 0; $x < $nrows; $x++ ) {
        $U = DB_fetchArray( $users );
        $storysql  = "SELECT sid,uid,date AS day,title,introtext,bodytext";
        $commonsql = " FROM {$_TABLES['stories']} WHERE draft_flag = 0 AND date <= NOW() AND date >= '{$lastrun}'";
        $topicsql  = "SELECT tid FROM {$_TABLES['topics']}"
                        . COM_getPermSQL( 'WHERE', $U['uuid'] );
        $tresult   = DB_query( $topicsql );
        $trows     = DB_numRows( $tresult );

        if ( $trows == 0 ) {
            // this user doesn't seem to have access to any topics ...
            continue;
        }

        $TIDS = array();
        for( $i = 0; $i < $trows; $i++ ) {
            $T = DB_fetchArray( $tresult );
            $TIDS[] = $T['tid'];
        }

        if ( !empty( $U['etids'] )) {
            $ETIDS = explode( ' ', $U['etids'] );
            $TIDS = array_intersect( $TIDS, $ETIDS );
        }

        if ( sizeof( $TIDS ) > 0) {
            $commonsql .= " AND (tid IN ('" . implode( "','", $TIDS ) . "'))";
        }

        $commonsql .= COM_getPermSQL( 'AND', $U['uuid'] );
        $commonsql .= ' ORDER BY featured DESC, date DESC';

        $storysql .= $commonsql;

        $stories = DB_query( $storysql );
        $nsrows  = DB_numRows( $stories );

        if ( $nsrows == 0 ) {
            // If no new stories where pulled for this user, continue with next
            continue;
        }

        $T = new Template($_CONF['path_layout']);
        $T->set_file(array('message'     => 'digest.thtml',
                           'story'       => 'digest_story.thtml'));

        $TT = new Template($_CONF['path_layout']);
        $TT->set_file(array('message'     => 'digest_text.thtml',
                           'story'        => 'digest_story_text.thtml'));

        $T->set_var('week_date',strftime( $_CONF['shortdate'], time() ));
        $TT->set_var('week_date',strftime( $_CONF['shortdate'], time() ));

        $T->set_var('site_name',$_CONF['site_name']);
        $TT->set_var('site_name',$_CONF['site_name']);

        $T->set_var('remove_msg',sprintf($LANG08[36],$_CONF['site_name'],$_CONF['site_url']));
        $TT->set_var('remove_msg',sprintf($LANG08[37],$_CONF['site_name'],$_CONF['site_url']));

        for( $y = 0; $y < $nsrows; $y++ ) {
            // Loop through stories building the requested email message
            $S = DB_fetchArray( $stories );

            $story = new Story();
            $args = array ( 'sid' => $S['sid'],'mode' => 'view');
            $output = STORY_LOADED_OK;
            $result = PLG_invokeService('story', 'get', $args, $output, $svc_msg);
            if ($result == PLG_RET_OK) {
                /* loadFromArray cannot be used, since it overwrites the timestamp */
                reset($story->_dbFields);

                while (list($fieldname,$save) = each($story->_dbFields)) {
                    $varname = '_' . $fieldname;

                    if (array_key_exists($fieldname, $output)) {
                        $story->{$varname} = $output[$fieldname];
                    }
                }
               $story->_username = $output['username'];
               $story->_fullname = $output['fullname'];
            }
            $story_url = COM_buildUrl( $_CONF['site_url'] . '/article.php?story=' . $S['sid'] );
            $title     = COM_undoSpecialChars(  $S['title'] );
            if ( $_CONF['contributedbyline'] == 1 ) {
                if ( empty( $authors[$S['uid']] )) {
                    $storyauthor = COM_getDisplayName ($S['uid']);
                    $authors[$S['uid']] = $storyauthor;
                } else {
                    $storyauthor = $authors[$S['uid']];
                }
            }

            $dt = new Date($S['day'], $_USER['tzid']);
            $story_date = $dt->format($_CONF['date'], true);

            if ( $_CONF['emailstorieslength'] > 0 ) {
                $storytext      = COM_undoSpecialChars( strip_tags( PLG_replaceTags( $S['introtext'],'glfusion','story')));
                $storytext_text = COM_undoSpecialChars( strip_tags( PLG_replaceTags( $S['introtext'],'glfusion','story')));

                if ( $_CONF['emailstorieslength'] > 1 ) {
                    $storytext = COM_truncate( $storytext,$_CONF['emailstorieslength'], '...' );
                    $storytext_text = COM_truncate( $storytext_text,$_CONF['emailstorieslength'], '...' );
                }
            } else {
                $storytext = '';
                $storytext_text = '';
            }
            $T->set_var ('story_introtext',$storytext);
            $TT->set_var ('story_introtext',$storytext_text);

            $T->set_var(array(
                'story_url'     => $story_url,
                'story_title'   => $title,
                'story_author'  => $storyauthor,
                'story_date'    => $story_date,
                'story_text'    => $storytext,
            ));
            $T->parse('digest_stories', 'story', true);

            $TT->set_var(array(
                'story_url'     => $story_url,
                'story_title'   => $title,
                'story_author'  => $storyauthor,
                'story_date'    => $story_date,
                'story_text'    => $storytext_text,
            ));
            $TT->parse('digest_stories', 'story', true);
        }

        $T->parse('digest', 'message', true);
        $TT->parse('digest', 'message', true);

        $mailtext = $T->finish($T->get_var('digest'));
        $mailtext_text = $TT->finish($TT->get_var('digest'));

        $mailfrom = $_CONF['noreply_mail'];
        $mailtext .= LB . LB . $LANG04[159];
        $mailtext_text .= LB . LB . $LANG04[159];

        $to = array();
        $from = array();
        $from = COM_formatEmailAddress('',$mailfrom);
        $to   = COM_formatEmailAddress( $U['username'],$U['email'] );
        COM_mail ($to, $subject, $mailtext, $from,1,0,'',$mailtext_text);

    }
    DB_query( "UPDATE {$_TABLES['vars']} SET value = NOW() WHERE name = 'lastemailedstories'" );
}


/**
* Shows any new information in a block
*
* Return the HTML that shows any new stories, comments, etc
*
* @param    string  $help     Help file for block
* @param    string  $title    Title used in block header
* @param    string  $position Position in which block is being rendered 'left', 'right' or blank (for centre)
* @return   string  Return the HTML that shows any new stories, comments, etc
*
*/

function COM_whatsNewBlock( $help = '', $title = '', $position = '' )
{
    global $_CONF, $_TABLES, $_USER, $_PLUGINS, $LANG01, $LANG_WHATSNEW, $page, $newstories;

    if ( !isset($_CONF['whatsnew_cache_time']) ) {
        $_CONF['whatsnew_cache_time'] = 3600;
    }

    $cacheInstance = 'whatsnew__' . CACHE_security_hash() . '__' . $_USER['theme'];
    $retval = CACHE_check_instance($cacheInstance, 0);
    if ( $retval ) {
        $lu = CACHE_get_instance_update($cacheInstance, 0);
        $now = time();
        if (( $now - $lu ) < $_CONF['whatsnew_cache_time'] ) {
            return $retval;
        }
    }

    $T = new Template($_CONF['path_layout'].'blocks');
    $T->set_file('block', 'whatsnew.thtml');

    $items_found = 0;

    $header = COM_startBlock( $title, $help,
                       COM_getBlockTemplate( 'whats_new_block', 'header', $position ), 'whats_new_block' );

    $T->set_var('block_start',$header);


    $topicsql = '';
    if (( $_CONF['hidenewstories'] == 0 ) || ( $_CONF['hidenewcomments'] == 0 )
            || ( $_CONF['trackback_enabled']
            && ( $_CONF['hidenewtrackbacks'] == 0 ))) {
        $topicsql = COM_getTopicSql ('AND', 0, $_TABLES['stories']);
    }

    if ( $_CONF['hidenewstories'] == 0 ) {
        $archsql = '';
        $archivetid = DB_getItem( $_TABLES['topics'], 'tid', "archive_flag=1" );
        if ( !empty( $archivetid )) {
            $archsql = " AND (tid <> '" . DB_escapeString( $archivetid ) . "')";
        }

        // Find the newest stories
        $sql = "SELECT * FROM {$_TABLES['stories']} WHERE (date >= (date_sub(NOW(), INTERVAL {$_CONF['newstoriesinterval']} SECOND))) AND (date <= NOW()) AND (draft_flag = 0)" . $archsql . COM_getPermSQL( 'AND' ) . $topicsql . COM_getLangSQL( 'sid', 'AND' ) . ' ORDER BY date DESC';

        $result = DB_query( $sql );
        $nrows  = DB_numRows( $result );

        if ( empty( $title )) {
            $title = DB_getItem( $_TABLES['blocks'], 'title', "name='whats_new_block'" );
        }

        $T->set_block('block', 'section', 'sectionblock');

        if ( $nrows > 0 ) {
            // Any late breaking news stories?
            $T->set_var('section_title',$LANG01[99]);
            $T->set_var('interval',COM_formatTimeString( $LANG_WHATSNEW['new_last'],$_CONF['newcommentsinterval'] ));

            $newstory = array();

            $T->set_block('block','datarow','datablock');

            while ($A=DB_fetchArray($result)) {
                $title = COM_undoSpecialChars( $A['title'] );
                $title = str_replace('&nbsp;',' ',$title);
                $titletouse = COM_truncate( $title, $_CONF['title_trim_length'],'...' );
                $attr = array('title' => htmlspecialchars($title,ENT_COMPAT,COM_getEncodingt()));
                $url = COM_buildUrl($_CONF['site_url'] . '/article.php?story=' . $A['sid']);
                $storyitem = COM_createLink($titletouse,$url,$attr);
                $newstory[] = $storyitem;

                $T->set_var('data_item',$storyitem);
                $T->parse('datablock', 'datarow',true);
                $items_found++;
            }
            $T->parse('sectionblock','section',true);
        }
    }
    $T->unset_var('datablock');

    if ( $_CONF['hidenewcomments'] == 0 ) {
        // Go get the newest comments
        $commentHeader = 0;
        $newcomments = array();
        $commentrow  = array();

        // get story whats new

        $stwhere = '';

        if ( !COM_isAnonUser() ) {
            $stwhere .= "({$_TABLES['stories']}.owner_id IS NOT NULL AND {$_TABLES['stories']}.perm_owner IS NOT NULL) OR ";
            $stwhere .= "({$_TABLES['stories']}.group_id IS NOT NULL AND {$_TABLES['stories']}.perm_group IS NOT NULL) OR ";
            $stwhere .= "({$_TABLES['stories']}.perm_members IS NOT NULL)";
        } else {
            $stwhere .= "({$_TABLES['stories']}.perm_anon IS NOT NULL)";
        }
        $sql = "SELECT DISTINCT COUNT(*) AS dups, type, {$_TABLES['stories']}.title, {$_TABLES['stories']}.sid, UNIX_TIMESTAMP(max({$_TABLES['comments']}.date)) AS lastdate FROM {$_TABLES['comments']} LEFT JOIN {$_TABLES['stories']} ON (({$_TABLES['stories']}.sid = {$_TABLES['comments']}.sid)" . COM_getPermSQL( 'AND', 0, 2, $_TABLES['stories'] ) . " AND ({$_TABLES['stories']}.draft_flag = 0) AND ({$_TABLES['stories']}.commentcode >= 0)" . $topicsql . COM_getLangSQL( 'sid', 'AND', $_TABLES['stories'] ) . ") WHERE ({$_TABLES['comments']}.date >= (DATE_SUB(NOW(), INTERVAL {$_CONF['newcommentsinterval']} SECOND))) AND ((({$stwhere}))) GROUP BY {$_TABLES['comments']}.sid,type, {$_TABLES['stories']}.title, {$_TABLES['stories']}.title, {$_TABLES['stories']}.sid ORDER BY 5 DESC LIMIT 15";
        $result = DB_query( $sql );
        $nrows = DB_numRows( $result );

        if ( $nrows > 0 ) {
            $T->set_var('section_title',$LANG01[83]);
            $T->set_var('interval',COM_formatTimeString( $LANG_WHATSNEW['new_last'],$_CONF['newcommentsinterval'] ));

            $commentHeader = 1;
            for ($x = 0; $x < $nrows; $x++ ) {
                $A = DB_fetchArray($result);
                $A['url'] = COM_buildUrl( $_CONF['site_url']
                        . '/article.php?story=' . $A['sid'] ) . '#comments';

                $commentrow[] = $A;
            }
        }

        $pluginComments = PLG_getWhatsNewComment();
        $commentrow = array_merge($pluginComments,$commentrow);

        usort($commentrow,'_commentsort');

        $nrows = count($commentrow);

        if ( $nrows > 0 ) {
            if ( $commentHeader == 0 ) {
                $commentHeader = 1;
                $T->set_var('section_title',$LANG01[83]);
                $T->set_var('interval',COM_formatTimeString( $LANG_WHATSNEW['new_last'],$_CONF['newcommentsinterval'] ));
            }

            $newcomments = array();
            for( $x = 0; $x < $nrows; $x++ ) {
                $titletouse = '';
                $url = $commentrow[$x]['url'];
                $title = COM_undoSpecialChars( $commentrow[$x]['title'] );
                $title = str_replace('&nbsp;',' ',$title);
                $titletouse = COM_truncate( $title, $_CONF['title_trim_length'],
                                            '...' );
                $attr = array('title' => htmlspecialchars($title,ENT_COMPAT,COM_getEncodingt()));

                if ( $commentrow[$x]['dups'] > 1 ) {
                    $titletouse .= ' [+' . $commentrow[$x]['dups'] . ']';
                }

                $newcomments[] = COM_createLink($titletouse, $url, $attr);
            }
            $T->set_block('block','datarow','datablock');
            foreach ($newcomments as $comment) {
                $T->set_var('data_item',$comment);
                $T->parse('datablock', 'datarow',true);
                $items_found++;
            }
            $T->parse('sectionblock','section',true);
        }
    }
    $T->unset_var('datablock');

    if ( $_CONF['trackback_enabled'] && ( $_CONF['hidenewtrackbacks'] == 0 )) {

        $sql = "SELECT DISTINCT COUNT(*) AS count,{$_TABLES['stories']}.title,t.sid,max(t.date) AS lastdate FROM {$_TABLES['trackback']} AS t,{$_TABLES['stories']} WHERE (t.type = 'article') AND (t.sid = {$_TABLES['stories']}.sid) AND (t.date >= (DATE_SUB(NOW(), INTERVAL {$_CONF['newtrackbackinterval']} SECOND)))" . COM_getPermSQL( 'AND', 0, 2, $_TABLES['stories'] ) . " AND ({$_TABLES['stories']}.draft_flag = 0) AND ({$_TABLES['stories']}.trackbackcode = 0)" . $topicsql . COM_getLangSQL( 'sid', 'AND', $_TABLES['stories'] ) . " GROUP BY t.sid, {$_TABLES['stories']}.title ORDER BY lastdate DESC LIMIT 15";
        $result = DB_query( $sql );

        $nrows = DB_numRows( $result );
        if ( $nrows > 0 ) {
            $T->set_var('section_title',$LANG01[114]);
            $T->set_var('interval',COM_formatTimeString( $LANG_WHATSNEW['new_last'],$_CONF['newtrackbackinterval'] ));

            $newcomments = array();
            $T->set_block('block','datarow','datablock');
            for( $i = 0; $i < $nrows; $i++ ) {
                $titletouse = '';
                $A = DB_fetchArray( $result );

                $url = COM_buildUrl( $_CONF['site_url']
                    . '/article.php?story=' . $A['sid'] ) . '#trackback';

                $title = COM_undoSpecialChars( $A['title'] );
                $title = str_replace('&nbsp;',' ',$title);
                $titletouse = COM_truncate( $title, $_CONF['title_trim_length'],'...' );

                $attr = array('title' => htmlspecialchars($title,ENT_COMPAT,COM_getEncodingt()));
                if ( $A['count'] > 1 ) {
                    $titletouse .= ' [+' . $A['count'] . ']';
                }
                $trackback = COM_createLink($titletouse, $url, $attr);
                $newcomments[] = $trackback;
                $T->set_var('data_item',$trackback);
                $T->parse('datablock', 'datarow',true);
                $items_found++;
            }
            $T->parse('sectionblock','section',true);
        }
    }
    $T->unset_var('datablock');

    if ( $_CONF['hidenewplugins'] == 0 ) {
        list( $headlines, $smallheadlines, $content ) = PLG_getWhatsNew();
        $plugins = count( $headlines );
        if ( $plugins > 0 ) {
            for( $i = 0; $i < $plugins; $i++ ) {
                $T->set_var('section_title',$headlines[$i]);
                $T->set_var('interval',$smallheadlines[$i]);
                $T->set_block('block','datarow','datablock');
                if ( is_array( $content[$i] )) {
                    foreach($content[$i] as $item ){
                        $T->set_var('data_item',$item);
                        $T->parse('datablock', 'datarow',true);
                        $items_found++;
                    }
                } else {
                    $T->set_var('data_item',$content[$i]);
                    $T->parse('datablock', 'datarow',true);
                    $items_found++;
                }
                $T->parse('sectionblock','section',true);
                $T->unset_var('datablock');
                $T->unset_var('interval');
                $T->unset_var('section_title');
            }
        }
    }

    if ( $items_found == 0 ) {
        $T->set_var('no_items_found',$LANG01['no_new_items']);
    } else {
        $T->set_var('no_items_found','');
    }
    $T->set_var('block_end',COM_endBlock( COM_getBlockTemplate( 'whats_new_block', 'footer', $position )));

    $T->parse ('output', 'block');
    $final = $T->finish($T->get_var('output'));
    CACHE_create_instance($cacheInstance, $final, 0);

    return $final;
}


/**
* Creates the string that indicates the timespan in which new items were found
*
* @param    string  $time_string    template string
* @param    int     $time           number of seconds in which results are found
* @param    string  $type           type (translated string) of new item
* @param    int     $amount         amount of things that have been found.
*/
function COM_formatTimeString( $time_string, $time, $type = '', $amount = 0 )
{
    global $LANG_WHATSNEW;

    $retval = $time_string;

    // This is the amount you have to divide the previous by to get the
    // different time intervals: hour, day, week, months
    $time_divider = array( 60, 60, 24, 7, 4 );

    // These are the respective strings to the numbers above. They have to match
    // the strings in $LANG_WHATSNEW (i.e. these are the keys for the array -
    // the actual text strings are taken from the language file).
    $time_description  = array( 'minute',  'hour',  'day',  'week',  'month'  );
    $times_description = array( 'minutes', 'hours', 'days', 'weeks', 'months' );

    $time_dividers = count( $time_divider );
    for( $s = 0; $s < $time_dividers; $s++ ) {
        $time = $time / $time_divider[$s];
        if ( $time < $time_divider[$s + 1] ) {
            if ( $time == 1 ) {
                if ( $s == 0 ) {
                    $time_str = $time_description[$s];
                } else { // go back to the previous unit, e.g. 1 day -> 24 hours
                    $time_str = $times_description[$s - 1];
                    $time *= $time_divider[$s];
                }
            } else {
                $time_str = $times_description[$s];
            }
            $fields = array( '%n', '%i', '%t', '%s' );
            $values = array( $amount, $type, $time, $LANG_WHATSNEW[$time_str] );
            $retval = str_replace( $fields, $values, $retval );
            break;
        }
    }

    return $retval;
}


/**
* Sets session variable for a message to display on next page load
*
* @param    int     $msg            number of the message to set
*/
function COM_setMessage( $msg = 0 )
{
    SESS_setVar('glfusion.infomessage',$msg);
}

function COM_setMsg( $msg, $type='info' )
{
    $msgArray = array('msg' => $msg, 'type' => $type);
    SESS_setVar('glfusion.infoblock', serialize($msgArray));
}

/**
* Returns message number if set
*
* @return    int     $msg           message number to display or 0
*/
function COM_getMessage()
{
    $msg = 0;
    if ( isset($_POST['msg']) ) {
        $msg = COM_applyFilter($_POST['msg'],true);
    } elseif ( isset($_GET['msg']) ) {
        $msg = COM_applyFilter($_GET['msg'],true);
    } elseif ( SESS_isSet('glfusion.infomessage') ) {
        $msg = COM_applyFilter(SESS_getVar('glfusion.infomessage'),true);
        SESS_unSet('glfusion.infomessage');
    }
    return $msg;
}


/**
* Displays a message text in a "System Message" block
*
* @param    string  $message    Message text; may contain HTML
* @param    string  $title      (optional) alternative block title
* @param    string    $boolean    (optional) whether message should be persistent
* @param    string  $type       (optional) type of message to display
* @return   string              HTML block with message
*
*/
function COM_showMessageText($message, $title = '', $persist = false, $type='')
{
    global $_CONF, $_USER, $MESSAGE, $_IMAGE_TYPE;

    $retval = '';

    $dt = new Date('now',$_USER['tzid']);

    $id = rand();

    if ( $type == '' ) {
        $type = 'info';
    }

    switch ($type) {
        case 'success' :
            $class = 'alert-success';
            break;
        case 'error' :
            $class = 'alert-error';
            break;
        case 'info' :
            $class = 'alert-info';
            break;
        case 'warning' :
            $class = 'alert-block';
            break;
        default :
            $type  = 'info';
            $class = 'alert-info';
            break;
    }

    if (!empty($message)) {

        $timestamp = $dt->format($_CONF['daytime'],true);

        $T = new Template( $_CONF['path_layout'] );
        $T->set_file('message','sysmessage.thtml');
        $T->set_var(array(
                    'title'         => $title,
                    'timestamp'     => $timestamp,
                    'message'       => $message,
                    'icon_url'      => $_CONF['layout_url'].'/images/sysmessage.'.$_IMAGE_TYPE,
                    'class'         => $class,
                    'block_title'   => $title . '-' . $timestamp,
                    'fade'          => (($persist) ? '' : true),
                    'type'          => $type,
                    'persist'       => $persist,
                    'id'            => $id,
        ));
        $T->parse( 'final', 'message' );
        $retval = $T->finish( $T->get_var( 'final' ));
    }
    return $retval;
}


/**
* Displays a message on the webpage
*
* Pulls $msg off the URL string and gets the corresponding message and returns
* it for display on the calling page
*
* @param    int     $msg        ID of message to show
* @param    string  $plugin     Optional Name of plugin to lookup plugin defined message
* @param    string  $title      (optional) alternative block title
* @param    string    $boolean    (optional) whether message should be persistent
* @return   string              HTML block with message
*/
function COM_showMessage($msg, $plugin = '', $title = '', $persist = false,$type='')
{
    global $MESSAGE;

    $retval = '';

    if ($msg > 0) {
        if (!empty($plugin)) {
            $var = 'PLG_' . $plugin . '_MESSAGE' . $msg;
            global $$var;
            if (isset($$var)) {
                $message = $$var;
            } else {
                $message = $MESSAGE[$msg];
            }
        } else {
            $message = $MESSAGE[$msg];
        }

        if (!empty($message)) {
            $retval .= COM_showMessageText($message, $title, $persist,$type);
        }
    }

    return $retval;
}

/**
* Displays a message, as defined by URL parameters
*
* Helper function to display a message, but only if $_GET parameter 'msg' is defined.
* optional parameters 'plugin', 'title' and 'persist' are also parsed
* Only for GET requests, but that's what glFusion uses everywhere anyway.
*
* @return   string  HTML block with message
*
*/
function COM_showMessageFromParameter()
{
    $retval = '';

    if (isset($_GET['msg'])) {
        $msg = COM_applyFilter($_GET['msg'], true);
        if ($msg > 0) {
            $plugin = (isset($_GET['plugin'])) ? COM_applyFilter($_GET['plugin']) : '';
            $title = (isset($_GET['title'])) ? COM_applyFilter($_GET['title']) : '';
            $persist = (isset($_GET['persist'])) ? true : false;
            $retval .= COM_showMessage($msg, $plugin, $title, $persist,'info');
        }
    }

    return $retval;
}


/**
* Prints Google(tm)-like paging navigation
*
* @param        string      $base_url       base url to use for all generated links
* @param        int         $curpage        current page we are on
* @param        int         $num_pages      Total number of pages
* @param        string      $page_str       page-variable name AND '='
* @param        boolean     $do_rewrite     if true, url-rewriting is respected
* @param        string      $msg            to be displayed with the navigation
* @param        string      $open_ended     replace next/last links with this
* @return       string   HTML formatted widget
*/
function COM_printPageNavigationOLD( $base_url, $curpage, $num_pages,
                                  $page_str='page=', $do_rewrite=false, $msg='',
                                  $open_ended = '',$suffix='')
{
    global $_CONF, $LANG05;

    $retval = '';

    $output = outputHandler::getInstance();

    if ( $num_pages < 2 ) {
        return $retval;
    }

    $T = new Template($_CONF['path_layout']);
    $T->set_file('pagination','pagination.thtml');

    if ( !$do_rewrite ) {
        $hasargs = strstr( $base_url, '?' );
        if ( $hasargs ) {
            $sep = '&amp;';
        } else {
            $sep = '?';
        }
    } else {
        $sep = '/';
        $page_str = '';
    }

    if ( $curpage > 1 ) {
        $T->set_var('first',true);
        $T->set_var('first_link',$base_url . $sep . $page_str . '1' . $suffix);
        $pg = $sep . $page_str . ( $curpage - 1 );
        $T->set_var('prev',true);
        $T->set_var('prev_link',$base_url . $pg . $suffix);
        $output->addLink('prev', urldecode($base_url . $pg . $suffix));
    } else {
        $T->unset_var('first');
        $T->unset_var('first_link');
        $T->unset_var('prev');
        $T->unset_var('prev_link');
    }
    $T->set_block('pagination', 'datarow', 'datavar');

    for( $pgcount = ( $curpage - 10 ); ( $pgcount <= ( $curpage + 9 )) AND ( $pgcount <= $num_pages ); $pgcount++ ) {
        if ( $pgcount <= 0 ) {
            $pgcount = 1;
        }

        if ( $pgcount == $curpage ) {
            $T->set_var('active',true);
            $T->set_var('page_str',$curpage);
        } else {
            $T->unset_var('active');
            $T->set_var('page_str',$pgcount);
            $pg = $sep . $page_str . $pgcount;
            $T->set_var('page_link',$base_url . $pg . $suffix);
        }
        $T->parse('datavar', 'datarow',true);
    }
    if ( !empty( $open_ended )) {
        $T->set_var('open_ended',true);
    } else if ( $curpage == $num_pages ) {
        $T->unset_var('open_ended');
        $T->unset_var('next');
        $T->unset_var('last');
        $T->unset_var('next_link');
        $T->unset_var('last_link');
    } else {
        $T->set_var('next',true);
        $T->set_var('next_link',$base_url . $sep.$page_str . ($curpage + 1) . $suffix);
        $T->set_var('last',true);
        $T->set_var('last_link',$base_url . $sep.$page_str . $num_pages . $suffix);
        $output->addLink('next', urldecode($base_url . $sep. $page_str . ($curpage + 1) . $suffix));
    }
    if (!empty($msg) ) {
        $T->set_var('msg',$msg);
    }

    $retval = $T->finish ($T->parse('output','pagination'));
    return $retval;
}

function COM_printPageNavigation( $base_url, $curpage, $num_pages,
                                  $page_str='page=', $do_rewrite=false, $msg='',
                                  $open_ended = '',$suffix='')
{
    global $_CONF, $LANG05;

    $retval = '';

    $output = outputHandler::getInstance();

    if ( $num_pages < 2 ) {
        return $retval;
    }
    $T = new Template($_CONF['path_layout']);
    $T->set_file('pagination','pagination.thtml');

    if ( !$do_rewrite ) {
        $hasargs = strstr( $base_url, '?' );
        if ( $hasargs ) {
            $sep = '&amp;';
        } else {
            $sep = '?';
        }
    } else {
        $sep = '/';
        $page_str = '';
    }

    if ( $curpage > 1 ) {
        $T->set_var('first',true);
        $T->set_var('first_link',$base_url . $sep . $page_str . '1' . $suffix);
        $pg = $sep . $page_str . ( $curpage - 1 );
        $T->set_var('prev',true);
        $T->set_var('prev_link',$base_url . $pg . $suffix);
        $output->addLink('prev', urldecode($base_url . $pg . $suffix));
    } else {
        $T->unset_var('first');
        $T->unset_var('first_link');
        $T->unset_var('prev');
        $T->unset_var('prev_link');
    }

    $T->set_block('pagination', 'datarow', 'datavar');

    if ( $curpage == 1 ) {
        $T->set_var('page_str','1');
        $T->set_var('page_link','#');
        $T->set_var('disabled',true);
        $T->set_var('active',true);
        $T->parse('datavar', 'datarow',true);
        $T->unset_var('active');
        $T->unset_var('disabled');
    } else {
        $T->set_var('page_str','1');
        $pg = $sep . $page_str . 1;
        $T->set_var('page_link',$base_url . $pg . $suffix);
        $T->parse('datavar', 'datarow',true);
    }

    if ( $num_pages > 5 ) {
        $start_cnt = min(max(1, $curpage - 4), $num_pages - 5);
        $end_cnt = max(min($num_pages,$curpage + 2), 6);
        if ( $start_cnt > 1 ) {
            $T->set_var('page_str','...');
            $T->set_var('page_link','#');
            $T->set_var('disabled',true);
            $T->parse('datavar', 'datarow',true);
        }
        $T->unset_var('disabled');

        for ( $i = ($start_cnt + 1); $i < $end_cnt; $i++ ) {
            if ( $i == $curpage ) {
                $T->set_var('page_str',$i);
                $T->set_var('page_link','#');
                $T->set_var('disabled',true);
                $T->set_var('active',true);
            } else {
                $T->set_var('page_str',$i);
                $pg = $sep . $page_str . $i;
                $T->set_var('page_link',$base_url . $pg . $suffix);
            }
            $T->parse('datavar', 'datarow',true);
            $T->unset_var('active');
            $T->unset_var('disabled');
        }
        if ( $end_cnt < $num_pages ) {
            $T->set_var('page_str','...');
            $T->set_var('page_link','#');
            $T->set_var('disabled',true);
            $T->parse('datavar', 'datarow',true);
        }
        $T->unset_var('disabled');
        if ( $curpage == $num_pages ) {
            $T->set_var('page_str',$num_pages);
            $T->set_var('page_link','#');
            $T->set_var('active',true);
        } else {
            $T->set_var('page_str',$num_pages);
            $pg = $sep . $page_str . $num_pages;
            $T->set_var('page_link',$base_url . $pg . $suffix);
        }
        $T->parse('datavar', 'datarow',true);
    } else {
        for( $pgcount = ( $curpage - 10 ); ( $pgcount <= ( $curpage + 9 )) AND ( $pgcount <= $num_pages ); $pgcount++ ) {
            if ( $pgcount <= 0 ) {
                $pgcount = 2;
            }
            if ( $pgcount == $curpage ) {
                $T->set_var('active',true);
                $T->set_var('page_str',$curpage);
            } else {
                $T->unset_var('active');
                $T->set_var('page_str',$pgcount);
                $pg = $sep . $page_str . $pgcount;
                $T->set_var('page_link',$base_url . $pg . $suffix);
            }
            $T->parse('datavar', 'datarow',true);
        }
    }
    if ( !empty( $open_ended )) {
        $T->set_var('open_ended',true);
    } else if ( $curpage == $num_pages ) {
        $T->unset_var('open_ended');
        $T->unset_var('next');
        $T->unset_var('last');
        $T->unset_var('next_link');
        $T->unset_var('last_link');
    } else {
        $T->set_var('next',true);
        $T->set_var('next_link',$base_url . $sep.$page_str . ($curpage + 1) . $suffix);
        $T->set_var('last',true);
        $T->set_var('last_link',$base_url . $sep.$page_str . $num_pages . $suffix);
        $output->addLink('next', urldecode($base_url . $sep. $page_str . ($curpage + 1) . $suffix));
    }
    if (!empty($msg) ) {
        $T->set_var('msg',$msg);
    }

    $retval = $T->finish ($T->parse('output','pagination'));
    return $retval;
}


/**
* Returns formatted date/time for user
*
* This function COM_takes a date in either unixtimestamp or in english and
* formats it to the users preference.  If the user didn't specify a format
* the format in the config file is used.  This returns an array where array[0]
* is the formatted date and array[1] is the unixtimestamp
*
* @param        string      $date       date to format, otherwise we format current date/time
* @return   array   array[0] is the formatted date and array[1] is the unixtimestamp.
*/

function COM_getUserDateTimeFormat( $date='now' )
{
    global $_TABLES, $_USER, $_CONF, $_SYSTEM;

    $dtObject = new Date($date,$_USER['tzid']);

    // Get display format for time

    if ( !COM_isAnonUser() ) {
        if ( empty( $_USER['format'] )) {
            $dateformat = $_CONF['date'];
        } else {
            $dateformat = $_USER['format'];
        }
    } else {
        $dateformat = $_CONF['date'];
    }

    if ( empty( $date ) || $date == 'now') {
        // Date is empty, get current date/time
        $stamp = time();
    } else if ( is_numeric( $date )) {
        // This is a timestamp
        $stamp = $date;
    } else {
        // This is a string representation of a date/time
        $stamp = $dtObject->toUnix();
    }
    $date = $dtObject->format($dateformat,true);

    return array( $date, $stamp );
}

/**
* Returns user-defined cookie timeout
*
* In account preferences users can specify when their long-term cookie expires.
* This function returns that value.
*
* @return   int Cookie time out value in seconds
*/

function COM_getUserCookieTimeout()
{
    global $_TABLES, $_USER, $_CONF;

    if ( empty( $_USER )) {
        return;
    }

    $timeoutvalue = DB_getItem( $_TABLES['users'], 'cookietimeout', "uid = {$_USER['uid']}" );

    if ( empty( $timeoutvalue )) {
        $timeoutvalue = 0;
    }

    return $timeoutvalue;
}



/**
* Gets the <option> values for calendar months
*
* @param        string      $selected       Selected month
* @see function COM_getDayFormOptions
* @see function COM_getYearFormOptions
* @see function COM_getHourFormOptions
* @see function COM_getMinuteFormOptions
* @return   string  HTML Months as option values
*/

function COM_getMonthFormOptions( $selected = '' )
{
    global $LANG_MONTH;

    $month_options = '';

    for( $i = 1; $i <= 12; $i++ ) {
        $mval = $i;
        $month_options .= '<option value="' . $mval . '"';

        if ( $i == $selected ) {
            $month_options .= ' selected="selected"';
        }

        $month_options .= '>' . $LANG_MONTH[$mval] . '</option>';
    }

    return $month_options;
}

/**
* Gets the <option> values for calendar days
*
* @param        string      $selected       Selected day
* @see function COM_getMonthFormOptions
* @see function COM_getYearFormOptions
* @see function COM_getHourFormOptions
* @see function COM_getMinuteFormOptions
* @return string HTML days as option values
*/

function COM_getDayFormOptions( $selected = '' )
{
    $day_options = '';

    for( $i = 1; $i <= 31; $i++ ) {
        if ( $i < 10 ) {
            $dval = '0' . $i;
        } else {
            $dval = $i;
        }

        $day_options .= '<option value="' . $dval . '"';

        if ( $i == $selected ) {
            $day_options .= ' selected="selected"';
        }

        $day_options .= '>' . $dval . '</option>';
    }

    return $day_options;
}

/**
* Gets the <option> values for calendar years
*
* Returns Option list Containing 5 years starting with current
* unless @selected is < current year then starts with @selected
*
* @param        string      $selected     Selected year
* @param        int         $startoffset  Optional (can be +/-) Used to determine start year for range of years
* @param        int         $endoffset    Optional (can be +/-) Used to determine end year for range of years
* @see function COM_getMonthFormOptions
* @see function COM_getDayFormOptions
* @see function COM_getHourFormOptions
* @see function COM_getMinuteFormOptions
* @return string  HTML years as option values
*/

function COM_getYearFormOptions($selected = '', $startoffset = -1, $endoffset = 5)
{
    $year_options = '';
    $start_year  = date('Y') + $startoffset;
    $cur_year    = date('Y', time());
    $finish_year = $cur_year + $endoffset;

    if (!empty($selected)) {
        if ($selected < $cur_year) {
            $start_year = $selected;
        }
    }

    for ($i = $start_year; $i <= $finish_year; $i++) {
        $year_options .= '<option value="' . $i . '"';

        if ($i == $selected) {
            $year_options .= ' selected="selected"';
        }

        $year_options .= '>' . $i . '</option>';
    }

    return $year_options;
}

/**
* Gets the <option> values for clock hours
*
* @param    string  $selected   Selected hour
* @param    int     $mode       12 or 24 hour mode
* @return   string              HTML string of options
* @see function COM_getMonthFormOptions
* @see function COM_getDayFormOptions
* @see function COM_getYearFormOptions
* @see function COM_getMinuteFormOptions
*/

function COM_getHourFormOptions( $selected = '', $mode = 12 )
{
    $hour_options = '';

    if ( $mode == 12 ) {
        for( $i = 1; $i <= 11; $i++ ) {
            if ( $i < 10 ) {
                $hval = '0' . $i;
            } else {
                $hval = $i;
            }

            if ( $i == 1 ) {
                $hour_options .= '<option value="12"';

                if ( $selected == 12 ) {
                    $hour_options .= ' selected="selected"';
                }

                $hour_options .= '>12</option>';
            }

            $hour_options .= '<option value="' . $hval . '"';

            if ( $selected == $i ) {
                $hour_options .= ' selected="selected"';
            }

            $hour_options .= '>' . $i . '</option>';
        }
    } else { // if ( $mode == 24 )
        for( $i = 0; $i < 24; $i++ ) {
            if ( $i < 10 ) {
                $hval = '0' . $i;
            } else {
                $hval = $i;
            }

            $hour_options .= '<option value="' . $hval . '"';

            if ( $selected == $i ) {
                $hour_options .= ' selected="selected"';
            }

            $hour_options .= '>' . $i . '</option>';
        }
    }

    return $hour_options;
}

/**
* Gets the <option> values for clock minutes
*
* @param    string      $selected   Selected minutes
* @param    int         $step       number of minutes between options, e.g. 15
* @see function COM_getMonthFormOptions
* @see function COM_getDayFormOptions
* @see function COM_getHourFormOptions
* @see function COM_getYearFormOptions
* @return string  HTML of option minutes
*/

function COM_getMinuteFormOptions( $selected = '', $step = 1 )
{
    $minute_options = '';

    if (( $step < 1 ) || ( $step > 30 )) {
        $step = 1;
    }

    for( $i = 0; $i <= 59; $i += $step ) {
        if ( $i < 10 ) {
            $mval = '0' . $i;
        } else {
            $mval = $i;
        }

        $minute_options .= '<option value="' . $mval . '"';

        if ( $selected == $i ) {
            $minute_options .= ' selected="selected"';
        }

        $minute_options .= '>' . $mval . '</option>';
    }

    return $minute_options;
}

/**
* for backward compatibility only
* - this function should always have been called COM_getMinuteFormOptions
*
*/
function COM_getMinuteOptions( $selected = '', $step = 1 )
{
    return COM_getMinuteFormOptions( $selected, $step );
}

/**
* Create an am/pm selector dropdown menu
*
* @param    string  $name       name of the <select>
* @param    string  $selected   preselection: 'am' or 'pm'
* @return   string  HTML for the dropdown; empty string in 24 hour mode
*
*/
function COM_getAmPmFormSelection( $name, $selected = '' )
{
    global $_CONF;

    $retval = '';

    if ( isset( $_CONF['hour_mode'] ) && ( $_CONF['hour_mode'] == 24 )) {
        $retval = '';
    } else {
        if ( empty( $selected )) {
            $selected = date( 'a' );
        }

        $retval .= '<select name="' . $name . '">' . LB;
        $retval .= '<option value="am"';
        if ( $selected == 'am' ) {
            $retval .= ' selected="selected"';
        }
        $retval .= '>am</option>' . LB . '<option value="pm"';
        if ( $selected == 'pm' ) {
            $retval .= ' selected="selected"';
        }
        $retval .= '>pm</option>' . LB . '</select>' . LB;
    }

    return $retval;
}

/**
* Creates an HTML unordered list from the given array.
* It formats one list item per array element, using the list.thtml
* and listitem.thtml templates.
*
* @param    array   $listofitems    Items to list out
* @param    string  $classname      optional CSS class name for the list
* @return   string                  HTML unordered list of array items
*/
function COM_makeList($listofitems, $classname = '')
{
    global $_CONF;

    $list = new Template($_CONF['path_layout']);
    $list->set_file(array('list'     => 'list.thtml',
                          'listitem' => 'listitem.thtml'));
    if (empty($classname)) {
        $list->set_var('list_class',      '');
        $list->set_var('list_class_name', '');
    } else {
        $list->set_var('list_class',      'class="' . $classname . '"');
        $list->set_var('list_class_name', $classname);
    }

    if (is_array($listofitems)) {
        foreach ($listofitems as $oneitem) {
            $list->set_var('list_item', $oneitem);
            $list->parse('list_items', 'listitem', true);
        }
    }

    $list->parse('newlist', 'list', true);

    return $list->finish($list->get_var('newlist'));
}

/**
* Check if speed limit applies
*
* @param    string  $type       type of speed limit, e.g. 'submit', 'comment'
* @param    int     $max        max number of allowed tries within speed limit
* @param    string  $property   IP address or other identifiable property
* @return   int                 0: does not apply, else: seconds since last post
*/
function COM_checkSpeedlimit($type = 'submit', $max = 1, $property = '')
{
    global $_TABLES;

    $last = 0;

    if (empty($property)) {
        $property = $_SERVER['REMOTE_ADDR'];
    }
    $property = DB_escapeString($property);

    $res  = DB_query("SELECT date FROM {$_TABLES['speedlimit']} WHERE (type = '".DB_escapeString($type)."') AND (ipaddress = '$property') ORDER BY date ASC");

    // If the number of allowed tries has not been reached,
    // return 0 (didn't hit limit)
    if (DB_numRows($res) < $max) {
        return $last;
    }

    list($date) = DB_fetchArray($res);

    if (!empty($date)) {
        $last = time() - $date;
        if ($last == 0) {
            // just in case someone manages to submit something in < 1 sec.
            $last = 1;
        }
    }

    return $last;
}

/**
* Store post info for speed limit
*
* @param    string  $type       type of speed limit, e.g. 'submit', 'comment'
* @param    string  $property   IP address or other identifiable property
*
*/
function COM_updateSpeedlimit($type = 'submit', $property = '')
{
    global $_TABLES;

    if (empty($property)) {
        $property = $_SERVER['REMOTE_ADDR'];
    }
    $property = DB_escapeString($property);
    $type     = DB_escapeString($type);

    DB_save($_TABLES['speedlimit'], 'ipaddress,date,type',
            "'$property',UNIX_TIMESTAMP(),'$type'");
}

/**
* Clear out expired speed limits, i.e. entries older than 'x' seconds
*
* @param speedlimit   int      number of seconds
* @param type         string   type of speed limit, e.g. 'submit', 'comment'
*
*/
function COM_clearSpeedlimit($speedlimit = 60, $type = '')
{
    global $_TABLES;

    $sql = "DELETE FROM {$_TABLES['speedlimit']} WHERE ";
    if (!empty($type)) {
        $sql .= "(type = '".DB_escapeString($type)."') AND ";
    }
    $sql .= "(date < UNIX_TIMESTAMP() - $speedlimit)";
    DB_query($sql);
}

/**
* Reset the speedlimit
*
* @param    string  $type       type of speed limit to reset, e.g. 'submit'
* @param    string  $property   IP address or other identifiable property
*
*/
function COM_resetSpeedlimit($type = 'submit', $property = '')
{
    global $_TABLES;

    if (empty($property)) {
        $property = $_SERVER['REMOTE_ADDR'];
    }
    $property = DB_escapeString($property);
    $type     = DB_escapeString($type);

    DB_delete($_TABLES['speedlimit'], array('type', 'ipaddress'), array($type, $property));
}

/**
* Wrapper function for URL class so as to not confuse people as this will
* eventually get used all over the place
*
* This function returns a crawler friendly URL (if possible)
*
* @param    string      $url    URL to try to build crawler friendly URL for
* @return   string              Rewritten URL
*/

function COM_buildURL( $url )
{
    global $_URL;

    return $_URL->buildURL( $url );
}

/**
* Wrapper function for URL class so as to not confuse people
*
* This function sets the name of the arguments found in url
*
* @param    array   $names  Names of arguments in query string to assign to values
* @return   boolean         True if successful
*/

function COM_setArgNames( $names = array() )
{
    global $_URL;

    return $_URL->setArgNames( $names );
}

/**
* Wrapper function for URL class
*
* returns value for specified argument
*
* @param        string      $name       argument to get value for
* @return   string     Argument value
*/

function COM_getArgument( $name )
{
    global $_URL;

    return $_URL->getArgument( $name );
}

/**
* Occurences / time
*
* This will take a number of occurrences, and number of seconds for the time span and return
* the smallest #/time interval
*
* @param    int     $occurrences        how many occurrences during time interval
* @param    int     $timespan           time interval in seconds
* @return   int Seconds per interval
*/

function COM_getRate( $occurrences, $timespan )
{
    // want to define some common time words (yes, dirk, i need to put this in LANG)
    // time words and their value in seconds
    // week is 7 * day, month is 30 * day, year is 365.25 * day

    $common_time = array(
        "second" => 1,
        "minute" => 60,
        "hour"   => 3600,
        "day"    => 86400,
        "week"   => 604800,
        "month"  => 2592000,
        "year"   => 31557600
        );

    if ( $occurrences != 0 ) {
        $rate = ( int )( $timespan / $occurrences );
        $adjustedRate = $occurrences + 1;
        $time_unit = 'second';

        $found_one = false;

        foreach( $common_time as $unit=>$seconds ) {
            if ( $rate > $seconds ) {
                $foo = ( int )(( $rate / $seconds ) + .5 );

                if (( $foo < $occurrences ) && ( $foo > 0 )) {
                    $adjustedRate = $foo;
                    $time_unit = $unit;
                }
            }
        }

        $singular = '1 shout every ' . $adjustedRate . ' ' . $time_unit;

        if ( $adjustedRate > 1 ) {
            $singular .= 's';
        }
    } else {
        $singular = 'No events';
    }

    return $singular;
}

/**
* Return SQL expression to check for permissions.
*
* Creates part of an SQL expression that can be used to request items with the
* standard set of glFusion permissions.
*
* @param        string      $type     part of the SQL expr. e.g. 'WHERE', 'AND'
* @param        int         $u_id     user id or 0 = current user
* @param        int         $access   access to check for (2=read, 3=r&write)
* @param        string      $table    table name if ambiguous (e.g. in JOINs)
* @return       string      SQL expression string (may be empty)
*
*/
function COM_getPermSQL( $type = 'WHERE', $u_id = 0, $access = 2, $table = '' )
{
    global $_USER, $_GROUPS;

    if ( !empty( $table )) {
        $table .= '.';
    }
    if ( $u_id <= 0) {
        if ( COM_isAnonUser() ) {
            $uid = 1;
        } else {
            $uid = $_USER['uid'];
        }
    } else {
        $uid = $u_id;
    }

    $UserGroups = array();
    if (( empty( $_USER['uid'] ) && ( $uid == 1 )) || ( $uid == $_USER['uid'] )) {
        if ( empty( $_GROUPS )) {
            $_GROUPS = SEC_getUserGroups( $uid );
        }
        $UserGroups = $_GROUPS;
    } else {
        $UserGroups = SEC_getUserGroups( $uid );
    }

    if ( empty( $UserGroups )) {
        // this shouldn't really happen, but if it does, handle user
        // like an anonymous user
        $uid = 1;
    }

    if ( SEC_inGroup( 'Root', $uid )) {
        return '';
    }

    $sql = ' ' . $type . ' (';

    if ( $uid > 1 ) {
        $sql .= "(({$table}owner_id = '{$uid}') AND ({$table}perm_owner >= $access)) OR ";

        $sql .= "(({$table}group_id IN (" . implode( ',', $UserGroups )
             . ")) AND ({$table}perm_group >= $access)) OR ";
        $sql .= "({$table}perm_members >= $access)";
    } else {
        $sql .= "{$table}perm_anon >= $access";
    }

    $sql .= ')';

    return $sql;
}

/**
* Return SQL expression to check for allowed topics.
*
* Creates part of an SQL expression that can be used to only request stories
* from topics to which the user has access to.
*
* Note that this function does an SQL request, so you should cache
* the resulting SQL expression if you need it more than once.
*
* @param    string  $type   part of the SQL expr. e.g. 'WHERE', 'AND'
* @param    int     $u_id   user id or 0 = current user
* @param    string  $table  table name if ambiguous (e.g. in JOINs)
* @return   string          SQL expression string (may be empty)
*
*/
function COM_getTopicSQL( $type = 'WHERE', $u_id = 0, $table = '' )
{
    global $_TABLES, $_USER, $_GROUPS;

    $topicsql = ' ' . $type . ' ';

    if ( !empty( $table )) {
        $table .= '.';
    }

    $UserGroups = array();
    if (( $u_id <= 0 ) || ( isset( $_USER['uid'] ) && $u_id == $_USER['uid'] )) {
        if ( !COM_isAnonUser() ) {
            $uid = $_USER['uid'];
        } else {
            $uid = 1;
        }
        $UserGroups = $_GROUPS;
    } else {
        $uid = $u_id;
        $UserGroups = SEC_getUserGroups( $uid );
    }

    if ( empty( $UserGroups )) {
        // this shouldn't really happen, but if it does, handle user
        // like an anonymous user
        $uid = 1;
    }

    if ( SEC_inGroup( 'Root', $uid )) {
        return '';
    }

    $result = DB_query( "SELECT tid FROM {$_TABLES['topics']}"
                        . COM_getPermSQL( 'WHERE', $uid ));
    $tids = array();
    while ( $T = DB_fetchArray( $result )) {
        $tids[] = $T['tid'];
    }

    if ( sizeof( $tids ) > 0 ) {
        $topicsql .= "({$table}tid IN ('" . implode( "','", $tids ) . "'))";
    } else {
        $topicsql .= '0';
    }

    return $topicsql;
}

/**
* Filter parameters passed per GET (URL) or POST.
*
* @param    string    $parameter   the parameter to test
* @param    boolean   $isnumeric   true if $parameter is supposed to be numeric
* @return   string    the filtered parameter (may now be empty or 0)
*
*/
function COM_applyFilter( $parameter, $isnumeric = false )
{
    $p = $parameter;

    return COM_applyBasicFilter($p, $isnumeric);
}

/**
* Filter parameters
*
* @param    string    $parameter   the parameter to test
* @param    boolean   $isnumeric   true if $parameter is supposed to be numeric
* @return   string    the filtered parameter (may now be empty or 0)
*
* @note     Use this function instead of COM_applyFilter for parameters
*           _not_ coming in through a GET or POST request.
*
*/
function COM_applyBasicFilter( $parameter, $isnumeric = false )
{
    $log_manipulation = false; // set to true to log when the filter applied

    $p = strip_tags( $parameter );
    $p = COM_killJS( $p ); // doesn't help a lot right now, but still ...

    if ( $isnumeric ) {
        // Note: PHP's is_numeric() accepts values like 4e4 as numeric
        if ( !is_numeric( $p ) || ( preg_match( '/^-?\d+$/', $p ) == 0 )) {
            $p = 0;
        }
    } else {
        $p = preg_replace( '/\/\*.*/', '', $p );
        $pa = explode( "'", $p );
        $pa = explode( '"', $pa[0] );
        $pa = explode( '`', $pa[0] );
        $pa = explode( ';', $pa[0] );
        $pa = explode( ',', $pa[0] );
        $pa = explode( '\\', $pa[0] );
        $p = $pa[0];
    }

    if ( $log_manipulation ) {
        if ( strcmp( $p, $parameter ) != 0 ) {
            COM_errorLog( "Filter applied: >> $parameter << filtered to $p [IP {$_SERVER['REMOTE_ADDR']}]", 1);
        }
    }

    return $p;
}

/**
* Sanitize a URL
*
* @param    string  $url                URL to sanitized
* @param    array   $allowed_protocols  array of allowed protocols
* @param    string  $default_protocol   replacement protocol (default: http)
* @return   string                      sanitized URL
*
*/
function COM_sanitizeUrl( $url, $allowed_protocols = array('http','https','ftp'), $default_protocol = 'http' )
{
    $filter = new sanitizer();
    return $filter->sanitizeUrl($url, $allowed_protocols = array('http','https','ftp'), $default_protocol = 'http');
}

/**
* Ensure an ID contains only alphanumeric characters, dots, dashes, or underscores
*
* @param    string  $id     the ID to sanitize
* @param    boolean $new_id true = create a new ID in case we end up with an empty string
* @return   string          the sanitized ID
*/
function COM_sanitizeID( $id, $new_id = true )
{
    $filter = new sanitizer();
    return $filter->sanitizeID($id,$new_id);
}

/**
* Sanitize a filename.
*
* @param    string  $filename   the filename to clean up
* @param    boolean $allow_dots whether to allow dots in the filename or not
* @return   string              sanitized filename
* @note     This function is pretty strict in what it allows. Meant to be used
*           for files to be included where part of the filename is dynamic.
*
*/
function COM_sanitizeFilename($filename, $allow_dots = false)
{
    $filter = new sanitizer();
    return $filter->sanitizeFilename($filename, $allow_dots);
}

/**
* Detect links in a plain-ascii text and turn them into clickable links.
* Will detect links starting with "http:", "https:", "ftp:", and "www.".
*
* Derived from a newsgroup posting by Andreas Schwarz in
* news:de.comp.lang.php <aieq4p$12jn2i$3@ID-16486.news.dfncis.de>
*
* @param    string    $text     the (plain-ascii) text string
* @return   string    the same string, with links enclosed in <a>...</a> tags
*
*/
function COM_makeClickableLinks( $text )
{
    // Matches http:// or https:// or ftp:// or ftps://
    $regex = '/(?<=^|[\n\r\t\s\(\)\[\]<>";])((?:(?:ht|f)tps?:\/{2})(?:[^\n\r\t\s\(\)\[\]<>"&]+(?:&amp;)?)+)(?=[\n\r\t\s\(\)\[\]<>"&]|$)/i';
    $replace = create_function(
        '$match',
        'return COM_makeClickableLinksCallback(\'\', $match[1]);'
    );

    $text = preg_replace_callback($regex, $replace, $text);

    $regex = '/(?<=^|[\n\r\t\s\(\)\[\]<>";])((?:[a-z0-9]+\.)*[a-z0-9-]+\.(?:[a-z]{2,}|xn--[0-9a-z]+)(?:[\/?#](?:[^\n\r\t\s\(\)\[\]<>"&]+(?:&amp;)?)*)?)(?=[\n\r\t\s\(\)\[\]<>"&]|$)/i';
    $replace = create_function(
        '$match',
        'return COM_makeClickableLinksCallback(\'http://\', $match[1]);'
    );

    $text = preg_replace_callback($regex, $replace, $text);

    return $text;
}

/**
* Callback function to help format links in COM_makeClickableLinks
*
* @param    string  $http   set to 'http://' when not already in the url
* @param    string  $link   the url
* @return   string          link enclosed in <a>...</a> tags
*
*/
function COM_makeClickableLinksCallback($http, $link) {
    global $_CONF;
    static $encoding = null;

    if ($encoding === null) {
        $encoding = COM_getEncodingt();
    }

    if (substr($link, -1) === '.') {
        $link = substr($link, 0, -1);
        $end = '.';
    } else {
        $end = '';
    }

    if (isset($_CONF['linktext_maxlen']) && $_CONF['linktext_maxlen'] > 0) {
        $text = COM_truncate($link, $_CONF['linktext_maxlen'], '...', 10);
    } else {
        $text = $link;
    }

    $text = htmlspecialchars($text, ENT_QUOTES, $encoding);

    return '<a href="' . $http . $link . '">' . $text . '</a>' . $end;
}


/**
* Undo the conversion of URLs to clickable links (in plain text posts),
* e.g. so that we can present the user with the post as they entered them.
*
* @param    string  $txt    story text
* @param    string          story text without links
*
*/
function COM_undoClickableLinks( $text )
{
    $text = preg_replace( '/<a href="([^"]*)">([^<]*)<\/a>/', '\1', $text );

    return $text;
}

/**
* Highlight the words from a search query in a given text string.
*
* @param    string  $text   the text
* @param    string  $query  the search query
* @return   string          the text with highlighted search words
*
*/
function COM_highlightQuery( $text, $query, $class = 'highlight' )
{
    if (!empty($text) && !empty($query)) {
        // escape PCRE special characters
        $query = preg_quote($query, '/');

        $mywords = explode(' ', $query);
        foreach ($mywords as $searchword) {
            if (!empty($searchword)) {
                $before = "/(?!(?:[^<]+>|[^>]+<\/a>))\b";
                $after = "\b/i";
                if ($searchword <> utf8_encode($searchword)) {
                     if (@preg_match('/^\pL$/u', urldecode('%C3%B1'))) { // Unicode property support
                          $before = "/(?<!\p{L})";
                          $after = "(?!\p{L})/u";
                     } else {
                          $before = "/";
                          $after = "/u";
                     }
                }
                $HLtext = @preg_replace($before . $searchword . $after, "<span class=\"$class\">\\0</span>", '<!-- x -->' . $text . '<!-- x -->' );
                if ( $HLtext != NULL ) {
                    $text = $HLtext;
                }
            }
        }
    }
    return $text;
}

/**
* Determines the difference between two dates.
*
* This will takes either unixtimestamps or English dates as input and will
* automatically do the date diff on the more recent of the two dates (e.g. the
* order of the two dates given doesn't matter).
*
* @author Tony Bibbs, tony DOT bibbs AT iowa DOT gov
* @access public
* @param string $interval Can be:
* y = year
* m = month
* w = week
* h = hours
* i = minutes
* s = seconds
* @param string|int $date1 English date (e.g. 10 Dec 2004) or unixtimestamp
* @param string|int $date2 English date (e.g. 10 Dec 2004) or unixtimestamp
* @return int Difference of the two dates in the unit of time indicated by the interval
*
*/
function COM_dateDiff( $interval, $date1, $date2 )
{
    // Convert dates to timestamps, if needed.
    if ( !is_numeric( $date1 )) {
        $date1 = strtotime( $date1 );
    }

    if ( !is_numeric( $date2 )) {
        $date2 = strtotime( $date2 );
    }

    // Function roughly equivalent to the ASP "DateDiff" function
    if ( $date2 > $date1 ) {
        $seconds = $date2 - $date1;
    } else {
        $seconds = $date1 - $date2;
    }

    switch( $interval ) {
        case "y":
            list($year1, $month1, $day1) = split('-', date('Y-m-d', $date1));
            list($year2, $month2, $day2) = split('-', date('Y-m-d', $date2));
            $time1 = (date('H',$date1)*3600) + (date('i',$date1)*60) + (date('s',$date1));
            $time2 = (date('H',$date2)*3600) + (date('i',$date2)*60) + (date('s',$date2));
            $diff = $year2 - $year1;
            if ($month1 > $month2) {
                $diff -= 1;
            } elseif ($month1 == $month2) {
                if ($day1 > $day2) {
                    $diff -= 1;
                } elseif ($day1 == $day2) {
                    if ($time1 > $time2) {
                        $diff -= 1;
                    }
                }
            }
            break;
        case "m":
            list($year1, $month1, $day1) = split('-', date('Y-m-d', $date1));
            list($year2, $month2, $day2) = split('-', date('Y-m-d', $date2));
            $time1 = (date('H',$date1)*3600) + (date('i',$date1)*60) + (date('s',$date1));
            $time2 = (date('H',$date2)*3600) + (date('i',$date2)*60) + (date('s',$date2));
            $diff = ($year2 * 12 + $month2) - ($year1 * 12 + $month1);
            if ($day1 > $day2) {
                $diff -= 1;
            } elseif ($day1 == $day2) {
                if ($time1 > $time2) {
                    $diff -= 1;
                }
            }
            break;
        case "w":
            // Only simple seconds calculation needed from here on
            $diff = floor($seconds / 604800);
            break;
         case "d":
            $diff = floor($seconds / 86400);
            break;
        case "h":
            $diff = floor($seconds / 3600);
            break;
        case "i":
            $diff = floor($seconds / 60);
            break;
        case "s":
            $diff = $seconds;
            break;
    }

    return $diff;
}

/**
* Try to figure out our current URL, including all parameters.
*
* This is an ugly hack since there's no single variable that returns what
* we want and the variables used here may not be available on all servers
* and / or setups.
*
* Seems to work on Apache (1.3.x and 2.x), IIS, and Zeus ...
*
* @return   string  complete URL, e.g. 'http://www.example.com/blah.php?foo=bar'
*
*/
function COM_getCurrentURL()
{
    global $_CONF;

    $thisUrl = '';

    if ( empty( $_SERVER['SCRIPT_URI'] )) {
        if ( !empty( $_SERVER['DOCUMENT_URI'] )) {
            $document_uri = $_SERVER['DOCUMENT_URI'];
            $first_slash = strpos( $_CONF['site_url'], '/' );
            if ( $first_slash === false ) {
                // special case - assume it's okay
                $thisUrl = $_CONF['site_url'] . $document_uri;
            } else if ( $first_slash + 1 == strrpos( $_CONF['site_url'], '/' )) {
                // site is in the document root
                $thisUrl = $_CONF['site_url'] . $document_uri;
            } else {
                // extract server name first
                $pos = strpos( $_CONF['site_url'], '/', $first_slash + 2 );
                $thisUrl = substr( $_CONF['site_url'], 0, $pos ) . $document_uri;
            }
        }
    } else {
        $thisUrl = $_SERVER['SCRIPT_URI'];
    }
    if ( !empty( $thisUrl ) && !empty( $_SERVER['QUERY_STRING'] ) && (strpos($thisUrl,'?') === false)  ) {
        $thisUrl .= '?' . $_SERVER['QUERY_STRING'];
    }
    if ( empty( $thisUrl )) {
        $requestUri = $_SERVER['REQUEST_URI'];
        if ( empty( $_SERVER['REQUEST_URI'] )) {
            // on a Zeus webserver, prefer PATH_INFO over SCRIPT_NAME
            if ( empty( $_SERVER['PATH_INFO'] )) {
                $requestUri = $_SERVER['SCRIPT_NAME'];
            } else {
                $requestUri = $_SERVER['PATH_INFO'];
            }
            if ( !empty( $_SERVER['QUERY_STRING'] )) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        }

        $firstslash = strpos( $_CONF['site_url'], '/' );
        if ( $firstslash === false ) {
            // special case - assume it's okay
            $thisUrl = $_CONF['site_url'] . $requestUri;
        } else if ( $firstslash + 1 == strrpos( $_CONF['site_url'], '/' )) {
            // site is in the document root
            $thisUrl = $_CONF['site_url'] . $requestUri;
        } else {
            // extract server name first
            $pos = strpos( $_CONF['site_url'], '/', $firstslash + 2 );
            $thisUrl = substr( $_CONF['site_url'], 0, $pos ) . $requestUri;
        }
    }
    $filter = sanitizer::getInstance();
    $thisUrl = $filter->sanitizeURL($thisUrl);
    return $thisUrl;
}

/**
* Check if we're on glFusion's index page.
*
* See if we're on the main index page (first page, no topics selected).
*
* @return   boolean     true = we're on the frontpage, false = we're not
*
*/
function COM_onFrontpage()
{
    global $_CONF, $topic, $page, $newstories;

    // Note: We can't use $PHP_SELF here since the site may not be in the
    // DocumentRoot
    $onFrontpage = false;

    // on a Zeus webserver, prefer PATH_INFO over SCRIPT_NAME
    if ( empty( $_SERVER['PATH_INFO'] )) {
        $scriptName = $_SERVER['SCRIPT_NAME'];
    } else {
        $scriptName = $_SERVER['PATH_INFO'];
    }

    preg_match( '/\/\/[^\/]*(.*)/', $_CONF['site_url'], $pathonly );
    if (( $scriptName == $pathonly[1] . '/index.php' ) &&
             empty($topic) && (empty($page) || ($page == 1)) && !$newstories) {
        $onFrontpage = true;
    }
    return $onFrontpage;
}

/**
* Check if we're on glFusion's index page [deprecated]
*
* Note that this function returns FALSE when we're on the index page. Due to
* the inverted return values, it has been deprecated and is only provided for
* backward compatibility - use COM_onFrontpage() instead.
*
* @see COM_onFrontpage
*
*/
function COM_isFrontpage()
{
    return !COM_onFrontpage();
}

/**
*   Converts a number for output into a formatted number with thousands-
*   separator, comma-separator and fixed decimals if necessary
*
*   @param  float   $number     Number that will be formatted
*   @param  integer $decimals   Optional number of decimals
*   @return string              Formatted number
*/
function COM_numberFormat( $number, $decimals=-1 )
{
    global $_CONF;

    if ($decimals != -1) {
        // Specific number of decimals requested, could be zero
        $dc = (int)$decimals;
    } elseif ( $number - floor( $number ) > 0 ) {
        // Number has decimals, get the configured decimal count
        $dc = $_CONF['decimal_count'];
    } else {
        // Number has no decimals, and we don't care
        $dc = 0;
    }
    $ts = $_CONF['thousand_separator'];
    $ds = $_CONF['decimal_separator'];

    return @number_format( $number, $dc, $ds, $ts );
}

/**
* Convert a text based date YYYY-MM-DD to a unix timestamp integer value
*
* @param    string  $date   Date in the format YYYY-MM-DD
* @param    string  $time   Option time in the format HH:MM::SS
* @return   int             UNIX Timestamp
*/
function COM_convertDate2Timestamp( $date, $time = '' )
{
    $atoks = array();
    $btoks = array();

    // Breakup the string using either a space, fwd slash, dash, bkwd slash or
    // colon as a delimiter
    $atok = strtok( $date, ' /-\\:' );
    while ( $atok !== FALSE ) {
        $atoks[] = $atok;
        $atok = strtok( ' /-\\:' );  // get the next token
    }

    for( $i = 0; $i < 3; $i++ ) {
        if ( !isset( $atoks[$i] ) || !is_numeric( $atoks[$i] )) {
            $atoks[$i] = 0;
        }
    }

    if ( $time == '' ) {
        $timestamp = @mktime( 0, 0, 0, $atoks[1], $atoks[2], $atoks[0] );
    } else {
        $btok = strtok( $time, ' /-\\:' );
        while ( $btok !== FALSE ) {
            $btoks[] = $btok;
            $btok = strtok( ' /-\\:' );
        }

        for( $i = 0; $i < 3; $i++ ) {
            if ( !isset( $btoks[$i] ) || !is_numeric( $btoks[$i] )) {
                $btoks[$i] = 0;
            }
        }

        $timestamp = @mktime( $btoks[0], $btoks[1], $btoks[2],
                             $atoks[1], $atoks[2], $atoks[0] );
    }

    return $timestamp;
}

/**
* Get the HTML for an image with height & width
*
* @param    string  $file   full path to the file
* @return   string          html that will be included in the img-tag
*/
function COM_getImgSizeAttributes( $file )
{
    $sizeattributes = '';

    if ( file_exists( $file )) {
        $dimensions = getimagesize( $file );
        if ( !empty( $dimensions[0] ) AND !empty( $dimensions[1] )) {
            $sizeattributes = 'width="' . $dimensions[0]
                            . '" height="' . $dimensions[1] . '" ';
        }
    }

    return $sizeattributes;
}

/**
* Display a message and abort
*
* @param    int     $msg            message number
* @param    string  $plugin         plugin name, if applicable
* @param    int     $http_status    HTTP status code to send with the message
* @param    string  $http_text      Textual version of the HTTP status code
*
* @note Displays the message and aborts the script.
*
*/
function COM_displayMessageAndAbort( $msg, $plugin = '', $http_status = 200, $http_text = 'OK')
{
    $display = COM_siteHeader( 'menu' )
             . COM_showMessage( $msg, $plugin,'',1,'error' )
             . COM_siteFooter( true );

    echo $display;
    exit;
}

/**
* Return full URL of a topic icon
*
* @param    string  $imageurl   (relative) topic icon URL
* @return   string              Full URL
*
*/
function COM_getTopicImageUrl( $imageurl )
{
    global $_CONF, $_THEME_URL;

    $iconurl = '';

    if ( !empty( $imageurl )) {
        if ( isset( $_THEME_URL )) {
            $iconurl = $_THEME_URL . $imageurl;
        } else {
            $stdImageLoc = true;
            if ( !strstr( $_CONF['path_images'], $_CONF['path_html'] )) {
                $stdImageLoc = false;
            }

            if ( $stdImageLoc ) {
                $iconurl = $_CONF['site_url'] . $imageurl;
            } else {
                $t = explode( '/', $imageurl );
                $topicicon = $t[count( $t ) - 1];
                $iconurl = $_CONF['site_url']
                         . '/getimage.php?mode=topics&amp;image=' . $topicicon;
            }
        }
    }

    return $iconurl;
}

/**
 * Create an HTML link
 *
 * @param   string  $content    the object to be linked (text, image etc)
 * @param   string  $url        the URL the link will point to
 * @param   array   $attr       an array of optional attributes for the link
 *                              for example array('title' => 'whatever');
 * @return  string              the HTML link
 */
function COM_createLink($content, $url, $attr = array())
{
    $retval = '';

    $attr_str = 'href="' . $url . '"';
    foreach ($attr as $key => $value) {
        $attr_str .= " $key=\"$value\"";
    }
    $retval .= "<a $attr_str>$content</a>";

    return $retval;
}

/**
 * Create an HTML img
 *
 * @param   string  $url        the URL of the image, either starting with
 *                              http://... or $_CONF['layout_url'] is prepended
 * @param   string  $alt        the 'alt'-tag of the image
 * @param   array   $attr       an array of optional attributes for the link
 *                              for example array('title' => 'whatever');
 * @return  string              the HTML img
 */
function COM_createImage($url, $alt = "", $attr = array())
{
    global $_CONF;

    $retval = '';

    if (strpos($url, 'http://') !== 0 && strpos($url,'https://') !== 0 ) {
        $url = $_CONF['layout_url'] . $url;
    }
    $attr_str = 'src="' . $url . '"';

    foreach ($attr as $key => $value) {
        $attr_str .= " $key=\"$value\"";
    }

    $retval = "<img $attr_str alt=\"$alt\"/>";

    return $retval;
}

/**
* Try to determine the user's preferred language by looking at the
* "Accept-Language" header sent by their browser (assuming they bothered
* to select a preferred language there).
*
* @return   string  name of the language file to use or an empty string
*
* Bugs: Does not take the quantity ('q') parameter into account, but only
*       looks at the order of language codes.
*
* Sample header: Accept-Language: en-us,en;q=0.7,de-de;q=0.3
*
*/
function COM_getLanguageFromBrowser()
{
    global $_CONF;

    $retval = '';

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $accept = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        foreach ($accept as $l) {
            $l = explode(';', trim($l));
            $l = $l[0];
            if (@array_key_exists($l, $_CONF['language_files'])) {
                $retval = $_CONF['language_files'][$l];
                break;
            } else {
                $l = explode('-', $l);
                $l = $l[0];
                if (@array_key_exists($l, $_CONF['language_files'])) {
                    $retval = $_CONF['language_files'][$l];
                    break;
                }
            }
        }
    }

    return $retval;
}

/**
* Determine current language
*
* @return   string  name of the language file (minus the '.php' extension)
*
*/
function COM_getLanguage()
{
    global $_CONF, $_USER;

    $langfile = '';

    if (!empty($_USER['language'])) {
        $langfile = $_USER['language'];
    } elseif (!empty($_COOKIE[$_CONF['cookie_language']])) {
        $langfile = $_COOKIE[$_CONF['cookie_language']];
    } elseif (isset($_CONF['languages'])) {
        $langfile = COM_getLanguageFromBrowser();
    }

    $langfile = COM_sanitizeFilename($langfile);
    if (!empty($langfile)) {
        if (is_file($_CONF['path_language'] . $langfile . '.php')) {
            return $langfile;
        }
    }

    // if all else fails, return the default language
    return $_CONF['language'];
}

/**
* Determine the ID to use for the current language
*
* The $_CONF['language_files'] array maps language IDs to language file names.
* This function returns the language ID for a certain language file, to be
* used in language-dependent URLs.
*
* @param    string  $language   current language file name (optional)
* @return   string              language ID, e.g 'en'; empty string on error
*
*/
function COM_getLanguageId($language = '')
{
    global $_CONF;

    if (empty($language)) {
        $language = COM_getLanguage();
    }

    $lang_id = '';
    if (isset($_CONF['language_files'])) {
        $lang_id = array_search($language, $_CONF['language_files']);

        if ($lang_id === false) {
            // that looks like a misconfigured $_CONF['language_files'] array
            COM_errorLog('Language "' . $language . '" not found in $_CONF[\'language_files\'] array!');

            $lang_id = ''; // not much we can do here ...
        }
    }

    return $lang_id;
}

/**
* Return SQL expression to request language-specific content
*
* Creates part of an SQL expression that can be used to request items in the
* current language only.
*
* @param    string  $field  name of the "id" field, e.g. 'sid' for stories
* @param    string  $type   part of the SQL expression, e.g. 'WHERE', 'AND'
* @param    string  $table  table name if ambiguous, e.g. in JOINs
* @return   string          SQL expression string (may be empty)
*
*/
function COM_getLangSQL( $field, $type = 'WHERE', $table = '' )
{
    global $_CONF;

    $sql = '';

    if ( !empty( $_CONF['languages'] ) && !empty( $_CONF['language_files'] )) {
        if ( !empty( $table )) {
            $table .= '.';
        }

        $lang_id = COM_getLanguageId();

        if ( !empty( $lang_id )) {
            $sql = ' ' . $type . " ({$table}$field LIKE '%\\_$lang_id')";
        }
    }

    return $sql;
}



/**
* Switch locale settings
*
* When multi-language support is enabled, allow overwriting the default locale
* settings with language-specific settings (date format, etc.). So in addition
* to $_CONF['date'] you can have a $_CONF['date_en'], $_CONF['date_de'], etc.
*
*/
function COM_switchLocaleSettings()
{
    global $_CONF;

    if ( !empty( $_CONF['languages'] ) && !empty( $_CONF['language_files'] )) {
        $overridables = array
        (
          'locale',
          'date', 'daytime', 'shortdate', 'dateonly', 'timeonly',
          'week_start', 'hour_mode',
          'thousand_separator', 'decimal_separator'
        );

        $langId = COM_getLanguageId();
        foreach( $overridables as $option ) {
            if ( isset( $_CONF[$option . '_' . $langId] )) {
                $_CONF[$option] = $_CONF[$option . '_' . $langId];
            }
        }
    }
}

/**
* Truncate a string that contains HTML tags.
*
* Truncates a string to a max. length and optionally adds a filler string,
* i.e.; '...', to indicate the truncation.
*
* This function is multi-byte string aware. This function is based on a
* code snippet by pitje at Snipplr.com.
*
* NOTE: The truncated string may be shorter or longer than $maxlen characters.
* Currently any initial HTML tags in the truncated string are taken into account.
* The $filler string is also taken into account but any HTML tags that are added
* by this function to close open HTML tags are not.
*
* @param    string  $htmltext   the text string which contains HTML tags to truncate
* @param    int     $maxlen     max. number of characters in the truncated string
* @param    string  $filler     optional filler string, e.g. '...'
* @param    int     $endchars   number of characters to show after the filler
* @return   string              truncated string
*
*/
function COM_truncateHTML ( $htmltext, $maxlen, $filler = '', $endchars = 0 )
{

    $newlen = $maxlen - utf8_strlen($filler);
    $len = utf8_strlen($htmltext);
    if ($len > $maxlen) {
        $htmltext = utf8_substr($htmltext, 0, $newlen - $endchars);

        // Strip any mangled tags off the end
        if (utf8_strrpos($htmltext, '<' ) > utf8_strrpos($htmltext, '>')) {
            $htmltext = utf8_substr($htmltext, 0, utf8_strrpos($htmltext, '<'));
        }

        $htmltext = $htmltext . $filler . utf8_substr($htmltext, $len - $endchars, $endchars);

        // put all opened tags into an array
        preg_match_all ( "#<([a-z]+)( .*)?(?!/)>#iU", $htmltext, $result );
        $openedtags = $result[1];
        $openedtags = array_diff($openedtags, array("img", "hr", "br"));
        $openedtags = array_values($openedtags);

        // put all closed tags into an array
        preg_match_all ("#</([a-z]+)>#iU", $htmltext, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);

        // all tags are closed
        if (count( $closedtags ) == $len_opened) {
            return $htmltext;
        }
        $openedtags = array_reverse ($openedtags);

        // close tags
        for($i = 0; $i < $len_opened; $i++) {
            if (!in_array ($openedtags[$i], $closedtags )) {
                $htmltext .= "</" . $openedtags[$i] . ">";
            } else {
                unset ($closedtags[array_search ($openedtags[$i], $closedtags)]);
            }
        }
    }

    return $htmltext;
}

/**
* Truncate a string
*
* Truncates a string to a max. length and optionally adds a filler string,
* e.g. '...', to indicate the truncation.
* This function is multi-byte string aware, based on a patch by Yusuke Sakata.
*
* @param    string  $text   the text string to truncate
* @param    int     $maxlen max. number of characters in the truncated string
* @param    string  $filler optional filler string, e.g. '...'
* @param    boolean $tip    optional tooltip with untruncated text
*
* @return   string          truncated string
*
* @note The truncated string may be shorter but will never be longer than
*       $maxlen characters, i.e. the $filler string is taken into account.
*       if $tip is true, and text is truncated, the result is encapsulated in a
*       span with title attribute set to the full text (hovertip effect)

*
*/
function COM_truncate( $text, $maxlen, $filler = '', $tip = false )
{
    $newlen = $maxlen - utf8_strlen( $filler );
    $len = utf8_strlen( $text );
    if ( $len > $maxlen ) {
        $retval = ($tip) ? '<span title="' . $text . '">' : '';
        $retval .= utf8_substr( $text, 0, $newlen ) . $filler;
        $retval .= ($tip) ? '</span>' : '';
        return $retval;
    } else {
        return $text;
    }
}

/**
* Get the current character set
*
* @return   string      character set, e.g. 'utf-8'
*
* Uses (if available, and in this order)
* - $LANG_CHARSET (from the current language file)
* - $_CONF['default_charset'] (from siteconfig.php)
* - 'iso-8859-1' (hard-coded fallback)
*
*/
function COM_getCharset()
{
    global $_CONF, $LANG_CHARSET;

    if ( empty( $LANG_CHARSET )) {
        $charset = $_CONF['default_charset'];
        if ( empty( $charset )) {
            $charset = 'iso-8859-1';
        }
    } else {
        $charset = $LANG_CHARSET;
    }

    return $charset;
}

/**
* Get a valid encoding for htmlspecialchars()
*
* @return   string      character set, e.g. 'utf-8'
*
*
*/
function COM_getEncodingt() {
    global $_CONF, $LANG_CHARSET;

    static $encoding = null;

    $valid_charsets = array('iso-8859-1','iso-8859-15','utf-8','cp866','cp1251','cp1252','koi8-r','big5','gb2312','big5-hkscs','shift_jis sjis','euc-jp');

    if ($encoding === null) {
        if (isset($LANG_CHARSET)) {
            $encoding = $LANG_CHARSET;
        } else if (isset($_CONF['default_charset'])) {
            $encoding = $_CONF['default_charset'];
        } else {
            $encoding = 'iso-8859-1';
        }
    }

    $encoding = strtolower($encoding);

    if ( in_array($encoding,$valid_charsets) ) {
        return $encoding;
    } else {
        return 'iso-8859-1';
    }

    return $encoding;
}

/**
  * Handle errors.
  *
  * This function will handle all PHP errors thrown at it, without exposing
  * paths, and hopefully, providing much more information to Root Users than
  * the default white error page.
  *
  * This function will call out to CUSTOM_handleError if it exists, but, be
  * advised, only override this function with a very, very stable function. I'd
  * suggest one that outputs some static, basic HTML.
  *
  * The PHP feature that allows us to do so is documented here:
  * http://uk2.php.net/manual/en/function.set-error-handler.php
  *
  * @param  int     $errno      Error Number.
  * @param  string  $errstr     Error Message.
  * @param  string  $errfile    The file the error was raised in.
  * @param  int     $errline    The line of the file that the error was raised at.
  * @param  array   $errcontext An array that points to the active symbol table at the point the error occurred.
  */
function COM_handleError($errno, $errstr, $errfile='', $errline=0, $errcontext='')
{
    global $_CONF, $_USER, $_SYSTEM;

    // Handle @ operator
    if (error_reporting() == 0) {
        return;
    }

    /*
     * If we have a root user, then output detailed error message:
     */
    if ((is_array($_USER) && function_exists('SEC_inGroup'))
            || (isset($_SYSTEM['rootdebug']) && $_SYSTEM['rootdebug'])) {
        if ($_SYSTEM['rootdebug'] || SEC_inGroup('Root')) {
            $title = 'An Error Occurred';
            if (!empty($_CONF['site_name'])) {
                $title = $_CONF['site_name'] . ' - ' . $title;
            }
            echo("<html><head><title>$title</title></head>\n<body>\n");

            echo('<h1>An error has occurred:</h1>');
            if ($_SYSTEM['rootdebug']) {
                echo('<h2 style="color: red">This is being displayed as "Root Debugging" is enabled
                        in your glFusion siteconfig.php.</h2><p>If this is a production
                        website you <strong><em>should disable</em></strong> this
                        option once you have resolved any issues you are
                        troubleshooting.</p>');
            } else {
                echo('<p>(This text is only displayed to users in the group \'Root\')</p>');
            }
            echo("<p>$errno - $errstr @ $errfile line $errline</p>");

            if (!function_exists('SEC_inGroup') || !SEC_inGroup('Root')) {
                if ('force' != ''.$_SYSTEM['rootdebug']) {
                    $errcontext = COM_rootDebugClean($errcontext);
                } else {
                    echo('<h2 style="color: red">Root Debug is set to "force", this
                    means that passwords and session cookies are exposed in this
                    message!!!</h2>');
                }
            }

            if (@ini_get('xdebug.default_enable') == 1) {
                ob_start();
                var_dump($errcontext);
                $errcontext = ob_get_contents();
                ob_end_clean();
                echo "$errcontext</body></html>";
            } else {
                $btr = debug_backtrace();
                if (count($btr) > 0) {
                    if ($btr[0]['function'] == 'COM_handleError') {
                        array_shift($btr);
                    }
                }
                if (count($btr) > 0) {
                    echo "<font size='1'><table class='xdebug-error' dir='ltr' border='1' cellspacing='0' cellpadding='1'>\n";
                    echo "<tr><th align='left' bgcolor='#e9b96e' colspan='5'>Call Stack</th></tr>\n";
                    echo "<tr><th align='right' bgcolor='#eeeeec'>#</th><th align='left' bgcolor='#eeeeec'>Function</th><th align='left' bgcolor='#eeeeec'>File</th><th align='right' bgcolor='#eeeeec'>Line</th></tr>\n";
                    $i = 1;
                    foreach ($btr as $b) {
                        $f = '';
                        if (! empty($b['file'])) {
                            $f = $b['file'];
                        }
                        $l = '';
                        if (! empty($b['line'])) {
                            $l = $b['line'];
                        }
                        echo "<tr><td bgcolor='#eeeeec' align='right'>$i</td><td bgcolor='#eeeeec'>{$b['function']}</td><td bgcolor='#eeeeec'>{$f}</td><td bgcolor='#eeeeec' align='right'>{$l}</td></tr>\n";
                        $i++;
                        if ($i > 100) {
                            echo "<tr><td bgcolor='#eeeeec' align='left' colspan='4'>Possible recursion - aborting.</td></tr>\n";
                            break;
                        }
                    }
                    echo "</table></font>\n";
                }
                echo '<pre>';
                ob_start();
                var_dump($errcontext);
                $errcontext = htmlspecialchars(ob_get_contents());
                ob_end_clean();
                echo "$errcontext</pre></body></html>";
            }
            exit;
        }
    }

    /* If there is a custom error handler, fail over to that, but only
     * if the error wasn't in lib-custom.php
     */
    if (is_array($_CONF) && !(strstr($errfile, 'lib-custom.php'))) {
        if (array_key_exists('path_system', $_CONF)) {
            if (file_exists($_CONF['path_system'] . 'lib-custom.php')) {
                require_once $_CONF['path_system'] . 'lib-custom.php';
            }
            if (function_exists('CUSTOM_handleError')) {
                CUSTOM_handleError($errno, $errstr, $errfile, $errline, $errcontext);
                exit;
            }
        }
    }

    // if we do not throw the error back to an admin, still log it in the error.log
    COM_errorLog("$errno - $errstr @ $errfile line $errline", 1);

    // Does the theme implement an error message html file?
    if (!empty($_CONF['path_layout']) &&
            file_exists($_CONF['path_layout'] . 'errormessage.html')) {
        // NOTE: NOT A TEMPLATE! JUST HTML!
        include $_CONF['path_layout'] . 'errormessage.html';
    } else {
        // Otherwise, display simple error message
        $title = 'An Error Occurred';
        if (!empty($_CONF['site_name'])) {
            $title = $_CONF['site_name'] . ' - ' . $title;
        }
        echo("
        <html>
            <head>
                <title>{$title}</title>
            </head>
            <body>
            <div style=\"width: 100%; text-align: center;\">
            There has been an error in building this page. Please try again later.
            </div>
            </body>
        </html>
        ");
    }

    exit;
}

/**
  * Recurse through the error context array removing/blanking password/cookie
  * values in case the "for development" only switch is left on in a production
  * environment.
  *
  * [Not fit for public consumption comments about what users who enable root
  * debug in production should have done to them, and why making this change
  * defeats the point of the entire root debug feature go here.]
  *
  * @param $array   Array of state info (Recursive array).
  * @return Cleaned array
  */
function COM_rootDebugClean($array, $blank=false)
{
    $blankField = false;
    while (list($key, $value) = each($array)) {
        $lkey = strtolower($key);
        if ((strpos($lkey, 'pass') !== false) || (strpos($lkey, 'cookie')!== false)) {
            $blankField = true;
        } else {
            $blankField = $blank;
        }
        if (is_array($value)) {
            $array[$key] = COM_rootDebugClean($value, $blankField);
        } elseif ($blankField) {
            $array[$key] = '[VALUE REMOVED]';
        }
    }
    return $array;
}

/**
  * Checks to see if the needed version is installed.
  *
  * @param  string $have    Version installed
  * @param  string $need    Version we must have to continue
  * @return boolean         true if $have is greater or equal to $need
  */

function COM_checkVersion($have, $need) {

    list($major,$minor,$rev,$extra) = explode('.',$have.'....');
    list($requireMajor,$requireMinor,$requireRev,$requireExtra) = explode('.',$need.'....');
    if ( !isset($major) )
        $major = 0;
    if ( !isset($minor) )
        $minor = 0;
    if ( !isset($rev) )
        $rev = 0;
    if ( !isset($extra) )
        $extra = '';
    if ( !isset($requireMajor) )
        $requireMajor = 0;
    if ( !isset($requireMinor) )
        $requireMinor = 0;
    if ( !isset($requireRev) )
        $requireRev = 0;
    if ( !isset($requireExtra) )
        $RequireExtra = '';
    $passed = 0;
    if ( $requireMajor <= $major ) {
        if ( $requireMajor < $major ) {
            $passed = 1;
        } else if ( $requireMinor <= $minor ) {
            if ( $requireMinor < $minor ) {
                $passed = 1;
            } else if ( $requireRev <= $rev ) {
                if ( $requireRev < $rev ) {
                    $passed = 1;
                } else if ($requireExtra != '' ) {
                    if ( $requireExtra == 'fusion' ) {
                        $passed = 1;
                    }
                } else {
                    $passed = 1;
                }
            }
        }
    }
    return $passed;
}

/**
  * Checks to see if a specified user, or the current user if non-specified
  * is the anonymous user.
  *
  * @param  int $uid    ID of the user to check, or none for the current user.
  * @return boolean     true if the user is the anonymous user.
  */
function COM_isAnonUser($uid = '')
{
    global $_USER;

    /* If no user was specified, fail over to the current user if there is one */
    if ( empty( $uid ) ) {
        if ( isset( $_USER['uid'] ) ) {
            $uid = $_USER['uid'];
        }
    }

    if ( !empty( $uid ) ) {
        return ($uid == 1);
    } else {
        return true;
    }
}

/**
* Set the {lang_id} and {lang_attribute} variables for a template
*
* @param    ref     $template   template to use
* @return   void
* @note     {lang_attribute} is only set in multi-language environments.
*
*/
function COM_setLangIdAndAttribute(&$template)
{
    global $_CONF;

    $langAttr = '';
    $langId   = '';

    if (!empty($_CONF['languages']) && !empty($_CONF['language_files'])) {
        $langId = COM_getLanguageId();
    } else {
        // try to derive the language id from the locale
        $l = explode('.', $_CONF['locale']); // get rid of character set
        $langId = $l[0];
        $l = explode('@', $langId); // get rid of '@euro', etc.
        $langId = $l[0];
    }

    if (!empty($langId)) {
        $l = explode('-', str_replace('_', '-', $langId));
        if ((count($l) == 1) && (strlen($langId) == 2)) {
            $langAttr = 'lang="' . $langId . '"';
        } else if (count($l) == 2) {
            if (($l[0] == 'i') || ($l[0] == 'x')) {
                $langId = implode('-', $l);
                $langAttr = 'lang="' . $langId . '"';
            } else if (strlen($l[0]) == 2) {
                $langId = implode('-', $l);
                $langAttr = 'lang="' . $langId . '"';
            } else {
                $langId = $l[0];
                // this isn't a valid lang attribute, so don't set $langAttr
            }
        }
    }
    $template->set_var('lang_id', $langId);

    if (!empty($_CONF['languages']) && !empty($_CONF['language_files'])) {
        $template->set_var('lang_attribute', ' ' . $langAttr);
    } else {
        $template->set_var('lang_attribute', '');
    }
}


/**
 * Display 404 - Not found message
 *
 */
function COM_404()
{
    global $LANG_404;
    /*
     * Allow for custom 404 handler
     */
    if ( function_exists('CUSTOM_404') ) {
        return CUSTOM_404();
    }

    $url = '';

    if (isset ($_SERVER['SCRIPT_URI'])) {
        $url = strip_tags ($_SERVER['SCRIPT_URI']);
    } else {
        $pos = strpos ($_SERVER['REQUEST_URI'], '?');
        if ($pos === false) {
            $request = $_SERVER['REQUEST_URI'];
        } else {
            $request = substr ($_SERVER['REQUEST_URI'], 0, $pos);
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . strip_tags ($request);
    }
    header("HTTP/1.0 404 Not Found");
    $display = COM_siteHeader ('menu', $LANG_404[1]);
    $display .= COM_startBlock ($LANG_404[1]);
    $display .= sprintf ($LANG_404[2]);
    $display .= $LANG_404[3];
    $display .= '<br/><br/><p><b>' . $url . '</b></p>';
    $display .= COM_endBlock ();
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

/**
* Decompress an archive
*
* @param    string  $file   soure file
* @param    string  $target destination directory
* @return   bool            true on success, false on fail
*
*/
function COM_decompress($file, $target)
{
    global $_CONF;

    $ok = 0;

    // decompression library doesn't like target folders ending in "/"
    if (substr($target, -1) == "/") $target = substr($target, 0, -1);
    $ext = substr($file, strrpos($file,'.')+1);

    // .tar, .tar.bz, .tar.gz, .tgz
    if (in_array($ext, array('tar','bz','bz2','gz','tgz'))) {

        require_once 'Archive/Tar.php';

        $tar = new Archive_Tar($file);

        $ok = $tar->extract($target);
        if ( $ok ) {
            $ok = 1;
        }
        return ($ok<1?false:true);

    } else if ($ext == 'zip') {

      require_once $_CONF['path'].'/lib/ZipLib.class.php';

      $zip = new ZipLib();
      $ok = $zip->Extract($file, $target);

      return ($ok==-1?false:true);

    }

    // unsupported file type
    return false;
}

function COM_isWritable($path)
{
    if ($path{strlen($path)-1}=='/')
        return COM_isWritable($path.uniqid(mt_rand()).'.tmp');

    if (@file_exists($path)) {
        if (!($f = @fopen($path, 'r+')))
            return false;
        @fclose($f);
        return true;
    }

    if (!($f = @fopen($path, 'w')))
        return false;
    @fclose($f);
    @unlink($path);
    return true;
}

function COM_recursiveDelete($path)
{
    if(is_file($path)){
        if ($COM_VERBOSE) COM_errorLog("COM_recursiveDelete(file): {$path}");
        return @unlink($path);
    }
    elseif(is_dir($path)){
        $scan = glob(rtrim($path,'/').'/*');
        foreach($scan as $index => $file){
            COM_recursiveDelete($file);
        }
        if ($COM_VERBOSE) COM_errorLog("COM_recursiveDelete(dir): {$path}");
        return @rmdir($path);
    }
}

function COM_buildOwnerList($fieldName,$owner_id=2)
{
    global $_TABLES;

    $result = DB_query("SELECT * FROM {$_TABLES['users']} WHERE status=3");
    $nRows  = DB_numRows($result);

    $owner_select = '<select name="'.$fieldName.'">';
    for ($i=0; $i<$nRows;$i++) {
        $row = DB_fetchArray($result);
        if ( $row['uid'] == 1 ) {
            continue;
        }
        $owner_select .= '<option value="' . $row['uid'] . '"' . ($owner_id == $row['uid'] ? 'selected="selected"' : '') . '>' . COM_getDisplayName($row['uid']) . '</option>';
    }
    $owner_select .= '</select>';

    return $owner_select;
}

/**
* Turn a piece of HTML into continuous(!) plain text
*
* This function removes HTML tags, line breaks, etc. and returns one long
* line of text. This is useful for word counts (do an explode() on the result)
* and for text excerpts.
*
* @param    string  $text   original text, including HTML and line breaks
* @return   string          continuous plain text
*
*/
function COM_getTextContent($text)
{
    // replace <br> with spaces so that Text<br>Text becomes two words
    $text = preg_replace('/\<br(\s*)?\/?\>/i', ' ', $text);

    // add extra space between tags, e.g. <p>Text</p><p>Text</p>
    $text = str_replace('><', '> <', $text);

    // only now remove all HTML tags
    $text = strip_tags($text);

    // replace all tabs, newlines, and carrriage returns with spaces
    $text = str_replace(array("\011", "\012", "\015"), ' ', $text);

    // replace entities with plain spaces
    $text = str_replace(array('&#20;', '&#160;', '&nbsp;'), ' ', $text);

    // collapse whitespace
    $text = preg_replace('/\s\s+/', ' ', $text);

    return trim($text);
}

function COM_getTooltipStyle()
{
    global $_CONF;

    $retval = 'gl_mootip';

    if ( function_exists('theme_getToolTipStyle') ) {
        $retval = theme_getToolTipStyle();
    }
    return $retval;
}

function COM_getEffectivePermission($owner, $group_id, $perm_owner,$perm_group, $perm_member, $perm_anon)
{
    global $_USER, $_GROUPS;

    $perm = 0;

    if ( COM_isAnonUser()) {
        $perm = $perm_anon;
    } else {
        $perm = $perm_member;
        if ( in_array($item['group_id'],$_GROUPS )) {
            if ( $perm_group > $perm)
                $perm = $perm_group;
        }
        if ( $item['owner_id'] == $_USER['uid'] ) {
            if ( $perm_owner > $perm ) {
                $perm = $perm_owner;
            }
        }
    }
    return $perm;
}

function COM_getStyleCacheLocation()
{
    global $_CONF, $_USER, $_SYSTEM;

    if ( !isset($_CONF['css_cache_filename']) ) {
        $_CONF['css_cache_filename'] = 'style.cache';
    }

    if ( isset($_SYSTEM['use_direct_style_js']) && $_SYSTEM['use_direct_style_js'] ) {
        $cacheFile = $_CONF['path_layout'].$_CONF['css_cache_filename'].'.css';
        $cacheURL  = $_CONF['layout_url'].'/'.$_CONF['css_cache_filename'].'.css';
    } else {
        $cacheFile = $_CONF['path'].'data/layout_cache/'.$_CONF['css_cache_filename'].$_USER['theme'].'.css';
        $cacheURL  = $_CONF['layout_url'].'/css.php?t='.$_USER['theme'];
    }

    return array($cacheFile, $cacheURL);

}

function COM_getJSCacheLocation()
{
    global $_CONF, $_USER, $_SYSTEM;

    if ( !isset($_CONF['js_cache_filename']) ) {
        $_CONF['js_cache_filename'] = 'js.cache';
    }

    if ( isset($_SYSTEM['use_direct_style_js']) && $_SYSTEM['use_direct_style_js'] ) {
        $cacheFile = $_CONF['path_layout'].'/'.$_CONF['js_cache_filename'].'.js';
        $cacheURL  = $_CONF['layout_url'].'/'.$_CONF['js_cache_filename'].'.js';
    } else {
        $cacheFile = $_CONF['path'].'/data/layout_cache/'.$_CONF['js_cache_filename'].'_'.$_USER['theme'].'.js';
        $cacheURL  = $_CONF['layout_url'].'/js.php?t='.$_USER['theme'];
    }

    return array($cacheFile, $cacheURL);
}



/*
 * For backward compatibility
 */
function COM_stripslashes($text)
{
    return $text;
}

function CMT_updateCommentcodes()
{
    global $_CONF, $_TABLES;

    $cleared = 0;

    if ($_CONF['comment_close_rec_stories'] > 0) {
        $results = DB_query("SELECT sid FROM {$_TABLES['stories']} ORDER BY date DESC LIMIT ".(int) $_CONF['comment_close_rec_stories']);
        while ($A = DB_fetchArray($results))  {
            $allowedcomments[] = $A['sid'];
        }
        //update comment codes.
        $sql = '';
        if ( is_array($allowedcomments) ) {
            foreach ($allowedcomments as $sid) {
                $sql .= "AND sid <> '".DB_escapeString($sid)."' ";
            }
            $sql = "UPDATE {$_TABLES['stories']} SET commentcode = 1 WHERE commentcode = 0 " . $sql;
            $result = DB_query($sql,1);
            if ( DB_affectedRows($result) > 0 ) {
                CACHE_remove_instance('story_');
                $cleared = 1;
            }
        }
    }
    $sql = "UPDATE {$_TABLES['stories']} SET commentcode = 1 WHERE UNIX_TIMESTAMP(comment_expire) < UNIX_TIMESTAMP() AND UNIX_TIMESTAMP(comment_expire) <> 0";
    $result = DB_query($sql,1);
    if ( $cleared == 0 ) {
        if ( DB_affectedRows($result) > 0 ) {
            CACHE_remove_instance('story_');
        }
    }
}


function _commentsort($a, $b)
{
    if ( $a['lastdate'] == $b['lastdate'] ) {
        return 0;
    }
    return ($b['lastdate'] < $a['lastdate']) ? -1 : 1;
}


function CTL_clearCacheDirectories($path, $needle = '')
{
    if ( $path[strlen($path)-1] != '/' ) {
        $path .= '/';
    }
    if ($dir = @opendir($path)) {
        while ($entry = readdir($dir)) {
            if ($entry == '.' || $entry == '..' || is_link($entry) || $entry == '.svn' || $entry == 'index.html') {
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


function CTL_clearCache($plugin='')
{
    global $TEMPLATE_OPTIONS, $_CONF, $_SYSTEM;

    if (!empty($plugin)) {
        $plugin = '__' . $plugin . '__';
    }

    if ( !isset($_CONF['css_cache_filename']) ) {
        $_CONF['css_cache_filename'] = 'style.cache';
    }
    if ( !isset($_CONF['js_cache_filename']) ) {
        $_CONF['js_cache_filename'] = 'js.cache';
    }

    CTL_clearCacheDirectories($_CONF['path_data'] . 'layout_cache/', $plugin);

    if ( empty($plugin) ) {
        if ( isset($_SYSTEM['use_direct_style_js']) && $_SYSTEM['use_direct_style_js'] ) {
            foreach (glob($_CONF['path_layout'].$_CONF['css_cache_filename']."*.*") as $filename) {
                @unlink($filename);
            }
            foreach (glob($_CONF['path_layout'].$_CONF['js_cache_filename']."*.*") as $filename) {
                @unlink($filename);
            }
        }
        if ( defined('DVLP_DEBUG') ) {
            COM_errorLog("DEBUG: Cache has been cleared");
        }

    }
}

function _css_out()
{
    global $_CONF, $_SYSTEM, $_VARS, $_USER, $_PLUGINS, $_TABLES;

    $css            = '';
    $file_content   = '';
    $files          = array();

    $outputHandle = outputHandler::getInstance();

    if ( !isset($_CONF['css_cache_filename']) ) {
        $_CONF['css_cache_filename'] = 'style.cache';
    }

    list($cacheFile,$cacheURL) = COM_getStyleCacheLocation();

    // Let's look in the custom directory first...
    if ( file_exists($_CONF['path_layout'] .'custom/style.css') ) {
        $files[] = $_CONF['path_layout'] . 'custom/style.css';
    } else {
        $files[] = $_CONF['path_layout'] . 'style.css';
    }

    if ( file_exists($_CONF['path_layout'] .'custom/style-colors.css') ) {
        $files[] = $_CONF['path_layout'] . 'custom/style-colors.css';
    } else if (file_exists($_CONF['path_layout'].'style-color.css')) {
        $files[] = $_CONF['path_layout'] . 'style-colors.css';
    }

    // need to parse the outputhandler to see if there are any js scripts to load

    $headercss = $outputHandle->getCSSFiles();
    foreach ($headercss as $s ) {
        $files[] = $s;
    }

    /*
     * Check to see if there are any custom CSS files to include
     */
    if ( function_exists( 'CUSTOM_css' )) {
        $customCSS = CUSTOM_css( );
        if ( is_array($customCSS) ) {
            foreach($customCSS AS $item => $file) {
                $files[] = $file;
            }
        }
    }

    if ( is_array($_PLUGINS) ) {
        foreach ( $_PLUGINS as $pi_name ) {
            if ( function_exists('plugin_getheadercss_'.$pi_name) ) {
                $function = 'plugin_getheadercss_'.$pi_name;
                $pHeader = array();
                $pHeader = $function();
                if ( is_array($pHeader) ) {
                    foreach($pHeader AS $item => $file) {
                        $files[] = $file;
                    }
                }
            }
        }
    }

    // default css to support JS libraries
    $files[] = $_CONF['path_html'].'javascript/addons/nivo-slider/nivo-slider.css';
    $files[] = $_CONF['path_html'].'javascript/addons/nivo-slider/themes/default/default.css';

//  Custom CSS added by theme if it supports it...
//    if ( file_exists($_CONF['path_layout'] .'custom.css') ) {
//        $files[] = $_CONF['path_layout'] . 'custom.css';
//    }

    // check cache age & handle conditional request
    if (css_cacheok($cacheFile,$files)){
        return $cacheURL;
    }

    $cacheID = 'css_' . md5( time() );

    DB_query("REPLACE INTO {$_TABLES['vars']} (name, value) VALUES ('cacheid','".$cacheID."')");
    $_VARS['cacheid'] = $cacheID;

    // load files
    if ( is_array($files) ) {
        foreach($files as $file) {
            $file_content = @file_get_contents($file);
            if ( $file_content === false ) {
                COM_errorLog("ERROR: Unable to retrieve CSS file: " . $file);
            } else {
                $css .= $file_content;
            }
            $css .= LB;
        }
    }

    // compress whitespace and comments
    if ($_CONF['compress_css']){
        $css = _css_compress($css);
    }
    // save cache file

    $rc = writeFile_lck($cacheFile,'',$css,'glfusion_css.lck');
    if ( $rc === false ) writeFile_lck($cacheFile,'',$css,'glfusion_css.lck');

    return $cacheURL;
}


function writeFile_lck($filename, $tempfile, $data, $mutex='glfusion.lck')
{
    global $_CONF;

    $retval = false; //assume failure of function
    if (! $tempfile) {
        $tempfile = tempnam(dirname($filename),basename($filename));
    }
    $fullmutex = $_CONF['path_data'] . $mutex;

    $fm = fopen($fullmutex, 'w');
    if (flock($fm, LOCK_EX)) {
        $ft = fopen($tempfile, 'w');
        if (flock($ft, LOCK_EX)) {
            fwrite($ft, $data);
            flock($ft, LOCK_UN);
            fclose($ft);
            @chmod($tempfile, 0644);
            if (rename($tempfile, $filename)) {
                $retval=true; // The only path to success
            } else { // The whole process failed.
                unlink($tempfile);
            }
        } else {
            COM_errorLog("ERROR: Unable to obtain exclusive lock on temp file: " . $tempfile);
        }
        flock($fm,LOCK_UN); // Only unlock mutex when whole atomic action has completed.
        fclose($fm);
    } else {
        COM_errorLog("ERROR: Unable to obtain exclusive lock on ".$mutex);
    }
    return $retval;
}

/**
 * Checks if a CSS Cache file still is valid
 *
 */
function css_cacheok($cache,$files)
{
    $ctime = @filemtime($cache);
    if ( $ctime === false ) {
        return false; // no cache file found
    }

    if ( is_array($files) ) {
        foreach($files as $file){
            $mod_time = @filemtime($file);
            if ( $mod_time === false ) {
//                COM_errorLog("ERROR: Unable to retrieve mod time for CSS file: " . $file);
            } else {
                if ( $mod_time > $ctime ) {
                    return false;
                }
            }
        }
    }
    return true;
}

/**
 * Very simple CSS optimizer
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function _css_compress($css){
    //strip comments through a callback
    $css = preg_replace_callback('#(/\*)(.*?)(\*/)#s','_css_comment_cb',$css);

    //strip (incorrect but common) one line comments
    $css = preg_replace_callback('/^.*\/\/.*$/m','_css_onelinecomment_cb',$css);

    // strip whitespaces
    $css = preg_replace('![\r\n\t ]+!',' ',$css);
    $css = preg_replace('/ ?([;,{}\/]) ?/','\\1',$css);
    $css = preg_replace('/ ?: /',':',$css);

    // number compression
    $css = preg_replace('/([: ])0+(\.\d+?)0*((?:pt|pc|in|mm|cm|em|ex|px)\b|%)(?=[^\{]*[;\}])/', '$1$2$3', $css); // "0.1em" to ".1em", "1.10em" to "1.1em"
    $css = preg_replace('/([: ])\.(0)+((?:pt|pc|in|mm|cm|em|ex|px)\b|%)(?=[^\{]*[;\}])/', '$1$2', $css); // ".0em" to "0"
    $css = preg_replace('/([: ]0)0*(\.0*)?((?:pt|pc|in|mm|cm|em|ex|px)(?=[^\{]*[;\}])\b|%)/', '$1', $css); // "0.0em" to "0"
    $css = preg_replace('/([: ]\d+)(\.0*)((?:pt|pc|in|mm|cm|em|ex|px)(?=[^\{]*[;\}])\b|%)/', '$1$3', $css); // "1.0em" to "1em"
    $css = preg_replace('/([: ])0+(\d+|\d*\.\d+)((?:pt|pc|in|mm|cm|em|ex|px)(?=[^\{]*[;\}])\b|%)/', '$1$2$3', $css); // "001em" to "1em"

    // shorten attributes (1em 1em 1em 1em -> 1em)
    $css = preg_replace('/(?<![\w\-])((?:margin|padding|border|border-(?:width|radius)):)([\w\.]+)( \2)+(?=[;\}]| !)/', '$1$2', $css); // "1em 1em 1em 1em" to "1em"
    $css = preg_replace('/(?<![\w\-])((?:margin|padding|border|border-(?:width)):)([\w\.]+) ([\w\.]+) \2 \3(?=[;\}]| !)/', '$1$2 $3', $css); // "1em 2em 1em 2em" to "1em 2em"

    // shorten colors
    $css = preg_replace("/#([0-9a-fA-F]{1})\\1([0-9a-fA-F]{1})\\2([0-9a-fA-F]{1})\\3(?=[^\{]*[;\}])/", "#\\1\\2\\3", $css);

    return $css;
}

/**
 * Callback for css_compress()
 *
 * Keeps short comments (< 5 chars) to maintain typical browser hacks
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 */
function _css_comment_cb($matches){
    if(strlen($matches[2]) > 4 && $matches[2][0] != '@') return '';
    return $matches[0];
}

/**
 * Callback for css_compress()
 *
 * Strips one line comments but makes sure it will not destroy url() constructs with slashes
 *
 * @param $matches
 * @return string
 */
function _css_onelinecomment_cb($matches) {
    $line = $matches[0];

    $i = 0;
    $len = strlen($line);

    while ($i< $len){
        $nextcom = strpos($line, '//', $i);
        $nexturl = stripos($line, 'url(', $i);

        if($nextcom === false) {
            // no more comments, we're done
            $i = $len;
            break;
        }

        // keep any quoted string that starts before a comment
        $nextsqt = strpos($line, "'", $i);
        $nextdqt = strpos($line, '"', $i);
        if(min($nextsqt, $nextdqt) < $nextcom) {
            $skipto = false;
            if($nextsqt !== false && ($nextdqt === false || $nextsqt < $nextdqt)) {
                $skipto = strpos($line, "'", $nextsqt+1) +1;
            } else if ($nextdqt !== false) {
                $skipto = strpos($line, '"', $nextdqt+1) +1;
            }

            if($skipto !== false) {
                $i = $skipto;
                continue;
            }
        }

        if($nexturl === false || $nextcom < $nexturl) {
            // no url anymore, strip comment and be done
            $i = $nextcom;
            break;
        }

        // we have an upcoming url
        $i = strpos($line, ')', $nexturl);
    }

    return substr($line, 0, $i);
}


function _js_out()
{
    global $_CONF, $_SYSTEM, $_USER, $_PLUGINS, $themeAPI;

    $js = '';
    $file_content = '';
    $files   = array();

    global $_CONF, $_SYSTEM, $_USER, $_PLUGINS;

    $outputHandle = outputHandler::getInstance();

    if ( !isset($_CONF['js_cache_filename']) ) {
        $_CONF['js_cache_filename'] = 'js.cache';
    }
    list($cacheFile,$cacheURL) = COM_getJSCacheLocation();

    // standard JS used by glFusion
    if ( !isset($_SYSTEM['disable_jquery']) || $_SYSTEM['disable_jquery'] == false ) {
        $files[] = $_CONF['path_html'].'javascript/jquery/jquery.min.js';
        $files[] = $_CONF['path_html'].'javascript/jquery/jquery-ui.min.js';
        $files[] = $_CONF['path_html'].'javascript/addons/jqrating.min.js';

        if ( !isset($_SYSTEM['disable_jquery_tooltip']) || $_SYSTEM['disable_jquery_tooltip'] == false ) {
            $files[] = $_CONF['path_html'].'javascript/addons/tooltipster/jquery.tooltipster.min.js';
            $files[] = $_CONF['path_html'].'javascript/addons/tooltipster/tooltip.min.js';
        }
        if ( !isset($_SYSTEM['disable_jquery_menu']) || $_SYSTEM['disable_jquery_menu'] == false ) {
            $files[] = $_CONF['path_html'].'javascript/addons/superfish/superfish.min.js';
            $files[] = $_CONF['path_html'].'javascript/addons/superfish/hoverIntent.min.js';
        }
        if ( !isset($_SYSTEM['disable_jquery_slimbox']) || $_SYSTEM['disable_jquery_slimbox'] == false ) {
            $files[] = $_CONF['path_html'].'javascript/addons/slimbox/slimbox2.min.js';
        }
        if ( !isset($_SYSTEM['disable_jquery_validate']) || $_SYSTEM['disable_jquery_validate'] == false ) {
            $files[] = $_CONF['path_html'].'javascript/addons/jquery-validate/jquery.validate.min.js';
            $files[] = $_CONF['path_html'].'javascript/addons/jquery-validate/additional-methods.min.js';
        }
        if ( !isset($_SYSTEM['disable_jquery_slideshow']) || $_SYSTEM['disable_jquery_slideshow'] == false ) {
            $files[] = $_CONF['path_html'].'javascript/addons/tcycle/jquery.tcycle.min.js';
        }
        $files[] = $_CONF['path_html'].'javascript/addons/nivo-slider/jquery.nivo.slider.pack.js';
    }
    $files[] = $_CONF['path_html'].'javascript/common.min.js';

    // need to parse the outputhandler to see if there are any js scripts to load

    $headerscripts = $outputHandle->getScriptFiles();
    foreach ($headerscripts as $s ) {
        $files[] = $s;
    }

    /*
     * Check to see if the theme has any JavaScript to include...
     */

    $function = 'theme_themeJS';

    if ( function_exists( $function )) {
        $jTheme = $function( );
        if ( is_array($jTheme) ) {
            foreach($jTheme AS $item => $file) {
                $files[] = $file;
            }
        }
    }

    /*
     * Check to see if there are any custom javascript files to include
     */
    if ( function_exists( 'CUSTOM_js' )) {
        $jTheme = CUSTOM_js( );
        if ( is_array($jTheme) ) {
            foreach($jTheme AS $item => $file) {
                $files[] = $file;
            }
        }
    }

    /*
     * Let the plugins add their JavaScript needs here...
     */

    if ( is_array($_PLUGINS) ) {
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
    }

    /*
     * Let the plugins add any global JS variables
     */
    if (is_array($_PLUGINS) ) {
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
    }

    if (js_cacheok($cacheFile,$files)){
        return $cacheURL;
    }

    // add some global variables

    $urlparts = parse_url($_CONF['site_url']);
    if ( isset($urlparts['path']) ) {
        $fileroot = $urlparts['path'];
    } else {
        $fileroot = '';
    }

    $js .= "var glfusionSiteUrl = '".$_CONF['site_url']."';" . LB;
    $js .= "var glfusionFileRoot = '".$fileroot ."';". LB;
    $js .= "var glfusionLayoutUrl = '".$_CONF['layout_url']."';" . LB;
    $js .= "var site_admin_url = '".$_CONF['site_admin_url']."';" . LB;
    if ( isset($_SYSTEM['use_direct_style_js']) && $_SYSTEM['use_direct_style_js'] ) {
        $js .= "var glfusionStyleCSS      = '".$_CONF['site_url'].'/'.$_CONF['css_cache_filename'].$_USER['theme'].'.css?t='.$_USER['theme'] . "';" . LB;
    } else {
        $js .= "var glfusionStyleCSS      = '".$_CONF['site_url']."/css.php?t=" . $_USER['theme'] . "';" . LB;
    }

    // send any global plugin JS vars

    if ( isset($pluginJSvars) && is_array($pluginJSvars) ) {
        foreach ($pluginJSvars AS $name => $value) {
            $js .= "var " . $name . " = '".$value."';";
        }
    }

    if ( is_array($files) ) {
        foreach($files as $file) {
            $file_content = @file_get_contents($file);
            if ( $file_content === false ) {
                COM_errorLog("ERROR: Unable to retrieve JS file: " . $file);
            } else {
                $js .= $file_content;
            }
            $js .= LB;
        }
    }

    $js .= LB; // https://bugzilla.mozilla.org/show_bug.cgi?id=316033

    $rc = writeFile_lck($cacheFile,'',$js,'glfusion_js.lck');
    if ( $rc === false ) writeFile_lck($cacheFile,'',$js,'glfusion_js.lck');

    return $cacheURL;
}


/**
 * Checks if a JavaScript Cache file still is valid
 *
 */
function js_cacheok($cache,$files)
{
    $ctime = @filemtime($cache);
    if (!$ctime)
        return false; //There is no cache

    // now walk the files
    if ( is_array($files) ) {
        foreach($files as $file){
            if (@filemtime($file) > $ctime){
                return false;
            }
        }
    }
    return true;
}


/**
 * This block will display a list of flags that link to the Google automatic
 * translation service.
 *
 * Loads the autotranslations widget block from private/system/lib-widgets.php
 */
function phpblock_autotranslations()
{
   global $_CONF, $LANG_WIDGETS;
   require_once $_CONF['path_system'] . 'lib-widgets.php';
   return(WIDGET_autotranslations());
}

function phpblock_lastlogin()
{
    global $_TABLES, $_CONF, $LANG10;

    $retval = '';

    $result = DB_query("SELECT u.uid AS uid, u.username AS 'username', ui.lastlogin AS 'login' FROM ".$_TABLES['userinfo']." AS ui LEFT JOIN ".$_TABLES['users']." AS u ON ui.uid=u.uid LEFT JOIN {$_TABLES['userprefs']} AS up ON u.uid=up.uid WHERE u.uid NOT IN (1) AND 0 != ui.lastlogin AND up.showonline != 0 ORDER BY ui.lastlogin DESC LIMIT 5");

    $nrows  = DB_numRows ($result);
    if ($nrows > 0) {
        $retval = sprintf($LANG10[29],$nrows);
        for ($i = 0; $i < $nrows; $i++) {
            if (0 < $i)
                $retval .= ', ';
            $A = DB_fetchArray($result);
            $A['username'] = str_replace('$','&#36;',$A['username']);
            $A['user'] = "<a href=\"" . $_CONF['site_url']
                      . "/users.php?mode=profile&amp;uid={$A['uid']}" . "\">{$A['username']}</a>";
            $retval .= $A['user'];
        }
    }
    return $retval;
}


/**
* Provides a drop-down menu (or simple link, if you only have two languages)
* to switch languages. This can be used as a PHP block or called from within
* your theme's header.thtml: <?php print phpblock_switch_language(); ?>
*
* @return   string  HTML for drop-down or link to switch languages
*
*/
function phpblock_switch_language()
{
    global $_CONF;

    $retval = '';

    if ( empty( $_CONF['languages'] ) || empty( $_CONF['language_files'] ) ||
          ( count( $_CONF['languages'] ) != count( $_CONF['language_files'] ))) {
        return $retval;
    }

    $lang = COM_getLanguage();
    $langId = COM_getLanguageId( $lang );

    if ( count( $_CONF['languages'] ) == 2 ) {
        foreach( $_CONF['languages'] as $key => $value ) {
            if ( $key != $langId ) {
                $newLang = $value;
                $newLangId = $key;
                break;
            }
        }

        $switchUrl = COM_buildUrl( $_CONF['site_url'] . '/switchlang.php?lang='
                                   . $newLangId );
        $retval .= COM_createLink($newLang, $switchUrl);
    } else {
        $retval .= '<form name="change" action="'. $_CONF['site_url']
                . '/switchlang.php" method="get">' . LB;
        $retval .= '<input type="hidden" name="oldlang" value="' . $langId
                . '"/>' . LB;

        $retval .= '<select onchange="change.submit()" name="lang">';
        foreach( $_CONF['languages'] as $key => $value ) {
            if ( $lang == $_CONF['language_files'][$key] ) {
                $selected = ' selected="selected"';
            } else {
                $selected = '';
            }
            $retval .= '<option value="' . $key . '"' . $selected . '>'
                    . $value . '</option>' . LB;
        }
        $retval .= '</select>' . LB;
        $retval .= '</form>' . LB;
    }

    return $retval;
}

/**
* Shows who is online in slick little block
* @return   string  HTML string of online users seperated by line breaks.
*/

function phpblock_whosonline()
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $_IMAGE_TYPE;

    $retval = '';

    $expire_time = time() - $_CONF['whosonline_threshold'];

    $byname = 'username';
    if ( $_CONF['show_fullname'] == 1 ) {
        $byname .= ',fullname';
    }
    if ( $_CONF['user_login_method']['openid'] || $_CONF['user_login_method']['3rdparty'] ) {
        $byname .= ',remoteusername,remoteservice';
    }

    $result = DB_query( "SELECT DISTINCT {$_TABLES['sessions']}.uid,{$byname},photo,showonline FROM {$_TABLES['sessions']},{$_TABLES['users']},{$_TABLES['userprefs']} WHERE {$_TABLES['users']}.uid = {$_TABLES['sessions']}.uid AND {$_TABLES['users']}.uid = {$_TABLES['userprefs']}.uid AND start_time >= $expire_time AND {$_TABLES['sessions']}.uid <> 1 ORDER BY {$byname}" );
    $nrows = DB_numRows( $result );

    $num_anon = 0;
    $num_reg  = 0;

    for( $i = 0; $i < $nrows; $i++ ) {
        $A = DB_fetchArray( $result );

        if ( $A['showonline'] == 1 ) {
            $fullname = '';
            if ( $_CONF['show_fullname'] == 1 ) {
                $fullname = $A['fullname'];
            }
            if ( $_CONF['user_login_method']['openid'] || $_CONF['user_login_method']['3rdparty'] ) {
                $username = COM_getDisplayName( $A['uid'], $A['username'],
                        $fullname, $A['remoteusername'], $A['remoteservice'] );
            } else {
                $username = COM_getDisplayName( $A['uid'], $A['username'],
                                                $fullname );
            }

            $url = $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' . $A['uid'];
            $retval .= COM_createLink($username, $url);

            if (!empty( $A['photo'] ) AND $_CONF['allow_user_photo'] == 1) {
                if ($_CONF['whosonline_photo'] == true) {
                    $usrimg = '<img src="' . $_CONF['site_url']
                            . '/images/userphotos/' . $A['photo']
                            . '" alt="" height="30" width="30"/>';
                } else {
                    $usrimg = '<img src="' . $_CONF['layout_url']
                            . '/images/smallcamera.' . $_IMAGE_TYPE
                            . '" border="0" alt=""/>';
                }
                $retval .= '&nbsp;' . COM_createLink($usrimg, $url);
            }

            $retval .= '<br/>';
            $num_reg++;
        } else {
            // this user does not want to show up in Who's Online
            $num_anon++; // count as anonymous
        }
    }

    $num_anon += DB_count( $_TABLES['sessions'], 'uid', 1 );

    if (( $_CONF['whosonline_anonymous'] == 1 ) && COM_isAnonUser() ) {
        // note that we're overwriting the contents of $retval here
        if ( $num_reg > 0 ) {
            $retval = $LANG01[112] . ': ' . COM_numberFormat($num_reg) . '<br/>';
        } else {
            $retval = '';
        }
    }

    if ( $num_anon > 0 ) {
        $retval .= $LANG01[41] . ': ' . COM_numberFormat($num_anon) . '<br/>';
    }

    return $retval;
}


/**
 * Loads the specified library or class normally not loaded by lib-common.php
 *
 * This allows use to move these files, the functions in the files, etc without
 * breaking existing code.
 */
function USES_lib_admin() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'lib-admin.php';
}
function USES_lib_bbcode() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'lib-bbcode.php';
}
function USES_lib_comment() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'lib-comment.php';
}
function USES_lib_comments() {  // depreciated
    global $_CONF;
    require_once $_CONF['path_system'] . 'lib-comment.php';
}
function USES_lib_html2text() {
    global $_CONF;
    require_once $_CONF['path'] . 'lib/html2text/html2text.php';
}
function USES_lib_image() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'imglib/lib-image.php';
}
function USES_lib_install() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'lib-install.php';
}
function USES_lib_pingback() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'lib-pingback.php';
}
function USES_lib_story() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'lib-story.php';
}
function USES_lib_trackback() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'lib-trackback.php';
}
function USES_lib_user() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'lib-user.php';
}
function USES_lib_widgets() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'lib-widgets.php';
}
function USES_class_navbar() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'classes/navbar.class.php';
}
function USES_class_date() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'classes/date.class.php';
}
function USES_class_search() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'classes/search.class.php';
}
function USES_class_story() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'classes/story.class.php';
}
function USES_class_upload() {
    global $_CONF;
    require_once $_CONF['path_system'] . 'classes/upload.class.php';
}

// Now include all plugin functions
if ( is_array($_PLUGINS) ) {
    foreach( $_PLUGINS as $pi_name ) {
        if ( !@include_once $_CONF['path'] . 'plugins/' . $pi_name . '/functions.inc' ) {
            unset($_PLUGINS[array_search($pi_name, $_PLUGINS)]);
        }
    }
    $_PLUGINS = array_values($_PLUGINS);
}

// load custom language file if it exists...
if ( @file_exists($_CONF['path_language'].'custom/'.$_CONF['language'].'.php') ) {
    include_once $_CONF['path_language'].'custom/'.$_CONF['language'].'.php';
}

if ( isset($_SYSTEM['maintenance_mode']) && $_SYSTEM['maintenance_mode'] == 1 ) {
    $_CONF['maintenance_mode'] = 1;
}
if ( isset($_CONF['maintenance_mode']) && $_CONF['maintenance_mode'] == 1 ) {
    if ( isset($_SYSTEM['maintenance_mode']) && ($_SYSTEM['maintenance_mode'] === -1) ) {
        $_CONF['maintenance_mode'] = 0;
    }
}
if ( isset($_CONF['maintenance_mode']) && $_CONF['maintenance_mode'] == 1 && !SEC_inGroup('Root') ) {
    if (empty($_CONF['site_disabled_msg'])) {
        header("HTTP/1.1 503 Service Unavailable");
        header("Status: 503 Service Unavailable");
        echo $_CONF['site_name'] . ' is temporarily undergoing maintenance.  Please check back soon.';
    } else {
        // if the msg starts with http: assume it's a URL we should redirect to
        if (preg_match("/^(https?):/", $_CONF['site_disabled_msg']) === 1) {
            echo COM_refresh($_CONF['site_disabled_msg']);
        } else {
            header("HTTP/1.1 503 Service Unavailable");
            header("Status: 503 Service Unavailable");
            echo $_CONF['site_disabled_msg'];
        }
    }
    exit;
}

// Check and see if any plugins (or custom functions)
// have scheduled tasks to perform
if ( !isset($_VARS['last_scheduled_run'] ) ) {
    $_VARS['last_scheduled_run'] = 0;
}
if ( $_CONF['cron_schedule_interval'] > 0 && COM_onFrontpage() ) {
    if (( $_VARS['last_scheduled_run']
            + $_CONF['cron_schedule_interval'] ) <= time()) {
        DB_query( "UPDATE {$_TABLES['vars']} SET value=UNIX_TIMESTAMP() WHERE name='last_scheduled_run'" );
        PLG_runScheduledTask();
    }
}

if ( function_exists('CUSTOM_splashpage') ) {
    CUSTOM_splashpage();
}

if ( isset($_POST['token_revalidate']) ) {
    require_once $_CONF['path_html'].'revalidate.inc.php';
}
?>
