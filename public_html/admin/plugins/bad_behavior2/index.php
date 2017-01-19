<?php
// +--------------------------------------------------------------------------+
// | Bad Behavior Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | Main administration page.                                                |
// +--------------------------------------------------------------------------+
// | Bad Behavior - detects and blocks unwanted Web accesses                  |
// | Copyright (C) 2005-2017 Michael Hampton                                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Dirk Haun         - dirk AT haun-online DOT de                  |
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

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

$display = '';

if (!SEC_inGroup ('Bad Behavior2 Admin')) {
    $display .= COM_siteHeader ('menu');
    $display .= COM_showMessageText($LANG20[6],$LANG20[1],true,'error');
    $display .= COM_siteFooter ();
    echo $display;
    exit;
}

USES_lib_admin();

require_once $_CONF['path_html'] . '/bad_behavior2/bad-behavior-glfusion.php';

/**
* List logged requests
*
* @param    int     $page   page number
* @return   string          HTML for list of entries
*
*/
function _bb_listEntries ($page = 1, $msg = '')
{
    global $_CONF, $_USER, $_TABLES, $LANG_BAD_BEHAVIOR, $LANG_BB2_RESPONSE, $LANG_ADMIN;

    $retval = '';

    if ($page < 1) {
        $page = 1;
    }

    $filter = 'all';
    if ( isset($_REQUEST['filter']) ) {
        $filter = COM_applyFilter($_REQUEST['filter']);
    }
    $where = '';

    if ( $filter != 'all' ) {
        $where = ' WHERE '.WP_BB_LOG.'.key="'. DB_escapeString($filter) . '"';
    }

    $start = (($page - 1) * 50);

    if ( $filter != 'all' ) {
        $entries = DB_count (WP_BB_LOG,WP_BB_LOG.'.key',DB_escapeString($filter));
    } else {
        $entries = DB_count (WP_BB_LOG);
    }

    if ($start > $entries) {
        $start = 1;
        $page = 1;
    }

    $donate = $LANG_BAD_BEHAVIOR['description'];
    if (DB_getItem ($_TABLES['vars'], 'value',"name = 'bad_behavior2.donate'") == 1) {
        $donate .= '<p>' . $LANG_BAD_BEHAVIOR['donate_msg'] . '</p>' . LB;
    }

    // writing the menu on top
    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/ban.php',
              'text' => $LANG_BAD_BEHAVIOR['list_ips']),
        array('url' => $_CONF['site_admin_url'].'/plugins/bad_behavior2/ban.php?mode=add',
              'text' => $LANG_BAD_BEHAVIOR['ban_ip']),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['plugin_display_name'] . ' - ' . $LANG_BAD_BEHAVIOR['block_title_list'], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $donate,
        $_CONF['site_url'] . '/bad_behavior2/images/bad_behavior2.png'
    );

    $retval .= '<br />';
    if (!empty ($msg)) {
        $retval .= COM_showMessage ($msg, 'bad_behavior2');
    }

    $templates = new Template ($_CONF['path'] . 'plugins/'
                               . BAD_BEHAVIOR_PLUGIN . '/templates');
    $templates->set_file ('list','log.thtml');

    $templates->set_var (array(
            'lang_ip'           => $LANG_BAD_BEHAVIOR['row_ip'],
            'lang_user_agent'   => $LANG_BAD_BEHAVIOR['row_user_agent'],
            'lang_referer'      => $LANG_BAD_BEHAVIOR['row_referer'],
            'lang_reason'       => $LANG_BAD_BEHAVIOR['row_reason'],
            'lang_response'     => $LANG_BAD_BEHAVIOR['row_response'],
            'lang_method'       => $LANG_BAD_BEHAVIOR['row_method'],
            'lang_protocol'     => $LANG_BAD_BEHAVIOR['row_protocol'],
            'lang_date'         => $LANG_BAD_BEHAVIOR['row_date'],
            'lang_search'       => $LANG_BAD_BEHAVIOR['search'],
            'lang_ip_date'      => $LANG_BAD_BEHAVIOR['ip_date'],
            'lang_headers'      => $LANG_BAD_BEHAVIOR['headers'],
            'lang_filter_select'=> $LANG_BAD_BEHAVIOR['filter'],
            'lang_go'           => $LANG_BAD_BEHAVIOR['go'],
            ));

    $filter_select = '<option value="all"';
    if ( $filter == '' ) {
        $filter_select .= ' selected="selected" ';
    }
    $filter_select .= '>'.$LANG_BAD_BEHAVIOR['no_filter'].'</option>';
    foreach ($LANG_BB2_RESPONSE AS $code => $text ) {
        $filter_select .= '<option value="'.$code.'"';
        if ( $filter == $code ) {
            $filter_select .= ' selected="selected" ';
        }
        $filter_select .= '>'.$text.'</option>';
    }
    $templates->set_var('filter_select', $filter_select);

    $result = DB_query ("SELECT id,ip,date,request_method,request_uri,server_protocol,http_headers,user_agent,request_entity,`key` FROM " . WP_BB_LOG . " " . $where . " ORDER BY date DESC LIMIT $start,50");
    $num = DB_numRows ($result);

    if ( $num == 0 ) {
        $templates->set_var('lang_no_data',$LANG_BAD_BEHAVIOR['list_no_entries']);
    }

    $templates->set_block('list','logrow','lrow');

    for ($i = 0; $i < $num; $i++) {
        $A = DB_fetchArray ($result);
        $lcount = (50 * ($page - 1)) + $i + 1;

        foreach ($A as $key => $val) {
            $A[$key] = htmlspecialchars ($val,ENT_QUOTES,COM_getEncodingt());
        }

        $dt = new Date($A['date'],$_USER['tzid']);

        $headers = str_replace("\n", "<br/>\n", $A['http_headers']);
		$headers = str_replace("User-Agent:","<strong>User-Agent:</strong>",$headers);
		$headers = str_replace("Host:","<strong>Host:</strong>",$headers);
		$headers = str_replace("POST ","<strong>POST</strong> ",$headers);
		$headers = str_replace("GET ","<strong>GET</strong> ",$headers);
		$headers = str_replace("Accept-Language:","<strong>Accept-Language:</strong> ",$headers);
		$headers = str_replace("Accept-Encoding:","<strong>Accept-Encoding:</strong> ",$headers);
		$headers = str_replace("Accept-Charset:","<strong>Accept-Charset:</strong> ",$headers);
		$headers = str_replace("X-Forwarded-For:","<strong>X-Forwarded-For:</strong> ",$headers);
		$headers = str_replace("Cookie:","<strong>Cookie:</strong> ",$headers);
		$headers = str_replace("Via:","<strong>Via:</strong> ",$headers);
		$headers = str_replace("Connection:","<strong>Connection:</strong>",$headers);
		$headers = str_replace("Accept:","<strong>Accept:</strong>",$headers);
		$headers = str_replace("Cache-Control:","<strong>Cache-Control:</strong>",$headers);
		$headers = str_replace("Referer:","<strong>Referer:</strong>",$headers);
		$headers = str_replace("Pragma:","<strong>Pragma:</strong>",$headers);
		$headers = str_replace("Proxy-","<strong>Proxy-</strong>",$headers);
		$headers = str_replace("Cf-Connecting-Ip","<strong>Cf-Connecting-Ip</strong>",$headers);
		$headers = str_replace("Cf-Ipcountry","<strong>Cf-Ipcountry</strong>",$headers);
		$headers = str_replace("X-Forwarded-Proto","<strong>X-Forwarded-Proto</strong>",$headers);
		$headers = str_replace("Cf-Visitor","<strong>Cf-Visitor</strong>",$headers);
		$headers = str_replace("X-Http-Proto","<strong>X-Http-Proto</strong>",$headers);
		$headers = str_replace("X-Real-Ip","<strong>X-Real-Ip</strong>",$headers);
		$headers = str_replace("Content-Length","<strong>Content-Length</strong>",$headers);
        $headers = str_replace("Content-Type","<strong>Content-Type</strong>",$headers);
        $headers = str_replace("Te:","<strong>Te:</strong>",$headers);
        $headers = str_replace("Expect:","<strong>Expect:</strong>",$headers);
        $headers = str_replace("Dnt:","<strong>Dnt:</strong>",$headers);

		$entity = str_replace("\n", "<br/>\n", $A["request_entity"]);

        $templates->set_var (array(
                'row_num'           => $lcount,
                'cssid'             => ($i % 2) + 1,
                'id'                => $A['id'],
                'ip'                => $A['ip'],
                'request_method'    => $A['request_method'],
                'http_host'         => $A['request_uri'],
                'server_protocol'   => $A['server_protocol'],
                'http_referer'      => $headers,
                'reason'            => $LANG_BB2_RESPONSE[$A['key']],
                'http_user_agent'   => $A['user_agent'],
                'http_response'     => $entity,
                'date_and_time'     => $dt->toRFC822(true)
        ));

        $url = $_CONF['site_admin_url'] . '/plugins/' . BAD_BEHAVIOR_PLUGIN
             . '/index.php?mode=view&amp;id=' . $A['id'];
        if ($page > 1) {
            $url .= '&amp;page=' . $page;
        }
        $templates->set_var ('start_headers_anchortag', '<a href="' . $url
            . '" title="' . $LANG_BAD_BEHAVIOR['title_show_headers'] . '">');
        $templates->set_var ('end_headers_anchortag', '</a>');

        if (!empty ($_CONF['ip_lookup'])) {
            $iplookup = str_replace ('*', $A['ip'], $_CONF['ip_lookup']);
            $templates->set_var ('start_ip_lookup_anchortag', '<a href="'
                    . $iplookup . '" title="'
                    . $LANG_BAD_BEHAVIOR['title_lookup_ip'] . '" target="_new">');
            $templates->set_var ('end_ip_lookup_anchortag', '</a>');
        } else {
            $templates->set_var ('start_ip_lookup_anchortag', '');
            $templates->set_var ('end_ip_lookup_anchortag', '');
        }

        $templates->parse('lrow', 'logrow',true);
    }

    if ($entries > 50) {
        $baseurl = $_CONF['site_admin_url'] . '/plugins/' . BAD_BEHAVIOR_PLUGIN
                 . '/index.php?mode=list&filter='.$filter;
        $numpages = ceil ($entries / 50);
        $templates->set_var ('google_paging',
                COM_printPageNavigation ($baseurl, $page, $numpages));
    } else {
        $templates->set_var ('google_paging', '');
    }
    $templates->parse('output', 'list');
    $retval .= $templates->finish($templates->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    return $retval;
}

