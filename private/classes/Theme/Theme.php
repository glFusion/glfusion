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

    // Logo size styling for the admin list
    const LOGO_ADMIN_STYLE = 'width:auto;max-height:50px';

    /** Table name, until included in glFusion core.
     * @var string */
    public static $table = 'gl_themes';

    /** Default theme name.
     * @var string */
    public static $default = '_default';

    /** Theme (layout) name.
     * @var string */
    private $theme = '_default';

    /** Use a graphic logo?
     * @var boolean */
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
    public function __construct($theme = '')
    {
        global $_USER;

        if ($theme == '') {
            $theme = $_USER['theme'];  // set in lib-common.php
        }
        $this->Load($theme);
    }


    /**
     * Get all the themes that have DB records.
     *
     * @return  array       Array of DB record arrays
     */
    protected static function getThemes() : array
    {
        static $themes = array();
        if (!empty($themes)) {
            return $themes;
        }

        try {
            // @todo: need table name
            $sql = "SELECT * FROM " . self::$table;
            $stmt = Database::getInstance()
                ->conn->executeQuery(
                    $sql
                );
            $data = $stmt->fetchAll(Database::ASSOCIATIVE);
            $stmt->closeCursor();
        } catch(\Doctrine\DBAL\DBALException $e) {
            $data = array();
            // Ignore errors or failed attempts
        }
        foreach($data as $A) {
            $themes[$A['theme']] = $A;
        }
        return $themes;
    }


    /**
     * Get the logo information for the theme.
     * Sets the default values and then overrides with the specific theme
     * values if found in the DB.
     * If the requested theme has a record in the DB the "exists" flag is set.
     *
     * @param   string  $theme  Theme name
     * @return  object  $this
     */
    private function Load($theme) : self
    {
        $themes = self::getThemes();
        $this->theme = $theme;
        $default = $themes[self::$default];

        // Set vars directly to avoid tainting if already exists.
        $this->logo_type = (int)$default['logo_type'];
        $this->display_site_slogan = (int)$default['display_site_slogan'];
        $this->logo_file = $default['logo_file'];

        if (array_key_exists($theme, $themes)) {
            $this->exists = true;
            $this->tainted = false;
            // Override the default values here
            $this->_override($themes[$theme]);
        } else {
            $this->exists = false;
            $this->tainted = true;  // doesn't exist in table yet, need to save
        }

        if ($this->exists && self::pathExists($theme)) {
            // Create the DB record if it doesn't exist but is on disk
            $this->Save();
        }
        return $this;
    }


    /**
     * Set the logo type - graphic, text or none
     *
     * @param   integer $type   Logo type flag
     * @return  object  $this
     */
    public function setLogoType(int $type) : self
    {
        if ($type != $this->logo_type) {
            $this->logo_type = (int)$type;
            $this->tainted = true;
        }
        return $this;
    }


    /**
     * Set the flag to display the site slogan or not.
     *
     * @param   integer $type   Display flag
     * @return  object  $this
     */
    public function setDisplaySiteSlogan(?bool $flag=NULL) : self
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
    public function setImageName(string $name) : self
    {
        if ($name != $this->logo_file) {
            $this->logo_file = $name;
            $this->tainted = true;
        }
        return $this;
    }


    /**
     * Set the override values from the DB record.
     *
     * @param   array   $A      Array of fields from the DB
     * @return  object  $this
     */
    private function _override(array $A) : self
    {
        global $_CONF;

        if (isset($A['logo_type']) && $A['logo_type'] > -1) {
            $this->logo_type = (int)$A['logo_type'];
        }
        if (isset($A['display_site_slogan']) && $A['display_site_slogan'] > -1) {
            $this->display_site_slogan = (int)$A['display_site_slogan'];
        }
        if (isset($A['logo_file']) && !empty($A['logo_file'])) {
            $this->logo_file = $A['logo_file'];
        }
        if (isset($A['grp_access']) && $A['theme'] != $_CONF['theme']) {
            // Override group access unless this is the site theme,
            // which must always be available.
            $this->grp_access = (int)$A['grp_access'];
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
        global $_CONF;

        if ($this->theme == $_CONF['theme']) {
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
        return $_CONF['path_html'] . '/images/' . $this->logo_file;
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
            $site_logo = $_CONF['site_url'] . '/images/' . $this->logo_file;
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

        $sql = "INSERT INTO " . self::$table . " SET
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

        $sql = "UPDATE " . self::$table .
                 " SET $field  = ?
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
        $sql = "UPDATE " . self::$table .
                " SET logo_file  = ?
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
                $Logo->setLogoType($_POST['logo_type'][$theme]);
            }
            if (isset($_POST['display_site_slogan'][$theme])) {
                $Logo->setDisplaySiteSlogan($_POST['display_site_slogan'][$theme]);
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
            'message' => $LANG_LOGO['invalid_type'],
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
                        $_CONF['path_html'] . 'images/' . $newlogoname
                    );
                    if ($rc) {
                        @chmod($_CONF['path_html'] . 'images/' . $newlogoname,0644);
                        $this->updateFile($newlogoname);
                        $thisinfo['status'] = true;
                    }
                }
            }
            $thisinfo['url'] =  COM_createImage(
                $_CONF['site_url'] . '/images/' . $this->logo_file,
                $LANG_LOGO['current_logo'],
                array(
                    'style' => self::LOGO_ADMIN_STYLE,
                )
            );
        }
        return $thisinfo;
    }

}
