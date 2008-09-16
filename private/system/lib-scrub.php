<?php
/* Reminder: always indent with 4 spaces (no tabs). */
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-scrub.php                                                            |
// |                                                                          |
// | Web and DB scrubbing functions.                                          |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Joe Mucchiello         jmucchiello AT yahoo DOT com                      |
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
//

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/** The SCRUB library is designed to clean data sent to the script via the web.
 *  In most cases the actually scrubbing takes place in one or two functions
 *  chained together so what is the benefit of the libary:
 *
 *   * Avoid extraneous isset() calls -- Code such as this becomes a single
 *      line of code:
 *
 *          $mode = '';
 *          if (isset($_GET['mode'])) {
 *              $mode = COM_applyFilter($_GET['mode']);
 *          }
 *          if ($mode != 'quickadd' && $mode != 'personal') {
 *              $mode = '';
 *          }
 *
 *                          -- OR --
 *
 *          $mode = SCRUB_modeText('mode', $_GET, '', Array('quickadd', 'personal'));
 *
 *   * Performs simple validations automatically -- As seen in the above
 *      example, not only is mode assign in one line of code, it is also
 *      only in one of three known states: 'quickadd', 'personal' or ''.
 *
 */


/** SCRUB_integer -- Get an integer from the web parameters
 *
 *  Usage:  $page = SCRUB_integer('page', $_GET, 1);
 *
 *  @param  string  $key        Key to the array.
 *  @param  array   $A          Array such as $_GET, $_POST, $_COOKIE or $_REQUEST
 *  @param  mixed   $default    If key is not in the array, return the default value.
 *  @return integer             A scrubbed integer
 */
function SCRUB_integer($key, &$A, $default = 0)
{
    $data = null;
    if ($A === null) { // allow cleaning of non-array data
        $data = $key;
    } elseif ($key === null) {
        $data = $A;
    } elseif (array_key_exists($key, $A)) {
        $data = $A[$key];
    }
    if ($data === null) {
        return $default;
    } elseif (is_array($data)) { // handle sub-arrays
        return SCRUB_array($data, $default, 'SCRUB_integer');
    }

    return intval($data);
}

/** SCRUB_boundInteger -- Get an integer from the web parameters which must be between a low and high threshhold.
 *
 *  Usage:  $month = SCRUB_boundInteger('mon', $_GET, array(0,1,12), 1, 12);
 *
 *  @param  string  $key        Key to the array.
 *  @param  array   $A          Array such as $_GET, $_POST, $_COOKIE or $_REQUEST
 *  @param  array   $default    Array of defaults. $default[0] is used if the
 *                               key is not found, $default[1] is used if the
 *                               value is too low, $default[2] is used if the
 *                               value is too high. If  is null
 *                               the low and high threshholds are their own
 *                               defaults.
 *  @param  integer $low        The lower bound
 *  @param  integer $high       The upper bound
 *  @return integer             A scrubbed integer
 */
function SCRUB_boundInteger($key, &$A, $default, $low, $high)
{
    $data = null;
    if ($A === null) { // allow cleaning of non-array data
        $data = $key;
    } elseif ($key === null) {
        $data = $A;
    } elseif (array_key_exists($key, $A)) {
        $data = $A[$key];
    }
    if ($data === null) {
        if (!is_array($default)) {
            return $default[0];
        } else {
            return $default;
        }
    } elseif (is_array($data)) { // handle sub-arrays
        return SCRUB_array($data, $default, 'SCRUB_boundInteger', $low, $high);
    }

    $value = intval($data);
    if ($low !== null && $value < $low) {
        if (is_array($default) && isset($default[1])) $value = $default[1];
        else $value = $low;
    } elseif ($high !== null && $value > $high) {
        if (is_array($default) && isset($default[2])) $value = $default[2];
        else $value = $low;
    }
    return $value;
}


/** SCRUB_float -- Get an float from the web parameters
 *
 *  Usage:  $zoom = SCRUB_float('zoom', $_REQUEST, 1.0);
 *
 *  @param  string  $key        Key to the array.
 *  @param  array   $A          Array such as $_GET, $_POST, $_COOKIE or $_REQUEST
 *  @param  mixed   $default    If key is not in the array, return the default value.
 *  @return float               A scrubbed float
 */
