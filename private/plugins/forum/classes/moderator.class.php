<?php
/**
*   Class to handle Forum moderators.
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2018 Lee Garner <lee@leegarner.com>
*   @package    glfusion
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
namespace Forum;

class Moderator
{
    /**
    *   Properties of the instantiated object
    *   @var array() */
    private $properties = array();

    /**
    *   Cache of moderator objects
    *   @var array() */
    private static $cache = array();


    /**
    *   Constructor
    *
    *   @param  mixed   $A  Array of properties or group ID
    */
    public function __construct($A='')
    {
        if (is_array($A)) {
            $this->setVars($A, true);
        } elseif (is_numeric($A)) {
            $A = (int)$A;
            $this->Read($A);
        }
    }


    /**
    *   Get a moderator object
    *
    *   @param  integer $mod_id     Moderator record ID
    *   @return object      Moderator object, will be NULL if invalid
    */
    public static function getInstance($mod_id)
    {
        $mod_id = (int)$mod_id;
        if (!array_key_exists(self::$cache[$mod_id])) {
            self::$cache[$mod_id] = new self($mod_id);
        }
        return self::$cache[$mod_id];
    }


    /**
    *   Get the moderator record for a specific user.
    *   First scans the $cache array to see if it's been read in already.
    *
    *   @param  integer $forum  Forum ID
    *   @param  integer $uid    User ID
    *   @return object          Moderator object, NULL if not found
    */
    public static function getByUser($forum, $uid)
    {
        global $_TABLES;

        $uid = (int)$uid;
        $forum = (int)$forum;
        foreach (self::$cache as $mod) {
            if ($mod->mod_uid == $uid && $mod->mod_forum == $forum) {
                return $mod;
            }
        }
        $sql = "SELECT * FROM {$_TABLES['ff_moderators']}
                WHERE mod_forum = $forum AND mod_uid = $uid";
        $res = DB_query($sql);
        if ($res && DB_numRows($res) == 1) {
            $A = DB_fetchArray($res, false);
            self::$cache[$A['mod_id']] = new self($A);
            return self::$cache[$A['mod_id']];
        }
        return NULL;
    }


    /**
    *   Get the moderator record for a given group.
    *   First scans the $cache array to see if it's been read in already.
    *
    *   @param  integer $forum  Forum ID
    *   @param  integer $gid    Group ID
    *   @return object          Moderator object, NULL if not found
    */
    public static function getByGroup($forum, $gid)
    {
        global $_TABLES;

        $gid = (int)$gid;
        $forum = (int)$forum;
        foreach (self::$cache as $mod) {
            if ($mod->mod_groupid == $gid && $mod->mod_forum == $forum) {
                return $mod;
            }
        }
        $sql = "SELECT * FROM {$_TABLES['ff_moderators']}
                WHERE mod_forum = $forum AND mod_groupid = $gid";
        $res = DB_query($sql);
        if ($res && DB_numRows($res) == 1) {
            $A = DB_fetchArray($res, false);
            self::$cache[$A['mod_id']] = new self($A);
            return self::$cache[$A['mod_id']];
        }
        return NULL;
    }


    /**
    *   Read a singl moderator record by ID
    *
    *   @param  integer $mod_id     Moderator record ID
    *   @return boolean     True on success, False on failure
    */
    public function Read($mod_id)
    {
        global $_TABLES;

        $sql = "SELECT * FROM {$_TABLES['ff_moderators']}
                WHERE mod_id = " . (int)$mod_id;
        $res = DB_query($sql);
        if ($res && DB_numRows($res) == 1) {
            $A = DB_fetchArray($res, false);
            $this->setVars($A, true);
            return true;
        } else {
            return false;
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
        case 'mod_id':
        case 'mod_uid':
        case 'mod_groupid':
        case 'mod_forum':
            $this->properties[$key] = (int)$value;
            break;
        case 'mod_delete':
        case 'mod_ban':
        case 'mod_edit':
        case 'mod_move':
        case 'mod_stick':
            $this->properties[$key] = $value == 1 ? 1 : 0;
            break;
        case 'mod_username':
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
        if (!$from_db) {    // form has different names for perms
            $this->mod_delete = isset($A['chk_delete']) ? 1 : 0;
            $this->mod_ban = isset($A['chk_ban']) ? 1 : 0;
            $this->mod_edit = isset($A['chk_edit']) ? 1 : 0;
            $this->mod_move = isset($A['chk_move']) ? 1 : 0;
            $this->mod_stick = isset($A['chk_stick']) ? 1 : 0;
        }
        return true;
    }


    /**
    *   Gets an array of all the moderator records for a forum.
    *
    *   @param  integer $forum  Forum ID
    *   @return array           Array of grp_name=>grp_id
    */
    public static function getAll($forum = 0)
    {
        global $_TABLES;

        static $cache = array();;

        if (!array_key_exists($forum, $cache)) {
            $forum = (int)$forum;
            $cache[$forum] = array();
            $sql = "SELECT * FROM {$_TABLES['ff_moderators']}";
            if ($forum > 0) $sql .= " WHERE mod_forum = '$forum'";
            $res = DB_query($sql);
            if ($res) {
                while ($A = DB_fetchArray($res, false)) {
                    $cache[$forum][$A['mod_id']] = new self($A);
                }
            }
        }
        return $cache[$forum];
    }


    /**
    *   Get all the moderator permissions to a forum for the current user.
    *
    *   @param  integer $forum  Forum ID
    *   @return array           Array of all (perm => 1/0) for all perms.
    */
    public static function getPerms($forum)
    {
        global $_USER;

        static $cache = array();
        $forum = (int)$forum;
        if (array_key_exists($forum, $cache)) {
            return $cache[$forum];
        }

        $uid = (int)$_USER['uid'];

        // Default return - no rights
        $retval = array(
            'mod_delete'    => 0,
            'mod_ban'       => 0,
            'mod_edit'      => 0,
            'mod_move'      => 0,
            'mod_stick'     => 0,
        );

        if ($uid < 2) {
            // Anonymous has no rights, use default $retval
        } elseif (\Group::inGroup(1, $uid) || SEC_hasRights('forum.edit') ) {
            // Root and Forum Editors have all moderation rights
            $retval = array(
                'mod_delete'    => 1,
                'mod_ban'       => 1,
                'mod_edit'      => 1,
                'mod_move'      => 1,
                'mod_stick'     => 1,
            );
        } else {
            $mods = self::getAll($forum);
            if (!empty($mods)) {
                foreach ($mods as $mod) {
                    if ( $mod->mod_uid == $uid ||
                        ($mod->mod_groupid > 0 && \Group::inGroup($mod->mod_groupid, $uid)) ) {
                        // Matching moderator found, accumulate rights
                        $retval['mod_delete'] += $mod->mod_delete;
                        $retval['mod_ban'] += $mod->mod_ban;
                        $retval['mod_edit'] += $mod->mod_edit;
                        $retval['mod_move'] += $mod->mod_move;
                        $retval['mod_stick'] += $mod->mod_stick;
                    }
                }
            }
        }
        $cache[$forum] = $retval;
        return $cache[$forum];
    }


    /**
    *   Determine if the current user has one or any moderator permission
    *
    *   @param  integer $forum      Forum ID
    *   @param  string  $permission Permission to check, empty for "any"
    */
    public static function hasPerm($forum, $permission = '')
    {
        $perms = self::getPerms($forum);
        if ($permission != '') {
            // Checking for a specific permission. See if it is set and non-zero
            if (isset($perms[$permission]) && $perms[$permission]) return true;
        } else {
            // Just checking for any moderator permission at all
            foreach ($perms as $perm=>$value) {
                if ($value) return true;
            }
        }
        return false;
    }


    /**
    *   Save records from a form.
    *
    *   @param  array   $A  Array of form vars
    *   @return boolean     True on success, False on failure
    */
    public function Save($A = array())
    {
        global $_TABLES;

        if (!empty($A)) {
            $this->setVars($A, false);
        }

        // Variables always updated regardless whether user or group
        $sql2 = "mod_delete = {$this->mod_delete},
                mod_ban = {$this->mod_ban},
                mod_edit = {$this->mod_edit},
                mod_move = {$this->mod_move},
                mod_stick = {$this->mod_stick}";

        if ($A['modtype'] == 'user') {
            foreach ($A['sel_user'] as $mod_uid) {
                $this->mod_uid = $mod_uid;
                $this->mod_username = DB_getItem($_TABLES['users'], "username","uid='{$this->mod_uid}'");
                foreach ($A['sel_forum'] as $modForum) {
                    $this->mod_forum = $modForum;
                    // See if an existing record exists for the user/forum
                    $mod = self::getByUser($this->mod_forum, $this->mod_uid);
                    if ($mod) {
                        $this->mod_id = $mod->mod_id;
                        $sql1 = "UPDATE {$_TABLES['ff_moderators']} SET ";
                        $sql3 = " WHERE mod_id = {$this->mod_id}";
                    } else {
                        $sql1 = "INSERT INTO {$_TABLES['ff_moderators']} SET ";
                        $sql3 = '';
                    }
                    $sql2 = "mod_uid = {$this->mod_uid},
                            mod_username = '{$this->mod_username}',
                            mod_groupid = 0,
                            mod_forum = {$this->mod_forum}, " . $sql2;
                    $sql = $sql1 . $sql2 . $sql3;
                    DB_query($sql);
                }
            }
        } elseif ($A['modtype'] == 'group' && $A['sel_group'] > 0)  {
            $this->mod_groupid = $A['sel_group'];
            foreach ($A['sel_forum'] as $modForum) {
                $this->mod_forum = $modForum;
                // See if an existing record exists for the group/forum
                $mod = self::getByGroup($this->mod_forum, $this->mod_groupid);
                if ($mod) {
                    $sql1 = "UPDATE {$_TABLES['ff_moderators']} SET ";
                    $sql3 = "WHERE mod_id = {$mod->mod_id}";
                } else {
                    $sql1 = "INSERT INTO {$_TABLES['ff_moderators']} SET ";
                    $sql3 = '';
                }
                $sql2 = "mod_uid = 0,
                        mod_username = '',
                        mod_groupid = {$this->mod_groupid},
                        mod_forum = {$this->mod_forum}, " . $sql2;
                $sql = $sql1 . $sql2 . $sql3;
                DB_query($sql);
            }
        }
        return DB_error() ? false : true;
    }


    /**
    *   Delete a specific moderator record
    *
    *   @param  integer $mod_id     Moderator record ID
    */
    public static function Delete($mod_id)
    {
        global $_TABLES;

        DB_delete($_TABLES['ff_moderators'], 'mod_id', (int)$mod_id);
    }


    /**
    *   The forum is being deleted, delete all the related moderator records
    *
    *   @param  integer $forum_id   ID of forum being deleted
    */
    public static function forumDelete($forum_id)
    {
        global $_TABLES;

        DB_delete($_TABLES['ff_moderators'], 'mod_forum', (int)$forum_id);
    }


    /**
    *   Get the moderator editing form
    *
    *   @return  string     Form HTML
    */
    public static function Edit($mod_id = 0)
    {
        global $_CONF, $LANG_GF93, $LANG_GF01, $_TABLES;;

        $T = new \Template($_CONF['path'] . 'plugins/forum/templates/admin/');
        $T->set_file (array('moderator'=>'mod_add.thtml'));
        $T->set_var(array(
            'action_url'    => $_CONF['site_admin_url'] . '/plugins/forum/mods.php',
            'sel_forums'    => COM_optionList($_TABLES['ff_forums'], 'forum_id,forum_name'),
            'sel_users'     => COM_optionList($_TABLES['users'], 'uid,username'),
            'sel_groups'    => COM_optionList($_TABLES['groups'], 'grp_id,grp_name'),
       ) );
        $T->parse('output', 'moderator');
        return $T->finish($T->get_var('output'));
    }


    /**
    *   Get the admin listing of moderators
    *
    *   @return string      HTML for admin list
    */
    public static function AdminList()
    {
        global $_CONF, $_TABLES, $LANG_GF01;

        $T = new \Template($_CONF['path'] . 'plugins/forum/templates/admin/');
        $T->set_file(array(
            'moderators' => 'moderators.thtml',
            'mod_record' => 'mod_record.thtml',
        ) );
        if (isset($_POST['filtermode']) && $_POST['filtermode'] == 'group') {
            $filterbygroup = true;
            $T->set_var(array(
                'groupfilter'   => 'checked="checked"',
                'userfilter'    => '',
                'LANG_HEADING2' => $LANG_GF01['GROUP'],
            ) );
        } else {
            $filterbygroup = false;
            $T->set_var(array(
                'userfilter'    => 'checked="checked"',
                'groupfilter'   => '',
                'LANG_HEADING2' => $LANG_GF01['USER'],
            ) );
        }
        $selected_forum = isset($_POST['sel_forum']) ? (int)$_POST['sel_forum'] : 0;
        $T->set_var('sel_forums', COM_optionList($_TABLES['ff_forums'],
                        'forum_id,forum_name', $selected_forum));
        $mods = self::getAll($selected_forum);

        $i = 1;
        foreach ($mods as $M) {
            if ($filterbygroup) {
                if ($M->mod_groupid == 0) continue;
                $T->set_var('name', DB_getItem($_TABLES['groups'],'grp_name', "grp_id='{$M->mod_groupid}'"));
            } else {
                if ($M->mod_uid == 0) continue;
                $T->set_var('name', $M->mod_username);
            }
            $T->set_var(array(
                'id'            => $M->mod_id,
                'action_url'    => $_CONF['site_admin_url'] . '/plugins/forum/mods.php',
                'forum' => DB_getITEM($_TABLES['ff_forums'], 'forum_name', "forum_id={$M->mod_forum}"),
                'delete_yes'    => $M->mod_delete ? 'checked="checked"' : '',
                'ban_yes'       => $M->mod_ban ? 'checked="checked"' : '',
                'edit_yes'      => $M->mod_edit ? 'checked="checked"' : '',
                'move_yes'      => $M->mod_move ? 'checked="checked"' : '',
                'stick_yes'     => $M->mod_stick ? 'checked="checked"' : '',
                'cssid'         => ($i%2)+1,
            ) );
            $T->parse('moderator_records', 'mod_record', true);
            $i++;
        }
        $T->parse('output', 'moderators');
        return $T->finish($T->get_var('output'));
    }
}

?>
