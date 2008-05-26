<?php
// +---------------------------------------------------------------------------+
// | Theme Functions                                               |
// +---------------------------------------------------------------------------+
// | $Id::                                                                    $|
// +---------------------------------------------------------------------------+
// | Copyright (C) 2007 by the following authors:                              |
// |                                                                           |
// | Author:                                                                   |
// | Mark R. Evans              - mevans@ecsnet.com                            |
// +---------------------------------------------------------------------------+
// | LICENSE                                                                   |
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
// | DOCUMENTATION                                                             |
// |                                                                           |
// | This custom functions.php will allow you to build Geeklog content in any  |
// | order.  By default, the functions.php is designed to build centercontent  |
// | first, then left, then right.                                             |
// |                                                                           |
// | USAGE                                                                     |
// |                                                                           |
// | You MUST rename the nouveau_siteHeader() and nouveau_siteFooter()     |
// | functions for your theme, by using your themename_siteHeader()/_siteFooter|
// |                                                                           |
// | This file also requires a new template file, htmlheader.thtml which is    |
// | included in the sample theme.  This new template sends just the HTML      |
// | header to the browser, it does not send the site content.                 |
// |                                                                           |
// | This functions sets a new template variable for the header.thtml called   |
// | {centercolumn}, this holds the proper class name depending on what content|
// | is going to be displayed.  For example, with left, center, right content  |
// | {centercolumn} is set to content.                                         |
// |                                                                           |
// | What's Displayed               CSS Class                                  |
// | -------------------            ---------------------                      |
// | left, center, right            content                                    |
// | left, center                   content-wide-left                          |
// | center, right                  content-wide-right                         |
// | center                         content-full                               |
// |                                                                           |
// +---------------------------------------------------------------------------+

// this file can't be used on its own
if (strpos ($_SERVER['PHP_SELF'], 'functions.php') !== false) {
    die ('This file can not be used on its own!');
}

$_IMAGE_TYPE = 'png';

if (!defined ('XHTML')) {
    define('XHTML',' /'); // change this to ' /' for XHTML, and '' for HTML. Don't forget to update your doctype in htmlheader.thtml.
}
$result = DB_query ("SELECT onleft,name FROM {$_TABLES['blocks']} WHERE is_enabled = 1");
$nrows = DB_numRows ($result);
for ($i = 0; $i < $nrows; $i++) {
    $A = DB_fetchArray ($result);
        if ($A['onleft'] == 1) {
            $_BLOCK_TEMPLATE[$A['name']] = 'blockheader-left.thtml,blockfooter-left.thtml';
        } else {
            $_BLOCK_TEMPLATE[$A['name']] = 'blockheader-right.thtml,blockfooter-right.thtml';
    }
}

$_BLOCK_TEMPLATE['_msg_block'] = 'blockheader-message.thtml,blockfooter-message.thtml';

$_BLOCK_TEMPLATE['whats_related_block'] = 'blockheader-related.thtml,blockfooter-related.thtml';
$_BLOCK_TEMPLATE['story_options_block'] = 'blockheader-related.thtml,blockfooter-related.thtml';

//Cacthing Template Library code that allows a theme to do away with redundant .thtml files.
//If the CTL doesn't find the needed .thtml file in the current theme directory, it will look
//for it in the default layout/professional/directory. This way, layouts can be more
//streamlined and not have to include a lot of redundant files.
//
//For more information, see the latest documentation avavilable from gllabs.org:
//http://www.gllabs.org/wiki/doku.php?id=geeklog:templatecache
//                                                                        |
//Authors: Joe Mucchiello     - joe AT throwingdice DOT com
/*
$TEMPLATE_OPTIONS['hook']['set_root'] = 'Template_set_root_missingfiles';

function Template_set_root_missingfiles($root)
{
    global $_CONF;
    $ret = Array();
    foreach ($root as $r) {
         $ret[] = $r;
         // if the file is supposed to be in path_layout
         // also look in professional directory
         if (strpos($r, $_CONF['path_layout']) === 0) {
            $ret[] = $_CONF['path_themes'] . 'professional/' .
            substr($r, strlen($_CONF['path_layout']));
         }
    }
    return $ret;
}
*/
/**
* Returns the site header
*
* This loads the proper templates, does variable substitution and returns the
* HTML for the site header with or without blocks depending on the value of $what
*
* Note that the default for the header is to display the left blocks and the
* default of the footer is to not display the right blocks.
*
* @param    string  $what       If 'none' then no left blocks are returned, if 'menu' (default) then right blocks are returned
* @param    string  $pagetitle  optional content for the page's <title>
* @param    string  $headercode optional code to go into the page's <head>
* @return   string              Formatted HTML containing the site header
* @see function COM_siteFooter
*
*/

