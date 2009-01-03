<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | ouptut.class.php                                                         |
// |                                                                          |
// | glFusion Browser Output Handler                                          |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Incorporates functions from the lib-head.php by Joe Mucchiello (c) 2008  |
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

define('HEADER_PRIO_VERYHIGH',  1);
define('HEADER_PRIO_HIGH',      2);
define('HEADER_PRIO_NORMAL',    3);
define('HEADER_PRIO_LOW',       4);
define('HEADER_PRIO_VERYLOW',   5);

/**
 * outputHandler class
 *
 * @package 	glFusion.Framework
 * @subpackage	output
 * @since 		1.2.0
 */

class outputHandler {

    public  $pageTemplate;
    private $_buffer = '';
    private $_msg = '';
    private $_displayNavigationBlocks = true;
    private $_displayExtraBlocks = true;
    private $_pagetitle = '';
    private $_frontPage = false;
    private $_errorlog_fn = NULL;
    private $_what = '';
    private $_custom = array();
    private $_charset = 'iso-8859-1';
    private $_topic = '';
    private $_headercode = '';
    private $_navigationBlocks = '';
    private $_extraBlocks = '';
    private $_curtime = '';
    private $_copyrightyear;
    private $_directHeader = array();
    private $_rewriteEnabled = false;
    private $_header = array(
                    'meta' => array('http-equiv' => array(), 'name' => array()),
                    'style' => array(HEADER_PRIO_VERYHIGH => array(),
                                     HEADER_PRIO_HIGH => array(),
                                     HEADER_PRIO_NORMAL => array(),
                                     HEADER_PRIO_LOW => array(),
                                     HEADER_PRIO_VERYLOW => array()),
                    'script' => array(HEADER_PRIO_VERYHIGH => array(),
                                     HEADER_PRIO_HIGH => array(),
                                     HEADER_PRIO_NORMAL => array(),
                                     HEADER_PRIO_LOW => array(),
                                     HEADER_PRIO_VERYLOW => array()),
                    'raw' => array(HEADER_PRIO_VERYHIGH => array(),
                                     HEADER_PRIO_HIGH => array(),
                                     HEADER_PRIO_NORMAL => array(),
                                     HEADER_PRIO_LOW => array(),
                                     HEADER_PRIO_VERYLOW => array()),
                    );


    /**************************************************************************/
    // Public Methods:

    /**
     * Constructor, initializes the output buffering.
     */

    public function __construct() {
        global $_CONF;

    	ob_start( );        // buffer any output
        $this->_charset         = COM_getCharset();
        $this->pageTemplate     = new Template ($_CONF['path_layout']);
        $this->_curtime         =  COM_getUserDateTimeFormat();
        $this->_rewriteEnabled  = $_CONF['url_rewrite'];
    }

	/**
	 * Returns a reference to a global outputHandler object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>$outputHandle = outputHandler::getInstance();</pre>
	 *
	 * @static
	 * @return	object	The outputHandler object.
	 * @since	1.2.0
	 */
    function &getInstance()
    {
        static $instance;

        if (!$instance) {
            $instance = new outputHandler();
        }

        return $instance;
    }

	/**
	 * Add CSS to a page
	 *
	 * This adds raw CSS, it should not be wrapped in a <style> tag.
	 *
	 * @param  string   $code The CSS code
	 * @param  int      $priority Load priority
	 * @param  string   $mime The mime type of the CSS, 'text/css' used if no
	 *                  other type passed.
	 * @access public
	 * @return nothing
	 */
    public function addStyle($code, $priority = HEADER_PRIO_NORMAL, $mime = 'text/css')
    {
        if ( $code != '' ) {
            $this->_header['style'][$priority][] = '<style type="' . $mime . "\">" . $code . "</style>";
        }
    }

	/**
	 * Add JavaScript to a page
	 *
	 * This adds raw JavaScript, it should not be wrapped in a <script> tag.
	 *
	 * @param  string   $code       The JavaScript code
	 * @param  int      $priority   Load priority
	 * @param  string   $mime       The mime type of the JS, 'text/javascript'
	 *                              used if no other type passed.
	 * @access public
	 * @return nothing
	 */
    public function addScript($code, $priority = HEADER_PRIO_NORMAL, $mime = 'text/javascript')
    {
        if ($code != '') {
            $this->_header['script'][$priority][] = '<script type="' . $mime . "\">".LB."<!--" . LB . $code . LB ."// --></script>" . LB;
        }
    }


