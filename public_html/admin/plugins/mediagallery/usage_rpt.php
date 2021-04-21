<?php
/**
* glFusion CMS - Media Gallery Plugin
*
* Usage Report
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

use \glFusion\Log\Log;

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    Log::write('system',Log::WARNING,"Someone has tried to access the Media Gallery Configuration page.  User id: ".$_USER['uid']);
    $display = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

function MG_usageReport() {
    global $_TABLES, $_CONF, $_MG_CONF, $LANG_MG02, $LANG_MG01, $LANG30, $_POST;

    $retval = '';

    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file('admin', 'usage_rpt.thtml');
    $T->set_var('site_url',$_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);
    $T->set_var('plugin','mediagallery');

    $rpt_month  = COM_applyFilter($_POST['month'],true);
    $rpt_day    = COM_applyFilter($_POST['day'],true);
    $rpt_year   = COM_applyFilter($_POST['year'],true);
    $user       = COM_applyFilter($_POST['user']);
    $alldates   = COM_applyFilter($_POST['alldates']);

    if ($alldates == "on" && $user == "") {
        return(MG_errorHandler($LANG_MG02['usage_report_error1']));
    }

    if ($alldates == "")
    {
        $begin_time = mktime( 0, 0, 0,$rpt_month,$rpt_day,$rpt_year);
        $end_time   = mktime(23,59,59,$rpt_month,$rpt_day,$rpt_year);
        $where = " WHERE (time >= $begin_time AND time <= $end_time) ";
    } else {
        $where = "";
    }

    if ($alldates == "" && $user != "")
    {
        $where .= "AND ";
    }

    if ($user != "")
    {
        if ( $alldates == "on" ) {
            $where .= "WHERE ";
        }
        $where .= " user_id='" . $user . "' ";
    }

    $sql = "SELECT * FROM {$_TABLES['mg_usage_tracking']} " . $where . " ORDER BY time";

    $result = DB_query($sql);
    $nRows = DB_numRows($result);

    $i = 0;
    $T->set_block('admin', 'usagerow', 'urow');
    for ($x = 0; $x < $nRows; $x++)
    {
        $row = DB_fetchArray($result);

        if ( $alldates == "on" )
        {
            $view_date = date("d-M-y @ h:i a", $row['time']);
        } else {
            $view_date = date("h:i a", $row['time']);
        }

        $T->set_var(array(
            'usage_time'    =>  $view_date,
            'user_id'       =>  $row['user_name'],
            'application'   =>  $row['application'],
            'album_title'   =>  stripslashes($row['album_title']),
            'media_title'   =>  (stripslashes($row['media_title']) == "" ? ($row['media_id'] ? $row['media_id'] : "") : $row['media_title']),
            'media_link'    =>  $_MG_CONF['site_url'] . '/media_popup.php?mid=' . $row['media_id'] . '&aid=0',
            'rowclass'      =>  $i % 2 ? '2' : '1',
        ));
        $i++;
        $T->parse('urow','usagerow',true);
    }

    if ($alldates == "on" )
    {
        $rpt_date = $LANG_MG01['all_dates'];
    } else {
        $rpt_date = $LANG30[12+$rpt_month] . ' ' . $rpt_day . ', ' . $rpt_year;
    }

    $T->set_var(array(
        'report_date'   =>  $rpt_date,
        's_form_action' =>  $_MG_CONF['admin_url'] . 'usage_rpt.php',
        'lang_usage_report' => $LANG_MG01['usage_report_header'],
        'lang_time'         => $LANG_MG01['time'],
        'lang_user_id'      => $LANG_MG01['user_id'],
        'lang_application'  => $LANG_MG01['application'],
        'lang_album_title'  => $LANG_MG01['album_title'],
        'lang_media_title'  => $LANG_MG01['mod_mediatitle'],
        'lang_new_report'   => $LANG_MG01['new_report'],

    ));

    $T->parse('output','admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_usageReportMenu() {
    global $_MG_CONF, $_CONF, $_TABLES, $LANG_MG01;

    $retval = '';

    $T = new Template($_MG_CONF['template_path'].'/admin');
    $T->set_file('admin', 'usage_menu.thtml');
    $T->set_var('site_url',$_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);
    $T->set_var('plugin','mediagallery');
    $local = localtime(time(),1);

    $day   = $local['tm_mday'];
    $month = $local['tm_mon'] + 1;
    $year  = $local['tm_year'] + 1900;

    // Month Select
    $month_select = '<select name="month">';
    $month_select .= COM_getMonthFormOptions($month);
    $month_select .= '</select>';

    $day_select = '<select name="day">';
    for ( $i = 1; $i <= 31; $i++ )
    {
        $day_select  .= '<option value="' . $i . '"' . (($day == $i) ? ' selected="selected"' : "") . '>' . $i . '</option>';
    }
    $day_select .= '</select>';

    $year_select = '<select name="year">';
    for ( $i = 2004; $i < 2038 ; $i++ )
    {
        $year_select .= '<option value="' . $i . '"' . (($year == $i) ? ' selected="selected"' : "") . '>' . $i . '</option>';
    }
    $year_select .= '</select>';

    // build user select

    $result = DB_query("SELECT * FROM {$_TABLES['users']} ORDER BY username");
    $nRows = DB_numRows($result);
    $user_select = '<select name="user"><option value="" SELECTED>' . $LANG_MG01['all'] . '</option>';
    for ($x=0; $x<$nRows;$x++)
    {
        $row = DB_fetchArray($result);

        $show = sprintf("%-25s = %s", $row['username'],$row['fullname']);
        $show_fixed = str_replace(" ", "&nbsp;", $show);
        $show_fixed = $row['fullname'] . ' (' . $row['username'] . ')';

        $user_select .= '<option value="' . $row['uid'] . '">' . $show_fixed . '</option>';
    }
    $user_select .= '</select>';

    $T->set_var(array(
        's_form_action'         =>  $_CONF['site_admin_url'] . '/plugins/mediagallery/usage_rpt.php',
        'month_select'          =>  $month_select,
        'day_select'            =>  $day_select,
        'year_select'           =>  $year_select,
        'user_select'           =>  $user_select,
        'lang_usage_report'     => $LANG_MG01['usage_report_header'],
        'lang_usage_report_help' => $LANG_MG01['usage_report_help'],
        'lang_all_dates'        => $LANG_MG01['all_dates'],
        'lang_select_user'      => $LANG_MG01['select_user'],
        'lang_submit'           => $LANG_MG01['submit'],
        'lang_select_date'      => $LANG_MG01['select_date'],
    ));

    $T->parse('output','admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

/**
* Main
*/

$display = '';
$mode = '';

if (isset ($_POST['mode'])) {
    $mode = COM_applyFilter($_POST['mode']);
} else if (isset ($_GET['mode'])) {
    $mode = COM_applyFilter($_GET['mode']);
}


$T = new Template($_MG_CONF['template_path'].'/admin');
$T->set_file (array ('admin' => 'administration.thtml'));

$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['pi_version'],
));

if ($mode == $LANG_MG01['submit'] && !empty ($LANG_MG01['submit'])) {
    $T->set_var(array(
        'admin_body'    => MG_usageReport(),
    ));

} elseif ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} else {
    $T->set_var(array(
        'admin_body'    => MG_usageReportMenu(),
        'title'         => $LANG_MG01['usage_reports'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Usage_Reports',
    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>