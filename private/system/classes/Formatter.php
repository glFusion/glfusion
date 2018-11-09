<?php
/**
*   glFusion Text, HTML, BBcode formatter
*
*   @author     Mark R. Evans <mark@lglfusion.org>
*   @copyright  Copyright (c) 2017-2018 Mark R. Evans <mark@glfusion.org>
*   @package    glFusion
*   @version    0.0.2
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*
*   This class handles formatting text, html and bbcode for presentation
*   Input can be directly from the user or from a DB and display it in the browser.
*
*   Text will be formatted based on $formatMode
*   Auto tags are processed if enabled
*   BBCodes are processed if enabled
*   Smilies are processed if enabled and the Smiley Plugin is available
*   Links (Urls, email, twitter) will be automatically parsed if enabled
*   Code blocks are always processed
*
*   Content is censored if enabled
*
*   Additional codes and filters (pre and post processing) can be specified
*
*   parse() returns display ready text
*
*   Output is cached using glFusin\Cache class - TTL defaults to 10 minutes
*
*/

namespace glFusion;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

// Magic url types
define('MAGIC_URL_EMAIL', 1);
define('MAGIC_URL_FULL', 2);
define('MAGIC_URL_LOCAL', 3);
define('MAGIC_URL_WWW', 4);

define ('FILTER_PRE', 1);
define ('FILTER_POST', 2);

class Formatter {

    /*
     * var formatType
     * text or html
     */
    var $formatType = 'text';

    /*
     * var processSmilies
     * Boolean - true process smilies
     */
    var $processSmilies = false;

    /*
     * var $processBBCode
     * boolean - true to process bbcode
     */
    var $processBBCode = false;

    /*
     * var $parseUrls
     * boolean - true to process links automatically
     */
    var $parseUrls = false;

    /*
     * var $parseAutoTags
     * boolean - true to process auto tags
     */
    var $parseAutoTags = false;

    /*
     * var $censor
     * boolean - true to censor content
     */
    var $censor = true;

    /*
     * var $convertPre
     * boolean - true to convert <pre> to [code]
     */
     var $convertPre = false;

    /*
     * var $allowedHTML
     * string - comma delimited list of allowed HTML tags
     */
    var $allowedHTML = '';

    /*
     * var $filter
     * object glfusion\sanitizer object
     */
    var $filter = null;

    /*
     * var $namespace
     * string - current namespace (i.e.; glfusion, forum, etc.)
     */
    var $namespace = '';

    /*
     * var $action
     * string - current action (i.e.; comment, story, etc.)
     */
    var $action   = '';

    /*
     * var $bbcodeBlackList
     * array - array of bbcode tags to ignore
     */
    var $bbcodeBlackList = array();

    /*
     * var $_codes
     * array - additional codes to process
     */
    var $_codes = array();

    /*
     * var $_filter
     * array - additional filters to process
     */
    var $_filters = array();

    /*
     * var $query
     * string - query string to highlight
     */
     var $query = '';

    /*
     * var $wysiwygEditor
     * bool - if WYSIWYG editor is being used
     */
     var $wysiwygEditor = false;

    /**
     * constructor
     */
    public function __construct()
    {

    }

    /**
     * Calculates a unique key for caching the content
     * @param none
     * @return string
     */
    private function getOptionsKey()
    {
        return md5(
            $this->formatType           .
            $this->processSmilies       .
            $this->processBBCode        .
            $this->parseUrls            .
            $this->parseAutoTags        .
            $this->censor               .
            $this->allowedHTML          .
            $this->namespace            .
            $this->action
        );
    }

    /**
     * Sets BBCodes to be ignored (not processed)
     * @param $blacklist - array of bbcodes to ignore
     * @return none
     */
    public function setBbcodeBlackList($blacklist = array())
    {
        if (is_array($blacklist)) {
            foreach($blacklist AS $code) {
                $this->bbcodeBlackList[] = $code;
            }
        } else {
            $this->bbcodeBlackList[] = $blacklist;
        }
    }

