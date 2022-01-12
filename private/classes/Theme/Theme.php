<?php
/**
 * Class to manage and configure glFusion themes.
 * Access to themes can be controlled by group to allow for development and
 * testing without impacting current visitors. If a user has a theme set which
 * is later disabled, the user will be reset to the $_CONF['theme'] setting.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2020-2022 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     v0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Theme;
use glFusion\Database\Database;
use glFusion\Cache\Cache;
use Template;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
 * This class handles saving and retrieving logo information for
 * different themes.
 * @package glfusion
 */
class Theme
{
    // Logo selection options
    const DEFAULT = -1;     // Use default setting
    const NONE = 0;
    const GRAPHIC = 1;
    const TEXT = 2;

    const DEFAULT_NAME = '_default';
    const CACHE_KEY = 'themes_available';
    const CACHE_TTL = 900;  // 15 minutes

    /** Default theme object.
     * @var object */
    private static $default = NULL;

    /** Theme (layout) name.
     * @var string */
    private $theme = self::DEFAULT_NAME;

    /** Use a graphic logo?
     * @var integer */
    private $logo_type = -1;

    /** Display the site slogan?
     * @var boolean */
    private $display_site_slogan = -1;

    /** Graphic logo filename.
     * @var string */
    private $logo_file = '';

    /** Group allowed to select the theme. Default = All Users.
     * @var integer */
    private $grp_access = 2;

    /** Flag indicating the theme record was found.
     * @var boolean */
    private $exists = false;

    /** Flag to indicate that some change has been made.
     * @var boolean */
    private $tainted = false;


    /**
     * Constructor.
     *
     * @param   string  $theme  Theme name being used
     */
    public function __construct(?array $A = NULL)
    {
        if (is_array($A) && !empty($A)) {
            $this->setVars($A);
        }
    }


    /**
     * Get an instance of a specific theme.
     *
     * @param   string  $theme  Theme name, default is user's current theme
     * @return  object  Theme object
     */
    public static function getInstance(?string $theme=NULL) : self
    {
        global $_USER;

        static $themes = array();
        if ($theme == '') {
            $theme = $_USER['theme'];  // set in lib-common.php
        }
        if (!array_key_exists($theme, $themes)) {
            $_Theme = new self;
            $_Theme->withName($theme)->readTheme();
            $_Theme->_override();
            $themes[$theme] = $_Theme;
        }
        return $themes[$theme];
    }


    /**
     * Set all the theme vars from an array, e.g. database record.
     *
     * @param   array   $A      Array of key-value pairs
     * @return  object  $this
     */
    public function setVars(?array $A=NULL) : self
    {
        if (!is_array($A) || empty($A)) {
            return $this;
        }

        if (isset($A['theme'])) {
            $this->withName($A['theme']);
        }
        if (isset($A['display_site_slogan'])) {
            $this->withDisplaySlogan((int)$A['display_site_slogan']);
        }
        if (isset($A['logo_type'])) {
            $this->withLogoType((int)$A['logo_type']);
        }
        if (isset($A['grp_access'])) {
            $this->withGrpAccess((int)$A['grp_access']);
        }
        if (isset($A['logo_file'])) {
            $this->withImageName($A['logo_file']);
        }
        return $this;
    }


    /**
     * Read the current theme name from the DB and load properties.
     * If the theme is not found in the DB but exists on disk, use
     * default properties. If the theme isn't valid (removed from disk),
     * then use the site theme.
     *
     * @return  object  $this
     */
    private function readTheme() : self
    {
        global $_TABLES;
        $sql = "SELECT * FROM {$_TABLES['themes']}
            WHERE theme = ?";
        try {
            $stmt = Database::getInstance()
                ->conn->executeQuery(
                    $sql,
                    array($this->theme),
                    array(Database::STRING)
                );
            $data = $stmt->fetch(Database::ASSOCIATIVE);
            $stmt->closeCursor();
        } catch(\Doctrine\DBAL\DBALException $e) {
            $data = array();
            // Ignore errors or failed attempts
        }
        if (is_array($data)) {
            $this->setVars($data);
        } else {
            // Theme not found. Maybe not set in the database yet.
            if ($this->isValid()) {
                $this->Taint()->Save(); // just creates a default record
            } else {
                $this->fromDefault();   // get the site theme
            }
        }
        return $this;
    }


