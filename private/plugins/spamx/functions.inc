<?php
/**
* glFusion CMS
*
* SpamX - SpamX Plugin for glFusion
*
* glFusion Interface functions.inc
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on the prior work
*  Copyright (C) 2004-2010 by the following authors:
*   Authors: Tom Willett     tomw AT pigstye DOT net
*            Dirk Haun       dirk AT haun-online DOT de
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

/*
* load the language file(s), including custom strings if any
*/
$langfile = $_CONF['path'] . 'plugins/spamx/language/' . $_CONF['language'] . '.php';
$custfile = $_CONF['path'] . 'plugins/spamx/language/custom/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
} else {
    $langfile = $_CONF['path'].'plugins/spamx/language/english_utf-8.php';
    $custfile = $_CONF['path'].'plugins/spamx/language/custom/english_utf-8.php';
    include_once $langfile;
    if (file_exists($custfile)) {
        include_once $custfile;
    }
}
/**
* Check and see if we need to load the plugin configuration
*/
if (!isset($_SPX_CONF['timeout'])) {
    $spx_config = config::get_instance();
    $_SPX_CONF = $spx_config->get_config('spamx');
}

require_once $_CONF['path'].'plugins/spamx/spamx.php';

$_CONF['spamx'] = 128;
if ( isset($_SPX_CONF['action_mail']) && $_SPX_CONF['action_mail'] == 1 ) {
    $_CONF['spamx'] += 8;
}
if ( !isset($_CONF['spamx']) || $_CONF['spamx'] == 0 ) {
    $_CONF['spamx'] = 128;
}

// +---------------------------------------------------------------------------+
// | glFusion Plugin API Implementations                                       |
// +---------------------------------------------------------------------------+

/**
* Shows the statistics for the plugin on stats.php.  If $showsitestats is 1
* then we are to only print the overall stats in the 'site statistics' box
* otherwise we show the detailed stats for the plugin
*
* @param    int     $showsitestats  Flag to let us know which stats to get
* @return   string  returns formatted HTML to insert in stats page
* @see      plugin_statssummary_spamx
*
*/
function plugin_showstats_spamx($showsitestats)
{
    global $_CONF, $_SPX_CONF, $_TABLES, $LANG_SX00;

    USES_lib_admin();

    $retval = '';

    if (SEC_hasRights('spamx.admin')) {
        $header_arr = array(
            array('text' => $LANG_SX00['stats_page_title'], 'field' => 'label', 'header_class' => 'stats-header-title'),
            array('text' => $LANG_SX00['stats_entries'], 'field' => 'stats', 'header_class' => 'stats-header-count', 'field_class' => 'stats-list-count'),
        );
        $data_arr = array();
        $text_arr = array('has_menu'     => false,
                          'title'        => $LANG_SX00['stats_headline'],
        );

        // summary by module only
        $sql = "select module,count(*) AS count from {$_TABLES['spamx_stats']} GROUP BY module";
        $result = DB_query($sql);
        while ( ( $row = DB_fetchArray($result)) != NULL ) {
            $data_arr[] = array('label'=> $row['module'],'stats' => $row['count']);
        }
        $retval .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
    }

    return $retval;
}

/**
* New stats plugin API function for proper integration with the site stats
*
* @return   array(item text, item count);
* @see      plugin_showstats_spamx
*
*/
function plugin_statssummary_spamx ()
{
    global $_TABLES, $LANG_SX00;

    $counter = DB_getItem ($_TABLES['vars'], 'value', "name = 'spamx.counter'");

    return array ($LANG_SX00['stats_deleted'], COM_numberFormat ($counter));
}

/**
 * This will put an option for the plugin in the command and control block on moderation.php
 *
 * Add the plugin name, icon and link to the command and control block in moderation.php
 *
 * @return array Array containing (plugin name, admin url, url of plugin icon)
 */
