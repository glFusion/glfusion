<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | sanitize.class.php                                                       |
// |                                                                          |
// | glFusion data filtering or sanitizing class library.                     |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2009 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on sanitize.class.php included in the Geeklog CMS                  |
// | Also based on the lib-scrub.php developed by Joe Mucchiello (c) 2008     |
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

/*
 * This class is designed to filter input or data.  Generally,
 * it will be used to filter $_POST, $_GET, $_REQUEST, $_COOKIE,
 * and $_ENV data.
 *
 * Filtering is limited to removing bad things (like scripts,
 * when the data type if HTML, or ensuring proper typed data
 * is returned (int, float, etc.).
 *
 * Input filtering does not perform functions such as
 * censoring (replacing bad words), template fixes (replacing
 * {} characters).  The class supports these functions, but they
 * are not automatically called on input.  The programmer must
 * make subsequent calls to perform these levels of filtering.
 *
 * All $_POST, $_GET, $_REQUEST, $_COOKIE, and $_ENV data
 * is automatically run through stripslashes() if necessary.
 * There should never be a reason for a programmer to call
 * stripslashes() outside of this function.
 *
 * Supported data types:
 *
 * Integer - will return an int
 * Float   - return float value
 * Strict  - returns a fully filtered input, generally used
 *           for single word items like mode, etc.  Will not
 *           contain quotes or any other DB dangerous code
 * Plain   - returns a text block that has been htmlencoded
 *           safe for display
 * HTML    - returns filtered HTML (supports code / raw blocks)
 * Raw     - returns as-is input, no filtering!
 * Boolean - returns 0 or 1
 * URL     - Returns a valid URL
 *
 *
 */

class sanitize
{
    private $_post   = array();
    private $_get    = array();
    private $_cookie = array();
    private $_env    = array();
    private $_request = array();
    private $_rewriteEnabled = false;
    private $_arguments = array();

    /**
     * Constructor, initializes the internal variable arrays
     * calls stripslashes on all input if necessary.
     */

