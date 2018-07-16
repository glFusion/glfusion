<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | deleted.php                                                              |
// |                                                                          |
// | glFusion Installation                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2015-2018 by the following authors:                        |
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
// 1.7.0
    'vendor/pear/',
    'system/classes/XML/',
    'lib/email-address-validation/',
    'lib/feedcreator/',
    'lib/geshi/',
    'lib/getid3/',
    'lib/html2text/',
    'lib/htmlpurifier/',
    'lib/http/',
    'lib/oauth/',
    'lib/phpmailer/',
    'lib/simplepie/',
    'system/pear/',
    'lib/utf8/docs/examples/',
    'lib/utf8/docs/',


    'lib/htmlpurifier/HTMLPurifier/DefinitionCache/Serializer/',
    'system/classes/openid/',
    'system/pear/Auth/',
    'system/pear/Console/',
    'system/pear/scripts/',
    'system/pear/Services/',
    'system/classes/syndication/',
    'plugins/mediagallery/templates/import/',
    'system/pear/HTTP/OAuth/',
    'system/pear/Net/DNS/',
    'system/pear/Services/',
);

$obsoletePublicDir = array(
// 1.7.5
    'ckeditor/plugins/codemirror/js/addmon/',
    'ckeditor/plugins/codemirror/js/mode/',

// 1.7.2
    'forum/images/badges/',

// 1.7.0
    'captcha/class/',
// 1.6.3
    'ckeditor/skins/moono/',
    'layout/cms/css/ui-lightness/',
    'layout/cms/css/ui-uikit/',
// older
    'ckeditor/plugins/filemanager/',
    'fckeditor/',
    'webservices/',
    'mediagallery/swfupload/',
    'images/menu/',
    'javascript/mootools/',
);

$obsoletePrivateFiles = array(
// 1.7.0
    'system/classes/filter.class.php',
    'system/classes/output.class.php',
    'system/classes/ajax.class.php',
    'system/classes/dbadmn-utf.class.php',
    'system/classes/oauthhelper.class.php',
    'plugins/bad_behavior2/templates/ban_entry.thtml',
    'lib/geshi/geshi/css-gen.cfg',

    'plugins/captcha/class/error.class.php',
    'plugins/captcha/class/filter.class.php',

// 1.6.5

// 1.6.2
    'lib/ZipLib.class.php',
    'filecheck_data.php',
// 1.6.1
    'plugins/ckeditor/templates/filemanager.thtml',

// 1.6.0
    'lib/simplepie/README.txt',
    'lib/simplepie/simplepie.php',
    'plugins/mediagallery/templates/swfupload.thtml',
    'plugins/staticpages/templates/admin/editor_advanced.thtml',
    'system/classes/authentication/LiveJournal.auth.class.php',
    'system/classes/sanitize.class.php',

    'plugins/mediagallery/templates/xpcreate.thtml',
    'plugins/mediagallery/templates/xplist.thtml',
    'plugins/mediagallery/templates/xplogin.thtml',
    'plugins/mediagallery/templates/xppublish.thtml',

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

    'plugins/captcha/class/recaptchalib.php',
    'plugins/commentfeeds/license',
    'plugins/forum/templates/admin/phpbb3_migrate_confirm.thtml',
    'plugins/forum/templates/admin/phpbb3_migrate_db.thtml',
    'plugins/links/README',
    'system/classes/conversion.class.php',
    'system/lib-compatibility.php',
    'system/pear/HTTP/OAuth.php',
    'system/pear/Net/DNS.php',
    'system/pear/Net/SMTP.php',
    'system/pear/PEAR/Dependency.php',
    'system/pear/PEAR/Remote.php',
    'system/pear/PEAR/RunTest.php',
    'system/pear/README',
    'system/pear/index.html',
);

$obsoletePublicFiles = array(
// 1.7.5
    'admin/refresh.php',
    'bad_behavior2/bad-behavior/screener.inc.php',
    'ckeditor/plugins/codemirror/js/beautify.js',
    'ckeditor/plugins/codemirror/js/beautify-html.js',
    'ckeditor/plugins/codemirror/js/codemirror.js',
    'ckeditor/plugins/codemirror/css/codemirror.ckeditor.css',
    'ckeditor/plugins/codemirror/css/codemirror.css',

// 1.7.4
    'layout/cms/images/icons/autotag.png',
    'filemgmt_data/files/css.pdf',

// 1.6.6
    'mediagallery/players/FMP3.swf',
    'mediagallery/players/XSPF_EV.swf',
    'mediagallery/players/XSPF_RadioV.swf',

// removed in 1.6.5
    'layout/cms/js/jQuery.menutron.min.js',

// removed in v1.6.3
    'layout/cms/menu/custom/menu_horizontal_cascading_navigation_mobile.thtml',
    'javascript/dbbackup.js',
    'javascript/dbadmin.js',
    'admin/install/templates/siteconfig.thtml',

// removed in v1.6.1
    'layout/vintage/plugins/ckeditor/ckeditor.thtml',
    'layout/vintage/plugins/ckeditor/ckeditor_block.thtml',
    'layout/vintage/plugins/ckeditor/ckeditor_comment.thtml',
    'layout/vintage/plugins/ckeditor/ckeditor_contact.thtml',
    'layout/vintage/plugins/ckeditor/ckeditor_email.thtml',
    'layout/vintage/plugins/ckeditor/ckeditor_sp.thtml',
    'layout/vintage/plugins/ckeditor/ckeditor_story.thtml',
    'layout/vintage/plugins/ckeditor/ckeditor_submitstory.thtml',
    'layout/vintage/plugins/ckeditor/filemanager.thtml',

// older releases
    'forum/javascript/forum_fckeditor.js',
    'javascript/advanced_editor.js',
    'javascript/blocks_fckeditor.js',
    'javascript/staticpages_fckeditor.js',
    'javascript/storyeditor_fckeditor.js',
    'javascript/submitcomment_fckeditor.js',
    'javascript/submitcontactuser_fckeditor.js',
    'javascript/submitmailuser_fckeditor.js',
    'javascript/submitstory_fckeditor.js',

    'images/star_small.png',
    'images/starrating.png',
    'images/working.gif',
    'javascript/fValidator.js',
    'mediagallery/gallery_remote2.php',
    'ckeditor/plugins/wsc/dialogs/tmp.html',
    'ckeditor/skins/moono/dialog_opera.css',

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

$obsoleteAdminFiles = array(
// 1.6.7
    'plugins/bad_behavior2/ban.php',
// removed in v1.6.2
    'filecheck.php',
    'plugin_upload.php',

    'install/help.php',
    'install/layout/arrow-back.gif',
    'install/layout/arrow-next.gif',
    'install/layout/arrow-recheck.gif',
    'install/layout/arrow.png',
    'install/layout/arrow_down.png',
    'install/layout/arrow_open.png',
    'install/layout/bottom-l-corner.png',
    'install/layout/bottom-r-corner.png',
    'install/layout/check.png',
    'install/layout/error.png',
    'install/layout/gl_moomenu1-bg.gif',
    'install/layout/gl_moopngfix.js',
    'install/layout/header-bg.png',
    'install/layout/ie6.css',
    'install/layout/ie7.css',
    'install/layout/mootools-release-1.11.packed.js',
    'install/layout/steplist-bottom.png',
    'install/layout/steplist-repeat.png',
    'install/layout/top-l-corner.png',
    'install/layout/top-r-corner.png',
    'install/layout/top.png',
    'install/toinnodb.php',

    'plugins/forum/phpbb3_migrate.php',
    'plugins/mediagallery/xppubwiz.php',
);

?>