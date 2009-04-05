<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | htmlfilter.class.php                                                     |
// |                                                                          |
// | glFusion HTML filtering class library.                                   |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
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
require_once $_CONF['path'].'lib/htmLawed/htmLawed.php';
require_once $_CONF['path'].'lib/bbcode/stringparser_bbcode.class.php';

class htmlFilter {
    var     $content = '';

    // behavior settings

    private $_balance = 1;      // Balance tags for well-formedness and proper nesting;
    private $_valid_xhtml = 0;  // Magic parameter to make input the most valid XHTML
                                // without needing to specify other relevant parameters

    /*
     * Make URLs absolute or relative; $this->_base_url needs
     * to be set
     *
     * -1 - make relative
     * 0 - no action  (default)
     * 1 - make absolute
     */
    private $_abs_url = 0;

    private $_base_url = 0;

    /*
     * Mark & characters in the original input
     */
    private $_and_mark = 0;

    /*
     * Anti-link-spam measure;
     *
     * 0 - no measure taken (default)
     * array("regex1", "regex2") - will ensure a rel attribute with
     * nofollow in its value in case the href attribute value matches
     * the regular expression pattern regex1, and/or will remove href
     * if its value matches the regular expression pattern regex2.
     * E.g., array("/./", "/://\W*(?!(abc\.com|xyz\.org))/");
     */
    private $_anti_link_spam = 0;

    /*
     * Anti-mail-spam measure;
     * 0 - no measure taken  (default)
     * word - @ in mail address in href attribute value is replaced
     * with specified word
     */
    private $_anti_mail_spam = 0;

    /* Handling of CDATA sections;
     *
     * 0 - don't consider CDATA sections as markup and proceed as
     *     if plain text  ^"
     * 1 - remove
     * 2 - allow, but neutralize any <, >, and & inside by converting them to named entities
     * 3 - allow
     */
    private $_cdata = 0;

    /*
     * Replace discouraged characters introduced by Microsoft Word, etc.;
     *
     * 0 - no  *
     * 1 - yes
     * 2 - yes, but replace special single & double quotes with ordinary ones
     */
    private $_clean_ms_char = 0;

    private $_comment = 0;
    private $_css_expression = 0;
    private $_hexdec_entity = 1;
    private $_keep_bad = 6;
    private $_lc_std_val = 1;
    private $_make_tag_strict = 1;
    private $_named_entity = 1;
    private $_no_deprecated_attr = 1;
    private $_parent = 'body';
    private $_tidy = 0;
    private $_unique_ids = 1;

    // element definitions
    private $_allowEmbed = false;
    private $_allowImg   = false;
    private $_allowForm  = false;

    private $_elements   = array();
    private $_schemes    = array();
    private $_userElements = array();


    /**
     * Constructor
     */

    public function __construct()
    {
        $this->_elements = array (
                'a' => 1,
                'abbr' => 1,
                'acronym' => 1,
                'address' => 1,
                'area' => 1,
                'b' => 1,
                'bdo' => 1,
                'big' => 1,
                'blockquote' => 1,
                'br' => 1,
                'button' => 1,
                'caption' => 1,
                'center' => 1,
                'cite' => 1,
                'code' => 1,
                'col' => 1,
                'colgroup' => 1,
                'dd' => 1,
                'del' => 1,
                'dfn' => 1,
                'dir' => 1,
                'div' => 1,
                'dl' => 1,
                'dt' => 1,
                'em' => 1,
                'fieldset' => 0,
                'font' => 1,
                'form' => 0,
                'h1' => 1,
                'h2' => 1,
                'h3' => 1,
                'h4' => 1,
                'h5' => 1,
                'h6' => 1,
                'hr' => 1,
                'i' => 1,
                'img' => 0,
                'input' => 0,
                'ins' => 1,
                'isindex' => 1,
                'kbd' => 1,
                'label' => 0,
                'legend' => 0,
                'li' => 1,
                'map' => 1,
                'menu' => 0,
                'noscript' => 1,
                'ol' => 1,
                'optgroup' => 0,
                'option' => 0,
                'p' => 1,
                'param' => 1,
                'pre' => 1,
                'q' => 1,
                'rb' => 1,
                'rbc' => 1,
                'rp' => 1,
                'rt' => 1,
                'rtc' => 1,
                'ruby' => 1,
                's' => 1,
                'samp' => 1,
                'select' => 0,
                'small' => 1,
                'span' => 1,
                'strike' => 1,
                'strong' => 1,
                'sub' => 1,
                'sup' => 1,
                'table' => 1,
                'tbody' => 1,
                'td' => 1,
                'textarea' => 0,
                'tfoot' => 1,
                'th' => 1,
                'thead' => 1,
                'tr' => 1,
                'tt' => 1,
                'u' => 1,
                'ul' => 1,
                'var' => 1,
                'embed' => 0,
                'object' => 0,
        );
    }