    /**
     * Sets type of content (text or HTML)
     * @param $mode - type of content being processed
     * @return none
     */
    public function setType($mode = 'text')
    {
        if (!in_array(strtolower($mode),array('text','html'))) $mode = 'text';
        $this->formatType = strtolower($mode);
    }

    /**
     * Enables Geshi formatting of code blocks - keep for compatibility
     * @param $mode - true or false
     * @return none
     */
    public function setGeshi($mode = false)
    {
        return;
    }

    /**
     * Signals if WYSIWYG editor is being used
     * @param $mode - true or false
     * @return none
     */
    public function setWysiwyg($mode = false)
    {
        $this->wysiwygEditor = (bool) $mode;
    }

    /**
     * Enables Smilies processing (if Smiley Plugin is available)
     * @param $allow - true or false
     * @return none
     */
    public function setProcessSmilies($allow = false)
    {
        $this->processSmilies = (bool) $allow;
    }

    /**
     * Enables BBCode processing
     * @param $allow - true or false
     * @return none
     */
    public function setProcessBBCode($allow = false)
    {
        $this->processBBCode = (bool) $allow;
    }

    /**
     * Enables URL / Link auto parsing
     * @param $allow - true or false
     * @return none
     */
    public function setParseURLs($allow = false)
    {
        $this->parseUrls = (bool) $allow;
    }

    /**
     * Enabled Auto tag processing
     * @param $allow - true or false
     * @return none
     */
    public function setParseAutoTags($allow = false)
    {
        $this->parseAutoTags = (bool) $allow;
    }

    /**
     * Enables Censoring of content
     * @param $allow - true or false
     * @return none
     */
    public function setCensor($allow = false)
    {
        $this->censor = (bool) $allow;
    }

    /**
     * Sets allowed HTML for content
     * @param $allowedHTML - comma delimited list of allowed HTML
     * @return none
     */
    public function setAllowedHTML($allowedHTML = '')
    {
        $this->allowedHTML = $allowedHTML;
    }

    /**
     * Sets namespace (used by auto tag replacement)
     * @param $namespace - current namespace (i.e.; glfusion, forum, etc.)
     * @return none
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Sets action (used by auto tag replacement)
     * @param $action - current action (i.e.; comment, post, etc.)
     * @return none
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Sets query string
     * @param $query - string to highlight
     * @return none
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * Enable / disable auto conversion of <pre></pre> to [code][/code]
     * @param $convert - bool true / false
     * @return none
     */
     public function setConvertPre($convert = false)
     {
        $this->convertPre = (bool) $convert;
     }

	/**
	 * Add a code
	 * @param string $name The name of the code
	 * @param string $callback_type
	 * @param string $callback_func The callback function to call
	 * @param array $callback_params The callback parameters
	 * @param string $content_type
	 * @param array $allowed_within
	 * @param array $not_allowed_within
	 * @return bool
	 */
	public function addCode ($name, $callback_type, $callback_func, $callback_params, $content_type, $allowed_within, $not_allowed_within)
	{
		if (!preg_match ('/^[a-zA-Z0-9*_!+-]+$/', $name)) {
			return false; // invalid
		}
		$this->_codes[$name] = array (
			'name' => $name,
			'callback_type' => $callback_type,
			'callback_func' => $callback_func,
			'callback_params' => $callback_params,
			'content_type' => $content_type,
			'allowed_within' => $allowed_within,
			'not_allowed_within' => $not_allowed_within
		);
		return true;
	}


    /**
     * Add a filter
     * @param $type - FILTER_PRE or FILTER_POST
     * @param $callback - function to call
     * @return none
     */
	public function addFilter ($type, $callback)
	{
	    $this->_filters[] = array('type' => $type, 'callback' => $callback);
	}

