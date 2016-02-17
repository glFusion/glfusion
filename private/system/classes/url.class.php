<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | url.class.php                                                            |
// |                                                                          |
// | class to allow for spider friendly URL's                                 |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2010-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2002-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs       - tony@tonybibbs.com                           |
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

/**
* This class will allow you to use friendlier URL's, like:
* http://www.example.com/index.php/arg_value_1/arg_value_2/ instead of
* uglier http://www.example.com?arg1=value1&arg2=value2.
* NOTE: this does not currently work under windows as there is a well documented
* bug with IIS and PATH_INFO.  Not sure yet if this will work with windows under
* apache.  This was built so you could use this class and just disable it
* if you are an IIS user.
*
* @author       Tony Bibbs <tony@tonybibbs.com>
*
*/
class url {
    /**
    * @access private
    */
    var $_arguments = array();		// Array of argument names
    /**
    * @access private
    */
    var $_enabled = true;

    /**
    * Constructor
    *
    * @param        boolean     $enabled    whether rewriting is enabled
    *
    */
    function __construct($enabled=true)
    {
        $this->setEnabled($enabled);
        $this->_arguments = array();
        if ($this->_enabled) {
            $this->_getArguments();
        }
    }

    /**
    * Grabs any variables from the query string
    *
    * @access   private
    */
    function _getArguments()
    {
        if (isset ($_SERVER['PATH_INFO'])) {
            if ($_SERVER['PATH_INFO'] == '') {
                if (isset ($_ENV['ORIG_PATH_INFO'])) {
                    $this->_arguments = explode('/', $_ENV['ORIG_PATH_INFO']);
                } else {
                    $this->_arguments = array();
                }
            } else {
                $this->_arguments = explode ('/', $_SERVER['PATH_INFO']);
            }
            array_shift ($this->_arguments);
            if ( $this->_arguments[0] == substr($_SERVER['SCRIPT_NAME'],1) ) {
                array_shift($this->_arguments);
            }
        } else if (isset ($_ENV['ORIG_PATH_INFO'])) {
            $scriptName = array();
            $scriptName = explode('/',$_SERVER['SCRIPT_NAME']);
            array_shift($scriptName);
            $this->_arguments = explode('/', substr($_ENV['ORIG_PATH_INFO'],1));
            if ( $this->_arguments[0] == $scriptName[0] ) {
                $this->_arguments = array();
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $this->_arguments = explode('/', substr($_SERVER['ORIG_PATH_INFO'], 1));
            array_shift ($this->_arguments);
            $script_name = strrchr($_SERVER['SCRIPT_NAME'],"/");
            if ( $script_name == '' ) {
                $script_name = $_SERVER['SCRIPT_NAME'];
            }
            $indexArray = 0;
            $search_script_name = substr($script_name,1);
            if ( array_search($search_script_name,$this->_arguments) !== FALSE ) {
                $indexArray = array_search($search_script_name,$this->_arguments);
            }
            for ($x=0; $x < $indexArray; $x++) {
                array_shift($this->_arguments);
            }
            if ( isset($this->_arguments[0]) && $this->_arguments[0] == substr($script_name,1) ) {
                array_shift($this->_arguments);
            }
        } else {
            $this->_arguments = array ();
        }
    }

    /**
    * Enables url rewriting, otherwise URL's are passed back
    *
    * @param        boolean     $switch     turns URL rewriting on/off
    *
    */
    function setEnabled($switch)
    {
        if ($switch) {
            $this->_enabled = true;
        } else {
            $this->_enabled = false;
        }
    }

    /**
    * Returns whether or not URL rewriting is enabled
    *
    * @return   boolean true if URl rewriting is enabled, otherwise false
    *
    */
    function isEnabled()
    {
        return $this->_enabled;
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
    function setArgNames($names = array())
    {
        if (count($names) < count($this->_arguments)) {
            print "URL Class: number of names passed to setArgNames must be equal or greater than number of arguments found in URL (" . count($this->_arguments) . ")";
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
        return true;
    }

    /**
    * Gets the value for an argument
    *
    * @param        string      $name       Name of argument to fetch value for
    * @return       mixed       returns value for a given argument
    *
    */
    function getArgument($name)
    {
        // if in GET VARS array return it
        if (!empty($_GET[$name])) {
            return $_GET[$name];
        }

        // ok, pull from query string
        if (in_array($name,array_keys($this->_arguments))) {
            return $this->_arguments[$name];
        }

        return '';
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
        if (!$this->isEnabled()) {
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
}

?>