    public function __construct()
    {
        global $_CONF;

        if((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase'))!="off")) ){
            $this->_stripslashes_deep($_GET);
            $this->_stripslashes_deep($_POST);
            $this->_stripslashes_deep($_COOKIE);
            $this->_stripslashes_deep($_ENV);
            $this->_stripslashes_deep($_REQUEST);
        }
        $this->_post   = &$_POST;
        $this->_get    = &$_GET;
        $this->_cookie = &$_COOKIE;
        $this->_env    = &$_ENV;
        $this->_request = &$_REQUEST;
        $this->_rewriteEnabled = $_CONF['url_rewrite'];
        if ( $this->_rewriteEnabled == 1 ) {
            $this->_getArguments();
        }
    }

	/**
	 * Returns a reference to a global sanitizer object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>$inputHandle =& sanitize::getInstance();</pre>
	 *
	 * @static
	 * @return	object	The sanitizer object.
	 * @since	1.2.0
	 */
    function &getInstance()
    {
        static $instance;

        if (!$instance) {
            $instance = new sanitize();
        }

        return $instance;
    }

    function getRawVars($type)
    {
        switch ($type) {
            case 'post' :
                return $this->_post;
                break;
            case 'get' :
                return $this->_get;
                break;
            case 'request' :
                return $this->_request;
                break;
            case 'env' :
                return $this->_env;
                break;
            case 'cookie' :
                return $this->_cookie;
                break;
            default :
                return '';
        }
    }

	/**
	 * Retrieves a $_POST, $_GET, $_REQUEST, $_ENV, or $_COOKIE variable.
	 *
	 * @param  string   $type    Type of data to retrieve
	 *                             - int, float, strict, plain, html, free, ...
	 * @param  string   $key     Array key
	 * @param  array    $mode    Type of data; post, get, request, env, cookie
	 *                           must specify in order of precedence
	 * @param  string   $default Default return value if not found
	 *
	 * @note This will return the first item found when passing an array of
	 *       modes. If you pass array('get','post') and the $_GET variable is
	 *       set, the $_GET variable will be returned.
     *
	 * @access public
	 * @return varible
	 */
    function getVar ( $type, $key, $mode=array('post'), $default='' )
    {
        if ( !is_array($mode) ) {
            $mode = array($mode);
        }
        foreach($mode AS $method) {
            switch (strtoupper($method) ) {
                case 'POST' :
                    $vData = $this->_post;
                    break;
                case 'GET' :
                    $vData = $this->_get;
                    break;
                case 'REQUEST' :
                    $vData = $this->_request;
                    break;
                case 'ENV' :
                    $vData = $this->_env;
                    break;
                case 'COOKIE' :
                    $vData = $this->_cookie;
                    break;
                case 'default' :
                    $vData = $this->_post;
                    break;
            }
            if (array_key_exists($key, $vData)) {
                return $this->filterVar($type,$vData[$key],'',$default);
            }
        }
        return $default;
    }


	/**
	 * Filters and returns a variable
	 *
	 * @param  string   $type    Type of data to retrieve
	 *                             - int, float, strict, plain, html, free, ...
	 * @param  string   $key     Array key or data to filter
	 * @param  array    $A       Array of data to filter
	 * @param  array    $mode    Type of data; post, get, request, env, cookie
	 * @param  string   $default Default return value if not found
     *
	 * @access public
	 * @return varible
	 *
	 * @note Pass only $key to filter a single variable
	 */
    function filterVar( $type, $key, $A, $default='' )
    {
        $data = null;
        if ($A === null || $A === '') {
            $data = $key;
        } elseif ($key === null || $key === '' ) {
            $data = $A;
        } elseif (array_key_exists($key, $A)) {
            $data = $A[$key];
        }
        if (($data === null || $data === '' ) && $default != '') {
            return $default;
        } elseif (is_array($data)) {
            return($this->_sanitizeArray($type,$data,$default));
        }
        switch ( strtoupper($type) ) {
            case 'INT' :
            case 'INTEGER' :
				return @intval($data);
                break;
            case 'FLOAT' :
            case 'DOUBLE' :
				return @doubleval($data);
                break;
            case 'STRICT' :
                return $this->_applyFilter($data);
                break;
            case 'PLAIN' :
            case 'TEXT'  :
            case 'PLAINTEXT' :
                $data = strip_tags($data);
                return @htmlspecialchars ($data,ENT_QUOTES, COM_getEncodingt());
                break;
            case 'HTML' :
                $htmlFilter =& htmlFilter::getInstance();
                return $htmlFilter->filterHTML($data);
                break;
            case 'RAW' :
                return $data;
                break;
            case 'BOOL' :
            case 'BOOLEAN' :
                if (!isset($default) || $default === '' ) {
                    $default = 0;
                }
                if (intval($data) != 0)
                    return true; // Make any non-zero numeric value true
                $data = MBYTE_strtolower($this->_applyFilter($data));
                if (strlen($data) == 0)
                    return $default;
                if ($data === 0)
                    return false;
                if (in_array($data, array('yes','on','true')))
                    return true;
                if (in_array($data, array('no','off','false')))
                    return false;
                return $default;
                break;
            case 'URL' :
                return $this->_sanitizeUrl($data,$default);
                break;
            case 'SQL' :
                return mysql_real_escape_string ($data);
                break;
            case 'FILENAME' :
                return $this->_sanitizeFilename($data,false);
                break;
            default :
                return (@htmlspecialchars ($data,ENT_QUOTES, COM_getEncodingt()));
                break;
        }
    }


    /** buttonCheck -- Find which button was pressed on form.
     *
     *  Instead of doing this:
     *      <input type="submit" value="{lang_save}" name="mode">
     *      <input type="submit" value="{lang_cancel}" name="mode">
     *
     *  and checking
     *      if ($_POST['mode'] == $LANG_ADMIN['save']) ...
     *
     *  Do this:
     *      <input type="submit" value="{lang_save}" name="save">
     *      <input type="submit" value="{lang_cancel}" name="cancel">
     *
     *  and check this way:
     *      $mode = $inputHandler->buttonCheck(array('save','cancel'), $_POST, '');
     *      if ($mode == 'save') ...
     *
     *  @param  string  $buttonlist     List of buttons.
     *  @param  array   $A              Array such as $_GET, $_POST, $_COOKIE or $_REQUEST
     *  @param  mixed   $default        If no entry is in the array, return this default value.
     *  @param  boolean $return_name    If true, the returned name is the name in the buttonlist
     *                                  If false, the return value is the key of the name in
     *                                  the buttonlist
     *  @return mixed                   The given button or the associated key of the button
     */
    function buttonCheck($buttonList, $A, $default = '', $return_name = true)
    {
        if (!is_array($buttonList) && !is_array($A)) {
            return false;
        }
        foreach ($buttonList as $optionalreturn => $button) {
            if (array_key_exists($button, $A)) {
                return ($return_name ? $button : $optionalreturn);
            }
        }
        return $default;
    }

    // URL Rewrite

    /**
    * Grabs any variables from the query string
    *
    * @access   private
    */
    function _getArguments()
    {
        if (isset ($_SERVER['PATH_INFO'])) {
            if ($_SERVER['PATH_INFO'] == '')
            {
                if (isset ($_ENV['ORIG_PATH_INFO']))
                {
                    $this->_arguments = explode('/', $_ENV['ORIG_PATH_INFO']);
                } else {
                    $this->_arguments = array();
                }
            } else {
                $this->_arguments = explode ('/', $_SERVER['PATH_INFO']);
            }
            array_shift ($this->_arguments);
        } else if (isset ($_ENV['ORIG_PATH_INFO'])) {
            $this->_arguments = explode('/', substr($_ENV['ORIG_PATH_INFO'],1));
        } else {
            $this->_arguments = array ();
        }
    }

    /**
    * Returns the number of variables found in query string
    *
    * This is particularly useful just before calling setArgNames() method
    *
    * @return   int     Number of arguments found in URL
    *
    */
    function numArguments()
    {
        return count($this->_arguments);
    }


    /**
    * Assigns logical names to query string variables
    *
    * @param        array       $names      String array of names to assign to variables pulled from query string
    * @return       boolean     true on success otherwise false
    *
    */
    function setArgNames($names)
    {
        if (count($names) < count($this->_arguments)) {
            print "URL Class: number of names passed to setArgNames must be equal or greater than number of arguments found in URL";
            exit;
        }
        if (is_array($names)) {
            $newArray = array();
            for ($i = 1; $i <= count($this->_arguments); $i++) {
                $newArray[current($names)] = current($this->_arguments);
                next($names);
		        next($this->_arguments);
            }
            $this->_arguments = $newArray;
            reset($this->_arguments);
        } else {
            return false;
        }
        // move these into the $_get array
        if ( is_array($this->_arguments) ) {
            foreach ($this->_arguments AS $name => $value ) {
                if ( !isset($_get[$name]) ) {
                    if((function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || (ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase'))!="off")) ){
                        $this->_get[$name] = stripslashes($value);
                    } else {
                        $this->_get[$name] = $value;
                    }
                }
            }
        }
        return true;
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
    function _sanitizeFilename($filename, $allow_dots = false)
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
    * Filter variable
    *
    * @param    string    $parameter   the parameter to test
    * @param    boolean   $isnumeric   true if $parameter is supposed to be numeric
    * @return   string    the filtered parameter (may now be empty or 0)
    *
    */
    function _applyFilter($parameter, $isnumeric = false)
    {
        $p = strip_tags( $parameter );
        $p = $this->_killJS( $p );

        if( $isnumeric ) {
            // Note: PHP's is_numeric() accepts values like 4e4 as numeric
            if( !is_numeric( $p ) || ( preg_match( '/^-?\d+$/', $p ) == 0 )) {
                $p = 0;
            }
        } else {
            $p = preg_replace( '/\/\*.*/', '', $p );
            $pa = explode( "'", $p );
            $pa = explode( '"', $pa[0] );
            $pa = explode( '`', $pa[0] );
            $pa = explode( ';', $pa[0] );
            $pa = explode( ',', $pa[0] );
            $pa = explode( '\\', $pa[0] );
            $p = $pa[0];
        }

        return $p;
    }



    /**
    *  Takes some amount of text and replaces all javascript events on*= with in
    *
    *  This script takes some amount of text and matches all javascript events, on*= (onBlur= onMouseClick=)
    *  and replaces them with in*=
    *  Essentially this will cause onBlur to become inBlur, onFocus to be inFocus
    *  These are not valid javascript events and the browser will ignore them.
    * @param    string  $Message    Text to filter
    * @return   string  $Message with javascript filtered
    * @see  _censor
    * @see  _checkHTML
    *
    */
    function _killJS( $text )
    {
        return( preg_replace( '/(\s)+[oO][nN](\w*) ?=/', '\1in\2=', $text ));
    }


    /**
    * Sanitize a URL
    *
    * @param    string  $url                URL to sanitized
    * @param    array   $allowed_protocols  array of allowed protocols
    * @param    string  $default_protocol   replacement protocol (default: http)
    * @return   string                      sanitized URL
    *
    */
    function _sanitizeUrl( $url, $allowed_protocols = array('http','https','ftp'), $default_protocol = 'http' )
    {
        if( !is_array( $allowed_protocols )) {
            $allowed_protocols = array( $allowed_protocols );
        }

        if( empty( $default_protocol )) {
            $default_protocol = 'http:';
        } else if( substr( $default_protocol, -1 ) != ':' ) {
            $default_protocol .= ':';
        }

        $url = strip_tags( $url );
        if( !empty( $url )) {
            $pos = MBYTE_strpos( $url, ':' );
            if( $pos === false ) {
                $url = $default_protocol . '//' . $url;
            } else {
                $protocol = MBYTE_substr( $url, 0, $pos + 1 );
                $found_it = false;
                foreach( $allowed_protocols as $allowed ) {
                    if( substr( $allowed, -1 ) != ':' ) {
                        $allowed .= ':';
                    }
                    if( $protocol == $allowed ) {
                        $found_it = true;
                        break;
                    }
                }
                if( !$found_it ) {
                    $url = $default_protocol . MBYTE_substr( $url, $pos + 1 );
                }
            }
        }

        return $url;
    }


    /**
    * Prepare data for DB use
    *
    * @param    string  $str                Data to be sanitized
    * @return   string                      Escaped string
    *
    */
    function prepareForDB($str) {
        return mysql_real_escape_string ($str);
    }


    /**
    * This censors inappropriate content
    *
    * This will replace 'bad words' with something more appropriate
    *
    * @param        string      $Message        String to check
    * @see function checkHTML
    * @return       string      Edited $Message
    *
    */
    function censor( $Message )
    {
        global $_CONF;

        $EditedMessage = $Message;

        if( $_CONF['censormode'] != 0 ) {
            if( is_array( $_CONF['censorlist'] )) {
                $Replacement = $_CONF['censorreplace'];

                switch( $_CONF['censormode']) {
                    case 1: # Exact match
                        $RegExPrefix = '(\s*)';
                        $RegExSuffix = '(\W*)';
                        break;

                    case 2: # Word beginning
                        $RegExPrefix = '(\s*)';
                        $RegExSuffix = '(\w*)';
                        break;

                    case 3: # Word fragment
                        $RegExPrefix   = '(\w*)';
                        $RegExSuffix   = '(\w*)';
                        break;
                }
                foreach ($_CONF['censorlist'] as $c) {
                    if (!empty($c)) {
                        $EditedMessage = MBYTE_eregi_replace($RegExPrefix . $c
                            . $RegExSuffix, "\\1$Replacement\\2", $EditedMessage);
                    }
                }
            }
        }

        return $EditedMessage;
    }


    function fixTemplate($text) {
        $text = str_replace('{','&#123;',$text);
        $text = str_replace('}','&#125;',$text);

        return $text;
    }


    static function _stripslashes_deep(&$value)
    {
        $value = is_array($value) ?
                    array_map(array('sanitize','_stripslashes_deep'), $value) :
                    stripslashes($value);

        return $value;
    }


    function _sanitizeArray($type,&$A, $default)
    {
        if (is_array($A)) { // handle sub-arrays
            $ret = array();
            foreach($A as $k => $v) {
                if (is_numeric($k)) {
                    $key = intval($k);
                } else {
                    $key = $this->_applyFilter($k);     // not sure we really want to do this here
                }
                $value = $this->filterVar($type,$v,null,$default);
                if (empty($key) && $key !== 0) {
                    $ret[] = $value;
                } else {
                    $ret[$key] = $value;
                }
            }
            return $ret;
        }
        return array();
    }


    function _getEncodingt() {
    	global $_CONF, $LANG_CHARSET;

    	static $encoding = null;

        $valid_charsets = array('iso-8859-1','iso-8859-15','utf-8','cp866','cp1251','cp1252','koi8-r','big5','gb2312','big5-hkscs','shift_jis sjis','euc-jp');

    	if ($encoding === null) {
    		if (isset($LANG_CHARSET)) {
    			$encoding = $LANG_CHARSET;
    		} else if (isset($_CONF['default_charset'])) {
    			$encoding = $_CONF['default_charset'];
    		} else {
    			$encoding = 'iso-8859-1';
    		}
    	}

    	$encoding = strtolower($encoding);

    	if ( in_array($encoding,$valid_charsets) ) {
    	    return $encoding;
    	} else {
    	    return 'iso-8859-1';
    	}

    	return $encoding;
    }

}
?>