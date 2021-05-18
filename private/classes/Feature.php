<?php
/**
 * Class to handle glFusion feature-related operations.
 * Allows administrators to update the feature-to-group mappings.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion;


/**
 * Feature class.
 * @package glfusion
 */
class Feature
{
    /** Feature record ID.
     * @var integer */
    private $ft_id = 0;

    /** Feature name.
     * @var string */
    private $ft_name = '';

    /** Feature description.
     * @var string */
    private $ft_descr = '';

    /** Flag indicating a core glFusion feature.
     * @var integer */
    private $ft_gl_core = 0;

    /** Cache array of feature objects
     * @var array() */
    private static $cache = array();


    /**
     * Constructor.
     *
     * @param  mixed   $A  Array of properties or group ID
     */
    public function __construct($A='')
    {
        if (is_array($A)) {
            $this->setVars($A, true);
        }
    }


    /**
     * Get a feature object by feature name.
     *
     * @param   string  $ft_name    Feature Name
     * @return  object|NULL     Feature object, NULL if not found
     */
    public static function getByName($ft_name)
    {
        global $_TABLES;

        if (isset(self::$cache[$ft_name])) {
            return self::$cache[$ft_name];
        }

        $sql = "SELECT * FROM {$_TABLES['features']}
                WHERE ft_name = '" . DB_escapeString($ft_name) . "'";
        $res = DB_query($sql, 1);
        if ($res && DB_numRows($res) > 0) {
            $A = DB_fetchArray($res, false);    
            self::$cache[$ft_name] = new self($A);
            return self::$cache[$ft_name];
        } else {
            return new self;
        }
    }


    /**
     * Get a feature by its record ID.
     *
     * @param   integer $ft_id  Feature ID
     * @return  object      Feature object
     */
    public static function getById($ft_id)
    {
        global $_TABLES;

        // share the cache table
        if (isset(self::$cache[$ft_id])) {
            return self::$cache[$ft_id];
        }

        $sql = "SELECT * FROM {$_TABLES['features']}
                WHERE ft_id = " . (int)$ft_id;
        $res = DB_query($sql, 1);
        if ($res && DB_numRows($res) > 0) {
            $A = DB_fetchArray($res, false);    
            self::$cache[$ft_id] = new self($A);
            return self::$cache[$ft_id];
        } else {
            return new self;
        }
    }


    /**
     * Get the display value of the feature.
     *
     * @return  string  Feature name
     */
    public function __toString()
    {
        return $this->ft_name;
    }


    /**
     * Set all property values from DB or form.
     *
     * @param   array   $A          Array of property name=>value
     * @return  object  $this
     */
    public function setVars($A)
    {
        if (isset($A['ft_id'])) {
            $this->ft_id = (int)$A['ft_id'];
        }
        if (isset($A['ft_name'])) {
            $this->ft_name = $A['ft_name'];
        }
        if (isset($A['ft_descr'])) {
            $this->ft_descr = $A['ft_descr'];
        }
        if (isset($A['ft_gl_core'])) {
            $this->ft_gl_core = (int)$A['ft_gl_core'];
        }
        return $this;
    }


    /**
     * Gets everything a user has permissions to within the system.
     * This is part of the glFusion security implmentation.  This function
     * will get all the permissions the current user has call itself recursively.
     *
     * @param   integer $uid    User to check, if empty current user.
     * @return  array           Array of features the user has access to
     */
    public static function getByUser($uid='')
    {
        global $_TABLES, $_USER, $_GROUPS;

        static $rights = array();

        // Get user ID if we don't already have it
        if (empty ($uid)) {
            $uid = COM_isAnonUser() ? 1 : (int)$_USER['uid'];
        }
        if (isset($rights[$uid])) return $rights[$uid]; // already cached this user

        $rights[$uid] = array();
        $groups = \Group::getAll($uid);
        if ( count($groups) > 0 ) {
            $glist = implode(',', $groups);
            $sql = "SELECT f.* FROM {$_TABLES['access']} ac
                LEFT JOIN {$_TABLES['features']} ft
                    ON ft.ft_id = ac.acc_ft_id
                WHERE ac.acc_grp_id IN ($glist) AND ft.ft_name IS NOT NULL
                GROUP BY ft.ft_name";
            $res = DB_query($sql);
            if ($res) {
                while ($A = DB_fetchArray($res, false)) {
                    $rights[$uid][$A['ft_id']] = new self($A);
                }
            }
        }
        return $rights[$uid];
    }


