<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | deleted.php                                                              |
// |                                                                          |
// | glFusion Installation                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2015-2016 by the following authors:                        |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

$obsoletePrivateDir = array(
    'system/classes/openid/',
    'system/pear/Auth/',
    'system/pear/Console/',
    'system/pear/scripts/',
    'system/pear/Services/',

);

$obsoletePublicDir = array(
    'ckeditor/plugins/filemanager/',
    'fckeditor/',
    'webservices/',
);

$obsoletePrivateFiles = array(
    'lib/getid3/dependencies.txt',
    'lib/getid3/readme.txt',
    'lib/getid3/structure.txt',
    'sql/updates/geeklog_mysql_1.4.1_to_1.5.0.php',

    'system/lib-webservices.php',

    'plugins/captcha/class/ayah.php',
    'plugins/captcha/class/picatchalib.php',

    'system/pear/Date.php',
    'system/pear/pearcmd.php',
    'system/pear/peclcmd.php',

    'plugins/spamx/BaseAdmin.class.php',
    'plugins/spamx/BaseCommand.class.php',
    'plugins/spamx/BlackList.Examine.class.php',
    'plugins/spamx/DeleteComment.Action.class.php',
    'plugins/spamx/EditBlackList.Admin.class.php',
    'plugins/spamx/EditHeader.Admin.class.php',
    'plugins/spamx/EditIP.Admin.class.php',
    'plugins/spamx/EditIPofURL.Admin.class.php',
    'plugins/spamx/Header.Examine.class.php',
    'plugins/spamx/IP.Examine.class.php',
    'plugins/spamx/IPofUrl.Examine.class.php',
    'plugins/spamx/MailAdmin.Action.class.php',
    'plugins/spamx/MassDelete.Admin.class.php',
    'plugins/spamx/MassDelTrackback.Admin.class.php',
    'plugins/spamx/SFS.Examine.class.php',
    'plugins/spamx/SFS.Misc.class.php',
    'plugins/spamx/SFSbase.class.php',
    'plugins/spamx/SFSreport.Action.class.php',
    'plugins/spamx/SLV.Examine.class.php',
    'plugins/spamx/SLVbase.class.php',
    'plugins/spamx/SLVreport.Action.class.php',
    'plugins/spamx/SLVwhitelist.Admin.class.php',
    'plugins/spamx/modules/SLV.Examine.class.php',
    'plugins/spamx/modules/SLVbase.class.php',
    'plugins/spamx/modules/SLVreport.Action.class.php',
    'plugins/spamx/modules/SLVwhitelist.Admin.class.php',

    'plugins/spamx/modules/SFSreport.Action.class.php',

    'system/classes/openidhelper.class.php',

);

$obsoletePublicFiles = array(
    'admin/install/help.php',
    'admin/install/layout/arrow-back.gif',
    'admin/install/layout/arrow-next.gif',
    'admin/install/layout/arrow-recheck.gif',
    'admin/install/layout/arrow.png',
    'admin/install/layout/arrow_down.png',
    'admin/install/layout/arrow_open.png',
    'admin/install/layout/bottom-l-corner.png',
    'admin/install/layout/bottom-r-corner.png',
    'admin/install/layout/check.png',
    'admin/install/layout/error.png',
    'admin/install/layout/gl_moomenu1-bg.gif',
    'admin/install/layout/gl_moopngfix.js',
    'admin/install/layout/header-bg.png',
    'admin/install/layout/ie6.css',
    'admin/install/layout/ie7.css',
    'admin/install/layout/mootools-release-1.11.packed.js',
    'admin/install/layout/steplist-bottom.png',
    'admin/install/layout/steplist-repeat.png',
    'admin/install/layout/top-l-corner.png',
    'admin/install/layout/top-r-corner.png',
    'admin/install/layout/top.png',
    'admin/install/toinnodb.php',
    'docs/docstyle.css',
    'docs/images/background.gif',
    'docs/images/index.html',
    'images/openid_login_icon.png',

    'layout/vintage/admin/menu/menulist.thtml',
    'layout/vintage/admin/menu/menutree.thtml',
    'layout/vintage/admin/plugins/plugin_upload_old_confirm.thtml',
    'layout/vintage/admin/user/edituser.thtml',
    'layout/vintage/admin/user/groupedit.thtml',
    'layout/vintage/search/headingcolumn.thtml',
    'layout/vintage/search/resultauthdatehits.thtml',
    'layout/vintage/search/resultcolumn.thtml',
    'layout/vintage/search/resultrow.thtml',
    'layout/vintage/search/resultrowenhanced.thtml',
    'layout/vintage/search/resultsummary.thtml',
    'layout/vintage/search/resulttitle.thtml',
    'layout/vintage/search/searchblock.thtml',
    'layout/vintage/search/searchresults.thtml',
    'layout/vintage/search/searchresults_heading.thtml',
    'layout/vintage/search/searchresults_norows.thtml',
    'layout/vintage/search/searchresults_rows.thtml',
    'layout/vintage/stats/singlesummary.thtml',
    'layout/vintage/stats/sitestatistics.thtml',
    'layout/vintage/submit/submitloginrequired.thtml',
    'layout/vintage/adminoption.thtml',
    'layout/vintage/adminoption_off.thtml',
    'layout/vintage/loginform_openid.thtml',
    'layout/vintage/menuitem.thtml',
    'layout/vintage/menuitem_last.thtml',
    'layout/vintage/menuitem_none.thtml',
    'layout/vintage/useroption.thtml',
    'layout/vintage/useroption_off.thtml',
);

?>