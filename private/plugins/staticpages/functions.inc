<?php
// +--------------------------------------------------------------------------+
// | Static Pages Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs       - tony AT tonybibbs DOT com                    |
// |          Tom Willett      - twillett AT users DOT sourceforge DOT net    |
// |          Blaine Lang      - blaine AT portalparts DOT com                |
// |          Dirk Haun        - dirk AT haun-online DOT de                   |
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

if (!defined ('GVERSION')) {
    die('This file can not be used on its own.');
}

$plugin_path = $_CONF['path'] . 'plugins/staticpages/';
$lang = $_CONF['language'];
$langfile = $plugin_path . 'language/' . $lang . '.php';

if (file_exists($langfile)) {
    require_once $langfile;
} else {
    $lang = 'english';
    require_once $plugin_path . 'language/english.php';
}

// Include the custom language file, if any
$langfile = $plugin_path . 'language/custom/' . $lang . '.php';
if (@file_exists($langfile)) {
    require_once $langfile;
}

/*
* Check and see if we need to load the plugin configuration
*/
if (!isset($_SP_CONF['allow_php'])) {
    require_once $_CONF['path_system'] . 'classes/config.class.php';

    $sp_config = config::get_instance();
    $_SP_CONF = $sp_config->get_config('staticpages');
}

require_once $_CONF['path'].'plugins/staticpages/staticpages.php';

// +---------------------------------------------------------------------------+
// | Plugin API - Services                                                     |
// +---------------------------------------------------------------------------+

include_once $plugin_path . 'services.inc.php';

// +---------------------------------------------------------------------------+
// | glFusion Plugin API Implementations                                       |
// +---------------------------------------------------------------------------+


/**
* Returns the items for this plugin that should appear on the main menu
*
* NOTE: this MUST return the url/value pairs in the following format
* $<arrayname>[<label>] = <url>
*
*/
function plugin_getmenuitems_staticpages()
{
    global $_CONF, $_TABLES, $_SP_CONF;

    $order = '';
    if (!empty ($_SP_CONF['sort_menu_by'])) {
        $order = ' ORDER BY ';
        if ($_SP_CONF['sort_menu_by'] == 'date') {
            $order .= 'sp_date DESC';
        } else if ($_SP_CONF['sort_menu_by'] == 'label') {
            $order .= 'sp_label';
        } else if ($_SP_CONF['sort_menu_by'] == 'title') {
            $order .= 'sp_title';
        } else { // default to "sort by id"
            $order .= 'sp_id';
        }
    }

    $result = DB_query('SELECT sp_id, sp_label FROM ' . $_TABLES['staticpage'] . ' WHERE sp_onmenu = 1 AND sp_status = 1' . COM_getPermSql('AND') . COM_getLangSql('sp_id', 'AND') . $order);
    $nrows = DB_numRows ($result);
    $menuitems = array ();
    for ($i = 0; $i < $nrows; $i++) {
        $A = DB_fetchArray ($result);
        $menuitems[$A['sp_label']] = COM_buildURL ($_CONF['site_url'] . '/page.php?page=' . $A['sp_id']);
    }

    return $menuitems;
}

/**
* glFusion is checking to see if this plugin supports comments.
*
* NOTE: to support comments you must used the same date/time based ID for your
* widget.  In other words, to make primary keys for your plugin you should call
* makesid().  Comments are keyed off of that...it is a limitation on how glFusion
* does comments.
*
*/
function plugin_commentsupport_staticpages()
{
    // Static Pages will use comments
    return true;
}

function plugin_getcommenturlid_staticpages( ) {
    global $_CONF;
    $retval = array();
    $retval[] = $_CONF['site_url'] . '/page.php';
    $retval[] = 'page';
    $retval[] = 'cmtpage=';
    return $retval;
}

/**
* Plugin should display [a] comment[s]
*
* @param   string  $id     Unique idenifier for item comment belongs to
* @param   int     $cid    Comment id to display (possibly including sub-comments)
* @param   string  $title  Page/comment title
* @param   string  $order  'ASC' or 'DSC' or blank
* @param   string  $format 'threaded', 'nested', or 'flat'
* @param   int     $page   Page number of comments to display
* @param   boolean $view   True to view comment (by cid), false to display (by $pid)
* @return  mixed   results of calling the plugin_displaycomment_ function
*/
function plugin_displaycomment_staticpages ($id, $cid, $title, $order, $format, $page, $view)
{
    global $_TABLES, $LANG_STATIC, $LANG_ACCESS;

    $retval = '';

    $retval = SP_returnStaticpage($id,'autotag', $order,$format);
    return $retval;
}

/**
 * Static Page saves a comment
 *
 * @param   string  $title  comment title
 * @param   string  $comment comment text
 * @param   string  $id     Item id to which $cid belongs
 * @param   int     $pid    comment parent
 * @param   string  $postmode 'html' or 'text'
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function plugin_savecomment_staticpages($title, $comment, $id, $pid, $postmode)
{
    global $_CONF, $_TABLES, $LANG03, $_USER;

    $retval = '';

    $commentcode = DB_getItem ($_TABLES['staticpage'], 'commentcode',
                               "sp_id = '".DB_escapeString($id)."'");
    if ($commentcode != 0) {
        return COM_refresh ($_CONF['site_url'] . '/index.php');
    }

    $ret = CMT_saveComment ($title, $comment, $id, $pid, 'staticpages', $postmode);
    if ($ret > 0) { // failure //FIXME: some failures should not return to comment form
        $retval .= CMT_commentForm ($title, $comment, $id, $pid, 'staticpages',
                                   $LANG03[14], $postmode);
    } else { // success
        $url = COM_buildUrl($_CONF['site_url'] . '/page.php?page='
                            . $id);
		CACHE_remove_instance('whatsnew_');
		echo COM_refresh($url);
		exit;
    }
    CACHE_remove_instance('whatsnew_');
    return $retval;
}

/**
 * staticpages: delete a comment
 *
 * @param   int     $cid    Comment to be deleted
 * @param   string  $id     Item id to which $cid belongs
 * @return  mixed   false for failure, HTML string (redirect?) for success
 */