function SCRUB_float($key, &$A, $default = 0.0)
{
    $data = null;
    if ($A === null) { // allow cleaning of non-array data
        $data = $key;
    } elseif ($key === null) {
        $data = $A;
    } elseif (array_key_exists($key, $A)) {
        $data = $A[$key];
    }
    if ($data === null) {
        return $default;
    } elseif (is_array($data)) { // handle sub-arrays
        return SCRUB_array($data, $default, 'SCRUB_float');
    }

    return floatval($data);
}

/** SCRUB_strictText -- Get text from the web parameters restricted with
 *                       COM_applyFilter
 *
 *  Usage:  $page = SCRUB_strictText('page', $_GET, 1);
 *
 *  @param  string  $key        Key to the array.
 *  @param  array   $A          Array such as $_GET, $_POST, $_COOKIE or $_REQUEST
 *  @param  mixed   $default    If key is not in the array, return the default value.
 *  @return integer             A scrubbed integer
 */
function SCRUB_strictText($key, &$A, $default = '')
{
    $data = null;
    if ($A === null) { // allow cleaning of non-array data
        $data = $key;
    } elseif ($key === null) {
        $data = $A;
    } elseif (array_key_exists($key, $A)) {
        $data = $A[$key];
    }
    if ($data === null) {
        return $default;
    } elseif (is_array($data)) { // handle sub-arrays
        return SCRUB_array($data, $default, 'SCRUB_strictText');
    }

    return COM_applyFilter($data);
}

function SCRUB_modeText($key, &$A, $default = '', $modes_available = Array(), $force_lower = false)
{
    $data = null;
    if ($A === null) { // allow cleaning of non-array data
        $data = $key;
    } elseif ($key === null) {
        $data = $A;
    } elseif (array_key_exists($key, $A)) {
        $data = $A[$key];
    }
    if ($data === null) {
        return $default;
    } elseif (is_array($data)) { // handle sub-arrays
        return SCRUB_array($data, $default, 'SCRUB_modeText', $modes_available, $force_lower);
    }

    $data = COM_applyFilter($data);
    if ($force_lower) {
        $data = strlower($data);
    }
    if (in_array($data, $modes_available)) {
        return $data;
    }
    return $default;
}

function SCRUB_plainText($key, &$A, $default = '')
{
    $data = null;
    if ($A === null) { // allow cleaning of non-array data
        $data = $key;
    } elseif ($key === null) {
        $data = $A;
    } elseif (array_key_exists($key, $A)) {
        $data = $A[$key];
    }
    if ($data === null) {
        return $default;
    } elseif (is_array($data)) { // handle sub-arrays
        return SCRUB_array($data, $default, 'SCRUB_plainText');
    }

    return COM_checkWords(strip_tags(COM_stripslashes($data)));
}

function SCRUB_htmlText($key, &$A, $default = '')
{
    $data = null;
    if ($A === null) { // allow cleaning of non-array data
        $data = $key;
    } elseif ($key === null) {
        $data = $A;
    } elseif (array_key_exists($key, $A)) {
        $data = $A[$key];
    }
    if ($data === null) {
        return $default;
    } elseif (is_array($data)) { // handle sub-arrays
        return SCRUB_array($data, $default, 'SCRUB_htmlText');
    }

    return COM_checkWords(COM_checkHTML($data)); // checkHtml calls stripslashes
}

function SCRUB_freeText($key, &$A, $default = '')
{
    $data = null;
    if ($A === null) { // allow cleaning of non-array data
        $data = $key;
    } elseif ($key === null) {
        $data = $A;
    } elseif (array_key_exists($key, $A)) {
        $data = $A[$key];
    }
    if ($data === null) {
        return $default;
    } elseif (is_array($data)) { // handle sub-arrays
        return SCRUB_array($data, $default, 'SCRUB_freeText');
    }

    return COM_stripslashes($data);
}

function SCRUB_boolean($key, &$A, $default = false)
{
    $data = null;
    if ($A === null) { // allow cleaning of non-array data
        $data = $key;
    } elseif ($key === null) {
        $data = $A;
    } elseif (array_key_exists($key, $A)) {
        $data = $A[$key];
    }
    if ($data === null) {
        return $default;
    } elseif (is_array($data)) { // handle sub-arrays
        return SCRUB_array($data, $default, 'SCRUB_boolean');
    }

    if (intval($data) != 0) return true; // Make any non-zero numeric value true
    $data = strtolower(COM_applyFilter($data));
    if (strlen($data) == 0) return $default;
    if ($data == 0) return false;
    if (in_array($data, array('yes','on','true'))) return true;
    if (in_array($data, array('no','off','false'))) return false;
    return $default;
}