function plugin_cclabel_spamx()
{
    global $_CONF, $LANG_SX00;

    $retval = array();
    if (SEC_hasRights('spamx.admin')) {
        $retval = array($LANG_SX00['plugin_name'],
            $_CONF['site_admin_url'] . '/plugins/spamx/index.php',
            plugin_geticon_spamx ());
    }

    return $retval;
}

/**
 * Returns the administrative option for this plugin
 *
 * Adds the plugin to the Admin menu
 *
 * @return array Array containing (plugin name, plugin admin url, # of items in plugin or '')
 */
function plugin_getadminoption_spamx()
{
    global $_CONF, $LANG_SX00;

    if (SEC_hasRights('spamx.admin')) {
        return array($LANG_SX00['plugin_name'],
            $_CONF['site_admin_url'] . '/plugins/spamx/index.php', 0);
    }
}

/**
 * Returns the current plugin code version
 *
 * @return string    plugin version
 */
function plugin_chkVersion_spamx ()
{
    global $_SPX_CONF;

    return $_SPX_CONF['pi_version'];
}

/**
* Update the Spam-X plugin
*
* @return   int     Number of message to display
*
*/
function plugin_upgrade_spamx()
{
    global $_CONF, $_SPX_CONF, $_TABLES;

    if ( @file_exists($_CONF['path'].'plugins/spamx/upgrade.php') ) {
        require_once $_CONF['path'].'plugins/spamx/upgrade.php';
        return spamx_upgrade();
    } else {
        // upgrades are done by the install script - return a generic error
        Log::write('system',Log::INFO,"Spamx plugin upgrade function not implemented");
        return 3001;
    }
}


/**
 * Actual Plugin Functions here.
 */

/**
 * Check a post for spam
 *
 * @param   string  $comment    comment text
 * @param   int     $action     (former spam action - not used any more)
 * @return  int                 > 0: spam detected, == 0: no spam
 *
 */
function plugin_checkforSpam_spamx ($comment, $action = -1, $data = array())
{
    global $_CONF, $_TABLES, $_USER, $_SPX_CONF;

    // skip spam check for members of the 'spamx Admin' group, if enabled
    if (isset ($_SPX_CONF['admin_override']) && $_SPX_CONF['admin_override']) {
        if (SEC_inGroup ('spamx Admin')) {
            return 0;
        }
    }

    $spamx_path = $_CONF['path'] . 'plugins/spamx/modules/';

    // Set up Spamx_Examine array
    $Spamx_Examine = array ();
    if ( @file_exists($spamx_path.'Formcheck.Examine.class.php')) {
        $Spamx_Examine[] = 'Formcheck';
    }
    if ($dir = @opendir ($spamx_path)) {
        while (($file = readdir ($dir)) !== false) {
            if (is_file ($spamx_path . $file)) {
                if (substr ($file, -18) == '.Examine.class.php') {
                    $sfile = str_replace ('.Examine.class.php', '', $file);
                    if ( $sfile != 'Formcheck') {
                        $Spamx_Examine[] = $sfile;
                    }
                }
            }
        }
        closedir ($dir);
    }

    $res = 0;
    foreach ($Spamx_Examine as $Examine) {
        $filename = $Examine . '.Examine.class.php';
        require_once ($spamx_path . $filename);
        $EX = new $Examine;
        $res = $EX->execute ($comment, $data);
        if ($res == 1) {
            $module = $Examine;
            $dt = new Date('now',$_USER['tzid']);
            $datetime = $dt->toMySQL(true);
            $ip = (isset($data['ip']) ? $data['ip'] : $_SERVER['REAL_ADDR']);
            $email = (isset($data['email']) ? $data['email'] : '' );
            $type  = (isset($data['type']) ? $data['type'] : 'other');
            $username = (isset($data['username']) ? $data['username'] : '');
            $sql =  "INSERT INTO {$_TABLES['spamx_stats']} (module, blockdate, ip, username, email, type) VALUES (";
            $sql .= "'".DB_escapeString($module)."',";
            $sql .= "'".DB_escapeString($datetime)."',";
            $sql .= "'".DB_escapeString($ip)."',";
            $sql .= "'".DB_escapeString($username)."',";
            $sql .= "'".DB_escapeString($email)."',";
            $sql .= "'".DB_escapeString($type)."');";
            DB_query($sql,1);
            break;
        }
    }

    return $res;
}

