<?php
// +--------------------------------------------------------------------------+
// | Links Plugin - glFusion CMS                                              |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | This is the main page for the glFusion Links Plugin                      |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Tom Willett       - tomw AT pigstye DOT net                     |
// |          Trinity Bays      - trinity93 AT gmail DOT com                  |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
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

/**
 * This is the links page
 *
 * @package Links
 * @subpackage public_html
 * @filesource
 * @version 2.0
 * @since GL 1.4.0
 * @copyright Copyright &copy; 2005-2008
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Tony Bibbs <tony AT tonybibbs DOT com>
 * @author Mark Limburg <mlimburg AT users DOT sourceforge DOT net>
 * @author Jason Whittenburg <jwhitten AT securitygeeks DOT com>
 * @author Tom Willett <tomw AT pigstye DOT net>
 * @author Trinity Bays <trinity93 AT gmail DOT com>
 * @author Dirk Haun <dirk AT haun-online DOT de>
 *
 */

require_once '../lib-common.php';

if (!in_array('links', $_PLUGINS)) {
    COM_404();
    exit;
}

USES_lib_social();

/**
* Create the links list depending on the category given
*
* @param    array   $message    message(s) to display
* @return   string              the links page
*
*/
function links_list($message)
{
    global $_CONF, $_TABLES, $_LI_CONF, $LANG_LINKS_ADMIN, $LANG_LINKS,
           $LANG_LINKS_STATS;

    $cid = $_LI_CONF['root'];
    $display = '';
    $linkCounter = 0;

    if (isset($_GET['category'])) {
        $cid = strip_tags($_GET['category']);
    } elseif (isset($_POST['category'])) {
        $cid = strip_tags($_POST['category']);
    }
    $cat = DB_escapeString($cid);
    $page = 0;
    if (isset ($_GET['page'])) {
        $page = COM_applyFilter ($_GET['page'], true);
    }
    if ($page == 0) {
        $page = 1;
    }

    if (empty($cid)) {
        if ($page > 1) {
            $page_title = sprintf ($LANG_LINKS[114] . ' (%d)', $page);
        } else {
            $page_title = $LANG_LINKS[114];
        }
    } else {
        if ($cid == $_LI_CONF['root']) {
            $category = $LANG_LINKS['root'];
        } else {
            $category = DB_getItem($_TABLES['linkcategories'], 'category',
                                   "cid = '{$cat}'");
        }
        if ($page > 1) {
            $page_title = sprintf ($LANG_LINKS[114] . ': %s (%d)', $category,
                                                                   $page);
        } else {
            $page_title = sprintf ($LANG_LINKS[114] . ': %s', $category);
        }
    }

    // Check has access to this category
    if ($cid != $_LI_CONF['root']) {
        $result = DB_query("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['linkcategories']} WHERE cid='{$cat}'");
        $A = DB_fetchArray($result);
        if (SEC_hasAccess ($A['owner_id'], $A['group_id'], $A['perm_owner'], $A['perm_group'], $A['perm_members'], $A['perm_anon']) < 2) {
            $display .= LINKS_siteHeader ($page_title);
            $display .= COM_showMessage (5, 'links');
            $display .= LINKS_siteFooter ();
            echo $display;
            exit;
        }
    }

    $display .= LINKS_siteHeader ($page_title);

    if (is_array($message) && !empty($message[0])) {
        $display .= COM_showMessageText($message[1],$message[0],true,'error');
    } else if (isset($_REQUEST['msg'])) {
        $msg = COM_applyFilter($_REQUEST['msg'], true);
        if ($msg > 0) {
            $display .= COM_showMessage($msg, 'links');
        }
    }

    $linklist = new Template ($_CONF['path'] . 'plugins/links/templates/');
    $linklist->set_file (array ('linklist' => 'links.thtml',
                                'catlinks' => 'categorylinks.thtml',
                                'link'     => 'linkdetails.thtml',
                                'catnav'   => 'categorynavigation.thtml',
                                'catrow'   => 'categoryrow.thtml',
                                'catcol'   => 'categorycol.thtml',
                                'actcol'   => 'categoryactivecol.thtml',
                                'pagenav'  => 'pagenavigation.thtml',
                                'catdrop'  => 'categorydropdown.thtml'));
    $linklist->set_var('blockheader', COM_startBlock($LANG_LINKS[114]));
    $linklist->set_var('layout_url', $_CONF['layout_url']);

    if ($_LI_CONF['linkcols'] > 0) {
        // Create breadcrumb trail
        $linklist->set_var('breadcrumbs',
                           links_breadcrumbs($_LI_CONF['root'], $cid));

        // Set dropdown for category jump
        $linklist->set_var('lang_go', $LANG_LINKS[124]);
        $linklist->set_var('link_dropdown', links_select_box(2, $cid));

        // Show categories
        $sql = "SELECT cid,pid,category,description FROM {$_TABLES['linkcategories']} WHERE pid='{$cat}'";
        $sql .= COM_getLangSQL('cid', 'AND');
        $sql .= COM_getPermSQL('AND') . " ORDER BY category";
        $result = DB_query($sql);
        $nrows  = DB_numRows ($result);
        if ($nrows > 0) {
            $linklist->set_var ('lang_categories', $LANG_LINKS_ADMIN[14]);
            for ($i = 1; $i <= $nrows; $i++) {
                $C = DB_fetchArray($result);
                // Get number of child links user can see in this category
                $ccid = DB_escapeString($C['cid']);
                $result1 = DB_query("SELECT COUNT(*) AS count FROM {$_TABLES['links']} WHERE cid='{$ccid}'" . COM_getPermSQL('AND'));
                $D = DB_fetchArray($result1);

                // Get number of child categories user can see in this category
                $result2 = DB_query("SELECT COUNT(*) AS count FROM {$_TABLES['linkcategories']} WHERE pid='{$ccid}'" . COM_getPermSQL('AND'));
                $E = DB_fetchArray($result2);

                // Format numbers for display
                $display_count = '';
                // don't show zeroes
                if ($E['count']>0) {
                    $display_count = COM_numberFormat ($E['count']);
                }
                if (($E['count']>0) && ($D['count']>0)) {
                    $display_count .= ', ';
                }
                if ($D['count']>0) {
                    $display_count .= COM_numberFormat ($D['count']);
                }
                // add brackets if child items exist
                if ($display_count<>'') {
                    $display_count = '('.$display_count.')';
                }

                $linklist->set_var ('category_name', $C['category']);
                if ($_LI_CONF['show_category_descriptions']) {
                    $linklist->set_var ('category_description', $C['description']);
                } else {
                    $linklist->set_var ('category_description', '');
                }
                $linklist->set_var ('category_link', $_CONF['site_url'] .
                    '/links/index.php?category=' . urlencode ($C['cid']));
                $linklist->set_var ('category_count', $display_count);
                $linklist->set_var ('width', floor (100 / $_LI_CONF['linkcols']));
                $linklist->set_var ('link_cols',$_LI_CONF['linkcols']);
                if (!empty($cid) && ($cid == $C['cid'])) {
                    $linklist->parse ('category_col', 'actcol', true);
                } else {
                    $linklist->parse ('category_col', 'catcol', true);
                }
                if ($i % $_LI_CONF['linkcols'] == 0) {
                    $linklist->parse ('category_row', 'catrow', true);
                    $linklist->set_var ('category_col', '');
                }
            }
            if ($nrows % $_LI_CONF['linkcols'] != 0) {
                $linklist->parse ('category_row', 'catrow', true);
            }
            $linklist->parse ('category_navigation', 'catnav', true);
        } else {
            $linklist->set_var ('category_navigation', '');
        }
    } else {
        $linklist->set_var ('category_navigation', '');
    }
    if ($_LI_CONF['linkcols'] == 0) {
        $linklist->set_var('category_dropdown', '');
    } else {
        $linklist->parse('category_dropdown', 'catdrop', true);
    }

    $linklist->set_var('site_url', $_CONF['site_url']);
    $linklist->set_var('cid', $cid);
    $linklist->set_var('cid_plain', $cid);
    $linklist->set_var('cid_encoded', urlencode($cid));

    if ( LINKS_canSubmit() ) {
        $linklist->set_var('lang_addalink', $LANG_LINKS[116]);
    }

    // Build SQL for links
    $sql = 'SELECT lid,cid,url,description,title,hits,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon';
    $from_where = " FROM {$_TABLES['links']}";
    if ($_LI_CONF['linkcols'] > 0) {
        if (!empty($cid)) {
            $from_where .= " WHERE cid='" . DB_escapeString($cid) . "'";
        } else {
            $from_where .= " WHERE cid=''";
        }
        $from_where .= COM_getPermSQL ('AND');
    } else {
        $from_where .= COM_getPermSQL ();
    }
    $order = ' ORDER BY cid ASC,title';
    $limit = '';
    if ($_LI_CONF['linksperpage'] > 0) {
        if ($page < 1) {
            $start = 0;
        } else {
            $start = ($page - 1) * $_LI_CONF['linksperpage'];
        }
        $limit = ' LIMIT ' . $start . ',' . $_LI_CONF['linksperpage'];
    }
    $result = DB_query ($sql . $from_where . $order . $limit);
    $nrows = DB_numRows ($result);

    if ($nrows == 0) {
        if (($cid == $_LI_CONF['root']) && ($page <= 1) && $_LI_CONF['show_top10']) {
            $result = DB_query ("SELECT lid,url,title,description,hits,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon FROM {$_TABLES['links']} WHERE (hits > 0)" . COM_getPermSQL ('AND') . " ORDER BY hits DESC LIMIT 10");
            $nrows  = DB_numRows ($result);
            if ($nrows > 0) {
                $linklist->set_var ('link_details', '');
                $linklist->set_var ('link_category',
                                    $LANG_LINKS_STATS['stats_headline']);
                for ($i = 0; $i < $nrows; $i++) {
                    $A = DB_fetchArray ($result);
                    prepare_link_item ($A, $linklist);
                    $linklist->parse ('link_details', 'link', true);
                }
                $linklist->parse ('category_links', 'catlinks', true);
                $linkCounter++;
            }
        }
        $linklist->set_var ('page_navigation', '');
    } else {
        $currentcid = '';
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray($result);
            if (strcasecmp ($A['cid'], $currentcid) != 0) {
                // print the category and link
                if ($i > 0) {
                    $linklist->parse('category_links', 'catlinks', true);
                    $linklist->set_var('link_details', '');
                }
                $currentcid = $A['cid'];
                $currentcategory = DB_getItem($_TABLES['linkcategories'],
                        'category', "cid = '" . DB_escapeString($currentcid) . "'");
                $linklist->set_var('link_category', $currentcategory);
            }

            prepare_link_item($A, $linklist);
            $linklist->parse('link_details', 'link', true);
        }
        $linklist->parse('category_links', 'catlinks', true);

        $result = DB_query ('SELECT COUNT(*) AS count ' . $from_where);
        list($numlinks) = DB_fetchArray ($result);
        $linkCounter += $numlinks;
        $pages = 0;
        if ($_LI_CONF['linksperpage'] > 0) {
            $pages = (int) ($numlinks / $_LI_CONF['linksperpage']);
            if (($numlinks % $_LI_CONF['linksperpage']) > 0 ) {
                $pages++;
            }
        }
        if ($pages > 0) {
            if (($_LI_CONF['linkcols'] > 0) && isset($currentcid)) {
                $catlink = '?category=' . urlencode($currentcid);
            } else {
                $catlink = '';
            }
            $linklist->set_var ('page_navigation',
                    COM_printPageNavigation ($_CONF['site_url']
                        . '/links/index.php' .  $catlink, $page, $pages));
        } else {
            $linklist->set_var ('page_navigation', '');
        }
    }

    if ( $_LI_CONF['linksperpage'] == 'x' ) {
        $social_icons = \glFusion\Social\Social::getShareIcons();
        $linklist->set_var('social_share',$social_icons);
    }

    if ($linkCounter == 0) {
        $linklist->set_var('nolinks',true);
    }

    $linklist->set_var ('blockfooter',COM_endBlock());
    $linklist->parse ('output', 'linklist');
    $display .= $linklist->finish ($linklist->get_var ('output'));

    return $display;
}


