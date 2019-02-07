<?php
/**
* glFusion CMS
*
* glFusion Search Page
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*  Tony Bibbs        tony@tonybibbs.com
*  Mark Limburg      mlimburg@users.sourceforge.net
*  Jason Whittenburg jwhitten@securitygeeks.com
*
*/

require_once 'lib-common.php';

if ( isset($_VARS['service_search'] ) ) {
    if ( class_exists ( $_VARS['service_search'],true) ) {
        $className = $_VARS['service_search'];
    } else {
        $className = 'Search';
    }
} else {
    $className = 'Search';
}
$searchObj = new $className;

$page = '';
$title = '';

if (isset ($_GET['mode']) && ($_GET['mode'] == 'search')) {
    $title = $LANG09[11];
    $page  = $searchObj->doSearch();
} else {
    $title = sprintf($LANG09[1],$_CONF['site_name']);
    $page  = $searchObj->showForm();
}

echo COM_siteHeader('menu',$title);
echo $page;
echo COM_siteFooter();
?>