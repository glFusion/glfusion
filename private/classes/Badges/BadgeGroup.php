<?php
/**
 * Class to handle user badge groups.
 * Groups allow related badges to be grouped together, and for multiple or
 * only one badge to be shown from a group.
 *
 * @author     Lee Garner <lee@leegarner.com>
 * @copyright  Copyright (c) 2022 Lee Garner <lee@leegarner.com>
 * @package    glfusion
 * @version    v0.0.1
 * @license    http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Badges;
use glFusion\Group;
use glFusion\Log\Log;
use glFusion\Database\Database;
use glFusion\Cache\Cache;


/**
 * User Badge Group class.
 * @package glfusion
 */
class BadgeGroup
{
    /** Cache tags affecting user group membership.
     * @var array */
    private static $cache_tags = array('badges', 'user_group', 'groups', 'f');

    /** Badge Group record ID.
     * @var integer */
    private $id = 0;

    /** Sort order.
     * @var integer */
    private $order = 9999;

    /** Group Name.
     * @var string */
    private $name = '';

    /** Flag indicating only the first badge will be returned for a user.
     * @var boolean */
    private $singular = 1;

    /** Flag indicating the badge group is enabled.
     * @var boolean */
    private $enabled = 1;


    /**
     * Constructor.
     * Sets the field values from the supplied array, or reads the record
     * if $A is a badge ID.
     *
     * @param  mixed   $A  Array of properties or group ID
     */
    public function __construct(?array $A = NULL)
    {
        if (is_array($A)) {
            $this->setVars($A, true);
        }
    }


    /**
     * Get a badge object by record ID.
     *
     * @param   integer $id     Badge ID
     * @return  object      Badge object
     */
    public static function getById(int $id) : self
    {
        $retval = new self;
        if ($id > 0) {
            $retval->Read($id);
        }
        return $retval;
    }


    /**
     * Read a single badge record into an instantiated object.
     *
     * @param   integer $id  Badge record ID
     * @return  boolean     True on success, False on error or not found
     */
    public function Read(int $id) :bool
    {
        global $_TABLES;

        $db = Database::getInstance();
        try {
            $stmt = $db->conn->executeQuery(
                "SELECT * FROM {$_TABLES['badge_groups']}
                WHERE bg_id = ?",
                array($id),
                array(Database::INTEGER)
            );
            $A = $stmt->fetch(Database::ASSOCIATIVE);
        } catch (\Throwable $e) {
            Log::write('system', Log::ERROR, $e->getMessage());
            return false;
        }
        if ($A && is_array($A)) {
            $this->setVars($A);
        }
        return true;
    }


    /**
     * Set all property values from DB or form.
     *
     * @param   array   $A          Array of property name=>value
     * @param   boolean $from_db    True of coming from the DB, False for form
     */
    public function setVars(array $A, bool $from_db = true) : self
    {
        $this->setId($A['bg_id'])
             ->setEnabled(isset($A['bg_enabled']) ? $A['bg_enabled'] : 0)
             ->setSingular(isset($A['bg_singular']) ? $A['bg_singular'] : 0)
             ->setOrder($A['bg_order'])
             ->setName($A['bg_name']);
        return $this;
    }


    /**
     * Set the badge record ID.
     *
     * @param   integer $id     Badge ID
     * @return  object  $this
     */
    public function setId(int $id) : self
    {
        $this->id = (int)$id;
        return $this;
    }


    /**
     * Get the badge group record ID.
     *
     * @return  integer     Record ID
     */
    public function getId() : int
    {
        return (int)$this->id;
    }


    /**
     * Set the badge group name.
     *
     * @param   string  $name   Group Name
     * @return  object  $this
     */
    public function setName(string $name) : self
    {
        $this->name = $name;
        return $this;
    }


    /**
     * Set the enabled flag.
     *
     * @param   integer $enabled    Enabled flag value
     * @return  object  $this
     */
    public function setEnabled(int $enabled) : self
    {
        $this->enabled = $enabled ? 1 : 0;
        return $this;
    }


    /**
     * Set the sort order for the badge.
     *
     * @param   integer $order  Value for sorting
     * @return  object  $this
     */
    public function setOrder(int $order) : self
    {
        $this->order = (int)$order;
        return $this;
    }


    /**
     * Get the group's sort order as an integer.
     *
     * @return  integer     Sort order value
     */
    public function getOrder() : int
    {
        return (int)$this->order;
    }


    /**
     * Set the single-badge flag for this group.
     *
     * @param   integer $flag   1 for single, 0 for multiple
     * @return  object  $this
     */
    public function setSingular(int $flag) : self
    {
        $this->singular = $flag ? 1 : 0;
        return $this;
    }


