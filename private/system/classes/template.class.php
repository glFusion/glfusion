<?php
/**
* glFusion CMS
*
* Template Engine
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 207-2020 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Caching and logic processing developed by:
*  (C) Copyright 2007-2009 Joe Mucchiello - joe AT throwingdice DOT com
*
*  Based on phpLib Template Library
*  (C) Copyright 1999-2000 NetUSE GmbH
*                  Kristian Koehntopp
*   Bug fixes to version 7.2c compiled by
*            Richard Archer <rha@juggernaut.com.au>:
*   (credits given to first person to post a diff to phplib mailing list)
*
*/

#namespace glFusion;

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

use \glFusion\Cache\Cache;
use \glFusion\Log\Log;

/**
* The template class allows you to keep your HTML code in some external files
* which are completely free of PHP code, but contain replacement fields.
* The class provides you with functions which can fill in the replacement fields
* with arbitrary strings. These strings can become very large, e.g. entire tables.
*
*/

class Template
{
    /**
    * Serialization helper, the name of this class.
    *
    * @var       string
    */
    public $classname = "Template";

    /**
    * Determines how much debugging output Template will produce.
    * This is a bitwise mask of available debug levels:
    * 0 = no debugging
    * 1 = debug variable assignments
    * 2 = debug calls to get variable
    * 4 = debug internals (outputs all function calls with parameters).
    * 8 = debug caching (incomplete)
    *
    * Note: setting $this->debug = true will enable debugging of variable
    * assignments only which is the same behaviour as versions up to release 7.2d.
    *
    * @var       int
    */
    public $debug    = 0;

    /**
    * The base directory array from which template files are loaded. When
    * attempting to open a file, the paths in this array are searched one at
    * a time. As soon as a file exists, the array search stops.
    *
    * @var       string
    * @see       set_root
    */
    private $root     = array();

    /**
    * A hash of strings forming a translation table which translates variable names
    * into names of files containing the variable content.
    * $file[varname] = "filename";
    *
    * @var       array
    * @see       set_file
    */
    private $file     = array();

    /**
    * A hash of strings to denote if an instance or
    * standard template. Contains the unique id for cache
    * identifier
    *
    * @var       array
    * @see       set_file
    */

    private $instance = array();

    /**
    * A hash of strings forming a translation table which translates variable names
    * into names of files containing the variable content.
    * $location[varname] = "full path to template";
    *
    * @var       array
    * @see       set_file
    */
    private $location     = array();

    /**
    * The in memory template
    *
    * @var       array
    * @see       set_file
    */
    private $templateCode = array();

    /**
    * A hash of strings forming a translation table which translates variable names
    * into names of files containing the variable content.
    * $file[varname] = "filename";
    *
    * @var       array
    * @see       cache_blocks,set_block
    */
    private $blocks   = array();

    /**
    * A hash of strings forming a translation table which translates variable names
    * into the parent name of the variable.
    *
    * @var       array
    * @see       cache_blocks,set_block, block_echo
    */
    private $block_replace = array();

    /**
    * A hash of strings forming a translation table which translates variable names
    * into regular expressions for themselves.
    * $varkeys[varname] = "/varname/"
    *
    * @var       array
    * @see       set_var
    */
    private $varkeys  = array();

    /**
    * A hash of strings forming a translation table which translates variable names
    * into values for their respective varkeys.
    * $varvals[varname] = "value"
    *
    * @var       array
    * @see       set_var
    */
    private $varvals  = array();

    /**
    * A hash of vars that are not to be translated when create_instance() is called.
    * $nocache[varname] = true
    *
    * @var       array
    * @see       create_instance, val_echo, mod_echo
    */
    private $nocache  = array();

    /**
    * Determines how to output variable tags with no assigned value in templates.
    *
    * @var       string
    * @see       set_unknowns
    */
    private $unknowns = "remove";

    /**
    * Determines if using memory based caching
    *
    * @var       string
    */
    private $memCache = false;

    /**
    * Internal error handler stub
    *
    * @var string
    */
    private static $emptyErrorHandler;

    /**
    * Determines how Template handles error conditions.
    * "yes"      = the error is reported, then execution is halted
    * "report"   = the error is reported, then execution continues by returning "false"
    * "no"       = errors are silently ignored, and execution resumes reporting "false"
    * "log"      = writes errors to glFusion Error log and returns false.
    *
    * @var       string
    * @see       halt
    */
    public $halt_on_error  = "yes";

    /**
    * The last error message is retained in this variable.
    *
    * @var       string
    * @see       halt
    */
    public $last_error     = "";

    /******************************************************************************
    * Class constructor. May be called with two optional parameters.
    * The first parameter sets the template directory the second parameter
    * sets the policy regarding handling of unknown variables.
    *
    * usage: Template([string $root = array()], [string $unknowns = "remove"])
    *
    * @param     $root        path to template directory
    * @param     $string      what to do with undefined variables
    * @see       set_root
    * @see       set_unknowns
    * @return    void
    */
    public function __construct($root = array(), $unknowns = "remove")
    {
        global $_CONF, $TEMPLATE_OPTIONS;

        $this->set_root($root);
        $this->set_unknowns($unknowns);
        if (is_array($TEMPLATE_OPTIONS) AND array_key_exists('default_vars',$TEMPLATE_OPTIONS) and is_array($TEMPLATE_OPTIONS['default_vars'])) {
            foreach ($TEMPLATE_OPTIONS['default_vars'] as $k => $v) {
                $this->set_var($k, $v);
            }
        }

        $c = Cache::getInstance();
        $cDriver = strtolower($c->getDriverName());

        if ($cDriver != 'files' && $cDriver != 'devnull') {
            $this->memCache = true;
        } else {
            if ( $_CONF['cache_templates'] ) {
                clearstatcache();
            }
        }
        self::$emptyErrorHandler = function () {};
    }


    /******************************************************************************
    * Checks that $root is a valid directory and if so sets this directory as the
    * base directory from which templates are loaded by storing the value in
    * $this->root. Relative filenames are prepended with the path in $this->root.
    *
    * Returns true on success, false on error.
    *
    * usage: set_root(string $root)
    *
    * @param     $root         string containing new template directory
    * @see       root
    * @return    boolean
    */
    public function set_root($root)
    {
        global $TEMPLATE_OPTIONS;

        if (!is_array($root)) {
            $root = array($root);
        }
        if ($this->debug & 4) {
            echo '<p><b>set_root:</b> root = array(' . (count($root) > 0 ? '"' . implode('","', $root) . '"' : '') .")</p>\n";
        }
        if (isset($TEMPLATE_OPTIONS['hook']['set_root'])) {
            $function = $TEMPLATE_OPTIONS['hook']['set_root'];
            if (is_callable($function)) {
                $root = call_user_func($function, $root);
            }
        }

        if ($this->debug & 4) {
            echo '<p><b>set_root:</b> root = array(' . (count($root) > 0 ? '"' . implode('","', $root) . '"' : '') .")</p>\n";
        }
        $this->root = array();
        $missing = array();
        foreach ($root as $r) {
            if (substr($r, -1) == '/') {
                $r = substr ($r, 0, -1);
            }
            if (!@is_dir($r)) {
                $missing[] = $r;
                continue;
            }
            $this->root[] = $r;
        }
        if ($this->debug & 4) {
            echo '<p><b>set_root:</b> root = array(' . (count($root) > 0 ? '"' . implode('","', $root) . '"' : '') .")</p>\n";
        }
        if (count($this->root) > 0) {
            return true;
        }

        if (count($missing) > 0) {
            $this->halt("set_root: none of these directories exist: " . implode(', ', $missing));
        } else {
            $this->halt("set_root: at least on existing directory must be set as root.");
        }
        return false;
    }