/**
 * Perform action after spam has been detected
 *
 * @param   string  $comment    comment text
 * @param   int     $action     which action modules to call (sum of module numbers)
 * @return  int                 number of message to display to the spammer
 *
 */
function plugin_spamaction_spamx ($comment, $action,$data)
{
    global $_CONF, $_SPX_CONF;

    $res = 0;

    $spamx_path = $_CONF['path'] . 'plugins/spamx/modules/';
    if (($action == -1) || ($action == '')) {
        $action = $_SPX_CONF['action'];
    }
    // Set up Spamx_Action array
    $Spamx_Action = array ();
    if ($dir = @opendir ($spamx_path)) {
        while (($file = readdir ($dir)) !== false) {
            if (is_file ($spamx_path . $file)) {
                if (substr ($file, -17) == '.Action.class.php') {
                    $sfile = str_replace ('.Action.class.php', '', $file);
                    require_once ($spamx_path . $file);
                    $CM = new $sfile;
                    $Spamx_Action[$sfile] = $CM->number ();
                }
            }
        }
        closedir ($dir);
    }

    foreach ($Spamx_Action as $Act => $Num) {
        if (($action & $Num) == $Num) {
            $AC = new $Act;
            $AC->execute ($comment,$data);

            $res = max ($res, $AC->result ());
        }
    }

    return $res;
}

/**
 * Logs message to spamx.log
 *
 * This will print a message to the spamx log
 *
 * @param   string  $logentry   Message to write to log
 */
function SPAMX_log ($logentry)
{
    global $_CONF, $_USER, $LANG01, $_SPX_CONF;

    if ((!isset ($_SPX_CONF['logging']) || ($_SPX_CONF['logging'] === true)) && !empty ($logentry)) {
        Log::write('system',Log::WARNING,$logentry);
    }
}

/**
* Returns the URL of the plugin's icon
*
* @return   string      URL of the icon
*
*/
function plugin_geticon_spamx ()
{
    global $_CONF;

    return $_CONF['site_admin_url'] . '/plugins/spamx/images/spamx.png';
}

function plugin_getconfigelementhelp_spamx($type, $option, $doclang = 'english' )
{
    global $_CONF;

    $retval = '';

    $baseUrl = $_CONF['site_url'];
    $cfg = 'docs/' . $doclang . '/spamx.html';
    if (@file_exists($_CONF['path_html'] . $cfg)) {
        $descUrl = $baseUrl . '/' . $cfg;
    } else {
        $descUrl = $baseUrl . '/docs/english/spamx.html';
    }
    $retval = $descUrl;

    return array($retval,0);
}

function plugin_templatesetvars_spamx($type, &$template)
{
    global $_CONF, $_SPX_CONF;

    if ( isset($_SPX_CONF['fc_enable']) && $_SPX_CONF['fc_enable'] == 1) {
        $rand = str_pad(rand(0, pow(10, 4)-1), 4, '0', STR_PAD_LEFT);
        $html = '<div class="form-check">';
        $html .= '<input type="hidden" name="fcfield" value="'.$rand.'">';
        $html .= '<label>Enter Email<input type="text" name="fc_email_'.$rand.'" value="" /></label></div>';
        $template->set_var('spamx', $html,false,true);
    }
}

function plugin_getheadercode_spamx()
{
    global $_SPX_CONF;

    $retval = '';

    if ( isset($_SPX_CONF['fc_enable']) && $_SPX_CONF['fc_enable'] == 1) {
        $retval = '<style>div.form-check {display: none;}</style>';
    }
    return $retval;
}
?>