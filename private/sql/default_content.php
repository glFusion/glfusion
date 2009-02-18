<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | default_content.php                                                      |
// |                                                                          |
// | glFusion Default Content                                                 |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
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
    die ('This file can not be used on its own.');
}

// Calendar Default Data
$_DATA['calendar'][] = "INSERT INTO {$_TABLES['eventsubmission']} (eid, title, description, location, datestart, dateend, url, allday, zipcode, state, city, address2, address1, event_type, timestart, timeend) VALUES ('2006051410130162','glFusion installed','Today, you successfully installed this glFusion site.','Your webserver',CURDATE(),CURDATE(),'http://www.glfusion.org/',1,NULL,NULL,NULL,NULL,NULL,'',NULL,NULL) ";

// FileMgmt Default Data
$_DATA['filemgmt'][] = "INSERT INTO {$_TABLES['filemgmt_cat']} (`cid`, `pid`, `title`, `imgurl`, `grp_access`, `grp_writeaccess`) VALUES (1,0,'General','',2,2);";
$_DATA['filemgmt'][] = "INSERT INTO {$_TABLES['filemgmt_filedesc']} (`lid`, `description`) VALUES (1,'Yahoo User Interface Grids CSS framework cheat sheet in .pdf format.');";
$_DATA['filemgmt'][] = "INSERT INTO {$_TABLES['filemgmt_filedetail']} (`lid`, `cid`, `title`, `url`, `homepage`, `version`, `size`, `platform`, `logourl`, `submitter`, `status`, `date`, `hits`, `rating`, `votes`, `comments`) VALUES (1,1,'YUI Grids CSS Cheat Sheet','css.pdf','http://developer.yahoo.com/yui/grids/','v2.6',131072 ,'','',2,1,UNIX_TIMESTAMP(),0,0.0000,0,1);";

// Forum Default Data
$_DATA['forum'][] = "INSERT INTO {$_TABLES['gf_categories']} (`cat_order`, `cat_name`, `cat_dscp`, `id`) VALUES (0,'General','General News and Discussions',1);";
$_DATA['forum'][] = "INSERT INTO {$_TABLES['gf_forums']} (`forum_order`, `forum_name`, `forum_dscp`, `forum_id`, `forum_cat`, `grp_id`, `use_attachment_grpid`, `is_hidden`, `is_readonly`, `no_newposts`, `topic_count`, `post_count`, `last_post_rec`) VALUES (0,'News and Announcements','Site News and Special Announcements',1,1,2,1,0,1,0,1,1,1);";
$_DATA['forum'][] = "INSERT INTO {$_TABLES['gf_moderators']} (`mod_id`, `mod_uid`, `mod_groupid`, `mod_username`, `mod_forum`, `mod_delete`, `mod_ban`, `mod_edit`, `mod_move`, `mod_stick`) VALUES (1,2,0,'Admin','1',1,1,1,1,1);";
$_DATA['forum'][] = "INSERT INTO {$_TABLES['gf_topic']} (`id`, `forum`, `pid`, `uid`, `name`, `date`, `lastupdated`, `last_reply_rec`, `email`, `website`, `subject`, `comment`, `postmode`, `replies`, `views`, `ip`, `mood`, `sticky`, `moved`, `locked`) VALUES (1,1,0,2,'Admin',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),0,NULL,'','Welcome to glFusion','Welcome to glFusion!  We hope you enjoy using your new glFusion site.\r\n\r\nglFusion is designed to provide you with features, functionality, and style, all in an easy to use package.\r\n\r\nYou can visit the [url=http://www.glfusion.org/wiki/]glFusion Wiki[/url] for the latest information on features and how to use them.\r\n\r\nThanks and enjoy!\r\nThe glFusion Team\r\n','text',0,1,'127.0.0.1','',0,0,0);";

// Links Default Data

$_DATA['links'][] = "INSERT INTO {$_TABLES['linkcategories']} (cid, pid, category, description, tid, created, modified, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('site', 'root', 'Root', 'Website root', NULL, NOW(), NOW(), 2, 5, 3, 3, 2, 2);";
$_DATA['links'][] = "INSERT INTO {$_TABLES['linkcategories']} (cid, pid, category, description, tid, created, modified, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('blog-roll', 'site', 'Blog Roll', 'glFusion Related Sites', NULL, NOW(), NOW(), 2, 5, 3, 3, 2, 2);";
$_DATA['links'][] = "INSERT INTO {$_TABLES['links']} (lid, cid, url, description, title, hits, date, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('glfusion.org', 'blog-roll', 'http://www.glfusion.org/', 'Visit glFusion - A site dedicated to enhancing glFusion.', 'glFusion - Enhancing glFusion', 1, NOW(), 2, 5, 3, 3, 2, 2);";
$_DATA['links'][] = "INSERT INTO {$_TABLES['links']} (lid, cid, url, description, title, hits, date, owner_id, group_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('glfusion_manual', 'blog-roll', 'http://www.glfusion.org/filemgmt/index.php?id=220', 'The glFusion manual is a snapshot compilation of the living documentation in the online wiki. It is in .pdf format.', 'glFusion Manual', 1, NOW(), 2, 5, 3, 3, 2, 2);";
$_DATA['links'][] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (10,1,'blogroll_block','phpblock','Blog Roll','all',2,'','','0000-00-00 00:00:00',0,'phpblock_blogroll',4,2,3,3,2,2) ";

// Media Gallery Default Data

