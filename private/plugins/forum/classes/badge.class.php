<?php
/**
*   Class to handle Forum badges
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2017 Lee Garner <lee@leegarner.com>
*   @package    glfusion
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
namespace Forum;

class Badge
{
    /**
    *   Properties of the instantiated object
    *   @var array() */
    private $properties = array();

    /**
    *   Cache of badge objects
    *   @var array() */
    //private static $cache = array();


    /**
    *   Constructor.
    *   Sets the field values from the supplied array, or reads the record
    *   if $A is a badge ID.
    *
    *   @param  mixed   $A  Array of properties or group ID
    */
    public function __construct($A='')
    {
        if (is_array($A)) {
            $this->setVars($A, true);
        } else {
            $A = (int)$A;
            $this->fb_id = $A;
            if ($this->fb_id > 0) {
                $this->Read($this->fb_id);
            } else {
                // Set reasonable defaults
                $this->fb_id = 0;
                $this->fb_enabled = 1;  // enable new badges
                $this->fb_order = 999;  // set to last in list
                $this->fb_grp = '';
                $this->fb_gl_grp = '';
                $this->fb_image = '';
            }
        }
    }


    /**
    *   Read a single badge record into an instantiated object.
    *
    *   @param  integer $fb_id  Badge record ID
    *   @return boolean     True on success, False on error or not found
    */
    public function Read($fb_id)
    {
        global $_TABLES;

        $sql = "SELECT * FROM {$_TABLES['ff_badges']}
                WHERE fb_id = " . (int)$fb_id;
        $res = DB_query($sql);
        if ($res && DB_numRows($res) == 1) {
            $A = DB_fetchArray($res, false);
            $this->setVars($A);
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
        case 'fb_id':
        case 'fb_order':
            $this->properties[$key] = (int)$value;
            break;
        case 'fb_enabled':
            $this->properties[$key] = $value == 1 ? 1 : 0;
            break;
        case 'fb_gl_grp':
        case 'fb_image':
        case 'fb_grp':
        case 'url':
        case 'html':
            $this->properties[$key] = trim($value);
            break;
        case 'grp_name':
            $this->properties[$key] = ucfirst($value);
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
        if (!$from_db) {
            if (isset($A['fb_grp_txt']) && !empty($A['fb_grp_txt'])) {
                // form may override gl_grp selection
                $this->fb_grp = $A['fb_grp_txt'];
            }
            if (!isset($A['fb_enabled'])) {
                // checkboxes are unset, force a value
                $this->fb_enabled = 0;
            }
        }
        return true;
    }


    /**
    *   Gets an array of all the badge groups.
    *
    *   @param  boolean $enabled    True to get only enabled badges
    *   @return array           Array of grp_name=>grp_id
    */
    public static function getAll($enabled = true)
    {
        global $_TABLES;

        static $cache = array();
        $enabled = $enabled ? 1 : 0;    // change to int

        if (!array_key_exists($enabled, $cache)) {
            $cache[$enabled] = array();
            $sql = "SELECT b.*, g.grp_name
                    FROM {$_TABLES['ff_badges']} b
                    LEFT JOIN {$_TABLES['groups']} g
                        ON g.grp_id = b.fb_gl_grp ";
            if ($enabled) {
                $sql .= "WHERE fb_enabled = 1";
            }
            $sql .= " ORDER BY b.fb_grp ASC, b.fb_order ASC";
            $res = DB_query($sql);
            if ($res) {
                $cbrk = NULL;
                while ($A = DB_fetchArray($res, false)) {
                    if ($cbrk != $A['fb_grp']) {
                        $cache[$enabled][$A['fb_grp']] = array();
                        $cbrk = $A['fb_grp'];
                    }
                    $cache[$enabled][$A['fb_grp']][$A['fb_gl_grp']] = new self($A);
                }
            }
        }
        return $cache[$enabled];
    }


    /**
    *   Gets an array of all the user Forum badges
    *
    *   @param  array   $grps   Array of all groups the user is in
    *   @return array       Array of all user badge images
    */
    public static function getUserBadges($uid)
    {
        global $_CONF;

        static $retval = array();

        $uid = (int)$uid;
        if (array_key_exists($uid, $retval)) {
            return $retval[$uid];
        }

        $badge_groups = self::getAll();
        $retval[$uid] = array();
        $grps = \Group::getAll($uid);
        foreach ($badge_groups as $badge_group) {
            foreach ($badge_group as $badge) {
                //if (array_key_exists($badge->fb_gl_grp, $grps)) {
                if (in_array($badge->fb_gl_grp, $grps)) {
                    $badge->url = self::getImageUrl($badge->fb_image);
                    if ($badge->url != '') {
                        $attrs = array(
                                'data-uk-tooltip' => "{pos:'right'}",
                                'title' => $badge->grp_name,
                        );
                        $badge->html = COM_createImage($url, $badge->grp_name, $attrs);
                        $retval[$uid][] = $badge;
                    } else {
                        $badge->html = '';
                    }
                    if ($badge->fb_grp != '') break;
                }
            }
        }
        return $retval[$uid];
    }


    /**
    *   Get the image URL for a badge.
    *   Looks first in the current theme, then in the plugin's html directory.
    *
    *   @param  string  $img    Image filename
    *   @return string          URL to image
    */
    public static function getImageUrl($img)
    {
        global $_CONF;

        $retval = '';
        $paths = array(
            $_CONF['path_layout'] . 'plugins/forum/images/badges/' =>
                    $_CONF['layout_url'] . '/plugins/forum/images/badges/',
            $_CONF['path_html'] . 'images/forum/badges/' =>
                    $_CONF['site_url'] . '/images/forum/badges/',
        );

        foreach ($paths as $path=>$url) {
            if (file_exists($path . $img)) {
                $url .= $img;
                $retval = $url;
                break;
            }
        }
        return $retval;
    }


    /**
    *   Reset all the order fields to increment by 10
    */
    public static function reOrder()
    {
        global $_TABLES;

        $badge_groups = self::getAll();
        $stepNumber = 10;
        foreach ($badge_groups as $grp) {
            $order = 10;
            foreach ($grp as $badge) {
                if ($badge->fb_order != $order) {
                    $sql = "UPDATE {$_TABLES['ff_badges']}
                            SET fb_order = '$order'
                            WHERE fb_id = '{$badge->fb_id}'";
                    DB_query($sql, 1);
                    if (DB_error()) {
                        COM_errorLog("Badge::reOrder() SQL error: $sql", 1);
                    }
                }
                $order += $stepNumber;
            }
        }
    }


    /**
    *   Move a badge up or down the list.
    *
    *   @param  string  $id     Badge database ID
    *   @param  string  $where  Direction to move (up or down)
    */
    public static function Move($id, $where)
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
        $sql = "UPDATE {$_TABLES['ff_badges']}
                SET fb_order = fb_order $oper 11
                WHERE fb_id = '$id'";
        //echo $sql;die;
        DB_query($sql, 1);
        if (!DB_error()) {
            self::reOrder();
        } else {
            COM_errorLog("Badge::moveRow() SQL error: $sql", 1);
        }
    }


    /**
    *   Creates the edit form.
    *
    *   @return string      HTML for edit form
    */
    public function Edit()
    {
        global $_TABLES;

        $retval = '';

        $T = new \Template(__DIR__ . '/../templates/admin/');
        $T->set_file('editform', 'editbadge.thtml');
        $T->set_var(array(
            'fb_id'     => $this->fb_id,
            'fb_grp'    => $this->fb_grp,
            'grp_select' => COM_optionList($_TABLES['groups'], 'grp_id,grp_name',
                            $this->fb_gl_grp),
            'fb_image_sel' => $this->_getImageSelection($this->fb_image),
            'fb_order'  => $this->fb_order,
            'fb_grp_sel' => COM_optionList(
                        $_TABLES['ff_badges'],
                        'DISTINCT fb_grp,fb_grp',
                        $this->fb_grp, 0,
                        "fb_grp <> ''"
                ),
            'ena_chk'   => $this->fb_enabled ? 'checked="checked"' : '',
         ) );
        $retval .= $T->parse('output', 'editform');
        return $retval;
    }


    /**
    *   Create an option list of the available badge images.
    *
    *   @param  string  $selected   Selected image name
    *   @return string      Option elements for the images
    */
    private function _getImageSelection($selected='')
    {
        global $_CONF;

        $retval = '';
        $path = $_CONF['path_html'] . 'images/forum/badges';
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
    *   Save a badge from the edit form
    *
    *   @param  array   $A      Array of fields, e.g. $_POST
    *   @return string      Error messages, empty string on success
    */
    public function Save($A = array())
    {
        global $_TABLES, $LANG_GF01;

        if (!empty($A)) {
            $this->setVars($A, false);
        }

        // Handle the file upload, if any. The _handleUpload() function
        // should return the filename. If it is empty then an error occurred.
        if (isset($_FILES['fb_imgfile']['name']) &&
                !empty($_FILES['fb_imgfile']['name'])) {
            $errors = $this->_handleUpload($_FILES['fb_imgfile']['name']);
            if (!empty($errors)) {
                return $LANG_GF01['badge_save_error'] . ':<br />' . $errors;
            } else {
                $this->fb_image = $_FILES['fb_imgfile']['name'];
            }
        }

        if ($this->fb_id > 0) {
            $sql1 = "UPDATE {$_TABLES['ff_badges']} SET ";
            $sql3 = "WHERE fb_id = {$this->fb_id}";
        } else {
            $sql1 = "INSERT INTO {$_TABLES['ff_badges']} SET ";
            $sql3 = '';
        }

        $sql2 = "fb_grp = '" . DB_escapeString($this->fb_grp) . "',
                fb_order = '{$this->fb_order}',
                fb_enabled = {$this->fb_enabled},
                fb_gl_grp = '" . DB_escapeString($this->fb_gl_grp) . "',
                fb_image = '" . DB_escapeString($this->fb_image) . "'";
        $sql = $sql1 . $sql2 . $sql3;
        DB_query($sql);
        if (DB_error())  {
            return $LANG_GF01['badge_save_error'];
        } else {
            self::reOrder();
            return '';
        }
    }


    /**
    *   Handle the image file upload.
    *
    *   @return string  Error messages, or empty string on success
    */
    private function _handleUpload($filename)
    {
        global $_CONF;

        $path = $_CONF['path_html'] . '/images/forum/badges';

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
        $Upload->setFieldName('fb_imgfile');
        $Upload->setFileNames($filename);
        $Upload->uploadFiles();
        if ($Upload->areErrors() > 0) {
            return $Upload->printErrors(false);
        } else {
            return '';
        }
    }


    /**
    *   Delete a single badge record. Does not delete the image.
    *
    *   @param  integer $fb_id  Badge ID to delete
    */
    public static function Delete($fb_id)
    {
        global $_TABLES;
        DB_delete($_TABLES['ff_badges'], 'fb_id', (int)$fb_id);
    }

}

?>