	/**
	 * Add a Link to a page
	 *
	 * This adds raw Link, it should not be wrapped in a <link> tag.
	 *
	 * @param  string   $rel        Link relationship
	 * @param  string   $href       The link href
	 * @param  string   $type       Type attribute (optional)
	 * @param  int      $priority   Load priority
	 * @param  array    $attr       Attributes array
     *
	 * @access public
	 * @return nothing
	 */
    public function addLink($rel, $href, $type = '', $priority = HEADER_PRIO_NORMAL, $attrs = array())
    {
        $link = '<link rel="' . $rel .'" href="' . htmlspecialchars($href) . '"';
        if (!empty($type)) {
            $link .= ' type="' . $type . '"';
        }
        if (is_array($attrs)) {
            foreach ($attrs as $k => $v) {
                $link .= ' ' . $k . '="' . $v . '"';
            }
        }
        $link .= XHTML . ">" . LB;

        $this->_header['raw'][$priority][] = $link;
    }


	/**
	 * Add a stylesheet to a page
	 *
	 * This adds a stylesheet to a page - The URL should not have the <link>
	 * attribute.
	 *
	 * @param  string   $href       The URL to the stylesheet
	 * @param  int      $priority   Load priority
	 * @param  string   $mime       The mime type of the stylesheet, 'text/css'
	 *                              used if no other type passed.
	 * @param  array    $attr       Attributes array
     *
	 * @access public
	 * @return nothing
	 */
    public function addLinkStyle($href, $priority = HEADER_PRIO_NORMAL, $mime = 'text/css', $attrs = array())
    {
        $link = '<link rel="stylesheet" type="' . $mime . '" href="' . htmlspecialchars($href) . '"';
        if (is_array($attrs)) {
            foreach ($attrs as $k => $v) {
                $link .= ' ' . $k . '="' . $v . '"';
            }
        }
        $link .= XHTML . ">" . LB;

        $this->_header['style'][$priority][] = $link;
    }


	/**
	 * Add a JavaScript source to a page
	 *
	 * This adds a javascript source file to a page - The URL should not have
	 * the <link> attribute.
	 *
	 * @param  string   $href       The URL to the javascript file
	 * @param  int      $priority   Load priority
	 * @param  string   $mime       The mime type of the stylesheet, 'text/css'
	 *                              used if no other type passed.
     *
	 * @access public
	 * @return nothing
	 */
    public function addLinkScript($href, $priority = HEADER_PRIO_NORMAL, $mime = 'text/javascript')
    {
        $link = '<script type="' . $mime . '" src="' . htmlspecialchars($href) . '"';
        $link .= "></script>" . LB;

        $this->_header['script'][$priority][] = $link;
    }


	/**
	 * Add Meta data to header
	 *
	 * This adds a meta name to the header
	 *
	 * @param  string   $name       Name of the meta attribute
	 * @param  string   $cotent     Content of the attribute
     *
	 * @access public
	 * @return nothing
	 */
    public function addMetaName($name, $content)
    {
        $this->_header['meta']['name'] = '<meta name="' .  $name . '" content="' . $content . '"' . XHTML .">" . LB;
    }


	/**
	 * Add Meta HTTP Equiv data to header
	 *
	 * This adds a meta name to the header
	 *
	 * @param  string   $header     header name
	 * @param  string   $cotent     Content of the attribute
     *
	 * @access public
	 * @return nothing
	 */
    public function addMetaHttpEquiv($header, $content)
    {
        $this->_header['meta']['http-equiv'] = '<meta http-equiv="' . $header .'" content="' . $content . '"' . XHTML .">" . LB;
    }


	/**
	 * Add a raw header
	 *
	 * Adds a raw header entry.  The code must be fully formed and include
	 * all necessary HTML.  No manipulations are done to the data.
	 *
	 * @param  string   $code       Fully formed code to add
	 * @param  int      $priority   Load priority
     *
	 * @access public
	 * @return nothing
	 */
    public function addRaw($code, $priority = HEADER_PRIO_NORMAL)
    {
        $this->_header['raw'][$priority][] = $code . LB;
    }


	/**
	 * Add a direct header
	 *
	 * Passes the $text variable directly to the PHP header() function.
	 * These items are always sent first.
	 *
	 * @param  string   $text       direct text to send to browser.
     *
	 * @access public
	 * @return nothing
	 */
    public function addDirectHeader($text) {
        $this->_directHeader[] = $text;
    }


	/**
	 * Add content to a page
	 *
	 * Stores the page contents (after the header)
	 *
	 * @param  string   $content    content to place in page.
     *
	 * @access public
	 * @return nothing
	 */
    public function addContent( $content ) {
        $this->_buffer .= $content;
    }