function nouveau_siteHeader($what = 'menu', $pagetitle = '', $headercode = '' )
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG_BUTTONS, $LANG_DIRECTION,
           $_IMAGE_TYPE, $topic, $_COM_VERBOSE, $theme_what, $theme_pagetitle,
           $theme_headercode, $theme_layout, $gllabsMooToolsLoaded,
           $mbMenuConfig;

    $theme_what         = $what;
    $theme_pagetitle    = $pagetitle;
    $theme_headercode   = $headercode;

    $header = new Template( $_CONF['path_layout'] );
    $header->set_file( array(
        'header'        => 'htmlheader.thtml',
    ));
    $header->set_var('xhtml',XHTML);

    // determine if MooTools is already loaded..
    if ( $gllabsMooToolsLoaded != 1 ) {
        $header->set_var('mootools',
            '<script type="text/javascript" src="' . $_CONF['layout_url'] . '/js/mootools-release-1.11.packed.js"></script>');
        $gllabsMooToolsLoaded = 1;
    }
	//Fade in animation for the gl_moomenu
	$header->set_var('gl_animatedmoomenu',
'<script type="text/javascript" src="' . $_CONF['layout_url'] . '/js/gl_moomenu.js"></script>
<script type="text/javascript">
	window.addEvent(\'load\', function() {
		new moomenu($E(\'ul.gl_moomenu\'), {
			bgiframe: false,
			delay: 50,
			animate: {
				props: [\'opacity\', \'width\', \'height\'],
				opts: {
					duration:300,
					fps: 100,
					transition: Fx.Transitions.Quad.easeOut
				}
			}
		});
	});
</script>');
	//Enables use of mootips
	$header->set_var('gl_mootips',
'<script type="text/javascript">
	window.addEvent(\'load\', function() {
		var Tips1 = new Tips($$(\'.gl_mootip\')); //enables use of tooltips
		var Tips2 = new Tips($$(\'.gl_mootipfade\'), { //enables use of fade in/out tooltips
			initialize:function(){
				this.fx = new Fx.Style(this.toolTip, \'opacity\', {duration: 500, wait: false}).set(0);
			},
			onShow: function(toolTip) {
				this.fx.start(1);
			},
			onHide: function(toolTip) {
				this.fx.start(0);
			}
		});
		var Tips3 = new Tips($$(\'.gl_mootipfixed\'), { //enables use of fixed position tooltips (good for hover help text)
			showDelay: 150,
			hideDelay: 400,
			fixed: true
		});
	});
</script>');
	//Enables use of gl_chronometer - a client-side header banner rotator
	$header->set_var('gl_moochronometer',
'<script type="text/javascript" src="' . $_CONF['layout_url'] . '/js/gl_moochronometer.js"></script>');

    $header->set_var(array(
        'mbgcolor'  => '#151515',
        'mtext'     => '#CCCCCC',
        'mhtext'    => '#FFFFFF',
        'shtext'    => '#679ef1',
        'sblcolor'  => '#333333',
        'sbrcolor'  => '#000000',
        'sbgcolor'  => '#151515',
        'sbtcolor'  => '#333333',
        'sbbcolor'  => '#000000',
        'spimage'   => 'url(images/gl_moomenu1-parent.png) 95% 50% norepeat',
    ));
/*-----------------------
    // set menu colors
    if ( function_exists('mb_getMenu') && $mbMenuConfig[0]['enabled'] == 1) {
        $result = DB_query("SELECT * FROM {$_TABLES['mb_config']} WHERE menu_id=0");
        list($id,$menu_id,$hcolor,$hhcolor,$htext,$hhtext,$enabled) = DB_fetchArray($result);
    }
    if ( $hcolor == '' || $hhcolor == '' || $htext == '' || $hhtext == '' ) {
        $hcolor = '#AAAAAA';
        $hhcolor = '#111111';
        $htext   = '#111111';
        $hhtext  = '#AAAAAA';
    }
    $header->set_var(array(
        'hcolor'    => $hcolor,
        'hhcolor'   => $hhcolor,
        'htext'     => $htext,
        'hhtext'    => $hhtext,
    ));
-------------------------- */

    // get topic if not on home page
    if( !isset( $_GET['topic'] )) {
        if( isset( $_GET['story'] )) {
            $sid = COM_applyFilter( $_GET['story'] );
        } elseif( isset( $_GET['sid'] )) {
            $sid = COM_applyFilter( $_GET['sid'] );
        } elseif( isset( $_POST['story'] )) {
            $sid = COM_applyFilter( $_POST['story'] );
        }
        if( empty( $sid ) && $_CONF['url_rewrite'] &&
                ( strpos( $_SERVER['PHP_SELF'], 'article.php' ) !== false )) {
            COM_setArgNames( array( 'story', 'mode' ));
            $sid = COM_applyFilter( COM_getArgument( 'story' ));
        }
        if( !empty( $sid )) {
            $topic = DB_getItem( $_TABLES['stories'], 'tid', "sid='$sid'" );
        }
    } else {
        $topic = COM_applyFilter( $_GET['topic'] );
    }

    $feed_url = array();
    if( $_CONF['backend'] == 1 ) { // add feed-link to header if applicable
        $baseurl = SYND_getFeedUrl();

        $sql = 'SELECT format, filename, title, language FROM '
             . $_TABLES['syndication'] . " WHERE (header_tid = 'all')";
        if( !empty( $topic )) {
            $sql .= " OR (header_tid = '" . addslashes( $topic ) . "')";
        }
        $result = DB_query( $sql );
        $numRows = DB_numRows( $result );
        for( $i = 0; $i < $numRows; $i++ ) {
            $A = DB_fetchArray( $result );
            if ( !empty( $A['filename'] )) {
                $format = explode( '-', $A['format'] );
                $format_type = strtolower( $format[0] );
                $format_name = ucwords( $format[0] );

                $feed_url[] = '<link rel="alternate" type="application/'
                          . $format_type . '+xml" hreflang="' . $A['language']
                          . '" href="' . $baseurl . $A['filename'] . '" title="'
                          . $format_name . ' Feed: ' . $A['title'] . '"' . XHTML . '>';
            }
        }
    }
    $header->set_var( 'feed_url', implode( LB, $feed_url ));

    $relLinks = array();
    if( !COM_onFrontpage() ) {
        $relLinks['home'] = '<link rel="home" href="' . $_CONF['site_url']
                          . '/" title="' . $LANG01[90] . '"' . XHTML . '>';
    }
    $loggedInUser = !COM_isAnonUser();
    if( $loggedInUser || (( $_CONF['loginrequired'] == 0 ) &&
                ( $_CONF['searchloginrequired'] == 0 ))) {
        if(( substr( $_SERVER['PHP_SELF'], -strlen( '/search.php' ))
                != '/search.php' ) || isset( $_GET['mode'] )) {
            $relLinks['search'] = '<link rel="search" href="'
                                . $_CONF['site_url'] . '/search.php" title="'
                                . $LANG01[75] . '"' . XHTML . '>';
        }
    }
    if( $loggedInUser || (( $_CONF['loginrequired'] == 0 ) &&
                ( $_CONF['directoryloginrequired'] == 0 ))) {
        if( strpos( $_SERVER['PHP_SELF'], '/article.php' ) !== false ) {
            $relLinks['contents'] = '<link rel="contents" href="'
                        . $_CONF['site_url'] . '/directory.php" title="'
                        . $LANG01[117] . '"' . XHTML . '>';
        }
    }

    $header->set_var( 'rel_links', implode( LB, $relLinks ));

    if( empty( $pagetitle ) && isset( $_CONF['pagetitle'] )) {
        $pagetitle = $_CONF['pagetitle'];
    }
    if( empty( $pagetitle )) {
        if( empty( $topic )) {
            $pagetitle = $_CONF['site_slogan'];
        } else {
            $pagetitle = stripslashes(DB_getItem( $_TABLES['topics'], 'topic',
                                                   "tid = '$topic'" ));
        }
    }
    if( !empty( $pagetitle )) {
        $header->set_var( 'page_site_splitter', ' - ');
    }
    $header->set_var( 'page_title', $pagetitle );
    $header->set_var( 'site_name', $_CONF['site_name']);

    if( isset( $_CONF['advanced_editor'] ) && ( $_CONF['advanced_editor'] == 1 )
            && file_exists( $_CONF['path_html']
                            . 'layout/professional/advanced_editor_header.thtml' )) {
        $header->set_file( 'editor'  , 'advanced_editor_header.thtml');
        $header->set_var('xthml',XHTML);
        $header->parse( 'advanced_editor', 'editor' );

    } else {
        $header->set_var( 'xhtml', XHTML );
        $header->set_var( 'advanced_editor', '' );
    }

    $langAttr = '';
    if( !empty( $_CONF['languages'] ) && !empty( $_CONF['language_files'] )) {
        $langId = COM_getLanguageId();
    } else {
        // try to derive the language id from the locale
        $l = explode( '.', $_CONF['locale'] );
        $langId = $l[0];
    }
    if( !empty( $langId )) {
        $l = explode( '-', str_replace( '_', '-', $langId ));
        if(( count( $l ) == 1 ) && ( strlen( $langId ) == 2 )) {
            $langAttr = 'lang="' . $langId . '"';
        } else if( count( $l ) == 2 ) {
            if(( $l[0] == 'i' ) || ( $l[0] == 'x' )) {
                $langId = implode( '-', $l );
                $langAttr = 'lang="' . $langId . '"';
            } else if( strlen( $l[0] ) == 2 ) {
                $langId = implode( '-', $l );
                $langAttr = 'lang="' . $langId . '"';
            } else {
                $langId = $l[0];
            }
        }
    }
    $header->set_var( 'lang_id', $langId );
    $header->set_var( 'lang_attribute', $langAttr );

    $header->set_var( 'background_image', $_CONF['layout_url']
                                          . '/images/bg.' . $_IMAGE_TYPE );
    $header->set_var( 'site_url', $_CONF['site_url'] );
    $header->set_var( 'layout_url', $_CONF['layout_url'] );
    $header->set_var( 'site_mail', "mailto:{$_CONF['site_mail']}" );
    $header->set_var( 'site_name', $_CONF['site_name'] );
    $header->set_var( 'site_slogan', $_CONF['site_slogan'] );
    $rdf = substr_replace( $_CONF['rdf_file'], $_CONF['site_url'], 0,
                           strlen( $_CONF['path_html'] ) - 1 );
    $header->set_var( 'rdf_file', $rdf );
    $header->set_var( 'rss_url', $rdf );
    $header->set_var( 'css_url', $_CONF['layout_url'] . '/style.css' );
    $header->set_var( 'theme', $_CONF['theme'] );

    $header->set_var( 'charset', COM_getCharset());
    if( empty( $LANG_DIRECTION )) {
        // default to left-to-right
        $header->set_var( 'direction', 'ltr' );
    } else {
        $header->set_var( 'direction', $LANG_DIRECTION );
    }

    // Call any plugin that may want to include extra Meta tags
    // or Javascript functions
    $header->set_var( 'plg_headercode', $headercode . PLG_getHeaderCode() );

    // Call to plugins to set template variables in the header
    PLG_templateSetVars( 'header', $header );

    $header->parse( 'index_header', 'header' );
    $retval = $header->finish( $header->get_var( 'index_header' ));

    // send out the charset header
    header( 'Content-Type: text/html; charset=' . COM_getCharset());
    echo $retval;

    // Start caching / capturing output from Geeklog / plugins
    ob_start();
    return '';
}


/**
* Returns the site footer
*
* This loads the proper templates, does variable substitution and returns the
* HTML for the site footer.
*
* @param   boolean     $rightblock     Whether or not to show blocks on right hand side default is no
* @param   array       $custom         An array defining custom function to be used to format Rightblocks
* @see function themename_siteHeader
* @return   string  Formated HTML containing site footer and optionally right blocks
*
*/

function nouveau_siteFooter( $rightblock = -1, $custom = '' )
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG12, $LANG_BUTTONS, $LANG_DIRECTION,
           $_IMAGE_TYPE, $topic, $_COM_VERBOSE, $_PAGE_TIMER, $theme_what,
           $theme_pagetitle, $theme_headercode, $theme_layout,$mbMenuConfig;

    $what       = $theme_what;
    $pagetitle  = $theme_pagetitle;
    $themecode  = $theme_headercode;

    // Grab any content that was cached by the system

    $content = ob_get_contents();
    ob_end_clean();

    $theme = new Template( $_CONF['path_layout'] );
    $theme->set_file( array(
        'header'        => 'header.thtml',
        'footer'        => 'footer.thtml',
        'menuitem'      => 'menuitem.thtml',
        'menuitem_last' => 'menuitem_last.thtml',
        'menuitem_none' => 'menuitem_none.thtml',
        'leftblocks'    => 'leftblocks.thtml',
        'rightblocks'   => 'rightblocks.thtml',
    ));
    $theme->set_var('xhtml',XHTML);

    // get topic if not on home page
    if( !isset( $_GET['topic'] )) {
        if( isset( $_GET['story'] )) {
            $sid = COM_applyFilter( $_GET['story'] );
        } elseif( isset( $_GET['sid'] )) {
            $sid = COM_applyFilter( $_GET['sid'] );
        } elseif( isset( $_POST['story'] )) {
            $sid = COM_applyFilter( $_POST['story'] );
        }
        if( empty( $sid ) && $_CONF['url_rewrite'] &&
                ( strpos( $_SERVER['PHP_SELF'], 'article.php' ) !== false )) {
            COM_setArgNames( array( 'story', 'mode' ));
            $sid = COM_applyFilter( COM_getArgument( 'story' ));
        } if( !empty( $sid )) {
            $topic = DB_getItem( $_TABLES['stories'], 'tid', "sid='$sid'" );
        }
    } else {
        $topic = COM_applyFilter( $_GET['topic'] );
    }

    $loggedInUser = !COM_isAnonUser();
    if( $loggedInUser || (( $_CONF['loginrequired'] == 0 ) &&
                ( $_CONF['searchloginrequired'] == 0 ))) {
        if(( substr( $_SERVER['PHP_SELF'], -strlen( '/search.php' ))
                != '/search.php' ) || isset( $_GET['mode'] )) {
            $relLinks['search'] = '<link rel="search" href="'
                                . $_CONF['site_url'] . '/search.php" title="'
                                . $LANG01[75] . '"' . XHTML . '>';
        }
    }
    if( $loggedInUser || (( $_CONF['loginrequired'] == 0 ) &&
                ( $_CONF['directoryloginrequired'] == 0 ))) {
        if( strpos( $_SERVER['PHP_SELF'], '/article.php' ) !== false ) {
            $relLinks['contents'] = '<link rel="contents" href="'
                        . $_CONF['site_url'] . '/directory.php" title="'
                        . $LANG01[117] . '"' . XHTML . '>';
        }
    }
    $theme->set_var( 'site_name', $_CONF['site_name']);
    $theme->set_var( 'background_image', $_CONF['layout_url']
                                          . '/images/bg.' . $_IMAGE_TYPE );
    $theme->set_var( 'site_url', $_CONF['site_url'] );
    $theme->set_var( 'layout_url', $_CONF['layout_url'] );
    $theme->set_var( 'site_mail', "mailto:{$_CONF['site_mail']}" );
    $theme->set_var( 'site_name', $_CONF['site_name'] );
    if ($use_slogan) {
        $theme->set_var( 'site_slogan', $_CONF['site_slogan'] );
    }

    $msg = $LANG01[67] . ' ' . $_CONF['site_name'];

    if( !empty( $_USER['username'] )) {
        $msg .= ', ' . COM_getDisplayName( $_USER['uid'], $_USER['username'],
                                           $_USER['fullname'] );
    }

    $curtime =  COM_getUserDateTimeFormat();

    $theme->set_var( 'welcome_msg', $msg );
    $theme->set_var( 'datetime', $curtime[0] );
    $theme->set_var( 'site_logo', $_CONF['layout_url']
                                   . '/images/logo.' . $_IMAGE_TYPE );

    $theme->set_var( 'charset', COM_getCharset());

    // Now add variables for buttons like e.g. those used by the Yahoo theme
    $theme->set_var( 'button_home', $LANG_BUTTONS[1] );
    $theme->set_var( 'button_contact', $LANG_BUTTONS[2] );
    $theme->set_var( 'button_contribute', $LANG_BUTTONS[3] );
    $theme->set_var( 'button_sitestats', $LANG_BUTTONS[7] );
    $theme->set_var( 'button_personalize', $LANG_BUTTONS[8] );
    $theme->set_var( 'button_search', $LANG_BUTTONS[9] );
    $theme->set_var( 'button_advsearch', $LANG_BUTTONS[10] );
    $theme->set_var( 'button_directory', $LANG_BUTTONS[11] );

    $theme->set_var( array (
        'lang_login'        => $LANG01[58],
        'lang_myaccount'    => $LANG01[48],
        'lang_logout'       => $LANG01[35],
        'lang_newuser'      => $LANG12[3],
    ));

    if ( function_exists('mb_getMenu') && $mbMenuConfig[0]['enabled'] == 1) {
        $theme->set_var('gllabs_menu',mb_getMenu(0,"gl_moomenu",'',"parent"));
    } else {
        $theme->set_var('gllabs_menu','');
    }

    // Get plugin menu options
    $plugin_menu = PLG_getMenuItems();

    if( $_COM_VERBOSE ) {
        COM_errorLog( 'num plugin menu items in header = ' . count( $plugin_menu ), 1 );
    }

    // Now add nested template for menu items
    COM_renderMenu( $theme, $plugin_menu );

    if( count( $plugin_menu ) == 0 ) {
        $theme->parse( 'plg_menu_elements', 'menuitem_none', true );
    } else {
        $count_plugin_menu = count( $plugin_menu );
        for( $i = 1; $i <= $count_plugin_menu; $i++ ) {
            $theme->set_var( 'menuitem_url', current( $plugin_menu ));
            $theme->set_var( 'menuitem_text', key( $plugin_menu ));

            if( $i == $count_plugin_menu ) {
                $theme->parse( 'plg_menu_elements', 'menuitem_last', true );
            } else {
                $theme->parse( 'plg_menu_elements', 'menuitem', true );
            }
            next( $plugin_menu );
        }
    }

    $lblocks = '';

    /* Check if an array has been passed that includes the name of a plugin
     * function or custom function
     * This can be used to take control over what blocks are then displayed
     */
    if( is_array( $what )) {
        $function = $what[0];
        if( function_exists( $function )) {
            $lblocks = $function( $what[1], 'left' );
        } else {
            $lblocks = COM_showBlocks( 'left', $topic );
        }
    } else if( $what <> 'none' ) {
        // Now show any blocks -- need to get the topic if not on home page
        $lblocks = COM_showBlocks( 'left', $topic );
    }

    /* Now build footer */

    if( empty( $lblocks )) {
        $theme->set_var( 'left_blocks', '' );
        $theme->set_var( 'geeklog_blocks', '' );
    } else {
        $theme->set_var( 'geeklog_blocks', $lblocks );
    }

    // Do variable assignments
    DB_change( $_TABLES['vars'], 'value', 'value + 1', 'name', 'totalhits', '', true );

    $theme->set_var( 'site_url', $_CONF['site_url']);
    $theme->set_var( 'site_mail', "mailto:{$_CONF['site_mail']}" );
    $theme->set_var( 'site_name', $_CONF['site_name'] );
    $theme->set_var( 'site_slogan', $_CONF['site_slogan'] );
    $rdf = substr_replace( $_CONF['rdf_file'], $_CONF['site_url'], 0,
                          strlen( $_CONF['path_html'] ) - 1 );
    $theme->set_var( 'rdf_file', $rdf );
    $theme->set_var( 'rss_url', $rdf );

    $year = date( 'Y' );
    $copyrightyear = $year;
    if( !empty( $_CONF['copyrightyear'] )) {
        $copyrightyear = $_CONF['copyrightyear'];
    }
    $theme->set_var( 'copyright_notice', '&nbsp;' . $LANG01[93] . ' &copy; '
            . $copyrightyear . ' ' . $_CONF['site_name'] . '<br' . XHTML . '>&nbsp;'
            . $LANG01[94] );
    $theme->set_var( 'copyright_msg', $LANG01[93] . ' &copy; '
            . $copyrightyear . ' ' . $_CONF['site_name'] );
    $theme->set_var( 'current_year', $year );
    $theme->set_var( 'lang_copyright', $LANG01[93] );
    $theme->set_var( 'trademark_msg', $LANG01[94] );
    $theme->set_var( 'powered_by', $LANG01[95] );
    $theme->set_var( 'geeklog_url', 'http://www.geeklog.net/' );
    $theme->set_var( 'geeklog_version', VERSION );
    $theme->set_var( 'gllabs_url', 'http://www.gllabs.org/' );
    $theme->set_var( 'glfusion_version', glFusion_VERSION );


    /* Check if an array has been passed that includes the name of a plugin
     * function or custom function.
     * This can be used to take control over what blocks are then displayed
     */
    if( is_array( $custom )) {
        $function = $custom['0'];
        if( function_exists( $function )) {
            $rblocks = $function( $custom['1'], 'right' );
        }
    } elseif( $rightblock == 1 ) {
        $rblocks = '';

        $rblocks = COM_showBlocks( 'right', $topic );

        if( empty( $rblocks )) {
            $theme->set_var( 'geeklog_rblocks', '');
            $theme->set_var( 'right_blocks','');
            if ( empty($lblocks) ) {
                $theme->set_var( 'centercolumn','gl_content-full' );
            } else {
                $theme->set_var( 'centercolumn','gl_content-wide-left' );
            }
        } else {
            $theme->set_var( 'geeklog_rblocks', $rblocks);
            if ( empty($lblocks) ) {
                $theme->set_var( 'centercolumn','gl_content-wide-right' );
            } else {
                $theme->set_var( 'centercolumn','gl_content' );
            }
        }
    } else {
        $theme->set_var( 'geeklog_rblocks', '');
        $theme->set_var( 'right_blocks', '' );
        if( empty( $lblocks )) {
            $theme->set_var( 'centercolumn','gl_content-full' );
        } else {
            $theme->set_var( 'centercolumn','gl_content-wide-left' );
        }
    }

    if ( !empty( $lblocks) ) {
        $theme->parse( 'left_blocks', 'leftblocks', true );
        $theme->set_var( 'geeklog_blocks', '');
    }
    if ( !empty ($rblocks) ) {
        $theme->parse( 'right_blocks', 'rightblocks', true );
        $theme->set_var( 'geeklog_rblocks', '');
    }

    $exectime = $_PAGE_TIMER->stopTimer();
    $exectext = $LANG01[91] . ' ' . $exectime . ' ' . $LANG01[92];

    $theme->set_var( 'execution_time', $exectime );
    $theme->set_var( 'execution_textandtime', $exectext );

    $theme->set_var('content',$content);

    // Call to plugins to set template variables in the footer
    PLG_templateSetVars( 'header', $theme );
    PLG_templateSetVars( 'footer', $theme );

    // Actually parse the template and make variable substitutions
    $theme->parse( 'index_footer', 'footer' );

    // The following lines allow users to embed PHP in their templates.  This
    // is almost a contradition to the reasons for using templates but this may
    // prove useful at times ...
    // Don't use PHP in templates if you can live without it!

    $tmp = $theme->finish($theme->parse( 'index_header', 'header' ));
    $retval = eval( '?>' . $tmp );

    $retval .= $theme->finish( $theme->get_var( 'index_footer' ));
    return $retval;
}

/**
* Get the current character set
*
* @return   string      character set, e.g. 'utf-8'
*
* Uses (if available, and in this order)
* - $LANG_CHARSET (from the current language file)
* - $_CONF['default_charset'] (from config.php)
* - 'iso-8859-1' (hard-coded fallback)
*
*/
if ( !function_exists('COM_getCharset') ) {
    function COM_getCharset()
    {
        global $_CONF, $LANG_CHARSET;

        if( empty( $LANG_CHARSET )) {
            $charset = $_CONF['default_charset'];
            if( empty( $charset )) {
                $charset = 'iso-8859-1';
            }
        } else {
            $charset = $LANG_CHARSET;
        }

        return $charset;
    }
}

/**
  * Checks to see if a specified user, or the current user if non-specified
  * is the anonymous user.
  *
  * @param  int $uid    ID of the user to check, or none for the current user.
  * @return boolean     true if the user is the anonymous user.
  */
if ( !function_exists('COM_isAnonUser') ) {
    function COM_isAnonUser($uid = '')
    {
        global $_USER;

        /* If no user was specified, fail over to the current user if there is one */
        if( empty( $uid ) )
        {
            if( isset( $_USER['uid'] ) )
            {
                $uid = $_USER['uid'];
            }
        }

        if( !empty( $uid ) )
        {
            return ($uid == 1);
        } else {
            return true;
        }
    }
}
//Caching Template Library code to pull redundant images from the layout/professional/ directory, so we don't have to duplicate them in our theme
function Template_finish_missingfiles($str)
{
    global $_CONF;
    return preg_replace_callback('#<img (.*)src="'.preg_quote($_CONF['layout_url']).'/([^"]*)"#i', 'missingfile_check', $str);
}

$TEMPLATE_OPTIONS['hook']['finish'] = 'Template_finish_missingfiles';

function missingfile_check($match)
{
    global $_CONF;
    if (file_exists($_CONF['path_layout'].$match[2])) {
        return $match[0];
    } else {
        return '<img ' . $match[1] . 'src="' . $_CONF['site_url'] . '/layout/professional/' . $match[2] . '"';
    }
}

// A portal staticpage (that bases itself on a user created proper portal block called gl_mootickerRSS)
//modified from LWC's forum post http://www.geeklog.net/forum/viewtopic.php?showtopic=67396 by Mark R. Evans and Joe Mucchiello
function gl_mootickerRSS() {
	global $_CONF, $_TABLES;
	$retval = '';
	$result = DB_query("SELECT *, rdfupdated as date FROM
{$_TABLES['blocks']} WHERE name='gl_mootickerRSS'");
	$numRows = DB_numRows($result);
	if ( $numRows < 1 || $result == NULL ) {
		return $retval;
	}
	$B = DB_fetchArray($result);
	if ( $B['is_enabled'] == 0 ) {
		$retval = <<<EOT
<script type="text/javascript"
src="{site_url}/layout/nouveau/js/gl_mooticker.js"></script>
<script type="text/javascript">
         window.addEvent('domready', function() {
                 var x = new MooTicker('gl_mooticker', {
                         controls: true,
                         delay: 2500,
                         duration: 600 });
         });
</script>
<div id="gl_mooticker">
   <span class="tickertitle">Latest News:</span>
EOT;
		$retval = str_replace('{site_url}',$_CONF['path_layout'] , $retval);
		$retval .= $B['content'] . '</div>';
	}
	return $retval;
}

?>