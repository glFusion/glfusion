<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-compatibility.php                                                    |
// |                                                                          |
// | Compatibility for older themes                                           |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS lib-common.php                                  |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs        - tony AT tonybibbs DOT com                   |
// |          Mark Limburg      - mlimburg AT users DOT sourceforge DOT net   |
// |          Jason Whittenburg - jwhitten AT securitygeeks DOT com           |
// |          Dirk Haun         - dirk AT haun-online DOT de                  |
// |          Vincent Furia     - vinny01 AT users DOT sourceforge DOT net    |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

$_CONF['right_blocks_in_footer'] = 1;

function COM_siteHeaderv1( $what = 'menu', $pagetitle = '', $headercode = '' )
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG_BUTTONS, $LANG_DIRECTION,
           $_IMAGE_TYPE, $topic, $_COM_VERBOSE;

    // If the theme implemented this for us then call their version instead.

    $function = $_CONF['theme'] . '_siteHeader';

    if( function_exists( $function ))
    {
        return $function( $what, $pagetitle, $headercode );
    }

    // send out the charset header
    header( 'Content-Type: text/html; charset=' . COM_getCharset());

    // If we reach here then either we have the default theme OR
    // the current theme only needs the default variable substitutions

    $header = new Template( $_CONF['path_layout'] );
    $header->set_file( array(
        'header'        => 'header.thtml',
        'menuitem'      => 'menuitem.thtml',
        'menuitem_last' => 'menuitem_last.thtml',
        'menuitem_none' => 'menuitem_none.thtml',
        'leftblocks'    => 'leftblocks.thtml',
        'rightblocks'   => 'rightblocks.thtml'
        ));
    $header->set_var( 'xhtml', XHTML );

    $cacheID = DB_getItem($_TABLES['vars'],'value','name="cacheid"');
    $header->set_var('cacheid',$cacheID);

    // get topic if not on home page
    if( !isset( $_GET['topic'] ))
    {
        if( isset( $_GET['story'] ))
        {
            $sid = COM_applyFilter( $_GET['story'] );
        }
        elseif( isset( $_GET['sid'] ))
        {
            $sid = COM_applyFilter( $_GET['sid'] );
        }
        elseif( isset( $_POST['story'] ))
        {
            $sid = COM_applyFilter( $_POST['story'] );
        }
        if( empty( $sid ) && $_CONF['url_rewrite'] &&
                ( strpos( $_SERVER['PHP_SELF'], 'article.php' ) !== false ))
        {
            COM_setArgNames( array( 'story', 'mode' ));
            $sid = COM_applyFilter( COM_getArgument( 'story' ));
        }
        if( !empty( $sid ))
        {
            $topic = DB_getItem( $_TABLES['stories'], 'tid', "sid='".DB_escapeString($sid)."'" );
        }
    }
    else
    {
        $topic = COM_applyFilter( $_GET['topic'] );
    }

    $feed_url = array();
    if( $_CONF['backend'] == 1 ) // add feed-link to header if applicable
    {
        $baseurl = SYND_getFeedUrl();

        $sql = 'SELECT format, filename, title, language FROM '
             . $_TABLES['syndication'] . " WHERE (header_tid = 'all')";
        if( !empty( $topic ))
        {
            $sql .= " OR (header_tid = '" . DB_escapeString( $topic ) . "')";
        }
        $result = DB_query( $sql );
        $numRows = DB_numRows( $result );
        for( $i = 0; $i < $numRows; $i++ )
        {
            $A = DB_fetchArray( $result );
            if ( !empty( $A['filename'] ))
            {
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
    if( !COM_onFrontpage() )
    {
        $relLinks['home'] = '<link rel="home" href="' . $_CONF['site_url']
                          . '/" title="' . $LANG01[90] . '"' . XHTML . '>';
    }
    $loggedInUser = !COM_isAnonUser();
    if( $loggedInUser || (( $_CONF['loginrequired'] == 0 ) &&
                ( $_CONF['searchloginrequired'] == 0 )))
    {
        if(( substr( $_SERVER['PHP_SELF'], -strlen( '/search.php' ))
                != '/search.php' ) || isset( $_GET['mode'] ))
        {
            $relLinks['search'] = '<link rel="search" href="'
                                . $_CONF['site_url'] . '/search.php" title="'
                                . $LANG01[75] . '"' . XHTML . '>';
        }
    }
    if( $loggedInUser || (( $_CONF['loginrequired'] == 0 ) &&
                ( $_CONF['directoryloginrequired'] == 0 )))
    {
        if( strpos( $_SERVER['PHP_SELF'], '/article.php' ) !== false ) {
            $relLinks['contents'] = '<link rel="contents" href="'
                        . $_CONF['site_url'] . '/directory.php" title="'
                        . $LANG01[117] . '"' . XHTML . '>';
        }
    }
    if (!$_CONF['disable_webservices']) {
        $relLinks['service'] = '<link rel="service" '
                    . 'type="application/atomsvc+xml" ' . 'href="'
                    . $_CONF['site_url'] . '/webservices/atom/?introspection" '
                    . 'title="' . $LANG01[130] . '"' . XHTML . '>';
    }
    // TBD: add a plugin API and a lib-custom.php function
    $header->set_var( 'rel_links', implode( LB, $relLinks ));

    if( empty( $pagetitle ) && isset( $_CONF['pagetitle'] ))
    {
        $pagetitle = $_CONF['pagetitle'];
    }
    if( empty( $pagetitle ))
    {
        if( empty( $topic ))
        {
            $pagetitle = $_CONF['site_slogan'];
        }
        else
        {
            $pagetitle = stripslashes( DB_getItem( $_TABLES['topics'], 'topic',
                                                   "tid = '".DB_escapeString($topic)."'" ));
        }
    }
    if( !empty( $pagetitle ))
    {
        $header->set_var( 'page_site_splitter', ' - ');
    }
    else
    {
        $header->set_var( 'page_site_splitter', '');
    }
    $header->set_var( 'page_title', $pagetitle );
    $header->set_var( 'site_name', $_CONF['site_name']);

    if (COM_onFrontpage()) {
        $title_and_name = $_CONF['site_name'];
        if (!empty($pagetitle)) {
            $title_and_name .= ' - ' . $pagetitle;
        }
    } else {
        $title_and_name = '';
        if (!empty($pagetitle)) {
            $title_and_name = $pagetitle . ' - ';
        }
        $title_and_name .= $_CONF['site_name'];
    }
    $header->set_var('page_title_and_site_name', $title_and_name);

    $langAttr = '';
    if( !empty( $_CONF['languages'] ) && !empty( $_CONF['language_files'] ))
    {
        $langId = COM_getLanguageId();
    }
    else
    {
        // try to derive the language id from the locale
        $l = explode( '.', $_CONF['locale'] );
        $langId = $l[0];
    }
    if( !empty( $langId ))
    {
        $l = explode( '-', str_replace( '_', '-', $langId ));
        if(( count( $l ) == 1 ) && ( strlen( $langId ) == 2 ))
        {
            $langAttr = 'lang="' . $langId . '"';
        }
        else if( count( $l ) == 2 )
        {
            if(( $l[0] == 'i' ) || ( $l[0] == 'x' ))
            {
                $langId = implode( '-', $l );
                $langAttr = 'lang="' . $langId . '"';
            }
            else if( strlen( $l[0] ) == 2 )
            {
                $langId = implode( '-', $l );
                $langAttr = 'lang="' . $langId . '"';
            }
            else
            {
                $langId = $l[0];
            }
        }
    }
    $header->set_var('lang_id', $langId );
    if (!empty($_CONF['languages']) && !empty($_CONF['language_files'])) {
        $header->set_var('lang_attribute', $langAttr);
    } else {
        $header->set_var('lang_attribute', '');
    }

    $header->set_var( 'background_image', $_CONF['layout_url']
                                          . '/images/bg.' . $_IMAGE_TYPE );
    $header->set_var( 'site_url', $_CONF['site_url'] );
    $header->set_var( 'site_admin_url', $_CONF['site_admin_url'] );
    $header->set_var( 'layout_url', $_CONF['layout_url'] );
    $header->set_var( 'site_mail', "mailto:{$_CONF['site_mail']}" );
    $header->set_var( 'site_name', $_CONF['site_name'] );
    $header->set_var( 'site_slogan', $_CONF['site_slogan'] );
    $rdf = substr_replace( $_CONF['rdf_file'], $_CONF['site_url'], 0,
                           strlen( $_CONF['path_html'] ) - 1 );
    $header->set_var( 'rdf_file', $rdf );
    $header->set_var( 'rss_url', $rdf );

    $msg = rtrim($LANG01[67]) . ' ' . $_CONF['site_name'];

    if( !empty( $_USER['username'] ))
    {
        $msg .= ', ' . COM_getDisplayName( $_USER['uid'], $_USER['username'],
                                           $_USER['fullname'] );
    }

    $curtime =  COM_getUserDateTimeFormat();

    $header->set_var( 'welcome_msg', $msg );
    $header->set_var( 'datetime', $curtime[0] );
    $header->set_var( 'site_logo', $_CONF['layout_url']
                                   . '/images/logo.' . $_IMAGE_TYPE );
    $header->set_var( 'css_url', $_CONF['site_url'].'/css.php?t='.$_CONF['theme'].'&amp;i='.$cacheID);
    $header->set_var( 'theme', $_CONF['theme'] );

    $header->set_var( 'charset', COM_getCharset());
    if( empty( $LANG_DIRECTION ))
    {
        // default to left-to-right
        $header->set_var( 'direction', 'ltr' );
    }
    else
    {
        $header->set_var( 'direction', $LANG_DIRECTION );
    }

    // Now add variables for buttons like e.g. those used by the Yahoo theme
    $header->set_var( 'button_home', $LANG_BUTTONS[1] );
    $header->set_var( 'button_contact', $LANG_BUTTONS[2] );
    $header->set_var( 'button_contribute', $LANG_BUTTONS[3] );
    $header->set_var( 'button_sitestats', $LANG_BUTTONS[7] );
    $header->set_var( 'button_personalize', $LANG_BUTTONS[8] );
    $header->set_var( 'button_search', $LANG_BUTTONS[9] );
    $header->set_var( 'button_advsearch', $LANG_BUTTONS[10] );
    $header->set_var( 'button_directory', $LANG_BUTTONS[11] );

    // Get plugin menu options
    $plugin_menu = PLG_getMenuItems();

    if( $_COM_VERBOSE )
    {
        COM_errorLog( 'num plugin menu items in header = ' . count( $plugin_menu ), 1 );
    }

    // Now add nested template for menu items
    COM_renderMenu( $header, $plugin_menu );

    if( count( $plugin_menu ) == 0 )
    {
        $header->parse( 'plg_menu_elements', 'menuitem_none', true );
    }
    else
    {
        $count_plugin_menu = count( $plugin_menu );
        for( $i = 1; $i <= $count_plugin_menu; $i++ )
        {
            $header->set_var( 'menuitem_url', current( $plugin_menu ));
            $header->set_var( 'menuitem_text', key( $plugin_menu ));

            if( $i == $count_plugin_menu )
            {
                $header->parse( 'plg_menu_elements', 'menuitem_last', true );
            }
            else
            {
                $header->parse( 'plg_menu_elements', 'menuitem', true );
            }

            next( $plugin_menu );
        }
    }

    $headercode = '<script type="text/javascript" src="'.$_CONF['site_url'].'/javascript/mootools/mootools-1.2.3.1-more.js"></script>' . $headercode;
    $headercode = '<script type="text/javascript" src="'.$_CONF['site_url'].'/javascript/mootools/mootools-1.2.3-core.js"></script>' . $headercode;

    // Call any plugin that may want to include extra Meta tags
    // or Javascript functions
    $header->set_var( 'plg_headercode', $headercode . PLG_getHeaderCode() );

    // Call to plugins to set template variables in the header
    PLG_templateSetVars( 'header', $header );

    if( $_CONF['left_blocks_in_footer'] == 1 )
    {
        $header->set_var( 'left_blocks', '' );
        $header->set_var( 'geeklog_blocks', '' );
    }
    else
    {
        $lblocks = '';

        /* Check if an array has been passed that includes the name of a plugin
         * function or custom function
         * This can be used to take control over what blocks are then displayed
         */
        if( is_array( $what ))
        {
            $function = $what[0];
            if( function_exists( $function ))
            {
                $lblocks = $function( $what[1], 'left' );
            }
            else
            {
                $lblocks = COM_showBlocks( 'left', $topic );
            }
        }
        else if( $what <> 'none' )
        {
            // Now show any blocks -- need to get the topic if not on home page
            $lblocks = COM_showBlocks( 'left', $topic );
        }

        if( empty( $lblocks ))
        {
            $header->set_var( 'left_blocks', '' );
            $header->set_var( 'geeklog_blocks', '' );
        }
        else
        {
            $header->set_var( 'geeklog_blocks', $lblocks );
            $header->parse( 'left_blocks', 'leftblocks', true );
            $header->set_var( 'geeklog_blocks', '');
        }
    }

    if( $_CONF['right_blocks_in_footer'] == 1 )
    {
        $header->set_var( 'right_blocks', '' );
        $header->set_var( 'geeklog_blocks', '' );
    }
    else
    {
        $rblocks = '';

        /* Check if an array has been passed that includes the name of a plugin
         * function or custom function
         * This can be used to take control over what blocks are then displayed
         */
        if( is_array( $what ))
        {
            $function = $what[0];
            if( function_exists( $function ))
            {
                $rblocks = $function( $what[1], 'right' );
            }
            else
            {
                $rblocks = COM_showBlocks( 'right', $topic );
            }
        }
        else if( $what <> 'none' )
        {
            // Now show any blocks -- need to get the topic if not on home page
            $rblocks = COM_showBlocks( 'right', $topic );
        }

        if( empty( $rblocks ))
        {
            $header->set_var( 'right_blocks', '' );
            $header->set_var( 'geeklog_blocks', '' );
        }
        else
        {
            $header->set_var( 'geeklog_blocks', $rblocks, true );
            $header->parse( 'right_blocks', 'rightblocks', true );
        }
    }

    if( isset( $_CONF['advanced_editor'] ) && ( $_CONF['advanced_editor'] == 1 )
            && file_exists( $_CONF['path_layout']
                            . 'advanced_editor_header.thtml' ))
    {
        $header->set_file( 'editor'  , 'advanced_editor_header.thtml');
        $header->parse( 'advanced_editor', 'editor' );

    }
    else
    {
         $header->set_var( 'advanced_editor', '' );
    }

    // Call any plugin that may want to include extra Meta tags
    // or Javascript functions
    $header->set_var( 'plg_headercode', $headercode . PLG_getHeaderCode() );

    // The following lines allow users to embed PHP in their templates.  This
    // is almost a contradition to the reasons for using templates but this may
    // prove useful at times ...
    // Don't use PHP in templates if you can live without it!

    $tmp = $header->parse( 'index_header', 'header' );

    $xml_declaration = '';
    if ( get_cfg_var('short_open_tag') == '1' )
    {
        if ( preg_match( '/(<\?xml[^>]*>)(.*)/s', $tmp, $match ) )
        {
            $xml_declaration = $match[1] . LB;
            $tmp = $match[2];
        }
    }

    ob_start();
    eval( '?>' . $tmp );
    $retval = $xml_declaration . ob_get_contents();
    ob_end_clean();

    return $retval;
}


/**
* Returns the site footer
*
* This loads the proper templates, does variable substitution and returns the
* HTML for the site footer.
*
* @param   boolean     $rightblock     Whether or not to show blocks on right hand side default is no
* @param   array       $custom         An array defining custom function to be used to format Rightblocks
* @see function COM_siteHeader
* @return   string  Formated HTML containing site footer and optionally right blocks
*
*/
function COM_siteFooterv1( $rightblock = -1, $custom = '' )
{
    global $_CONF, $_TABLES, $LANG01, $_PAGE_TIMER, $topic, $LANG_BUTTONS;

    // If the theme implemented this for us then call their version instead.

    $function = $_CONF['theme'] . '_siteFooter';

    if( function_exists( $function ))
    {
        return $function( $rightblock, $custom );
    }

    COM_hit();

    // Set template directory
    $footer = new Template( $_CONF['path_layout'] );

    // Set template file
    $footer->set_file( array(
            'footer'      => 'footer.thtml',
            'rightblocks' => 'rightblocks.thtml',
            'leftblocks'  => 'leftblocks.thtml'
            ));

    // Do variable assignments
    $footer->set_var( 'xhtml', XHTML );
    $footer->set_var( 'site_url', $_CONF['site_url']);
    $footer->set_var( 'site_admin_url', $_CONF['site_admin_url']);
    $footer->set_var( 'layout_url',$_CONF['layout_url']);
    $footer->set_var( 'site_mail', "mailto:{$_CONF['site_mail']}" );
    $footer->set_var( 'site_name', $_CONF['site_name'] );
    $footer->set_var( 'site_slogan', $_CONF['site_slogan'] );
    $rdf = substr_replace( $_CONF['rdf_file'], $_CONF['site_url'], 0,
                           strlen( $_CONF['path_html'] ) - 1 );
    $footer->set_var( 'rdf_file', $rdf );
    $footer->set_var( 'rss_url', $rdf );

    $year = date( 'Y' );
    $copyrightyear = $year;
    if( !empty( $_CONF['copyrightyear'] ))
    {
        $copyrightyear = $_CONF['copyrightyear'];
    }
    $footer->set_var( 'copyright_notice', '&nbsp;' . $LANG01[93] . ' &copy; '
            . $copyrightyear . ' ' . $_CONF['site_name'] . '<br' . XHTML . '>&nbsp;'
            . $LANG01[94] );
    $footer->set_var( 'copyright_msg', $LANG01[93] . ' &copy; '
            . $copyrightyear . ' ' . $_CONF['site_name'] );
    $footer->set_var( 'current_year', $year );
    $footer->set_var( 'lang_copyright', $LANG01[93] );
    $footer->set_var( 'trademark_msg', $LANG01[94] );
    $footer->set_var( 'powered_by', $LANG01[95] );
    $footer->set_var( 'geeklog_url', 'http://www.glfusion.org/' );
    $footer->set_var( 'geeklog_version', GVERSION );
    // Now add variables for buttons like e.g. those used by the Yahoo theme
    $footer->set_var( 'button_home', $LANG_BUTTONS[1] );
    $footer->set_var( 'button_contact', $LANG_BUTTONS[2] );
    $footer->set_var( 'button_contribute', $LANG_BUTTONS[3] );
    $footer->set_var( 'button_sitestats', $LANG_BUTTONS[7] );
    $footer->set_var( 'button_personalize', $LANG_BUTTONS[8] );
    $footer->set_var( 'button_search', $LANG_BUTTONS[9] );
    $footer->set_var( 'button_advsearch', $LANG_BUTTONS[10] );
    $footer->set_var( 'button_directory', $LANG_BUTTONS[11] );

    /* Right blocks. Argh. Don't talk to me about right blocks...
     * Right blocks will be displayed if Right_blocks_in_footer is set [1],
     * AND (this function has been asked to show them (first param) OR the
     * show_right_blocks conf variable has been set to override what the code
     * wants to do.
     *
     * If $custom sets an array (containing functionname and first argument)
     * then this is used instead of the default (COM_showBlocks) to render
     * the right blocks (and left).
     *
     * [1] - if it isn't, they'll be in the header already.
     *
     */
    $displayRightBlocks = true;
    if ($_CONF['right_blocks_in_footer'] == 1)
    {
        if( ($rightblock < 0) || !$rightblock )
        {
            if( isset( $_CONF['show_right_blocks'] ) )
            {
                $displayRightBlocks = $_CONF['show_right_blocks'];
            }
            else
            {
                $displayRightBlocks = false;
            }
        } else {
            $displayRightBlocks = true;
        }
    } else {
        $displayRightBlocks = false;
    }

    if ($displayRightBlocks)
    {
        /* Check if an array has been passed that includes the name of a plugin
         * function or custom function.
         * This can be used to take control over what blocks are then displayed
         */
        if( is_array( $custom ))
        {
            $function = $custom['0'];
            if( function_exists( $function ))
            {
                $rblocks = $function( $custom['1'], 'right' );
            } else {
                $rblocks = COM_showBlocks( 'right', $topic );
            }
        } else {
            $rblocks = COM_showBlocks( 'right', $topic );
        }

        if( empty( $rblocks ))
        {
            $footer->set_var( 'geeklog_blocks', '');
            $footer->set_var( 'right_blocks', '' );
        } else {
            $footer->set_var( 'geeklog_blocks', $rblocks);
            $footer->parse( 'right_blocks', 'rightblocks', true );
            $footer->set_var( 'geeklog_blocks', '');
        }
    } else {
        $footer->set_var( 'geeklog_blocks', '');
        $footer->set_var( 'right_blocks', '' );
    }

    if( $_CONF['left_blocks_in_footer'] == 1 )
    {
        $lblocks = '';

        /* Check if an array has been passed that includes the name of a plugin
         * function or custom function
         * This can be used to take control over what blocks are then displayed
         */
        if( is_array( $custom ))
        {
            $function = $custom[0];
            if( function_exists( $function ))
            {
                $lblocks = $function( $custom[1], 'left' );
            }
        }
        else
        {
            $lblocks = COM_showBlocks( 'left', $topic );
        }

        if( empty( $lblocks ))
        {
            $footer->set_var( 'left_blocks', '' );
            $footer->set_var( 'geeklog_blocks', '');
        }
        else
        {
            $footer->set_var( 'geeklog_blocks', $lblocks);
            $footer->parse( 'left_blocks', 'leftblocks', true );
            $footer->set_var( 'geeklog_blocks', '');
        }
    }

    // Global centerspan variable set in index.php
    if( isset( $GLOBALS['centerspan'] ))
    {
        $footer->set_var( 'centerblockfooter-span', '</td></tr></table>' );
    }

    $exectime = $_PAGE_TIMER->stopTimer();
    $exectext = $LANG01[91] . ' ' . $exectime . ' ' . $LANG01[92];

    $footer->set_var( 'execution_time', $exectime );
    $footer->set_var( 'execution_textandtime', $exectext );

    // Call to plugins to set template variables in the footer
    PLG_templateSetVars( 'footer', $footer );

    // Actually parse the template and make variable substitutions
    $footer->parse( 'index_footer', 'footer' );

    // Return resulting HTML
    return $footer->finish( $footer->get_var( 'index_footer' ));
}
?>