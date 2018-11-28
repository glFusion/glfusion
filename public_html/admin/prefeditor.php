<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | prefeditor.php                                                           |
// |                                                                          |
// | glFusion user preference editor.                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2010-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

// Set this to true to get various debug messages from this script
$_USER_VERBOSE = false;

require_once '../lib-common.php';
require_once 'auth.inc.php';
USES_lib_user();
USES_lib_admin();

$display = '';

// Make sure user has access to this page
if (!SEC_hasRights('user.edit')) {
    $display .= COM_siteHeader ('menu', $MESSAGE[30]);
    $display .= COM_showMessageText($MESSAGE[37],$MESSAGE[30],true,'error');
    $display .= COM_siteFooter ();
    COM_accessLog("User {$_USER['username']} tried to access the user administration screen.");
    echo $display;
    exit;
}

function editPreferences()
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG04, $LANG28, $LANG_confignames, $LANG_configselects, $LANG_configSelect,$_IMAGE_TYPE;

    $retval = '';

    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/user.php',
              'text' => $LANG_ADMIN['admin_users']),
        array('url' => $_CONF['site_admin_url'] . '/user.php?edit=x',
              'text' => $LANG_ADMIN['create_new']),
        array('url' => $_CONF['site_admin_url'] . '/user.php?import=x',
              'text' => $LANG28[23]),
        array('url' => $_CONF['site_admin_url'] . '/user.php?batchadmin=x',
              'text' => $LANG28[54]),
        array('url' => $_CONF['site_admin_url'] . '/prefeditor.php',
                          'text' => $LANG28[95],'active'=>true),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $T = new Template ($_CONF['path_layout'] . 'admin/user/');
    $T->set_file('editor','prefeditor.thtml');

    $T->set_var(array(
        'lang_save'             => $LANG_ADMIN['save'],
        'lang_cancel'           => $LANG_ADMIN['cancel'],
        'lang_language'         => $LANG04[73],
        'lang_theme'            => $LANG04[72],
        'lang_cooktime'         => $LANG04[68],
        'lang_noicons'          => $LANG04[40],
        'lang_noboxes'          => $LANG04[44],
        'lang_maxstories'       => $LANG04[43],
        'lang_timezone'         => $LANG04[158],
        'lang_dateformat'       => $LANG04[42],
        'lang_search_format'    => $LANG_confignames['Core']['search_show_type'],
        'lang_displaymode'      => $LANG28[97],
        'lang_sortorder'        => $LANG28[98],
        'lang_commentlimit'     => $LANG04[59],
        'lang_emailfromadmin'   => $LANG04[100],
        'lang_emailfromuser'    => $LANG04[102],
        'lang_showonline'       => $LANG04[104],
        'lang_confirm'          => $LANG28[91],
        'lang_attribute'        => $LANG28[92],
        'lang_value'            => $LANG28[93],
        'lang_selected'         => $LANG28[94],
    ));

    // Get available languages
    $language = MBYTE_languageList ($_CONF['default_charset']);
    // build language select
    $selection = '<select id="language" name="language">' . LB;
    foreach ($language as $langFile => $langName) {
        $selection .= '<option value="' . $langFile . '"';
        if ( $langFile == $_CONF['language'] ) {
            $selection .= ' selected="selected"';
        }
        $selection .= '>' . $langName . '</option>' . LB;
    }
    $selection .= '</select>';

    $T->set_var('language_selector', $selection);

    if ($_CONF['allow_user_themes'] == 1) {
        $selection = '<select id="theme" name="theme">' . LB;
        $usertheme = $_CONF['theme'];
        $themeFiles = COM_getThemes ();
        usort ($themeFiles,function ($a,$b) {return strcasecmp($a,$b);});
        foreach ($themeFiles as $theme) {
            $selection .= '<option value="' . $theme . '"';
            if ($usertheme == $theme) {
                $selection .= ' selected="selected"';
            }
            $words = explode ('_', $theme);
            $bwords = array ();
            foreach ($words as $th) {
                if ((strtolower ($th{0}) == $th{0}) &&
                    (strtolower ($th{1}) == $th{1})) {
                    $bwords[] = strtoupper ($th{0}) . substr ($th, 1);
                } else {
                    $bwords[] = $th;
                }
            }
            $selection .= '>' . implode (' ', $bwords) . '</option>' . LB;
        }
        $selection .= '</select>';
        $T->set_var('theme_selector', $selection);
    } else {
        $T->set_var('theme_selector',$_CONF['theme']);
    }

    $selection  = '<select id="cooktime" name="cooktime">' . LB;
    $selection .= COM_optionList($_TABLES['cookiecodes'],'cc_value,cc_descr',2678400, 0);
    $selection .= '</select>';
    $T->set_var('cooktime_selector', $selection);

    $selection = Date::getTimeZoneDropDown($_CONF['timezone'],
            array('id' => 'tzid', 'name' => 'tzid'));
    $T->set_var('timezone_selector', $selection);

    $selection = '<select id="dfid" name="dfid">' . LB
               . COM_optionList ($_TABLES['dateformats'], 'dfid,description',
                                 0) . '</select>';
    $T->set_var('dateformat_selector', $selection);
    $search_result_select  = '<select name="search_result_format" id="search_result_format">'.LB;
    if (isset($LANG_configSelect['Core'])) {
        $cfgSelect = $LANG_configSelect['Core'][18];
    } else {
        $cfgSelect = array_flip($LANG_configselects['Core'][18]);
    }
    foreach ($cfgSelect AS $type => $name ) {
        $search_result_select .= '<option value="'. $type . '"' . ('google' == $type ? 'selected="selected"' : '') . '>'.$name.'</option>'.LB;
    }
    $search_result_select .= '</select>';
    $T->set_var('search_result_select',$search_result_select);

    $selection = '<select id="commentmode" name="commentmode">';
    $selection .= COM_optionList ($_TABLES['commentmodes'], 'mode,name',
                                  $_CONF['comment_mode']);
    $selection .= '</select>';
    $T->set_var('displaymode_selector', $selection);

    $selection = '<select id="commentorder" name="commentorder">';
    $selection .= COM_optionList ($_TABLES['sortcodes'], 'code,name',
                                  0);
    $selection .= '</select>';
    $T->set_var('sortorder_selector', $selection);

    $T->set_var('gltoken_name', CSRF_TOKEN);
    $T->set_var('gltoken', SEC_createToken());

    $retval .= COM_startBlock($LANG28[95], '',COM_getBlockTemplate('_admin_block', 'header'));
    $retval .= ADMIN_createMenu(
        $menu_arr,
        $LANG28[96],
        $_CONF['layout_url'] . '/images/icons/user.' . $_IMAGE_TYPE
    );
    $retval .= $T->finish ($T->parse ('output', 'editor'));
    $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

    return $retval;
}