function plugin_deletecomment_staticpages($cid, $id)
{
    global $_CONF, $_TABLES, $_USER;

    $retval = '';

    $has_editPermissions = SEC_hasRights ('staticpages.edit');
    $result = DB_query ("SELECT owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon "
        . "FROM {$_TABLES['staticpage']} WHERE sp_id = '".DB_escapeString($id)."'");
    $A = DB_fetchArray ($result);

    if ($has_editPermissions && SEC_hasAccess ($A['owner_id'],
            $A['group_id'], $A['perm_owner'], $A['perm_group'],
            $A['perm_members'], $A['perm_anon']) == 3) {
        CMT_deleteComment($cid, $id, 'staticpages');
        $url = COM_buildUrl($_CONF['site_url'] . '/page.php?page='
                            . $id);
        $retval .= COM_refresh($url);
    } else {
        COM_errorLog ("User {$_USER['username']} (IP: {$_SERVER['REMOTE_ADDR']}) "
                    . "tried to illegally delete comment $cid from staticpage $id");
        $retval .= COM_refresh ($_CONF['site_url'] . '/index.php');
    }
    CACHE_remove_instance('whatsnew_');
    echo $retval;
    exit;
}

function plugin_getwhatsnewcomment_staticpages()
{
    global $_CONF, $_TABLES, $_USER;

    $commentrow = array();

    $stwhere = '';
    if( !COM_isAnonUser() ) {
        $stwhere .= "({$_TABLES['staticpage']}.owner_id IS NOT NULL AND {$_TABLES['staticpage']}.perm_owner IS NOT NULL) OR ";
        $stwhere .= "({$_TABLES['staticpage']}.group_id IS NOT NULL AND {$_TABLES['staticpage']}.perm_group IS NOT NULL) OR ";
        $stwhere .= "({$_TABLES['staticpage']}.perm_members IS NOT NULL)";
    } else {
        $stwhere .= "({$_TABLES['staticpage']}.perm_anon IS NOT NULL)";
    }
    $stwhere .= " AND ({$_TABLES['staticpage']}.sp_status = 1)";

    $sql = "SELECT DISTINCT COUNT(*) AS dups, {$_TABLES['staticpage']}.sp_title AS title,
            {$_TABLES['staticpage']}.sp_id AS sid, UNIX_TIMESTAMP(max({$_TABLES['comments']}.date)) AS lastdate
            FROM {$_TABLES['comments']} LEFT JOIN {$_TABLES['staticpage']} ON (({$_TABLES['staticpage']}.sp_id = {$_TABLES['comments']}.sid)"
            . COM_getPermSQL( 'AND', 0, 2, $_TABLES['staticpage'] ) .
            " AND ({$_TABLES['staticpage']}.commentcode >= 0)" .
            COM_getLangSQL( 'sp_id', 'AND', $_TABLES['staticpage'] ) . ")
            WHERE ({$_TABLES['comments']}.date >= (DATE_SUB(NOW(), INTERVAL {$_CONF['newcommentsinterval']} SECOND)))
            AND ((({$stwhere}))) GROUP BY {$_TABLES['comments']}.sid,type, {$_TABLES['staticpage']}.sp_title,
            {$_TABLES['staticpage']}.sp_title, {$_TABLES['staticpage']}.sp_id
            ORDER BY 4 DESC LIMIT 15";

    $result = DB_query( $sql );
    $nrows = DB_numRows( $result );
    if ( $nrows > 0 ) {
        for ($x = 0; $x < $nrows; $x++ ) {
            $A = DB_fetchArray($result);

            $cmtReturn['url'] = COM_buildUrl($_CONF['site_url'].'/page.php?page='.$A['sid']).'#comments';
            $cmtReturn['type'] = 'staticpage';
            $cmtReturn['dups'] = $A['dups'];
            $cmtReturn['title'] = $A['title'];
            $cmtReturn['lastdate'] = $A['lastdate'];
            $commentrow[] = $cmtReturn;
        }
    }
    return $commentrow;
}

/**
* Helper function: Count static pages visible to the current user
*
* @return   int     number of pages
*
*/
function SP_countVisiblePages ()
{
    global $_TABLES;

    $perms = SP_getPerms ();
    if (!empty ($perms)) {
        $perms = ' WHERE ' . $perms;
    }
    $result = DB_query ("SELECT COUNT(*) AS cnt FROM {$_TABLES['staticpage']}" . $perms . " AND sp_status = 1");
    $A = DB_fetchArray ($result);

    return $A['cnt'];
}

/**
* Prepare static page for display.
*
* @param    string  $page       static page id
* @param    array   $A          static page data
* @param    string  comment_order sorting of comments
* @param    string  comment_mode
* @return   string              HTML for the static page
*
*/
function SP_displayPage ($page, $A, $comment_order = 'ASC', $comment_mode ='nested')
{
    global $_CONF, $_TABLES, $_USER,
           $LANG01, $LANG11, $LANG_STATIC, $_IMAGE_TYPE, $_SP_CONF;

    $retval = '';
    $displaypage = '';
    $headerOption = '';
    $footerOption = '';

    $outputHandle = outputHandler::getInstance();

    $sp_url = COM_buildUrl($_CONF['site_url'] . '/page.php?page='.$page);
    $outputHandle->addLink('canonical', $sp_url);


    if ($A['sp_format'] == 'allblocks' OR $A['sp_format'] == 'leftblocks') {
        $headerOption = 'menu';
    } else {
        if ($A['sp_format'] <> 'blankpage') {
            $headerOption = 'none';
        }
    }
    if (($A['sp_inblock'] == 1) && ($A['sp_format'] != 'blankpage')) {
        $retval .= COM_startBlock ($A['sp_title'], $A['sp_help'],
                        COM_getBlockTemplate ('_staticpages_block', 'header'));
    }

    $spage = new Template( $_CONF['path'] . 'plugins/staticpages/templates/' );
    $spage->set_var('site_admin_url', $_CONF['site_admin_url']);
    $spage->set_file( array('page'=>'staticpage.thtml',
        'comments' => 'spcomments.thtml'));

    if ($A['sp_format'] <> 'blankpage') {
        if ($_CONF['hideprintericon'] == 0) {
            $icon_url = $_CONF['layout_url'] . '/images/print.' . $_IMAGE_TYPE;
            $attr = array('title' => $LANG_STATIC['printable_format']);
            $printicon = COM_createImage($icon_url, $LANG01[65], $attr);
            $print_url = COM_buildURL ($_CONF['site_url']
                . '/page.php?page=' . $page . '&amp;disp_mode=print');
            $icon = COM_createLink($printicon, $print_url,array('rel' => 'nofollow'));
            $spage->set_var('print_icon', $icon);
            $spage->set_var('link_print',$print_url);
        }
        if ((SEC_hasAccess ($A['owner_id'], $A['group_id'], $A['perm_owner'],
                $A['perm_group'], $A['perm_members'], $A['perm_anon']) == 3) &&
                SEC_hasRights ('staticpages.edit')) {
            $icon_url = $_CONF['layout_url'] . '/images/edit.' . $_IMAGE_TYPE;
            $attr = array('title' => $LANG_STATIC['edit']);
            $editiconhtml = COM_createImage($icon_url, $LANG_STATIC['edit'], $attr);
            $attr = array('rel' => 'nofollow','class' => 'editlink','title' => $LANG_STATIC['edit']);
            $url = $_CONF['site_admin_url']
                . '/plugins/staticpages/index.php?edit=x&amp;sp_id=' . $page;
            $icon =
                '&nbsp;' . COM_createLink(
                $editiconhtml, //display
                $url,  //target
                $attr //other attributes
            );
            $spage->set_var('edit_icon', $icon);
            $spage->set_var('link_edit',$url);
        }
    }

    if($A['commentcode'] >= 0 ) {
        $delete_option = (SEC_hasRights('staticpages.edit') &&
            SEC_hasAccess($A['owner_id'], $A['group_id'],
            $A['perm_owner'], $A['perm_group'], $A['perm_members'],
            $A['perm_anon']) == 3 ? true : false);


        require_once $_CONF['path_system'] . 'lib-comment.php';

        $cmt_page = isset($_GET['cmtpage']) ? COM_applyFilter($_GET['cmtpage'],true) : 0;

        $spage->set_var ('commentbar', CMT_userComments($page, $A['sp_title'], 'staticpages',
                                    $comment_order, $comment_mode, 0, $cmt_page, false,
                                    $delete_option, $A['commentcode'],$A['sp_uid']));
    }


    $content = SP_render_content ($A['sp_content'], $A['sp_php']);
    $spage->set_var('content', $content );

    $dt = new Date($A['sp_date'],$_USER['tzid']);
    $curtime = $dt->format($dt->getUserFormat(),true);

    $spage->set_var('info_separator', 'hidden');
    if ($A['sp_format'] <> 'blankpage') {
        if ($_SP_CONF['show_date'] == 1) {
            $lastupdate = $LANG_STATIC['lastupdated']. ' ' . $curtime;
            $spage->set_var('lastupdate', $lastupdate);
        }

        if ($_SP_CONF['show_hits'] == 1) {
            if ($_SP_CONF['show_date'] == 1) {
                $spage->set_var('info_separator','visible');
            }
            $hits = COM_numberFormat ($A['sp_hits']) . ' ' . $LANG_STATIC['hits'];
            $spage->set_var('hits', $hits);
        }
    }

    $retval .= $spage->finish($spage->parse('output', 'page'));
    if (($A['sp_inblock'] == 1) && ($A['sp_format'] != 'blankpage')) {
        $retval .= COM_endBlock (COM_getBlockTemplate ('_staticpages_block',
                                                       'footer'));
    }

    if ($A['sp_format'] <> 'blankpage') {
        if (!isset($_USER['noboxes'])) {
            if ( COM_isAnonUser() ) {
                $_USER['noboxes'] = 0;
            } else {
                $_USER['noboxes'] = DB_getItem($_TABLES['userindex'],
                                        'noboxes', "uid=".(int)$_USER['uid']);
            }
        }

        if (($A['sp_format'] == 'allblocks' || $A['sp_format'] == 'rightblocks') && ($_USER['noboxes'] != 1)) {
            $footerOption = true;
        } else if (($A['sp_format'] == 'leftblocks') || ($A['sp_format'] == 'noblocks')) {
            $footerOption = false;
        } else {
            $footerOption = '';
        }
    }

    if ($A['sp_format'] <> 'blankpage') {
        $displaypage = COM_siteHeader($headerOption, $A['sp_title']);
    }
    $displaypage .= $retval;
    if ($A['sp_format'] <> 'blankpage') {
        $displaypage .= COM_siteFooter($footerOption);
    }
    return $displaypage;
}

/**
* Prepare static page for print (i.e. display as "printable version").
*
* @param    string  $page       static page id
* @param    array   $A          static page data
* @return   string              HTML for the static page
*
*/
function SP_printPage ($page, $A)
{
    global $_CONF, $LANG01, $_TABLES;

    $template_path = staticpages_templatePath ();
    $print = new Template ($template_path);
    $print->set_file (array ('print' => 'printable.thtml'));
    $print->set_var ('site_name', $_CONF['site_name']);
    $print->set_var ('site_slogan', $_CONF['site_slogan']);

    $print->set_var ('page_title', $_CONF['site_name'] . ' - '
                                   . $A['sp_title']);
    $sp_url = COM_buildUrl ($_CONF['site_url']
                               . '/page.php?page=' . $page);
    $print->set_var ('sp_url', $sp_url);
    $print->set_var ('sp_title', $A['sp_title']);
    $print->set_var ('sp_content',
            SP_render_content ($A['sp_content'], $A['sp_php']));
    $print->set_var ('sp_hits', COM_numberFormat ($A['sp_hits']));
    $printable = COM_buildURL($_CONF['site_url']
               . '/page.php?page=' . $page . '&amp;mode=print');
    $print->set_var('printable_url', $printable);
    if ($A['commentcode'] >= 0) {
        $commentsUrl = $sp_url . '#comments';
        $comments = (int) DB_count($_TABLES['comments'],
                             array('sid', 'type'), array(DB_escapeString($page), 'staticpages'));
        if ($comments > 0 ) {
            $numComments = COM_numberFormat ($comments);
            $print->set_var ('story_comments', $numComments);
            $print->set_var ('comments_url', $commentsUrl);
            $print->set_var ('comments_text', $numComments . ' ' . $LANG01[3]);
            $print->set_var ('comments_count', $numComments);
            $print->set_var ('lang_comments', $LANG01[3]);
            $comments_with_count = sprintf ($LANG01[121], $numComments);

            if ($comments > 0) {
                $comments_with_count = COM_createLink($comments_with_count, $commentsUrl);
            }
            $print->set_var ('comments_with_count', $comments_with_count);
        }

    }
    $print->parse ('output', 'print');

    return $print->finish ($print->get_var ('output'));
}

/**
* Prepare static page for display
*
* @param    string  $page       static page id
* @param    string  $mode       type of display to return ('', 'print', 'autotag'
* @param    string  comment_order sorting of comments
* @param    string  comment_mode
* @return   string              HTML for the static page
*
*/
function SP_returnStaticpage($page='', $mode='', $comment_order = 'ASC', $comment_mode ='nested')
{
    global $_CONF, $_TABLES, $LANG_ACCESS, $LANG_STATIC, $LANG_LOGIN;

    $retval = '';

    $args = array(
                'sp_id' => $page,
                'mode'  => $mode
                 );
    $svc_msg = array();

    if (PLG_invokeService('staticpages', 'get', $args, $retval, $svc_msg) == PLG_RET_OK) {

        SESS_setContext(array('type' => 'staticpages','id' => $page, 'uid' => $retval['sp_uid']));

        if ($mode == 'print') {
            $retval = SP_printPage($page, $retval);
        } else if ($mode =='autotag') {
            $retval = SP_render_content($retval['sp_content'], $retval['sp_php']);
        } else {
            $retval = SP_displayPage($page, $retval, $comment_order, $comment_mode);
        }
        SESS_clearContext();
        // increment hit counter for page
        DB_query("UPDATE {$_TABLES['staticpage']} SET sp_hits = sp_hits + 1 WHERE sp_id = '".DB_escapeString($page)."'");

    }

    return $retval;
}

/**
* Shows the statistics for the Static Pages plugin on stats.php.
* If $showsitestats is 1 then we are to only print the overall stats in the
* 'site statistics box' otherwise we show the detailed stats
*
* @param    int     showsitestate   Flag to let us know which stats to get
*/
function plugin_showstats_staticpages($showsitestats)
{
    global $_CONF, $_TABLES, $LANG_STATIC;
    $retval = '';

    $perms = SP_getPerms ();
    if (!empty ($perms)) {
        $perms = ' AND ' . $perms;
    }
    $result = DB_query ("SELECT sp_id,sp_title,sp_hits FROM {$_TABLES['staticpage']} WHERE sp_hits > 0 AND sp_search = 1 AND sp_status = 1 " . $perms . ' ORDER BY sp_hits DESC LIMIT 10');
    $nrows  = DB_numRows ($result);
    if ($nrows > 0) {
        require_once( $_CONF['path_system'] . 'lib-admin.php' );
        $header_arr = array(
            array('text' => $LANG_STATIC['stats_page_title'], 'field' => 'sid', 'header_class' => 'stats-header-title'),
            array('text' => $LANG_STATIC['stats_hits'], 'field' => 'sp_hits', 'header_class' => 'stats-header-count', 'field_class' => 'stats-list-count'),
        );
        $data_arr = array();
        $text_arr = array('has_menu'     => false,
                          'title'        => $LANG_STATIC['stats_headline'],
        );
        for ($i = 0; $i < $nrows; $i++) {
            $A = DB_fetchArray ($result);
            $A['sp_title'] = $A['sp_title'];
            $A['sid'] = COM_createLink($A['sp_title'], COM_buildUrl ($_CONF['site_url']
                      . "/page.php?page={$A['sp_id']}"));
            $A['sp_hits'] = COM_NumberFormat ($A['sp_hits']);
            $data_arr[$i] = $A;
        }
        $retval .= ADMIN_simpleList("", $header_arr, $text_arr, $data_arr);
    } else {
        $retval .= COM_startBlock ($LANG_STATIC['stats_headline'],'',COM_getBlockTemplate('_admin_block', 'header'));
        $retval .= $LANG_STATIC['stats_no_hits'];
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    }

    return $retval;
}

/**
* New stats plugin API function for proper integration with the site stats
*
* @return   array(item text, item count);
*
*/
function plugin_statssummary_staticpages ()
{
    global $_TABLES, $LANG_STATIC;

    $total_pages = SP_countVisiblePages ();

    return array ($LANG_STATIC['staticpages'], COM_numberFormat ($total_pages));
}

/**
* glFusion is asking us to provide any new items that show up in the type
* drop-down on search.php.  Let's let users search static pages!
*
*/
function plugin_searchtypes_staticpages()
{
    global $LANG_STATIC;

    $tmp['staticpages'] = $LANG_STATIC['staticpages'];

    return $tmp;
}


/**
* this searches for static pages matching the user query and returns an array of
* for the header and table rows back to search.php where it will be formated and
* printed
*
* @param    string  $query      Keywords user is looking for
* @param    date    $datestart  Start date to get results for
* @param    date    $dateend    End date to get results for
* @param    string  $topic      The topic they were searching in
* @param    string  $type       Type of items they are searching, or 'all' (deprecated)
* @param    int     $author     Get all results by this author
* @param    string  $keyType    search key type: 'all', 'phrase', 'any'
* @param    int     $page       page number of current search (deprecated)
* @param    int     $perpage    number of results per page (deprecated)
*
*/
function plugin_dopluginsearch_staticpages($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $_TABLES, $LANG_STATIC;

    // Make sure the query is SQL safe
    $query = trim(DB_escapeString($query));

    $sql = "SELECT sp.sp_id AS id, sp.sp_title AS title, sp.sp_content AS description, UNIX_TIMESTAMP(sp.sp_date) AS date, sp.sp_uid AS uid, sp.sp_hits AS hits, CONCAT('/page.php?page=', sp.sp_id) AS url ";
    $sql .= "FROM {$_TABLES['staticpage']} AS sp,{$_TABLES['users']} AS u ";
    $sql .= "WHERE (sp.sp_uid = u.uid) AND (sp_php <> 1) AND (sp_search = 1) AND (sp_status = 1) ";
    $sql .= COM_getPermSQL('AND') . COM_getLangSQL('sp_id', 'AND', 'sp') . ' ';

    if (!empty ($author))
        $sql .= "AND (sp_uid = '$author') ";

    $search = new SearchCriteria('staticpages', $LANG_STATIC['staticpages']);
    $columns = array('sp_content','sp_title');
    $sql .= $search->getDateRangeSQL('AND', 'UNIX_TIMESTAMP(sp_date)', $datestart, $dateend);
    list($sql,$ftsql) = $search->buildSearchSQL($keyType, $query, $columns, $sql);
    $search->setSQL($sql);
    $search->setFTSQL($ftsql);
    $search->setRank(3);
    $search->setURLRewrite(true);

    return $search;
}

/**
* this searches the plugins comments matching the user query and returns an array of
* for the header and table rows back to search.php where it will be formated and
* printed
*
* @query            string          Keywords user is looking for
* @datestart        date/time       Start date to get results for
* @dateend          date/time       End date to get results for
* @topic            string          The topic they were searching in
* @type             string          Type of items they are searching
* @author           string          Get all results by this author
*
*/
function plugin_dopluginsearch_comment_staticpages($query, $datestart, $dateend, $topic, $type, $author, $keyType, $page, $perpage)
{
    global $_TABLES, $LANG_STATIC, $LANG09;

    $query = trim(DB_escapeString(htmlspecialchars($query)));

    $sql =  "SELECT c.sid AS id, c.title AS title, c.comment AS description, UNIX_TIMESTAMP(c.date) AS date, c.uid AS uid, '0' AS hits, ";
    $sql .= "CONCAT('/page.php?page=', c.sid,'#comments') AS url ";
    $sql .= "FROM {$_TABLES['users']} AS u, {$_TABLES['comments']} AS c ";
    $sql .= "LEFT JOIN {$_TABLES['staticpage']} s ON c.sid=s.sp_id ";
    $sql .= "WHERE (c.type='staticpages') AND (u.uid = c.uid) ";
    $sql .= COM_getPermSQL('AND',0,2,'s') . COM_getLangSQL('sp_id', 'AND', 's') . ' ';

    if (!empty($author)) {
        $sql .= "AND (c.uid = ".(int) $author.") ";
    }

    $search_c = new SearchCriteria('comments', $LANG_STATIC['staticpages'] . ' > ' . $LANG09[66]);
    $columns = array('comment','c.title');
    $sql .= $search_c->getDateRangeSQL('AND', 'UNIX_TIMESTAMP(c.date)', $datestart, $dateend);
    list($sql,$ftsql) = $search_c->buildSearchSQL($keyType, $query, $columns, $sql);
    $search_c->setSQL($sql);
    $search_c->setFTSQL($ftsql);
    $search_c->setRank(2);

    return $search_c;
}


/**
* This will put an option for static pages in the command and control block on
* moderation.php
*
*/
function plugin_cclabel_staticpages()
{
    global $LANG_STATIC, $_CONF;

    if (SEC_hasRights ('staticpages.edit,staticpages.delete', 'OR')) {
        return array ($LANG_STATIC['staticpages'],
                $_CONF['site_admin_url'] . '/plugins/staticpages/index.php',
                plugin_geticon_staticpages ());
    }

    return false;
}

/**
* returns the administrative option for this plugin
*
*/
function plugin_getadminoption_staticpages()
{
    global $_CONF, $_TABLES, $LANG_STATIC;

    if (SEC_hasRights ('staticpages.edit,staticpages.delete', 'OR')) {
        $result = DB_query ("SELECT count(*) AS cnt FROM {$_TABLES['staticpage']}" . COM_getPermSQL ('WHERE', 0, 3));
        $A = DB_fetchArray ($result);
        $total_pages = $A['cnt'];
        return array ($LANG_STATIC['staticpages'], $_CONF['site_admin_url'] . '/plugins/staticpages/index.php', $total_pages);
    }
}

/**
* Return SQL where statement with appropriate permissions
*
* Takes User id and permission and returns SQL where clause which will return
* the appropriate objects.
* This assumes that the table has the following security structure:
* owner_id        | mediumint(8)
* group_id        | mediumint(8)
* perm_owner      | tinyint(1) unsigned
* perm_group      | tinyint(1) unsigned
* perm_members    | tinyint(1) unsigned
* perm_anon       | tinyint(1) unsigned
* This will work with the standard GL tables
*
* @param    string  $table  Table name (used in joins)
* @param    int     $access Access if blank read access  2 = read 3 = read/edit
* @param    int     $u_id   User ID if blank current user
* @return   string          Where clause of sql statement
*
*/
function SP_getPerms ($table = '', $access = '2', $u_id = '')
{
    global $_USER, $_GROUPS;

    if ($table != '') { $table .= '.'; }

    if ($u_id == '') {
        if (!COM_isAnonUser()) {
            $uid = $_USER['uid'];
        } else {
            $uid = 1;
        }
        $GROUPS = $_GROUPS;
    } else {
        $uid = $u_id;
        $GROUPS = SEC_getUserGroups ($uid);
    }

    $sql = '(';

    if ($uid > 1) {
        $sql .= "((owner_id = '{$uid}') AND (perm_owner >= $access)) OR ";

        $sql .= "((group_id IN (" . implode (',', $GROUPS) . ")) "
             . "AND (perm_group >= $access)) OR (perm_members >= $access)";
    } else {
        $sql .= "((group_id IN (" . implode (',', $GROUPS) . ")) "
             . "AND (perm_group >= $access)) OR (perm_anon >= $access)";
    }

    $sql .= ')';

    return $sql;
}

/**
* Display static pages in the center block.
*
* @param   where   int      where the static page will be displayed (0..3)
* @param   page    int      page number
* @param   topic   string   topic ID
* @return          string   HTML for the static page (can be empty)
*/
function plugin_centerblock_staticpages ($where = 1, $page = 1, $topic ='')
{
    global $_CONF, $_TABLES, $_USER, $_SP_CONF, $LANG_STATIC, $_IMAGE_TYPE, $LANG01;

    $retval = '';
    $moresql = "(sp_where = " . (int) $where . ") AND (sp_status = 1) AND ";

    $dt = new Date('now',$_USER['tzid']);

    if( !COM_onFrontpage() ) {
        $hpsql = " OR (sp_tid = 'allnhp') ";
    } else {
        $hpsql = '';
    }

    if (empty ($topic)) {
        $moresql .= "((sp_tid = 'none') OR (sp_tid = 'all')".$hpsql.")";
    } else {
        $moresql .= "((sp_tid = '".DB_escapeString($topic)."') OR (sp_tid = 'all')".$hpsql.")";
    }

    if ($_SP_CONF['sort_by'] == 'date') {
        $sort = 'sp_date DESC';
    } else if ($_SP_CONF['sort_by'] == 'title') {
        $sort = 'sp_title';
    } else { // default to "sort by id"
        $sort = 'sp_id';
    }
    $sort = 'sp_where,' . $sort;

    $perms = SP_getPerms ();
    if (!empty ($perms)) {
        $perms = ' AND ' . $perms;
    }
    $spsql = "SELECT sp_id,sp_title,sp_content,sp_format,sp_date,sp_hits,sp_tid,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon,sp_php,sp_inblock,sp_help FROM {$_TABLES['staticpage']} WHERE (sp_centerblock = 1)" . COM_getLangSql ('sp_id', 'AND') . ' AND ' . $moresql . $perms . " ORDER BY " . $sort;
    $result = DB_query ($spsql);

    $pages = DB_numRows ($result);
    if ($pages > 0) {
        for ($i = 0; $i < $pages; $i++) {
            $S = DB_fetchArray ($result);
            if ( $page > 1 ) {
                if ( $S['sp_tid'] != 'all' ) {
                    return;
                }
            }
            if ($where == 0) {
                switch ($S['sp_format']) {
                    case 'noblocks':
                    case 'rightblocks':
                        $retval .= COM_siteHeader ('none');
                        break;
                    case 'allblocks':
                    case 'leftblocks':
                        $retval .= COM_siteHeader ('menu');
                        break;
                }
                if (isset($_GET['msg'])) {
                    $msg = COM_applyFilter($_GET['msg'], true);
                    if ($msg > 0) {
                        $retval .= COM_showMessage($msg);
                    }
                }
            }

            if (($S['sp_inblock'] == 1) && !empty ($S['sp_title'])
                && (($where != 0) || ($S['sp_format'] != 'blankpage'))) {
                $retval .= COM_startBlock ($S['sp_title'], $S['sp_help'],
                    COM_getBlockTemplate ('_staticpages_centerblock', 'header'));
            }

            $spage = new Template( $_CONF['path'] . 'plugins/staticpages/templates/' );
            $spage->set_file( array('page'=>'centerblock.thtml'));
            $spage->set_var('site_admin_url', $_CONF['site_admin_url']);

            if ($_CONF['hideprintericon'] == 0) {
                $icon_url = $_CONF['layout_url'] . '/images/print.' . $_IMAGE_TYPE;
                $attr = array('title' => $LANG_STATIC['printable_format']);
                $printicon = COM_createImage($icon_url, $LANG01[65], $attr);
                $print_url = COM_buildURL ($_CONF['site_url']
                    . '/page.php?page=' . $S['sp_id'] . '&amp;mode=print');
                $icon = COM_createLink($printicon, $print_url,array('rel'=>'nofollow'));
                $spage->set_var('print_icon', $icon);
            }
            if ((SEC_hasAccess ($S['owner_id'], $S['group_id'], $S['perm_owner'],
                    $S['perm_group'], $S['perm_members'], $S['perm_anon']) == 3) &&
                    SEC_hasRights ('staticpages.edit')) {
                $icon_url = $_CONF['layout_url'] . '/images/edit.' . $_IMAGE_TYPE;
                $attr = array('title' => $LANG_STATIC['edit']);
                $editiconhtml = COM_createImage($icon_url, $LANG_STATIC['edit'], $attr);
                $url = $_CONF['site_admin_url']
                    . '/plugins/staticpages/index.php?edit=x&amp;sp_id=' . $S['sp_id'];
                $attr = array('class' => 'editlink','title' => $LANG_STATIC['edit'],'rel'=>'nofollow');
                $icon =
                    '&nbsp;' . COM_createLink(
                    $editiconhtml, //display
                    $url,  //target
                    $attr //other attributes
                );
                $spage->set_var('edit_icon', $icon);
            }

            $spage->set_var('info_separator', 'hidden');
            if ($_SP_CONF['show_date'] == 1) {
                $dt->setTimestamp(strtotime($S['sp_date']));
                $lastupdate = $LANG_STATIC['lastupdated']. ' ' . $dt->format($_CONF['daytime'], true);
                $spage->set_var('lastupdate', $lastupdate);
            }

            if ($_SP_CONF['show_hits'] == 1) {
                if ($_SP_CONF['show_date'] == 1) {
                    $spage->set_var('info_separator', 'visible');
                }
                $hits = COM_numberFormat($S['sp_hits']) . ' '
                                         . $LANG_STATIC['hits'];
                $spage->set_var('hits', $hits);
            }

            $content = SP_render_content ($S['sp_content'], $S['sp_php']);
            $spage->set_var('content', $content );
            $retval .= $spage->finish($spage->parse('output', 'page'));
            if (($S['sp_inblock'] == 1) && !empty ($S['sp_title'])
                && (($where != 0) || ($S['sp_format'] != 'blankpage'))) {
                $retval .= COM_endBlock (COM_getBlockTemplate ('_staticpages_centerblock', 'footer'));
            }

            if ($where == 0) {
                if ($S['sp_format'] == 'allblocks' || $S['sp_format'] == 'rightblocks') {
                    $retval .= COM_siteFooter (true);
                } else if ($S['sp_format'] != 'blankpage') {
                    $retval .= COM_siteFooter ();
                }
            }
            // increment hit counter for page
            DB_query ("UPDATE {$_TABLES['staticpage']} SET sp_hits = sp_hits + 1 WHERE sp_id = '{$S['sp_id']}'");
        }
    }

    return $retval;
}

function plugin_user_move_staticpages($origUID,$destUID)
{
    global $_TABLES;

    DB_query ("UPDATE {$_TABLES['staticpage']} SET sp_uid = ".(int)$destUID." WHERE sp_uid = ".(int)$origUID,1);
    DB_query ("UPDATE {$_TABLES['staticpage']} SET owner_id = ".(int)$destUID." WHERE owner_id = ".(int)$origUID,1);
}

/**
* A user is about to be deleted. Update ownership of any static pages owned
* by that user or delete them.
*
* @param   uid   int   User id of deleted user
*
*/
function plugin_user_delete_staticpages($uid)
{
    global $_TABLES, $_SP_CONF;

    if (DB_count ($_TABLES['staticpage'], 'sp_uid', intval($uid)) +
            DB_count ($_TABLES['staticpage'], 'owner_id', intval($uid)) == 0) {
        return;
    }

    if ($_SP_CONF['delete_pages'] == 1) {
        // delete the pages
        DB_query ("DELETE FROM {$_TABLES['staticpage']} WHERE (sp_uid = ".intval($uid).") OR (owner_id = ".intval($uid).")");
    } else {
        // assign ownership to a user from the Root group
        $rootgroup = DB_getItem ($_TABLES['groups'], 'grp_id',
                                 "grp_name = 'Root'");
        $result = DB_query ("SELECT DISTINCT ug_uid FROM {$_TABLES['group_assignments']} WHERE ug_main_grp_id = $rootgroup ORDER BY ug_uid LIMIT 1");
        $A = DB_fetchArray ($result);
        $rootuser = $A['ug_uid'];

        DB_query ("UPDATE {$_TABLES['staticpage']} SET sp_uid = $rootuser WHERE sp_uid = ".intval($uid));
        DB_query ("UPDATE {$_TABLES['staticpage']} SET owner_id = $rootuser WHERE owner_id = ".intval($uid));
    }
}


/**
* Return the current version of code.
* Used in the Plugin Editor to show the registered version and code version
*/
function plugin_chkVersion_staticpages()
{
    global $_SP_CONF;

    return $_SP_CONF['pi_version'];
}

/**
* Implements the [staticpage:] autotag.
*
*/
function plugin_autotags_staticpages($op, $content = '', $autotag = '')
{
    global $_CONF, $_TABLES, $LANG_SP_AUTOTAG;

    if ($op == 'tagname' ) {
        return array('staticpage', 'staticpage_content');
    } else if ($op == 'tagusage') {
        $tagUsage = array(
            array('namespace' => 'staticpages','usage' => 'page')
        );
        return $tagUsage;
    } else if ($op == 'desc') {
        switch ($content) {
            case 'staticpage' :
                return $LANG_SP_AUTOTAG['desc_staticpage'];
                break;
            case 'staticpage_content' :
                return $LANG_SP_AUTOTAG['desc_staticpage_content'];
                break;
            default :
                return '';
                break;
        }
    } else if ($op == 'parse') {

        $mode = '';

        $px = explode (' ', trim ($autotag['parm2']));
        if (is_array ($px)) {
            foreach ($px as $part) {
                if (substr ($part, 0, 6) == 'where:') {
                    $a = explode (':', $part);
                    $mode = $a[1];
                } else {
                    break;
                }
            }
        }
        switch ($mode) {
            case 'home' :
                if (COM_isFrontpage()) return;
                break;
            case 'nohome' :
                if ( !COM_isFrontpage()) return;
                break;
        }

        if (($autotag['tag'] == 'staticpage') || ($autotag['tag'] == 'staticpage_content' )) {
            $sp_id = COM_applyFilter($autotag['parm1']);
            if ( empty($sp_id) ) {
                $content = str_replace ($autotag['tagstr'], '',$content);
                return $content;
            }
            $sp_status = 0;
            $perms = SP_getPerms ();
            if (!empty ($perms)) {
                $perms = ' AND ' . $perms;
            }
            $sql = "SELECT sp_title FROM {$_TABLES['staticpage']} "
                 . "WHERE (sp_id = '".DB_escapeString($sp_id)."') AND (sp_status = 1)" . $perms;
            $result = DB_query ($sql);
            $count  = DB_numRows ($result);
            if ( $count < 1 ) {
                $content = str_replace ($autotag['tagstr'], '',$content);
                return $content;
            }
            $S = DB_fetchArray($result);
            if (($autotag['tag'] == 'staticpage')) {
                $url = COM_buildUrl($_CONF['site_url']
                                    . '/page.php?page=' . $sp_id);
                if (empty($autotag['parm2'])) {
                    $linktext = $S['sp_title'];
                } else {
                    $linktext = $autotag['parm2'];
                }
                $link = COM_createLink($linktext, $url);
                $content = str_replace($autotag['tagstr'], $link, $content);
            } else if (($autotag['tag'] == 'staticpage_content') ) {
                $recursive[$sp_id] = true;
                $sp_content = SP_returnStaticpage($sp_id, 'autotag');
                $content = str_replace($autotag['tagstr'], $sp_content,$content);
            } else {
                $content = str_replace ($autotag['tagstr'], '',$content);
            }
        }

        return $content;
    }
}

/**
* Returns the URL of the plugin's icon
*
* @return   string      URL of the icon
*
*/
function plugin_geticon_staticpages ()
{
    global $_CONF;

    return $_CONF['site_url'] . '/staticpages/images/staticpages.png';
}

/**
* Update the Static Pages plugin
*
* @return   int     Number of message to display (true = generic success msg)
*
*/
function plugin_upgrade_staticpages()
{
    global $_TABLES, $_CONF, $_SP_CONF;

    if ( @file_exists($_CONF['path'].'plugins/staticpages/upgrade.php') ) {
        require_once $_CONF['path'].'plugins/staticpages/upgrade.php';
        return staticpages_upgrade();
    } else {
        // upgrades are done by the install script - return a generic error
        COM_errorLog("StaticPages plugin upgrade function not implemented");
        return 3001;
    }
}


/**
* Get path for the template files.
*
* @param    string  $path   subdirectory within the base template path
* @return   string          full path to template directory
*
*/
function staticpages_templatePath ($path = '')
{
    global $_CONF;

    if (empty ($path)) {
        $layout_path = $_CONF['path_layout'] . 'staticpages';
    } else {
        $layout_path = $_CONF['path_layout'] . 'staticpages/' . $path;
    }

    if (is_dir ($layout_path)) {
        $retval = $layout_path;
    } else {
        $retval = $_CONF['path'] . 'plugins/staticpages/templates';
        if (!empty ($path)) {
            $retval .= '/' . $path;
        }
    }

    return $retval;
}

/**
* Return information for a static page
*
* @param    string  $id         static page ID or '*'
* @param    string  $what       comma-separated list of properties
* @param    int     $uid        user ID or 0 = current user
* @param    array   $options    (reserved for future extensions)
* @return   mixed               string or array of strings with the information
*
*/
function plugin_getiteminfo_staticpages($id, $what, $uid = 0, $options = array())
{
    global $_CONF, $_TABLES, $LANG_STATIC;

    // parse $what to see what we need to pull from the database
    $properties = explode(',', $what);
    $fields = array();
    foreach ($properties as $p) {
        switch ($p) {
        case 'date-modified':
            $fields[] = 'UNIX_TIMESTAMP(sp_date) AS unixdate';
            break;
        case 'description':
        case 'excerpt':
        case 'raw-description':
            $fields[] = 'sp_content';
            $fields[] = 'sp_php';
            break;
        case 'id':
            $fields[] = 'sp_id';
            break;
        case 'title':
            $fields[] = 'sp_title';
            break;
        case 'url':
        case 'label':
            $fields[] = 'sp_id';
            break;
        case 'status':
            $fields[] = 'sp_status';
            break;
        default:
            break;
        }
    }

    $fields = array_unique($fields);

    if (count($fields) == 0) {
        $retval = array();

        return $retval;
    }

    if ($id == '*') {
        $where = '';
        $permOp = 'WHERE';
    } else {
        $where = " WHERE sp_id = '" . DB_escapeString($id) . "'";
        $permOp = 'AND';
    }
    if ($uid > 0) {
        $permSql = COM_getPermSql($permOp, $uid);
    } else {
        $permSql = COM_getPermSql($permOp);
    }
    $sql = "SELECT " . implode(',', $fields) . " FROM {$_TABLES['staticpage']}" . $where . $permSql;
    if ($id != '*') {
        $sql .= ' LIMIT 1';
    }

    $result = DB_query($sql);
    $numRows = DB_numRows($result);

    $retval = array();
    for ($i = 0; $i < $numRows; $i++) {
        $A = DB_fetchArray($result);

        $props = array();
        foreach ($properties as $p) {
            switch ($p) {
                case 'date-modified':
                    $props['date-modified'] = $A['unixdate'];
                    break;
                case 'description':
                case 'excerpt':
                    $props[$p] = SP_render_content($A['sp_content'],$A['sp_php']);
                    break;
                case 'raw-description':
                    $props[$p] = $A['sp_content'];
                    break;
                case 'id':
                    $props['id'] = $A['sp_id'];
                    break;
                case 'title':
                    $props['title'] = $A['sp_title'];
                    break;
                case 'url':
                    if (empty($A['sp_id'])) {
                        $props['url'] = COM_buildUrl($_CONF['site_url'].'/page.php?page=' . $id);
                    } else {
                        $props['url'] = COM_buildUrl($_CONF['site_url'].'/page.php?page='.$A['sp_id']);
                    }
                    break;
                case 'label':
                    $props['label'] = $LANG_STATIC['staticpages'];
                    break;
                case 'status':
                    $props['status'] = $A['sp_status'];
                    break;
                default:
                    $props[$p] = '';
                break;
            }
        }

        $mapped = array();
        foreach ($props as $key => $value) {
            if ($id == '*') {
                if ($value != '') {
                    $mapped[$key] = $value;
                }
            } else {
                $mapped[$key] = $value;
            }
        }

        if ($id == '*') {
            $retval[] = $mapped;
        } else {
            $retval = $mapped;
            break;
        }
    }

    if (($id != '*') && (count($retval) == 1)) {
        $tRet = array_values($retval);
        $retval = $tRet[0];
    }

    return $retval;
}

function plugin_getconfigelementhelp_staticpages($type, $option, $doclang = 'english' )
{
    global $_CONF;

    $retval = '';

    $baseUrl = $_CONF['site_url'];
    $cfg = 'docs/' . $doclang . '/staticpages.html';
    if (@file_exists($_CONF['path_html'] . $cfg)) {
        $descUrl = $baseUrl . '/' . $cfg;
    } else {
        $descUrl = $baseUrl . '/docs/english/staticpages.html';
    }
    $retval = $descUrl;

    return array($retval,0);
}

/**
* Render the actual content of a static page (without any surrounding blocks)
*
* @param    string  $sp_content the content (HTML or PHP source)
* @param    int     $sp_php     flag: 1 = content is PHP source, 0 = is HTML
* @return   string              rendered content (HTML)
*
*/
function SP_render_content ($sp_content, $sp_php)
{
    global $_SP_CONF, $LANG_STATIC;

    $retval = '';

    if ($_SP_CONF['allow_php'] == 1) {
        // Check for type (ie html or php)
        if ($sp_php == 1) {
            $retval = eval ($sp_content);
        } else if ($sp_php == 2) {
            ob_start ();
            eval ($sp_content);
            $retval = ob_get_contents ();
            ob_end_clean ();
        } else {
            $retval = $sp_content;
        }
        $retval = PLG_replacetags ($retval,'staticpages','page');
    } else {
        if ($sp_php != 0) {
            COM_errorLog ("PHP in static pages is disabled. Can not display page '$page'.", 1);
            $retval .= $LANG_STATIC['deny_msg'];
        } else {
            $retval .= PLG_replacetags ($sp_content,'staticpages','page');
        }
    }

    return $retval;
}

/**
 * Return true since this plugin supports webservices
 *
 * @return  bool	True, if webservices are supported
 */
function plugin_wsEnabled_staticpages() {
    return true;
}

?>