    /**
     * Get the default theme.
     * Caches in the static object var.
     *
     * @return  object  Default Theme object
     */
    protected static function getDefault() : object
    {
        if (self::$default === NULL) {
            self::$default = new self;
            self::$default->withName(self::DEFAULT_NAME)->readTheme();
        }
        return self::$default;
    }


    /**
     * Get all the themes that have DB records.
     *
     * @param   boolean $all    True to get all, False to check group access
     * @return  array       Array of DB record arrays
     */
    public static function getAll(?bool $all=false, ?bool $enabled = true) : array
    {
        global $_TABLES, $_USER, $_CONF;

        $themes = array();

        try {
            $sql = "SELECT * FROM {$_TABLES['themes']}
                WHERE theme <> '" . self::DEFAULT_NAME . "'";
            if ($enabled) {
                $sql .= " AND grp_access > 0";
            }
            if (!$all) {
                $groups = SEC_getUserGroups($_USER['uid']);
                $grp_sql = implode(',', $groups);
                $sql .= " AND grp_access IN ($grp_sql)";
            }
            $sql .= " ORDER BY theme ASC";
            $stmt = Database::getInstance()
                ->conn->executeQuery($sql);
            $data = $stmt->fetchAll(Database::ASSOCIATIVE);
            $stmt->closeCursor();
        } catch(\Doctrine\DBAL\DBALException $e) {
            $data = array();
            // Ignore errors or failed attempts
        }

        foreach($data as $A) {
            $themes[$A['theme']] = new self($A);
        }
        return $themes;
    }


    /**
     * Add or remove themes from the DB if they've been changed on the filesystem.
     *
     * @param   array   $themes     Array of Theme objects
     * @return  array       Updated array of themes
     */
    protected static function syncFilesystem(array $themes) : array
    {
        global $_CONF;

        $themes = self::getAll(true, false);
        $themes[self::DEFAULT_NAME] = self::getDefault();

        $tmp = array_diff(scandir($_CONF['path_themes']), array('.', '..'));
        if ($tmp !== false) {
            foreach ($tmp as $idx=>$dirname) {
                if (!is_dir($_CONF['path_themes'] . $dirname)) {
                    // ignore regular files
                    continue;
                }
                if (array_key_exists($dirname, $themes)) {
                    // already have this theme in the DB, verify that it's valid
                    if (!$themes[$dirname]->isValid()) {
                        self::delete($dirname);
                        unset($themes[$dirname]);
                    }
                } else {
                    // don't have it in the DB, add it with defaults if valid
                    $Th = new Theme;
                    $Th->withName($dirname);
                    if ($Th->isValid()) {
                        $Th->Taint()->Save();
                        $themes[$dirname] = $Th;
                    }
                }
            }

            // Now remove themes no longer on disk but still in the DB.
            $dbThemes = array_keys($themes);
            foreach (array_diff($dbThemes, $tmp) as $theme) {
                if ($theme != self::DEFAULT_NAME) {
                    self::delete($theme);
                }
            }
        }
        return $themes;
    }


    /**
     * Set the logo type - graphic, text or none
     *
     * @param   integer $type   Logo type flag
     * @return  object  $this
     */
    public function withLogoType(int $type) : self
    {
        if ($type != $this->logo_type) {
            $this->logo_type = (int)$type;
            $this->tainted = true;
        }
        return $this;
    }


    /**
     * Get the type of logo (text, image).
     *
     * @return  integer     Logo type flag
     */
    public function getLogoType() : int
    {
        return (int)$this->logo_type;
    }


    /**
     * Set the theme name.
     *
     * @param   string  $name   Theme name
     * @return  object  $this
     */
    public function withName(string $name) : self
    {
        $this->theme = $name;
        return $this;
    }


