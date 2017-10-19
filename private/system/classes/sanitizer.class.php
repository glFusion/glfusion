<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | sanitizer.cass.php                                                       |
// |                                                                          |
// | glFusion HTML / Text Filter                                              |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2014-2017 by the following authors:                        |
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

//namespace glFusion;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/*
 * the following are the configuration vars
 *
 * htmlfilter_default - default set of elements to use if no other is defined
 * htmlfilter_comment - HTML elements for comments
 * htmlfilter_story   - HTML elements for stories
 * htmlfilter_root    - HTML elements that applys to anything a Root user enters
 *
 * $_SYSTEM['html_filter'] defines which html filter to use. Defaults to
 *                         HTMLPurifier is the default - other option is
 *                         HtmLawed.
 *
 *
 * This class contains serveral filtering and sanitization routines.
 * Plugins should provide the valid set of allowed HTML elements and
 * not rely on the very restrictive default set of elements.
 *
 */

class sanitizer
{
    var $_postmode       = 'text';  // Post mode
    var $allowedElements = 'p,b,a,i,strong,em';   // default allowed HTML elements - very restrictive
    var $schemas         = 'http:,https:,ftp:';   // default schemas
    var $encoding        = 'utf-8'; // character encoding
    var $namespace       = '';      // who is calling.. used for replacetags...
    var $operation       = '';      // operations being performed - i.e.; story, media_description
    var $replaceTags     = true;    // do we or don't we replace auto tags
    var $_censorData     = true;    // do we or don't we censor data
    var $filterMethod    = 'htmlpurifier'; // or htmlawed

    var $query = '';

    public function __construct( )
    {
        global $_SYSTEM;

        $this->encoding = COM_getEncodingt();
        if ( isset($_SYSTEM['html_filter']) && $_SYSTEM['html_filter'] == 'htmlawed' ) {
            $this->setFilterMethod('htmlawed');
        }
        if ( isset($_CONF['htmlfilter_default']) ) {
            $this->setAllowedElements($_CONF['htmlfilter_default']);
        }
    }


    public static function getInstance(  )
    {
        static $instance;

        if (!isset($instance) ) {
            $instance = new sanitizer();
        }
        return $instance;
    }

    /**
     * Set the post mode - determines type of filtering to perform
     *
     * Set the postmode type - this is used to determine how the HTML
     * filter and other text rendering functions act based on text type
     * Either html or text
     *
     * @param   string  $mode   post mode - either html or text
     * @return  none
     * @access  public
     *
     */
    public function setPostmode ( $mode )
    {
        $mode = strtolower($mode);
        if ( in_array($mode,array('text','html'))) {
            $this->_postmode = $mode;
        } else {
            $this->_postmode = 'text';
        }
    }

    /**
     * Build allowed HTML elements list
     *
     * Combines the current HTML allowed elements with the root
     * allowed elemnets (if set).  This function builds the comma
     * delimited list which can be consumed by ->setAllowedElements()
     *
     * @param   string  $elements   Comma delimited string of allowed HTML elements
     * @return  string              Comma delimited string of all allowed HTML
     *                              elements for this operations
     * @access  public
     *
     */
    public function makeAllowedElements( $elements )
    {
        global $_CONF;

        $elementArray = array();
        $root         = array();
        $itemElements = explode(',',$elements);
        $elementArray = array_merge($elementArray,$itemElements);
        if ( SEC_inGroup('Root') ) {
            $root    = explode(',',$_CONF['htmlfilter_root']);
            $elementArray = array_merge($elementArray,$root);
        }
        $filterArray = array_unique($elementArray);
        $fullelementlist = implode(',',$filterArray);
        return $fullelementlist;
    }



    /**
     * Define allowed HTML elements
     *
     * @param   string  $elements     Comma delimited string of allowed HTML elements
     * @return  none
     * @access  public
     *
     */
    public function setAllowedElements( $elements )
    {
        $this->allowedElements = $elements;
    }


