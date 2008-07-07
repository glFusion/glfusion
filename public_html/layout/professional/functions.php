<?php

// this file can't be used on its own
if (strpos($_SERVER['PHP_SELF'], 'functions.php') !== false) {
    die('This file can not be used on its own!');
}

$_IMAGE_TYPE = 'png';

if (!defined('XHTML')) {
    define('XHTML', ''); // change this to ' /' for XHTML
}

$lang = COM_getLanguageId();
if (empty($lang)) {
    $result = DB_query("SELECT onleft,name FROM {$_TABLES['blocks']} WHERE is_enabled = 1");
} else {
    $result = DB_query("SELECT onleft,name FROM {$_TABLES['blocks']}");
}
$nrows = DB_numRows($result);
for ($i = 0; $i < $nrows; $i++) {
    $A = DB_fetchArray($result);
        if ($A['onleft'] == 1) {
            $_BLOCK_TEMPLATE[$A['name']] = 'blockheader-left.thtml,blockfooter-left.thtml';
        } else {
            $_BLOCK_TEMPLATE[$A['name']] = 'blockheader-right.thtml,blockfooter-right.thtml';
    }
}

$_BLOCK_TEMPLATE['_msg_block'] = 'blockheader-message.thtml,blockfooter-message.thtml';
$_BLOCK_TEMPLATE['configmanager_block'] = 'blockheader-config.thtml,blockfooter-config.thtml';
$_BLOCK_TEMPLATE['configmanager_subblock'] = 'blockheader-config.thtml,blockfooter-config.thtml';
$_BLOCK_TEMPLATE['whats_related_block'] = 'blockheader-related.thtml,blockfooter-related.thtml';
$_BLOCK_TEMPLATE['story_options_block'] = 'blockheader-related.thtml,blockfooter-related.thtml';

// Define the blocks that are a list of links styled as an unordered list - using class="blocklist"
$_BLOCK_TEMPLATE['admin_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';
$_BLOCK_TEMPLATE['section_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';
$_BLOCK_TEMPLATE['user_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';


function professional_siteHeader( $what = 'menu', $pagetitle = '', $headercode = '' )
{
    global $_CONF, $_TABLES, $_USER, $LANG01, $LANG_BUTTONS, $LANG_DIRECTION,
           $_IMAGE_TYPE, $topic, $_COM_VERBOSE;

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
            $topic = DB_getItem( $_TABLES['stories'], 'tid', "sid='$sid'" );
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
            $sql .= " OR (header_tid = '" . addslashes( $topic ) . "')";
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
                                                   "tid = '$topic'" ));
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

    $msg = $LANG01[67] . ' ' . $_CONF['site_name'];

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
    $header->set_var( 'css_url', $_CONF['layout_url'] . '/style.css' );
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
function professional_siteFooter( $rightblock = -1, $custom = '' )
{
    global $_CONF, $_TABLES, $LANG01, $_PAGE_TIMER, $topic, $LANG_BUTTONS;

    // use the right blocks here only if not in header already
    if ($_CONF['right_blocks_in_footer'] == 1)
    {
        if( $rightblock < 0)
        {
            if( isset( $_CONF['show_right_blocks'] ))
            {
                $rightblock = $_CONF['show_right_blocks'];
            }
            else
            {
                $rightblock = false;
            }
        }
    }

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
    $footer->set_var( 'geeklog_url', 'http://www.geeklog.net/' );
    $footer->set_var( 'geeklog_version', VERSION );
    $footer->set_var( 'gllabs_url', 'http://www.gllabs.org/' );
    $footer->set_var( 'glfusion_version', glFusion_VERSION );

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
        }
    }
    elseif( $rightblock )
    {
        $rblocks = COM_showBlocks( 'right', $topic );
    }
    if( $rightblock && !empty( $rblocks ))
    {
        $footer->set_var( 'geeklog_blocks', $rblocks );
        $footer->parse( 'right_blocks', 'rightblocks', true );
    }
    else
    {
        $footer->set_var( 'geeklog_blocks', '' );
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