    /******************************************************************************
    * Sets the policy for dealing with unresolved variable names.
    *
    * unknowns defines what to do with undefined template variables
    * "remove"   = remove undefined variables
    * "comment"  = replace undefined variables with comments
    * "keep"     = keep undefined variables
    *
    * Note: "comment" can cause unexpected results when the variable tag is embedded
    * inside an HTML tag, for example a tag which is expected to be replaced with a URL.
    *
    * usage: set_unknowns(string $unknowns)
    *
    * @param     $unknowns         new value for unknowns
    * @see       unknowns
    * @return    void
    */
    public function set_unknowns($unknowns = "")
    {
        global $TEMPLATE_OPTIONS;

        if (isset($TEMPLATE_OPTIONS['force_unknowns'])) {
            $unknowns = $TEMPLATE_OPTIONS['force_unknowns'];
        } else if (empty($unknowns) && !is_numeric($unknowns)) {
            if (isset($TEMPLATE_OPTIONS['unknowns'])) {
                $unknowns = $TEMPLATE_OPTIONS['unknowns'];
            } else {
                $unknowns = 'remove';
            }
        }

        if ($this->debug & 4) {
            echo "<p><b>unknowns:</b> unknowns = $unknowns</p>\n";
        }
        $this->unknowns = $unknowns;
    }


    /******************************************************************************
    * Defines a filename for the initial value of a variable.
    *
    * It may be passed either a varname and a file name as two strings or
    * a hash of strings with the key being the varname and the value
    * being the file name.
    *
    * The new mappings are stored in the array $this->file.
    * The files are not loaded yet, but only when needed.
    *
    * Returns true on success, false on error.
    *
    * usage: set_file(array $filelist = (string $varname => string $filename))
    * or
    * usage: set_file(string $varname, string $filename)
    *
    * @param     $varname      either a string containing a varname or a hash of varname/file name pairs.
    * @param     $filename     if varname is a string this is the filename otherwise filename is not required
    * @return    boolean
    */
    public function set_file($varname, $filename = "")
    {
        global $_CONF;

        if (!is_array($varname)) {
            if ($this->debug & 4) {
                echo "<p><b>set_file:</b> (with scalar) varname = $varname, filename = $filename</p>\n";
            }
            if ($filename == "") {
                $this->halt("set_file: For varname $varname filename is empty.");
                return false;
            }
            $tFilename = $this->filename($filename);
            $templateCode = $this->compile_template($varname, $tFilename);
            $this->templateCode[$varname] = $templateCode;
            $this->location[$varname] = $tFilename;
        } else {
            foreach($varname AS $v => $f ) {
                if ($this->debug & 4) {
                    echo "<p><b>set_file:</b> (with array) varname = $v, filename = $f</p>\n";
                }
                if ($f == "") {
                    $this->halt("set_file: For varname $v filename is empty.");
                    return false;
                }
                $tFilename = $this->filename($f);
                $f = $this->compile_template($varname, $tFilename);
                $this->templateCode[$v] = $f;
                $this->location[$v] = $tFilename;
            }
        }
        return true;
    }


    /******************************************************************************
    * A variable $parent may contain a variable block defined by:
    * &lt;!-- BEGIN $varname --&gt; content &lt;!-- END $varname --&gt;. This function removes
    * that block from $parent and replaces it with a variable reference named $name.
    * The block is inserted into the varkeys and varvals hashes. If $name is
    * omitted, it is assumed to be the same as $varname.
    *
    * Blocks may be nested but care must be taken to extract the blocks in order
    * from the innermost block to the outermost block.
    *
    * Returns true on success, false on error.
    *
    * usage: set_block(string $parent, string $varname, [string $name = ""])
    *
    * @param     $parent       a string containing the name of the parent variable
    * @param     $varname      a string containing the name of the block to be extracted
    * @param     $name         the name of the variable in which to store the block
    * @return    boolean
    */
    public function set_block($parent, $varname, $name = "")
    {
        global $_CONF;

        $this->block_replace[$varname] = (!empty($name) || is_numeric($name)) ? $name : $parent;

        return true;
    }


    /******************************************************************************
    * This functions sets the value of a variable.
    *
    * It may be called with either a varname and a value as two strings or an
    * an associative array with the key being the varname and the value being
    * the new variable value.
    *
    * The function inserts the new value of the variable into the $varkeys and
    * $varvals hashes. It is not necessary for a variable to exist in these hashes
    * before calling this function.
    *
    * An optional third parameter allows the value for each varname to be appended
    * to the existing variable instead of replacing it. The default is to replace.
    * This feature was introduced after the 7.2d release.
    *
    *
    * usage: set_var(string $varname, [string $value = ""], [boolean $append = false])
    * or
    * usage: set_var(array $varname = (string $varname => string $value), [mixed $dummy_var], [boolean $append = false])
    *
    * @param     $varname      either a string containing a varname or a hash of varname/value pairs.
    * @param     $value        if $varname is a string this contains the new value for the variable otherwise this parameter is ignored
    * @param     $append       if true, the value is appended to the variable's existing value
    * @param     $nocache      if true, the variable is added to the list of variable that are not instance cached.
    * @return    void
    */
    public function set_var($varname, $value = "", $append = false, $nocache = false)
    {
        if (!is_array($varname)) {
            if (!empty($varname) || is_numeric($varname) ) {
                if ($this->debug & 1) {
                    printf("<b>set_var:</b> (with scalar) <b>%s</b> = '%s'<br>\n", $varname, htmlentities($value));
                }
                if ($append && isset($this->varvals[$varname])) {
                    $this->varvals[$varname] .= $value;
                } else {
                    $this->varvals[$varname] = $value;
                }
                if ($nocache) {
                    $this->nocache[$varname] = true;
                }
            }
        } else {
            foreach( $varname AS $k => $v) {

                if (!empty($k) || is_numeric($k) ) {
                    if ($this->debug & 1) {
                        printf("<b>set_var:</b> (with array) <b>%s</b> = '%s'<br>\n", $k, htmlentities($v));
                    }
                    if ($append && isset($this->varvals[$k])) {
                        $this->varvals[$k] .= $v;
                    } else {
                        $this->varvals[$k] = $v;
                    }
                    if ($nocache) {
                        $this->nocache[$k] = true;
                    }
                }
            }
        }
    }


