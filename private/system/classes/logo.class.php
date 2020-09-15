<?php
/**
 * Class to configure and display graphic and text logos.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2020 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
use \glFusion\Database\Database;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
* This class will allow you to use friendlier URL's, like:
* http://www.example.com/index.php/arg_value_1/arg_value_2/ instead of
* uglier http://www.example.com?arg1=value1&arg2=value2.
* NOTE: this does not currently work under windows as there is a well documented
* bug with IIS and PATH_INFO.  Not sure yet if this will work with windows under
* apache.  This was built so you could use this class and just disable it
* if you are an IIS user.
*
* @author       Tony Bibbs <tony@tonybibbs.com>
*
*/
class Logo
{
    const DEFAULT = -1;
    const NONE = 0;
    const GRAPHIC = 1;
    const TEXT = 2;

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

    /** Flag indicating the theme record was found.
     * @var boolean */
    private $exists = 0;


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
    private static function getThemes()
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
    private function Load($theme)
    {
        global $_TABLES;

        $themes = self::getThemes();
        $this->theme = $theme;
        $this->logo_type = (int)$themes[self::$default]['logo_type'];
        $this->display_site_slogan = (int)$themes[self::$default]['display_site_slogan'];
        $this->logo_file = $themes[self::$default]['logo_file'];
        if (isset($themes[$theme])) {
            $this->_override($themes[$theme]);
            $this->exists = 1;
        }
        return $this;
    }


    /**
     * Set the override values from the DB record.
     *
     * @param   array   $A      Array of fields from the DB
     * @return  object  $this
     */
    private function _override($A)
    {
        if ($A['logo_type'] != self::DEFAULT) {
            $this->logo_type = (int)$A['logo_type'];
        }
        if ($A['display_site_slogan'] != self::DEFAULT) {
            $this->display_site_slogan = (int)$A['display_site_slogan'];
        }
        if (!empty($A['logo_file'])) {
            $this->logo_file = $A['logo_file'];
        }
        return $this;
    }


    /**
     * Check if this theme was found in the database.
     *
     * @return  boolean     1 if found, 0 if not
     */
    public function Exists()
    {
        return $this->exists ? 1 : 0;
    }


    /**
     * Get the display_site_slogan flag value.
     *
     * @return  boolean     1 if set, 0 if not
     */
    public function displaySlogan()
    {
        return $this->display_site_slogan ? 1 : 0;
    }


    /**
     * Check if a graphic logo should be displayed.
     * Checks the flag setting and whether the logo file exists.
     *
     * @return  boolean     1 to use graphic, 0 to not
     */
    public function useGraphic()
    {
        global $_CONF;

        if (
            $this->logo_type == self::GRAPHIC &&
            file_exists($this->getImagePath())
        ) {
            return 1;
        } else {
            return 0;
        }
    }


    /**
     * Check if a text logo should be displayed.
     *
     * @return  boolean     1 to use text, 0 to not
     */
    public function useText()
    {
        return $this->logo_type == self::TEXT;
    }


    /**
     * Get the logo file name, no path.
     *
     * @return  string      Logo filename
     */
    public function getImageName()
    {
        return $this->logo_file;
    }


    /**
     * Get the full path to the logo image.
     *
     * @return  string      Filesystem path to logo image
     */
    public function getImagePath()
    {
        global $_CONF;
        return $_CONF['path_html'] . '/images/' . $this->logo_file;
    }