    /**
     * Get the theme name.
     *
     * @return  string      Theme name
     */
    public function getName() : string
    {
        return $this->theme;
    }


    /**
     * Set the flag to display the site slogan or not.
     *
     * @param   integer $type   Display flag
     * @return  object  $this
     */
    public function withDisplaySlogan(?bool $flag=NULL) : self
    {
        if ($flag != $this->display_site_slogan) {
            $this->display_site_slogan = $flag ? 1 : 0;
            $this->tainted = true;
        }
        return $this;
    }


    /**
     * Set the logo image filename.
     *
     * @param   string  $name   Image filename
     * @return  object  $this
     */
    public function withImageName(string $name) : self
    {
        if ($name != $this->logo_file) {
            $this->logo_file = $name;
            $this->tainted = true;
        }
        return $this;
    }


    /**
     * Use default values for this theme where value is set to inherit.
     *
     * @return  object  $this
     */
    private function _override() : self
    {
        $Def = self::getDefault();

        if ($this->logo_type == -1) {
            $this->logo_type = $Def->getLogoType();
        }
        if ($this->display_site_slogan == -1) {
            $this->display_site_slogan = $Def->displaySlogan() ? 1 : 0;
        }
        if (empty($this->logo_file)) {
            $this->logo_file = $Def->getImageName();
        }

        // Override group access unless this is the site theme,
        // which must always be available.
        if ($this->isSiteTheme()) {
            $this->grp_access = 2;
        } elseif ($this->grp_access == 0) {
            $this->grp_access = $Def->getGrpAccess();
        }
        return $this;
    }


    /**
     * Check if the current user can use the current theme.
     *
     * @return  boolean     True if the user is in the access group
     */
    public function canUse() : bool
    {
        if ($this->isSiteTheme()) {
            // The main site theme must always be available or there's
            // nothing to fall back on
            return true;
        } elseif (self::pathExists($this->theme)) {
            return SEC_inGroup($this->grp_access);
        } else {
            return false;
        }
    }


    /**
     * Check if the theme path exists on disk.
     * Verifies that there's a functions.php file in the path.
     *
     * @param   string  $theme  Theme name
     * @return  boolean     True if the theme path exists
     */
    public static function pathExists(string $theme) : bool
    {
        global $_CONF;

        return is_file($_CONF['path_themes'] . $theme . '/functions.php');
    }


    /**
     * Check if this theme was found in the database.
     *
     * @return  boolean     1 if found, 0 if not
     */
    public function Exists() : bool
    {
        return $this->exists;
    }


    /**
     * Get the display_site_slogan flag value.
     *
     * @return  boolean     1 if set, 0 if not
     */
    public function displaySlogan() : bool
    {
        return $this->display_site_slogan ? true : false;
    }