    /**
     * Set namespace - this defines what area / operation is being
     * perfomred - for example, glFusion is the area, comment is the operation
     * Used to determine autotag replacements.
     *
     * @param   string  $namespace  namespace i.e.; glfusion, mediagallery, forum
     * @return  none
     * @access  public
     *
     */
    public function setNamespace( $namespace, $operation )
    {
        $this->namespace = $namespace;
        $this->operation = $operation;
    }

    public function setOperation( $operation )
    {
        $this->operation = $operation;
    }

    public function setReplaceTags( $option )
    {
        if ( $option ) {
            $this->replaceTags = true;
        } else {
            $this->replaceTags = false;
        }
    }

    public function setCensorData( $option )
    {
        if ( $option ) {
            $this->_censorData = true;
        } else {
            $this->_censorData = false;
        }
    }

    public function setFilterMethod( $method )
    {
        if ( strtolower($method) == 'htmlawed' ) {
            $this->filterMethod = 'htmlawed';
        } else {
            $this->filterMethod = 'htmlpurifier';
        }
    }


    /**
     * Filter data - will filter HTML or text data based on the
     * current _postmode setting.  This will return filtered HTML
     * data where only whitelisted elements are included and
     * malicious HTML removed.  Or, if postmost == text, it will
     * return htmlspecialchar() text data.
     *
     * NOTE: Code blocks, i.e.; [code][/code] are not procssed
     * by this function - they are not filtered or modified.
     *
     * @param   string  $str    Data to be filtered
     * @return  string          Filtered data.
     * @access  public
     *
     */
    public function filterData( $str )
    {
        if ($this->_postmode == 'html' ) {
            return $this->filterHTML($str);
        } else {
            return $this->filterText($str);
        }
    }

    public function filterHTML( $str )
    {
        global $_CONF;

        $sp = new StringParser_BBCode ();
        $sp->setGlobalCaseSensitive (false);

        if ( $this->_postmode == 'html' ) {
            if ( $this->filterMethod == 'htmlawed' ) {
                $sp->addParser(array('block'), array($this,'_cleanHTMLawed'));
            } else {
                $sp->addParser(array('block'), array($this,'_cleanHTML'));
            }
        }
        $sp->addCode ('code', 'usecontent?', array($this,'_codeblockFilter'), array ('usecontent_param' => 'default'),
                      'code', array('listitem', 'block', 'inline', 'quote'), array ('link'));

        $str = $sp->parse ($str);

        return $str;
    }

    public function filterText( $str )
    {
        global $_CONF;

        $sp = new StringParser_BBCode ();
        $sp->setGlobalCaseSensitive (false);

        if ( $this->_postmode != 'html' ) {
            $sp->addParser(array('block'), array($this,'htmlspecialchars'));
        }
        $sp->addCode ('code', 'usecontent?', array($this,'_codeblockFilter'), array ('usecontent_param' => 'default'),
                      'code', array('listitem', 'block', 'inline', 'quote'), array ('link'));

        $str = $sp->parse ($str);

        return $str;
    }

    /**
     * Return text ready to be used in an input field.
     *
     * For HTML data - it is assumed that filterHTML has already
     * been run on the data.
     *
     * NOTE: Code blocks, i.e.; [code][/code] do not receive any
     *       special treatment here - all data is run through
     *       htmlspecialchars();
     *
     * @param   string  $str    Data to be filtered
     * @return  string          Filtered data.
     * @access  public
     *
     */
    public function editableText($str)
    {
        if ( $this->_postmode == 'html' ) {
            // html filter escapes several items...
//            $str = htmlspecialchars_decode($str,ENT_NOQUOTES);
        }
        return $this->htmlspecialchars($str,ENT_NOQUOTES,$this->encoding);
    }

