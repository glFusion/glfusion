<?php
/**
* glFusion CMS
*
* Class to handle glFusion group-related operations
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2021 by the following authors:
*   Lee Garner      lee AT leegarner DOT com
*
*   @filesource
*/

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Log\Log;

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

        $db = Database::getInstance();

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
                WHERE ga.ug_uid = ? AND g.grp_id IS NOT NULL";

       try {
            $stmt = $db->conn->executeQuery($sql,
                        array($uid),
                        array(Database::INTEGER)
                        );

        } catch(Throwable $e) {
            // Ignore errors or failed attempts
        }

        $data = $stmt->fetchAll(Database::ASSOCIATIVE);

        $stmt->closeCursor();
        if (count($data) < 1) {
            $data = array();
        }
        foreach($data AS $row) {
            $groups[ucfirst($row['grp_name'])] = $row['ug_main_grp_id'];
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
        $cTotalGroups = 0;

        $db = Database::getInstance();

        if (empty($uid)) {
            if (COM_isAnonUser()) {
                $uid = 1;
            } else {
                $uid = $_USER['uid'];
            }
        }
        $uid = (int)$uid;
        if ($uid < 0) {
            return array();  // Invalid user ID type
        }

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

        $cTotalGroups = count($groups);
//        Log::write('system',Log::DEBUG,sprintf("%s::%s got %d assigned groups.",__CLASS__,__FUNCTION__,$cTotalGroups));

        $cgroups = array();
        foreach ($groups as $grp_name=>$gid) {
            $cgroups[] = $gid;
        }
        while ($cTotalGroups > 0) {
            if (count($cgroups) > 0) {
                $sql = "SELECT ug_main_grp_id,grp_name
                        FROM {$_TABLES['group_assignments']} ga
                        LEFT JOIN {$_TABLES['groups']} g
                            ON g.grp_id = ga.ug_main_grp_id
                        WHERE ga.ug_grp_id IN (?) AND g.grp_id IS NOT NULL";

               try {
                    $stmt = $db->conn->executeQuery($sql,
                                array($cgroups),
                                array(Database::PARAM_INT_ARRAY)
                                );

                } catch(Throwable $e) {
                    // Ignore errors or failed attempts
                }
                $data = $stmt->fetchAll(Database::ASSOCIATIVE);
                $stmt->closeCursor();
                $cTotalGroups = count($data);
            } else {
                $cTotalGroups = 0;
                $data = array();
            }

            if ($cTotalGroups > 0) {
                $cgroups = array();
                foreach($data AS $row) {
                    if (!in_array($row['ug_main_grp_id'], $groups)) {
                        $cgroups[] = $row['ug_main_grp_id'];
                        $groups[ucfirst($row['grp_name'])] = $row['ug_main_grp_id'];
                    }
                }
            }
        }
        $groups['All Users'] = 2;
        if (!COM_isAnonUser()) {
            $groups['Logged-in Users'] = 13;
        }

        if (count($groups) == 0) {
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
        global $_USER, $_GROUPS, $_RIGHTS, $_TABLES;

        $rc = false;

        if (empty($grp_to_verify)) return true;

        if (empty ($uid)) {
            if (COM_isAnonUser()) {
                $uid = 1;
            } else {
                $uid = $_USER['uid'];
            }
        }

        // we will return true if a member of Root or has system.root permission
        // 'remote users' group is handled special - it will only return if an actual member of this group

        if ( (isset($_USER['uid']) && $uid == $_USER['uid'])) {
            if (empty ($_GROUPS)) {
                $_GROUPS = self::getAll($uid);
            }
            $groups = $_GROUPS;
            $rights = $_RIGHTS;
        } else {
            $groups = self::getAll($uid);
            $rights = explode( ',', SEC_getUserPermissions(0,$uid));
        }

        if (is_numeric($grp_to_verify)) {
            $rc = (in_array($grp_to_verify, $groups)) ? true : false;
            if ($rc === false) {
                $db = Database::getInstance();
                $remotegroup = $db->getItem($_TABLES['groups'],'grp_id',array('grp_name' => 'remote users'),array(Database::STRING));
                if ($grp_to_verify != $remotegroup) {
                    if (in_array(1,$groups) || in_array('system.root',$rights)) {
                        $rc = true;
                    }
                }
            }
            return $rc;
        } else {
            // perform case-insensitive comparison
            $lgroups = array_change_key_case($groups, CASE_LOWER);
            $grp_to_verify = strtolower($grp_to_verify);

            $rc = (isset($lgroups[$grp_to_verify])) ? true : false;

            if ($rc === false && strcmp($grp_to_verify,'remote users') !== 0) {
                if (isset($lgroups['root']) || in_array('system.root',$rights)) {
                    $rc = true;
                }
            }
            return $rc;
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

        $db = Database::getInstance();

        static $groups = array();
        if (!isset($groups[$feature])) {
            $groups[$feature] = array();
            $ugroups = self::getAll($uid);

            $ft_id = (int) $db->getItem($_TABLES['features'],'ft_id',array('ft_name' => $feature));

            if ($ft_id > 0 && count($ugroups) > 0) {
               $sql = "SELECT * FROM {$_TABLES['access']}
                        WHERE acc_ft_id = ?
                        AND acc_grp_id IN (?)
                        ORDER BY acc_grp_id";

               try {
                    $stmt = $db->conn->executeQuery($sql,
                                array($ft_id,$ugroups),
                                array(Database::INTEGER,Database::PARAM_INT_ARRAY)
                                );

                } catch(Throwable $e) {
                    $groups[$feature][] = 0;
                }
                $data = $stmt->fetchAll(Database::ASSOCIATIVE);
                $stmt->closeCursor();
                foreach($data AS $row) {
                    $groups[$feature][] = (int)$row['acc_grp_id'];
                }
            } else {
                $groups[$feature][] = 0;
            }
        }
        return $groups[$feature];
    }

  /**
    *   Gets an array of all groups available
    *
    *   @return array           Array of grp_name=>grp_id
    */
    public static function getAllAvailable()
    {
        global $_TABLES, $_USER, $_SEC_VERBOSE;

        $cache = false;
        $groups = array();

        $db = Database::getInstance();

        // Then check the glFusion cache to save DB queries
        $cache_key = 'group_all_available';
        if (Cache::getInstance()->has($cache_key)) {
            return Cache::getInstance()->get($cache_key);
        }

        // Not in cache? First get directly-assigned memberships, then
        // all inherited ones.
        $sql = "SELECT grp_id,grp_name
                FROM {$_TABLES['groups']} g";

        try {
            $stmt = $db->conn->executeQuery($sql,
                        array(),
                        array()
                        );

        } catch(Throwable $e) {
            // Ignore errors or failed attempts
        }
        $data = $stmt->fetchAll(Database::ASSOCIATIVE);
        $stmt->closeCursor();
        foreach($data AS $row) {
            $groups[ucfirst($row['grp_name'])] = $row['grp_id'];
        }
        $groups['All Users'] = 2;
        $groups['Logged-in Users'] = 13;
        ksort($groups);
        Cache::getInstance()->set($cache_key, $groups, array('groups', 'group_'));
        return $groups;
    }
}

?>