/**
* Prepare a link item for rendering
*
* @param    array   $A          link details
* @param    ref     $template   reference of the links template
*
*/
function prepare_link_item ($A, &$template)
{
    global $_LI_CONF, $_CONF, $_USER, $LANG_ADMIN, $LANG_LINKS, $_IMAGE_TYPE, $LANG_LOCALE;

    $url = COM_buildUrl ($_CONF['site_url']
                 . '/links/portal.php?what=link&amp;item=' . $A['lid']);
    $template->set_var ('link_url', $url);
    $template->set_var ('link_actual_url', $A['url']);
    $template->set_var ('link_name', $A['title']);
    $template->set_var ('link_hits', COM_numberFormat ($A['hits']));

    $format = new glFusion\Formatter();
    $format->setNamespace('links');
    $format->setAction('description');
    $format->setType('text');
    $format->setProcessBBCode(false);
    $format->setParseURLs(false);
    $format->setProcessSmilies(false);
    $format->setCensor(true);
    $format->setParseAutoTags(true);

    $linkDesc = $format->parse(htmlspecialchars_decode($A['description']));

//    $linkDesc = PLG_replaceTags(nl2br($A['description']),'links','description');

    $template->set_var ('link_description',$linkDesc);

    $content = $A['title'];

    if ( $_LI_CONF['target_blank'] == 1 ) {
        $attr = array(
            'title' => $A['url'],
            'class' => 'ext-link',
            'target' => '_blank',
            'rel' => 'noopener noreferrer');
    } else {
        $attr = array(
            'title' => $A['url'],
            'class' => 'ext-link'
        );
        if ( isset($_CONF['open_ext_url_new_window']) && $_CONF['open_ext_url_new_window'] == true && stristr($A['url'],$_CONF['site_url']) === false ) {
            $attr['target'] = '_blank';
            $attr['rel'] = 'noopener noreferrer';
        }
    }
    $html = COM_createLink($content, $url, $attr);
    $template->set_var ('link_html', $html);
    if (!COM_isAnonUser() && !SEC_hasRights('links.edit')) {
        $reporturl = $_CONF['site_url']
                 . '/links/index.php?mode=report&amp;lid=' . $A['lid'];
        $template->set_var ('link_broken',
                COM_createLink($LANG_LINKS[117], $reporturl,
                               array('class' => 'pluginSmallText',
                                     'rel'   => 'nofollow'))
        );
    } else {
        $template->set_var ('link_broken', '');
    }

    if ((SEC_hasAccess ($A['owner_id'], $A['group_id'], $A['perm_owner'],
            $A['perm_group'], $A['perm_members'], $A['perm_anon']) == 3) &&
            SEC_hasRights ('links.edit')) {
        $editurl = $_CONF['site_admin_url']
                 . '/plugins/links/index.php?edit=x&amp;lid=' . $A['lid'];
        $template->set_var ('edit_url',$editurl);
        $template->set_var ('link_edit', COM_createLink($LANG_ADMIN['edit'],$editurl));
        $edit_icon = "<img src=\"{$_CONF['layout_url']}/images/edit.$_IMAGE_TYPE\" "
            . "alt=\"{$LANG_ADMIN['edit']}\" title=\"{$LANG_ADMIN['edit']}\"/>";
        $template->set_var ('edit_icon', COM_createLink($edit_icon, $editurl));
    } else {
        $template->set_var ('link_edit', '');
        $template->set_var ('edit_icon', '');
    }

    if ( $_LI_CONF['linksperpage'] == 1 ) {
        $outputHandle = outputHandler::getInstance();
        $outputHandle->addMeta('property','og:site_name',$_CONF['site_name']);
        $outputHandle->addMeta('property','og:locale',isset($LANG_LOCALE) ? $LANG_LOCALE : 'en_US');
        $outputHandle->addMeta('property','og:title',$A['title']);
        $outputHandle->addMeta('property','og:type','website');
        $outputHandle->addMeta('property','og:url',$A['url']);
        if (preg_match('/<img[^>]+src=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/si', $linkDesc, $arrResult)) {
            $outputHandle->addMeta('property','og:image',$arrResult[2]);
        }
    }
}


