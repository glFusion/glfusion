<?php
/**
 * Class to handle glFusion syndication feed definitions.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Syndication;
use \glFusion\Database\Database;


/**
 * Syndication Feed class.
 * @package glfusion
 */
class Feed
{
    /** Record ID.
     * @var integer */
    protected $fid = 0;

    /** Type of feed, e.g. plugin name.
     * @var string */
    protected $type = '';

    /** Feed topic. For plugins may be a category or other limiter.
     * @var string */
    protected $topic = '';

    /** Header Topic ID.
     * @var string */
    protected $header_tid = '';

    /** Feed format spec.
     * @var string */
    protected $format = '';

    /** Feed format version.
     * @var string */
    protected $format_version = '';

    /** Limit the number of items or time span for feed content.
     * @var string */
    protected $limits = '10';

    /** Content Length. 0 = none, 1 = all, anything else limits the content.
     * @var integer */
    protected $content_length = 1;

    /** Feed title.
     * @var string */
    protected $title = '';

    /** Feed description.
     * @var string */
    protected $description = '';

    /** Logo image URL.
     * @var string */
    protected $feedlogo = '';

    /** Feed filename to write in the rss_path.
     * @var string */
    protected $filename = '';

    /** Character Set.
     * @var string */
    protected $charset = 'utf-8';

    /** Language.
     * @var string */
    protected $language = 'en-gb';

    /** Flag to enable or disable a feed.
     * @var integer */
    protected $is_enabled = 1;

    /** Last update date/time.
     * @var object */
    protected $updated = NULL;

    /** Comma-separated lists of item IDs in the last update.
     * Used to determine if the feed needs to be rewritten.
     * @var string */
    protected $update_info = '';


    /**
     * Constructor.
     *
     * @param  mixed   $A  Array of properties or group ID
     */
    public function __construct(array $A=array())
    {
        global $_TABLES;

        if (is_array($A)) {
            $this->setVars($A);
        }
    }


    /**
     * Get a feed object based on the feed type.
     *
     * @param   integer $fid    Feed record ID
     * @return  object      Child object according to feed type
     */
    public static function getById(int $fid) : object
    {
        $sql = "fid = " . (int)$fid;
        $feeds = self::_execQuery($sql);
        // $feeds will be an array with one element, so get it by index.
        if (isset($feeds[$fid])) {
            return $feeds[$fid];
        } else {
            return new self;
        }
    }


    /**
     * Get all the enabled feeds, optionally filtering by type.
     *
     * @param   string  $type   Optional type of feed, aka plugin name
     * @return  array       Array of feed objects
     */
    public static function getEnabled(string $type='') : array
    {
        $type = "is_enabled = 1";
        if (!empty($type)) {
            $type = "AND type = '" . DB_escapeString($type) . "'";
        }
        return self::_execQuery($type);
    }


    /**
     * Get all the feeds, optionally filtering by type.
     *
     * @param   string  $type   Optional type of feed, aka plugin name
     * @return  array       Array of feed objects
     */
    public static function getAll(string $type='') : array
    {
        if (!empty($type)) {
            $type = "type = '" . DB_escapeString($type) . "'";
        }
        return self::_execQuery($type);
    }


    /**
     * Execute the SQL query with the provided filter.
     *
     * @param   string  $where  Optional SQL filter string
     * @return  array       Array of instantiated child objects
     */
    private static function _execQuery(string $where='') : array
    {
        global $_TABLES;

        $db = Database::getInstance();
        $sql = "SELECT * FROM `{$_TABLES['syndication']}`";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        try {
            $stmt = $db->conn->executeQuery($sql);
        } catch(Throwable $e) {
            return $retval;
        }
        $data = $stmt->fetchAll(Database::ASSOCIATIVE);
        if (is_array($data)) {
            foreach ($data as $feed) {
                $format = explode('-', $feed['format']);
                switch ($format[0]) {
                case 'ICS':
                case 'Atom':
                case 'RSS':
                case 'XML':
                    $cls = __NAMESPACE__ . '\\Formats\\' . $format[0];
                    $retval[$feed['fid']] = new $cls($feed);
                    break;
                default:
                    $F = PLG_callFunctionForOnePlugin(
                        'plugin_getfeedprovider_' . $feed['type'],
                        array(1 => $format[0], 2 => $feed)
                    );
                    if ($F) {
                        $retval[$feed['fid']] = new $F($feed);
                    } else {
                        $retval[$feed['fid']] = new Formats\XML($feed);
                    }
                    break;
                }
            }
        }
        return $retval;
    }