// Polls Default Data
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 0, 1, 'CTL Support', 0, '');";
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 0, 2, 'Integrated Plugins', 0, '');";
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 0, 3, 'Nouveau Theme', 0, '');";
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 0, 4, 'Enhanced Security', 0, '');";
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 0, 5, 'Other', 0, '');";
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 1, 1, 'Media Gallery', 0, '');";
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 1, 2, 'Site Tailor', 0, '');";
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 1, 3, 'Forum', 0, '');";
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['pollanswers']}` (`pid`, `qid`, `aid`, `answer`, `votes`, `remark`) VALUES ('glfusionfeaturepoll', 1, 4, 'File Management', 0, '');";
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['pollquestions']}` (`pid`, `qid`, `question`) VALUES ('glfusionfeaturepoll', 0, 'What is the best new feature of glFusion?');";
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['pollquestions']}` (`pid`, `qid`, `question`) VALUES ('glfusionfeaturepoll', 1, 'What is your favorite plugin?');";
$_DATA['polls'][] = "INSERT INTO `{$_TABLES['polltopics']}` (`pid`, `topic`, `voters`, `questions`, `date`, `display`, `is_open`, `hideresults`, `commentcode`, `statuscode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES ('glfusionfeaturepoll', 'Tell us your opinion about glFusion', 0, 2, NOW(), 1, 1, 1, 0, 0, 2, 8, 3, 2, 2, 2);";
$_DATA['polls'][] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (4,1,'polls_block','phpblock','Poll','all',3,'','','0000-00-00 00:00:00',0,'phpblock_polls',4,2,3,3,2,2) ";

// Site Tailor Default Data
$_ST_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['st_menu_elements']} (`id`, `pid`, `menu_id`, `element_label`, `element_type`, `element_subtype`, `element_order`, `element_active`, `element_url`, `element_target`, `group_id`) VALUES
(7, 0, 1, 'Widgets', 1, '', 70, 1, '', '', 2),
(8, 7, 1, 'moorotator', 5, 'gl_moorotator', 10, 1, '', '', 2),
(9, 7, 1, 'moosimplebox', 5, 'gl_moosimplebox', 20, 1, '', '', 2),
(10, 7, 1, 'mooslide', 5, 'gl_mooslide', 30, 1, '', '', 2),
(11, 7, 1, 'moospring', 5, 'gl_moospring', 40, 1, '', '', 2),
(12, 7, 1, 'mootickerRSS', 5, 'gl_mootickerRSS', 50, 1, '', '', 2),
(13, 0, 1, 'Typography', 5, 'typography', 80, 1, '', '', 2);
";

// Spamx Default Data