    /******************************************************************************
    * This functions clears the value of a variable.
    *
    * It may be called with either a varname as a string or an array with the
    * values being the varnames to be cleared.
    *
    * The function sets the value of the variable in the $varkeys and $varvals
    * hashes to "". It is not necessary for a variable to exist in these hashes
    * before calling this function.
    *
    *
    * usage: clear_var(string $varname)
    * or
    * usage: clear_var(array $varname = (string $varname))
    *
    * @param     $varname      either a string containing a varname or an array of varnames.
    * @return    void
    */
    public function clear_var($varname)
    {
        if (!is_array($varname)) {
            if (!empty($varname) || is_numeric($varname) ) {
                if ($this->debug & 1) {
                    printf("<b>clear_var:</b> (with scalar) <b>%s</b><br>\n", $varname);
                }
                $this->set_var($varname, "");
            }
        } else {
            foreach( $varname AS $k => $v ) {
                if (!empty($v) || is_numeric($v) ) {
                    if ($this->debug & 1) {
                        printf("<b>clear_var:</b> (with array) <b>%s</b><br>\n", $v);
                    }
                    $this->set_var($v, "");
                }
            }
        }
    }


    /******************************************************************************
    * This functions unsets a variable completely.
    *
    * It may be called with either a varname as a string or an array with the
    * values being the varnames to be cleared.
    *
    * The function removes the variable from the $varkeys and $varvals hashes.
    * It is not necessary for a variable to exist in these hashes before calling
    * this function.
    *
    *
    * usage: unset_var(string $varname)
    * or
    * usage: unset_var(array $varname = (string $varname))
    *
    * @param     $varname      either a string containing a varname or an array of varnames.
    * @return    void
    */
    public function unset_var($varname)
    {
        if (!is_array($varname)) {
            if (!empty($varname) || is_numeric($varname) ) {
                if ($this->debug & 1) {
                    printf("<b>unset_var:</b> (with scalar) <b>%s</b><br>\n", $varname);
                }
                unset($this->varkeys[$varname]);
                unset($this->varvals[$varname]);
            }
        } else {
            foreach($varname AS $k => $v ) {
                if (!empty($v) || is_numeric($v) ) {
                    if ($this->debug & 1) {
                        printf("<b>unset_var:</b> (with array) <b>%s</b><br>\n", $v);
                    }
                    unset($this->varkeys[$v]);
                    unset($this->varvals[$v]);
                }
            }
        }
    }


    /******************************************************************************
    * This function fills in all the variables contained within the variable named
    * $varname. The resulting value is returned as the function result and the
    * original value of the variable varname is not changed. The resulting string
    * is not "finished", that is, the unresolved variable name policy has not been
    * applied yet.
    *
    * Returns: the value of the variable $varname with all variables substituted.
    *
    * usage: subst(string $varname)
    *
    * @param     $varname      the name of the variable within which variables are to be substituted
    * @return    string
    */
    public function subst($varname)
    {
        global $_CONF;

        $instance = false;

        if (isset($this->blocks[$varname])) {
            $templateCode = $this->blocks[$varname];
        } else if (isset($this->instance[$varname])) {
            $templateCode = $this->instance[$varname];
        } else if (isset($this->templateCode[$varname])) {
            $templateCode = $this->templateCode[$varname];
        } else if (isset($this->varvals[$varname]) OR (empty($varname) && !is_numeric($varname) ) ) {
            return $this->slow_subst($varname);
        } else {
            // $varname does not reference a file so return
            if ($this->debug & 4) {
                echo "<p><b>subst:</b> varname $varname does not reference a file</p>\n";
            }
            return "";
        }

        ob_start();
        eval('?>'.$templateCode.'<?php ');
        $str = ob_get_contents();
        ob_end_clean();
        return $str;

    }

    /******************************************************************************
    * This function fills in all the variables contained within the variable named
    * $varname. The resulting value is returned as the function result and the
    * original value of the variable varname is not changed. The resulting string
    * is not "finished", that is, the unresolved variable name policy has not been
    * applied yet.
    *
    * This is the old version of subst.
    *
    * Returns: the value of the variable $varname with all variables substituted.
    *
    * usage: subst(string $varname)
    *
    * @param     $varname      the name of the variable within which variables are to be substituted
    * @return    string
    */
    public function slow_subst($varname)
    {
        $varvals_quoted = array();
        if ($this->debug & 4) {
            echo "<p><b>subst:</b> varname = $varname</p>\n";
        }

        if (count($this->varkeys) < count($this->varvals)) {
            foreach ($this->varvals as $k => $v) {
                $this->varkeys[$k] = "{".$k."}";
            }
        }

        // quote the replacement strings to prevent bogus stripping of special chars
        foreach($this->varvals AS $k => $v ) {
            $varvals_quoted[$k] = str_replace(array('\\\\', '$'), array('\\\\\\\\', '\\\\$'), $v);
        }

        $str = $this->get_var($varname);
        $str = str_replace($this->varkeys, $varvals_quoted, $str);
        return $str;
    }


    /******************************************************************************
    * This is shorthand for print $this->subst($varname). See subst for further
    * details.
    *
    * Returns: always returns false.
    *
    * usage: psubst(string $varname)
    *
    * @param     $varname      the name of the variable within which variables are to be substituted
    * @return    false
    * @see       subst
    */
    public function psubst($varname)
    {
        if ($this->debug & 4) {
            echo "<p><b>psubst:</b> varname = $varname</p>\n";
        }
        print $this->subst($varname);

        return false;
    }