    /**
     * Gets all the rights for a user as a comma-separated string.
     *
     * @param   integer $uid    User ID to check, empty for current user
     * @return  string          Comma-separated list of rights
     */
    public static function getList($uid = '')
    {
        return implode(',', self::getByUser($uid));
    }


    /**
     * Get all the groups having this feature.
     *
     * @return  array   Array of group IDs
     */
    public function getGroups()
    {
        global $_TABLES;

        static $groups = array();
        if (!isset($groups[$this->ft_id])) {
            $groups[$this->ft_id] = array();

            if ($this->ft_id > 0) {
                $sql = "SELECT grp.grp_id, grp.grp_name
                    FROM {$_TABLES['access']} ac
                    LEFT JOIN {$_TABLES['groups']} grp
                        ON ac.acc_grp_id = grp.grp_id
                    WHERE acc_ft_id = {$this->ft_id}
                    ORDER BY grp_id";
                $res = DB_query($sql, 1);
                if ($res) {
                    while ($A = DB_fetchArray($res, false)) {
                        $groups[$this->ft_id][] = (int)$A['grp_id'];
                    }
                }
            } else {
                $groups[$this->ft_id][] = 0;
            }
        }
        return $groups[$this->ft_id];
    }


    /**
     * Get an array of group IDs with access to a feature.
     *
     * @param   string  $feature    Feature name, e.g. "story.edit"
     * @return  array       Array of group IDs
     */
    public static function Groups($feature)
    {
        global $_TABLES;

        static $groups = array();
        if (!isset($groups[$feature])) {
            $groups[$feature] = array();

            $ft_id = self::getByName($feature)->ft_id;
            if ($ft_id > 0) {
                $sql = "SELECT * FROM {$_TABLES['access']}
                        WHERE acc_ft_id = $ft_id
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


    /**
     * Creates the edit form.
     *
     * @return  string      HTML for edit form
     */
    public function Edit()
    {
        global $_CONF, $LANG_ADMIN, $LANG_ACCESS, $_TABLES;

        $allgroups = \Group::getAll();
        $inc_groups = self::Groups($this->ft_name);

        $included_opts = '';
        $excluded_opts = '';
        foreach ($allgroups as $grp_name=>$grp_id) {
            if (in_array($grp_id, $inc_groups)) {
                $included_opts .= '<option value="' . $grp_id . '">' . $grp_name . '</option>' . LB;
            } else {
                $excluded_opts .= '<option value="' . $grp_id . '">' . $grp_name . '</option>' . LB;
            }
        }

        $T = new \Template($_CONF['path_layout'] . '/admin/feature');
        $T->set_file('form', 'featureeditor.thtml');
        $T->set_var(array(
            'ft_id'         => $this->ft_id,
            'ft_name'       => $this->ft_name,
            'ft_descr'      => $this->ft_descr,
            'ft_gl_core'    => $this->ft_gl_core,
            'lang_ft_id'    => $LANG_ACCESS['feature_id'],
            'lang_ft_name'  => $LANG_ACCESS['feature_name'],
            'lang_ft_descr' => $LANG_ACCESS['description'],
            'lang_fg_gl_core' => $LANG_ACCESS['coregroup'],
            'lang_save'     => $LANG_ADMIN['save'],
            'lang_cancel'   => $LANG_ADMIN['cancel'],
            'gltoken_name'  => CSRF_TOKEN,
            'gltoken'       => SEC_createToken(),
            'included_groups' => $included_opts,
            'excluded_groups' => $excluded_opts,
            'lang_available' => $LANG_ACCESS['avail_groups'],
            'lang_included' => $LANG_ACCESS['incl_groups'],
            'LANG_add'      => $LANG_ACCESS['add'],
            'LANG_remove'   => $LANG_ACCESS['remove'],
        ) );

        // Construct the checkboxes of groups
        /*$groups = $this->getGroups();
        $sql = "SELECT grp_id, grp_name, grp_descr FROM {$_TABLES['groups']}";
        $res = DB_query($sql);
        $T->set_block('form', 'grpItems', 'grpChk');
        while ($A = DB_fetchArray($res, false)) {
            $T->set_var(array(
                'grp_id'    => $A['grp_id'],
                'grp_name'  => $A['grp_name'],
                'grp_descr' => $A['grp_descr'],
                'chk'       => in_array($A['grp_id'], $groups) ? 'checked="checked"' : '',
            ) );
            $T->parse('grpChk', 'grpItems', true);
        }*/

        $T->parse('output', 'form');
        $retval = COM_startBlock(
            $LANG_ADMIN['feature_editor'],
            '',
            COM_getBlockTemplate('_admin_block', 'header')
        );
        $retval .= self::adminMenu('edit');
        $retval .= $T->finish($T->get_var('output'));
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
        return $retval;
    }


    /**
     * Save the feature information to the database.
     *
     * @param   array   $A      Optional array of values from $_POST
     * @return  boolean         True if no errors, False otherwise
     */
    public function Save($A =NULL)
    {
        global $_TABLES, $_SHOP_CONF;

        if (is_array($A)) {
            $this->setVars($A, false);
        }

        // Insert or update the record, as appropriate.
        if ($this->ft_id == 0) {
            $isNew = true;
            $sql1 = "INSERT INTO {$_TABLES['features']}";
            $sql3 = '';
        } else {
            $isNew = false;
            $sql1 = "UPDATE {$_TABLES['features']}";
            $sql3 = " WHERE ft_id = {$this->ft_id}";
        }
        $sql2 = " SET ft_name = '" . DB_escapeString($this->ft_name) . "',
            ft_descr = '" . DB_escapeString($this->ft_descr) . "',
            ft_gl_core  = {$this->ft_gl_core}";
        $sql = $sql1 . $sql2 . $sql3;
        //echo $sql;die;
        DB_query($sql);
        $err = DB_error();
        if ($err == '') {
            if ($isNew) {
                $this->ft_id = DB_insertID();
            }
            $status = true;
        } else {
            $status = false;
        }

        // Now save the group assignments
        if ($status) {
            if (!$isNew) {
                // If updating, delete all access records.
                // Simplest method to remove any unchecked groups.
                DB_delete($_TABLES['access'], 'acc_ft_id', $this->ft_id);
            }
            if (isset($A['groupmembers'])) {
                $grp_members = explode('|', $A['groupmembers']);
                foreach (explode('|', $A['groupmembers']) as $grp_id) {
                    $vals[] = "({$this->ft_id}, {$grp_id})";
                }
                $vals = implode(', ', $vals);
                $sql = "INSERT INTO {$_TABLES['access']} VALUES $vals";
                DB_query($sql);
            }
        }
        return $status;
    }


    /**
     * Displays the admin list of features.
     *
     * @return  string      List of features
     */
    public static function adminList($coreonly=0)
    {
        global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACCESS, $LANG28;

        USES_lib_admin();
        $retval = '';

        $sql = "SELECT * FROM {$_TABLES['features']} ";
        $header_arr = array(
            array(
                'text'  => 'ID',
                'field' => 'ft_id',
                'sort'  => true,
            ),
            array(
                'text'  => $LANG_ADMIN['edit'],
                'field' => 'edit',
                'sort'  => false,
                'align' => 'center',
            ),
            array(
                'text'  => $LANG_ADMIN['name'],
                'field' => 'ft_name',
                'sort'  => true,
            ),
            array(
                'text'  => $LANG_ACCESS['description'],
                'field' => 'ft_descr',
            ),
            array(
                'text'  => $LANG_ACCESS['coregroup'],
                'field' => 'ft_gl_core',
            ),
        );

        $defsort_arr = array(
            'field' => 'ft_name',
            'direction' => 'ASC',
        );

        if ($coreonly) {
            $def_filter = ' WHERE ft_gl_core = 1';
            $corechk = 'checked="checked"';
        } else {
            $def_filter = ' WHERE 1=1';
            $corechk = '';
        }

        $filter = '<input type="checkbox" name="core" ' . $corechk .
            ' onclick="this.form.submit();">&nbsp;' .
            $LANG_ADMIN['core_only'] . '&nbsp;&nbsp;';

        $query_arr = array(
            'table' => 'features',
            'sql' => $sql,
            'query_fields' => array('ft_name', 'ft_descr'),
            'default_filter' => $def_filter,
        );
        $text_arr = array(
            'has_extras' => true,
            'form_url' => $_CONF['site_admin_url'] . '/feature.php',
        );

        $options = array();
        $retval .= COM_startBlock(
            $LANG_ADMIN['feature_admin'],
            '',
            COM_getBlockTemplate('_admin_block', 'header')
        );
        $retval .= self::adminMenu('list');
        $retval .= ADMIN_list(
            'gl_ft_adminlist',
            array(__CLASS__,  'getAdminField'),
            $header_arr, $text_arr, $query_arr, $defsort_arr,
            $filter, '', $options, ''
        );
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
        return $retval;
    }


    /**
     * Get an individual field for the feature list.
     *
     * @param  string  $fieldname  Name of field (from the array, not the db)
     * @param  mixed   $fieldvalue Value of the field
     * @param  array   $A          Array of all fields from the database
     * @param  array   $icon_arr   System icon array (not used)
     * @return string              HTML for field display in the table
     */
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr)
    {
        global $_CONF, $LANG_ADMIN;

        static $grp_names = array();
        $retval = '';

        switch($fieldname) {
        case 'edit':
            $retval .= COM_createLink(
                $icon_arr['edit'],
                $_CONF['site_admin_url'] . "/feature.php?edit={$A['ft_id']}"
            );
            break;
        case 'ft_gl_core':
            if ($fieldvalue) {
                $retval .= $icon_arr['check'];
            }
            break;
        default:
            $retval = htmlspecialchars($fieldvalue, ENT_QUOTES, COM_getEncodingt());
            break;
        }
        return $retval;
    }


    /**
     * Create a menu for the admin interface.
     *
     * @param   string  $view   View to mark as active
     * @return  string      HTML for admin menu
     */
    public static function adminMenu($view='list')
    {
        global $_CONF, $LANG_ADMIN, $LANG_ACCESS;

        USES_lib_admin();

        $menu_arr = array(
            array(
                'url' => $_CONF['site_admin_url'] . '/feature.php',
                'text' => $LANG_ADMIN['feature_list'],
                'active'=> $view == 'list',
            ),
            /*array(
                'url' => $_CONF['site_admin_url'] . '/feature.php?edit=x',
                'text' => $LANG_ADMIN['create_new'],
            ),*/
            array(
                'url' => $_CONF['site_admin_url'].'/index.php',
                'text' => $LANG_ADMIN['admin_home'],
            ),
        );
        $retval = ADMIN_createMenu(
            $menu_arr,
            '',
            $_CONF['layout_url'] . '/images/icons/group.png',
        );
        return $retval;
    }

}