    /**
     * Delete the logo image.
     *
     * @return  boolean     True on success, False on failure
     */
    public function delImage()
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
    public function getTemplate()
    {
        global $_CONF;
        $_CONF['site_slogan'] = 'Testing site slogan';

        $retval = '';
        $L = new Template( $_CONF['path_layout']);
        if ($this->useGraphic()) {
            $L->set_file( array(
                'logo' => 'logo-graphic.thtml',
            ));

            $imgInfo = @getimagesize($this->getImagePath());
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
    public function createRecord()
    {
        global $_TABLES;

        // TODO: Fix table name
        $sql = "INSERT INTO " . self::$table . "SET
            theme = ?,
            logo_type = -1,
            display_site_slogan = -1,
            logo_file = ''";
        $stmt = Database::getInstance()
            ->conn->executeQuery(
                $sql,
                array($this->theme),
                array(Database::STRING)
            );
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
    public function setval($field, $oldvalue, $newvalue)
    {
        global $_TABLES;

        // Determing the new value (opposite the old)
        $oldvalue = (int)$oldvalue;
        $newvalue = (int)$newvalue;
        COM_errorLog("changing $field from $oldvalue to $newvalue for {$this->theme}");

        $sql = "UPDATE " . self::$table .
                 "SET $field  = ?
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


    public function updateFile($filename)
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
            // Do nothing
        }
        return $this;
    }


    /**
     * Logo Admin List View.
     *
     * @return  string      HTML for the logo list.
     */
    public static function adminList($cat_id=0)
    {
        global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_LOGO, $_IMAGE_TYPE;

        $dbThemes = self::getThemes();
        $data_arr = array(
            array(
                'theme' => self::$default,
                'logo_type' => (int)$dbThemes[self::$default]['logo_type'],
                'display_site_slogan' => (int)$dbThemes[self::$default]['display_site_slogan'],
                'logo_file' => $dbThemes[self::$default]['logo_file'],
            )
        );

        $tmp = array_diff(scandir($_CONF['path_themes']), array('.', '..'));
        if ($tmp !== false) {
            foreach ($tmp as $dirname) {
                if (is_dir($_CONF['path_themes'] . $dirname)) {
                    if (isset($dbThemes[$dirname])) {
                        $logo_type = (int)$dbThemes[$dirname]['logo_type'];
                        $show_slogan = (int)$dbThemes[$dirname]['display_site_slogan'];
                        $logo_file = $dbThemes[$dirname]['logo_file'];
                    } else {
                        $logo_type = 0;
                        $show_slogan = 0;
                        $logo_file = '';
                    }
                    $data_arr[] = array(
                        'theme' => $dirname,
                        'logo_type' => $logo_type,
                        'display_site_slogan' => $show_slogan,
                        'logo_file' => $logo_file,
                    );
                }
            }
        }

        USES_lib_admin();
    
        $menu_arr = array(
            array(
                'url'  => $_CONF['site_admin_url'],
                'text' => $LANG_ADMIN['admin_home'],
            ),
        );

        $retval = '';
        $retval .= COM_startBlock($LANG_LOGO['logo_options'],'', COM_getBlockTemplate('_admin_block', 'header'));
        $retval .= ADMIN_createMenu(
            $menu_arr,
            $LANG_LOGO['instructions'],
            $_CONF['layout_url'] . '/images/icons/logo.' . $_IMAGE_TYPE
        );
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

        $T = new \Template($_CONF['path_layout'] . '/admin/logo');
        $T->set_file('form', 'themes.thtml');
        $T->set_block('form', 'dataRow', 'DR');
        foreach ($data_arr as $A){
            $img_path = $_CONF['path_html'] . '/images/' . $A['logo_file'];
            if (!is_file($img_path)) {
                $img_path = '';
                $img_url = '';
            } else {
                $img_url = COM_createImage(
                    $_CONF['site_url'] . '/images/' . $A['logo_file'],
                    _('Logo Image'),
                    array(
                        'style' => 'width:auto;height:100px',
                    )
                );
            }

            $T->set_var(array(
                'theme_name'    => $A['theme'],
                'not_default'   => $A['theme'] != self::$default,
                'type_sel_' . $A['logo_type'] => 'selected="selected"',
                'slogan_sel_' . $A['display_site_slogan'] => 'selected="selected"',
                'img_path'      => $img_path,
                'img_url'       => $img_url,
                'type_sel'      => $A['logo_type'],
                'slogan_sel'    => $A['display_site_slogan'],
            ) );
            $T->parse('DR', 'dataRow', true);
        }
        $T->parse('output', 'form');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }


    /**
     * Save all images uploaded for logos.
     *
     * @return  array   Array of file information, possibly for future ajax
     */
    public static function saveLogos()
    {
        global $_CONF;

        $retval = array();
        $files = $_FILES['newlogo'];
        foreach ($files['name'] as $theme=>$filename) {
            if (
                !isset($files['tmp_name'][$theme]) ||
                $files['tmp_name'][$theme] == ''
            ) {
                continue;
            }

            switch ($files['type'][$theme]) {
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
            $thisinfo = array(
                'theme' => $theme,
            );
            if ($ext != 'unknown') {
                $imgInfo = @getimagesize($files['tmp_name'][$theme]);
                if (
                    $imgInfo[0] > $_CONF['max_logo_width'] ||
                    $imgInfo[1] > $_CONF['max_logo_height']
                ) {
                    $thisinfo['message'] = _('The logo is larger than the allowed dimensions');
                } else {
                    $newlogoname = 'logo' . substr(md5(uniqid(rand())),0,8) . $ext;
                    $rc = move_uploaded_file(
                        $files['tmp_name'][$theme],
                        $_CONF['path_html'] . 'images/' . $newlogoname
                    );
                    @chmod($_CONF['path_html'] . 'images/' . $newlogoname,0644);
                    if ($rc) {
                        $logo_name = $newlogoname;
                        $thisinfo['logo_file'] = $logo_name;

                        // Now update the themes table
                        $Logo = new self($theme);
                        if (!$Logo->Exists()) {
                            $Logo->createRecord();
                        }
                        $Logo->updateFile($logo_name);
                    }
                }
            } else {
                $thisinfo['message'] = _('Unknown file type uploaded');
                $thisinfo['url'] =  COM_createImage(
                    $_CONF['site_url'] . '/images/' . $logo_name,
                    _('Logo Image'),
                    array(
                        'style' => 'width:auto;height:100px',
                    )
                );
            }
            $retval[] = $thisinfo;
        }
        return $retval;
    }

}