    /**
     * See if this badge group returns only one single badge, or multiple.
     *
     * @return  integer     1 if singular, 0 if multiple
     */
    public function isSingular() : int
    {
        return $this->singular ? 1 : 0;
    }


    /**
     * See if this badge group is the configured primary group.
     *
     * @return  boolean     True if configured as primary
     */
    public function isPrimary() : bool
    {
        global $_CONF;

        return $this->id == $_CONF['badge_primary_grp'];
    }


    /**
     * Gets an array of all the badge groups.
     *
     * @param  boolean $enabled    True to get only enabled badges
     * @return array           Array of grp_name=>grp_id
     */
    public static function optionList(?int $sel = NULL) : string
    {
        global $_TABLES;
        return COM_optionList(
            $_TABLES['badge_groups'],
            'bg_id,bg_name',
            $sel,
            'bg_order'
        );
    }


    /**
     * Reset all the order fields to increment by 10.
     */
    public static function reOrder() : void
    {
        global $_TABLES;

        $db = Database::getInstance();
        try {
            $data = $db->conn->executeQuery(
                "SELECT bg_id, bg_order FROM {$_TABLES['badge_groups']}
                ORDER BY bg_order ASC")
                             ->fetchAll(Database::ASSOCIATIVE);
        } catch (\Throwable $e) {
            Log::write('system',Log::ERROR,'Badge::reOrder() SQL error: '.$e->getMessage());
            $data = NULL;
        }
        if (is_array($data)) {
            $sql = "UPDATE {$_TABLES['badge_groups']}
                    SET bg_order = ?
                    WHERE bg_id = ?";
            $order = 10;
            foreach ($data as $A) {
                if ($order != $A['bg_order']) {
                    try {
                        $db->conn->executeUpdate(
                            $sql,
                            array($order, $A['bg_id']),
                            array(Database::INTEGER, Database::INTEGER)
                        );
                    } catch (\Throwable $e) {
                        // Log the error but keep going
                        Log::write(
                            'system',
                            Log::ERROR,
                            'BadgeGroup::reOrder() SQL error: '.$e->getMessage()
                        );
                    }
                }
                $order += 10;
            }
        }
    }


    /**
     * Move a badge group up or down the list.
     *
     * @param   integer $id     Badge database ID
     * @param   string  $where  Direction to move (up or down)
     */
    public static function Move(int $id, string $where) : void
    {
        global $_TABLES;

        $retval = '';
        $id = (int)$id;

        switch ($where) {
        case 'up':
            $oper = '-';
            break;
        case 'down':
            $oper = '+';
            break;
        default:
            return;
        }
        $Badge = self::getById((int)$id);
        $db = Database::getInstance();
        $sql = "UPDATE {$_TABLES['badge_groups']}
                SET bg_order = bg_order $oper 11
                WHERE bg_id = ?";
        try {
            $stmt = $db->conn->executeUpdate(
                $sql,
                array($id),
                array(Database::INTEGER)
            );
            self::reOrder();
        } catch (\Throwable $e) {
            Log::write('system',Log::ERROR,'BadgeGroup::moveRow() SQL error: '.$e->getMessage());
        }
    }


    /**
     * Creates the edit form.
     *
     * @return  string      HTML for edit form
     */
    public function Edit() : string
    {
        global $_TABLES, $_CONF;

        $T = new \Template($_CONF['path_layout'] . 'admin/badges/');
        $T->set_file('editform', 'groupeditor.thtml');
        $T->set_var(array(
            'bg_id'     => $this->id,
            'bg_name'   => $this->name,
            'bg_order'  => $this->order,
            'ena_chk'   => $this->enabled ? 'checked="checked"' : '',
            'sing_chk'  => $this->singular ? 'checked="checked"' : '',
        ) );
        $T->parse('output','editform');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Save a badge group from the edit form.
     *
     * @param  array   $A      Array of fields, e.g. $_POST
     * @return string      Error messages, empty string on success
     */
    public function Save(?array $A = NULL) : string
    {
        global $_TABLES, $LANG_ADMIN;

        if (is_array($A) && !empty($A)) {
            $this->setVars($A, false);
        }

        $db = Database::getInstance();
        if ($this->id > 0) {
            $sql1 = "UPDATE {$_TABLES['badge_groups']} SET ";
            $sql3 = ' WHERE bg_id = :id';
        } else {
            $sql1 = "INSERT INTO {$_TABLES['badge_groups']} SET ";
            $sql3 = '';
        }

        $sql2 = "bg_name = :name,
            bg_order = :order,
            bg_enabled = :enabled,
            bg_singular = :singular";
        $sql = $sql1 . $sql2 . $sql3;
        $params = array(
            'id' => $this->id,
            'name' => $this->name,
            'order' => $this->order,
            'enabled' => $this->enabled,
            'singular' => $this->singular,
        );
        $types = array(
            Database::INTEGER,
            Database::STRING,
            Database::INTEGER,
            Database::INTEGER,
            Database::INTEGER,
        );

        try {
            $db->conn->executeUpdate($sql, $params, $types);
            self::reOrder();
            Cache::getInstance()->deleteItemsByTags(self::$cache_tags);
            return '';
        } catch (\Throwable $e) {
            Log::write('system', Log::ERROR, $e->getMessage());
            return $LANG_ADMIN['save_error'];
        }
    }


    /**
     * Delete a single badge group record.
     * Sets all related badges to group "0" and disables them.
     *
     * @param  integer $bid  Badge ID to delete
     */
    public static function Delete(int $id) : void
    {
        global $_TABLES;
        $db = Database::getInstance();

        // Move any badges to the Misc. group and disable them.
        try {
            $db->conn->executeUpdate(
                "UPDATE {$_TABLES['badges']}
                SET bg_id = 1, enabled = 0
                WHERE bg_id = ?",
                array($id),
                array(Database::INTEGER)
            );
        } catch (\Throwable $e) {
            Log::write('system', Log::ERROR, $e->getMessage());
            return;
        }

        // Then delete the badge group.
        try {
            $db->conn->delete(
                $_TABLES['badge_groups'],
                array('bg_id' => $id),
                array(Database::INTEGER)
            );
        } catch (\Throwable $e) {
            Log::write('system', Log::ERROR, $e->getMessage());
        }
    }


    /**
     * Toggle a database field for a badge record.
     *
     * @param   integer $id     Badge record ID
     * @param   string  $field  Field to toggle
     * @param   integer $oldval Old value to be changed
     * @return  integer     New value on success, Old value on error
     */
    public static function Toggle(int $id, string $field, int $oldval) : int
    {
        global $_TABLES;

        $db = Database::getInstance();
        $id = (int)$id;
        if ($id < 1) return $oldval;
        $oldval = $oldval == 0 ? 0 : 1; // sanitize
        $newval = $oldval == 1 ? 0 : 1; // toggle to opposite
        switch ($field) {
        case 'enabled':
        case 'singular':
            try {
                $stmt = $db->conn->executeUpdate(
                    "UPDATE {$_TABLES['badge_groups']}
                    SET bg_{$field} = ?
                    WHERE bg_id = ?",
                    array($newval, $id),
                    array(Database::INTEGER, Database::INTEGER)
                );
            } catch (\Throwable $e) {
                Log::write('system', Log::ERROR, $e->getMessage());
                $newval = $oldval;
            }
            break;
        default:
            // unsupported change attempted
            $newval = $oldval;
            break;
        }
        return $newval;
    }


    /**
     * Create a new badge group from a supplied name.
     *
     * @param   string  $name   Badge Group name
     * @return  integer     Record ID of the badge group
     */
    public static function createFromName(string $name) : int
    {
        global $_TABLES;

        $db = Database::getInstance();
        try {
            $bg_id = $db->getItem(
                $_TABLES['badge_groups'],
                'bg_id',
                array('bg_name' => $name),
                array(Database::STRING),
            );
        } catch (\Throwable $e) {
            Log::write('system', Log::ERROR, $e->getMessage());
            $bg_id = false;
        }

        if (!$bg_id) {     // create the badge group
            try {
                $db->conn->executeUpdate(
                    "INSERT INTO {$_TABLES['badge_groups']} (bg_name)
                    VALUES (?)",
                    array($name),
                    array(Database::STRING)
                );
                $bg_id = $db->conn->lastInsertId();
            } catch (\Throwable $e) {
                Log::write('system', Log::ERROR, $e->getMessage());
                $bg_id = false;
            }
        }
        return $bg_id;
    }


    public static function getAll() : array
    {
        global $_TABLES;

        $retval = NULL;
        $cache_key = 'badge_groups_enabled';
        $Cache = Cache::getInstance();
        if ($Cache->has($cache_key)) {
            $retval = $Cache->get($cache_key);
        }
        if (!is_array($retval)) {
            $retval = array();
            $db = Database::getInstance();
            try {
                $data = $db->conn->executeQuery(
                    "SELECT * FROM {$_TABLES['badge_groups']}
                    WHERE bg_enabled = 1
                    ORDER BY bg_order ASC"
                )->fetchAll(Database::ASSOCIATIVE);
            } catch (\Throwable $e) {
                Log::write('system', Log::ERROR, $e->getMessage());
                $data = NULL;
            }
            if (is_array($data)) {
                foreach ($data as $A) {
                    $retval[$A['bg_id']] = new self($A);
                }
            }
            $Cache->set($cache_key, $retval, array('badges'));
        }
        return $retval;
    }

}