    /******************************************************************************
    * The function substitutes the values of all defined variables in the variable
    * named $varname and stores or appends the result in the variable named $target.
    *
    * It may be called with either a target and a varname as two strings or a
    * target as a string and an array of variable names in varname.
    *
    * The function inserts the new value of the variable into the $varkeys and
    * $varvals hashes. It is not necessary for a variable to exist in these hashes
    * before calling this function.
    *
    * An optional third parameter allows the value for each varname to be appended
    * to the existing target variable instead of replacing it. The default is to
    * replace.
    *
    * If $target and $varname are both strings, the substituted value of the
    * variable $varname is inserted into or appended to $target.
    *
    * If $handle is an array of variable names the variables named by $handle are
    * sequentially substituted and the result of each substitution step is
    * inserted into or appended to in $target. The resulting substitution is
    * available in the variable named by $target, as is each intermediate step
    * for the next $varname in sequence. Note that while it is possible, it
    * is only rarely desirable to call this function with an array of varnames
    * and with $append = true. This append feature was introduced after the 7.2d
    * release.
    *
    * Returns: the last value assigned to $target.
    *
    * usage: parse(string $target, string $varname, [boolean $append])
    * or
    * usage: parse(string $target, array $varname = (string $varname), [boolean $append])
    *
    * @param     $target      a string containing the name of the variable into which substituted $varnames are to be stored
    * @param     $varname     if a string, the name the name of the variable to substitute or if an array a list of variables to be substituted
    * @param     $append      if true, the substituted variables are appended to $target otherwise the existing value of $target is replaced
    * @return    string
    * @see       subst
    */
    public function parse($target, $varname, $append = false)
    {
        if (!is_array($varname)) {
            if ($this->debug & 4) {
                echo "<p><b>parse:</b> (with scalar) target = $target, varname = $varname, append = $append</p>\n";
            }
            if ( isset($this->location[$varname]) ) {
                $this->set_var('templatelocation',$this->location[$varname]);
            }
            $str = $this->subst($varname);
            if ($append) {
                $this->set_var($target, $this->get_var($target) . $str);
            } else {
                $this->set_var($target, $str);
            }
        } else {
            foreach ( $varname AS $i => $v ) {
                if ($this->debug & 4) {
                    echo "<p><b>parse:</b> (with array) target = $target, i = $i, varname = $v, append = $append</p>\n";
                }
                $this->set_var('templatelocation',$this->location[$v]);
                $str = $this->subst($v);
                if ($append) {
                    $this->set_var($target, $this->get_var($target) . $str);
                } else {
                    $this->set_var($target, $str);
                }
            }
        }

        if ($this->debug & 4) {
            echo "<p><b>parse:</b> completed</p>\n";
        }
        return $str;
    }


    /******************************************************************************
    * This is shorthand for print $this->parse(...) and is functionally identical.
    * See parse for further details.
    *
    * Returns: always returns false.
    *
    * usage: pparse(string $target, string $varname, [boolean $append])
    * or
    * usage: pparse(string $target, array $varname = (string $varname), [boolean $append])
    *
    * @param     $target      a string containing the name of the variable into which substituted $varnames are to be stored
    * @param     $varname     if a string, the name the name of the variable to substitute or if an array a list of variables to be substituted
    * @param     $append      if true, the substituted variables are appended to $target otherwise the existing value of $target is replaced
    * @return    false
    * @see       parse
    */
    public function pparse($target, $varname, $append = false)
    {
        if ($this->debug & 4) {
            echo "<p><b>pparse:</b> passing parameters to parse...</p>\n";
        }
        print $this->finish($this->parse($target, $varname, $append));
        return false;
    }


    /******************************************************************************
    * This function returns an associative array of all defined variables with the
    * name as the key and the value of the variable as the value.
    *
    * This is mostly useful for debugging. Also note that $this->debug can be used
    * to echo all variable assignments as they occur and to trace execution.
    *
    * Returns: a hash of all defined variable values keyed by their names.
    *
    * usage: get_vars()
    *
    * @return    array
    * @see       $debug
    */
    public function get_vars()
    {
        if ($this->debug & 4) {
            echo "<p><b>get_vars:</b> constructing array of vars...</p>\n";
        }
        foreach ( $this->varvals AS $k => $v ) {
            $result[$k] = $this->get_var($k);
        }
        return $result;
    }

    public function dump_vars()
    {
        $tvars = $this->get_vars();
        foreach ($tvars AS $name => $value) {
            print $name . '<br/>';
        }
    }

    /******************************************************************************
    * This function returns the value of the variable named by $varname.
    * If $varname references a file and that file has not been loaded yet, the
    * variable will be reported as empty.
    *
    * When called with an array of variable names this function will return a a
    * hash of variable values keyed by their names.
    *
    * Returns: a string or an array containing the value of $varname.
    *
    * usage: get_var(string $varname)
    * or
    * usage: get_var(array $varname)
    *
    * @param     $varname     if a string, the name the name of the variable to get the value of, or if an array a list of variables to return the value of
    * @return    string or array
    */
    public function get_var($varname)
    {
        if (!is_array($varname)) {
            if (isset($this->varvals[$varname])) {
                $str = $this->varvals[$varname];
            } else {
                $str = "";
            }
            if ($this->debug & 2) {
                printf ("<b>get_var</b> (with scalar) <b>%s</b> = '%s'<br>\n", $varname, htmlentities($str));
            }
            return $str;
        } else {
            foreach ( $varname AS $k => $v ) {
                if (isset($this->varvals[$v])) {
                    $str = $this->varvals[$v];
                } else {
                    $str = "";
                }
                if ($this->debug & 2) {
                    printf ("<b>get_var:</b> (with array) <b>%s</b> = '%s'<br>\n", $v, htmlentities($str));
                }
                $result[$v] = $str;
            }
            return $result;
        }
    }


    /******************************************************************************
    * Returns: Unknown processing use to take place here. Now it happens directly
    * in the cache file. This function is still necessary for being able to hook
    * the final output from the library.
    *
    * usage: finish(string $str)
    *
    * @param     $str         a string to return
    * @return    string
    */
    public function finish($str)
    {
        global $TEMPLATE_OPTIONS;

        if (isset($TEMPLATE_OPTIONS['hook']['finish'])) {
            $function = $TEMPLATE_OPTIONS['hook']['finish'];
            if (is_callable($function)) {
                $str = call_user_func($function, $str);
            }
        }
        return $str;
    }


    /******************************************************************************
    * This function prints the finished version of the value of the variable named
    * by $varname. That is, the policy regarding unresolved variable names will be
    * applied to the variable $varname then it will be printed.
    *
    * usage: p(string $varname)
    *
    * @param     $varname     a string containing the name of the variable to finish and print
    * @return    void
    * @see       set_unknowns
    * @see       finish
    */
    public function p($varname)
    {
        print $this->finish($this->get_var($varname));
    }


    /******************************************************************************
    * This function returns the finished version of the value of the variable named
    * by $varname. That is, the policy regarding unresolved variable names will be
    * applied to the variable $varname and the result returned.
    *
    * Returns: a finished string derived from the variable $varname.
    *
    * usage: get(string $varname)
    *
    * @param     $varname     a string containing the name of the variable to finish
    * @return    void
    * @see       set_unknowns
    * @see       finish
    */
    public function get($varname)
    {
        return $this->finish($this->get_var($varname));
    }