// Static Pages Default Data
$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['staticpage']} (`sp_id`, `sp_uid`, `sp_title`, `sp_content`, `sp_hits`, `sp_date`, `sp_format`, `sp_onmenu`, `sp_label`, `commentcode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`, `sp_centerblock`, `sp_help`, `sp_tid`, `sp_where`, `sp_php`, `sp_nf`, `sp_inblock`, `postmode`, `sp_search`) VALUES ('typography', 2, 'typography', '<div style=\"margin-bottom: 10px;\">&nbsp;</div>
<blockquote>
<p>This page shows all of the typography styles and settings that can be applied in glFusion, using one of the default themes.<br />
<br />
In addition to extended typography and styles, glFusion also features <a href=\"http://developer.yahoo.com/yui/grids/\" target=\"_blank\" class=\"gl_mootipfixed\" title=\"Yahoo User Interface :: Grids css layout framework\">YUI Grids css support</a> for easy implementation of multi-column layouts.</p>
</blockquote>
<div style=\"border-bottom: 2px solid rgb(247, 247, 247);\" class=\"yui-g\">
<div class=\"yui-u first\"><span class=\"alert\">Create it with the following html:<br />
<strong>&lt;span class=&quot;alert&quot;&gt;....&lt;/span&gt;</strong></span> <span class=\"info\">Create it with the following html:<br />
<strong>&lt;span class=&quot;info&quot;&gt;....&lt;/span&gt;</strong></span> <span class=\"down\">Create it with the following html:<br />
<strong>&lt;span class=&quot;down&quot;&gt;....&lt;/span&gt;</strong></span></div>
<div class=\"yui-u\"><span class=\"note\">Create it with the following html:<br />
<strong>&lt;span class=&quot;note&quot;&gt;....&lt;/span&gt;</strong></span> <span class=\"idea\">Create it with the following html:<br />
<strong>&lt;span class=&quot;idea&quot;&gt;....&lt;/span&gt;</strong></span> <span class=\"help\">Create it with the following html:<br />
<strong>&lt;span class=&quot;help&quot;&gt;...&gt;&lt;/span&gt;</strong></span></div>
</div>
<div style=\"border-bottom: 2px solid rgb(247, 247, 247); padding: 1em;\" class=\"yui-u\">
<div style=\"margin-bottom: 10px;\" class=\"story-featured\">
<h1>Create featured story H1 text with the following html: <strong>&lt;div class=&quot;story-featured&quot;&gt;&lt;h1&gt;....&lt;/h1&gt;&lt;/div&gt;</strong></h1>
<br />
THIS&nbsp;PARAGRAPH&nbsp;IS&nbsp;LEFT&nbsp;JUSTIFIED: Aenean neque est, laoreet quis, condimentum ut, pellentesque et, nulla. Etiam malesuada ipsum egestas lorem. Vestibulum gravida laoreet justo. Maecenas eget tellus mollis lacus cursus suscipit. Phasellus ante ante, dapibus ut, pellentesque eu, tincidunt vel, velit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vivamus aliquet nulla sed nisl. Nullam egestas sagittis leo. Quisque dolor ligula, hendrerit laoreet, gravida sit amet, rutrum et, tortor. Donec lorem dui, varius sed, nonummy ut, viverra ac, metus. Duis in mauris ut erat porta placerat.</div>
<h1>Create H1 text with the following html: <strong>&lt;h1&gt;....&lt;/h1&gt;</strong></h1>
<p style=\"text-align: center;\">THIS&nbsp;PARAGRAPH&nbsp;IS&nbsp;CENTER&nbsp;JUSTIFIED: Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vivamus aliquet nulla sed nisl. Nullam egestas sagittis leo. Quisque dolor ligula, hendrerit laoreet, gravida sit amet, rutrum et, tortor. Donec lorem dui, varius sed, nonummy ut, viverra ac, metus. Duis in mauris ut erat porta placerat. Aenean neque est, laoreet quis, condimentum ut, pellentesque et, nulla. Etiam malesuada ipsum egestas lorem. Vestibulum gravida laoreet justo. Maecenas eget tellus mollis lacus cursus suscipit. Phasellus ante ante, dapibus ut, pellentesque eu, tincidunt vel, velit.</p>
<h2>Create H2 text with the following html: <strong>&lt;h2&gt;....&lt;/h2&gt;</strong></h2>
<p style=\"text-align: right;\">THIS PARAGRAPH IS RIGHT JUSTIFIED: Aenean neque est, laoreet quis, condimentum ut, pellentesque et, nulla. Etiam malesuada ipsum egestas lorem. Vestibulum gravida laoreet justo. Maecenas eget tellus mollis lacus cursus suscipit. Phasellus ante ante, dapibus ut, pellentesque eu, tincidunt vel, velit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vivamus aliquet nulla sed nisl. Nullam egestas sagittis leo. Quisque dolor ligula, hendrerit laoreet, gravida sit amet, rutrum et, tortor. Donec lorem dui, varius sed, nonummy ut, viverra ac, metus. Duis in mauris ut erat porta placerat.</p>
<h3>Create H3 text with the following html: <strong>&lt;h3&gt;....&lt;/h3&gt;</strong></h3>
<p style=\"text-align: justify;\">THIS PARAGRAPH IS BLOCK JUSTIFIED: Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vivamus aliquet nulla sed nisl. Nullam egestas sagittis leo. Quisque dolor ligula, hendrerit laoreet, gravida sit amet, rutrum et, tortor. Donec lorem dui, varius sed, nonummy ut, viverra ac, metus. Duis in mauris ut erat porta placerat. Aenean neque est, laoreet quis, condimentum ut, pellentesque et, nulla. Etiam malesuada ipsum egestas lorem. Vestibulum gravida laoreet justo. Maecenas eget tellus mollis lacus cursus suscipit. Phasellus ante ante, dapibus ut, pellentesque eu, tincidunt vel, velit.</p>
</div>
<div class=\"story-body\" style=\"margin: auto; width: 520px; padding-top: 20px;\">
<pre>
This is a sample <strong>&lt;div class=&quot;story-body&quot;&gt;&lt;pre&gt;...&lt;/pre&gt;&lt;/div&gt;</strong> tag:
=============================================
.story-body pre {
background:#F7F7F7 url(layout/nouveau/images/code.png) no-repeat scroll 5px 50%;
border:3px solid #CCC;
font-size:90%;
line-height:135%;
overflow:auto;
padding:1em 1em 1em 5em;
}
=============================================
</pre>
</div>
<div style=\"border-bottom: 2px solid rgb(247, 247, 247); padding: 1em;\" class=\"yui-u\">This is an example of a block quote. Wrap your text in: <strong>&lt;blockquote&gt;&lt;p&gt;....&lt;/p&gt;&lt;/blockquote&gt;</strong> <blockquote>
<p>Etiam congue risus in mi. Suspendisse scelerisque. Integer vel ante at odio tempor pretium. Proin porta augue quis augue. Aliquam erat volutpat. Proin condimentum. Vivamus gravida convallis massa. Proin turpis.</p>
</blockquote></div>
<div style=\"padding: 0.5em;\" class=\"yui-gb\">

<div class=\"yui-u first\">
<h1>List Styles - Bullets</h1>
<ul class=\"bullet-blue\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>bullet-blue</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
<ul class=\"bullet-grey\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>bullet-grey</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
<ul class=\"bullet-plus\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>bullet-plus</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
<ul class=\"bullet-rss\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>bullet-rss</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
<ul class=\"bullet-star\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>bullet-star</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
</div>

<div class=\"yui-u\">
<h1>List Styles - Images</h1>
<ul class=\"arrow\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>arrow</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
<ul class=\"bug\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>bug</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
<ul class=\"cart\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>cart</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;.<br />
    </strong></li>
</ul>
<ul class=\"check\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>check</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
<ul class=\"script\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>script</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
</div>

<div class=\"yui-u\">
<h1>List Styles - Media</h1>
<ul class=\"disc\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>disc</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
<ul class=\"headphones\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>headphones</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
<ul class=\"mic\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>mic</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
<ul class=\"speaker\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>speaker</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
<ul class=\"video\">
    <li>Use this style with the following html code:<br />
    <strong>&lt;ul class=&quot;</strong><em>video</em><strong>&quot;&gt;&lt;li&gt;....&lt;/li&gt;&lt;/ul&gt;</strong>.</li>
</ul>
</div>
</div>

<div style=\"border-bottom: 1px solid rgb(204, 204, 204); padding: 1em;\" class=\"yui-g\">
<div class=\"yui-u first\">
<h1>List Styles - Blue Numbers &lt;ul class=&quot;number&quot;&gt;...&lt;/ul&gt;</h1>
<ul class=\"number\">
    <li class=\"num-1\">Create this list number with the following html: <strong>&lt;li class=&quot;num-1&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-2\">Create this list number with the following html: <strong>&lt;li class=&quot;num-2&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-3\">Create this list number with the following html: <strong>&lt;li class=&quot;num-3&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-4\">Create this list number with the following html: <strong>&lt;li class=&quot;num-4&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-5\">Create this list number with the following html: <strong>&lt;li class=&quot;num-5&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-6\">Create this list number with the following html: <strong>&lt;li class=&quot;num-6&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-7\">Create this list number with the following html: <strong>&lt;li class=&quot;num-7&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-8\">Create this list number with the following html: <strong>&lt;li class=&quot;num-8&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-9\">Create this list number with the following html: <strong>&lt;li class=&quot;num-9&quot;&gt;...&lt;/li&gt;.</strong></li>
</ul>
</div>

<div class=\"yui-u\">
<h1>List Styles - Grey Numbers &lt;ul class=&quot;number&quot;&gt;...&lt;/ul&gt;</h1>
<ul class=\"number\">
    <li class=\"num-1g\">Create this list number with the following html: <strong>&lt;li class=&quot;num-1g&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-2g\">Create this list number with the following html: <strong>&lt;li class=&quot;num-2g&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-3g\">Create this list number with the following html: <strong>&lt;li class=&quot;num-3g&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-4g\">Create this list number with the following html: <strong>&lt;li class=&quot;num-4g&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-5g\">Create this list number with the following html: <strong>&lt;li class=&quot;num-5g&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-6g\">Create this list number with the following html: <strong>&lt;li class=&quot;num-6g&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-7g\">Create this list number with the following html: <strong>&lt;li class=&quot;num-7g&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-8g\">Create this list number with the following html: <strong>&lt;li class=&quot;num-8g&quot;&gt;...&lt;/li&gt;.</strong></li>
    <li class=\"num-9g\">Create this list number with the following html: <strong>&lt;li class=&quot;num-9g&quot;&gt;...&lt;/li&gt;.</strong></li>
</ul>
</div>
</div>', 1, NOW(), 'noblocks', 0, '', 0, 2, 14, 3, 2, 2, 2, 0, '', 'none', 1, 0, 0, 0, 'html', 0) ";


$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['staticpage']} (`sp_id`, `sp_uid`, `sp_title`, `sp_content`, `sp_hits`, `sp_date`, `sp_format`, `sp_onmenu`, `sp_label`, `commentcode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`, `sp_centerblock`, `sp_help`, `sp_tid`, `sp_where`, `sp_php`, `sp_nf`, `sp_inblock`, `postmode`, `sp_search`) VALUES ('gl_mootickerRSS', 2, 'gl_mootickerRSS', '// this staticpage needs to have PHP set to execute PHP (return) below
// use lib-widgets.php
USES_lib_widgets();

//call the WIDGET_mootickerRSS function from lib-widgets.php
return WIDGET_mootickerRSS();', 1, NOW(), 'allblocks', 0, '', -1, 2, 14, 3, 2, 2, 2, 0, '', 'none', 1, 1, 0, 0, 'html', 0) ";

$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['staticpage']} (`sp_id`, `sp_uid`, `sp_title`, `sp_content`, `sp_hits`, `sp_date`, `sp_format`, `sp_onmenu`, `sp_label`, `commentcode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`, `sp_centerblock`, `sp_help`, `sp_tid`, `sp_where`, `sp_php`, `sp_nf`, `sp_inblock`, `postmode`, `sp_search`) VALUES ('gl_moospring', 2, 'gl_moospring', '// this staticpage needs to have PHP set to execute PHP below
// use lib-widgets.php
USES_lib_widgets();

//call the WIDGET_moospring function from lib-widgets.php
echo WIDGET_moospring();
?>
<center>
<div id=\"gl_moospring\">
	<ul class=\"gl_moosprings\">
		<li>
			<a class=\"gl_moospring gl_moospring1\" href=\"http://www.glfusion.org/filemgmt/index.php\">
				<span>Grab It</span>
			</a>
		</li>
		<li>
			<a class=\"gl_moospring gl_moospring2\" href=\"http://glfusion.org/wiki/doku.php\">
				<span>Read It</span>
			</a>
		</li>
		<li>
			<a class=\"gl_moospring gl_moospring3\" href=\"http://www.glfusion.org/forum/\">
				<span>Say It</span>
			</a>
		</li>
		<li>
			<a class=\"gl_moospring gl_moospring4\" href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:mission\">
				<span>Join Us</span>
			</a>
		</li>
	</ul>
</div>
</center>', 1, NOW(), 'leftblocks', 0, '', -1, 2, 14, 3, 2, 2, 2, 0, '', 'none', 2, 2, 0, 0, 'html', 0) ";

$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['staticpage']} (`sp_id`, `sp_uid`, `sp_title`, `sp_content`, `sp_hits`, `sp_date`, `sp_format`, `sp_onmenu`, `sp_label`, `commentcode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`, `sp_centerblock`, `sp_help`, `sp_tid`, `sp_where`, `sp_php`, `sp_nf`, `sp_inblock`, `postmode`, `sp_search`) VALUES ('gl_mooslide', 2, 'gl_mooslide', '// this staticpage needs to have PHP set to execute PHP (return) below
// use lib-widgets.php
USES_lib_widgets();

// add your staticpage IDs, order here is order they appear on the mooslide tabs
\$slides = Array(\'mooslide_whatsnew\', \'mooslide_cachetech\', \'mooslide_integratedplugins\', \'mooslide_mootools\', \'mooslide_widgets\');

//call the WIDGET_mooslide function from lib-widgets.php
// last 3 options below are width, height, and css id
return WIDGET_mooslide(\$slides, 560, 160, \'gl_slide\');', 1, NOW(), 'leftblocks', 0, '', -1, 2, 14, 3, 2, 2, 2, 1, '', 'none', 3, 1, 0, 0, 'html', 0) ";

$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['staticpage']} (`sp_id`, `sp_uid`, `sp_title`, `sp_content`, `sp_hits`, `sp_date`, `sp_format`, `sp_onmenu`, `sp_label`, `commentcode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`, `sp_centerblock`, `sp_help`, `sp_tid`, `sp_where`, `sp_php`, `sp_nf`, `sp_inblock`, `postmode`, `sp_search`) VALUES ('mooslide_whatsnew', 2, 'What\'s New', '<p><img hspace=\"18\" height=\"135\" width=\"135\" vspace=\"5\" align=\"left\" alt=\"whats new\" src=\"xxxSITEURLxxx/images/library/Image/whatsnew.png\" /></p>
<h3><span style=\"font-size: large; padding-top: 10px;\"><em>What\'s New in glFusion?<br />
</em></span></h3>
<p>&nbsp;<img hspace=\"5\" height=\"8\" width=\"8\" alt=\"\" src=\"xxxSITEURLxxx/layout/nouveau/images/bullet-star.png\" />Create dynamic menus anywhere on your site!</p>
<p>&nbsp;<img hspace=\"5\" height=\"8\" width=\"8\" alt=\"\" src=\"xxxSITEURLxxx/layout/nouveau/images/bullet-star.png\" />Integrated Google-style site search results!</p>
<p>&nbsp;<img hspace=\"5\" height=\"8\" width=\"8\" alt=\"\" src=\"xxxSITEURLxxx/layout/nouveau/images/bullet-star.png\" />Improved centralized configuration utility!</p>
<p>&nbsp;<img hspace=\"5\" height=\"8\" width=\"8\" alt=\"\" src=\"xxxSITEURLxxx/layout/nouveau/images/bullet-star.png\" />Smarter Javascript and CSS handling!</p>
<p>&nbsp;And more! Visit <a target=\"_blank\" href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:whatsnew\">www.glfusion.org</a> for a detailed list!</p>', 1, NOW(), 'allblocks', 0, '', -1, 2, 14, 3, 2, 2, 2, 0, '', 'none', 1, 0, 0, 0, 'adveditor', 0) ";
$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['staticpage']} (`sp_id`, `sp_uid`, `sp_title`, `sp_content`, `sp_hits`, `sp_date`, `sp_format`, `sp_onmenu`, `sp_label`, `commentcode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`, `sp_centerblock`, `sp_help`, `sp_tid`, `sp_where`, `sp_php`, `sp_nf`, `sp_inblock`, `postmode`, `sp_search`) VALUES ('mooslide_mootools', 2, 'MooTools', '<p><a target=\"_blank\" href=\"http://mootools.net\"><img height=\"60\" alt=\"MooTools\" hspace=\"10\" width=\"190\" align=\"right\" vspace=\"10\" src=\"xxxSITEURLxxx/images/library/Image/mootools.png\" /></a></p>
<h3><em><span style=\"margin-top: 10px; font-size: large\">What is MooTools?</span></em></h3>
<p><a target=\"_blank\" href=\"http://mootools.net\">Mootools</a> is a powerful, yet compact, object-oriented javascript framework that is fully integrated into the <em><strong>glFusion CMS</strong></em>.</p>
<p>Their&nbsp;<a target=\"_blank\" href=\"http://mootools.net/docs/\">well-documented API</a> allows devs &amp; site admins to easily create a wide range of dynamic enhancements that extend the functionality in a <em><strong>glFusion</strong></em> powered site.</p>', 1, NOW(), 'allblocks', 0, '', -1, 2, 14, 3, 2, 2, 2, 0, '', 'none', 1, 0, 0, 0, 'adveditor', 0) ";
$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['staticpage']} (`sp_id`, `sp_uid`, `sp_title`, `sp_content`, `sp_hits`, `sp_date`, `sp_format`, `sp_onmenu`, `sp_label`, `commentcode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`, `sp_centerblock`, `sp_help`, `sp_tid`, `sp_where`, `sp_php`, `sp_nf`, `sp_inblock`, `postmode`, `sp_search`) VALUES ('mooslide_widgets', 2, 'Widgets', '<p><img height=\"128\" alt=\"Widgets\" hspace=\"5\" width=\"128\" align=\"left\" vspace=\"10\" src=\"xxxSITEURLxxx/images/library/Image/widgets.png\" /></p>
<h3><em><span style=\"font-size: large\">All Your Favorite Widgets</span></em></h3>
<p>Building on the foundation of <a target=\"_blank\" href=\"http://mootools.net\">Mootools</a>, and coupled with the power of <em><strong>glFusion\'s</strong></em> native block &amp; staticpage editors, we\'ve included some pre-configured widgets to enhance the functionality of your site!</p>
<p>For a complete list of widgets,&nbsp;and how to configure them, visit the <a target=\"_blank\" href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:widgets\">glFusion documentation</a>.</p>', 1, NOW(), 'allblocks', 0, '', -1, 2, 14, 3, 2, 2, 2, 0, '', 'none', 1, 0, 0, 0, 'adveditor', 0) ";
$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['staticpage']} (`sp_id`, `sp_uid`, `sp_title`, `sp_content`, `sp_hits`, `sp_date`, `sp_format`, `sp_onmenu`, `sp_label`, `commentcode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`, `sp_centerblock`, `sp_help`, `sp_tid`, `sp_where`, `sp_php`, `sp_nf`, `sp_inblock`, `postmode`, `sp_search`) VALUES ('mooslide_cachetech', 2, 'Cache Technology', '<p><img hspace=\"5\" height=\"128\" align=\"right\" width=\"128\" alt=\"Cache\" src=\"xxxSITEURLxxx/images/library/Image/cache.png\" /></p>
<h3><em><span style=\"font-size: large;\">Template Caching Technology</span></em></h3>
<p><em><strong>glFusion</strong></em> includes powerful template caching technology that streamlines page rendering &amp; reduces the load on your web server.</p>
<p>It also eliminates redundant template files, which allows for streamlined development, customization &amp; delivery!</p>', 1, NOW(), 'allblocks', 0, '', -1, 2, 14, 3, 2, 2, 2, 0, '', 'none', 1, 0, 0, 0, 'adveditor', 0) ";
$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['staticpage']} (`sp_id`, `sp_uid`, `sp_title`, `sp_content`, `sp_hits`, `sp_date`, `sp_format`, `sp_onmenu`, `sp_label`, `commentcode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`, `sp_centerblock`, `sp_help`, `sp_tid`, `sp_where`, `sp_php`, `sp_nf`, `sp_inblock`, `postmode`, `sp_search`) VALUES ('mooslide_integratedplugins', 2, 'Integrated Plugins', '<p><img hspace=\"5\" height=\"128\" width=\"128\" vspace=\"10\" align=\"left\" src=\"xxxSITEURLxxx/images/library/Image/plugins.png\" alt=\"whats new\" /></p>
<h3><span style=\"font-size: large;\"><em>Integrated Best of Class Plugins</em></span></h3>
<p>&nbsp; <em><strong>glFusion</strong></em> comes with the best plugins pre-installed:</p>
<p>&nbsp;<img hspace=\"5\" height=\"16\" width=\"16\" align=\"top\" src=\"xxxSITEURLxxx/layout/nouveau/images/check.png\" alt=\"\" /><strong>Captcha</strong> - only accept human generated content!</p>
<p>&nbsp;<img hspace=\"5\" height=\"16\" width=\"16\" align=\"top\" src=\"xxxSITEURLxxx/layout/nouveau/images/check.png\" alt=\"\" /><strong>Forum</strong> - enable online discussion &amp; collaboration!</p>
<p>&nbsp;<img hspace=\"5\" height=\"16\" width=\"16\" align=\"top\" src=\"xxxSITEURLxxx/layout/nouveau/images/check.png\" alt=\"\" /><strong>File Mgmt</strong> - manage downloads availabe to visitors!</p>
<p>&nbsp;<img hspace=\"5\" height=\"16\" width=\"16\" align=\"top\" src=\"xxxSITEURLxxx/layout/nouveau/images/check.png\" alt=\"\" /><strong>Media Gallery</strong> - full-featured media management!</p>', 1, NOW(), 'allblocks', 0, '', -1, 2, 14, 3, 2, 2, 2, 0, '', 'none', 1, 0, 0, 0, 'adveditor', 0) ";
$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['staticpage']} (`sp_id`, `sp_uid`, `sp_title`, `sp_content`, `sp_hits`, `sp_date`, `sp_format`, `sp_onmenu`, `sp_label`, `commentcode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`, `sp_centerblock`, `sp_help`, `sp_tid`, `sp_where`, `sp_php`, `sp_nf`, `sp_inblock`, `postmode`, `sp_search`) VALUES ('gl_moorotator', 2, 'gl_moorotator', '// this staticpage needs to have PHP set to execute PHP below
// use lib-widgets.php
USES_lib_widgets();

//call the WIDGET_moorotator function from lib-widgets.php
echo WIDGET_moorotator();
?>
<script type=\"text/javascript\">
	window.addEvent(\'domready\', function() {
		var rotator = new gl_mooRotator(\'gl_moorotator\', {
			controls: true,
			delay: 7000,
			duration: 800,
			autoplay: true
		});
	});
