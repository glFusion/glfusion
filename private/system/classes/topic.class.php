<?php
/**
*   Topic class, used to track the current topic.
*
*   Usage:  Topic::setCurrent(tid)      set the current topic
*           Topic::Current()            retrieve the current topic object
*           Topic::Current()->varname   get "varname" for the current topic
*           Topic::Current(varname, default)    get "varname", or default
*           Topic::Clear()              unset the current topic
*           Topic::Get(tid)             get a single topic object
*           Topic::Get(tid, fld, default) get a topic's field, default if not found
*           Topic::All()                get an array of all topic objects
*           Topic::Access(tid)          check the current user's topic access
*           Topic::isEmpty()            check if the current topic is set
*           Topic::currentID(def_tid)   get the current TID, or "def_tid"
*           Topic::archiveID()          get the archive TID
*           Topic::defaultID()          get the default TID
*           Topic::optionList()         create the <option></option> elements
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2017 Lee Garner <lee@leegarner.com>
*   @package    glfusion
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
class Topic
{
    /**
    *   Holder for the current topic ID
    *   @var string */
    private static $current = NULL;

    /**
    *   Array holder for all topic objects
    *   Not sure if this is needed, but need to store all topics somewhere
    *   and if archiveTID() and optionList() get called for the same page load
    *   it will save a query.
    *   @var array */
    private static $all = NULL;

    /**
    *   Cache of topic objects.
    *   Populated by Get() only for requested objects, e.g. the index page.
    *   @var array */
    private static $cache = array();

    /**
    *   Array holder for fields in the instantiated topic.
    *   @var array */
    private $properties = array();


    /**
    *   Get a value from the current topic, or the current topic object
    *
    *   @uses   self::currentID()   To get the current topic ID
    *   @uses   self::Get()         To get a specific topic field value
    *   @param  string  $key        Name of value, if requested
    *   @param  mixed   $default    Default value, NULL to use class default
    *   @return mixed               Value of field, or object if no field. 
    */
    public static function Current($key = NULL, $default = NULL)
    {
        return self::Get(self::$current, $key, $default);
    }


    /**
    *   Sets or resets the current topic, and returns the current topic object
    *
    *   @uses   self::Get()
    *   @param  string  $tid    Topic ID to set
    *   @return object          Current topic object
    */
    public static function setCurrent($tid)
    {
        // First try to get the requested topic. Only if valid is the current
        // topic actually changed.
        $obj = self::Get($tid);
        if ($obj !== NULL) {
            self::$current = $tid;
        }
        return self::Current();
    }


    /**
    *   Get the current topic ID, or the supplied default value if empty.
    *
    *   @uses   self::Get()
    *   @uses   self::isEmpty()
    *   @param  string  $def_tid    Default return value if no current topic
    *   @return string              Current topic ID, or supplied return value
    */
    public static function currentID($def_tid = '')
    {
        return self::isEmpty() ? $def_tid : self::Current()->tid;
    }


    /**
    *   Get the archive topic ID
    *   Returns the first topic ID with the archive flag set. There should
    *   be only one.
    *
    *   @return string      ID of designated archive topic
    */
    public static function archiveID()
    {
        global $_TABLES;

        // Check the topic cache first
        foreach (self::$cache as $tid=>$obj) {
            if ($obj->archive_flag == 1) return $tid;
        }
        // Not found in cache, read from DB
        return DB_getItem($_TABLES['topics'], 'tid', 'archive_flag=1');
    }


    /**
    *   Get the default topic ID
    *   Returns the first topic ID with the is_default flag set. There should
    *   be only one.
    *
    *   @return string      ID of designated archive topic
    */
    public static function defaultID()
    {
        global $_TABLES;

        // Check the topic cache first
        foreach (self::$cache as $tid=>$obj) {
            if ($obj->is_default == 1) return $tid;
        }
        // Not found in cache, read from DB
        return DB_getItem($_TABLES['topics'], 'tid', 'is_default=1');
    }


    /**
    *   Get a selection list of topics.
    *
    *   @uses   self::All()         To get all available topics
    *   @param  string  $selected   Selected topic ID, if any
    *   @param  string  $fld        Name of field to display in list
    *   @param  integer $access     Access level required, default "2" (read)
    *   @return string              Option tags for selection list
    */
    public static function optionList($selected = '', $fld = 'topic', $access = 2)
    {
        $opts = array();
        foreach (self::All() as $tid=>$obj) {
            if (self::Access($tid) < $access) continue;
            $sel = $tid == $selected ? ' selected="selected"' : '';
            $opts[] .= "<option value=\"$tid\"$sel>" .
                    htmlspecialchars($obj->$fld) .
                    '</option>';
        }
        return implode(LB, $opts);
    }


    /**
    *   Get all topic records.
    *   First load the static $all variable with all objects, then return the
    *   array.
    *
    *   @return array   All topic records as objects
    */
    public static function All()
    {
        global $_TABLES;

        if (self::$all === NULL) {
            $sql = "SELECT * FROM {$_TABLES['topics']}";
            $res = DB_query($sql, 1);
            if (DB_error()) {
                COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . ': Unable to read topics table');
            } elseif (DB_numRows($res) == 0) {
                COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . ': No topics found');
            } else {
                while ($A = DB_fetchArray($res, false)) {
                    $retval[$A['tid']] = new self($A);
                }
            }
        }
        return self::$all;
    }


    /**
    *   Clear the current topic.
    */
    public static function Clear()
    {
        self::setCurrent(NULL);
    }


    /**
    *   Determine if the current topic is *not* set
    *
    *   @return boolean     True if not set, False if set
    */
    public static function isEmpty()
    {
        return (self::$current === NULL) ? true : false;
    }


    /**
    *   Constructor.
    *   Reads the topic into the object variables.
    *   Does not set the current topic.
    *
    *   @param  mixed   $tid    Topic ID, or complete topic record from DB
    */
    public function __construct($tid = '')
    {
        if (!empty($tid)) {
            if (is_array($tid)) {
                // A DB record passed in as an array
                $this->setVars($tid, true);
            } else {
                // A single topic ID passed in as a string
                // Gets the info from self::Get() to ensure properties array
                // is populated.
                $this->setVars(self::Get($tid, 'properties'), true);
            }
        }
        // Else, this is an empty object to populate from a form.
    }


    /**
    *   Sets all variables from an array into object members
    *
    *   @param  array   $A      Array of key-value pairs
    *   @param  boolean $fromDB True if reading from DB, False if from a form
    */
    public function setVars($A, $fromDB=false)
    {
        if ($fromDB) {
            foreach ($A as $key=>$value) {
                $this->$key = $value;
            }
        } else {
            // reading from form input not yet supported
        }
    }


    /**
    *   Set a key-value pair in the properties array
    *
    *   @param  string  $key    Property name
    *   @param  mixed   $value  Value of property
    */
    public function __set($key, $value)
    {
        switch ($key) {
        case 'owner_id':
        case 'group_id':
        case 'perm_owner':
        case 'perm_group':
        case 'perm_members':
        case 'perm_anon':
        case 'limitnews':
        case 'sortnum':
            $this->properties[$key] = (int)$value;
            break;
        case 'is_default':
        case 'archive_flag':
        case 'sort_by':
            $this->properties[$key] = $value == 0 ? 0 : 1;
            break;
        case 'sort_dir':
            $this->properties[$key] = $value == 'ASC' ? 'ASC' : 'DESC';
            break;
        case 'tid':
            $this->properties[$key] = COM_sanitizeID($value, false);
            break;
        default:
            $this->properties[$key] = $value;
            break;
        }
    }


    /**
    *   Get a property by name
    *
    *   @param  string  $key    Name of property to return
    *   @return mixed       Value of property, NULL if undefined
    */
    public function __get($key)
    {
        return isset($this->properties[$key]) ? $this->properties[$key] : NULL;
    }


    /**
    *   Wrapper to get a value and return a default for empty topics.
    *
    *   @param  string  $key        Name of value
    *   @param  mixed   $default    Default value, NULL to use class default
    *   @return mixed               Value of field
    */
    public static function Get($tid, $key = NULL, $default = NULL)
    {
        global $_TABLES;

        $obj = NULL;
        $tid = COM_sanitizeID($tid, false);
        if (empty($tid)) return NULL;

        // Get the topic object, first checking cache then the DB
        if (isset(self::$cache[$tid])) {
            $obj = self::$cache[$tid];
        } else {    // Attempt to read the topic
            $sql = "SELECT * FROM {$_TABLES['topics']}
                WHERE tid = '$tid'";
            $res = DB_query($sql, 1);
            if (DB_error()) {
                COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . ': Unable to read topics table');
                return NULL;
            } elseif (DB_numRows($res) == 0) {
                COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . ": Topic $tid not found");
                return NULL;;
            } else {
                $A = DB_fetchArray($res, false);
                self::$cache[$A['tid']] = new self($A);
                $obj = self::$cache[$A['tid']];
            }
        }

        if ($obj === NULL) {
            if ($key !== NULL) {
                // Bad topic, key requested, return default or NULL if none
                if ($default === NULL) {
                    // No default supplied, use some standard ones
                    switch ($key) {
                        case 'limitnews':   $default = 0;       break;
                        case 'sort_by':     $default = 'date';  break;
                        case 'sort_dir':    $default = 'DESC';  break;
                    }
                }
                return $default;
            } else {
                // No value requested, return null object
                return NULL;
            }
        } elseif ($key !== NULL) {
            // Good topic, key requested, return the value
            return $obj->$key;
        } else {
            // Good topic, no key requested, return the topic object
            return $obj;
        }
    }


    /**
    *   Determine the current user's access to a specified topic
    *
    *   @param  integer $tid        Topic to check
    *   @return integer Access level (0=none, 2=read, 3=write)
    */
    public static function Access($tid)
    {
        $t = self::Get($tid);    // shorthand for readability
        if ($t !== NULL) {
            return SEC_hasAccess(
                $t->owner_id, $t->group_id,
                $t->perm_owner, $t->perm_group, $t->perm_members, $t->perm_anon
            );
        } else {
            // Topic not defined, return "no access"
            return 0;
        }
    }

}

?>
