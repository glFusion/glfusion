<?php
// +---------------------------------------------------------------------------+
// | Media Gallery Plugin 1.6                                                  |
// +---------------------------------------------------------------------------+
// | $Id:: filecheck.php 2145 2008-04-30 04:14:13Z mevans0263                 $|
// |                                                                           |
// | this program will scan your media gallery installation directories        |
// | and locate old, stale files from previous installations.                  |
// | it will give you the opportunity to remove these files, just a nice       |
// | housekeeping exercise, it does not affect Media Gallery's performance     |
// | in any way.                                                               |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2005-2008 by the following authors:                         |
// |                                                                           |
// | Mark R. Evans              - mark@gllabs.org                              |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+
//

require_once('../../../lib-common.php');
require_once($_MG_CONF['path_admin'] . 'navigation.php');
require_once($_MG_CONF['path_html'] . 'lib-upload.php');

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}, IP: " . $_SERVER['REMOTE_ADDR'],1);
    $display  = COM_siteHeader();
    $display .= COM_startBlock($LANG_MG00['access_denied']);
    $display .= $LANG_MG00['access_denied_msg'];
    $display .= COM_endBlock();
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}

// List of files included in Media Gallery v1.4.9    distribution

$_MG_FILES['root']['filelist.txt'] = 1;
$_MG_FILES['root']['INSTALL'] = 1;
$_MG_FILES['root']['config.php'] = 1;
$_MG_FILES['root']['filelist'] = 1;
$_MG_FILES['root']['gpl.txt'] = 1;
$_MG_FILES['root']['im_image.php'] = 1;
$_MG_FILES['root']['pbm_image.php'] = 1;
$_MG_FILES['root']['staticpage.txt'] = 1;
$_MG_FILES['root']['upgrade.inc'] = 1;
$_MG_FILES['root']['ChangeLog'] = 1;
$_MG_FILES['root']['UPGRADE'] = 1;
$_MG_FILES['root']['functions.inc'] = 1;
$_MG_FILES['root']['gd_image.php'] = 1;
$_MG_FILES['root']['admin'] = 1;
$_MG_FILES['root']['docs'] = 1;
$_MG_FILES['root']['language'] = 1;
$_MG_FILES['root']['public_html'] = 1;
$_MG_FILES['root']['sql'] = 1;
$_MG_FILES['root']['templates'] = 1;
$_MG_FILES['root']['tmp'] = 1;
$_MG_FILES['root']['uploads'] = 1;

$_MG_FILES['public_html']['JPEG.php'] = 1;
$_MG_FILES['public_html']['admin.php'] = 1;
$_MG_FILES['public_html']['album.php'] = 1;
$_MG_FILES['public_html']['batch.php'] = 1;
$_MG_FILES['public_html']['classAlbum.php'] = 1;
$_MG_FILES['public_html']['classFrame.php'] = 1;
$_MG_FILES['public_html']['common.php'] = 1;
$_MG_FILES['public_html']['enroll.php'] = 1;
$_MG_FILES['public_html']['exif.php'] = 1;
$_MG_FILES['public_html']['fslideshow.php'] = 1;
$_MG_FILES['public_html']['gallery_remote2.php'] = 1;
$_MG_FILES['public_html']['index.php'] = 1;
$_MG_FILES['public_html']['lib-batch.php'] = 1;
$_MG_FILES['public_html']['lib-media.php'] = 1;
$_MG_FILES['public_html']['lib-rating.php'] = 1;
$_MG_FILES['public_html']['lib-upload.php'] = 1;
$_MG_FILES['public_html']['media.php'] = 1;
$_MG_FILES['public_html']['media_popup.php'] = 1;
$_MG_FILES['public_html']['mgindex.php'] = 1;
$_MG_FILES['public_html']['popup.php'] = 1;
$_MG_FILES['public_html']['property.php'] = 1;
$_MG_FILES['public_html']['rater_rpc.php'] = 1;
$_MG_FILES['public_html']['userprefs.php'] = 1;
$_MG_FILES['public_html']['xml.php'] = 1;
$_MG_FILES['public_html']['xspf.php'] = 1;
$_MG_FILES['public_html']['classMedia.php'] = 1;
$_MG_FILES['public_html']['download.php'] = 1;
$_MG_FILES['public_html']['frames'] = 1;
$_MG_FILES['public_html']['getcard.php'] = 1;
$_MG_FILES['public_html']['lib-exif.php'] = 1;
$_MG_FILES['public_html']['lib-watermark.php'] = 1;
$_MG_FILES['public_html']['lightbox.php'] = 1;
$_MG_FILES['public_html']['mad.php'] = 1;
$_MG_FILES['public_html']['mgcss.php'] = 1;
$_MG_FILES['public_html']['mgjs.php'] = 1;
$_MG_FILES['public_html']['phpinfo.php'] = 1;
$_MG_FILES['public_html']['playall.php'] = 1;
$_MG_FILES['public_html']['postcard.php'] = 1;
$_MG_FILES['public_html']['rater.php'] = 1;
$_MG_FILES['public_html']['search.php'] = 1;
$_MG_FILES['public_html']['simpleviewer.php'] = 1;
$_MG_FILES['public_html']['slideshow.php'] = 1;
$_MG_FILES['public_html']['style.css'] = 1;
$_MG_FILES['public_html']['video.php'] = 1;
$_MG_FILES['public_html']['maint'] = 1;
$_MG_FILES['public_html']['jupload'] = 1;
$_MG_FILES['public_html']['rss'] = 1;
$_MG_FILES['public_html']['classes'] = 1;
$_MG_FILES['public_html']['players'] = 1;
$_MG_FILES['public_html']['watermarks'] = 1;
$_MG_FILES['public_html']['getid3'] = 1;
$_MG_FILES['public_html']['images'] = 1;
$_MG_FILES['public_html']['js'] = 1;
$_MG_FILES['public_html']['mediaobjects'] = 1;
$_MG_FILES['public_html']['docs'] = 1;
$_MG_FILES['public_html']['makers'] = 1;

$_MG_FILES['frames']['legoyellowsmall'] = 1;
$_MG_FILES['frames']['shadow_light'] = 1;
$_MG_FILES['frames']['dotapple'] = 1;
$_MG_FILES['frames']['legoredsmall'] = 1;
$_MG_FILES['frames']['polaroid'] = 1;
$_MG_FILES['frames']['gold2'] = 1;
$_MG_FILES['frames']['shell'] = 1;
$_MG_FILES['frames']['shadow_lighter'] = 1;
$_MG_FILES['frames']['slide'] = 1;
$_MG_FILES['frames']['traintrack'] = 1;
$_MG_FILES['frames']['bamboo'] = 1;
$_MG_FILES['frames']['postage_due'] = 1;
$_MG_FILES['frames']['book'] = 1;
$_MG_FILES['frames']['diapo'] = 1;
$_MG_FILES['frames']['polaroids'] = 1;
$_MG_FILES['frames']['brand'] = 1;
$_MG_FILES['frames']['flicking'] = 1;
$_MG_FILES['frames']['kodak'] = 1;
$_MG_FILES['frames']['gold'] = 1;
$_MG_FILES['frames']['legoyellow'] = 1;
$_MG_FILES['frames']['scrapbook'] = 1;
$_MG_FILES['frames']['scrapbookshadowsmall'] = 1;
$_MG_FILES['frames']['shadow'] = 1;
$_MG_FILES['frames']['legored'] = 1;
$_MG_FILES['frames']['notebook'] = 1;

$_MG_FILES['legoyellowsmall']['BB.gif'] = 1;
$_MG_FILES['legoyellowsmall']['BL.gif'] = 1;
$_MG_FILES['legoyellowsmall']['BR.gif'] = 1;
$_MG_FILES['legoyellowsmall']['LL.gif'] = 1;
$_MG_FILES['legoyellowsmall']['RR.gif'] = 1;
$_MG_FILES['legoyellowsmall']['TL.gif'] = 1;
$_MG_FILES['legoyellowsmall']['TR.gif'] = 1;
$_MG_FILES['legoyellowsmall']['TT.gif'] = 1;
$_MG_FILES['legoyellowsmall']['frame.inc'] = 1;
$_MG_FILES['legoyellowsmall']['pixel_trans.gif'] = 1;

$_MG_FILES['shadow_light']['BB.png'] = 1;
$_MG_FILES['shadow_light']['BBL.png'] = 1;
$_MG_FILES['shadow_light']['BR.png'] = 1;
$_MG_FILES['shadow_light']['RR.png'] = 1;
$_MG_FILES['shadow_light']['RRT.png'] = 1;
$_MG_FILES['shadow_light']['frame.inc'] = 1;

$_MG_FILES['dotapple']['BB.gif'] = 1;
$_MG_FILES['dotapple']['BL.gif'] = 1;
$_MG_FILES['dotapple']['BR.gif'] = 1;
$_MG_FILES['dotapple']['LL.gif'] = 1;
$_MG_FILES['dotapple']['RR.gif'] = 1;
$_MG_FILES['dotapple']['TL.gif'] = 1;
$_MG_FILES['dotapple']['TR.gif'] = 1;
$_MG_FILES['dotapple']['TT.gif'] = 1;
$_MG_FILES['dotapple']['frame.inc'] = 1;

$_MG_FILES['legoredsmall']['BB.gif'] = 1;
$_MG_FILES['legoredsmall']['BL.gif'] = 1;
$_MG_FILES['legoredsmall']['BR.gif'] = 1;
$_MG_FILES['legoredsmall']['LL.gif'] = 1;
$_MG_FILES['legoredsmall']['RR.gif'] = 1;
$_MG_FILES['legoredsmall']['TL.gif'] = 1;
$_MG_FILES['legoredsmall']['TR.gif'] = 1;
$_MG_FILES['legoredsmall']['TT.gif'] = 1;
$_MG_FILES['legoredsmall']['frame.inc'] = 1;
$_MG_FILES['legoredsmall']['pixel_trans.gif'] = 1;

$_MG_FILES['polaroid']['BB.gif'] = 1;
$_MG_FILES['polaroid']['BL.gif'] = 1;
$_MG_FILES['polaroid']['BR.gif'] = 1;
$_MG_FILES['polaroid']['LL.gif'] = 1;
$_MG_FILES['polaroid']['RR.gif'] = 1;
$_MG_FILES['polaroid']['TL.gif'] = 1;
$_MG_FILES['polaroid']['TR.gif'] = 1;
$_MG_FILES['polaroid']['TT.gif'] = 1;
$_MG_FILES['polaroid']['frame.inc'] = 1;