    /*
     * prepares text for display
     * does not filter malicious HTML - assumed this has been done elsewhere
     */
    public function displayText( $str )
    {
        $sp = new StringParser_BBCode ();
        $sp->setGlobalCaseSensitive (false);

        if ( $this->_postmode != 'html') {
            $sp->addParser (array ('block', 'inline'), array($this,'htmlspecialchars'));
            $sp->addParser(array('block','inline'), 'nl2br');
            $sp->addParser(array('block','inline'), array($this,'linkify'));
        }

        $sp->addCode ('code', 'usecontent', array($this,'_codeblock'), array ('usecontent_param' => 'default'),
                      'code', array ('block'), array ());

        if ( $this->replaceTags ) {
            $sp->addParser(array('block'), array($this,'_replacetags'));
        }

        if ( $this->_censorData ) {
            $str = $this->censor($str);
        }
        $str = $sp->parse ($str);

        return $str;
    }

    /*
     * replace glFusion autotags with final form data
     */
    public function _replaceTags($text) {
        return PLG_replaceTags($text,$this->namespace,$this->operation);
    }

    public function htmlspecialchars($text)
    {
        return (@htmlspecialchars ($text,ENT_NOQUOTES, $this->encoding));
    }

    /*
     * Filter HTML input to remove malicious or un-wanted HTML tags
     */
    public function _cleanHTML( $str )
    {
        global $_CONF,$_SYSTEM;

        if ( !isset($_CONF['debug_html_filter']) ) $_CONF['debug_html_filter'] = false;

        if ( isset( $_CONF['skip_html_filter_for_root'] ) && ( $_CONF['skip_html_filter_for_root'] == 1 ) && SEC_inGroup( 'Root' )) {
            if ( $_CONF['debug_html_filter'] == true ) COM_errorLog("HTMLFILTER: Skipped for root user");
            return $str;
        }
        $configArray = explode(',',$this->allowedElements);
        $filterArray = array_unique($configArray);
        foreach($filterArray as $element) {
            $final[$element] = true;
        }
        $configFilter = implode(",",$filterArray);

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Attr.AllowedFrameTargets', array('_blank'));
        $config->set('HTML.Allowed', $configFilter);
        $config->set('Core.Encoding',$this->encoding);
        $config->set('AutoFormat.Linkify',false);
        $config->set('HTML.SafeObject',true);
        $config->set('Output.FlashCompat',true);
        $config->set('Cache.SerializerPath',$_CONF['path_data'].'htmlpurifier');

        if ( $_CONF['debug_html_filter'] == true ) $config->set('Core.CollectErrors',true);
        $purifier = new HTMLPurifier($config);

        $clean_html = $purifier->purify($str);

        if (  $_CONF['debug_html_filter'] == true  ) {
            $e = $purifier->context->get('ErrorCollector');
            $errArray = $e->getRaw();
            if (is_array($errArray)) {
                foreach ($errArray as $error) {
                    if ( $error[1] == 1 ) {
                        COM_errorLog("HTMLFILTER: " .  $error[2]);
                    }
                }
            }
        }
        return $clean_html;
    }

    function _cleanHTMLawed( $str )
    {
        global $_CONF;

        if( isset( $_CONF['skip_html_filter_for_root'] ) &&
                 ( $_CONF['skip_html_filter_for_root'] == 1 ) &&
                SEC_inGroup( 'Root' )) {
            return $str;
        }

        $allowed = '';
        $allowedElements = explode(',',$this->allowedElements);
        $filterArray = array_unique($allowedElements);
        $allowed = implode(',',$filterArray);
        $allowed = preg_replace("#\\[.*?\\]#", "",$allowed);

        $configArray = array(
            'anti_link_spam' => array('`.`', ''),
            'comment' => 1,
            'cdata' => 3,
            'css_expression' => 1,
//            'deny_attribute' => 'style',
            'style_pass' => 1,
            'hook_tag' => 'glfusion_style_check',
            'unique_ids' => 0,
            'elements' => $allowed,
            'keep_bad' => 0,
            'schemes' => 'classid:clsid; href:, aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; style: nil; *:file, http, https', // clsid allowed in class
            'valid_xhtml' => 0,
            'direct_list_nest' => 1,
            'balance' => 1,
            'safe' => 1
        );
        $spec = 'object=-classid-type, -codebase; embed=type(oneof=application/x-shockwave-flash)';

        require_once $_CONF['path'] . 'lib/htmLawed/htmLawed.php';

        $str = htmLawed($str,$configArray,$spec);

        return $str;
    }