    /******************************************************************************
    * When called with a relative pathname, this function will return the pathname
    * with $this->root prepended. Absolute pathnames are returned unchanged.
    *
    * Returns: a string containing an absolute pathname.
    *
    * usage: filename(string $filename)
    *
    * @param     $filename    a string containing a filename
    * @return    string
    * @see       set_root
    */
    private function filename($filename)
    {
        if ($this->debug & 4) {
            echo "<p><b>filename:</b> filename = $filename</p>\n";
        }
        if ($this->debug & 8) {
            foreach($this->root as $r) {
                echo "root: " . $r . "<br>";
            }
        }

        // if path reaches root, just use it.
        if (substr($filename, 0, 1) == '/' ||   // handle unix root /
        substr($filename, 1, 1) == ':' ||   // handle windows d:\path
        substr($filename, 0, 2) == '\\\\'   // handle windows network path \\server\path
        ) {
            if (!file_exists($filename)) {
                $this->halt("filename: file $filename does not exist.(1)");
            }
            return $filename;
        } else {
            // check each path in order
            foreach ($this->root as $r) {
                $f = $r.'/'.$filename;
                if ($this->debug & 8) {
                    echo "<p><b>filename:</b> filename = $f</p>\n";
                }
                if (file_exists($f)) {
                    return $f;
                }
            }
        }
        $this->halt("filename: file $filename does not exist.(2)");
        return $filename;
    }


    /******************************************************************************
    * This function will construct a regexp for a given variable name with any
    * special chars quoted.
    *
    * Returns: a string containing an escaped variable name.
    *
    * usage: varname(string $varname)
    *
    * @param     $varname    a string containing a variable name
    * @return    string
    */
    private function varname($varname)
    {
        return preg_quote("{".$varname."}");
    }


    /******************************************************************************
    * This function is called whenever an error occurs and will handle the error
    * according to the policy defined in $this->halt_on_error. Additionally the
    * error message will be saved in $this->last_error.
    *
    * Returns: always returns false.
    *
    * usage: halt(string $msg)
    *
    * @param     $msg         a string containing an error message
    * @return    void
    * @see       $halt_on_error
    */
    private function halt($msg)
    {
        $this->last_error = $msg;

        if ($this->halt_on_error != "no" && $this->halt_on_error != "log") {
            $this->haltmsg($msg);
        }

        if ($this->halt_on_error == "log") {
            Log::write('system',Log::ERROR,$msg);
        }

        return false;
    }


    /******************************************************************************
    * This function prints an error message.
    * It can be overridden by your subclass of Template. It will be called with an
    * error message to display.
    *
    * usage: haltmsg(string $msg)
    *
    * @param     $msg         a string containing the error message to display
    * @return    void
    * @see       halt
    */
    private function haltmsg($msg)
    {
        if ($this->halt_on_error == 'yes') {
            trigger_error(sprintf("Template Error: %s", $msg));
        } else {
            printf("<b>Template Error:</b> %s<br />\n", $msg);
        }
    }

    /******************************************************************************
    * These functions are called from the cached php file to fetch data into the template.
    * You should NEVER have to call them directly.
    *
    * @param  $val             string containing name of template variable
    * @param  $modifier        Optional parameter to apply modifiers to template variables
    * @return string
    * @see    cache_blocks,check_cache
    *
    */
    private function val_echo($val)
    {
        if (array_key_exists($val, $this->nocache) && $this->unknowns == 'PHP') {
            return '<?php echo $this->val_echo(\''.$val.'\'); ?>';
        }

        if (array_key_exists($val, $this->varvals)) {
            return $this->varvals[$val];
        }
        if ($this->unknowns == 'comment') {
            return "<!-- Template variable $val undefined -->";
        } else if ($this->unknowns == 'keep') {
            return '{'.$val.'}';
        }
        return '';
    }

    /***
     * Used in {!if var}. Avoid duplicating a large string when all we care about is if the string is non-zero length
     *
     * @param  string $val
     * @return bool
     */
    private function var_notempty($val)
    {
        if (array_key_exists($val, $this->varvals)) {
            return !(empty($this->varvals[$val]) ) ;
        }
        return false;
    }

    private function var_empty($val)
    {
        if ( !array_key_exists($val, $this->varvals)) return true;
        if (array_key_exists($val, $this->varvals)) {
            return (empty($this->varvals[$val]) ) ;
        }
        return false;
    }


    private function mod_echo($val, $modifier = '')
    {
        if (array_key_exists($val, $this->nocache) && $this->unknowns == 'PHP') {
            if (empty($modifier) && !is_numeric($modifer) ) {
                return '<?php echo $this->val_echo(\''.$val.'\'); ?>';
            } else {
                return '<?php echo $this->mod_echo(\''.$val.'\',\''.$modifier.'\'); ?>';
            }
        }

        if (array_key_exists($val, $this->varvals)) {
            $mods = explode(':', substr($modifier,1));
            $ret = $this->varvals[$val];
            foreach ($mods as $mod) {
                switch ($mod[0]) {
                    case 'u':
                        $ret = urlencode($ret);
                        break;
                    case 's':
                        $ret = htmlspecialchars($ret);
                        break;
                    case 't':
                        $ret = substr($ret, 0, intval(substr($mod,1))); // truncate
                        break;
                }
            }
            return $ret;
        }
        if ($this->unknowns == 'comment') {
            return '<!-- Template variable '.htmlspecialchars($val).' undefined -->';
        } else if ($this->unknowns == 'keep') {
            return '{'.htmlspecialchars($val.$modifier).'}';
        }
        return '';
    }

    private function lang_echo($val)
    {
        // only allow variables with LANG in them to somewhat protect this from harm.
        if (stristr($val, 'LANG') === false) {
            return '';
        }
        $A = explode('[',$val);
        if (isset($GLOBALS[$A[0]])) {
            $var = $GLOBALS[$A[0]];
            for ($i = 1; $i < count($A); ++$i) {
                $idx = str_replace(array(']',"'"),'',$A[$i]);
                if (array_key_exists($idx, $var)) {
                    $var = $var[$idx];
                } else {
                    break;
                }
            }
            if (is_scalar($var)) {
                return $var;
            }
        }
        if ($this->unknowns == 'comment') {
            return '<!-- Language variable '.htmlspecialchars($val).' undefined -->';
        } else if ($this->unknowns == 'keep') {
            return '{'.htmlspecialchars($val).'}';
        }
        return '';
    }

    private function block_echo($block)
    {
        if (array_key_exists($block, $this->block_replace)) {
            return $this->get_var($this->block_replace[$block]);
        }
        return '';
    }

    private function loop($var)
    {
        $loopvar = $var . '__loopvar';
        $limit = $this->get_var($var);
        $current = $this->get_var($loopvar);
        if ($limit > 0) {
            $this->set_var($loopvar, ++$current);
            $ret = $current <= $limit;
        } else {
            $this->set_var($loopvar, --$current);
            $ret = $current >= $limit;
        }
        if (!$ret) $this->unset_var($loopvar);
        return $ret;
    }
    private function inc($var)
    {
        $val = $this->get_var($var);
        if ($val == 0) $val = 0;
        $this->set_var($var, ++$val);
    }
    private function inc_echo($var)
    {
        $val = $this->get_var($var);
        if ($val == 0) $val = 0;
        $this->set_var($var, ++$val);
        return $val;
    }
    private function dec($var)
    {
        $val = $this->get_var($var);
        if ($val == 0) $val = 0;
        $this->set_var($var, --$val);
    }
    private function dec_echo($var)
    {
        $val = $this->get_var($var);
        if ($val == 0) $val = 0;
        $this->set_var($var, --$val);
        return $val;
    }