    /**
     * Set all property values from DB or form.
     *
     * @param   array   $A          Array of property name=>value
     * @return  object  $this
     */
    public function setVars(array $A) : object
    {
        global $_CONF;

        // All property names, except "updated" which gets a Date object
        $fields = array(
            'fid', 'type', 'topic', 'header_tid', 'limits', //'format', 
            'content_length', 'title', 'description', 'feedlogo', 'filename',
            'charset', 'language', 'is_enabled', 'update_info',
        );
        foreach ($fields as $fld_name) {
            if (isset($A[$fld_name])) {
                $this->$fld_name = $A[$fld_name];
            }
        }
        if (isset($A['format'])) {
            $format = explode('-', $A['format']);
            $this->format = $format[0];
            if (isset($format[1])) {
                $this->format_version = $format[1];
            }
        }
        if (isset($A['updated'])) {
            $this->updated = new \Date($A['updated'], $_CONF['timezone']);
        }
        return $this;
    }


    public function getDescription()
    {
        return $this->description;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getContentLength()
    {
        return $this->content_length;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getLogo()
    {
        return $this->feedlogo;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getFid()
    {
        return $this->fid;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function getLimits()
    {
        return $this->limits;
    }


    /**
     * Get the path of the feed directory or a specific feed file
     *
     * @param   string  $feedfile   (option) feed file name
     * @return  string              path of feed directory or file
     */
    public static function getFeedPath( $feedfile = '' ) : string
    {
        global $_CONF;

        $feed = $_CONF['path_rss'] . $feedfile;
        return $feed;
    }
    
    /**
     * Get the URL of the feed directory or a specific feed file
     *
     * @param    string  $feedfile   (option) feed file name
     * @return   string              URL of feed directory or file
     */
    public static function getFeedUrl( string $feedfile = '' ) : string
    {
        global $_CONF;

        $feedpath = self::getFeedPath();
        $url = substr_replace($feedpath, $_CONF['site_url'], 0, strlen ($_CONF['path_html']) - 1);
        $url .= $feedfile;
        return $url;
    }


    /**
     * Wrapper function for each feed type's _generate() function.
     * Handles updating the syndication table with new information.
     *
     * @return  void
     */
    public function Generate() : void
    {
        global $_CONF, $_TABLES, $_SYND_DEBUG;

        // Actually generate the feed.
        $this->_generate();

        // Now perform housekeeping.
        if (empty($this->update_info)) {
            $data = 'NULL';
        } else {
            $data = DB_escapeString($this->update_info);
        }
        if ($_SYND_DEBUG) {
            Log::write('system',Log::DEBUG,"update_info for feed {$this->fid} is {$this->update_info}");
        }

        $db = Database::getInstance();
        $db->conn->executeUpdate(
            "UPDATE {$_TABLES['syndication']} SET updated = ?, update_info = ? WHERE fid = ?",
            array(
                $_CONF['_now']->toMySQL(true),
                $data,
                $this->fid
            ),
            array(Database::STRING,Database::STRING,Database::STRING)
        );
    }


    /**
     * Accessor to allow plugin feed providers to send the list of updated IDs.
     *
     * @param   string  $data_str   Comma-separated list of item IDs
     * @return  object  $this
     */
    public function setUpdateData(?string $data_str) : object
    {
        $this->update_info= $data_str;
        return $this;
    }


    /**
     * Get all the available feed formats.
     *
     * @return  array   Array of feed (name, version) elements
     */
    public static function findFormats(string $type = '') : array
    {
        global $_CONF;

        if (!empty($type)) {
            $func = 'plugin_getfeedformats_' . $type;
            $formats= PLG_callFunctionForOnePlugin($func);
            if (!empty($formats)) {
                return $formats;
            }
        }

        $formats = array();
        $files = glob(__DIR__ . '/Formats/*.php');
        if (is_array($files)) {
            foreach ($files as $fullpath) {
                $parts = pathinfo($fullpath);
                $class = $parts['filename'];
                $classfile = __NAMESPACE__ . '\\Formats\\' . $class;
                foreach ($classfile::$versions as $version) {
                    $formats[] = array(
                        'name' => $class,
                        'version' => $version,
                    );
                }
            }
            asort($formats);
        }
        return $formats;
    }


    /**
     * Write the feed data to the specified file.
     *
     * @param   string  $filename   Filename to write, path will be added
     * @param   string  $data       Data to be written
     * @return  bool        True on success, False on error
     */
    protected function _writeFile(string $fileName, string $data) : bool
    {
        $filepath = self::getFeedPath($fileName);
        if (($fp = @fopen($filepath, 'w')) !== false) {
            fputs($fp, $data);
            fclose($fp);
            return true;
        } else {
            Log:;write('system', Log::ERROR, "Error: Unable to open $filepath for writing");
            return false;
        }
    }


    /**
     * Creates the edit form.
     *
     * @return  string      HTML for edit form
     */
    public function Edit()
    {
        global $_CONF, $LANG_ADMIN, $LANG_ACCESS, $_TABLES;

        $allgroups = \Group::getAllAvailable();
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
            'fid'         => $this->fid,
            'ft_name'       => $this->ft_name,
            'ft_descr'      => $this->ft_descr,
            'ft_gl_core'    => $this->ft_gl_core,
            'lang_fid'    => $LANG_ACCESS['feature_id'],
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
            'lang_included' => $LANG_ADMIN['assigned_groups'],
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

        $db = Database::getInstance();
        $dataArray = array();
        $typeArray = array();

        // Insert or update the record, as appropriate.
        if ($this->fid == 0) {
            $isNew = true;
            $sql1 = "INSERT INTO `{$_TABLES['features']}`";
            $sql3 = '';
        } else {
            $isNew = false;
            $sql1 = "UPDATE `{$_TABLES['features']}`";
            $sql3 = " WHERE fid = ?";
            $dataArray[] = $this->fid;
            $typeArray[] = DATABASE::INTEGER;
        }
        $sql2 = " SET ft_name = ?,
            ft_descr = ?,
            ft_gl_core  = ?";
        $dataArray[] = $this->ft_name;
        $typeArray[] = DATABASE::STRING;

        $dataArray[] = $this->ft_descr;
        $typeArray[] = DATABASE::STRING;

        $dataArray[] = $this->ft_gl_core;
        $typeArray[] = DATABASE::INTEGER;

        $sql = $sql1 . $sql2 . $sql3;
        //echo $sql;die;

        $status = true;
        try {
            $stmt = $db->conn->executeQuery($sql,$dataArray,$typeArray);
        } catch(Throwable $e) {
            $status = false;
        }
        if ($isNew) {
            $this->fid = $db->conn->lastInsertId();
        }

        // Now save the group assignments
        if ($status) {
            if (!$isNew) {
                // If updating, delete all access records.
                // Simplest method to remove any unchecked groups.

                try {
                    $db->conn->delete($_TABLES['access'],array('acc_fid' => $this->fid),array(DATABASE::INTEGER));
                } catch(Throwable $e) {
                    throw($e);
                }
            }
            if (isset($A['groupmembers'])) {
                $grp_members = explode('|', $A['groupmembers']);
                foreach (explode('|', $A['groupmembers']) as $grp_id) {
                    $vals[] = "(" . intval($this->fid).",".intval($grp_id).")";
                }
                $vals = implode(', ', $vals);
                $sql = "INSERT INTO `{$_TABLES['access']}` VALUES $vals";
                try {
                    $db->conn->query($sql);
                } catch (Throwable $e) {
                    throw($e);
                }
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

        $sql = "SELECT * FROM `{$_TABLES['features']}` ";
        $header_arr = array(
            array(
                'text'  => 'ID',
                'field' => 'fid',
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
            $retval = FieldList::edit(array(
                'url' => $_CONF['site_admin_url'] . "/feature.php?edit={$A['fid']}",
                'attr' => array(
                    'title' => $LANG_ADMIN['edit']
                )
            ) );
            break;
        case 'ft_gl_core':
            if ($fieldvalue) {
                $retval = FieldList::checkmark(array(
                    'active' => true,
                ) );
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
            array(
                'url' => $_CONF['site_admin_url'] . '/group.php',
                'text' => $LANG_ADMIN['admin_groups'],
            ),
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