    /**
    * This censors inappropriate content
    *
    * This will replace 'bad words' with something more appropriate
    *
    * @param        string      $Message        String to check
    * @see function COM_checkHTML
    * @return   string  Edited $Message
    *
    * @copyright (c) 2005 phpBB Group
    * @license http://opensource.org/licenses/gpl-license.php GNU Public License
    *
    */
    public function censor( $text )
    {
        global $_CONF;

        if ( $_CONF['censormode'] != 0 && is_array( $_CONF['censorlist'] ) ) {
            $Replacement = $_CONF['censorreplace'];
            $unicode = (@preg_match('/\p{L}/u', 'a') !== false) ? true : false;
            foreach ($_CONF['censorlist'] AS $word ) {
    			if ($unicode) {
    				// Unescape the asterisk to simplify further conversions
    				$word = str_replace('\*', '*', preg_quote($word, '#'));
    				// Replace the asterisk inside the pattern, at the start and at the end of it with regexes
    				$word = preg_replace(array('#(?<=[\p{Nd}\p{L}_])\*(?=[\p{Nd}\p{L}_])#iu', '#^\*#', '#\*$#'), array('([\x20]*?|[\p{Nd}\p{L}_-]*?)', '[\p{Nd}\p{L}_-]*?', '[\p{Nd}\p{L}_-]*?'), $word);

    				// Generate the final substitution
    				$censors['match'][] = '#(?<![\p{Nd}\p{L}_-])(' . $word . ')(?![\p{Nd}\p{L}_-])#iu';
    			} else {
    				$censors['match'][] = '#(?<!\S)(' . str_replace('\*', '\S*?', preg_quote($word, '#')) . ')(?!\S)#iu';
    			}

    			$censors['replace'][] = $Replacement;
    		}
    	    if (sizeof($censors)) {
    		    return preg_replace($censors['match'], $censors['replace'], $text);
    	    }
        }
    	return $text;
    }