    /******************************************************************************
    * These functions build the cached php file.
    * You should NEVER have to call them directly.
    *
    * @param  $tmplt           string being cached
    * @param  $in_php          boolean used to determing if php escape chars need to be printed
    * @return string
    * @see    cache_write
    *
    */
    private function replace_vars($tmplt, $in_php = false)
    {
        $tmplt = str_replace(array("{{","}}") ,array("?x?x?","x?x?x"),$tmplt);

        // do all the common substitutions
        if ($in_php) {
            $tmplt = preg_replace(
            array(
            '/\{([-\w\d_\[\]]+)\}/',                              // matches {identifier}
            '/\{([-\w\d_\[\]]+)((:u|:s|:t\d+)+)\}/',              // matches {identifier} with optional :s, :u or :t### suffix
            ),
            array(
            '$this->get_var(\'\1\')',
            '$this->mod_echo(\'\1\',\'\2\')',
            ),
            $tmplt);
        } else {
            $tmplt = preg_replace(
            array(
            '/\{([-\w\d_\[\]]+)\}/',                              // matches {identifier}
            '/\{([-\w\d_\[\]]+)((:u|:s|:t\d+)+)\}/',              // matches {identifier} with optional :s, :u or :t### suffix
            ),
            array(
            '<?php echo $this->val_echo(\'\1\'); ?>',
            '<?php echo $this->mod_echo(\'\1\',\'\2\'); ?>',
            ),
            $tmplt);
        }

        $tmplt = str_replace(array("?x?x?","x?x?x"),array("{","}"),$tmplt);

        return $tmplt;
    }

    private function replace_lang($tmplt, $in_php = false)
    {
        global $TEMPLATE_OPTIONS;

        if ($TEMPLATE_OPTIONS['cache_by_language']) {
            if ($in_php) {
                $tmplt = preg_replace_callback(
                '/\{\$(LANG[\w\d_]+)\[(\')?([\w\d_]+)(?(2)\')\]\}/',
                array($this, 'parse_quoted_lang_callback'),
                $tmplt);
            } else {
                $tmplt = preg_replace_callback(
                '/\{\$(LANG[\w\d_]+)\[(\')?([\w\d_]+)(?(2)\')\]\}/',
                array($this, 'parse_lang_callback'),
                $tmplt);
            }
        } else {
            if ($in_php) {
                $tmplt = preg_replace(
                '/\{\$(LANG[\w\d_]+)\[(\')?([\w\d_]+)(?(2)\')\]\}/',
                '$this->lang_echo(\'\1[\3]\')',
                $tmplt);
            } else {
                $tmplt = preg_replace(
                '/\{\$(LANG[\w\d_]+)\[(\')?([\w\d_]+)(?(2)\')\]\}/',
                '<?php echo $this->lang_echo(\'\1[\3]\'); ?>',
                $tmplt);
            }
        }
        return $tmplt;
    }

    // Callbacks for replace_lang
    private function parse_lang_callback($matches)
    {
        return $this->lang_echo($matches[1].'['.$matches[3].']');
    }

    private function parse_quoted_lang_callback($matches)
    {
        return '\'' . addslashes($this->lang_echo($matches[1].'['.$matches[3].']')) . '\'';
    }

    private function replace_extended($tmplt)
    {
        if (strpos($tmplt, '!}') !== false || strpos($tmplt, '$}') !== false) {
            $tmplt = preg_replace_callback(
            array('/\{\!\!(if|elseif|while|echo|global|autotag) (([^\\\']|\\\\|\\\')+?) \!\!\}/',
            '/\{\!\!(set) ([-\w\d_\[\]]+) (([^\\\']|\\\\|\\\')+?) \!\!\}/',       // sets a variable
            '/\{(\$LANG[\w\d_]+)\[(\')?([\w\d_]+)(?(2)\')\] (([^\\\']|\\\\|\\\')+?) \$\}/',       // Substitutable language independence
            ),
            array($this, 'parse_extended_callback'),
            $tmplt);
        }

        if (strpos($tmplt, '{!') !== false) {
            $tmplt = preg_replace(
            array(
            '/\{!(if|elseif|while) ([-\w\d_\[\]]+)\}/',
            '/\{!(if|elseif|while) !([-\w\d_\[\]]+)\}/',
            '/\{!else(|!| !)\}/',
            '/\{!end(if|while|for)(|!| !)\}/',                    // for is not yet supported but here for future use
            '/\{!loop ([-\w\d_\[\]]+)(|!| !)\}/',
            '/\{!endloop(|!| !)\}/',
            '/\{!(inc|dec) ([-\w\d_\[\]]+)(|!| !)\}/',
            '/\{!(inc|dec)(\+(echo))? ([-\w\d_\[\]]+)(|!| !)\}/',
            '/\{!(break|continue)( \d+)?(|!| !)\}/',
            '/\{!unset ([-\w\d_\[\]]+)(|!| !)\}/',                // unsets a variable
            ),
            array(
            '<?php \1 ($this->var_notempty(\'\2\')): ?>',         // if exists and is non-zero
            '<?php \1 ($this->var_empty(\'\2\')): ?>',            // if exists and is non-zero
            '<?php else: ?>',
            '<?php end\1; ?>',
            '<?php while ($this->loop(\'\1\')): ?>',              // !loop
            '<?php endwhile; ?>',                                 // !endloop
            '<?php $this->\1(\'\2\'); ?>',                        // !inc and !dec and +echo
            '<?php \3 $this->\1_echo(\'\4\'); ?>',                // !inc and !dec and +echo
            '<?php \1\2; ?>',                                     // !break and !continue
            '<?php $this->unset_var(\'\1\'); ?>',
            ),
            $tmplt);
        }
        return $tmplt;
    }

    // Callbacks for replace_extended
    private function parse_extended_callback($matches)
    {
        global $TEMPLATE_OPTIONS;

        $cond    = '';
        $prefix  = '';
        $postfix = '';

        if ( $matches[1] == 'autotag' ) {
            $cond = "echo PLG_replaceTags('[".$matches[2]."]');";
        } else if ($matches[1] == 'set') {
            $cond = $matches[3];
            $prefix = '$this->set_var(\'' . addslashes($matches[2]) . '\', ';
            $postfix = ');';
        } else if ($matches[1] == 'global' || $matches[1] == 'echo' || $matches[1] == '') {
            $cond = $matches[2];
            $prefix = $matches[1] . ' ';
            $postfix = ';';
        } else if (substr($matches[1],0,5) == '$LANG') {
            $lang = substr($matches[1],1);
            $cond = $matches[4];

            $prefix = 'echo sprintf($this->lang_echo(\''.$lang.'['.$matches[3].']\'),';
            $postfix = ');';
        } else {
            $cond = $matches[2];
            $prefix = $matches[1] . ' (';
            $postfix = '):';
        }

        $cond = $this->replace_vars($cond,true);
        $cond = $this->replace_lang($cond,true);

        return '<?php ' . $prefix . $cond . $postfix . ' ?>';
    }