	/**
	 * Adds a message block to the top of the page
	 *
	 * This function takes a message number and optional plugin name
	 * It will format the message text in the standard system message
	 * box and ensure it is displayed at the top of the page.
	 *
	 * @param  int      $msg    The message number
	 * @param  string   $plugin Optional plugin name
     *
	 * @access public
	 * @return nothing
	 */
    public function addMessage( $msg, $plugin = '') {
        global $_CONF, $MESSAGE;

        if ($msg > 0) {
            $timestamp = strftime($_CONF['daytime']);
            if (!empty($plugin)) {
                $var = 'PLG_' . $plugin . '_MESSAGE' . $msg;
                global $$var;
                if (isset($$var)) {
                    $message = $$var;
                } else {
                    $message = sprintf($MESSAGE[61], $plugin);
                    COM_errorLog($MESSAGE[61] . ": " . $var, 1);
                }
            } else {
                $message = $MESSAGE[$msg];
            }

            $this->_msg .= $message . '<br />';
        }
    }


	/**
	 * Adds message text
	 *
	 * This function takes message text
	 * It will format the message text in the standard system message
	 * box and ensure it is displayed at the top of the page.
	 *
	 * @param  string   $text   The text to display
     *
	 * @access public
	 * @return nothing
	 */
    public function addMessageText( $text ) {
        $this->_msg .= $text . '<br />';
    }


	/**
	 * Set Show Extra Blocks
	 *
	 * This function will set whether the extra blocks should be displayed.
	 *
	 * @param  bool     $bool   True or False
     *
	 * @access public
	 * @return nothing
	 */
    public function setShowExtraBlocks( $bool ) {
        $this->_displayExtraBlocks =  $bool ? 1 : 0;
    }


	/**
	 * Set Show Navigation Blocks
	 *
	 * This function will set whether the navigation blocks should be displayed.
	 *
	 * @param  bool     $bool   True or False
     *
	 * @access public
	 * @return nothing
	 */
    public function setShowNavigationBlocks( $bool ) {
        $this->_displayNavigationBlocks =  $bool ? 1 : 0;
    }


	/**
	 * Set Navigation block custom function
	 *
	 * Allows overriding the default navigation block code with custom function.
	 *
	 * @param  string   $what   The name of the custom block function
     *
	 * @access public
	 * @return nothing
	 */
    public function setNavigationBlocksFunction ( $what ) {
        $this->_what = array();
        $this->_what = $what;
    }


	/**
	 * Set Extra block custom function
	 *
	 * Allows overriding the default extra block code with custom function.
	 *
	 * @param  string   $what   The name of the custom block function
     *
	 * @access public
	 * @return nothing
	 */
    public function setExtraBlocksFunction ($custom ) {
        $this->_custom = $custom;
    }


	/**
	 * Set the page title
	 *
	 * Sets the page title to display in the browser title bar
	 *
	 * @param  string   $title  The page title
     *
	 * @access public
	 * @return nothing
	 */
    public function setPageTitle($title) {
        $this->_pagetitle = $title;
    }


	/**
	 * Display Error
	 *
	 * Displays an error message, ignoring any content already added
	 * via the addContent() call.  This function terminates the running script.
	 *
	 * @param  string   $content    The error message to display
     *
	 * @access public
	 * @return nothing
	 */
    public function displayError( $content ) {
        $this->_msg = '';
        $this->_buffer = '<p style="width:100%;text-align:center;"><span class="alert pluginAlert" style="text-align:center;font-size:1.5em;">' . $content . '</span></p>';
        COM_errorLog('ERRORLOG: ' . $content);
        $this->displayPage();
        exit;
    }


	/**
	 * Display Permissiong Denied
	 *
	 * Displays an error message, ignoring any content already added
	 * via the addContent() call.  This function terminates the running script.
	 *
	 * @param  string   $content    The error message to display
     *
	 * @access public
	 * @return nothing
	 */
    public function displayAccessError( $pageTitle='',$message='', $desc='' ) {
        global $_USER, $LANG_ACCESS;

        if ( $pageTitle != '' ) {
            $this->setPageTitle($pageTitle);
        } else {
            $this->setPageTitle($LANG_ACCESS['accessdenied']);
        }
        if ( $message == '' ) {
            $message = 'You have attempted to access a protected resource.';
        }
        if ( $desc == '' ) {
            $desc = 'an administrative area.';
        }
        $this->_buffer = COM_startBlock ($pageTitle, '',
                          COM_getBlockTemplate ('_msg_block', 'header'))
                             . $message
                             . COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
        COM_accessLog ("User {$_USER['username']} tried to illegally access " . $desc);
        $this->displayPage();
        exit;
    }