</script>
<div id=\"gl_moorotator\">
	<div class=\"gl_moorotator\">
		<div class=\"gl_moorotatorimage\">
			<a href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:mission\" target=\"_blank\">
				<img src=\"xxxSITEURLxxx/images/library/Image/moorotator1.jpg\" alt=\"glFusion Mission\" title=\"glFusion Mission\" />
			</a>
		</div>
		<div class=\"gl_moorotatortext\">
					<b>Welcome to the glFusion Revolution!</b>
				<p>
				Learn more about the glFusion development philosophy today!
				</p>
		</div>
	</div>

	<div class=\"gl_moorotator\">
		<div class=\"gl_moorotatorimage\">
			<a href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:nouveau#custom_header_images\" target=\"_blank\">
				<img src=\"xxxSITEURLxxx/images/library/Image/moorotator2.jpg\" alt=\"Custom Headers\" title=\"Custom Headers\" />
			</a>
		</div>
		<div class=\"gl_moorotatortext\">
					<b>Custom Header Images</b>
				<p>
				Personalize your site according to your visitors time of day!
				</p>
		</div>
	</div>

	<div class=\"gl_moorotator\">
		<div class=\"gl_moorotatorimage\">
			<a href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:language\" target=\"_blank\">
				<img src=\"xxxSITEURLxxx/images/library/Image/moorotator3.jpg\" alt=\"Language Localization\" title=\"Language Localization\" />
			</a>
		</div>
		<div class=\"gl_moorotatortext\">
					<b>Language Localization</b>
				<p>
				Support for a multi-lingual site right out of the box!
				</p>
		</div>
	</div>


	<div class=\"gl_moorotator\">
		<div class=\"gl_moorotatorimage\">
			<a href=\"http://tracker.glfusion.org\" target=\"_blank\">
				<img src=\"xxxSITEURLxxx/images/library/Image/moorotator4.jpg\" alt=\"Bug Tracker\" title=\"Bug Tracker\" />
			</a>
		</div>
		<div class=\"gl_moorotatortext\">
					<b>Bugs Belong in Nature, not glFusion</b>
				<p>
				Found a bug? Visit our Tracker and submit a report!
				</p>
		</div>
	</div>

	<!-- repeat as needed -->