$_MG_FILES['gold2']['BB.jpg'] = 1;
$_MG_FILES['gold2']['BBL.jpg'] = 1;
$_MG_FILES['gold2']['BBR.jpg'] = 1;
$_MG_FILES['gold2']['BL.jpg'] = 1;
$_MG_FILES['gold2']['BR.jpg'] = 1;
$_MG_FILES['gold2']['LL.jpg'] = 1;
$_MG_FILES['gold2']['LLB.jpg'] = 1;
$_MG_FILES['gold2']['LLT.jpg'] = 1;
$_MG_FILES['gold2']['RR.jpg'] = 1;
$_MG_FILES['gold2']['RRB.jpg'] = 1;
$_MG_FILES['gold2']['RRT.jpg'] = 1;
$_MG_FILES['gold2']['TL.jpg'] = 1;
$_MG_FILES['gold2']['TR.jpg'] = 1;
$_MG_FILES['gold2']['TT.jpg'] = 1;
$_MG_FILES['gold2']['TTL.jpg'] = 1;
$_MG_FILES['gold2']['TTR.jpg'] = 1;
$_MG_FILES['gold2']['frame.inc'] = 1;

$_MG_FILES['shell']['BB.jpg'] = 1;
$_MG_FILES['shell']['BL.jpg'] = 1;
$_MG_FILES['shell']['BR.jpg'] = 1;
$_MG_FILES['shell']['LL.jpg'] = 1;
$_MG_FILES['shell']['RR.jpg'] = 1;
$_MG_FILES['shell']['TL.jpg'] = 1;
$_MG_FILES['shell']['TR.jpg'] = 1;
$_MG_FILES['shell']['TT.jpg'] = 1;
$_MG_FILES['shell']['frame.inc'] = 1;

$_MG_FILES['shadow_lighter']['BB.png'] = 1;
$_MG_FILES['shadow_lighter']['BBL.png'] = 1;
$_MG_FILES['shadow_lighter']['BR.png'] = 1;
$_MG_FILES['shadow_lighter']['RR.png'] = 1;
$_MG_FILES['shadow_lighter']['RRT.png'] = 1;
$_MG_FILES['shadow_lighter']['frame.inc'] = 1;

$_MG_FILES['slide']['BB.gif'] = 1;
$_MG_FILES['slide']['BL.gif'] = 1;
$_MG_FILES['slide']['BR.gif'] = 1;
$_MG_FILES['slide']['LL.gif'] = 1;
$_MG_FILES['slide']['RR.gif'] = 1;
$_MG_FILES['slide']['TL.gif'] = 1;
$_MG_FILES['slide']['TR.gif'] = 1;
$_MG_FILES['slide']['TT.gif'] = 1;
$_MG_FILES['slide']['frame.inc'] = 1;

$_MG_FILES['traintrack']['BB.gif'] = 1;
$_MG_FILES['traintrack']['BL.gif'] = 1;
$_MG_FILES['traintrack']['BR.gif'] = 1;
$_MG_FILES['traintrack']['LL.gif'] = 1;
$_MG_FILES['traintrack']['RR.gif'] = 1;
$_MG_FILES['traintrack']['TL.gif'] = 1;
$_MG_FILES['traintrack']['TR.gif'] = 1;
$_MG_FILES['traintrack']['TT.gif'] = 1;
$_MG_FILES['traintrack']['frame.inc'] = 1;

$_MG_FILES['bamboo']['BB.gif'] = 1;
$_MG_FILES['bamboo']['BBL.gif'] = 1;
$_MG_FILES['bamboo']['BBR.gif'] = 1;
$_MG_FILES['bamboo']['BL.gif'] = 1;
$_MG_FILES['bamboo']['BR.gif'] = 1;
$_MG_FILES['bamboo']['LL.gif'] = 1;
$_MG_FILES['bamboo']['LLB.gif'] = 1;
$_MG_FILES['bamboo']['LLT.gif'] = 1;
$_MG_FILES['bamboo']['RR.gif'] = 1;
$_MG_FILES['bamboo']['RRB.gif'] = 1;
$_MG_FILES['bamboo']['RRT.gif'] = 1;
$_MG_FILES['bamboo']['TL.gif'] = 1;
$_MG_FILES['bamboo']['TR.gif'] = 1;
$_MG_FILES['bamboo']['TT.gif'] = 1;
$_MG_FILES['bamboo']['TTL.gif'] = 1;
$_MG_FILES['bamboo']['TTR.gif'] = 1;
$_MG_FILES['bamboo']['frame.inc'] = 1;

$_MG_FILES['postage_due']['BB5.gif'] = 1;
$_MG_FILES['postage_due']['BL5.gif'] = 1;
$_MG_FILES['postage_due']['BR5.gif'] = 1;
$_MG_FILES['postage_due']['LL5.gif'] = 1;
$_MG_FILES['postage_due']['RR5.gif'] = 1;
$_MG_FILES['postage_due']['ReadMe.txt'] = 1;
$_MG_FILES['postage_due']['TL5.gif'] = 1;
$_MG_FILES['postage_due']['TR5.gif'] = 1;
$_MG_FILES['postage_due']['TT5.gif'] = 1;
$_MG_FILES['postage_due']['frame.inc'] = 1;

$_MG_FILES['book']['BB.gif'] = 1;
$_MG_FILES['book']['BL.gif'] = 1;
$_MG_FILES['book']['BR.gif'] = 1;
$_MG_FILES['book']['RR.gif'] = 1;
$_MG_FILES['book']['TR.gif'] = 1;
$_MG_FILES['book']['frame.inc'] = 1;

$_MG_FILES['diapo']['BB.gif'] = 1;
$_MG_FILES['diapo']['BL.gif'] = 1;
$_MG_FILES['diapo']['BR.gif'] = 1;
$_MG_FILES['diapo']['LL.gif'] = 1;
$_MG_FILES['diapo']['RR.gif'] = 1;
$_MG_FILES['diapo']['TL.gif'] = 1;
$_MG_FILES['diapo']['TR.gif'] = 1;
$_MG_FILES['diapo']['TT.gif'] = 1;
$_MG_FILES['diapo']['frame.inc'] = 1;

$_MG_FILES['polaroids']['BB.gif'] = 1;
$_MG_FILES['polaroids']['BL.gif'] = 1;
$_MG_FILES['polaroids']['BR.gif'] = 1;
$_MG_FILES['polaroids']['LL.gif'] = 1;
$_MG_FILES['polaroids']['RR.gif'] = 1;
$_MG_FILES['polaroids']['TL.gif'] = 1;
$_MG_FILES['polaroids']['TR.gif'] = 1;
$_MG_FILES['polaroids']['TT.gif'] = 1;
$_MG_FILES['polaroids']['frame.inc'] = 1;

$_MG_FILES['brand']['BB.jpg'] = 1;
$_MG_FILES['brand']['BBL.jpg'] = 1;
$_MG_FILES['brand']['BBR.jpg'] = 1;
$_MG_FILES['brand']['BL.jpg'] = 1;
$_MG_FILES['brand']['BR.jpg'] = 1;
$_MG_FILES['brand']['LL.jpg'] = 1;
$_MG_FILES['brand']['LLB.jpg'] = 1;
$_MG_FILES['brand']['LLT.jpg'] = 1;
$_MG_FILES['brand']['RR.jpg'] = 1;
$_MG_FILES['brand']['RRB.jpg'] = 1;
$_MG_FILES['brand']['RRT.jpg'] = 1;
$_MG_FILES['brand']['TL.jpg'] = 1;
$_MG_FILES['brand']['TR.jpg'] = 1;
$_MG_FILES['brand']['TT.jpg'] = 1;
$_MG_FILES['brand']['TTL.jpg'] = 1;
$_MG_FILES['brand']['TTR.jpg'] = 1;
$_MG_FILES['brand']['frame.inc'] = 1;

$_MG_FILES['flicking']['BB.gif'] = 1;
$_MG_FILES['flicking']['BL.gif'] = 1;
$_MG_FILES['flicking']['BR.gif'] = 1;
$_MG_FILES['flicking']['LL.gif'] = 1;
$_MG_FILES['flicking']['RR.gif'] = 1;
$_MG_FILES['flicking']['TL.gif'] = 1;
$_MG_FILES['flicking']['TR.gif'] = 1;
$_MG_FILES['flicking']['TT.gif'] = 1;
$_MG_FILES['flicking']['frame.inc'] = 1;

$_MG_FILES['kodak']['BB.gif'] = 1;
$_MG_FILES['kodak']['BBL.gif'] = 1;
$_MG_FILES['kodak']['BBR.gif'] = 1;
$_MG_FILES['kodak']['TT.gif'] = 1;
$_MG_FILES['kodak']['TTL.gif'] = 1;
$_MG_FILES['kodak']['TTR.gif'] = 1;
$_MG_FILES['kodak']['frame.inc'] = 1;

$_MG_FILES['gold']['BB.gif'] = 1;
$_MG_FILES['gold']['BL.gif'] = 1;
$_MG_FILES['gold']['BR.gif'] = 1;
$_MG_FILES['gold']['LL.gif'] = 1;
$_MG_FILES['gold']['RR.gif'] = 1;
$_MG_FILES['gold']['TL.gif'] = 1;
$_MG_FILES['gold']['TR.gif'] = 1;
$_MG_FILES['gold']['TT.gif'] = 1;
$_MG_FILES['gold']['frame.inc'] = 1;

$_MG_FILES['legoyellow']['LL.gif'] = 1;
$_MG_FILES['legoyellow']['TT.gif'] = 1;
$_MG_FILES['legoyellow']['pixel_trans.gif'] = 1;
$_MG_FILES['legoyellow']['BB.gif'] = 1;
$_MG_FILES['legoyellow']['BL.gif'] = 1;
$_MG_FILES['legoyellow']['BR.gif'] = 1;
$_MG_FILES['legoyellow']['RR.gif'] = 1;
$_MG_FILES['legoyellow']['TL.gif'] = 1;
$_MG_FILES['legoyellow']['TR.gif'] = 1;
$_MG_FILES['legoyellow']['frame.inc'] = 1;