	/**
	 * Display Login Required
	 *
	 * Displays an error message, ignoring any content already added
	 * via the addContent() call.  This function terminates the running script.
	 *
	 * @param  string   $content    The error message to display
     *
	 * @access public
	 * @return nothing
	 */
    public function displayLoginRequired( )
    {
        global $_CONF, $LANG_LOGIN;

        $this->setPageTitle($LANG_LOGIN[1]);

        $this->_buffer = COM_startBlock ($LANG_LOGIN[1], '',
                                COM_getBlockTemplate ('_msg_block', 'header'));
        $login = new Template($_CONF['path_layout'] . 'submit');
        $login->set_file (array ('login'=>'submitloginrequired.thtml'));
        $login->set_var ('login_message', $LANG_LOGIN[2]);
        $login->set_var ('lang_login', $LANG_LOGIN[3]);
        $login->set_var ('lang_newuser', $LANG_LOGIN[4]);
        $login->parse ('output', 'login');

        $this->_buffer .= $login->finish ($login->get_var('output'));
        $this->_buffer .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));

        $this->displayPage();
        exit;
    }


	/**
	 * Redirect
	 *
	 * Immediately redirects to the passed URL
	 *
	 * @param  string   $url    The URL to redirect to.
     *
	 * @access public
	 * @return nothing
	 */
    public function redirect($url) {
        echo "<html><head><meta http-equiv=\"refresh\" content=\"0; URL=$url\"" . XHTML . "></head></html>" . LB;
        exit;
    }


	/**
	 * Retrieves the full URL to an image file
	 *
	 * This function will attempt to locate an image file using the following
	 * search order:
	 *
	 *   - Current theme images/ directory
	 *   - Nouveau theme images/ directory
	 *   - Public HTML images/ directory
	 *
	 * If namespace (plugin) is provided, the search order will be
	 *
	 *   - current theme/plugins/$namespace/images/
	 *   - public_html/$namespace/images/
	 *   - public_html/admin/plugins/$namespace/images/
	 *
	 * @param  string   $image      The name of the image file
	 * @param  string   $namespace  Plugin name (optional)
     *
	 * @access public
	 * @return nothing
	 */
    public function getImage( $image, $namespace='' ) {
        global $_CONF;

        if ( $namespace == 'core' || $namespace == '' ) {
            if ( @file_exists($_CONF['path_themes'] . '/'. $_CONF['theme'] . '/images/' . $image) ) {
                return $_CONF['site_url'] . '/layout/' .$_CONF['theme'] . '/images/' . $image;
            }
            if ( @file_exists($_CONF['path_themes'] . '/nouveau/images/' . $image) ) {
                return $_CONF['site_url'] . '/layout/nouveau/images/' . $image;
            }
            if ( @file_exists($_CONF['path_html'] . '/images/' . $image ) ) {
                return $_CONF['site_url'] . '/images/' . $image;
            }
        } else {
            // see if theme overrides
            if ( @file_exists($_CONF['path_themes'] . '/'.$_CONF['theme'].'/'.$namespace.'/images/'.$image) ) {
                return $_CONF['site_url'] . '/layout/'.$_CONF['theme'].'/'.$namespace.'/images/'.$image;
            }
            // now check the plugin
            if ( @file_exists($_CONF['path_html'] . '/'.$namespace.'/images/'.$image) ) {
                return $_CONF['site_url'] . '/'.$namespace.'/images/'.$image;
            }
            // now check the admin area...
            if ( @file_exists($_CONF['path_html'] . '/admin/plugins/'.$namespace.'/images/'.$image) ) {
                return $_CONF['site_admin_url'] . '/plugins/'.$namespace.'/images/'.$image;
            }
        }
        return '';
    }

    /*
     * renders and displays the full HTML output to the browser
     */
    public function displayPage() {
        global $_CONF, $_TABLES, $_USER, $LANG01, $LANG_DIRECTION,$_PAGE_TIMER,
               $_ST_CONF, $LANG12;

        $this->pageTemplate->set_file( array(
            'page'        => 'page.thtml',
        ));

        $this->_frontPage = COM_onFrontpage();
        COM_hit();

        // get any buffered output
        $content = ob_get_contents();
        $this->_buffer .= $content;
        ob_end_clean();

        /*
         * Send direct headers
         */
        foreach ($this->_directHeader AS $header ) {
            header($header);
        }

        $this->_generateHeader();
        $this->_generateBlockNavigation();
        $this->_generateExtraNavigation();
        $this->_generateFooter();

        $this->pageTemplate->set_var(array(
            'meta_data' => $this->_renderHeader('meta'),
            'css_links' => $this->_renderHeader('style'),
            'js_links'  => $this->_renderHeader('script'),
            'rel_links' => $this->_renderHeader('raw'),
        ));

        $this->pageTemplate->set_var(array(
            'site_name'         => $_CONF['site_name'],
            'background_image'  => $this->getImage('bg.png'),
            'site_mail'         => "mailto:{$_CONF['site_mail']}",
            'site_slogan'       => $_CONF['site_slogan'],
            'datetime'          => $this->_curtime[0],
            'theme'             => $_CONF['theme'],
            'charset'           => $this->_charset,
            'plg_headercode'    => $this->_headercode . PLG_getHeaderCode(),
            'num_search_results'=> $_CONF['num_search_results'],
            'lang_login'        => $LANG01[58],
            'lang_myaccount'    => $LANG01[48],
            'lang_logout'       => $LANG01[35],
            'lang_newuser'      => $LANG12[3],
            'copyright_notice'  => $LANG01[93] . ' &copy; '
                                    . $this->_copyrightyear . ' ' . $_CONF['site_name'] . '&nbsp;&nbsp;&bull;&nbsp;&nbsp;'
                                    . $LANG01[94],
            'copyright_msg'     => $LANG01[93] . ' &copy; '
                                    . $this->_copyrightyear . ' ' . $_CONF['site_name'],
            'lang_copyright'    => $LANG01[93],
            'trademark_msg'     => $LANG01[94],
            'powered_by'        => $LANG01[95],
            'glfusion_url'      => 'http://www.glfusion.org/',
            'glfusion_version'  => GVERSION,
        ));

        $exectime = $_PAGE_TIMER->stopTimer();
        $exectext = $LANG01[91] . ' ' . $exectime . ' ' . $LANG01[92];

        $this->pageTemplate->set_var( 'execution_time', $exectime );
        $this->pageTemplate->set_var( 'execution_textandtime', $exectext );

// builds the content

        $newcontent = ($this->_msg == '' ? '' : $this->_renderMessage()) . $this->_buffer;
        $this->pageTemplate->set_var('content',$newcontent);

// final call to let plugins put stuff in places

        // Call to plugins to set template variables
        PLG_templateSetVars( 'header', $this->pageTemplate );
        PLG_templateSetVars( 'footer', $this->pageTemplate );

// display it!

        echo $this->pageTemplate->finish($this->pageTemplate->parse('output', 'page'));
        exit;
    }

    // URL Rewriting support


    /**
    * Enables url rewriting, otherwise URL's are passed back
    *
    * @param        boolean     $switch     turns URL rewriting on/off
    *
    */
    function setRewriteEnabled($switch)
    {
        if ($switch) {
            $this->_rewriteEnabled = true;
        } else {
            $this->_rewriteEnabled = false;
        }
    }

    /**
    * Returns whether or not URL rewriting is enabled
    *
    * @return   boolean true if URl rewriting is enabled, otherwise false
    *
    */
    function isRewriteEnabled()
    {
        return $this->_rewriteEnabled;
    }


    /**
    * Builds crawler friendly URL if URL rewriting is enabled
    *
    * This function will attempt to build a crawler friendly URL.  If this feature is
    * disabled because of platform issue it just returns original $url value
    *
    * @param        string      $url    URL to try and convert
    * @return       string      rewritten if _isenabled is true otherwise original url
    *
    */
    function buildURL($url)
    {
        if (!$this->isRewriteEnabled()) {
            return $url;
        }

        $pos = strpos($url,'?');
        $query_string = substr($url,$pos+1);
        $finalList = array();
        $paramList = explode('&',$query_string);
        for ($i = 1; $i <= count($paramList); $i++) {
            $keyValuePairs = explode('=',current($paramList));
            if (is_array($keyValuePairs)) {
                $argName = current($keyValuePairs);
                next($keyValuePairs);
                $finalList[$argName] = current($keyValuePairs);
            }
            next($paramList);
        }
        $newArgs = '/';
        for ($i = 1; $i <= count($finalList); $i++) {
            $newArgs .= current($finalList);
            if ($i <> count($finalList)) {
                $newArgs .= '/';
            }
            next($finalList);
        }
        return str_replace('?' . $query_string,$newArgs,$url);
    }

    private function _generateHeader()
    {
        global $_CONF, $_TABLES, $_USER, $LANG01, $LANG_DIRECTION,$_PAGE_TIMER,
               $_ST_CONF, $LANG12, $inputHandler;

        $cacheID = DB_getItem($_TABLES['vars'],'value','name="cacheid"');
        $this->pageTemplate->set_var('cacheid',$_CONF['theme'].$cacheID);

        $topic = $inputHandler->getVar('strict','topic','get','');
        $story = $inputHandler->getVar('strict','story','get','');
        $sid   = $inputHandler->getVar('strict','sid','get','');

        // get topic if not on home page
        if( empty($topic) ) {
            if( empty( $sid ) && $_CONF['url_rewrite'] &&
                    ( strpos( $_SERVER['PHP_SELF'], 'article.php' ) !== false )) {
                COM_setArgNames( array( 'story', 'mode' ));
                $sid = COM_applyFilter( COM_getArgument( 'story' ));
            }
            if( !empty( $sid )) {
                $this->_topic = DB_getItem( $_TABLES['stories'], 'tid', "sid='$sid'" );
            }
        } else {
            $this->_topic = $topic;
        }

        if( $_CONF['backend'] == 1 ) { // add feed-link to header if applicable
            $baseurl = SYND_getFeedUrl();

            $sql = 'SELECT format, filename, title, language FROM '
                 . $_TABLES['syndication'] . " WHERE (header_tid = 'all')";
            if( !empty( $this->_topic )) {
                $sql .= " OR (header_tid = '" . addslashes( $this->_topic ) . "')";
            }
            $result = DB_query( $sql );
            $numRows = DB_numRows( $result );
            for( $i = 0; $i < $numRows; $i++ ) {
                $A = DB_fetcharray( $result );
                if ( !empty( $A['filename'] )) {
                    $format = explode( '-', $A['format'] );
                    $format_type = strtolower( $format[0] );
                    $format_name = ucwords( $format[0] );
                    $this->addLink('alternate',$baseurl . $A['filename'],'application/'. $format_type . '+xml',HEADER_PRIO_VERYHIGH,array('hreflang'=>$A['language'],'title'=>$format_name . ' Feed: ' . $A['title']));
                }
            }
        }
        if( $this->_frontPage ) {
            $this->addLink('home',$_CONF['site_url'],'',HEADER_PRIO_VERYHIGH,array('title'=>$LANG01[90]));
        } else {
            CMT_updateCommentcodes();
        }
        $mode = $inputHandler->getVar('strict','mode','get','');
        $loggedInUser = !COM_isAnonUser();
        if( $loggedInUser || (( $_CONF['loginrequired'] == 0 ) &&
                    ( $_CONF['searchloginrequired'] == 0 ))) {
            if(( substr( $_SERVER['PHP_SELF'], -strlen( '/search.php' ))
                    != '/search.php' ) || !empty($mode)) {
                $this->addLink('search',$_CONF['site_url'] . '/search.php','',HEADER_PRIO_VERYHIGH,array('title'=>$LANG01[75]));
            }
        }
        if( $loggedInUser || (( $_CONF['loginrequired'] == 0 ) &&
                    ( $_CONF['directoryloginrequired'] == 0 ))) {
            if( strpos( $_SERVER['PHP_SELF'], '/article.php' ) !== false ) {
                $this->addLink('contents',$_CONF['site_url'] . '/directory.php','',HEADER_PRIO_VERYHIGH,array('title'=>$LANG01[117]));
            }
        }
        if (!$_CONF['disable_webservices']) {
            $this->addLink('service',$_CONF['site_url'] . '/webservices/atom/?introspection','application/atomsvc+xml',HEADER_PRIO_VERYHIGH,array('title'=>$LANG01[130]));
        }

        /*
         * Page title
         */

        if( empty( $this->_pagetitle ) && isset($_CONF['pagetitle']) && $_CONF['pagetitle'] != '') {
            $this->_pagetitle = $_CONF['pagetitle'];
        }
        if( empty( $this->_pagetitle )) {
            if( empty( $this->_topic )) {
                $this->_pagetitle = $_CONF['site_slogan'];
            } else {
                $this->_pagetitle = stripslashes(DB_getItem( $_TABLES['topics'], 'topic',
                                                       "tid = '$this->_topic'" ));
            }
        }
        if( !empty( $this->_pagetitle )) {
            $this->pageTemplate->set_var( 'page_site_splitter', ' - ');
        } else {
            $this->pageTemplate->set_var( 'page_site_splitter', '');
        }
        $this->pageTemplate->set_var( 'page_title', $this->_pagetitle );

        if ($this->_frontPage) {
            $title_and_name = $_CONF['site_name'];
            if (!empty($this->_pagetitle)) {
                $title_and_name .= ' - ' . $this->_pagetitle;
            }
        } else {
            $title_and_name = '';
            if (!empty($this->_pagetitle)) {
                $title_and_name = $this->_pagetitle . ' - ';
            }
            $title_and_name .= $_CONF['site_name'];
        }
        $this->pageTemplate->set_var('page_title_and_site_name', $title_and_name);

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
        $this->pageTemplate->set_var( 'lang_id', $langId );
        if (!empty($_CONF['languages']) && !empty($_CONF['language_files'])) {
            $this->pageTemplate->set_var('lang_attribute', $langAttr);
        } else {
            $this->pageTemplate->set_var('lang_attribute', '');
        }

        $msg = $LANG01[67] . ' ' . $_CONF['site_name'];

        if( !empty( $_USER['username'] )) {
            $msg .= ', ' . COM_getDisplayName( $_USER['uid'], $_USER['username'],
                                               $_USER['fullname'] );
        }
        if( empty( $LANG_DIRECTION )) {
            // default to left-to-right
            $this->pageTemplate->set_var( 'direction', 'ltr' );
        } else {
            $this->pageTemplate->set_var( 'direction', $LANG_DIRECTION );
        }

        if ( $_ST_CONF['use_graphic_logo'] == 1 && file_exists($_CONF['path_html'] . '/images/' . $_ST_CONF['logo_name']) ) {
            $L = new Template( $_CONF['path_layout'] );
            $L->set_file( array(
                'logo'          => 'logo-graphic.thtml',
            ));

            $imgInfo = @getimagesize($_CONF['path_html'] . '/images/' . $_ST_CONF['logo_name']);
            $dimension = $imgInfo[3];

            $L->set_var( 'xhtml', XHTML);
            $L->set_var( 'site_url', $_CONF['site_url'] );
            $L->set_var( 'layout_url', $_CONF['layout_url'] );
            $L->set_var( 'site_name', $_CONF['site_name'] );
            $site_logo = $_CONF['site_url'] . '/images/' . $_ST_CONF['logo_name'];
            $L->set_var( 'site_logo', $site_logo);
            $L->set_var( 'dimension', $dimension );
            if ( $imgInfo[1] != 100 ) {
                $delta = 100 - $imgInfo[1];
                $newMargin = $delta;
                $L->set_var( 'delta', 'style="padding-top:' . $newMargin . 'px;"');
            } else {
                $L->set_var('delta','');
            }
            if ($_ST_CONF['display_site_slogan']) {
                $L->set_var( 'site_slogan', $_CONF['site_slogan'] );
            }
            $L->parse('output','logo');
            $this->pageTemplate->set_var('logo_block',$L->finish($L->get_var('output')));
        } else {
            $L = new Template( $_CONF['path_layout'] );
            $L->set_file( array(
                'logo'          => 'logo-text.thtml',
            ));
            $L->set_var( 'xhtml',XHTML);
            $L->set_var( 'site_name', $_CONF['site_name'] );
            $L->set_var( 'site_url', $_CONF['site_url'] );
            $L->set_var( 'layout_url', $_CONF['layout_url'] );
            if ($_ST_CONF['display_site_slogan']) {
                $L->set_var( 'site_slogan', $_CONF['site_slogan'] );
            }
            $L->parse('output','logo');
            $this->pageTemplate->set_var('logo_block',$L->finish($L->get_var('output')));
        }

        // build the site tailor menus
        if ( function_exists('st_getMenu') ) {
            $this->pageTemplate->set_var('st_hmenu',st_getMenu('navigation',"gl_moomenu","gl_moomenu",'',"parent"));
            $this->pageTemplate->set_var('st_footer_menu',st_getMenu('footer','st-fmenu','','','','st-f-last'));
            $this->pageTemplate->set_var('st_header_menu',st_getMenu('header','st-fmenu','','','','st-f-last'));
        }
        $this->pageTemplate->set_var('welcome_msg',$msg);
    }

    private function _generateBlockNavigation()
    {
        $this->_navigationBlocks = '';

        /* Check if an array has been passed that includes the name of a plugin
         * function or custom function
         * This can be used to take control over what blocks are then displayed
         */
        if( is_array( $this->_what ) && count($this->_what) > 0) {
            $function = $this->_what[0];
            if( function_exists( $function )) {
                $this->_navigationBlocks = $function( $this->_what[1], 'left' );
            } else {
                $this->_navigationBlocks = COM_showBlocks( 'left', $this->_topic );
            }
        } else if( $this->_displayNavigationBlocks == true) {
            // Now show any blocks -- need to get the topic if not on home page
            $this->_navigationBlocks = COM_showBlocks( 'left', $this->_topic );
        }

        if( empty( $this->_navigationBlocks )) {
            $this->_displayNavigationBlocks = false;  // set to false since we don't have any
            $this->pageTemplate->set_var( 'glfusion_blocks', '' );
        } else {
            $this->pageTemplate->set_var( 'glfusion_blocks', $this->_navigationBlocks );
        }
    }

    private function _generateExtraNavigation()
    {
        /* Check if an array has been passed that includes the name of a plugin
         * function or custom function.
         * This can be used to take control over what blocks are then displayed
         */
        if( is_array( $this->_custom ) && count($this->_custom) > 0) {
            $function = $this->_custom['0'];
            if( function_exists( $function )) {
                $this->_extraBlocks = $function( $this->_custom['1'], 'right' );
            }
        } elseif( $this->_displayExtraBlocks == true ) {
            $this->_extraBlocks = '';

            $this->_extraBlocks = COM_showBlocks( 'right', $this->_topic );

            if( empty( $this->_extraBlocks )) {
                $this->_displayExtraBlocks = false;  // set to false since we don't have them

                $this->pageTemplate->set_var( 'glfusion_rblocks', '');
                $this->pageTemplate->set_var( 'right_blocks','');
                if ( empty($this->_navigationBlocks) ) {
                    $this->pageTemplate->set_var( 'centercolumn','gl_content-full' );
                } else {
                    $this->pageTemplate->set_var( 'centercolumn','gl_content-wide-left' );
                }
            } else {
                $this->pageTemplate->set_var( 'glfusion_rblocks', $this->_extraBlocks);
                if ( empty($this->_navigationBlocks) ) {
                    $this->pageTemplate->set_var( 'centercolumn','gl_content-wide-right' );
                } else {
                    $this->pageTemplate->set_var( 'centercolumn','gl_content' );
                }
            }
        } else {
            $this->pageTemplate->set_var( 'glfusion_rblocks', '');
            $this->pageTemplate->set_var( 'right_blocks', '' );
            if( empty( $this->_navigationBlocks )) {
                $this->pageTemplate->set_var( 'centercolumn','gl_content-full' );
            } else {
                $this->pageTemplate->set_var( 'centercolumn','gl_content-wide-left' );
            }
        }
    }

    private function _generateFooter()
    {
        global $LANG01,$_CONF;

        $year = date( 'Y' );
        $this->_copyrightyear = $year;
        if( isset($_CONF['copyrightyear']) && $_CONF['copyrightyear'] != '' ) {
            $this->_copyrightyear = $_CONF['copyrightyear'];
        }
        $rdf = substr_replace( $_CONF['rdf_file'], $_CONF['site_url'], 0, strlen( $_CONF['path_html'] ) - 1 ) . LB;
        $msg = $LANG01[67] . ' ' . $_CONF['site_name'];
        $this->pageTemplate->set_var('rss_url',$rdf);

    }

    // PRIVATE METHODS

    /**
    * Concatenates an array - recursively
    *
    * @access   private
    * @param    array  $a     array
    *
    */

    private function _array_concat_recursive($a)
    {
        if (is_array($a)) {
            $cat = '';
            foreach ($a as $aa) {
                if (is_array($aa)) {
                    $cat .= $this->_array_concat_recursive($aa);
                } elseif (!is_null($aa)) {
                    $cat .= $aa;
                }
            }
            return $cat;
        } else {
            return false;
        }
    }


    /**
    * Logs messages
    *
    * Logs messages by calling the function held in $_errorlog_fn
    *
    * @param    string      $msg        Message to log
    * @access   private
    */
    private function _errorlog($msg)
    {
        $function = $this->_errorlog_fn;
        if (function_exists($function)) {
            $function($msg);
        }
    }

    private function _renderHeader($type)
    {
        if ( $type != 'script' && $type != 'style' && $type != 'raw' && $type != 'meta' ) {
            return '';
        }
        return $this->_array_concat_recursive($this->_header[$type]);
    }

    private function _renderMessage() {
        global $_CONF, $MESSAGE;

        $timestamp = strftime($_CONF['daytime']);
        $retval = COM_startBlock($MESSAGE[40] . ' - ' . $timestamp, '',
                                  COM_getBlockTemplate('_msg_block', 'header'))
                . '<p class="sysmessage"><img src="' . $this->getImage('sysmessage.png')
                . '" alt="" ' . XHTML
                . '>' . $this->_msg . '</p>'
                . COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'));
        return $retval;
    }
}
?>