/** SCRUB_buttonCheck -- Find which button was pressed on form.
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
 *      $mode = SCRUB_buttonCheck(array('save','cancel'), $_POST, '');
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
function SCRUB_buttonCheck($buttonList, $A, $default = '', $return_name = true)
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



/** Internal function for cleaning args passed as arrays
 *
 *  Usage: $perm_owner = SCRUB_boundInteger('perm_owner', $_POST, Array(0,0), 0, 2);
 *
 *  If you have your own scrubber function, this function can be used as follows:
 *
 *  function SCRUB_mydata($key, $A, $default)
 *  {
 *      $data = null;
 *      if ($A === null) { // allow cleaning of non-array data
 *          $data = $key;
 *      } elseif ($key === null) {
 *          $data = $A;
 *      } elseif (array_key_exists($key, $A)) {
 *          $data = $A[$key];
 *      }
 *      if ($data === null) {
 *          return $default;
 *      } elseif (is_array($data)) { // handle sub-arrays
 *          return SCRUB_array($data, $default, 'SCRUB_mydata');
 *      }
 *
 *      // perform scrubbing here.
 *      return ****scrubbed data****;
 *  )
 *
 *
 *  @param  array   $A          Array to scrub
 *  @param  mixed   $default    Default value if no data passed
 *  @param  functor $scrubber   The function to scrub
 *  vararg  mixed               Any additional parameters are appended to the
 *                               $scrubber function when it is called.
 *  @return array               The scrubbed data
 */
function SCRUB_array(&$A, $default, $scrubber)
{
    if (is_array($A)) { // handle sub-arrays
        // additional args
        $args = Array(null, // place holder for data
                      null, // called with no array
                      $default); // the third parameter is always the default
        for ($i = 3; $i < func_num_args(); ++$i) {
            $args[] = func_get_arg($i); // append extras to the end
        }
        $ret = Array();
        foreach($A as $k => $v) {
            if (is_numeric($k)) {
                $key = intval($k);
            } else {
                $key = COM_applyFilter($k);
            }
            $args[0] = $v; // set data value
            $value = call_user_func_array($scrubber, $args);
            if (empty($key) && $key !== 0) {
                $ret[] = $value;
            } else {
                $ret[$key] = $value;
            }
        }
        return $ret;
    }
    return Array();
}

/**
 *  lib-quote -- DB safe data tools.
 *
 *  The object here is to have a uniform set of functions that can be used to
 *   create safe SQL statements. The object here is provide a database
 *   agnostic set of methods for generating various parts of a SQL statement.
 *
 *  NOTE: Not all parts of the example are coded (QUOTE_func and the SELECT
 *        series specifically)
 *
 *  Usage:
 *  $A = Array(
 *          'bid' => QUOTE_int($bid),
 *          'name' => QUOTE_text($name),
 *          'is_enabled' => QUOTE_boolean($enabled),
 *          'last_run' => QUOTE_func(QF_CURRENT_TIMESTAMP),
 *          'content' => QUOTE_bigtext($content)
 *  );
 *  DB_save($_TABLES['block'], implode(',', array_keys($A)), implode(',', $A));
 *
 *
 *  $A = Array('bid',
 *             'name',
 *             SELECT_boolean('is_enabled'),
 *             SELECT_timestamp('last_run', SF_UNIXTIME),  // return as unixtime
 *             SELECT_bigtext('content'));
 *  $res = DB_query('SELECT ' . implode(',',$A) .
 *                  ' FROM ' . $_TABLES['block'] ....);
 *
 */



/**
 *  QUOTE_text -- prepare text for inclusion in a sql statement.
 *
 *  @param  string  text    The text to quote
 *  @return string          Quoted text inside a quotes.
 */
function QUOTE_text($text)
{
    return '\''.addslashes($text).'\'';
}

/**
 *  QUOTE_bigtext -- prepare blob text for inclusion in a sql statement.
 *
 *  @param  string  text    The text to quote
 *  @return string          Quoted text inside a quotes.
 */
function QUOTE_bigtext($text)
{
    return '\''.addslashes($text).'\'';
}

/**
 *  QUOTE_int -- prepare number for inclusion in a sql statement.
 *
 *  @param  string  n       The number
 *  @return int             Forces the text to int
 */
function QUOTE_int($n)
{
    return intval($n);
}

/**
 *  QUOTE_int -- prepare number for inclusion in a sql statement.
 *
 *  @param  string  b       The boolean
 *  @return int             Forces the value to either 1 or 0
 */
function QUOTE_boolean($b)
{
    return $b ? 1 : 0;
}



?>