$_MG_FILES['scrapbookshadowsmall']['BB.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['BBL.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['BBR.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['BL.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['BR.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['LL.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['LLB.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['LLT.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['RR.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['RRB.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['RRT.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['TL.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['TR.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['TT.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['TTL.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['TTR.gif'] = 1;
$_MG_FILES['scrapbookshadowsmall']['frame.inc'] = 1;

$_MG_FILES['shadow']['BB.png'] = 1;
$_MG_FILES['shadow']['BBL.png'] = 1;
$_MG_FILES['shadow']['BR.png'] = 1;
$_MG_FILES['shadow']['RR.png'] = 1;
$_MG_FILES['shadow']['RRT.png'] = 1;
$_MG_FILES['shadow']['frame.inc'] = 1;

$_MG_FILES['legored']['BB.gif'] = 1;
$_MG_FILES['legored']['BR.gif'] = 1;
$_MG_FILES['legored']['LL.gif'] = 1;
$_MG_FILES['legored']['RR.gif'] = 1;
$_MG_FILES['legored']['TL.gif'] = 1;
$_MG_FILES['legored']['TR.gif'] = 1;
$_MG_FILES['legored']['TT.gif'] = 1;
$_MG_FILES['legored']['pixel_trans.gif'] = 1;
$_MG_FILES['legored']['BL.gif'] = 1;
$_MG_FILES['legored']['frame.inc'] = 1;

$_MG_FILES['notebook']['BB.gif'] = 1;
$_MG_FILES['notebook']['BL.gif'] = 1;
$_MG_FILES['notebook']['BR.gif'] = 1;
$_MG_FILES['notebook']['LL.gif'] = 1;
$_MG_FILES['notebook']['RR.gif'] = 1;
$_MG_FILES['notebook']['TL.gif'] = 1;
$_MG_FILES['notebook']['TR.gif'] = 1;
$_MG_FILES['notebook']['TT.gif'] = 1;
$_MG_FILES['notebook']['frame.inc'] = 1;

$_MG_FILES['maint']['albumedit.php'] = 1;
$_MG_FILES['maint']['batch.php'] = 1;
$_MG_FILES['maint']['caption.php'] = 1;
$_MG_FILES['maint']['global.php'] = 1;
$_MG_FILES['maint']['index.html'] = 1;
$_MG_FILES['maint']['mediamanage.php'] = 1;
$_MG_FILES['maint']['moderate.php'] = 1;
$_MG_FILES['maint']['newmedia.php'] = 1;
$_MG_FILES['maint']['rebuild.php'] = 1;
$_MG_FILES['maint']['remote.php'] = 1;
$_MG_FILES['maint']['rotate.php'] = 1;
$_MG_FILES['maint']['rssfeed.php'] = 1;
$_MG_FILES['maint']['sort.php'] = 1;
$_MG_FILES['maint']['ftpmedia.php'] = 1;

$_MG_FILES['jupload']['index.html'] = 1;
$_MG_FILES['jupload']['jupload.php'] = 1;
$_MG_FILES['jupload']['wjhk.jupload.jar'] = 1;

$_MG_FILES['rss']['index.html'] = 1;

$_MG_FILES['classes']['class.phpmailer.php'] = 1;
$_MG_FILES['classes']['class.smtp.php'] = 1;
$_MG_FILES['classes']['feedcreator.class.php'] = 1;
$_MG_FILES['classes']['index.html'] = 1;
$_MG_FILES['classes']['language'] = 1;
$_MG_FILES['classes']['lgpl.txt'] = 1;

$_MG_FILES['classes_language']['phpmailer.lang-br.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-ca.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-cz.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-de.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-dk.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-en.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-es.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-fi.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-fo.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-fr.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-hu.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-it.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-ja.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-nl.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-no.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-pl.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-ro.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-ru.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-se.php'] = 1;
$_MG_FILES['classes_language']['phpmailer.lang-tr.php'] = 1;

$_MG_FILES['players']['FMP3.swf'] = 1;
$_MG_FILES['players']['FlowPlayer.swf'] = 1;
$_MG_FILES['players']['MGflv.swf'] = 1;
$_MG_FILES['players']['MGmp3.swf'] = 1;
$_MG_FILES['players']['XSPF_EV.swf'] = 1;
$_MG_FILES['players']['XSPF_RadioV.swf'] = 1;
$_MG_FILES['players']['audio-player.js'] = 1;
$_MG_FILES['players']['expressinstall.swf'] = 1;
$_MG_FILES['players']['minislideshow.swf'] = 1;
$_MG_FILES['players']['play.jpg'] = 1;
$_MG_FILES['players']['player.swf'] = 1;
$_MG_FILES['players']['slideshow.swf'] = 1;
$_MG_FILES['players']['sviewer.swf'] = 1;

$_MG_FILES['watermarks']['blank.png'] = 1;
$_MG_FILES['watermarks']['index.html'] = 1;

$_MG_FILES['getid3']['changelog.txt'] = 1;
$_MG_FILES['getid3']['dependencies.txt'] = 1;
$_MG_FILES['getid3']['license.commercial.txt'] = 1;
$_MG_FILES['getid3']['license.txt'] = 1;
$_MG_FILES['getid3']['readme.txt'] = 1;
$_MG_FILES['getid3']['structure.txt'] = 1;
$_MG_FILES['getid3']['getid3'] = 1;

$_MG_FILES['getid3_code']['extension.cache.mysql.php'] = 1;
$_MG_FILES['getid3_code']['getid3.lib.php'] = 1;
$_MG_FILES['getid3_code']['module.archive.gzip.php'] = 1;
$_MG_FILES['getid3_code']['module.archive.rar.php'] = 1;
$_MG_FILES['getid3_code']['module.archive.szip.php'] = 1;
$_MG_FILES['getid3_code']['module.archive.tar.php'] = 1;
$_MG_FILES['getid3_code']['module.archive.zip.php'] = 1;
$_MG_FILES['getid3_code']['module.audio-video.asf.php'] = 1;
$_MG_FILES['getid3_code']['module.audio-video.bink.php'] = 1;
$_MG_FILES['getid3_code']['module.audio-video.flv.php'] = 1;
$_MG_FILES['getid3_code']['module.audio-video.real.php'] = 1;
$_MG_FILES['getid3_code']['module.audio-video.riff.php'] = 1;
$_MG_FILES['getid3_code']['module.audio-video.swf.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.avr.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.bonk.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.dts.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.la.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.lpac.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.midi.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.mod.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.monkey.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.mp3.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.ogg.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.rkau.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.tta.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.vqf.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.wavpack.php'] = 1;
$_MG_FILES['getid3_code']['module.graphic.bmp.php'] = 1;
$_MG_FILES['getid3_code']['module.graphic.jpg.php'] = 1;
$_MG_FILES['getid3_code']['module.graphic.png.php'] = 1;
$_MG_FILES['getid3_code']['module.graphic.svg.php'] = 1;
$_MG_FILES['getid3_code']['module.misc.par2.php'] = 1;
$_MG_FILES['getid3_code']['module.misc.pdf.php'] = 1;
$_MG_FILES['getid3_code']['module.tag.id3v1.php'] = 1;
$_MG_FILES['getid3_code']['module.tag.id3v2.php'] = 1;
$_MG_FILES['getid3_code']['module.tag.lyrics3.php'] = 1;
$_MG_FILES['getid3_code']['write.apetag.php'] = 1;
$_MG_FILES['getid3_code']['write.id3v1.php'] = 1;
$_MG_FILES['getid3_code']['write.id3v2.php'] = 1;
$_MG_FILES['getid3_code']['write.lyrics3.php'] = 1;
$_MG_FILES['getid3_code']['write.php'] = 1;
$_MG_FILES['getid3_code']['write.real.php'] = 1;
$_MG_FILES['getid3_code']['extension.cache.dbm.php'] = 1;
$_MG_FILES['getid3_code']['getid3.php'] = 1;
$_MG_FILES['getid3_code']['module.audio-video.matroska.php'] = 1;
$_MG_FILES['getid3_code']['module.audio-video.mpeg.php'] = 1;
$_MG_FILES['getid3_code']['module.audio-video.nsv.php'] = 1;
$_MG_FILES['getid3_code']['module.audio-video.quicktime.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.aac.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.ac3.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.au.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.flac.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.mpc.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.optimfrog.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.shorten.php'] = 1;
$_MG_FILES['getid3_code']['module.audio.voc.php'] = 1;
$_MG_FILES['getid3_code']['module.graphic.gif.php'] = 1;
$_MG_FILES['getid3_code']['module.graphic.pcd.php'] = 1;
$_MG_FILES['getid3_code']['module.graphic.tiff.php'] = 1;
$_MG_FILES['getid3_code']['module.misc.exe.php'] = 1;
$_MG_FILES['getid3_code']['module.misc.iso.php'] = 1;
$_MG_FILES['getid3_code']['module.misc.msoffice.php'] = 1;
$_MG_FILES['getid3_code']['module.tag.apetag.php'] = 1;
$_MG_FILES['getid3_code']['write.metaflac.php'] = 1;
$_MG_FILES['getid3_code']['write.vorbiscomment.php'] = 1;

$_MG_FILES['images']['closelabel.gif'] = 1;
$_MG_FILES['images']['digibug.gif'] = 1;
$_MG_FILES['images']['index.html'] = 1;
$_MG_FILES['images']['progress_bar_full.gif'] = 1;
$_MG_FILES['images']['1_close.png'] = 1;
$_MG_FILES['images']['1_loading.gif'] = 1;
$_MG_FILES['images']['1_next.png'] = 1;
$_MG_FILES['images']['1_prev.png'] = 1;
$_MG_FILES['images']['arrow-left.gif'] = 1;
$_MG_FILES['images']['arrow-right.gif'] = 1;
$_MG_FILES['images']['blank_blk.jpg'] = 1;
$_MG_FILES['images']['button_help.png'] = 1;
$_MG_FILES['images']['feed.png'] = 1;
$_MG_FILES['images']['icon_envelopeSmall.gif'] = 1;
$_MG_FILES['images']['icon_small_envelope.gif'] = 1;
$_MG_FILES['images']['loading.gif'] = 1;
$_MG_FILES['images']['mediagallery.gif'] = 1;
$_MG_FILES['images']['mediagallery.png'] = 1;
$_MG_FILES['images']['nextlabel.gif'] = 1;
$_MG_FILES['images']['powerby_mg-REVERSED.gif'] = 1;
$_MG_FILES['images']['powerby_mg.gif'] = 1;
$_MG_FILES['images']['powerby_mg.png'] = 1;
$_MG_FILES['images']['prevlabel.gif'] = 1;
$_MG_FILES['images']['progress_bar.gif'] = 1;
$_MG_FILES['images']['rotate_left_icon.gif'] = 1;
$_MG_FILES['images']['rotate_right_icon.gif'] = 1;
$_MG_FILES['images']['shutterfly.jpeg'] = 1;
$_MG_FILES['images']['stamp.gif'] = 1;
$_MG_FILES['images']['star_small.gif'] = 1;
$_MG_FILES['images']['star_small.png'] = 1;
$_MG_FILES['images']['starrating.png'] = 1;
$_MG_FILES['images']['working.gif'] = 1;

$_MG_FILES['js']['admin_editor.js'] = 1;
$_MG_FILES['js']['avdefaults_editor.js'] = 1;
$_MG_FILES['js']['client_sniff.js'] = 1;
$_MG_FILES['js']['defaults_editor.js'] = 1;
$_MG_FILES['js']['index.html'] = 1;
$_MG_FILES['js']['mediagallery.js'] = 1;
$_MG_FILES['js']['memberalbums_editor.js'] = 1;
// $_MG_FILES['js']['mootools.js'] = 1;
$_MG_FILES['js']['mootools-release-1.11.compressed.js'] = 1;
$_MG_FILES['js']['mootools-release-1.11.js'] = 1;
$_MG_FILES['js']['mootools-release-1.11.packed.js'] = 1;
$_MG_FILES['js']['qtobject.js'] = 1;
$_MG_FILES['js']['rating.js'] = 1;
$_MG_FILES['js']['slideshow.js'] = 1;
$_MG_FILES['js']['slimbox.js'] = 1;
$_MG_FILES['js']['swfobject.js'] = 1;

$_MG_FILES['mediaobjects']['audio.png'] = 1;
$_MG_FILES['mediaobjects']['empty.png'] = 1;
$_MG_FILES['mediaobjects']['flash.png'] = 1;
$_MG_FILES['mediaobjects']['flv.png'] = 1;
$_MG_FILES['mediaobjects']['generic.png'] = 1;
$_MG_FILES['mediaobjects']['googlevideo.png'] = 1;
$_MG_FILES['mediaobjects']['index.html'] = 1;
$_MG_FILES['mediaobjects']['missing.png'] = 1;
$_MG_FILES['mediaobjects']['pdf.png'] = 1;
$_MG_FILES['mediaobjects']['quicktime.png'] = 1;
$_MG_FILES['mediaobjects']['remote.png'] = 1;
$_MG_FILES['mediaobjects']['video.png'] = 1;
$_MG_FILES['mediaobjects']['wmp.png'] = 1;
$_MG_FILES['mediaobjects']['youtube.png'] = 1;
$_MG_FILES['mediaobjects']['zip.png'] = 1;
$_MG_FILES['mediaobjects']['covers'] = 1;
$_MG_FILES['mediaobjects']['orig'] = 1;
$_MG_FILES['mediaobjects']['disp'] = 1;
$_MG_FILES['mediaobjects']['tn'] = 1;

$_MG_FILES['covers']['index.html'] = 1;

$_MG_FILES['orig']['index.html'] = 1;
$_MG_FILES['orig']['4'] = 1;
$_MG_FILES['orig']['5'] = 1;
$_MG_FILES['orig']['6'] = 1;
$_MG_FILES['orig']['7'] = 1;
$_MG_FILES['orig']['8'] = 1;
$_MG_FILES['orig']['9'] = 1;
$_MG_FILES['orig']['0'] = 1;
$_MG_FILES['orig']['1'] = 1;
$_MG_FILES['orig']['2'] = 1;
$_MG_FILES['orig']['3'] = 1;
$_MG_FILES['orig']['a'] = 1;
$_MG_FILES['orig']['b'] = 1;
$_MG_FILES['orig']['c'] = 1;
$_MG_FILES['orig']['d'] = 1;
$_MG_FILES['orig']['e'] = 1;
$_MG_FILES['orig']['f'] = 1;

$_MG_FILES['orig_0']['index.html'] = 1;
$_MG_FILES['orig_1']['index.html'] = 1;
$_MG_FILES['orig_2']['index.html'] = 1;
$_MG_FILES['orig_3']['index.html'] = 1;
$_MG_FILES['orig_4']['index.html'] = 1;
$_MG_FILES['orig_5']['index.html'] = 1;
$_MG_FILES['orig_6']['index.html'] = 1;
$_MG_FILES['orig_7']['index.html'] = 1;
$_MG_FILES['orig_8']['index.html'] = 1;
$_MG_FILES['orig_9']['index.html'] = 1;
$_MG_FILES['orig_a']['index.html'] = 1;
$_MG_FILES['orig_b']['index.html'] = 1;
$_MG_FILES['orig_c']['index.html'] = 1;
$_MG_FILES['orig_d']['index.html'] = 1;
$_MG_FILES['orig_e']['index.html'] = 1;
$_MG_FILES['orig_f']['index.html'] = 1;

$_MG_FILES['disp']['index.html'] = 1;
$_MG_FILES['disp']['4'] = 1;
$_MG_FILES['disp']['5'] = 1;
$_MG_FILES['disp']['6'] = 1;
$_MG_FILES['disp']['7'] = 1;
$_MG_FILES['disp']['8'] = 1;
$_MG_FILES['disp']['9'] = 1;
$_MG_FILES['disp']['0'] = 1;
$_MG_FILES['disp']['1'] = 1;
$_MG_FILES['disp']['2'] = 1;
$_MG_FILES['disp']['3'] = 1;
$_MG_FILES['disp']['a'] = 1;
$_MG_FILES['disp']['b'] = 1;
$_MG_FILES['disp']['c'] = 1;
$_MG_FILES['disp']['d'] = 1;
$_MG_FILES['disp']['e'] = 1;
$_MG_FILES['disp']['f'] = 1;

$_MG_FILES['disp_0']['index.html'] = 1;
$_MG_FILES['disp_1']['index.html'] = 1;
$_MG_FILES['disp_2']['index.html'] = 1;
$_MG_FILES['disp_3']['index.html'] = 1;
$_MG_FILES['disp_4']['index.html'] = 1;
$_MG_FILES['disp_5']['index.html'] = 1;
$_MG_FILES['disp_6']['index.html'] = 1;
$_MG_FILES['disp_7']['index.html'] = 1;
$_MG_FILES['disp_8']['index.html'] = 1;
$_MG_FILES['disp_9']['index.html'] = 1;
$_MG_FILES['disp_a']['index.html'] = 1;
$_MG_FILES['disp_b']['index.html'] = 1;
$_MG_FILES['disp_c']['index.html'] = 1;
$_MG_FILES['disp_d']['index.html'] = 1;
$_MG_FILES['disp_e']['index.html'] = 1;
$_MG_FILES['disp_f']['index.html'] = 1;


$_MG_FILES['tn']['index.html'] = 1;
$_MG_FILES['tn']['4'] = 1;
$_MG_FILES['tn']['5'] = 1;
$_MG_FILES['tn']['6'] = 1;
$_MG_FILES['tn']['7'] = 1;
$_MG_FILES['tn']['8'] = 1;
$_MG_FILES['tn']['9'] = 1;
$_MG_FILES['tn']['0'] = 1;
$_MG_FILES['tn']['1'] = 1;
$_MG_FILES['tn']['2'] = 1;
$_MG_FILES['tn']['3'] = 1;
$_MG_FILES['tn']['a'] = 1;
$_MG_FILES['tn']['b'] = 1;
$_MG_FILES['tn']['c'] = 1;
$_MG_FILES['tn']['d'] = 1;
$_MG_FILES['tn']['e'] = 1;
$_MG_FILES['tn']['f'] = 1;

$_MG_FILES['tn_0']['index.html'] = 1;
$_MG_FILES['tn_1']['index.html'] = 1;
$_MG_FILES['tn_2']['index.html'] = 1;
$_MG_FILES['tn_3']['index.html'] = 1;
$_MG_FILES['tn_4']['index.html'] = 1;
$_MG_FILES['tn_5']['index.html'] = 1;
$_MG_FILES['tn_6']['index.html'] = 1;
$_MG_FILES['tn_7']['index.html'] = 1;
$_MG_FILES['tn_8']['index.html'] = 1;
$_MG_FILES['tn_9']['index.html'] = 1;
$_MG_FILES['tn_a']['index.html'] = 1;
$_MG_FILES['tn_b']['index.html'] = 1;
$_MG_FILES['tn_c']['index.html'] = 1;
$_MG_FILES['tn_d']['index.html'] = 1;
$_MG_FILES['tn_e']['index.html'] = 1;
$_MG_FILES['tn_f']['index.html'] = 1;

$_MG_FILES['docs']['index.html'] = 1;
$_MG_FILES['docs']['mediaGallery_logo.png'] = 1;
$_MG_FILES['docs']['upgrade.html'] = 1;
$_MG_FILES['docs']['usage.html'] = 1;
$_MG_FILES['docs']['images'] = 1;

$_MG_FILES['makers']['canon.php'] = 1;
$_MG_FILES['makers']['fujifilm.php'] = 1;
$_MG_FILES['makers']['gps.php'] = 1;
$_MG_FILES['makers']['index.html'] = 1;
$_MG_FILES['makers']['nikon.php'] = 1;
$_MG_FILES['makers']['olympus.php'] = 1;
$_MG_FILES['makers']['sanyo.php'] = 1;

$_MG_FILES['uploads']['README'] = 1;

$_MG_FILES['sql']['mssql_install.php'] = 1;
$_MG_FILES['sql']['sql_defaults.php'] = 1;
$_MG_FILES['sql']['sql_install.php'] = 1;

$_MG_FILES['templates']['album_page_body_media_cell_1.thtml'] = 1;
$_MG_FILES['templates']['album_page_body_media_cell_rating.thtml'] = 1;
$_MG_FILES['templates']['createmembers.thtml'] = 1;
$_MG_FILES['templates']['edit_mov_options.thtml'] = 1;
$_MG_FILES['templates']['edit_swf_options.thtml'] = 1;
$_MG_FILES['templates']['editalbum_admin.thtml'] = 1;
$_MG_FILES['templates']['embed.thtml'] = 1;
$_MG_FILES['templates']['envcheck.thtml'] = 1;
$_MG_FILES['templates']['export.thtml'] = 1;
$_MG_FILES['templates']['filecheck.thtml'] = 1;
$_MG_FILES['templates']['fslideshow.thtml'] = 1;
$_MG_FILES['templates']['ftpimport.thtml'] = 1;
$_MG_FILES['templates']['mediarow.thtml'] = 1;
$_MG_FILES['templates']['moderate.thtml'] = 1;
$_MG_FILES['templates']['mp3_wmp.thtml'] = 1;
$_MG_FILES['templates']['pc_edit.thtml'] = 1;
$_MG_FILES['templates']['pc_preview.thtml'] = 1;
$_MG_FILES['templates']['remoteupload.thtml'] = 1;
$_MG_FILES['templates']['sessitems.thtml'] = 1;
$_MG_FILES['templates']['userupload.thtml'] = 1;
$_MG_FILES['templates']['view_asf.thtml'] = 1;
$_MG_FILES['templates']['xplogin.thtml'] = 1;
$_MG_FILES['templates']['administration.thtml'] = 1;
$_MG_FILES['templates']['album_page.thtml'] = 1;
$_MG_FILES['templates']['album_page_body_album_cell.thtml'] = 1;
$_MG_FILES['templates']['album_page_body_media_cell.thtml'] = 1;
$_MG_FILES['templates']['album_page_body_media_cell_comment.thtml'] = 1;
$_MG_FILES['templates']['album_page_body_media_cell_keywords.thtml'] = 1;
$_MG_FILES['templates']['album_page_body_media_cell_podcast.thtml'] = 1;
$_MG_FILES['templates']['album_page_body_media_cell_view.thtml'] = 1;
$_MG_FILES['templates']['album_page_header.thtml'] = 1;
$_MG_FILES['templates']['album_page_noitems.thtml'] = 1;
$_MG_FILES['templates']['albumrow.thtml'] = 1;
$_MG_FILES['templates']['autotag_nb.thtml'] = 1;
$_MG_FILES['templates']['batch_percent.thtml'] = 1;
$_MG_FILES['templates']['batch_progress.thtml'] = 1;
$_MG_FILES['templates']['cat_noitems.thtml'] = 1;
$_MG_FILES['templates']['category.thtml'] = 1;
$_MG_FILES['templates']['cb_featured_album.thtml'] = 1;
$_MG_FILES['templates']['cb_featured_album.thtml.2'] = 1;
$_MG_FILES['templates']['cfgedit.thtml'] = 1;
$_MG_FILES['templates']['cleanlist.thtml'] = 1;
$_MG_FILES['templates']['confirm.thtml'] = 1;
$_MG_FILES['templates']['editalbum.thtml'] = 1;
$_MG_FILES['templates']['editalbum_formats.thtml'] = 1;
$_MG_FILES['templates']['editavdefaults.thtml'] = 1;
$_MG_FILES['templates']['editcategory.thtml'] = 1;
$_MG_FILES['templates']['exif_tags.thtml'] = 1;
$_MG_FILES['templates']['featured_album.thtml'] = 1;
$_MG_FILES['templates']['ftpupload.thtml'] = 1;
$_MG_FILES['templates']['gallery_page_body_1.thtml'] = 1;
$_MG_FILES['templates']['gallery_page_footer.thtml'] = 1;
$_MG_FILES['templates']['gallery_page_noitems.thtml'] = 1;
$_MG_FILES['templates']['global_album_attr.thtml'] = 1;
$_MG_FILES['templates']['jupload.thtml'] = 1;
$_MG_FILES['templates']['massdelete.thtml'] = 1;
$_MG_FILES['templates']['mediaitems.thtml'] = 1;
$_MG_FILES['templates']['medialink.thtml'] = 1;
$_MG_FILES['templates']['modemail.thtml'] = 1;
$_MG_FILES['templates']['playall_xspf.thtml'] = 1;
$_MG_FILES['templates']['postcard.thtml'] = 1;
$_MG_FILES['templates']['property.thtml'] = 1;
$_MG_FILES['templates']['quotaconfirm.thtml'] = 1;
$_MG_FILES['templates']['quotareport.thtml'] = 1;
$_MG_FILES['templates']['random_block.thtml'] = 1;
$_MG_FILES['templates']['search_results2.thtml'] = 1;
$_MG_FILES['templates']['sortalbum.thtml'] = 1;
$_MG_FILES['templates']['staticsort.thtml'] = 1;
$_MG_FILES['templates']['swf.thtml'] = 1;
$_MG_FILES['templates']['success.thtml'] = 1;
$_MG_FILES['templates']['upload.thtml'] = 1;
$_MG_FILES['templates']['usage_menu.thtml'] = 1;
$_MG_FILES['templates']['useredit.thtml'] = 1;
$_MG_FILES['templates']['useruploadstatus.thtml'] = 1;
$_MG_FILES['templates']['view_flv.thtml'] = 1;
$_MG_FILES['templates']['view_flv_light.thtml'] = 1;
$_MG_FILES['templates']['view_flv_light_stream.thtml'] = 1;
$_MG_FILES['templates']['view_image.thtml'] = 1;
$_MG_FILES['templates']['view_image_detail.thtml'] = 1;
$_MG_FILES['templates']['view_mp3_qt.thtml'] = 1;
$_MG_FILES['templates']['view_mp3_wmp.thtml'] = 1;
$_MG_FILES['templates']['view_quicktime.thtml'] = 1;
$_MG_FILES['templates']['view_swf.thtml'] = 1;
$_MG_FILES['templates']['wm_upload.thtml'] = 1;
$_MG_FILES['templates']['wmitems.thtml'] = 1;
$_MG_FILES['templates']['wmmanage.thtml'] = 1;
$_MG_FILES['templates']['xpcreate.thtml'] = 1;
$_MG_FILES['templates']['xspf_radio.thtml'] = 1;
$_MG_FILES['templates']['album_page_body.thtml'] = 1;
$_MG_FILES['templates']['album_page_body_album_cell_1.thtml'] = 1;
$_MG_FILES['templates']['album_page_footer.thtml'] = 1;
$_MG_FILES['templates']['albumblock.thtml'] = 1;
$_MG_FILES['templates']['asf.thtml'] = 1;
$_MG_FILES['templates']['autotag.thtml'] = 1;
$_MG_FILES['templates']['autotag_ss.thtml'] = 1;
$_MG_FILES['templates']['batch_caption_edit.thtml'] = 1;
$_MG_FILES['templates']['batch_caption_media_items.thtml'] = 1;
$_MG_FILES['templates']['catitems.thtml'] = 1;
$_MG_FILES['templates']['convert_4images_settings.thtml'] = 1;
$_MG_FILES['templates']['convert_inmemoriam.thtml'] = 1;
$_MG_FILES['templates']['deletealbum.thtml'] = 1;
$_MG_FILES['templates']['digibug.thtml'] = 1;
$_MG_FILES['templates']['edit_album_perm_member.thtml'] = 1;
$_MG_FILES['templates']['edit_album_permissions.thtml'] = 1;
$_MG_FILES['templates']['edit_asf_options.thtml'] = 1;
$_MG_FILES['templates']['edit_flv_options.thtml'] = 1;
$_MG_FILES['templates']['edit_mp3_options.thtml'] = 1;
$_MG_FILES['templates']['editdefaults.thtml'] = 1;
$_MG_FILES['templates']['editmember.thtml'] = 1;
$_MG_FILES['templates']['enroll.thtml'] = 1;
$_MG_FILES['templates']['error.thtml'] = 1;
$_MG_FILES['templates']['exif_detail.thtml'] = 1;
$_MG_FILES['templates']['filelist.thtml'] = 1;
$_MG_FILES['templates']['flvfp.thtml'] = 1;
$_MG_FILES['templates']['flvmg.thtml'] = 1;
$_MG_FILES['templates']['flvpopup.thtml'] = 1;
$_MG_FILES['templates']['fsat.thtml'] = 1;
$_MG_FILES['templates']['gallery_page.thtml'] = 1;
$_MG_FILES['templates']['gallery_page_body_2.thtml'] = 1;
$_MG_FILES['templates']['gallery_page_body_3.thtml'] = 1;
$_MG_FILES['templates']['gallery_page_header.thtml'] = 1;
$_MG_FILES['templates']['getcard.thtml'] = 1;
$_MG_FILES['templates']['gl_story_import.thtml'] = 1;
$_MG_FILES['templates']['global_album_perm.thtml'] = 1;
$_MG_FILES['templates']['import_config_dir.thtml'] = 1;
$_MG_FILES['templates']['import_coppermine.thtml'] = 1;
$_MG_FILES['templates']['import_gallery2_parms.thtml'] = 1;
$_MG_FILES['templates']['import_gallery_dir.thtml'] = 1;
$_MG_FILES['templates']['import_select_items.thtml'] = 1;
$_MG_FILES['templates']['install.thtml'] = 1;
$_MG_FILES['templates']['mediablock.thtml'] = 1;
$_MG_FILES['templates']['mediaedit.thtml'] = 1;
$_MG_FILES['templates']['mediamanage.thtml'] = 1;
$_MG_FILES['templates']['mg_navigation.thtml'] = 1;
$_MG_FILES['templates']['mp3_podcast.thtml'] = 1;
$_MG_FILES['templates']['mp3_qt.thtml'] = 1;
$_MG_FILES['templates']['mp3_swf.thtml'] = 1;
$_MG_FILES['templates']['pc-error.thtml'] = 1;
$_MG_FILES['templates']['purgealbums.thtml'] = 1;
$_MG_FILES['templates']['quicktime.thtml'] = 1;
$_MG_FILES['templates']['rssedit.thtml'] = 1;
$_MG_FILES['templates']['search.thtml'] = 1;
$_MG_FILES['templates']['search_results.thtml'] = 1;
$_MG_FILES['templates']['sess_noitems.thtml'] = 1;
$_MG_FILES['templates']['sessions.thtml'] = 1;
$_MG_FILES['templates']['slideshow.thtml'] = 1;
$_MG_FILES['templates']['slideshow_empty.thtml'] = 1;
$_MG_FILES['templates']['staticsortalbums.thtml'] = 1;
$_MG_FILES['templates']['staticsortmedia.thtml'] = 1;
$_MG_FILES['templates']['swfobject.thtml'] = 1;
$_MG_FILES['templates']['thumbs.thtml'] = 1;
$_MG_FILES['templates']['usage_rpt.thtml'] = 1;
$_MG_FILES['templates']['userprefs.thtml'] = 1;
$_MG_FILES['templates']['view_audio.thtml'] = 1;
$_MG_FILES['templates']['view_mp3_flv.thtml'] = 1;
$_MG_FILES['templates']['view_mp3_swf.thtml'] = 1;
$_MG_FILES['templates']['view_video.thtml'] = 1;
$_MG_FILES['templates']['xplist.thtml'] = 1;
$_MG_FILES['templates']['xppublish.thtml'] = 1;
$_MG_FILES['templates']['themes'] = 1;

$_MG_FILES['skins']['README'] = 1;
$_MG_FILES['skins']['clean'] = 1;
$_MG_FILES['skins']['podcast'] = 1;
$_MG_FILES['skins']['simpleviewer'] = 1;
$_MG_FILES['skins']['lightbox'] = 1;

$_MG_FILES['clean']['album_page.thtml'] = 1;
$_MG_FILES['clean']['album_page_body.thtml'] = 1;
$_MG_FILES['clean']['album_page_body_album_cell.thtml'] = 1;
$_MG_FILES['clean']['album_page_body_media_cell.thtml'] = 1;
$_MG_FILES['clean']['album_page_footer.thtml'] = 1;
$_MG_FILES['clean']['album_page_header.thtml'] = 1;
$_MG_FILES['clean']['style.css'] = 1;
$_MG_FILES['clean']['view_audio.thtml'] = 1;
$_MG_FILES['clean']['view_image.thtml'] = 1;
$_MG_FILES['clean']['view_video.thtml'] = 1;

$_MG_FILES['podcast']['album_page_body.thtml'] = 1;
$_MG_FILES['podcast']['album_page_body_album_cell.thtml'] = 1;
$_MG_FILES['podcast']['album_page_body_album_cell_1.thtml'] = 1;
$_MG_FILES['podcast']['album_page_body_media_cell.thtml'] = 1;
$_MG_FILES['podcast']['album_page_body_media_cell_1.thtml'] = 1;

$_MG_FILES['simpleviewer']['album_page_body.thtml'] = 1;

$_MG_FILES['lightbox']['album_page_body_media_cell.thtml'] = 1;
$_MG_FILES['lightbox']['album_page_body_media_cell_1.thtml'] = 1;
$_MG_FILES['lightbox']['album_page_body_media_cell_comment.thtml'] = 1;
$_MG_FILES['lightbox']['medialink.thtml'] = 1;

$_MG_FILES['tmp']['README'] = 1;

$_MG_FILES['admin']['avdefaults.php'] = 1;
$_MG_FILES['admin']['category.php'] = 1;
$_MG_FILES['admin']['cfgedit.php'] = 1;
$_MG_FILES['admin']['check.png'] = 1;
$_MG_FILES['admin']['createmembers.php'] = 1;
$_MG_FILES['admin']['defaults.php'] = 1;
$_MG_FILES['admin']['dvlpupdate.php'] = 1;
$_MG_FILES['admin']['edituser.php'] = 1;
$_MG_FILES['admin']['envcheck.php'] = 1;
$_MG_FILES['admin']['exif_admin.php'] = 1;
$_MG_FILES['admin']['export.php'] = 1;
$_MG_FILES['admin']['filecheck.php'] = 1;
$_MG_FILES['admin']['importers'] = 1;
$_MG_FILES['admin']['index.php'] = 1;
$_MG_FILES['admin']['install.php'] = 1;
$_MG_FILES['admin']['install_doc.html'] = 1;
$_MG_FILES['admin']['logview.php'] = 1;
$_MG_FILES['admin']['maint.php'] = 1;
$_MG_FILES['admin']['massdelete.php'] = 1;
$_MG_FILES['admin']['member.php'] = 1;
$_MG_FILES['admin']['navigation.php'] = 1;
$_MG_FILES['admin']['purgealbums.php'] = 1;
$_MG_FILES['admin']['queue.php'] = 1;
$_MG_FILES['admin']['quota.php'] = 1;
$_MG_FILES['admin']['quotareport.php'] = 1;
$_MG_FILES['admin']['redX.png'] = 1;
$_MG_FILES['admin']['resetdefaults.php'] = 1;
$_MG_FILES['admin']['resetmembers.php'] = 1;
$_MG_FILES['admin']['rss.php'] = 1;
$_MG_FILES['admin']['rssrebuild.php'] = 1;
$_MG_FILES['admin']['sessions.php'] = 1;
$_MG_FILES['admin']['staticsortalbums.php'] = 1;
$_MG_FILES['admin']['staticsortmedia.php'] = 1;
$_MG_FILES['admin']['success.php'] = 1;
$_MG_FILES['admin']['usage_rpt.php'] = 1;
$_MG_FILES['admin']['xppubwiz.php'] = 1;

$_MG_FILES['importers']['4images'] = 1;
$_MG_FILES['importers']['Inmemoriam'] = 1;
$_MG_FILES['importers']['geekary'] = 1;
$_MG_FILES['importers']['gl_story'] = 1;
$_MG_FILES['importers']['Coppermine'] = 1;
$_MG_FILES['importers']['Gallery1'] = 1;
$_MG_FILES['importers']['gallery2'] = 1;

$_MG_FILES['4images']['index.php'] = 1;

$_MG_FILES['Inmemoriam']['index.php'] = 1;

$_MG_FILES['geekary']['index.php'] = 1;

$_MG_FILES['gl_story']['index.php'] = 1;

$_MG_FILES['Coppermine']['index.php'] = 1;

$_MG_FILES['Gallery1']['Gallery1DataParser.class'] = 1;
$_MG_FILES['Gallery1']['index.php'] = 1;

$_MG_FILES['gallery2']['index.php'] = 1;

$_MG_FILES['adocs']['README'] = 1;

$_MG_FILES['language']['chinese_simplified_utf-8.php'] = 1;
$_MG_FILES['language']['chinese_traditional_utf-8.php'] = 1;
$_MG_FILES['language']['english.php'] = 1;
$_MG_FILES['language']['english_utf-8.php'] = 1;
$_MG_FILES['language']['french_canada_utf-8.php'] = 1;
$_MG_FILES['language']['german.php'] = 1;
$_MG_FILES['language']['hebrew_utf-8.php'] = 1;
$_MG_FILES['language']['italian.php'] = 1;
$_MG_FILES['language']['japanese.php'] = 1;
$_MG_FILES['language']['japanese_utf-8.php'] = 1;
$_MG_FILES['language']['norwegian.php'] = 1;
$_MG_FILES['language']['polish.php'] = 1;
$_MG_FILES['language']['polish_utf-8.php'] = 1;
$_MG_FILES['language']['spanish_utf-8.php'] = 1;
$_MG_FILES['language']['turkish.php'] = 1;

function MG_maintScanDir ( $where, $dirOnly = 0 ) {
    global $_CONF, $_MG_CONF, $_MG_FILES;

    switch ( $where ) {
        case 'root' :
            $dir = $_CONF['path'] . '/plugins/mediagallery/';
            break;
        case 'public_html' :
            $dir = $_MG_CONF['path_html'];
            break;
        case 'frames' :
            $dir = $_MG_CONF['path_html'] . 'frames/';
            break;
        case 'legoyellowsmall' :
            $dir = $_MG_CONF['path_html'] . 'frames/legoyellowsmall/';
            break;
        case 'shadow_light' :
            $dir = $_MG_CONF['path_html'] . 'frames/shadow_light/';
            break;
        case 'dotapple' :
            $dir = $_MG_CONF['path_html'] . 'frames/dotapple/';
            break;
        case 'legoredsmall' :
            $dir = $_MG_CONF['path_html'] . 'frames/legoredsmall/';
            break;
        case 'polaroid' :
            $dir = $_MG_CONF['path_html'] . 'frames/polaroid/';
            break;
        case 'gold2' :
            $dir = $_MG_CONF['path_html'] . 'frames/gold2/';
            break;
        case 'shell' :
            $dir = $_MG_CONF['path_html'] . 'frames/shell/';
            break;
        case 'shadow_lighter' :
            $dir = $_MG_CONF['path_html'] . 'frames/shadow_lighter/';
            break;
        case 'slide' :
            $dir = $_MG_CONF['path_html'] . 'frames/slide/';
            break;
        case 'traintrack' :
            $dir = $_MG_CONF['path_html'] . 'frames/traintrack/';
            break;
        case 'bamboo' :
            $dir = $_MG_CONF['path_html'] . 'frames/bamboo/';
            break;
        case 'postage_due' :
            $dir = $_MG_CONF['path_html'] . 'frames/postage_due/';
            break;
        case 'book' :
            $dir = $_MG_CONF['path_html'] . 'frames/book/';
            break;
        case 'diapo' :
            $dir = $_MG_CONF['path_html'] . 'frames/diapo/';
            break;
        case 'polaroids' :
            $dir = $_MG_CONF['path_html'] . 'frames/polaroids/';
            break;
        case 'brand' :
            $dir = $_MG_CONF['path_html'] . 'frames/brand/';
            break;
        case 'flicking' :
            $dir = $_MG_CONF['path_html'] . 'frames/flicking/';
            break;
        case 'kodak' :
            $dir = $_MG_CONF['path_html'] . 'frames/kodak/';
            break;
        case 'gold' :
            $dir = $_MG_CONF['path_html'] . 'frames/gold/';
            break;
        case 'legoyellow' :
            $dir = $_MG_CONF['path_html'] . 'frames/legoyellow/';
            break;
        case 'scrapbookshadowsmall' :
            $dir = $_MG_CONF['path_html'] . 'frames/scrapbookshadowsmall/';
            break;
        case 'shadow' :
            $dir = $_MG_CONF['path_html'] . 'frames/shadow/';
            break;
        case 'legored' :
            $dir = $_MG_CONF['path_html'] . 'frames/legored/';
            break;
        case 'notebook' :
            $dir = $_MG_CONF['path_html'] . 'frames/notebook/';
            break;
        case 'maint' :
            $dir = $_MG_CONF['path_html'] . 'maint/';
            break;
        case 'jupload' :
            $dir = $_MG_CONF['path_html'] . 'jupload/';
            break;
        case 'rss' :
            $dir = $_MG_CONF['path_html'] . 'rss/';
            break;
        case 'classes' :
            $dir = $_MG_CONF['path_html'] . 'classes/';
            break;
        case 'classes_language' :
            $dir = $_MG_CONF['path_html'] . 'classes_language/';
            break;
        case 'players' :
            $dir = $_MG_CONF['path_html'] . 'players/';
            break;
        case 'watermarks' :
            $dir = $_MG_CONF['path_html'] . 'watermarks/';
            break;
        case 'getid3' :
            $dir = $_MG_CONF['path_html'] . 'getid3/';
            break;
        case 'getid3_code' :
            $dir = $_MG_CONF['path_html'] . 'getid3/getid3/';
            break;
        case 'images' :
            $dir = $_MG_CONF['path_html'] . 'images/';
            break;
        case 'js' :
            $dir = $_MG_CONF['path_html'] . 'js/';
            break;
        case 'mediaobjects' :
            $dir = $_MG_CONF['path_mediaobjects'];
            break;

        case 'orig_0' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/0/';
            break;
        case 'orig_1' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/1/';
            break;
        case 'orig_2' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/2/';
            break;
        case 'orig_3' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/3/';
            break;
        case 'orig_4' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/4/';
            break;
        case 'orig_5' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/5/';
            break;
        case 'orig_6' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/6/';
            break;
        case 'orig_7' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/7/';
            break;
        case 'orig_8' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/8/';
            break;
        case 'orig_9' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/9/';
            break;
        case 'orig_a' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/a/';
            break;
        case 'orig_b' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/b/';
            break;
        case 'orig_c' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/c/';
            break;
        case 'orig_d' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/d/';
            break;
        case 'orig_e' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/e/';
            break;
        case 'orig_f' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/f/';
            break;




        case 'covers' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'covers/';
            break;
        case 'orig' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'orig/';
            break;
        case 'disp' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/';
            break;

        case 'disp_0' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/0/';
            break;
        case 'disp_1' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/1/';
            break;
        case 'disp_2' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/2/';
            break;
        case 'disp_3' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/3/';
            break;
        case 'disp_4' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/4/';
            break;
        case 'disp_5' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/5/';
            break;
        case 'disp_6' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/6/';
            break;
        case 'disp_7' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/7/';
            break;
        case 'disp_8' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/8/';
            break;
        case 'disp_9' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/9/';
            break;
        case 'disp_a' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/a/';
            break;
        case 'disp_b' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/b/';
            break;
        case 'disp_c' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/c/';
            break;
        case 'disp_d' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/d/';
            break;
        case 'disp_e' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/e/';
            break;
        case 'disp_f' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'disp/f/';
            break;

        case 'tn' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/';
            break;

        case 'tn_0' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/0/';
            break;
        case 'tn_1' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/1/';
            break;
        case 'tn_2' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/2/';
            break;
        case 'tn_3' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/3/';
            break;
        case 'tn_4' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/4/';
            break;
        case 'tn_5' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/5/';
            break;
        case 'tn_6' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/6/';
            break;
        case 'tn_7' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/7/';
            break;
        case 'tn_8' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/8/';
            break;
        case 'tn_9' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/9/';
            break;
        case 'tn_a' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/a/';
            break;
        case 'tn_b' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/b/';
            break;
        case 'tn_c' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/c/';
            break;
        case 'tn_d' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/d/';
            break;
        case 'tn_e' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/e/';
            break;
        case 'tn_f' :
            $dir = $_MG_CONF['path_mediaobjects'] . 'tn/f/';
            break;

        case 'docs' :
            $dir = $_MG_CONF['path_html'] . 'docs/';
            break;
        case 'makers' :
            $dir = $_MG_CONF['path_html'] . 'makers/';
            break;
        case 'uploads' :
            $dir = $_CONF['path'] . 'plugins/mediagallery/uploads/';
            break;
        case 'sql' :
            $dir = $_CONF['path'] . 'plugins/mediagallery/sql/';
            break;
        case 'templates' :
            $dir = $_CONF['path'] . 'plugins/mediagallery/templates/';
            break;
        case 'skins' :
            $dir = $_CONF['path'] . 'plugins/mediagallery/templates/themes/';
            break;
        case 'clean' :
            $dir = $_CONF['path'] . 'plugins/mediagallery/templates/clean/';
            break;
        case 'podcast' :
            $dir = $_CONF['path'] . 'plugins/mediagallery/templates/podcast/';
            break;
        case 'simpleviewer' :
            $dir = $_CONF['path'] . 'plugins/mediagallery/templates/simpleviewer/';
            break;
        case 'lightbox' :
            $dir = $_CONF['path'] . 'plugins/mediagallery/templates/ligthbox/';
            break;
        case 'tmp' :
            $dir = $_CONF['path'] . 'plugins/mediagallery/tmp/';
            break;
        case 'admin' :
            $dir = $_MG_CONF['path_admin'];
            break;
        case 'importers' :
            $dir = $_MG_CONF['path_admin'] . 'importers/';
            break;
        case '4images' :
            $dir = $_MG_CONF['path_admin'] . 'importers/4images/';
            break;
        case 'Inmemoriam' :
            $dir = $_MG_CONF['path_admin'] . 'importers/Inmemoriam/';
            break;
        case 'geekary' :
            $dir = $_MG_CONF['path_admin'] . 'importers/geekary/';
            break;
        case 'gl_story' :
            $dir = $_MG_CONF['path_admin'] . 'importers/gl_story/';
            break;
        case 'Coppermine' :
            $dir = $_MG_CONF['path_admin'] . 'importers/Coppermine/';
            break;
        case 'Gallery1' :
            $dir = $_MG_CONF['path_admin'] . 'importers/Gallery1/';
            break;
        case 'gallery2' :
            $dir = $_MG_CONF['path_admin'] . 'importers/gallery2/';
            break;
        case 'adocs' :
            $dir = $_CONF['path'] . 'plugins/mediagallery/docs/';
            break;
        case 'language' :
            $dir = $_CONF['path'] . 'plugins/mediagallery/language/';
            break;
        default :
            return ("Unknown directory");
            exit;
    }

    $directory = $dir;

    if (!@is_dir($directory)) {
        return;
    }
    if (!$dh = @opendir($directory)) {
        return;
    }

    $rowcounter = 0;

    $retval = '';

    $T = new Template($_MG_CONF['template_path']);
    $T->set_file (array(
        'filelist'  =>  'cleanlist.thtml',
    ));
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);


   $T->set_block('filelist', 'fileRow','fRow');

    while ( ( $file = readdir($dh) ) != false ) {
        if ( $file == '..' || $file == '.' ) {
            continue;
        }
        $filename = $file;

        if (PHP_OS == "WINNT") {
            $filetmp = $directory . "\\" . $file;
        } else {
            $filetmp  = $directory . '/' . $file;
        }

        $filename = basename($file);

        $file_extension = strtolower(substr(strrchr($filename,"."),1));

        if ( $dirOnly == 1 ) {
            if ( !is_dir($filename)) {
                continue;
            }
        }

        if (!isset($_MG_FILES[$where][$filename])) {
            $T->set_var(array(
                'row_class'     =>      ($rowcounter % 2) ? '1' : '2',
                'filename'      =>      is_dir($filename) ? '<strong>' . $filename . '</strong>' : $filename,
                'dir'           =>      $where,
            ));
            $T->parse('fRow','fileRow',true);
            $rowcounter++;
        }
    }
    $T->parse('filelist','filelist');

    closedir($dh);
    $T->set_var(array(
        'directory' => $directory,
    ));
    $T->parse('output','filelist');
    $retval .= $T->finish($T->get_var('output'));

    if ( $rowcounter == 0 ) {
        return '';
    } else {
        return $retval;
    }
}

function MG_fileCheck() {
    global $_CONF, $_MG_CONF, $_MG_FILES, $LANG_MG00, $LANG_MG01;

    $retval = '';
    $filelist = '';

    $T = new Template($_MG_CONF['template_path']);
    $T->set_file (array(
        'admin'  =>  'filecheck.thtml',
    ));
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    $filelist .= MG_maintScanDir('root');
    $filelist .= MG_maintScanDir('public_html');
    $filelist .= MG_maintScanDir('frames');
    $filelist .= MG_maintScanDir('legoyellowsmall');
    $filelist .= MG_maintScanDir('shadow_light');
    $filelist .= MG_maintScanDir('dotapple');
    $filelist .= MG_maintScanDir('legoredsmall');
    $filelist .= MG_maintScanDir('polaroid');
    $filelist .= MG_maintScanDir('gold2');
    $filelist .= MG_maintScanDir('shell');
    $filelist .= MG_maintScanDir('shadow_lighter');
    $filelist .= MG_maintScanDir('slide');
    $filelist .= MG_maintScanDir('traintrack');
    $filelist .= MG_maintScanDir('bamboo');
    $filelist .= MG_maintScanDir('postage_due');
    $filelist .= MG_maintScanDir('book');
    $filelist .= MG_maintScanDir('diapo');
    $filelist .= MG_maintScanDir('polaroids');
    $filelist .= MG_maintScanDir('brand');
    $filelist .= MG_maintScanDir('flicking');
    $filelist .= MG_maintScanDir('kodak');
    $filelist .= MG_maintScanDir('gold');
    $filelist .= MG_maintScanDir('legoyellow');
    $filelist .= MG_maintScanDir('scrapbookshadowsmall');
    $filelist .= MG_maintScanDir('shadow');
    $filelist .= MG_maintScanDir('legored');
    $filelist .= MG_maintScanDir('notebook');
    $filelist .= MG_maintScanDir('maint');
    $filelist .= MG_maintScanDir('jupload');
    $filelist .= MG_maintScanDir('rss',1);
    $filelist .= MG_maintScanDir('classes');
    $filelist .= MG_maintScanDir('classes_language');
    $filelist .= MG_maintScanDir('players');
    $filelist .= MG_maintScanDir('watermarks',1);
    $filelist .= MG_maintScanDir('getid3');
    $filelist .= MG_maintScanDir('getid3_code');
    $filelist .= MG_maintScanDir('images');
    $filelist .= MG_maintScanDir('js');
    $filelist .= MG_maintScanDir('mediaobjects');
    $filelist .= MG_maintScanDir('covers',1);
    $filelist .= MG_maintScanDir('orig');

    $filelist .= MG_maintScanDir('orig_0',1);
    $filelist .= MG_maintScanDir('orig_1',1);
    $filelist .= MG_maintScanDir('orig_2',1);
    $filelist .= MG_maintScanDir('orig_3',1);
    $filelist .= MG_maintScanDir('orig_4',1);
    $filelist .= MG_maintScanDir('orig_5',1);
    $filelist .= MG_maintScanDir('orig_6',1);
    $filelist .= MG_maintScanDir('orig_7',1);
    $filelist .= MG_maintScanDir('orig_8',1);
    $filelist .= MG_maintScanDir('orig_9',1);
    $filelist .= MG_maintScanDir('orig_a',1);
    $filelist .= MG_maintScanDir('orig_b',1);
    $filelist .= MG_maintScanDir('orig_c',1);
    $filelist .= MG_maintScanDir('orig_d',1);
    $filelist .= MG_maintScanDir('orig_e',1);
    $filelist .= MG_maintScanDir('orig_f',1);

    $filelist .= MG_maintScanDir('disp');

    $filelist .= MG_maintScanDir('disp_0',1);
    $filelist .= MG_maintScanDir('disp_1',1);
    $filelist .= MG_maintScanDir('disp_2',1);
    $filelist .= MG_maintScanDir('disp_3',1);
    $filelist .= MG_maintScanDir('disp_4',1);
    $filelist .= MG_maintScanDir('disp_5',1);
    $filelist .= MG_maintScanDir('disp_6',1);
    $filelist .= MG_maintScanDir('disp_7',1);
    $filelist .= MG_maintScanDir('disp_8',1);
    $filelist .= MG_maintScanDir('disp_9',1);
    $filelist .= MG_maintScanDir('disp_a',1);
    $filelist .= MG_maintScanDir('disp_b',1);
    $filelist .= MG_maintScanDir('disp_c',1);
    $filelist .= MG_maintScanDir('disp_d',1);
    $filelist .= MG_maintScanDir('disp_e',1);
    $filelist .= MG_maintScanDir('disp_f',1);

    $filelist .= MG_maintScanDir('tn');

    $filelist .= MG_maintScanDir('tn_0',1);
    $filelist .= MG_maintScanDir('tn_1',1);
    $filelist .= MG_maintScanDir('tn_2',1);
    $filelist .= MG_maintScanDir('tn_3',1);
    $filelist .= MG_maintScanDir('tn_4',1);
    $filelist .= MG_maintScanDir('tn_5',1);
    $filelist .= MG_maintScanDir('tn_6',1);
    $filelist .= MG_maintScanDir('tn_7',1);
    $filelist .= MG_maintScanDir('tn_8',1);
    $filelist .= MG_maintScanDir('tn_9',1);
    $filelist .= MG_maintScanDir('tn_a',1);
    $filelist .= MG_maintScanDir('tn_b',1);
    $filelist .= MG_maintScanDir('tn_c',1);
    $filelist .= MG_maintScanDir('tn_d',1);
    $filelist .= MG_maintScanDir('tn_e',1);
    $filelist .= MG_maintScanDir('tn_f',1);

    $filelist .= MG_maintScanDir('docs');
    $filelist .= MG_maintScanDir('makers');
    $filelist .= MG_maintScanDir('uploads');
    $filelist .= MG_maintScanDir('sql');
    $filelist .= MG_maintScanDir('templates');
    $filelist .= MG_maintScanDir('skins');
    $filelist .= MG_maintScanDir('clean');
    $filelist .= MG_maintScanDir('podcast');
    $filelist .= MG_maintScanDir('simpleviewer');
    $filelist .= MG_maintScanDir('lightbox');
    $filelist .= MG_maintScanDir('tmp');
    $filelist .= MG_maintScanDir('admin');
    $filelist .= MG_maintScanDir('importers');
    $filelist .= MG_maintScanDir('4images');
    $filelist .= MG_maintScanDir('Inmemoriam');
    $filelist .= MG_maintScanDir('geekary');
    $filelist .= MG_maintScanDir('gl_story');
    $filelist .= MG_maintScanDir('Coppermine');
    $filelist .= MG_maintScanDir('Gallery1');
    $filelist .= MG_maintScanDir('gallery2');
    $filelist .= MG_maintScanDir('adocs');
    $filelist .= MG_maintScanDir('language');

    $T->set_var(array(
        's_form_action'         => $_MG_CONF['admin_url'] . '/filecheck.php?mode=clean',
        'filelist'              => $filelist,
        'lang_filecheck_disclaimer' => $LANG_MG00['filecheck_disclaimer'],
        'lang_cancel'           => $LANG_MG01['cancel'],
        'lang_delete'           => $LANG_MG01['delete'],
        'lang_checkall'         => $LANG_MG01['check_all'],
        'lang_uncheckall'       => $LANG_MG01['uncheck_all'],
        'lang_delete_confirm'   => $LANG_MG01['delete_item_confirm'],
    ));

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_fileCheckDelete( ) {
    global $_MG_CONF, $_CONF, $_POST;

    $numItems = count($_POST['sel']);
    for ($i=0; $i < $numItems; $i++) {
        $filename = COM_applyFilter($_POST['sel'][$i]);
        $where    = COM_applyFilter($_POST['dir'][$i]);

        switch ( $where ) {
            case 'root' :
                $dir = $_CONF['path'] . '/plugins/mediagallery/';
                break;
            case 'public_html' :
                $dir = $_MG_CONF['path_html'];
                break;
            case 'frames' :
                $dir = $_MG_CONF['path_html'] . 'frames/';
                break;
            case 'legoyellowsmall' :
                $dir = $_MG_CONF['path_html'] . 'frames/legoyellowsmall/';
                break;
            case 'shadow_light' :
                $dir = $_MG_CONF['path_html'] . 'frames/shadow_light/';
                break;
            case 'dotapple' :
                $dir = $_MG_CONF['path_html'] . 'frames/dotapple/';
                break;
            case 'legoredsmall' :
                $dir = $_MG_CONF['path_html'] . 'frames/legoredsmall/';
                break;
            case 'polaroid' :
                $dir = $_MG_CONF['path_html'] . 'frames/polaroid/';
                break;
            case 'gold2' :
                $dir = $_MG_CONF['path_html'] . 'frames/gold2/';
                break;
            case 'shell' :
                $dir = $_MG_CONF['path_html'] . 'frames/shell/';
                break;
            case 'shadow_lighter' :
                $dir = $_MG_CONF['path_html'] . 'frames/shadow_lighter/';
                break;
            case 'slide' :
                $dir = $_MG_CONF['path_html'] . 'frames/slide/';
                break;
            case 'traintrack' :
                $dir = $_MG_CONF['path_html'] . 'frames/traintrack/';
                break;
            case 'bamboo' :
                $dir = $_MG_CONF['path_html'] . 'frames/bamboo/';
                break;
            case 'postage_due' :
                $dir = $_MG_CONF['path_html'] . 'frames/postage_due/';
                break;
            case 'book' :
                $dir = $_MG_CONF['path_html'] . 'frames/book/';
                break;
            case 'diapo' :
                $dir = $_MG_CONF['path_html'] . 'frames/diapo/';
                break;
            case 'polaroids' :
                $dir = $_MG_CONF['path_html'] . 'frames/polaroids/';
                break;
            case 'brand' :
                $dir = $_MG_CONF['path_html'] . 'frames/brand/';
                break;
            case 'flicking' :
                $dir = $_MG_CONF['path_html'] . 'frames/flicking/';
                break;
            case 'kodak' :
                $dir = $_MG_CONF['path_html'] . 'frames/kodak/';
                break;
            case 'gold' :
                $dir = $_MG_CONF['path_html'] . 'frames/gold/';
                break;
            case 'legoyellow' :
                $dir = $_MG_CONF['path_html'] . 'frames/legoyellow/';
                break;
            case 'scrapbookshadowsmall' :
                $dir = $_MG_CONF['path_html'] . 'frames/scrapbookshadowsmall/';
                break;
            case 'shadow' :
                $dir = $_MG_CONF['path_html'] . 'frames/shadow/';
                break;
            case 'legored' :
                $dir = $_MG_CONF['path_html'] . 'frames/legored/';
                break;
            case 'notebook' :
                $dir = $_MG_CONF['path_html'] . 'frames/notebook/';
                break;
            case 'maint' :
                $dir = $_MG_CONF['path_html'] . 'maint/';
                break;
            case 'jupload' :
                $dir = $_MG_CONF['path_html'] . 'jupload/';
                break;
            case 'rss' :
                $dir = $_MG_CONF['path_html'] . 'rss/';
                break;
            case 'classes' :
                $dir = $_MG_CONF['path_html'] . 'classes/';
                break;
            case 'classes_language' :
                $dir = $_MG_CONF['path_html'] . 'classes_language/';
                break;
            case 'players' :
                $dir = $_MG_CONF['path_html'] . 'players/';
                break;
            case 'watermarks' :
                $dir = $_MG_CONF['path_html'] . 'watermarks/';
                break;
            case 'getid3' :
                $dir = $_MG_CONF['path_html'] . 'getid3/';
                break;
            case 'getid3_code' :
                $dir = $_MG_CONF['path_html'] . 'getid3/getid3/';
                break;
            case 'images' :
                $dir = $_MG_CONF['path_html'] . 'images/';
                break;
            case 'js' :
                $dir = $_MG_CONF['path_html'] . 'js/';
                break;
            case 'mediaobjects' :
                $dir = $_MG_CONF['path_mediaobjects'] . '';
                break;
            case 'orig_0' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/0/';
                break;
            case 'orig_1' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/1/';
                break;
            case 'orig_2' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/2/';
                break;
            case 'orig_3' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/3/';
                break;
            case 'orig_4' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/4/';
                break;
            case 'orig_5' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/5/';
                break;
            case 'orig_6' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/6/';
                break;
            case 'orig_7' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/7/';
                break;
            case 'orig_8' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/8/';
                break;
            case 'orig_9' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/9/';
                break;
            case 'orig_a' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/a/';
                break;
            case 'orig_b' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/b/';
                break;
            case 'orig_c' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/c/';
                break;
            case 'orig_d' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/d/';
                break;
            case 'orig_e' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/e/';
                break;
            case 'orig_f' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/f/';
                break;
            case 'covers' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'covers/';
                break;
            case 'orig' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'orig/';
                break;
            case 'disp' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/';
                break;
            case 'disp_0' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/0/';
                break;
            case 'disp_1' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/1/';
                break;
            case 'disp_2' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/2/';
                break;
            case 'disp_3' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/3/';
                break;
            case 'disp_4' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/4/';
                break;
            case 'disp_5' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/5/';
                break;
            case 'disp_6' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/6/';
                break;
            case 'disp_7' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/7/';
                break;
            case 'disp_8' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/8/';
                break;
            case 'disp_9' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/9/';
                break;
            case 'disp_a' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/a/';
                break;
            case 'disp_b' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/b/';
                break;
            case 'disp_c' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/c/';
                break;
            case 'disp_d' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/d/';
                break;
            case 'disp_e' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/e/';
                break;
            case 'disp_f' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'disp/f/';
                break;
            case 'tn' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/';
                break;
            case 'tn_0' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/0/';
                break;
            case 'tn_1' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/1/';
                break;
            case 'tn_2' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/2/';
                break;
            case 'tn_3' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/3/';
                break;
            case 'tn_4' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/4/';
                break;
            case 'tn_5' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/5/';
                break;
            case 'tn_6' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/6/';
                break;
            case 'tn_7' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/7/';
                break;
            case 'tn_8' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/8/';
                break;
            case 'tn_9' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/9/';
                break;
            case 'tn_a' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/a/';
                break;
            case 'tn_b' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/b/';
                break;
            case 'tn_c' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/c/';
                break;
            case 'tn_d' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/d/';
                break;
            case 'tn_e' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/e/';
                break;
            case 'tn_f' :
                $dir = $_MG_CONF['path_mediaobjects'] . 'tn/f/';
                break;
            case 'docs' :
                $dir = $_MG_CONF['path_html'] . 'docs/';
                break;
            case 'makers' :
                $dir = $_MG_CONF['path_html'] . 'makers/';
                break;
            case 'uploads' :
                $dir = $_CONF['path'] . 'plugins/mediagallery/uploads/';
                break;
            case 'sql' :
                $dir = $_CONF['path'] . 'plugins/mediagallery/sql/';
                break;
            case 'templates' :
                $dir = $_CONF['path'] . 'plugins/mediagallery/templates/';
                break;
            case 'skins' :
                $dir = $_CONF['path'] . 'plugins/mediagallery/templates/themes/';
                break;
            case 'clean' :
                $dir = $_CONF['path'] . 'plugins/mediagallery/templates/clean/';
                break;
            case 'podcast' :
                $dir = $_CONF['path'] . 'plugins/mediagallery/templates/podcast/';
                break;
            case 'simpleviewer' :
                $dir = $_CONF['path'] . 'plugins/mediagallery/templates/simpleviewer/';
                break;
            case 'lightbox' :
                $dir = $_CONF['path'] . 'plugins/mediagallery/templates/ligthbox/';
                break;
            case 'tmp' :
                $dir = $_CONF['path'] . 'plugins/mediagallery/tmp/';
                break;
            case 'admin' :
                $dir = $_MG_CONF['path_admin'];
                break;
            case 'importers' :
                $dir = $_MG_CONF['path_admin'] . 'importers/';
                break;
            case '4images' :
                $dir = $_MG_CONF['path_admin'] . 'importers/4images/';
                break;
            case 'Inmemoriam' :
                $dir = $_MG_CONF['path_admin'] . 'importers/Inmemoriam/';
                break;
            case 'geekary' :
                $dir = $_MG_CONF['path_admin'] . 'importers/geekary/';
                break;
            case 'gl_story' :
                $dir = $_MG_CONF['path_admin'] . 'importers/gl_story/';
                break;
            case 'Coppermine' :
                $dir = $_MG_CONF['path_admin'] . 'importers/Coppermine/';
                break;
            case 'Gallery1' :
                $dir = $_MG_CONF['path_admin'] . 'importers/Gallery1/';
                break;
            case 'gallery2' :
                $dir = $_MG_CONF['path_admin'] . 'importers/gallery2/';
                break;
            case 'adocs' :
                $dir = $_CONF['path'] . 'plugins/mediagallery/docs/';
                break;
            case 'language' :
                $dir = $_CONF['path'] . 'plugins/mediagallery/language/';
                break;
            default :
                continue;
        }
        $fullName = $dir . $filename;
        COM_errorLog("Media Gallery: FileCheck removing " . $fullName);

        if ( is_dir($fullName) ) {
            MG_deleteDir($fullName);
        } else {
            @unlink($fullName);
        }
    }
    echo COM_refresh($_MG_CONF['admin_url'] . 'index.php?msg=17');
    exit;
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

$display = COM_siteHeader();
$T = new Template($_MG_CONF['template_path']);
$T->set_file (array ('admin' => 'administration.thtml'));
$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['version'],
));

if ($mode == $LANG_MG01['cancel']) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} elseif ($mode == $LANG_MG01['delete'] && !empty ($LANG_MG01['delete'])) {
    MG_fileCheckDelete( )    ;
} else {
    $T->set_var(array(
        'admin_body'    => MG_fileCheck(),
        'title'         => $LANG_MG00['filecheck'],
    ));
}

$T->parse('output', 'admin');
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>