// MAIN

$display = '';
$mode = '';
$root = $_LI_CONF['root'];
if (isset ($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
}

$message = array();

if ( $mode == 'submit' ) {
    if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) || ($_CONF['submitloginrequired'] == 1))) {
        $display .= LINKS_siteHeader($LANG_LINKS[114]);
        $display .= SEC_loginRequiredForm();
        $display .= LINKS_siteFooter();
        echo $display;
        exit;
    }

    if (SEC_hasRights ("links.edit") || SEC_hasRights ("links.admin"))  {
        echo COM_refresh ($_CONF['site_admin_url']."/plugins/links/index.php?edit=x");
        exit;
    }

    if ( !LINKS_canSubmit() ) {
        echo COM_refresh($_CONF['site_url'].'/links/index.php');
    }

    $slerror = '';
    COM_clearSpeedlimit ($_CONF['speedlimit'], 'submit');
    $last = COM_checkSpeedlimit ('submit');
    if ($last > 0) {
        $slerror .= COM_showMessageText($LANG12[30].$last.sprintf($LANG12[31],$_CONF['speedlimit']),$LANG12[26],true,'error');
    }

    echo LINKS_siteHeader();
    if ( $slerror != '' ) {
        echo $slerror;
    } else {
        echo plugin_submit_links();
    }
    echo LINKS_siteFooter();
    exit;
}