</div>', 1, NOW(), 'leftblocks', 0, '', -1, 2, 14, 3, 2, 2, 2, 1, '', 'none', 1, 2, 0, 0, 'html', 0) ";
$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['staticpage']} (`sp_id`, `sp_uid`, `sp_title`, `sp_content`, `sp_hits`, `sp_date`, `sp_format`, `sp_onmenu`, `sp_label`, `commentcode`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`, `sp_centerblock`, `sp_help`, `sp_tid`, `sp_where`, `sp_php`, `sp_nf`, `sp_inblock`, `postmode`, `sp_search`) VALUES ('gl_moosimplebox', 2, 'gl_moosimplebox', '<center>
<h2>Click on the logo or text below</h2>
<img id=\"gl_moosimplebox_trigger1\" src=\"xxxSITEURLxxx/layout/nouveau/images/cms-glfusion.gif\" alt=\"mooSimpleBox\" title=\"mooSimpleBox\" /><br />
You can also <span id=\"gl_moosimplebox_trigger2\" style=\"cursor:pointer;\">click me</span>.
</center>

<!-- set the style on the div below to initially be hidden if you have anything more than just text loading in the div -->
<div id=\"my_gl_moosimpleboxDiv\" style=\"visibility:hidden;\">
     This is html content in a sample mooSimpleBox.
     <span class=\"info\">
          You can also make this box draggable by setting the isDrag variable to true.<br />
          Click on the text link to see an example.
     </span>
</div>

<div id=\"my_gl_moosimpleboxDiv2\">
     This is a draggable mooSimpleBox.<br />
     You can change the box styling in your theme\'s style.css file.
</div>

<script type=\"text/javascript\" src=\"xxxSITEURLxxx/javascript/mootools/gl_moosimplebox.js\"></script>
<script language=\"javascript\" type=\"text/javascript\">
window.addEvent(\'domready\',function(){
	var p = new mooSimpleBox({
		width:430,
		height:130,
		btnTitle:\'Test\',
		closeBtn:\'myBtn\',
		btnTitle: \' \',
		boxClass:\'gl_moosimplebox\',
		id:\'gl_moosimplebox\',
		fadeSpeed:500,
		opacity:\'1\',
		addContentID:\'my_gl_moosimpleboxDiv\',
		boxTitle:\'My mooSimpleBox\',
		isDrag:\'false\'
	});
	$(\'gl_moosimplebox_trigger1\').addEvent(\'click\',function(e){
		e = new Event(e).stop();
		p.fadeIn();
	})
})
</script>

<script type=\"text/javascript\" src=\"xxxSITEURLxxx/javascript/mootools/gl_moosimplebox.js\"></script>
<script language=\"javascript\" type=\"text/javascript\">
window.addEvent(\'domready\',function(){
	var p = new mooSimpleBox({
		width:350,
		height:60,
		btnTitle:\'Test\',
		closeBtn:\'myBtn\',
		btnTitle: \' \',
		boxClass:\'gl_moosimplebox\',
		id:\'gl_moosimplebox\',
		fadeSpeed:500,
		opacity:\'1\',
		addContentID:\'my_gl_moosimpleboxDiv2\',
		boxTitle:\'My Draggable mooSimpleBox\',
		isDrag:\'true\'
	});
	$(\'gl_moosimplebox_trigger2\').addEvent(\'click\',function(e){
		e = new Event(e).stop();
		p.fadeIn();
	})
})
</script>', 1, NOW(), 'allblocks', 0, '', -1, 2, 14, 3, 2, 2, 2, 0, '', 'none', 2, 0, 0, 0, 'html', 0) ";

$_SP_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (19,1,'moorotator','normal','Visit glFusion','all',0,'<script type=\"text/javascript\" src=\"xxxSITEURLxxx/javascript/mootools/gl_moorotator-block.js\"></script>
<script type=\"text/javascript\">
	window.addEvent(\'domready\', function() {
		var rotator = new gl_mooRotator_block(\'gl_moorotator_block\', {
			controls: false,  //if true, make sure to specify the absolute path to blankimage var in gl_moorotator-block.js above.
			delay: 4000,
			duration: 800,
			autoplay: true
		});
	});
</script>
<div id=\"gl_moorotator_block\">
	<div class=\"gl_moorotator_block\">
		<div class=\"gl_moorotatorimage_block\">
			<a href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:start\" target=\"_blank\">
				<img alt=\"Documentation Wiki\" title=\"Documentation Wiki\" src=\"xxxSITEURLxxx/images/library/Image/moorotatorblock1.jpg\" />
			</a>
		</div>
		<div class=\"gl_moorotatortext_block\">&nbsp;</div>
	</div>

	<div class=\"gl_moorotator_block\">
		<div class=\"gl_moorotatorimage_block\">
			<a href=\"http://www.glfusion.org/wiki/doku.php?id=roadmap\" target=\"_blank\">
				<img alt=\"glFusion Roadmap\" title=\"glFusion Roadmap\" src=\"xxxSITEURLxxx/images/library/Image/moorotatorblock2.jpg\" />
			</a>
		</div>
		<div class=\"gl_moorotatortext_block\">&nbsp;</div>
	</div>

	<div class=\"gl_moorotator_block\">
		<div class=\"gl_moorotatorimage_block\">
			<a href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:mission#teamspeak\" target=\"_blank\">
				<img alt=\"Talk With Us\" title=\"Talk With Us\" src=\"xxxSITEURLxxx/images/library/Image/moorotatorblock3.jpg\" />
			</a>
		</div>
		<div class=\"gl_moorotatortext_block\">&nbsp;</div>
	</div>

	<div class=\"gl_moorotator_block\">
		<div class=\"gl_moorotatorimage_block\">
			<a href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:mission\" target=\"_blank\">
				<img alt=\"Join Us\" title=\"Join Us\" src=\"xxxSITEURLxxx/images/library/Image/moorotatorblock4.jpg\" />
			</a>
		</div>
		<div class=\"gl_moorotatortext_block\">&nbsp;</div>
	</div>

<!-- repeat as needed -->
</div>','',NOW(),1,'',4,2,3,2,2,2) ";

// Story / Topic Default Data
$_CORE_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['topics']} (tid, topic, imageurl, sortnum, limitnews, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES ('glFusion','glFusion','/images/topics/topic_gl.png',2,10,6,2,3,2,2,2)";
$_CORE_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['stories']} (`sid`, `uid`, `draft_flag`, `tid`, `date`, `title`, `introtext`, `bodytext`, `hits`, `numemails`, `comments`, `comment_expire`, `trackbacks`, `related`, `featured`, `show_topic_icon`, `commentcode`, `trackbackcode`, `statuscode`, `expire`, `postmode`, `advanced_editor_mode`, `frontpage`, `in_transit`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES('glfusion', 2, 0, 'General', NOW(), 'What Can You Do with glFusion?', '<p><strong><em>glFusion</em></strong> gives you the ability to quickly and easily setup a web site complete with extras like forums, CAPTCHA support, calendaring, and full file and media gallery management solutions.&nbsp; Check out the <a href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:quickstart\">Quick Start Guide</a> for more information. Features of your glFusion site include:</p>\r<ul>\r    <li>Integrated Menu Builder - Allowing you to change the site menu using an online editor.</li>\r    <li>Direct integration of the FCKeditor WYSIWYG editor with your theme''s styles.&nbsp; See exactly how your story will look as you type it.</li>\r    <li>Pre-installed plugins, bringing additional functionality and enhanced security:\r    <ul>\r        <li>Bad Behavior2 - A great tool that blocks bot attacks, spam bots, and other nasties from the Internet.</li>\r        <li>CAPTCHA - Ensures that humans are the only ones entering content on your site.</li>\r        <li>Forum - A community collaboration and discussion tool.</li>\r        <li>FileMgmt - A complete File Manager for your site.</li>\r        <li>Media Gallery - A complete multi-media manager.</li>\r    </ul>\r    </li>\r    <li>glFusion gives you a true XHTML compliant site with a pure CSS driven layout.&nbsp; The design is SEO optomized (search engine friendly) and is also more accessible by screen readers and other assistance tools used by the visually impaired.</li>\r    <li>Better documentation - the glFusion community has published a full users guide to glFusion - Check out the online <a href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:start\">wiki</a> at <a href=\"http://www.glfusion.org\">http://www.glfusion.org</a>.</li>\r</ul>\r<p>Welcome to your new site, we hope you enjoy using it as much as we did assembling the software to drive it!</p>\r<p>The glFusion Team</p>', '', 0, 0, 0, '0000-00-00 00:00:00', 0, '<a href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:quickstart\">Quick Start Guide</a>\n<a href=\"http://www.glfusion.org/wiki/doku.php?id=glfusion:start\">wiki</a>\n<a href=\"http://www.glfusion.org\">http://www.glfusion.org</a>', 0, 1, 0, 0, 0, '0000-00-00 00:00:00', 'html', 1, 1, 0, 3, 3, 3, 2, 2, 2)";
$_CORE_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['stories']} (`sid`, `uid`, `draft_flag`, `tid`, `date`, `title`, `introtext`, `bodytext`, `hits`, `numemails`, `comments`, `trackbacks`, `related`, `featured`, `show_topic_icon`, `commentcode`, `trackbackcode`, `statuscode`, `expire`, `postmode`, `advanced_editor_mode`, `frontpage`, `in_transit`, `owner_id`, `group_id`, `perm_owner`, `perm_group`, `perm_members`, `perm_anon`) VALUES ('20080824115808580',2,0,'glFusion',NOW(),'glFusion v".GVERSION." - The Evolution Continues...','<div style=\"margin-bottom: 10px\">&nbsp;</div>\r<blockquote>\r<p><a href=\"http://www.glfusion.org\" target=\"_blank\">glFusion</a>, the next generation content management system built on the foundation of synergy, stability, and style.</p>\r</blockquote>\r<p>&nbsp;</p>\r<div class=\"yui-g\" style=\"border-bottom: rgb(247,247,247) 2px solid\">\r<div class=\"yui-u first\">\r<h2>Better Style</h2>\r<ul>\r    <li>The Nouveau Theme - A versitile and flexible default layout.</li>\r    <li>Improved WYSIWYG editor integration - See the styles available from the theme in the editor.</li>\r    <li>Pure CSS driven layout - Seach Engine friendly and provides a more accessible web site, aiding visually impaired users.</li>\r    <li>XHTML Compliant</li>\r    <li>Additional authoring styles - Improves the look and feel of your stories.</li>\r</ul>\r</div>\r<div class=\"yui-u\">\r<h2>Better Usability</h2>\r<ul>\r    <li>Caching Template Library - Speed and Scalability</li>\r    <li>Integrated Menu Builder</li>\r    <li>Integrated Logo management</li>\r    <li>Integrated Security Features\r    <ul>\r        <li>CAPTCHA Plugin</li>\r        <li>Bad Behavior2 Plugin</li>\r    </ul>\r    </li>\r    <li>Integrated Collaboration Tools\r    <ul>\r        <li>Forum Plugin</li>\r        <li>File Management Plugin</li>\r        <li>Media Management Plugin</li>\r    </ul>\r    </li>\r</ul>\r</div>\r</div>\r<p><a href=\"http://www.glfusion.org\" target=\"_blank\"><strong><em>glFusion</em></strong></a>, the next generation content management system built on the foundation of synergy, stability, and style. It provides a complete content management solution in a single package that lets you get your site up and running quickly, without having to search for 3rd party plugins or components.</p>\r<p><span class=\"alert\"><b>Don\'t forget to change your password after logging in!</b></span></p>','',5,0,0,0,'',1,0,0,0,0,'0000-00-00 00:00:00','html',1,1,0,2,3,3,2,2,2);";
$_CORE_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['storysubmission']} (sid, uid, tid, title, introtext, date, postmode) VALUES ('security-reminder',2,'glFusion','Are you secure?','<p>This is a reminder to secure your site once you have glFusion up and running. What you should do:</p>\r\r<ol>\r<li>Change the default password for the Admin account.</li>\r<li>Remove the install directory (you won\'t need it any more).</li>\r</ol>',NOW(),'html') ";
$_CORE_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (11,0,'gl_mootickerRSS','portal','gl_mootickerRSS','all',10,'<ul class=\"list-feed\">  <li><a href=\"http://www.glfusion.org/article.php?story=glfusion112freeze\">glFusion v1.1.2 Feature Freeze</a></li>  <li><a href=\"http://www.glfusion.org/article.php?story=glfusion102\">glFusion v1.0.2 Released (Security Update)</a></li>  <li><a href=\"http://www.glfusion.org/article.php?story=glfusion110-update2\">glFusion Development Update 2</a></li>  <li><a href=\"http://www.glfusion.org/article.php?story=20080909145217132\">Vote for glFusion at WebMonkey!</a></li>  <li><a href=\"http://www.glfusion.org/article.php?story=glfusionv112devupdate\">glFusion v1.1.2 Development Update</a></li>  <li><a href=\"http://www.glfusion.org/article.php?story=fckeditor-upload-exploit\">FCKEditor Upload Exploit</a></li>  <li><a href=\"http://www.glfusion.org/article.php?story=chameleon200\">Chameleon v2.0.0 for glFusion</a></li>  <li><a href=\"http://www.glfusion.org/article.php?story=20080707224104910\">glFusion v1.0.1 Released</a></li>  <li><a href=\"http://www.glfusion.org/article.php?story=20080629203901111\">glPopMail is ready for testing and feedback</a></li>  <li><a href=\"http://www.glfusion.org/article.php?story=phpversion\">What PHP Version do you use?</a></li></ul>','http://www.glfusion.org/backend/glfusion.rss','0000-00-00 00:00:00',0,'',4,2,3,3,2,2) ";
$_CORE_DEFAULT_DATA[] = "INSERT INTO {$_TABLES['blocks']} (bid, is_enabled, name, type, title, tid, blockorder, content, rdfurl, rdfupdated, onleft, phpblockfn, group_id, owner_id, perm_owner, perm_group, perm_members, perm_anon) VALUES (12,1,'auto_translations','phpblock','Auto Translations','all',0,'','','0000-00-00 00:00:00',0,'phpblock_autotranslations',4,2,3,3,2,2) ";

?>