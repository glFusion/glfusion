<?php
/**
* glFusion CMS
*
* glFusion Output Handler
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*/

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
 * @since 		1.3.0
 */

class outputHandler {

    public  $pageTemplate;
    private $_buffer = '';
    private $_msg = '';
    private $_displayNavigationBlocks = true;
    private $_displayExtraBlocks = false;
    private $_pagetitle = '';
    private $_frontPage = false;
    private $_errorlog_fn = NULL;
    private $_what = '';
    private $_custom = array();
    private $_charset = 'utf-8';
    private $_topic = '';
    private $_headercode = '';
    private $_navigationBlocks = '';
    private $_extraBlocks = '';
    private $_curtime = '';
    private $_copyrightyear;
    private $_directHeader = array();
    private $_rewriteEnabled = false;
    private $priorities = array(HEADER_PRIO_VERYHIGH, HEADER_PRIO_HIGH, HEADER_PRIO_NORMAL, HEADER_PRIO_LOW, HEADER_PRIO_VERYLOW);
    private static $norepeat = array(
        'meta' => array('keywords', 'description', 'author'),
    );
    private $_header = array(
                    'meta' => array('http-equiv' => array(), 'name' => array()),
                    'style' => array(HEADER_PRIO_VERYHIGH => array(),
                                     HEADER_PRIO_HIGH => array(),
                                     HEADER_PRIO_NORMAL => array(),
                                     HEADER_PRIO_LOW => array(),
                                     HEADER_PRIO_VERYLOW => array()),
                    'css_file' => array(HEADER_PRIO_VERYHIGH => array(),
                                     HEADER_PRIO_HIGH => array(),
                                     HEADER_PRIO_NORMAL => array(),
                                     HEADER_PRIO_LOW => array(),
                                     HEADER_PRIO_VERYLOW => array()),
                    'script' => array(HEADER_PRIO_VERYHIGH => array(),
                                     HEADER_PRIO_HIGH => array(),
                                     HEADER_PRIO_NORMAL => array(),
                                     HEADER_PRIO_LOW => array(),
                                     HEADER_PRIO_VERYLOW => array()),
                    'script_file' => array(HEADER_PRIO_VERYHIGH => array(),
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

    private $_footer = array(
                    'script' => array(HEADER_PRIO_VERYHIGH => array(),
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
        $this->_charset             = COM_getCharset();
        $this->pageTemplate         = new Template ($_CONF['path_layout']);
        $this->_rewriteEnabled      = $_CONF['url_rewrite'];
        $this->_displayExtraBlocks  = $_CONF['show_right_blocks'];
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
	 * @since	1.3.0
	 */
    public static function &getInstance()
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
            $this->_header['style'][$priority][] = '<style>' . LB . $code . LB . "</style>" . LB;
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
            $this->_header['script'][$priority][] = '<script>'.LB."<!--" . LB . $code . LB ."// --></script>" . LB;
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
        $link = '<link rel="' . $rel .'" href="' . @htmlspecialchars($href,ENT_QUOTES, COM_getEncodingt()) . '"';
        if (!empty($type)) {
            $link .= ' type="' . $type . '"';
        }
        if (is_array($attrs)) {
            foreach ($attrs as $k => $v) {
                $link .= ' ' . $k . '="' . $v . '"';
            }
        }
        $link .= "/>" . LB;

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
        $link = '<link rel="stylesheet" type="' . $mime . '" href="' . @htmlspecialchars($href,ENT_QUOTES, COM_getEncodingt()) . '"';
        if (is_array($attrs)) {
            foreach ($attrs as $k => $v) {
                $link .= ' ' . $k . '="' . $v . '"';
            }
        }
        $link .= "/>" . LB;

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
	 * @param  boolean  $async      true - load script asynchronously
     *
	 * @access public
	 * @return nothing
	 */
    public function addLinkScript($href, $priority = HEADER_PRIO_NORMAL, $mime = 'text/javascript',$async = false)
    {
        $link = '<script src="' . @htmlspecialchars($href,ENT_QUOTES, COM_getEncodingt()) . '"';
        if ( $async ) $link .= ' async';
        $link .= "></script>" . LB;

        $this->_header['script'][$priority][] = $link;
    }

	/**
	 * Add a JavaScript source to a page
	 *
	 * This adds a javascript source file to a page - Physical path to the JS file
	 * the <link> attribute.
	 *
	 * @param  string   $href       The URL to the javascript file
	 * @param  int      $priority   Load priority
     *
	 * @access public
	 * @return nothing
	 */
    public function addScriptFile($href, $priority = HEADER_PRIO_NORMAL)
    {
        $this->_header['script_file'][$priority][] = $href;
    }

	/**
	 * Add a CSS source to a page
	 *
	 * This adds a CSS source file to a page - Physical path to the CSS file
	 * the <link> attribute.
	 *
	 * @param  string   $href       The URL to the CSS file
	 * @param  int      $priority   Load priority
     *
	 * @access public
	 * @return nothing
	 */
    public function addCSSFile($href, $priority = HEADER_PRIO_NORMAL)
    {
        $this->_header['css_file'][$priority][] = $href;
    }

	/**
	 * Add Meta data to header
	 *
	 * This adds a meta record to the header
	 *
	 * @param  string   $tpe        Meta data type
	 * @param  string   $name       Name of the meta attribute
	 * @param  string   $content    Content of the attribute
     *
	 * @access public
	 * @return nothing
	 */

    public function addMeta($type, $name, $content, $priority = HEADER_PRIO_VERYLOW)
    {
        if ( trim($content) == '' ) return;

        //$this->_header['meta'][] = $link = '<meta '.$type.'="' .  $name . '" content="' . $content . '"/>' . LB;
        // This blocks any duplicate "<meta name=..." tags, as well as any in
        // the $norepeat array
        if (    ($type == 'name' || in_array($name, self::$norepeat['meta'])) &&
                isset($this->_header['meta'][$type][$name]) &&
                $this->_header['meta'][$type][$name]['priority'] <= $priority
        ) {
            return;
        }
        $this->_header['meta'][$type][$name] = array(
            'content' => @htmlspecialchars($content,ENT_QUOTES,COM_getEncodingt(),false),
            'priority' => $priority,
        );
    }


	/**
	 * Add a JavaScript source footer section of page
	 *
	 * This adds a javascript source file to a page - The URL should not have
	 * the <link> attribute.
	 *
	 * @param  string   $href       The URL to the javascript file
	 * @param  int      $priority   Load priority
	 * @param  string   $mime       The mime type of the stylesheet, 'text/css'
	 *                              used if no other type passed.
	 * @param  boolean  $async      true - load script asynchronously
     *
	 * @access public
	 * @return nothing
	 */
    public function addLinkScriptFooter($href, $priority = HEADER_PRIO_NORMAL, $mime = 'text/javascript',$async = false)
    {
        $link = '<script src="' . @htmlspecialchars($href,ENT_QUOTES, COM_getEncodingt()) . '"';
        if ( $async ) $link .= ' async';
        $link .= "></script>" . LB;

        $this->_footer['script'][$priority][] = $link;
    }

	/**
	 * Add JavaScript to footer section of page
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
    public function addScriptFooter($code, $priority = HEADER_PRIO_NORMAL, $mime = 'text/javascript')
    {
        if ($code != '') {
            $this->_footer['script'][$priority][] = '<script>'.LB."" . LB . $code . LB ."</script>" . LB;
        }
    }

    public function renderFooter($type)
    {
        switch ($type) {
        case 'script':
            return $this->_array_concat_recursive($this->_footer[$type]);
            break;
        default:
            return '';
        }
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
//REMOVE
    public function addMetaName($name, $content)
    {
        $this->_header['meta']['name'] = '<meta name="' .  $name . '" content="' . $content . '"/>' . LB;
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
// REMOVE
    public function addMetaHttpEquiv($header, $content)
    {
        $this->_header['meta']['http-equiv'] = '<meta http-equiv="' . $header .'" content="' . $content . '" />' . LB;
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

    public function renderHeader($type)
    {
        //if ( $type != 'script' && $type != 'style' && $type != 'raw' && $type != 'meta' ) {
        //    return '';
        //}
        //return $this->_array_concat_recursive($this->_header[$type]);
        switch ($type) {
        case 'script':
        case 'style':
        case 'raw':
            return $this->_array_concat_recursive($this->_header[$type]);
        case 'meta':
            return $this->_renderMeta();
        default:
            return '';
        }
    }

    private function _renderMeta()
    {
        $ret = '';
        foreach ($this->_header['meta'] as $type=>$data) {
            foreach ($data as $name=>$values) {
                $ret .= '<meta '. $type . '="' . $name . '" content="' . $values['content'] .
                '" />' . LB;
            }
        }
        return $ret;
    }

	/**
	 * Returns array of JS files
	 *
	 * Returns an array of JavaScript file names (with paths) to
	 * all queued JS files
	 *
	 * @access public
	 * @return array
	 */
    public function getCSSFiles()
    {
        $css = array();

        foreach ($this->_header['css_file'] as $priority ) {
            if ( is_array($priority) ) {
                foreach ($priority as $path ) {
                    $css[] = $path;
                }
            }
        }
        return $css;
    }

	/**
	 * Returns array of JS files
	 *
	 * Returns an array of JavaScript file names (with paths) to
	 * all queued JS files
	 *
	 * @access public
	 * @return array
	 */
    public function getScriptFiles()
    {
        $js = array();

        foreach ($this->_header['script_file'] as $priority ) {
            if ( is_array($priority) ) {
                foreach ($priority as $path ) {
                    $js[] = $path;
                }
            }
        }
        return $js;
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
}
?>