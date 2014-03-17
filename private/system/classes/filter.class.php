<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | filter.cass.php                                                          |
// |                                                                          |
// | glFusion HTML / Text Filter                                              |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2014 by the following authors:                             |
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

if (!class_exists('StringParser') ) {
    require_once $_CONF['path'] . 'lib/bbcode/stringparser_bbcode.class.php';
}

class sanitizer
{
    var $_postmode       = 'text';  // Post mode
    var $allowedElements = 'p,b,a,i,strong,em';      // default allowed HTML elements - very restrictive
    var $schemas         = 'http:,https:,ftp:';   // default schemas
    var $encoding        = 'utf-8'; // character encoding
    var $namespace       = '';      // who is calling.. used for replacetags...
    var $action          = '';      // action being performed...
    var $replaceTags     = true;    // do we or don't we replace auto tags
    var $_censorData     = true;    // do we or don't we censor data
    var $filterMethod    = 'htmlpurifier'; // or htmlawed

    public function __construct( )
    {
        global $_SYSTEM;

        $this->encoding = COM_getEncodingt();
    }


    public static function getInstance(  )
    {
        static $instance = array();

        if (!isset($instance) ) {
            $instance = new sanitizer();
        }
        return $instance;
    }

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
     * Set namespace - this defines what area / action is being
     * perfomred - for example, glFusion is the area, comment is the action
     * Used to determine autotag replacements.
     *
     * @param   string  $namespace  namespace i.e.; glfusion, mediagallery, forum
     * @return  none
     * @access  public
     *
     */
    public function setNamespace( $namespace, $action )
    {
        $this->namespace = $namespace;
        $this->action    = $action;
    }

    public function setAction( $action )
    {
        $this->action = $action;
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
        $sp->addCode ('code', 'usecontent', array($this,'_codeblockFilter'), array ('usecontent_param' => 'default'),
                      'code', array ('block'), array ());

        if ( $this->_censorData ) {
            $str = $this->_censor($str);
        }

        $str = $sp->parse ($str);

        return $str;
    }

    /*
     * The HTML Filters escape some < > &, etc.  we can undo them
     *
     */

    public function editableText($str)
    {
        if ( $this->_postmode == 'html' ) {
            // html filter escapes several items...
            $str = htmlspecialchars_decode($str,ENT_NOQUOTES);
        }
        return $this->_htmlspecialchars($str,ENT_NOQUOTES,$this->encoding);
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
            $sp->addParser (array ('block', 'inline'), array($this,'_htmlspecialchars'));
            $sp->addParser(array('block','inline'), 'nl2br');
        }
        $sp->addParser(array('block','inline'), array($this,'linkify'));

        $sp->addCode ('code', 'usecontent', array($this,'_codeblock'), array ('usecontent_param' => 'default'),
                      'code', array ('block'), array ());

        if ( $this->replaceTags ) {
            $sp->addParser(array('block'), array($this,'_replacetags'));
        }

        if ( $this->_censorData ) {
            $str = $this->_censor($str);
        }
        $str = $sp->parse ($str);

        return $str;
    }

    /*
     * replace glFusion autotags with final form data
     */
    function _replaceTags($text) {
        return PLG_replaceTags($text,$this->namespace,$this->action);
    }

    function _htmlspecialchars($text)
    {
        return (@htmlspecialchars ($text,ENT_NOQUOTES, $this->encoding));
    }


    /*
     * Filter HTML input to remove malicious or un-wanted HTML tags
     */
    public function _cleanHTML( $str )
    {
        global $_CONF,$_SYSTEM;

        if ( isset( $_CONF['skip_html_filter_for_root'] ) && ( $_CONF['skip_html_filter_for_root'] == 1 ) && SEC_inGroup( 'Root' )) {
            return $str;
        }

        $configArray = explode(',',$this->allowedElements);

        $filterArray = array_unique($configArray);
        foreach($filterArray as $element) {
            $final[$element] = true;
        }
        $configFilter = implode(",",$filterArray);

        require_once $_CONF['path'] . 'lib/htmlpurifier/HTMLPurifier.auto.php';
        require_once $_CONF['path'] . 'lib/htmlpurifier/CustomFilters.php';

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.AllowedElements', $final);
        $config->set('Core.Encoding',$this->encoding);
        $config->set('Core.CollectErrors',true);
        $config->set('AutoFormat.Linkify',false);

        $config->set('HTML.SafeObject',true);       // allow youtube
        $config->set('Output.FlashCompat',true);    // allow youtube

        $purifier = new HTMLPurifier($config);
        $clean_html = $purifier->purify($str);

    //@TODO debug code

        if (@$config->get('Core', 'CollectErrors')) {
            $e = $purifier->context->get('ErrorCollector');
            $class = $e->getRaw() ? 'fail' : 'pass';
            COM_filterLog( $e->getHTMLFormatted($config) );

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
        $allowedStuff = explode(',',$this->allowedElements);
        $filterArray = array_unique($allowedStuff);
        $allowed = implode(',',$filterArray);

        $configArray = array(
            'anti_link_spam' => array('`.`', ''),
            'comment' => 1,
            'cdata' => 3,
            'css_expression' => 1,
            'deny_attribute' => 'style',
            'unique_ids' => 0,
            'elements' => $allowed,
            'keep_bad' => 0,
            'schemes' => 'classid:clsid; href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; style: nil; *:file, http, https', // clsid allowed in class
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
        $codeblock = '[code]'.$content.'[/code]';
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
        $codeblock = '<pre>'  . @htmlspecialchars($content,ENT_NOQUOTES, $this->encoding) . '</pre>';
        return $codeblock;
    }

    function _censor( $text )
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

    function _makeClickableLinks( $text )
    {
        $text = preg_replace( '/([^"]?)((((ht|f)tps?):(\/\/)|www\.)[a-z0-9%&_\-\+,;=:@~#\/.\?\[\]]+(\/|[+0-9a-z]))/is', '\\1<a href="\\2">\\2</a>', $text );
        $text = str_replace( '<a href="www', '<a href="http://www', $text );

        return $text;
    }

    public function sanitizeUrl( $url, $allowed_protocols = array('http','https','ftp'), $default_protocol = 'http' )
    {
        global $_CONF;

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
    function sanitizeFilename($filename, $allow_dots = false)
    {
        if ($allow_dots) {
            $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
            $filename = str_replace('..', '', $filename);
        } else {
            $filename = preg_replace('/[^a-zA-Z0-9\-_]/', '', $filename);
        }

        return $filename;
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
        if ( empty( $id ) && $new_id ) {
            $id = COM_makesid();
        }

        return $id;
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
     * Turn all URLs in clickable links.
     *
     * @param string $value
     * @param array  $protocols  http/https, ftp, mail, twitter
     * @param array  $attributes
     * @param string $mode       normal or all
     * @return string
     *
     * from: http://www.jasny.net/articles/linkify-turning-urls-into-clickable-links-in-php/
     *
     */
    public function linkify($value, $protocols = array('http', 'mail','twitter'), array $attributes = array())
    {
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
                case 'https':   $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, "<a $attr href=\"$protocol://$link\">$link</a>") . '>'; }, $value); break;
                case 'mail':    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
                case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\">{$match[0]}</a>") . '>'; }, $value); break;
                default:        $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
            }
        }
        // Insert all link
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
    }
}
?>