/**
* View details of an entry
*
* @param    int     $id     ID of the entry to display
* @param    int     $page   page number on the list (for the back link)
* @return   string          HTML for the entry details
*
*/
function _bb_viewEntry ($id, $page = 1)
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_BAD_BEHAVIOR,$LANG_BB2_RESPONSE;

    $retval = '';

    $donate = $LANG_BAD_BEHAVIOR['description'];

    if (DB_getItem ($_TABLES['vars'], 'value',"name = 'bad_behavior2.donate'") == 1) {
        $donate .= '<p>' . $LANG_BAD_BEHAVIOR['donate_msg'] . '</p>' . LB;
    }

    $backlink = $_CONF['site_admin_url'] . '/plugins/' . BAD_BEHAVIOR_PLUGIN
                . '/index.php?mode=list';
    if ($page > 1) {
        $backlink .= '&amp;page=' . $page;
    }

    // writing the menu on top
    $menu_arr = array (
        array('url' => $_CONF['site_admin_url'] . '/plugins/bad_behavior2/index.php?mode=list',
              'text' => 'Log Entries'),
        array('url' => $_CONF['site_admin_url'],
              'text' => $LANG_ADMIN['admin_home'])
    );

    $retval .= COM_startBlock ($LANG_BAD_BEHAVIOR['plugin_display_name'] . ' - ' . $LANG_BAD_BEHAVIOR['block_title_entry'], '',
                               COM_getBlockTemplate ('_admin_block', 'header'));

    $retval .= ADMIN_createMenu(
        $menu_arr,
        $donate,
        $_CONF['site_url'] . '/bad_behavior2/images/bad_behavior2.png'
    );
    $retval .= '<br />';

    $templates = new Template ($_CONF['path'] . 'plugins/'. BAD_BEHAVIOR_PLUGIN . '/templates');
    $templates->set_file ('entry','entry.thtml');
    $templates->set_var ('id', $id);
    $templates->set_var ('lang_ip', $LANG_BAD_BEHAVIOR['row_ip']);
    $templates->set_var ('lang_user_agent', $LANG_BAD_BEHAVIOR['row_user_agent']);
    $templates->set_var ('lang_referer', $LANG_BAD_BEHAVIOR['row_referer']);
    $templates->set_var ('lang_response', $LANG_BAD_BEHAVIOR['row_response']);
    $templates->set_var ('lang_method', $LANG_BAD_BEHAVIOR['row_method']);
    $templates->set_var ('lang_protocol', $LANG_BAD_BEHAVIOR['row_protocol']);
    $templates->set_var ('lang_date', $LANG_BAD_BEHAVIOR['row_date']);
    $templates->set_var ('lang_back', $LANG_BAD_BEHAVIOR['link_back']);
    $templates->set_var ('lang_denied_reason', $LANG_BAD_BEHAVIOR['denied_reason']);
    $templates->set_var ('lang_search', $LANG_BAD_BEHAVIOR['search']);

    $id = DB_escapeString ($id);
    $result = DB_query ("SELECT ip,date,request_method,request_uri,server_protocol,http_headers,user_agent,request_entity,`key` FROM " . WP_BB_LOG . " WHERE id = '$id'");
    $A = DB_fetchArray ($result);

    foreach ($A as $key => $val) {
        $A[$key] = htmlspecialchars ($val);
    }

    $templates->set_var ('ip', $A['ip']);
    $templates->set_var ('request_method', $A['request_method']);
    $templates->set_var ('http_host', $A['request_uri']);
    $templates->set_var ('server_protocol', $A['server_protocol']);
    $templates->set_var ('http_referer', $A['http_headers']);
    $templates->set_var ('http_user_agent', $A['user_agent']);
    $templates->set_var ('http_response', $A['request_entity']);
    $templates->set_var ('date_and_time', $A['date']);
    $templates->set_var ('http_headers', $A['http_headers']);
    $templates->set_var ('denied_reason', $LANG_BB2_RESPONSE[$A['key']]);

    if (!empty ($_CONF['ip_lookup'])) {
        $iplookup = str_replace ('*', $A['ip'], $_CONF['ip_lookup']);
        $templates->set_var ('start_ip_lookup_anchortag', '<a href="'
                . $iplookup . '" title="'
                . $LANG_BAD_BEHAVIOR['title_lookup_ip'] . '" target="_blank">');
        $templates->set_var ('end_ip_lookup_anchortag', '</a>');
    } else {
        $templates->set_var ('start_ip_lookup_anchortag', '');
        $templates->set_var ('end_ip_lookup_anchortag', '');
    }

    $templates->parse('output', 'entry');
    $retval .= $templates->finish($templates->get_var('output'));
    $retval .= COM_endBlock (COM_getBlockTemplate ('_admin_block', 'footer'));

    return $retval;
}

// MAIN
$rightblocks = false;
$display .= COM_siteHeader ('menu', $LANG_BAD_BEHAVIOR['page_title']);

if ( isset($_GET['mode']) ) {
    $mode = COM_applyFilter ($_GET['mode']);
} else {
    $mode = 'list';
}

if ($mode == 'list') {
    $page = isset($_GET['page']) ? COM_applyFilter ($_GET['page'], true) : 0;
    $display .= _bb_listEntries ($page);
} else if ($mode == 'view') {
    $id = isset($_GET['id']) ? COM_applyFilter ($_GET['id'], true) : 0;
    $page = isset($_GET['page']) ? COM_applyFilter ($_GET['page'], true) : 0;
    $display .= _bb_viewEntry ($id, $page);
} else {
    $page = isset($_GET['page']) ? COM_applyFilter ($_GET['page'], true) : 0;
    $display .= _bb_listEntries ($page);
}

$display .= COM_siteFooter ($rightblocks);

echo $display;

?>