    /**
     * Turn all URLs in clickable links.
     *
     * @param string $value
     * @param array  $protocols  http/https, ftp, mail, twitter
     * @param array  $attributes
     * @param string $mode       normal or all
     * @return string
     */
    public function linkify($value, $protocols = array('http', 'mail','twitter'), array $attributes = array())
    {
        global $_CONF;

        $linkify = new \Misd\Linkify\Linkify(array('callback' => function($url, $caption, $isEmail) {
                global $_CONF;
                if ( !$isEmail && isset($_CONF['open_ext_url_new_window']) && $_CONF['open_ext_url_new_window'] == true && stristr($url,$_CONF['site_url']) === false ) {
                    // external
                    $target = ' target="_blank" rel="noopener noreferrer" ';
                } else {
                    $target = '';
                }
                $returnLink = '';
                $url = $this->sanitizeUrl( $url );
                if ( $isEmail ) {
                    $returnLink = '<a href="mailto:'.$caption.'">'.$caption.'</a>';
                } else {
                    $returnLink =     '<a href="'.$url.'" ' . $target .'>'.$caption.'</a>';
                }
                return $returnLink;
                return '<a href="'.$url.'" ' . $target .'>'.$caption.'</a>';
        }));

        $value = $linkify->process($value);

        // Link attributes
        $attr = '';
        foreach ($attributes as $key => $val) {
            $attr = ' ' . $key . '="' . htmlentities($val) . '"';
        }

        $links = array();

        // Extract existing links and tags
        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) { return '<' . array_push($links, $match[1]) . '>'; }, $value);

        // Extract text links for each protocol
        foreach ((array)$protocols as $protocol) {
            switch ($protocol) {
                case 'http':
                case 'https':
                    break;
                case 'mail':
                    break;
                case 'twitter':
                    $value = preg_replace_callback('~(?<!\w)[@](\w++)~',
                    function ($match) use (&$links, $attr) {
                        global $_CONF;
                        if ( isset($_CONF['open_ext_url_new_window']) && $_CONF['open_ext_url_new_window'] == true ) {
                            // external
                            $target = ' target="_blank" rel="noopener noreferrer" ';
                        } else {
                            $target = '';
                        }
                        return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\" rel=\"nofollow\"".$target.">{$match[0]}</a>") . '>';
                    }, $value);
                    break;
                default:
                    break;
            }
        }

        // Insert all link
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
    }

    public function sanitizeUrl( $url, $allowed_protocols = array('http','https','ftp'), $default_protocol = 'http' )
    {
        global $_CONF;

        $url = filter_var($url, FILTER_VALIDATE_URL);

        if ( empty( $allowed_protocols )) {
            $allowed_protocols = $_CONF['allowed_protocols'];
        } else if ( !is_array( $allowed_protocols )) {
            $allowed_protocols = array( $allowed_protocols );
        }

        if ( empty( $default_protocol )) {
            $default_protocol = 'http:';
        } else if ( substr( $default_protocol, -1 ) != ':' ) {
            $default_protocol .= ':';
        }

        $url = strip_tags( $url );
        if ( !empty( $url )) {
            $pos = utf8_strpos( $url, ':' );
            if ( $pos === false ) {
                $url = $default_protocol . '//' . $url;
            } else {
                $protocol = utf8_substr( $url, 0, $pos + 1 );
                $found_it = false;
                foreach( $allowed_protocols as $allowed ) {
                    if ( substr( $allowed, -1 ) != ':' ) {
                        $allowed .= ':';
                    }
                    if ( $protocol == $allowed ) {
                        $found_it = true;
                        break;
                    }
                }
                if ( !$found_it ) {
                    $url = $default_protocol . utf8_substr( $url, $pos + 1 );
                }
            }
        }

        return $url;
    }

    /**
    * Sanitize a filename.
    *
    * @param    string  $filename   the filename to clean up
    * @param    boolean $allow_dots whether to allow dots in the filename or not
    * @return   string              sanitized filename
    * @note     This function is pretty strict in what it allows. Meant to be used
    *           for files to be included where part of the filename is dynamic.
    *
    */
    function sanitizeFilename($filename, $allow_dots = true)
    {
        if ($allow_dots) {
            $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
            $filename = str_replace('..', '', $filename);
        } else {
            $filename = preg_replace('/[^a-zA-Z0-9\-_]/', '', $filename);
        }

        return trim($filename);
    }

    /**
    * Ensure an ID contains only alphanumeric characters, dots, dashes, or underscores
    *
    * @param    string  $id     the ID to sanitize
    * @param    boolean $new_id true = create a new ID in case we end up with an empty string
    * @return   string          the sanitized ID
    */
    public function sanitizeID( $id, $new_id = true )
    {
        $id = str_replace( ' ', '', $id );
        $id = str_replace( array( '/', '\\', ':', '+' ), '-', $id );
        $id = preg_replace( '/[^a-zA-Z0-9\-_\.]/', '', $id );
        $id = trim($id);
        if ( empty( $id ) && $new_id ) {
            $id = COM_makesid();
        }
        return trim($id);
    }

    public function prepareForDB($data) {
        if (is_array($data)) {
            # loop through array and apply the filters
            foreach($data as $var)  {
                $return_data[]  = DB_escapeString($var);
            }
            return $return_data;
        } else {
            $data = DB_escapeString($data);
            return $data;
        }
    }


    /**
    * Returns what HTML is allowed in content
    *
    * Returns what HTML tags the system allows to be used inside content.
    *
    * @param    boolean $list_only      true = return only the list of HTML tags
    * @return   string  HTML <span> enclosed string
    */
    function getAllowedHTML( $list_only = false)
    {
        global $_CONF, $LANG01;

        $retval = '';
        $first  = 0;

        $allow_page_break = false;
        if ( isset( $_CONF['skip_html_filter_for_root'] ) &&
                 ( $_CONF['skip_html_filter_for_root'] == 1 ) &&
                SEC_inGroup( 'Root' )) {
            if ( !$list_only && $this->_postmode == 'html' ) {
                $retval .= '<span class="warningsmall"><strong>' . $LANG01[123] . '</strong></span>, ';
            }

        } else {
            if ( !$list_only && $this->_postmode == 'html' ) {
                $retval .= '<span class="warningsmall"><strong>' . $LANG01['allowed_html'] . '</strong> ';
            }
            if ( $_CONF['allow_page_breaks'] && $this->operation == 'story')
                $allow_page_break = true;
        }

        if ( $this->_postmode == 'html' ) {

            $elementArray = array();
            $itemElements = explode(',',$this->allowedElements);
            $elementArray = array_merge($elementArray,$itemElements);
            if ( SEC_inGroup('Root') ) {
                $root    = explode(',',$_CONF['htmlfilter_root']);
                $elementArray = array_merge($elementArray,$root);
            }
            $filterArray = array_unique($elementArray);

            foreach ( $filterArray as $tag ) {
                if ( $first != 0 ) $retval .= ', ';
                $retval .= '&lt;' . $tag . '&gt;&nbsp;';
                $first++;
            }
            $retval .= '</span>';
        }
        return $retval;
    }

    public function sanitizeUsername($username)
    {
        $result = (string) preg_replace( '/[\x00-\x1F\x7F<>"%&*\/\\\\]/', '', $username );

        return trim($result);
    }

    /*
     * Does not filter or modify code block data - simply returns
     * this serves as a stub to prevent code blocks from being
     * parsed by other filters
     */
    function _codeblockFilter($action, $attributes, $content, $params, $node_object)
    {
        if ( $action == 'validate') {
            return true;
        }
        if (!isset ($attributes['default'])) {
            $codeblock = '[code]'.$content.'[/code]';
        } else {
            $lang = strip_tags($attributes['default']);
            $codeblock = '[code='.$lang.']'.$content.'[/code]';
        }
        return $codeblock;
    }

    /*
     * prepares a code block for display.
     */
    function _codeblock($action, $attributes, $content, $params, $node_object)
    {
        if ( $action == 'validate') {
            return true;
        }
        $codeblock = '<pre>' . @htmlspecialchars($content,ENT_NOQUOTES, $this->encoding) . '</pre>';
        return $codeblock;
    }

    function highlightQuery($str){
        return COM_highlightQuery( $str, $this->query);
    }

}