    /**
     * Processes content and performs all filtering / sanitize actions
     * @param string  $str      - string to parse
     * @param boolean $cache    - whether or not to cache the results
     * @param integer $cacheTTL - Time to Live for cache object
     * @return string - display ready string
     */
    public function parse($str, $cache = false, $cacheTTL = 3600)
    {
        global $_CONF;

        if ($cache) {
            $key = 'f_'.md5($str) .'_'. $this->getOptionsKey();
            $c = \glFusion\Cache::getInstance();
            if ($c->has($key)) {
                $str = $c->get($key);
                if ($this->parseAutoTags) {
                    $str = $this->_replaceTags($str);
                }
                return $str;
            }
        }

        $bbcode = new \StringParser_BBCode();
        $bbcode->setGlobalCaseSensitive (false);
        $this->filter = \sanitizer::getInstance();

        $this->filter->setPostmode($this->formatType);

        if ($this->formatType == 'text') {
            // filter all code prior to replacements
            $bbcode->addFilter(FILTER_PRE, array($this,'_htmlspecialchars'));
        }
        $bbcode->addFilter(FILTER_PRE, array($this,'_fixmarkup'));

        foreach($this->_filters AS $filter) {
           	$bbcode->addFilter ($filter['type'], $filter['callback']);
        }

        if ($this->convertPre && $this->formatType == 'html') {
            $str = str_replace('<pre>','[code]',$str);
            $str = str_replace('</pre>','[/code]',$str);
        }

        if ($this->formatType != 'html') {
            $bbcode->addParser(array('block','inline','link','listitem'), array($this,'_nl2br'));
        }

        if ($this->query != '') {
            $filter->query = $$this->query;
            $bbcode->addParser(array('block','inline','listitem'), array(&$filter,'highlightQuery'));
        }

        if ($this->processSmilies) {
            $bbcode->addParser (array ('block', 'inline', 'listitem'), array($this,'_replacesmilie'));
        }

        if ($this->parseUrls) {
            $bbcode->addParser (array('block','inline','listitem'), array ($this->filter, 'linkify'));
        }

        if (!in_array('code',$this->bbcodeBlackList)) {
            $bbcode->addCode ('code', 'usecontent?', array($this,'_do_code'), array ('usecontent_param' => 'default'),
                              'code', array('listitem', 'block', 'inline', 'quote'), array ('link'));
        }
        if ($this->processBBCode) {
            if (!in_array('list',$this->bbcodeBlackList)) {
                $bbcode->addParser ('list', array($this,'bbcode_stripcontents'));
            }
            if (!in_array('b',$this->bbcodeBlackList) && !isset($this->_codes['b'])) {
                $bbcode->addCode ('b', 'simple_replace', null, array ('start_tag' => '<b>', 'end_tag' => '</b>'),
                                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
            }
            if (!in_array('i',$this->bbcodeBlackList) && !isset($this->_codes['i'])) {
                $bbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '<i>', 'end_tag' => '</i>'),
                                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
            }
            if (!in_array('u',$this->bbcodeBlackList) && !isset($this->_codes['u'])) {
                $bbcode->addCode ('u', 'simple_replace', null, array ('start_tag' => '<span style="text-decoration: underline;">', 'end_tag' => '</span>'),
                                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
            }
            if (!in_array('p',$this->bbcodeBlackList) && !isset($this->_codes['p'])) {
                $bbcode->addCode ('p', 'simple_replace', null, array ('start_tag' => '<p>', 'end_tag' => '</p>'),
                                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
            }
            if (!in_array('s',$this->bbcodeBlackList) && !isset($this->_codes['s'])) {
                $bbcode->addCode ('s', 'simple_replace', null, array ('start_tag' => '<del>', 'end_tag' => '</del>'),
                                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
            }
            if (!in_array('size',$this->bbcodeBlackList) && !isset($this->_codes['size'])) {
                $bbcode->addCode ('size', 'callback_replace', array($this,'do_bbcode_size'), array('usecontent_param' => 'default'),
                                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
            }
            if (!in_array('color',$this->bbcodeBlackList) && !isset($this->_codes['color'])) {
                $bbcode->addCode ('color', 'callback_replace', array($this,'do_bbcode_color'), array ('usercontent_param' => 'default'),
                                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());
            }
            if (!in_array('list',$this->bbcodeBlackList) && !isset($this->_codes['list'])) {
                $bbcode->addCode ('list', 'callback_replace', array($this,'do_bbcode_list'), array ('usecontent_param' => 'default'),
                                  'list', array ('inline','block', 'listitem'), array ());
                $bbcode->addCode ('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'),
                                  'listitem', array ('list'), array ());
            }
            if (!in_array('quote',$this->bbcodeBlackList) && !isset($this->_codes['quote'])) {
                $bbcode->addCode ('quote','simple_replace',null,array('start_tag' => '</p><blockquote>', 'end_tag' => '</blockquote><p>'),
                                  'inline', array('listitem','block','inline','link'), array());
            }
            if (!in_array('url',$this->bbcodeBlackList) && !isset($this->_codes['url'])) {
                $bbcode->addCode ('url', 'usecontent?', array($this,'do_bbcode_url'), array ('usecontent_param' => 'default'),
                                  'link', array ('listitem', 'block', 'inline'), array ('link'));
            }
            if (!in_array('img',$this->bbcodeBlackList) && !isset($this->_codes['img'])) {
                $bbcode->addCode ('img', 'usecontent', array($this,'do_bbcode_img'), array (),
                                  'image', array ('listitem', 'block', 'inline', 'link'), array ());
            }

            foreach($this->_codes AS $code) {
                $bbcode->addCode($code['name'],
                                 $code['callback_type'],
                                 $code['callback_func'],
                                 $code['callback_params'],
                                 $code['content_type'],
                                 $code['allowed_within'],
                                 $code['not_allowed_within']
                );
            }

            $bbcode->setCodeFlag ('quote', 'paragraph_type', BBCODE_PARAGRAPH_ALLOW_INSIDE);
            $bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
            $bbcode->setCodeFlag ('*', 'paragraphs', true);
            $bbcode->setCodeFlag ('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
            $bbcode->setCodeFlag ('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
        }

        if ($this->formatType == 'html') {
           $bbcode->addParser(array('block','inline','list','listitem'), array($this,'_cleanHTML'));
        }

//        $bbcode->setRootParagraphHandling (true);

        if ($this->censor) {
            $str = $this->filter->censor($str);
        }

        $str = $bbcode->parse ($str);
        if ($cache) {
            $c->set($key,$str,array($this->namespace,$this->namespace.'_'.$this->action),$cacheTTL);
        }

        unset($bbcode);

        if ($this->parseAutoTags) {
            $str = $this->_replaceTags($str);
        }
        return $str;
    }

    /**
     * Convert linebreaks to \n
     * @param $text
     * @return string filterd string
     */
    public function convertlinebreaks ($text)
    {
        return preg_replace ("/\015\012|\015|\012/", "\n", $text);
    }

    /**
     * Strip newlines
     * @param $text
     * @return string
     */
    public function bbcode_stripcontents ($text)
    {
        return preg_replace ("/[^\n]/", '', $text);
    }

    /**
     * Encode string
     * @param $text
     * @return string
     */
    public function _htmlspecialchars($text)
    {
        return (@htmlspecialchars ($text,ENT_NOQUOTES, COM_getEncodingt(),true));
    }

    /**
     * [url] bbcode
     */
    public function do_bbcode_url ($action, $attributes, $content, $params, $node_object)
    {
        global $_CONF;

        if ($action == 'validate') {
            return true;
        }

    	$retval = '';
        $url = '';
        $linktext = '';
        $target = '';

        if (!isset ($attributes['default'])) {
            if (stristr($content,'http')) {
                $url = strip_tags($content);
                $linktext = @htmlspecialchars ($content,ENT_QUOTES, COM_getEncodingt(),true);
            } else {
                $url = 'http://'.strip_tags($content);
                $linktext = @htmlspecialchars ($content,ENT_QUOTES, COM_getEncodingt(),true);
            }
        } else if (stristr($attributes['default'],'http')) {
            $url = strip_tags($attributes['default']);
    //        $linktext = @htmlspecialchars ($content,ENT_QUOTES,COM_getEncodingt());
            $linktext = strip_tags($content);
        } else {
            $url = 'http://'.strip_tags($attributes['default']);
            $linktext = @htmlspecialchars ($content,ENT_QUOTES,COM_getEncodingt(),true);
        }

        if (isset($_CONF['open_ext_url_new_window']) && $_CONF['open_ext_url_new_window'] == true && stristr($url,$_CONF['site_url']) === false) {
            $target = ' target="_blank" ';
        }
        $url = COM_sanitizeUrl($url);
        $retval = '<a href="'. $url .'" rel="nofollow noopener noreferrer"'.$target.'>'.$linktext.'</a>';
        return $retval;
    }

    /**
     * [list] bbcode
     */
    public function do_bbcode_list ($action, $attributes, $content, $params, $node_object)
    {
        if ($action == 'validate') {
            return true;
        }
        if (!isset ($attributes['default'])) {
            return '<ul>'.$content.'</ul>';
        } else {
            if (is_numeric($attributes['default'])) {
                return '<ol>'.$content.'</ol>';
            } else {
                return '<ul>'.$content.'</ul>';
            }
        }
        return '<ul>'.$content.'</ul>';
    }

    /**
     * [img] bbcode
     */
    public function do_bbcode_img ($action, $attributes, $content, $params, $node_object)
    {
        if ($action == 'validate') {
            if (isset($attributes['caption'])) {
                $node_object->setFlag('paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
                if ($node_object->_parent->type() == STRINGPARSER_NODE_ROOT OR
                    in_array($node_object->_parent->_codeInfo['content_type'], array('block', 'list', 'listitem'))) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
        if (!in_array('img',$this->bbcodeBlackList)) {
            if (isset($attributes['h']) AND isset ($attributes['w'])) {
                $dim = 'width=' . (int) $attributes['w'] . ' height=' . (int) $attributes['h'];
            } else {
                $dim = '';
            }
            if (isset($attributes['align'] )) {
                if (!in_array(strtolower($attributes['align']),array('left','right','center'))) {
                    $attributes['align'] = 'left';
                }
                $align = ' align=' . $attributes['align'] . ' ';
            } else {
                $align = '';
            }
            $content = $this->bbcode_cleanHTML($content);
            return '<img src="'.htmlspecialchars($content,ENT_QUOTES, COM_getEncodingt(),true).'" ' . $dim . $align . ' alt=""/>';
        } else {
            return '[img]' . $this->bbcode_cleanHTML($content) . '[/img]';
        }
    }

    /**
     * [size] bbcode
     */
    public function do_bbcode_size  ($action, $attributes, $content, $params, $node_object)
    {
        if ($action == 'validate') {
            return true;
        }
        return '<span style="font-size: '.(int) $attributes['default'].'px;">'.$content.'</span>';
    }

    /**
     * [color] bbcode
     */
    public function do_bbcode_color  ($action, $attributes, $content, $params, $node_object)
    {
        if ($action == 'validate') {
            return true;
        }
        return '<span style="color: '. strip_tags($attributes['default']).';">'.$content.'</span>';
    }

    /**
     * [code] bbcode
     */
    public function _do_code($action, $attributes, $content, $params, $node_object)
    {
        global $_FF_CONF;

        if ($action == 'validate') {
            return true;
        }

        static $insideCode = 0;

        if ($insideCode > 0 ) return $content;

        $insideCode = 1;

        $content = @htmlspecialchars_decode($content,ENT_QUOTES);
        $content = @html_entity_decode($content);
        $content = preg_replace('/^\s*?\n|\s*?\n$/','',$content);

        if (isset ($attributes['default'])) {
            $style = strtolower(strip_tags($attributes['default']));
        } else {
            $style = 'php';
        }

        $codeblock = '<div style="width:100%;"><pre><code class="'.$style.'">'
                    . @htmlspecialchars ($content,ENT_NOQUOTES, COM_getEncodingt(),true)
                    . '</code></pre></div>';

        $codeblock = str_replace('{','&#123;',$codeblock);
        $codeblock = str_replace('}','&#125;',$codeblock);

        $codeblock = str_replace('[','&#91;',$codeblock);
        $codeblock = str_replace(']','&#93;',$codeblock);

        $insideCode = 0;

        return $codeblock;
    }

    /**
    * Cleans (filters) HTML - only allows safe HTML tags
    *
    * @param        string      $str    string to filter
    * @return       string      filtered HTML code
    */
    public function bbcode_cleanHTML($str)
    {
        $AllowedElements = $this->filter->makeAllowedElements($this->allowedHTML);
        $this->filter->setAllowedelements($AllowedElements);
        $this->filter->setNamespace($this->namespace,$this->action);
        $this->filter->setPostmode('html');
        return $this->filter->filterHTML($str);
    }

    /**
     * Converts newline to <br>
     */
    public function _nl2br($str)
    {
        return str_replace(array("\r\n", "\r", "\n"), "<br>", $str);
    }

    /**
     * Fixes older markup
     */
    public function _fixmarkup($str)
    {
        $str = str_replace(array("[/list]\r\n", "[/list]\r", "[/list]\n","[/list] \r\n", "[/list] \r", "[/list] \n"), "[/list]", $str);
        $str = str_replace(array("[/code]\r\n", "[/code]\r", "[/code]\n","[/code] \r\n", "[/code] \r", "[/code] \n"), "[/code]", $str);
        $str = str_replace(array("[quote]\r\n", "[quote]\r", "[quote]\n","[quote] \r\n", "[quote] \r", "[quote] \n"), "[quote]", $str);
        $str = str_replace(array("[/quote]\r\n", "[/quote]\r", "[/quote]\n","[/quote] \r\n", "[/quote] \r", "[/quote] \n"), "[/quote]", $str);
        $str = str_replace(array("[QUOTE]\r\n", "[QUOTE]\r", "[QUOTE]\n","[QUOTE] \r\n", "[QUOTE] \r", "[QUOTE] \n"), "[QUOTE]", $str);
        $str = str_replace(array("[/QUOTE]\r\n", "[/QUOTE]\r", "[/QUOTE]\n","[/QUOTE] \r\n", "[/QUOTE] \r", "[/QUOTE] \n"), "[/QUOTE]", $str);

        return $str;
    }


    /**
    * Cleans (filters) HTML - only allows HTML tags specified in the
    * in $this->allowedHTML string.  This function is designed to be called
    * by the stringparser class to filter everything except [code] blocks.
    *
    * @param        string      $message        The topic post to filter
    * @return       string      filtered HTML code
    */
    public function _cleanHTML($message)
    {
        $AllowedElements = $this->filter->makeAllowedElements($this->allowedHTML);
        $this->filter->setAllowedelements($AllowedElements);
        $this->filter->setNamespace($this->namespace,$this->action);
        $this->filter->setPostmode($this->formatType);
        return $this->filter->filterHTML($message);
    }

    /**
     * Processes glFusion auto tags
     */
    public function _replaceTags($text)
    {
        return PLG_replaceTags($text,$this->namespace,$this->action);
    }

    /**
     * Processes smilies
     */
    public function _replacesmilie($str)
    {
        if ($this->processSmilies) {
            if (function_exists('msg_showsmilies')) {
                $str = msg_replaceEmoticons($str);
            } elseif (function_exists('forum_xchsmilies')) {
                $str = forum_xchsmilies($str);
            }
        }
        return $str;
    }
}
