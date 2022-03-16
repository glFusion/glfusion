<?php
/**
 * Class to handle user badges.
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
 * User Badge class.
 * @package glfusion
 */
class Badge
{
    /** Default foreground and background colors for CSS-type badges
     * Matches the default uk-badge colors.
     */
    private const DEF_BG = '#009dd8';
    private const DEF_FG = '#ffffff';

    /** Default badge group when getting only a single badge.
     */
    private const DEF_GRP = '1_site';

    /** Cache tags affecting user group membership.
     * @var array */
    private static $cache_tags = array('badges', 'user_group', 'groups');

    private $bid = 0;
    private $enabled = 1;
    private $inherit = 1;
    private $order = 999;  // set to last in list
    private $badge_grp = '';
    private $gl_grp = 13;
    private $type = 'img';
    private $bgcolor = self::DEF_BG;
    private $fgcolor = self::DEF_FG;
    private $dscp = '';
    private $image = '';
    private $html = NULL;


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
                "SELECT * FROM {$_TABLES['badges']}
                WHERE bid = ?",
                array($id),
                array(Database::INTEGER)
            );
            $A = $stmt->fetch(Database::ASSOCIATIVE);
        } catch (\Throwable $e) {
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
        $this->setBid($A['bid'])
             ->setEnabled(isset($A['enabled']) ? $A['enabled'] : 0)
             ->setInherit(isset($A['inherit']) ? $A['inherit'] : 0)
             ->setOrder($A['sortorder'])
             ->setBadgeGroup($A['badge_grp'])
             ->setSiteGroup((int)$A['gl_grp'])
             ->setType($A['type'])
             ->setDscp($A['dscp']);

        $this->type = $A['type'];
        if ($from_db) {
            if ($this->type == 'css') {
                $data = @unserialize($A['data']);
                if (is_array($data)) {
                    $this->fgcolor = isset($data['fgcolor']) ? $data['fgcolor'] : self::DEF_FG;
                    $this->bgcolor = isset($data['bgcolor']) ? $data['bgcolor'] : self::DEF_BG;
                }
            } else {
                $this->image = $A['data'];
            }
        } else {
            $this->fgcolor = isset($A['fgcolor']) ? $A['fgcolor'] : self::DEF_FG;
            $this->bgcolor = isset($A['bgcolor']) ? $A['bgcolor'] : self::DEF_BG;
            $this->image = $A['image'];
        }

        return $this;
    }


    /**
     * Set the badge record ID.
     *
     * @param   integer $id     Badge ID
     * @return  object  $this
     */
    public function setBid(int $id) : self
    {
        $this->bid = (int)$id;
        return $this;
    }


    public function getBid() : int
    {
        return (int)$this->bid;
    }


    /**
     * Set the badge group name.
     *
     * @param   string  $grp    Group name
     * @return  object  $this
     */
    public function setGroup(string $grp) : self
    {
        $this->badge_grp = $grp;
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


    public function getOrder() : int
    {
        return (int)$this->order;
    }


    /**
     * Set the inherit flag value.
     * Specifies if all inherited group memberships are considered, or
     * false if only directly-assigned groups are used.
     *
     * @param   integer $flag   Flag value
     * @return  object  $this
     */
    public function setInherit(int $flag) : self
    {
        $this->inherit = $flag ? 1 : 0;
        return $this;
    }


    /**
     * Get the inherited-group flag value.
     *
     * @return  integer     Value of inherited flat
     */
    public function inheritGroup() : int
    {
        return $this->inherit ? 1 : 0;
    }


    /**
     * Set the badge type, either `img` or `css`.
     *
     * @param   string  $type   Badge type
     * @return  object  $this
     */
    public function setType(string $type) : self
    {
        $this->type = $type;
        return $this;
    }


    /**
     * Set the badge description.
     *
     * @param   string  $dscp   Description or title
     * @return  object  $this
     */
    public function setDscp(string $dscp) : self
    {
        $this->dscp = $dscp;
        return $this;
    }


    /**
     * Set the badge group name for grouping badges.
     *
     * @param   string  $name   Group name
     * @return  object  $this
     */
    public function setBadgeGroup(string $name) : self
    {
        $this->badge_grp = $name;
        return $this;
    }


    /**
     * Get the badge group name.
     *
     * @return  string      Group name for grouping badges
     */
    public function getBadgeGroup() : string
    {
        return $this->badge_grp;
    }


    /**
     * Set the glFusion group ID associated with this badge.
     *
     * @param   integer $grp_id     glFusion group ID
     * @return  object  $this
     */
    public function setSiteGroup(int $grp_id) : self
    {
        $this->gl_grp = (int)$grp_id;
        return $this;
    }


    /**
     * Get the glFusion group ID associated with this badge.
     *
     * @return  integer     glFusion group ID
     */
    public function getSiteGroup() : int
    {
        return (int)$this->gl_grp;
    }


    /**
     * Gets an array of all the badge groups.
     *
     * @param  boolean $enabled    True to get only enabled badges
     * @return array           Array of grp_name=>grp_id
     */
    public static function getAll(bool $enabled = true) : array
    {
        global $_TABLES;

        static $cache = array();
        $enabled = $enabled ? 1 : 0;    // change to int

        if (!array_key_exists($enabled, $cache)) {
            $cache[$enabled] = array();
            $db = Database::getInstance();
            $sql = "SELECT b.*, g.grp_name
                    FROM {$_TABLES['badges']} b
                    LEFT JOIN {$_TABLES['groups']} g
                        ON g.grp_id = b.gl_grp ";
            if ($enabled) {
                $sql .= "WHERE enabled = 1";
            }
            $sql .= " ORDER BY b.badge_grp ASC, b.sortorder ASC";
            try {
                $stmt = $db->conn->executeQuery($sql);
                $data = $stmt->fetchAll(Database::ASSOCIATIVE);
            } catch (\Throwable $e) {
                return $cache[$enabled];
            }
            $cbrk = NULL;
            foreach ($data as $A) {
                if ($cbrk != $A['badge_grp']) {
                    $cache[$enabled][$A['badge_grp']] = array();
                    $cbrk = $A['badge_grp'];
                }
                $cache[$enabled][$A['badge_grp']][$A['gl_grp']] = new self($A);
            }
        }
        return $cache[$enabled];
    }


    /**
     * Gets an array of all the user badges.
     *
     * @param  integer  $uid    ID of user
     * @return array       Array of all user badge objects
     */
    public static function getUserBadges(int $uid) : array
    {
        global $_CONF;

        static $retval = array();
        $cache_key = 'badges_' . $uid;
        $uid = (int)$uid;
        if (array_key_exists($uid, $retval)) {
            return $retval[$uid];
        } elseif (Cache::getInstance()->has($cache_key)) {
            return Cache::getInstance()->get($cache_key);
        }

        $badge_groups = self::getAll();
        $retval[$uid] = array();
        $all_grps = Group::getAll($uid);
        $assigned_grps = Group::getAssigned($uid);
        foreach ($badge_groups as $id=>$badge_group) {
            foreach ($badge_group as $grp_id=>$badge) {
                $grps = $badge->inheritGroup() ? $all_grps : $assigned_grps;
                if (in_array($grp_id, $grps)) {
                    $retval[$uid][] = $badge;
                    if ($badge->getBadgeGroup() != '') {
                        break;
                    }
                }
            }
        }
        Cache::getInstance()->set($cache_key, $retval, self::$cache_tags);
        return $retval[$uid];
    }


    /**
     * Get a single user badge from a given group.
     *
     * @param   integer $uid    User ID
     * @param   string  $b_grp  Badge group, default if not specified
     * @return  string      HTML for badge, empty string if not found
     */
    public static function getSingle(int $uid, ?string $grp=NULL) : self
    {
        if ($grp === NULL) {
            $grp = self::DEF_GRP;
        }
        $retval = '';
        $Badges = self::getUserBadges($uid);
        foreach ($Badges as $Badge) {
            if ($Badge->getBadgeGroup() == $grp) {
                $retval = $Badge;
                break;
            }
        }
        return $retval;
    }


    /**
     * Get the final HTML to display the badge.
     * Returns the HTML and also sets $this->html as a cache.
     *
     * @return  string  HTML for badge
     */
    public function getHTML() : string
    {
        global $_CONF;

        // If html is defined at all, return it.
        if ($this->html !== NULL) {
            return $this->html;
        }

        // Description for title and display text, fallback to GL group name
        $dscp = $this->dscp == '' ? $this->badge_grp : $this->dscp;
        $this->html = '';
        switch ($this->type) {
        case 'css':
            //$tpl_filename = 'badge_css.thtml';
            $url = '';
            break;
        case 'img':
        default:
            //$tpl_filename = 'badge_img.thtml';
            $url = $this->getImageUrl();
            break;
        }
        $T = new \Template($_CONF['path_layout']);
        $T->set_file('badge', 'user_badge.thtml');
        $T->set_var(array(
            'title' => $dscp,
            'alt'   => $dscp,
            'dscp'  => $dscp,
            'badge_url' => $url,
            'fgcolor'   => $this->fgcolor,
            'bgcolor'   => $this->bgcolor,
        ) );
        $T->parse('output','badge');
        $this->html = $T->finish($T->get_var('output'));
        return $this->html;
    }


    /**
     * Get the image URL for a badge.
     * Looks first in the current theme, then in the plugin's html directory.
     *
     * @return  string          URL to image
     */
    public function getImageUrl() : string
    {
        global $_CONF;

        $retval = '';
        if ($this->type == 'img') {
            // Using an array is a holdover from the Forum badges,
            // but makes it easy to add layout paths later.
            $paths = array(
                $_CONF['path_html'] . 'data/badges/' =>
                    $_CONF['site_url'] . '/data/badges/',
            );

            foreach ($paths as $path=>$url) {
                if (file_exists($path . $this->image)) {
                    $url .= $this->image;
                    $retval = $url;
                    break;
                }
            }
        }
        return $retval;
    }


    /**
     * Reset all the order fields to increment by 10.
     */
    public static function reOrder(string $badge_group) : void
    {
        global $_TABLES;

        $badge_groups = self::getAll(false);
        if (!array_key_exists($badge_group, $badge_groups)) {
            return;
        }

        $stepNumber = 10;
        $db = Database::getInstance();
        $order = 10;
        foreach ($badge_groups[$badge_group] as $badge) {
            if ($badge->getOrder() != $order) {
                $sql = "UPDATE {$_TABLES['badges']}
                        SET sortorder = ?
                        WHERE bid = ?";
                try {
                    $stmt = $db->conn->executeUpdate(
                        $sql,
                        array($order, $badge->getBid()),
                        array(Database::INTEGER, Database::INTEGER)
                    );
                } catch (\Throwable $e) {
                    Log::write(
                        'system',
                        Log::ERROR,
                        'Badge::reOrder() SQL error: '.$e->getMessage()
                    );
                }
            }
            $order += $stepNumber;
        }
    }


    /**
     * Move a badge up or down the list.
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
        $sql = "UPDATE {$_TABLES['badges']}
                SET sortorder = sortorder $oper 11
                WHERE bid = ?";
        try {
            $stmt = $db->conn->executeUpdate(
                $sql,
                array($id),
                array(Database::INTEGER)
            );
            self::reOrder($Badge->getBadgeGroup());
        } catch (\Throwable $e) {
            Log::write('system',Log::ERROR,'Badge::moveRow() SQL error: '.$e->getMessage());
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
        $T->set_file('editform', 'editor.thtml');
        $T->set_var(array(
            'bid'     => $this->bid,
            'grp'    => $this->badge_grp,
            'grp_select' => COM_optionList(
                $_TABLES['groups'], 'grp_id,grp_name', $this->gl_grp
            ),
            'image_sel' => $this->_getImageSelection($this->image),
            'order'  => $this->order,
            'grp_sel' => COM_optionList(
                    $_TABLES['badges'],
                    'DISTINCT badge_grp,badge_grp',
                    $this->badge_grp,
                    0,
                    "badge_grp <> ''"
                ),
            'ena_chk'   => $this->enabled ? 'checked="checked"' : '',
            'inherit_chk' => $this->inherit ? 'checked="checked"' : '',
            'chk_' . $this->type => 'checked="checked"',
            'dscp' => htmlspecialchars($this->dscp),
            'type' => $this->type,
            'fgcolor' => $this->fgcolor,
            'bgcolor' => $this->bgcolor,
        ) );
        $T->parse('output','editform');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Create an option list of the available badge images.
     *
     * @param  string  $selected   Selected image name
     * @return string      Option elements for the images
     */
    private function _getImageSelection(string $selected='') : string
    {
        global $_CONF;

        $retval = '';
        $path = $_CONF['path_html'] . 'data/badges';
        if (!is_dir($path) || !is_readable($path)) {
            return '';
        }
        $dir = opendir($path);
        while(false !== ($file = readdir($dir))) {
            if ($file == '.' || $file == '..' || $file == 'index.html') continue;
            $sel = $file == $selected ? 'selected="selected"' : '';
            $retval .= '<option value="' . $file . '" ' . $sel . '>' .
                    $file . '</option>' . LB;
        }
        closedir($dir);
        return $retval;
    }


    /**
     * Save a badge from the edit form.
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

        // Handle the file upload, if any. The _handleUpload() function
        // should return the filename. If it is empty then an error occurred.
        if ($this->type == 'css') {
            $data = @serialize(array(
                'fgcolor' => $this->fgcolor,
                'bgcolor' => $this->bgcolor,
            ) );
        } else {
            if (
                isset($_FILES['badge_imgfile']['name']) &&
                !empty($_FILES['badge_imgfile']['name'])
            ) {
                $errors = $this->_handleUpload($_FILES['badge_imgfile']['name']);
                if (!empty($errors)) {
                    return $LANG_ADMIN['save_error'] . ':<br />' . $errors;
                } else {
                    $data = $_FILES['badge_imgfile']['name'];
                }
            } else {
                $data = $this->image;
            }
        }

        $db = Database::getInstance();
        if ($this->bid > 0) {
            $sql1 = "UPDATE {$_TABLES['badges']} SET ";
            $sql3 = ' WHERE bid = :bid';
        } else {
            $sql1 = "INSERT INTO {$_TABLES['badges']} SET ";
            $sql3 = '';
        }

        $sql2 = "badge_grp = :badge_grp,
            sortorder = :sortorder,
            enabled = :enabled,
            inherit = :inherit,
            gl_grp = :gl_grp,
            data = :data,
            type = :type,
            dscp = :dscp";
        $sql = $sql1 . $sql2 . $sql3;
        $params = array(
            'bid' => $this->bid,
            'badge_grp' => $this->badge_grp,
            'sortorder' => $this->order,
            'enabled' => $this->enabled,
            'inherit' => $this->inherit,
            'gl_grp' => $this->gl_grp,
            'data' => $data,
            'type' => $this->type,
            'dscp' => $this->dscp,
        );
        $types = array(
            Database::INTEGER,
            Database::STRING,
            Database::INTEGER,
            Database::INTEGER,
            Database::INTEGER,
            Database::INTEGER,
            Database::STRING,
            Database::STRING,
            Database::STRING,
        );

        try {
            $stmt = $db->conn->executeUpdate($sql, $params, $types);
            self::reOrder($this->badge_grp);
            Cache::getInstance()->deleteItemsByTags(self::$cache_tags);
            return '';
        } catch (\Throwable $e) {
            Log::write('system', Log::ERROR, $e->getMessage());
            return $LANG_ADMIN['save_error'];
        }
    }


    /**
     * Handle the image file upload.
     *
     * @return string  Error messages, or empty string on success
     */
    private function _handleUpload(string $filename) : string
    {
        global $_CONF;

        $path = $_CONF['path_html'] . 'data/badges';

        $Upload = new \upload();
        $Upload->setContinueOnError(true);
        $Upload->setpath($path);
        $Upload->setAllowedMimeTypes(array(
                'image/pjpeg' => '.jpg,.jpeg',
                'image/jpeg'  => '.jpg,.jpeg',
                'image/png'   => '.png',
                'image/x-png' => '.png',
                'image/gif'   => '.gif',
        ));
        $Upload->setMaxFileSize($_CONF['max_image_size']);
        $Upload->setAutomaticResize(false);
        $Upload->setFieldName('badge_imgfile');
        $Upload->setFileNames($filename);
        $Upload->uploadFiles();
        if ($Upload->areErrors() > 0) {
            return $Upload->printErrors(false);
        } else {
            return '';
        }
    }


    /**
     * Delete a single badge record. Does not delete the image.
     *
     * @param  integer $bid  Badge ID to delete
     */
    public static function Delete(int $bid) : void
    {
        global $_TABLES;
        $db = Database::getInstance();
        try {
            $db->conn->delete(
                $_TABLES['badges'],
                array('bid' => $bid),
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
        case 'inherit':
            try {
                $stmt = $db->conn->executeUpdate(
                    "UPDATE {$_TABLES['badges']}
                    SET $field = ?
                    WHERE bid = ?",
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

}