if ( $mode == $LANG12[8] && !empty($LANG12[8]) ) {
    $A = array();
    if ( isset($_POST['url']) ) {
        $A['url'] = $_POST['url'];
    }
    if ( isset($_POST['title']) ) {
        $A['title'] = $_POST['title'];
    }
    if ( isset($_POST['description']) ) {
        $A['description'] = $_POST['description'];
    }
    if ( isset($_POST['categorydd']) ) {
        $A['categorydd'] = $_POST['categorydd'];
    }
    echo LINKS_siteHeader();
    echo plugin_savesubmission_links($A);
    echo LINKS_siteFooter();
    exit;
}


if (($mode == 'report') && (isset($_USER['uid']) && ($_USER['uid'] > 1))) {
    if (isset ($_GET['lid'])) {
        $lid = COM_sanitizeID(COM_applyFilter($_GET['lid']));
    }
    if (!empty($lid)) {
        $lidsl = DB_escapeString($lid);
        $result = DB_query("SELECT url, title FROM {$_TABLES['links']} WHERE lid = '$lidsl'");
        list($url, $title) = DB_fetchArray($result);

        $editurl = $_CONF['site_admin_url']
                 . '/plugins/links/index.php?edit=x&lid=' . $lid;
        $msg = $LANG_LINKS[119] . LB . LB . "$title, <$url>". LB . LB
             .  $LANG_LINKS[120] . LB . '<' . $editurl . '>' . LB . LB
             .  $LANG_LINKS[121] . $_USER['username'] . ', IP: '
             . $_SERVER['REMOTE_ADDR'];
        $to = array();
        $to = COM_formatEmailAddress('',$_CONF['site_mail']);
        COM_mail($to, $LANG_LINKS[118], $msg);
        $message = array($LANG_LINKS[123], $LANG_LINKS[122]);
    }
}

if (COM_isAnonUser() && (($_CONF['loginrequired'] == 1) || ($_LI_CONF['linksloginrequired'] == 1))) {
    $display .= LINKS_siteHeader($LANG_LINKS[114]);
    $display .= SEC_loginRequiredForm();
    $display .= LINKS_siteFooter();
    echo $display;
    exit;
} else {
    $display .= links_list($message);
}

$display .= LINKS_siteFooter ();

echo $display;

?>