    /******************************************************************************
    * This function is only used with template files that contain BEGIN/END BLOCK
    * sections. See set_block for more details.
    *
    * As an internal function, you should never call it directly
    * usage: compile_blocks(string $filestub, array $parent, string $replace)
    *
    * @param  $filestub       format string for sprintf to create cache filename
    * @param  $parent         array containing name and content of the block
    * @return void
    * @see    cache_write,compile_template,set_block
    *
    */
    private function compile_blocks($parent)
    {
        global $_CONF;

        $reg = "/\s*<!--\s+BEGIN ([-\w\d_]+)\s+-->\s*?\n?(\s*.*?\n?)\s*<!--\s+END \\1\s+-->\s*?\n?/smU";
        $matches = array();
        $str = $parent[2];
        $matches = array();
        if (preg_match_all($reg, $str, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $replace = '<?php echo $this->block_echo(\''.$m[1].'\'); ?>';
                $str = str_replace($m[0], $replace, $str);
                $this->compile_blocks($m);
            }
        }

        $tmplt = $this->compile_template_code($str,true);

        $this->blocks[$parent[1]] = $tmplt;
    }

    /******************************************************************************
    * Called by filename(), compile_template verifies that the cache file is not out of
    * date. If it is, it recreates it from the existing template file.
    *
    * As an internal function, you should never call it directly
    * usage: compile_template(string $filename, string $tmplt, string $replace)
    *
    * @param  $varname        unused, name of the variable associated with the file
    * @param  $filename       path to the template file
    * @return void
    * @see    cache_block,cache_write,filename
    *
    */
    private function compile_template($varname, $filename)
    {
        global $TEMPLATE_OPTIONS, $_CONF;

        if ($this->debug & 8) {
            printf("<compile_template> Var %s for file %s<br>", $varname, $filename);
        }
        $tmplt = $this->check_cache($varname, $filename);
        if ( $tmplt !== null ) {
            return $tmplt;
        }

        $str = @file_get_contents($filename);

        $tmplt = $this->compile_template_code($str,false);

        return $tmplt;

    }

    // this compiles the template code to use the PHP replacements
    // option to skip block processing for things like cached templates
    // or instance caches
    private function compile_template_code($tmplt,$skipblocks = false)
    {
        global $TEMPLATE_OPTIONS, $_CONF;

        if ($skipblocks == false) {
            // check for begin/end block stuff
            $reg = "/\s*<!--\s+BEGIN ([-\w\d_]+)\s+-->\s*?\n?(\s*.*?\n?)\s*<!--\s+END \\1\s+-->\s*?\n?/smU";
            $matches = array();
            if (preg_match_all($reg, $tmplt, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $tmplt = str_replace($m[0], '<?php echo $this->block_echo(\''.$m[1].'\'); ?>', $tmplt);
                    $this->compile_blocks($m);
                }
            }
        }

        // order of operations could matter a lot so get rid of
        // template comments first: emits nothing to the output file
        // since the regex is multiline, make sure there is a comment before calling it

        if (strpos($tmplt, '{#') !== false) {
            if ( isset($_CONF['template_comments']) && $_CONF['template_comments'] == true ) {
                $tmplt = str_replace('{#','<!-- ',$tmplt);
                $tmplt = str_replace('#}',' -->',$tmplt);
            } else {
                $tmplt = preg_replace('!\{#.*?#\}(\n)?!sm', '', $tmplt);
            }
        }
        $tmplt = $this->replace_extended($tmplt);
        $tmplt = $this->replace_lang($tmplt);
        $tmplt = $this->replace_vars($tmplt);

        // clean up concatenation.
        $tmplt = str_replace('?'.'><'.'?php ', // makes the cache file easier on the eyes (need the concat to avoid PHP interpreting the ? >< ?php incorrectly
        "\n", $tmplt);

        return $tmplt;
    }

    /******************************************************************************
    * Prevents certain variables from being cached in the instance cache.
    *
    * @param  $vars           A string varname or array of varnames
    * @return none
    * @see    create_instance, set_var
    */
    public function uncached_var($vars)
    {
        if (empty($vars) && !is_numeric($vars) ) {
            return;
        } elseif (!is_array($vars)) {
            $vars = array($vars);
        }
        foreach ($vars as $varname) {
            $this->nocache[$varname] = true;
        }
    }

    /******************************************************************************
    * Creates an instance of the current template. Variables in the nocache array
    * are untranslated by returning the original PHP back. Conceptually, this
    * function is equivalent to the parse function.
    *
    * The $iid parameter must be globally unique. The recommended format is
    *   $plugin_$primarykey  or $plugin_$page_$uid
    *
    * The $filevar parameter is supposed to match one of the varnames passed to
    * set_file.
    *
    * usage: create_instance(string $iid, string $filevar)
    *
    * @param  $iid            A globally unique instance identifier.
    * @param  $filevar        This is the varname passed to $T->set_file.
    * @return void
    * @see    check_instance
    *
    */
    public function create_instance($iid, $filevar)
    {
        global $TEMPLATE_OPTIONS, $_CONF, $_SYSTEM;

        if ((isset($_SYSTEM['disable_instance_caching']) && $_SYSTEM['disable_instance_caching'] == true)
              || (isset($_CONF['cache_driver']) && $_CONF['cache_driver'] == 'Devnull')) {
            return;
        }

        $old_unknowns = $this->unknowns;
        $this->unknowns = 'PHP';
        $tmplt = $this->parse($iid, $filevar);

        $iid = str_replace(array('..', '/', '\\', ':'), '', $iid);
//        $iid = str_replace('-','_',$iid);
        $tmplt = '<!-- begin cached as '.htmlspecialchars($iid)." -->\n"
        . $tmplt
        . '<!-- end cached as '.htmlspecialchars($iid)." -->\n";

        $tmplt = $this->compile_template_code($tmplt,true);

        $c = Cache::getInstance();

        $c->set($iid,$tmplt,array('story','story_'.$this->varvals['story_id']));
        $this->instance[$filevar] = $tmplt;

        $this->unknowns = $old_unknowns;
        return;
    }

