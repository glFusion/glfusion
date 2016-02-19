<?php
// +--------------------------------------------------------------------------+
// | CAPTCHA Plugin - glFusion CMS                                            |
// +--------------------------------------------------------------------------+
// | english_utf-8.php                                                        |
// |                                                                          |
// | English language file                                                    |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2016 by the following authors:                        |
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

$LANG_CP00 = array (
    'menulabel'         => 'CAPTCHA',
    'plugin'            => 'CAPTCHA',
    'access_denied'     => 'Access Denied',
    'access_denied_msg' => 'You do not have the proper security privilege to access to this page.  Your user name and IP have been recorded.',
    'admin'             => 'CAPTCHA Administration',
    'install_header'    => 'CAPTCHA Plugin Install/Uninstall',
    'installed'         => 'CAPTCHA is Installed',
    'uninstalled'       => 'CAPTCHA is Not Installed',
    'install_success'   => 'CAPTCHA Installation Successful.  <br /><br />Please review the system documentation and also visit the  <a href="%s">administration section</a> to insure your settings correctly match the hosting environment.',
    'install_failed'    => 'Installation Failed -- See your error log to find out why.',
    'uninstall_msg'     => 'Plugin Successfully Uninstalled',
    'install'           => 'Install',
    'uninstall'         => 'UnInstall',
    'warning'           => 'Warning! Plugin is still Enabled',
    'enabled'           => 'Disable plugin before uninstalling.',
    'readme'            => 'CAPTCHA Plugin Installation',
    'installdoc'        => "<a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Install Document</a>",
    'overview'          => 'CAPTCHA is a native glFusion plugin that provides an additional layer of security for spambots. <br /><br />A CAPTCHA (an acronym for "Completely Automated Public Turing test to tell Computers and Humans Apart", trademarked by Carnegie Mellon University) is a type of challenge-response test used in computing to determine whether or not the user is human.  By presenting a difficult to read graphic of letters and numbers, it is assumed that only a human could read and enter the characters properly.  By implementing the CAPTCHA test, it should help reduce the number of Spambot entries on your site.',
    'details'           => 'The CAPTCHA plugin will use static (already generated) CAPTCHA images unless you configure CAPTCHA to build dynamic images using either the GD Graphic Library or ImageMagick.  In order to use either GD libraries or ImageMagick, they must support True Type fonts.  Check with your hosting provider to determine if they support TTF.',
    'preinstall_check'  => 'CAPTCHA has the following requirements:',
    'glfusion_check'    => 'glFusion v1.4.3 or greater, version reported is <b>%s</b>.',
    'php_check'         => 'PHP v5.2.0 or greater, version reported is <b>%s</b>.',
    'preinstall_confirm' => "For full details on installing CAPTCHA, please refer to the <a href=\"{$_CONF['site_admin_url']}/plugins/captcha/install_doc.html\">Installation Manual</a>.",
    'captcha_help'      => 'Enter the characters',
    'bypass_error'      => "You have attempted to bypass the CAPTCHA processing at this site, please use the New User link to register.",
    'bypass_error_blank' => "You have attempted to bypass the CAPTCHA processing at this site, please enter a valid CAPTCHA phrase.",
    'entry_error'       => 'The entered CAPTCHA string did not match the characters on the graphic, please try again. <b>This is case sensitive.</b>',
    'entry_error_pic'   => 'The selected CAPTCHA images did not match the request on the graphic, please try again.',
    'captcha_info'      => 'The CAPTCHA Plugin provides another layer of protection against SpamBots for your glFusion site.  See the <a href="%s">Online Documentation Wiki</a> for more info.',
    'enabled_header'    => 'Current CAPTCHA Settings',
    'on'                => 'On',
    'off'               => 'Off',
    'captcha_alt'       => 'You must enter the graphic text - contact Site Admin if you are unable to read the graphic',
    'save'              => 'Save',
    'cancel'            => 'Cancel',
    'success'           => 'Configuration Options successfully saved.',
    'reload'            => 'Reload',
    'reload_failed'     => 'Sorry, cannot autoreload CAPTCHA image\nSubmit the form and a new CAPTCHA will be loaded',
    'reload_too_many'   => 'You may only request up to 5 image refreshes',
    'session_expired'   => 'Your CAPTCHA Session has expired, please try again',
    'picture'           => 'Picture',
    'characters'        => 'Characters',
    'ayah_error'        => 'Sorry, but we were not able to verify you as human. Please try again.',
    'captcha_math'      => 'Enter the answer',
    'captcha_prompt'    => 'Are You Human?',
    'captcha_help'      => 'Solve the Problem',
);

// Localization of the Admin Configuration UI
$LANG_configsections['captcha'] = array(
    'label'                 => 'CAPTCHA',
    'title'                 => 'CAPTCHA Configuration'
);
$LANG_confignames['captcha'] = array(
    'gfxDriver'             => 'Graphics Driver',
    'gfxFormat'             => 'Graphics Format',
    'imageset'			    => 'Static Image Set',
    'debug'                 => 'Debug',
    'gfxPath'               => 'Full Path to ImageMagick\'s Convert Utility',
    'remoteusers'           => 'Force CAPTCHA for All Remote Users',
    'logging'               => 'Log Invalid CAPTCHA Attempts',
    'anonymous_only'        => 'Anonymous Only',
    'enable_comment'        => 'Enable Comment',
    'enable_story'          => 'Enable Story',
    'enable_registration'   => 'Enable Registration',
    'enable_contact'        => 'Enable Contact',
    'enable_emailstory'     => 'Enable Email Story',
    'enable_forum'          => 'Enable Forum',
    'enable_mediagallery'   => 'Enable Media Gallery (Postcards)',
    'enable_rating'         => 'Enable Rating Plugin Support',
    'enable_links'          => 'Enable Links Plugin Support',
    'enable_calendar'       => 'Enable Calendar Plugin Support',
    'expire'                => 'How Many Seconds a CAPTCHA Session is Valid',
    'publickey'             => 'reCAPTCHA Public Key - <a href="https://www.google.com/recaptcha/admin/create" target=_blank>reCAPTCHA Signup</a>',
    'privatekey'            => 'reCAPTCHA Private Key',
    'recaptcha_theme'       => 'reCAPTCHA Theme',

);
$LANG_configsubgroups['captcha'] = array(
    'sg_main'               => 'Configuration Settings'
);
$LANG_fs['captcha'] = array(
    'cp_public'                 => 'General Settings',
    'cp_integration'            => 'CAPTCHA Integration',
);
// Note: entries 0, 1, and 12 are the same as in $LANG_configselects['Core']
$LANG_configselects['captcha'] = array(
    0 => array('True' => 1, 'False' => 0),
    1 => array('True' => TRUE, 'False' => FALSE),
    2 => array('GD Libs' => 0, 'ImageMagick' => 1, 'Static Images' => 2, 'reCAPTCHA' => 3, 'Math Equation' => 6),
    4 => array('Default' => 'default','Simple' => 'simple'),
    5 => array('JPG' => 'jpg','PNG' => 'png'),
    6 => array('clean' => 'clean','red' => 'red','white' => 'white','blackglass' => 'blackglass'),
);

$PLG_captcha_MESSAGE1 = 'CAPTCHA plugin upgrade: Update completed successfully.';
$PLG_captcha_MESSAGE2 = 'CAPTCHA plugin upgrade failed - check error.log';
$PLG_captcha_MESSAGE3 = 'CAPTCHA Plugin Successfully Installed';
?>