    /**
     * Check if a graphic logo should be displayed.
     * Checks the flag setting and whether the logo file exists.
     *
     * @return  boolean     1 to use graphic, 0 to not
     */
    public function useGraphic() : bool
    {
        global $_CONF;

        if (
            $this->logo_type == self::GRAPHIC &&
            file_exists($this->getImagePath())
        ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Check if a text logo should be displayed.
     *
     * @return  boolean     1 to use text, 0 to not
     */
    public function useText() : bool
    {
        return $this->logo_type == self::TEXT;
    }


    /**
     * Get the logo file name, no path.
     *
     * @return  string      Logo filename
     */
    public function getImageName() : string
    {
        return $this->logo_file;
    }


    /**
     * Get the full path to the logo image, including the filename.
     *
     * @return  string      Filesystem path to logo image
     */
    public function getImagePath() : string
    {
        global $_CONF;
        return $_CONF['path_images'] . $this->logo_file;
    }


    /**
     * Set the group access value.
     *
     * @param   integer $grp_access Group ID with access
     */
    public function withGrpAccess(int $grp_access) : self
    {
        $this->grp_access = $grp_access;
        return $this;
    }


    /**
     * Get the group that can use this theme.
     *
     * @return  ingeter     Group ID
     */
    public function getGrpAccess() : int
    {
        return (int)$this->grp_access;
    }


    /**
     * Delete the logo image.
     *
     * @return  boolean     True on success, False on failure
     */
    public function delImage() : bool
    {
        if (is_file($this->getImagePath())) {
            $this->updateFile('');
            if ($this->logo_file == '') {
                @unlink($this->getImagePath());
                return true;
            }
        }
        return false;
    }


    /**
     * Get the logo HTML to set in the header template.
     *
     * @return  string      HTML for logo
     */
    public function getTemplate() : string
    {
        global $_CONF;

        $retval = '';
        $L = new Template( $_CONF['path_layout']);
        if ($this->useGraphic()) {
            $L->set_file( array(
                'logo' => 'logo-graphic.thtml',
            ));

            $imgInfo = @getimagesize($this->getImagePath());
            if (!is_array($imgInfo)) {
                return '';
            }
            $dimension = $imgInfo[3];

            $L->set_var( 'site_name', $_CONF['site_name'] );
            $site_logo = $_CONF['path_images_url'] . '/' . $this->logo_file;
            $L->set_var( 'site_logo', $site_logo);
            $L->set_var( 'dimension', $dimension );
            if ( $imgInfo[1] != 100 ) {
                $delta = 100 - $imgInfo[1];
                $newMargin = $delta;
                $L->set_var( 'delta', 'style="padding-top:' . $newMargin . 'px;"');
            } else {
                $L->set_var('delta','');
            }
        } elseif ($this->useText()) {
            $L->set_file( array(
                'logo' => 'logo-text.thtml',
            ));
            $L->set_var('site_name', $_CONF['site_name']);
        }
        if ($this->display_site_slogan) {
            $L->set_var('site_slogan', $_CONF['site_slogan']);
        }
        $L->parse('output','logo');
        $retval = $L->finish($L->get_var('output'));
        return $retval;
    }


    /**
     * Create a new record in the logo table.
     * Called when toggling or uploading images.
     *
     * @return  object  $this
     */
    public function Save() : self
    {
        global $_TABLES;

        if (!$this->tainted) {
            // No changes made, act as if successful.
            return $this;
        }

        $sql = "INSERT INTO {$_TABLES['themes']} SET
            theme = ?,
            logo_type = ?,
            display_site_slogan = ?,
            logo_file = ?
            ON DUPLICATE KEY UPDATE
            logo_type = ?,
            display_site_slogan = ?,
            logo_file = ?";
        $stmt = Database::getInstance()
            ->conn->executeQuery(
                $sql,
                array(
                    $this->theme,
                    $this->logo_type,
                    $this->display_site_slogan,
                    $this->logo_file,
                    $this->logo_type,
                    $this->display_site_slogan,
                    $this->logo_file,
                ),
                array(
                    Database::STRING,
                    Database::INTEGER,
                    Database::INTEGER,
                    Database::STRING,
                    Database::INTEGER,
                    Database::INTEGER,
                    Database::STRING,
                )
            );
        $this->tainted = false;
        $this->exists = true;
        return $this;
    }


    /**
     * Sets a boolean field to the opposite of the supplied value.
     * Field ID value is sanitized but the field name is not.
     *
     * @param   string  $field      Name of DB field to toggle
     * @param   integer $id         ID of record to modify
     * @param   integer $oldvalue   Old (current) value
     * @return  integer     New value, or old value upon failure
     */
    public function setval(string $field, int $oldvalue, int $newvalue) : int
    {
        global $_TABLES;

        // Determing the new value (opposite the old)
        $oldvalue = (int)$oldvalue;
        $newvalue = (int)$newvalue;

        $sql = "UPDATE {$_TABLES['themes']}
            SET $field  = ?
            WHERE theme = ?";
        try {
            $stmt = Database::getInstance()
                ->conn->executeQuery(
                    $sql,
                    array($newvalue, $this->theme),
                    array(Database::INTEGER, Database::STRING)
                );
            $retval = $newvalue;
        } catch(\Doctrine\DBAL\DBALException $e) {
            COM_errorLog(print_r($e,true));
            $retval = $oldvalue;
        }
        return $retval;
    }


    /**
     * Update the logo filename in the database when an image is uploaded.
     *
     * @param   string  $filename   New filename
     * @return  object  $this
     */
    public function updateFile(string $filename) : self
    {
        global $_TABLES;

        $sql = "UPDATE {$_TABLES['themes']}
            SET logo_file  = ?
            WHERE theme = ?";
        try {
            $stmt = Database::getInstance()
                ->conn->executeQuery(
                    $sql,
                    array($filename, $this->theme),
                    array(Database::STRING, Database::STRING)
                );
            $this->logo_file = $filename;
        } catch(\Doctrine\DBAL\DBALException $e) {
            // Do nothing. Error message is returned from Javascript
        }
        return $this;
    }


    /**
     * Save all images uploaded for logos.
     *
     * @deprecated - functions are handled by AJAX now
     * @return  array   Array of file information, possibly for future ajax
     */
    public static function saveLogos() : array
    {
        global $_CONF;

        $retval = array();
        $files = $_FILES['newlogo'];
        foreach ($files['name'] as $theme=>$filename) {
            // Create or update the theme record.
            $Logo = new self($theme);
            $thisinfo = array(
                'theme' => $theme,
            );

            if (isset($_POST['logo_type'][$theme])) {
                $Logo->withLogoType($_POST['logo_type'][$theme]);
            }
            if (isset($_POST['display_site_slogan'][$theme])) {
                $Logo->withDisplaySlogan($_POST['display_site_slogan'][$theme]);
            }

            // Handle the file upload, if any
            if (
                isset($files['tmp_name'][$theme]) &&
                $files['tmp_name'][$theme] != ''
            ) {
                $Logo->handleUpload($files, $theme);
            }
        }
    }


    /**
     * Handle logo image uploads.
     *
     * @param   array   $files  $_FILES array
     * @param   integer $index  Index into the $files array
     * @return  array       Array of status, message, and url (if successful)
     */
    public function handleUpload(array $files, int $index) : array
    {
        global $_CONF, $LANG_LOGO;

        $thisinfo = array(
            'status' => false,
            'message' => '',
        );
        switch ($files['type'][$index]) {
        case 'image/png' :
        case 'image/x-png' :
            $ext = '.png';
            break;
        case 'image/gif' :
            $ext = '.gif';
            break;
        case 'image/jpg' :
        case 'image/jpeg' :
        case 'image/pjpeg' :
            $ext = '.jpg';
            break;
        default :
            $ext = 'unknown';
            $thisinfo['message'] = $LANG_LOGO['invalid_type'];
            break;
        }

        if ($ext != 'unknown') {
            $imgInfo = @getimagesize($files['tmp_name'][$index]);

            if ($imgInfo) {
                if (
                    $imgInfo[0] > $_CONF['max_logo_width'] ||
                    $imgInfo[1] > $_CONF['max_logo_height']
                ) {
                    $thisinfo['message'] = $LANG_LOGO['invalid_size'] .
                        $_CONF['max_logo_width'] . ' x ' . $_CONF['max_logo_height'];
                } else {
                    $newlogoname = 'logo' . substr(md5(uniqid(rand())),0,8) . $ext;
                    $rc = move_uploaded_file(
                        $files['tmp_name'][$index],
                        $_CONF['path_images'] . $newlogoname
                    );
                    if ($rc) {
                        @chmod($_CONF['path_images'] . $newlogoname,0644);
                        $this->updateFile($newlogoname);
                        $thisinfo['status'] = true;
                    }
                }
            }
            $thisinfo['url'] =  COM_createImage(
                $_CONF['path_images_url'] . '/' . $this->logo_file,
                $LANG_LOGO['current_logo'],
                array(
                    'class' => 'themeAdminImage',
                )
            );
        }
        return $thisinfo;
    }


    /**
     * Check if the theme is valid.
     * Used to avoid offering themes which may have been removed from being
     * used.
     *
     * @return  boolean     True if valid, False if not
     */
    public function isValid() : bool
    {
        static $valid = NULL;
        if ($valid === NULL) {
            $valid = self::pathExists($this->theme);
        }
        return $valid;
    }


    /**
     * Taint the record to force saving.
     *
     * @return  object  $this
     */
    public function Taint() : self
    {
        $this->tainted = true;
        return $this;
    }


    /**
     * Delete a theme from the database.
     *
     * @param   string  $name   Theme name
     */
    public static function delete(string $name) : void
    {
        global $_TABLES;

        try {
            $stmt = Database::getInstance()
                ->conn->executeQuery(
                    "DELETE FROM {$_TABLES['themes']}
                    WHERE theme = ?",
                    array($name),
                    array(Database::STRING)
                );
        } catch(\Doctrine\DBAL\DBALException $e) {
            // Ignore errors or failed attempts
        }
    }


    /**
     * Invalidate the cache, such as after updating themes.
     */
    private static function _invalidateCache()
    {
        Cache::getInstance()->delete(self::CACHE_KEY);
    }


    /**
     * Set themes into the cache to speed up subsequent lookups.
     *
     * @param   array   $data   Array of Theme objects
     * @param   string  $cache_key  Cache key (for future use)
     */
    private static function _setCache($data, $cache_key=self::CACHE_KEY)
    {
        Cache::getInstance()->set($cache_key, $data, array('themes'), self::CACHE_TTL);
    }


    /**
     * Get an item from cache, if available.
     *
     * @param   string  $cache_key  Cache key (for future use)
     * @return  mixed       Cache data, NULL if not found
     */
    private static function _getCache($cache_key=self::CACHE_KEY)
    {
        if (Cache::getInstance()->has($cache_key)) {
            return Cache::getInstance()->get($cache_key);
        } else {
            return NULL;
        }
    }


    /**
     * Read the default site theme as a fallback if a theme is not found.
     *
     * @return  object  $this
     */
    private function fromDefault() : self
    {
        global $_CONF;

        if ($this->isSiteTheme()) {   // avoid recursion
            return $this;
        }
        $this->theme = $_CONF['theme'];
        return $this->readTheme();
    }


    /**
     * Helper function to check if this is the default site theme.
     *
     * @return  boolean     True if default theme, False if user theme
     */
    public function isSiteTheme() : bool
    {
        global $_CONF;

        return $this->theme == $_CONF['theme'];
    }


    /**
     * Transfer settings from the legacy logo table to themes.
     */
    public static function upgradeFromLogo() : void
    {
        global $_TABLES, $_CONF;

        if (!isset($_TABLES['logo'])) {
            return;
        }

        // Check the _default theme for a flag to indicate that the
        // upgrade should be done. Do not import from the logo table
        // if already done, or for new installations.
        $Default = self::getDefault();
        if ($Default->getLogoType() != 99) {
            // Already done
            return;
        }

        // Get the logo information to populate the site theme.
        $stmt = Database::getInstance()
            ->conn->executeQuery("SELECT * FROM {$_TABLES['logo']}");
        $data = $stmt->fetchAll(Database::ASSOCIATIVE);
        if (!is_array($data)) {
            return;
        }

        foreach ($data as $d) {
            switch ($d['config_name']) {
            case 'use_graphic_logo':
                if ($d['config_value'] == 1) {
                    $Default->withLogoType(self::GRAPHIC);
                } elseif ($d['config_value'] == 0) {
                    $Default->withLogoType(self::TEXT);
                } else {
                    $Default->withLogoType(self::NONE);
                }
                break;
            case 'display_site_slogan':
                $Default->withDisplaySlogan($d['config_value']);
                break;
            case 'logo_name':
                $Default->withImageName($d['config_value']);
                break;
            }
        }
        // Update with the default logo type to avoid overwriting values again.
        $Default->withLogoType(0)->Save();
    }

}