    /******************************************************************************
    * Checks for an instance of the current template. This check is based soley on
    * the $iid. The $filevar is replaces with the cached file if it exists.
    *
    * The $iid parameter must be globally unique. The recommended format is
    *   $plugin_$primarykey  or $plugin_$page_$uid
    *
    * The $filevar parameter is supposed to match one of the varnames passed set_file.
    *
    * usage:
    *          $T->set_file('main', 'main.thtml');
    *          $iid = 'mainfile_'.$primarykey;
    *          if (!$T->check_instance($iid, 'main')) {
    *              $T->set_var(...); //...
    *              $T->create_instance($iid, 'main');
    *          }
    *          $T->set_var('hits', $hit_count, false, true);
    *          $T->parse('output', 'main');
    *
    * @param  $iid            A globally unique instance identifier.
    * @param  $filevar        This is the varname passed to $T->set_file.
    * @return boolean         true if the instance file exists
    * @see    create_instance
    *
    */
    public function check_instance($iid, $filevar)
    {
        global $TEMPLATE_OPTIONS, $_CONF, $_SYSTEM;

        if ( (isset($_SYSTEM['disable_instance_caching']) && $_SYSTEM['disable_instance_caching'] == true) || (isset($_CONF['cache_driver']) && $_CONF['cache_driver'] == 'Devnull') ) {
            return false;
        }

        $iid = str_replace(array('..', '/', '\\', ':'), '', $iid);
//        $iid = str_replace('-','_',$iid);
        $c = Cache::getInstance();
        $rc = $c->has($iid);
        if ($rc === true) {
            $this->instance[$filevar] = $c->get($iid);
//Log::write('system',Log::DVLP_DEBUG,"Instance Cache HIT on " . $iid);
            return true;
        }
//Log::write('system',Log::DVLP_DEBUG,"Instance Cache MISS on " . $iid);
        return false;
    }

    /******************************************************************************
    * Creates the full path and filename for cache files
    *
    * @param  $filename       The template filename
    * @return string          full path/filename of cache file
    * @see    create_instance
    *
    */
    private function cache_create_filename($filename)
    {
        global $TEMPLATE_OPTIONS, $_CONF;

        $p = pathinfo($filename);
        if ($p['extension'] == 'php') {
            return $filename;
        }
        $basefile = basename($p['basename'],".{$p['extension']}");

        // convert /path_to_glfusion/private//layout/theme/dir1/dir2/file to dir1/dir2/file
        $extra_path = '';
        if ( is_array($TEMPLATE_OPTIONS['path_prefixes']) ) {
            foreach ($TEMPLATE_OPTIONS['path_prefixes'] as $prefix) {
                if (strpos($p['dirname'], $prefix) === 0) {
                    $extra_path = substr($p['dirname'].'/', strlen($prefix));
                    break;
                }
            }
        }

        if (!empty($extra_path)) {
            $extra_path = str_replace(array('/','\\',':'), '__', $extra_path);
        }

        if ($TEMPLATE_OPTIONS['cache_by_language']) {
            $extra_path = $_CONF['language'] . '/' . $extra_path;
            if (!is_dir($TEMPLATE_OPTIONS['path_cache'] . $_CONF['language'])) {
                @mkdir($TEMPLATE_OPTIONS['path_cache'] . $_CONF['language']);
                @touch($TEMPLATE_OPTIONS['path_cache'] . $_CONF['language'] . '/index.html');
            }
        }

        if ($this->memCache) {
            return str_replace(".","",$extra_path . $basefile);
        }

        $phpfile = $TEMPLATE_OPTIONS['path_cache'] . $extra_path . $basefile . '.php';
        return $phpfile;
    }

    /******************************************************************************
    * Called by compile_template(), check_cache verifies that the cache file is not out of
    * date. If it is, it recreates it from the existing template file.
    *
    * As an internal function, you should never call it directly
    * usage: check_cache(string $filename, string $tmplt, string $replace)
    *
    * @param  $varname        unused, name of the variable associated with the file
    * @param  $filename       path to the template file
    * @return $tmplt          Mixed - the template code from cache or null
    *                         if caching disabled
    * @see    compile_template, cache_write,filename
    *
    */
    private function check_cache($varname, $filename)
    {
        global $TEMPLATE_OPTIONS, $_CONF;

        if ( $_CONF['cache_templates'] == false ) {
            return null;
        }

        static $internalCache = array();

        $phpfile = $this->cache_create_filename($filename);

        $template_fstat = @filemtime($filename);

        if ($this->memCache) {
            $c = Cache::getInstance();
            $cache_fstat = $c->getModificationDate($phpfile);
            if ($template_fstat > $cache_fstat) {
                $str = @file_get_contents($filename);
                // cache_write will compile the template prior to creating the cache file
                $tmplt= $this->cache_write($phpfile, $str);
            } else {
                $tmplt = $c->get($phpfile);
            }
        } else {
            $key = md5($phpfile);
            set_error_handler(self::$emptyErrorHandler);
            $data = include($phpfile);
            restore_error_handler();

            if (!isset($data['touch']) || $template_fstat > $data['touch']) {
                $str = @file_get_contents($filename);
                // cache_write will compile the template prior to creating the cache file
                $tmplt= $this->cache_write($phpfile, $str);
                // save compiled template to internal cache
                $internalCache[$key] = $tmplt;
            } else {
                if (isset($internalCache[$key])) {
                    $tmplt = $internalCache[$key];
                } else {
                    $tmplt = $data['data'];
                    $internalCache[$key] = $tmplt;
                }
            }
        }
        $reg = "/\s*<!--\s+BEGIN ([-\w\d_]+)\s+-->\s*?\n?(\s*.*?\n?)\s*<!--\s+END \\1\s+-->\s*?\n?/smU";
        $matches = array();
        if (preg_match_all($reg, $tmplt, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $tmplt = str_replace($m[0], '<?php echo $this->block_echo(\''.$m[1].'\'); ?>', $tmplt);
                $this->compile_blocks($m);
            }
        }

        return $tmplt;
    }


    /******************************************************************************
    * This function does the final replace of {variable} with the appropriate PHP
    * and writes the text to the cache directory.
    *
    * As an internal function, you should never call it directly
    * usage: cache_write(string $filename, string $tmplt, string $replace)
    *
    * @param  $filename       string containing complete path to the cache file
    * @param  $tmplt          contents of the template file before replacement
    * @return void
    * @see    check_cache
    *
    */
    private function cache_write($filename, $tmplt)
    {
        global $TEMPLATE_OPTIONS, $_CONF;

        $tmplt = $this->compile_template_code($tmplt,true);

        if ($this->debug & 4) {
            printf("<b>cache_write:</b> opening $filename<br>\n");
        }

        if ($this->memCache) {
            $c = Cache::getInstance();
            $c->set($filename,$tmplt,'template');
        } else {
            $value = [
                'touch' => time(),
                'data'  => $tmplt
            ];
            $value  = var_export(serialize($value), true);
            $code   = sprintf('<?php return unserialize(%s);', $value);
            $f = @fopen($filename,'w');
            if ($f !== false ) {
                fwrite($f, $code);
                fclose($f);
            }
        }
        return $tmplt;
    }
} // end class
?>