function applyPreferences()
{
    global $_CONF, $_TABLES;

    $retval = '';

    $users_sql = '';
    $prefs_sql = '';
    $index_sql = '';
    $comment_sql = '';

    $users_first = 0;
    $prefs_first = 0;
    $index_first = 0;
    $comment_first = 0;

    $enabledOptions = array();

    $enabledOptions = (isset($_POST['enabled']) ? $_POST['enabled'] : array());
    if ( is_array($enabledOptions) ) {
        foreach($enabledOptions AS $attribute) {
            switch ($attribute) {
                case 'cooktime' : // users - cookietimeout
                    if ( isset($_POST['cooktime']) ) {
                        $cooktime = COM_applyFilter($_POST['cooktime'],true);
                        if ( $users_first ) {
                            $users_sql .= ',';
                        } else {
                            $users_first++;
                        }
                        $users_sql .= 'cookietimeout='.$cooktime;
                    }
                    break;
                case 'language' :   // users - lanaguage
                    if ( isset($_POST['language']) ) {
                        $language = COM_applyFilter($_POST['language']);
                        if ( $users_first ) {
                            $users_sql .= ',';
                        } else {
                            $users_first++;
                        }
                        $users_sql .= 'language="'.DB_escapeString($language).'" ';
                    }
                    break;
                case 'theme' :   // users - theme
                    if ( isset($_POST['theme']) ) {
                        $theme = COM_applyFilter($_POST['theme']);
                        if ( $users_first ) {
                            $users_sql .= ',';
                        } else {
                            $users_first++;
                        }
                        $users_sql .= 'theme="'.DB_escapeString($theme).'" ';
                    }
                    break;
                case 'noicons' :    // userprefs - noicons
                    if ( isset($_POST['noicons']) && $_POST['noicons'] == 'on' ) {
                        $noicons = 1;
                    } else {
                        $noicons = 0;
                    }
                    if ( $prefs_first ) {
                        $prefs_sql .= ',';
                    } else {
                        $prefs_first++;
                    }
                    $prefs_sql .= 'noicons='.$noicons;
                    break;
                case 'noboxes' : // userindex - noboxes
                    if ( isset($_POST['noboxes']) && $_POST['noboxes'] == 'on' ) {
                        $noboxes = 1;
                    } else {
                        $noboxes = 0;
                    }
                    if ($index_first) {
                        $index_sql .= ',';
                    } else {
                        $index_first++;
                    }
                    $index_sql .= 'noboxes='.$noboxes;
                    break;
                case 'maxstories' : // userindex - maxstories
                    if ( isset($_POST['maxstories']) ) {
                        $maxstories = COM_applyFilter($_POST['maxstories'],true);
                        if ( $index_first ) {
                            $index_sql .= ',';
                        } else {
                            $index_first++;
                        }
                        $index_sql .= 'maxstories='.$maxstories;
                    }
                    break;
                case 'tzid' :   // userprefs - tzid
                    if ( isset($_POST['tzid']) ) {
                        $tzid = COM_applyFilter($_POST['tzid']);
                        if ( $prefs_first ) {
                            $prefs_sql .= ',';
                        } else {
                            $prefs_first++;
                        }
                        $prefs_sql .= 'tzid="'.DB_escapeString($tzid).'"';
                    }
                    break;
                case 'dfid' :   // userprefs - dfid
                    if ( isset($_POST['dfid']) ) {
                        $dfid = COM_applyFilter($_POST['dfid'],true);
                        if ( $prefs_first ) {
                            $prefs_sql .= ',';
                        } else {
                            $prefs_first++;
                        }
                        $prefs_sql .= 'dfid='.$dfid;
                    }
                    break;
                case 'search_result_format' :   // userprefs - search_result_format
                    if ( isset($_POST['search_result_format']) ) {
                        $format = $_POST['search_result_format'];
                        $search_result_format = ($format == 'google' ? 'google' : 'table');
                        if ( $prefs_first ) {
                            $prefs_sql .= ',';
                        } else {
                            $prefs_first++;
                        }
                        $prefs_sql .= 'search_result_format="'.DB_escapeString($search_result_format).'"';
                    }
                    break;
                case 'commentmode' :    //usercomment - commentmode
                    if ( isset($_POST['commentmode']) ) {
                        $commentmode = COM_applyFilter($_POST['commentmode']);
                        if ( $comment_first ) {
                            $comment_sql .= ',';
                        } else {
                            $comment_first++;
                        }
                        $comment_sql .= 'commentmode="'.DB_escapeString($commentmode).'"';
                    }
                    break;
                case 'commentorder' :   // usercomment - commentorder
                    if ( isset($_POST['commentorder']) ) {
                        $commentorder = ($_POST['commentorder'] == 'ASC' ? 'ASC' : 'DESC');
                        if ( $comment_first ) {
                            $comment_sql .= ',';
                        } else {
                            $comment_first++;
                        }
                        $comment_sql .= 'commentorder="'.DB_escapeString($commentorder).'"';
                    }
                    break;
                case 'commentlimit' :   // usercomment - commentlimit
                    if ( isset($_POST['commentlimit']) ) {
                        $commentlimit = COM_applyFilter($_POST['commentlimit'],true);
                        if ( $commentlimit < 1 ) {
                            $commentlimit = 1;
                        }
                        if ( $comment_first ) {
                            $comment_sql .= ',';
                        } else {
                            $comment_first++;
                        }
                        $comment_sql .= 'commentlimit='.$commentlimit;
                    }
                    break;
                case 'emailfromuser' :  // userprefs - emailfromuser
                    if ( isset($_POST['emailfromuser']) && $_POST['emailfromuser'] == 'on' ) {
                        $emailfromuser = 1;
                    } else {
                        $emailfromuser = 0;
                    }
                    if ( $prefs_first ) {
                        $prefs_sql .= ',';
                    } else {
                        $prefs_first++;
                    }
                    $prefs_sql .= 'emailfromuser='.$emailfromuser;
                    break;
                case 'emailfromadmin' : // userprefs - emailfromadmin
                    if ( isset($_POST['emailfromadmin']) && $_POST['emailfromadmin'] == 'on' ) {
                        $emailfromadmin = 1;
                    } else {
                        $emailfromadmin = 0;
                    }
                    if ( $prefs_first ) {
                        $prefs_sql .= ',';
                    } else {
                        $prefs_first++;
                    }
                    $prefs_sql .= 'emailfromadmin='.$emailfromadmin;
                    break;
                case 'showonline' : // userprefs - showonline
                    if ( isset($_POST['showonline']) && $_POST['showonline'] == 'on' ) {
                        $showonline = 1;
                    } else {
                        $showonline = 0;
                    }
                    if ( $prefs_first ) {
                        $prefs_sql .= ',';
                    } else {
                        $prefs_first++;
                    }
                    $prefs_sql .= 'showonline='.$showonline;
                    break;
            }
        }
    }

    // now execute the queries...

    if ( $users_sql != '' ) {
        $sql = "UPDATE {$_TABLES['users']} SET " . $users_sql . " WHERE uid > 1";
        DB_query($sql);
    }
    if ( $prefs_sql != '' ) {
        $sql = "UPDATE {$_TABLES['userprefs']} SET " . $prefs_sql . " WHERE uid > 1";
        DB_query($sql);
    }
    if ( $index_sql != '' ) {
        $sql = "UPDATE {$_TABLES['userindex']} SET " . $index_sql . " WHERE uid > 1";
        DB_query($sql);
    }
    if ( $comment_sql != '' ) {
        $sql = "UPDATE {$_TABLES['usercomment']} SET " . $comment_sql. " WHERE uid > 1";
        DB_query($sql);
    }

    COM_setMessage(501);
    echo COM_refresh($_CONF['site_admin_url'].'/user.php');
    exit;
}

if ( isset($_POST['cancel']) ) {
    echo COM_refresh($_CONF['site_admin_url'].'/user.php');
    exit;
}

$mode = '';
if ( isset($_POST['submit']) ) {
    $mode = 'apply';
}

switch ( $mode ) {
    case 'apply' :
        if ( SEC_checkToken() ) {
            $content = applyPreferences();
        }
    default :
        $content = editPreferences();
        break;
}

$display = COM_siteHeader();
$display .= $content;
$display .= COM_siteFooter();
echo $display;
exit;
?>
