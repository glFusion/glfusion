<?php
/**
*   Class to handle glFusion group-related operations
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2017 Lee Garner <lee@leegarner.com>
*   @package    glfusion
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/

use \glFusion\Cache\Cache;

class Group
{
    /**
    *   Properties of the instantiated object
    *   @var array() */
    private $properties = array();

    /**
    *   Constructor
    *
    *   @param  mixed   $A  Array of properties or group ID
    */
    public function __construct($A='')
    {
        if (is_array($A)) {
            $this->setVars($A, true);
        }
    }


    /**
    *   Setter function. Sanitizes values and sets into $properties
    *
    *   @param  string  $key    Name of property
    *   @param  mixed   $value  Property value
    */
    public function __set($key, $value)
    {
        switch ($key) {
        case 'grp_id':
            $this->properties[$key] = (int)$value;
            break;
        case 'grp_gl_core':
        case 'grp_default':
            $this->properties[$key] = $value == 1 ? 1 : 0;
            break;
        case 'grp_name':
        case 'grp_descr':
            $this->properties[$key] = trim($value);
            break;
        }
    }


    /**
    *   Getter function. Returns property value or NULL if not set
    *
    *   @param  string  $key    Name of property
    *   @return mixed           Value of property
    */
    public function __get($key)
    {
        return isset($this->properties[$key]) ? $this->properties[$key] : NULL;
    }


    /**
    *   Set all property values from DB or form
    *
    *   @param  array   $A          Array of property name=>value
    *   @param  boolean $from_db    True of coming from the DB, False for form
    */
    public function setVars($A, $from_db = true)
    {
        foreach ($A as $key=>$value) {
            $this->$key = $value;
        }
    }


    /**
    *   Gets an array of all groups directly assigned to the specified user.
    *
    *   @param  integer $uid    User ID, default = current user
    *   @return array       Array of grp_name=>grp_id
    */
    public static function getAssigned($uid='')
    {
        global $_TABLES, $_USER, $_SEC_VERBOSE;
        static $runonce = array();

        if (empty($uid)) {
            if (COM_isAnonUser()) {
                $uid = 1;
            } else {
                $uid = $_USER['uid'];
            }
        }
        $uid = (int)$uid;
        if ($uid < 1 ) return array();  // Invalid non-integer $uid

        // Check the static var in case this is called more than once
        // for a page load
        if (array_key_exists($uid, $runonce)) {
            return $runonce[$uid];
        }
        // Then check the glFusion cache to save DB queries
        $cache_key = 'user_group_assigned_' . $uid;
        if (Cache::getInstance()->has($cache_key)) {
            return Cache::getInstance()->get($cache_key);
        }

        // Not found in cache? Perform the DB lookup
        $groups = array();
        $sql = "SELECT ga.ug_main_grp_id, g.grp_name
                FROM {$_TABLES['group_assignments']} ga
                LEFT JOIN {$_TABLES['groups']} g
                    ON g.grp_id = ga.ug_main_grp_id
                WHERE ga.ug_uid = $uid AND g.grp_id IS NOT NULL";
        $result = DB_query($sql, 1);
        if ($result) {
            while ($A = DB_fetchArray($result, false)) {
                $groups[ucfirst($A['grp_name'])] = $A['ug_main_grp_id'];
            }
        }
        ksort($groups);
        $runonce[$uid] = $groups;
        Cache::getInstance()->set($cache_key, $groups, array('groups', 'user_' . $uid));
        return $runonce[$uid];
    }


    /**
    *   Gets an array of all groups that the specified user is in.
    *   Matches the function of SEC_getGroups()
    *
    *   @uses   self::getAssigned()
    *   @param  integer $uid    User ID, default = current user
    *   @return array           Array of grp_name=>grp_id
    */
    public static function getAll($uid='')
    {
        global $_TABLES, $_USER, $_SEC_VERBOSE;
        static $runonce = array();

        $cache = false;
        $groups = array();

        if (empty($uid)) {
            if (COM_isAnonUser()) {
                $uid = 1;
            } else {
                $uid = $_USER['uid'];
            }
        }
        $uid = (int)$uid;
        if ($uid < 0 ) return array();  // Invalid user ID type

        // Check the static var in case this is called more than once
        // for a page load
        if (array_key_exists($uid, $runonce)) {
            return $runonce[$uid];
        }
        // Then check the glFusion cache to save DB queries
        $cache_key = 'user_group_all_' . $uid;
        if (Cache::getInstance()->has($cache_key)) {
            return Cache::getInstance()->get($cache_key);
        }

        // Not in cache? First get directly-assigned memberships, then
        // all inherited ones.
        $groups = self::getAssigned($uid);
        $nrows = count($groups);
        if ($_SEC_VERBOSE) {
            COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . ": got $nrows assigned groups",1);
        }

        $cgroups = array();
        foreach ($groups as $grp_name=>$gid) {
            $cgroups[] = $gid;
        }
        while ($nrows > 0) {
            if (count($cgroups) > 0) {
                $glist = join(',', $cgroups);
                $result = DB_query("SELECT ug_main_grp_id,grp_name
                        FROM {$_TABLES["group_assignments"]} ga
                        LEFT JOIN {$_TABLES["groups"]} g
                            ON g.grp_id = ga.ug_main_grp_id
                        WHERE ga.ug_grp_id IN ($glist) AND g.grp_id IS NOT NULL", 1);
                $nrows = DB_numRows($result);
            } else {
                // No sub-groups found to query
                $nrows = 0;
            }

            if ($nrows > 0) {
                $cgroups = array();
                while ($A = DB_fetchArray($result, false)) {
                    if ($_SEC_VERBOSE) {
                        COM_errorLog(__CLASS__ . '::' . __FUNCTION__ . ":user inherits group {$A['grp_name']}",1);
                    }
                    // If not already in the $groups array, add it and include it
                    // in the next search iteration.
                    if (!in_array($A['ug_main_grp_id'], $groups)) {
                        $cgroups[] = $A['ug_main_grp_id'];
                        $groups[ucfirst($A['grp_name'])] = $A['ug_main_grp_id'];
                    }
                }
            }
        }

        if ( count($groups) == 0 ) {
            $groups = array('All Users' => 2);
        }
        ksort($groups);
        $runonce[$uid] = $groups;
        Cache::getInstance()->set($cache_key, $groups, array('groups', 'user_' . $uid));
        return $runonce[$uid];
    }


    /**
    *   Determines if user belongs to specified group
    *
    *   @param  string  $grp_to_verify      Group we want to see if user belongs to
    *   @param  integer $uid                ID for user to check. If empty current user.
    *   @return boolean     True if user is in group, otherwise False
    */
    public static function inGroup($grp_to_verify, $uid='')
    {
        global $_USER, $_GROUPS;

        if (empty ($uid)) {
            if (COM_isAnonUser()) {
                $uid = 1;
            } else {
                $uid = $_USER['uid'];
            }
        }

        if ( (isset($_USER['uid']) && $uid == $_USER['uid'])) {
            if (empty ($_GROUPS)) {
                $_GROUPS = self::getAll($uid);
            }
            $groups = $_GROUPS;
        } else {
            $groups = self::getAll($uid);
        }
        if (is_numeric($grp_to_verify)) {
            return (in_array($grp_to_verify, $groups)) ? true : false;
        } else {
            // perform case-insensitive comparison
            $lgroups = array_change_key_case($groups, CASE_LOWER);
            $grp_to_verify = strtolower($grp_to_verify);
            return (isset($lgroups[$grp_to_verify])) ? true : false;
        }
    }


    /**
    *   Get the groups that have a specific feature.
    *   Always returns at least a single-element array so that calling
    *   Group::withFeature('feature.name')[0] will match the return of
    *   SEC_getFeatureGroup().
    *
    *   @param  string  $feature    Feature name, e.g. "story.edit"
    *   @param  integer $uid        User ID to check, may be empty
    *   @return array       Array of group IDs
    */
    public static function withFeature($feature, $uid = '')
    {
        global $_TABLES;

        static $groups = array();
        if (!isset($groups[$feature])) {
            $groups[$feature] = array();
            $ugroups = self::getAll($uid);

            $ft_id = (int)DB_getItem($_TABLES['features'], 'ft_id',
                    "ft_name = '".DB_escapeString($feature)."'");
            if ($ft_id > 0 && count($ugroups) > 0) {
                $grouplist = implode (',', $ugroups);
                $sql = "SELECT * FROM {$_TABLES['access']}
                        WHERE acc_ft_id = $ft_id
                        AND acc_grp_id IN ($grouplist)
                        ORDER BY acc_grp_id";
                $res = DB_query($sql, 1);
                if ($res) {
                    while ($A = DB_fetchArray($res, false)) {
                        $groups[$feature][] = (int)$A['acc_grp_id'];
                    }
                }
            } else {
                $groups[$feature][] = 0;
            }
        }
        return $groups[$feature];
    }

}

?>