function glfusion_style_check( $element, $attribute_array = 0 ) {

    // Only some elements can have 'style' and its value should not look fishy
    // I.e., only alphanumeric characters, spaces, colons, semi-colons, commas, number-signs and single-quotes are permitted

    if ( $attribute_array == 0 ) return '</'.$element.'>';

    static $allowedElements = array('a', 'span', 'p', 'img', 'ul', 'ol', 'li', 'div');
    $badMatch = "`[^\w\s;:,#\-']`";
    static $allowedProperties = array('border', 'color', 'background-color', 'display', 'float', 'font-family', 'font-weight', 'font-size', 'list-style-type', 'margin', 'margin-left', 'margin-right', 'margin-top', 'margin-bottom', 'text-align', 'text-decoration', 'vertical-align', 'height', 'width');

    if ( in_array($element, $allowedElements) && isset($attribute_array['style']) && !preg_match($badMatch, $attribute_array['style'] ) ) {

        $style = $attribute_array['style'];
        // Remove unnecessary white-space
        $style = str_replace(array("\r", "\n", "\t"), '', $style);

        // Identify CSS property names and values in 'style' value
        $properties = explode(';', $style);
        $finalProperties = array();
        foreach( $properties as $namevalue ) {
            $namevalue = explode(':', trim($namevalue));
            $name = strtolower(trim($namevalue[0]));
            $value = isset($namevalue[1]) ? $namevalue[1] : 0;
            if ($value and in_array($name, $allowedProperties)) {
                $value = trim($value);
                switch( $name ) {
                    case 'height' :
                    case 'width' :
                        if ( $element == 'img' ) {
                            $finalProperties[] = $name . ':' . $value;
                        }
                        break;
                    case 'border':
                        if (stripos('solid black', $value)) {
                            $finalProperties[] = 'border: '. $value;
                        }
                        break;
                    case 'color':
                    case 'margin-top':
                    case 'margin-bottom':
                    case 'background-color':
                        $finalProperties[] = $name. ': '. $value;
                        break;
                        case 'display':
                        if (stripos(' block', $value)) {
                            $finalProperties[] = 'display: '. $value;
                        }
                        break;
                    case 'float':
                        if (stripos(' left right', $value)) {
                            $finalProperties[] = 'float: '. $value;
                        }
                        break;
                    case 'font-size':
                        if ( (preg_match('`(\d+)\s*px`i', $value, $m) and intval($m[1]) < 100) ) {
                            $finalProperties[] = 'font-size: '. $value;
                        }
                        break;
                    case 'font-weight' :
                        $finalProperties[] = 'font-weight: ' . (int) $value;
                        break;
                    case 'font-family':
                        $fonts = explode(',', $value);
                        $finalFonts = array();
                        foreach( $fonts as $font ) {
                            $font = trim(strtolower($font), " '\"");
                            if ( in_array($font, array('andale mono','arial', 'arial black', 'avant garde', 'chicago', 'comic sans ms', 'courier', 'courier new', 'geneva', 'georgia', 'helvetica', 'impact', 'monaco', 'tahoma', 'terminal', 'times', 'times new roman', 'trebuchet ms', 'verdana', 'serif', 'san-serif' ) ) ) {
                                $finalFonts[] = $font;
                            }
                        }
                        if (!empty($finalFonts)) {
                            $finalProperties[] = 'font-family: '. implode(', ', $finalFonts);
                        }
                        break;
                    case 'list-style-type':
                        if (stripos(' circle disc square lower-roman upper-roman lower-greek upper-greek lower-alpha upper-alpha', $value)) {
                            $finalProperties[] = 'list-style-type: '. $value;
                        }
                        break;
                    case 'margin-left':
                    case 'margin-right':
                        if ((strtolower($value) == 'auto') or (preg_match('`(\d+)\s*px`i', $value, $m) and intval($m[1]) <601)) {
                            $finalProperties[] = $name. ': '. $value;
                        }
                        break;
                    case 'margin' :
                        if ((strtolower($value) == 'auto') or (preg_match('`(\d+)\s*px`i', $value, $m) and intval($m[1]) < 500)) {
                            $finalProperties[] = $name. ': '. $value;
                        }
                        break;
                    case 'text-align':
                        if (stripos(' left right center justify', $value)) {
                            $finalProperties[] = 'text-align: '. $value;
                        }
                        break;
                    case 'text-decoration':
                        if (strtolower($value) == 'underline') {
                            $finalProperties[] = 'text-decoration: '. $value;
                        }
                        break;
                    case 'vertical-align':
                        if (stripos(' middle, bottom, top, baseline, text-top, text-bottom', $value)) {
                            $finalProperties[] = 'vertical-align: '. $value;
                        }
                        break;
                }
            }
        }

        // Assign 'style' the filtered value
        $style = implode('; ', $finalProperties);
        if (!empty($style)) {
            $attribute_array['style'] = $style;
        } else {
            unset($attribute_array['style']);
        }
    } elseif( isset($attribute_array['style']) ) {
        unset($attribute_array['style']);
    }

    // Finally, return to htmLawed the element in the opening tag with attributes
    $attributes = '';
    if ( is_array($attribute_array)) {
        foreach( $attribute_array as $k=>$v ) {
            $attributes .= " {$k}=\"{$v}\"";
        }
    }
    static $empty_elements = array('area'=>1, 'br'=>1, 'col'=>1, 'embed'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'isindex'=>1, 'param'=>1);
    return "<{$element}{$attributes}". (isset($empty_elements[$element]) ? ' /' : ''). '>';
}

?>