	/**
	 * Returns a reference to a global htmlFilter object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>$htmlFilter =& htmlFilter::getInstance();</pre>
	 *
	 * @static
	 * @return	object	The sanitizer object.
	 * @since	1.2.0
	 */
    function &getInstance()
    {
        static $instance;

        if (!$instance) {
            $instance = new htmlFilter();
        }

        return $instance;
    }

    function setElement( $name, $value ) {
        $this->_elements[$name] = $value;
    }

    function setAndMark( $bool ) {
        $this->_and_mark = $bool;
    }


    /**
    * This function filters the HTML and attempts to clean up invalid markup.
    *
    * @param    string  $str            HTML to check
    * @return   string                  Filtered HTML
    *
    */
    function filterHTML( $str )
    {
        global $_CONF, $htmlconfig;

        if( isset( $_CONF['skip_html_filter_for_root'] ) &&
                 ( $_CONF['skip_html_filter_for_root'] == 1 ) &&
                SEC_inGroup( 'Root' )) {
            return $str;
        }

        $elements = $this->_elements;

        $str = htmLawed($str,array( 'safe' => 1,
                                    'and_mark' => $this->_and_mark,
                                    'show_setting' => 'htmlconfig',
                                    array('elements' => $elements),
                                    'balance' => 1,
                                    'valid_xhtml' => 0
                                    )
                        );
        return $str;
    }



    /**
    * This function returns a formatted block of code, either text or html
    *
    * @param    string  $str            block of text to format
    * @param    string  $mode           Text or HTML
    * @return   string                  Formatted text block
    *
    */
    function formatBlock($str,$mode='text') {
        global $_CONF;

        $bbcode = new StringParser_BBCode ();

        $bbcode->setGlobalCaseSensitive (false);

        if ( strtoupper($mode) == 'HTML' ) {
            $bbcode->addParser(array('block','inline','link','listitem'), array('htmlFilter','filterHTML'));
            $bbcode->addCode ('code', 'usecontent', array('htmlFilter','_do_bbcode_code'), array ('usecontent_param' => 'default'),
                              'code', array ('listitem', 'block', 'inline', 'link'), array ());
            $bbcode->addCode ('raw', 'usecontent', array('htmlFilter','_do_bbcode_raw'), array ('usecontent_param' => 'default'),
                              'raw', array ('listitem', 'block', 'inline', 'link'), array ());
        } else {
            $bbcode->addParser (array ('block', 'inline', 'link', 'listitem'), array ('htmlFilter', '_bbcode_htmlspecialchars'));
        }

        $bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
        $bbcode->setCodeFlag ('*', 'paragraphs', true);

        $bbcode->setRootParagraphHandling (true);

        $str = $bbcode->parse ($str);

        return $str;
    }

    function _do_bbcode_raw($action, $attributes, $content, $params, $node_object) {
        if ( $action == 'validate') {
            return true;
        }
        $codeblock = '<!--raw--><span class="raw">'  . @htmlspecialchars($content,ENT_QUOTES, COM_getCharset()) . '</span><!--/raw-->';

        $codeblock = str_replace('{','&#123;',$codeblock);
        $codeblock = str_replace('}','&#125;',$codeblock);

        return $codeblock;
    }

    function _do_bbcode_code($action, $attributes, $content, $params, $node_object) {
        if ( $action == 'validate') {
            return true;
        }
        $codeblock = '<pre><code>'  . @htmlspecialchars($content,ENT_QUOTES, COM_getCharset()) . '</code></pre>';

        $codeblock = str_replace('{','&#123;',$codeblock);
        $codeblock = str_replace('}','&#125;',$codeblock);

        return $codeblock;
    }


    function _bbcode_htmlspecialchars($text) {

        return (@htmlspecialchars ($text,ENT_QUOTES, COM_getCharset()));
    }

}
?>