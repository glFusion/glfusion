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
    const LOGO_GRAPHIC = 1;
    const LOGO_TEXT = 0;
    const LOGO_NONE = -1;

    /** Default theme name.
     * @var string */
    private static $default = '_default';

    /** Theme (layout) name.
     * @var string */
    private $theme = '_default';

    /** Use a graphic logo?
     * @var boolean */
    private $use_graphic_logo = 0;

    /** Display the site slogan?
     * @var boolean */
    private $display_site_slogan = 0;

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


    private static function getThemes()
    {
        static $themes = array();
        if (!empty($themes)) {
            return $themes;
        }

        try {
            // @todo: need table name
            $sql = "SELECT * FROM gl_logo_new";
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
        $this->use_graphic_logo = (int)$themes[self::$default]['use_graphic_logo'];
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
        $this->use_graphic_logo = (int)$A['use_graphic_logo'];
        $this->display_site_slogan = (int)$A['display_site_slogan'];
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

        if ($this->use_graphic_logo == self::LOGO_GRAPHIC &&
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
        return $this->use_graphic_logo == self::LOGO_TEXT;
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
     * Get the logo HTML to set in the header template.
     *
     * @return  string      HTML for logo
     */
    public function getTemplate()
    {
        global $_CONF;

        $retval = '';
        if ($this->useGraphic()) {
            $L = new Template( $_CONF['path_layout'] );
            $L->set_file( array(
                'logo'          => 'logo-graphic.thtml',
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
            if ($this->display_site_slogan) {
                $L->set_var( 'site_slogan', $_CONF['site_slogan'] );
            }
            $L->parse('output','logo');
            $retval = $L->finish($L->get_var('output'));
        } elseif ($this->useText()) {
            $L = new Template( $_CONF['path_layout'] );
            $L->set_file( array(
                'logo'          => 'logo-text.thtml',
            ));
            $L->set_var( 'site_name', $_CONF['site_name'] );
            if ($this->display_site_slogan) {
                $L->set_var( 'site_slogan', $_CONF['site_slogan'] );
            }
            $L->parse('output','logo');
            $retval = $L->finish($L->get_var('output'));
        }
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
        $sql = "INSERT INTO gl_logo_new SET
            theme = ?,
            use_graphic_logo = 0,
            display_site_slogan = 0,
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
    public function toggle($field, $oldvalue)
    {
        global $_TABLES;

        // Determing the new value (opposite the old)
        $oldvalue = $oldvalue == 1 ? 1 : 0;
        $newvalue = $oldvalue == 1 ? 0 : 1;

        $sql = "UPDATE gl_logo_new
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
            var_Dump($e);die;
            $retval = $oldvalue;
        }
        return $retval;
    }


    /**
     * Logo Admin List View.
     *
     * @return  string      HTML for the logo list.
     */
    public static function adminList($cat_id=0)
    {
        global $_CONF, $_TABLES, $LANG_ADMIN;

        // @todo: need table name
        $_TABLES['logo_new'] = 'gl_logo_new';

        $dbThemes = self::getThemes();
        $data_arr = array(
            array(
                'theme' => self::$default,
                'use_graphic_logo' => (int)$dbThemes[self::$default]['use_graphic_logo'],
                'display_site_slogan ' => (int)$dbThemes[self::$default]['display_site_slogan'],
                'logo_file' => $dbThemes[self::$default]['logo_file'],
            )
        );
        $tmp = array_diff(scandir($_CONF['path_themes']), array('.', '..'));
        if ($tmp !== false) {
            foreach ($tmp as $dirname) {
                if (is_dir($_CONF['path_themes'] . $dirname)) {
                    if (isset($dbThemes[$dirname])) {
                        $use_graphic = (int)$dbThemes[$dirname]['use_graphic_logo'];
                        $show_slogan = (int)$dbThemes[$dirname]['display_site_slogan'];
                        $logo_file = $dbThemes[$dirname]['logo_file'];
                    } else {
                        $use_graphic = 0;
                        $show_slogan = 0;
                        $logo_file = '';
                    }
                    $data_arr[] = array(
                        'theme' => $dirname,
                        'use_graphic_logo' => $use_graphic,
                        'display_site_slogan' => $show_slogan,
                        'logo_file' => $logo_file,
                    );
                }
            }
        }

        USES_lib_admin();

        $display = ''; 
        $display .= '<script>
            var LogoToggle = function(cbox, id, type) {
            oldval = cbox.checked ? 0 : 1;
            var dataS = {
                "action" : "ajaxtoggle",
                "theme": id,
                "oldval": oldval,
                "type": type,
            };
            data = $.param(dataS);
            $.ajax({
                type: "POST",
                dataType: "json",
                url: site_admin_url + "/logo.php",
                data: data,
                success: function(result) {
                    cbox.checked = result.newval == 1 ? true : false;
                    try {
                        $.UIkit.notify("<i class=\'uk-icon-check\'></i>&nbsp;" + result.statusMessage, {timeout: 1000,pos:\'top-center\'});
                    }
                    catch(err) {
                        // Form is already updated, annoying popup message not needed
                        // alert(result.statusMessage);
                    }
                }
            });
            return false;
        };</script>';

        $header_arr = array(
            array(
                'text'  => _('Theme'),
                'field' => 'theme',
                'sort'  => true,
            ),
            array(
                'text'  => _('Use Graphic?'),
                'field' => 'use_graphic_logo',
                'align' => 'center',
            ),
            array(
                'text'  => _('Show Site slogan?'),
                'field' => 'display_site_slogan',
                'align' => 'center',
            ),
            array(
                'text'  => _('Logo File'),
                'field' => 'logo_file',
                'align' => 'left',
            ),
            array(
                'text' => _('Upload New'),
                'field' => 'upload',
                'align' => 'left',
            ),
        );
        $text_arr = array();
        $defsort_arr = array(
            'field' => 'theme',
            'direction' => 'ASC',
        );
        $filter = '';
        $options = '';
        $display .= ADMIN_simpleList(
            array(__CLASS__,  'getAdminField'),
            $header_arr, $text_arr, $data_arr, $defsort_arr,
            $filter, '', $options, ''
        );
        return $display;
    }


    /**
     * Get an individual field for the admin list.
     *
     * @param   string  $fieldname  Name of field (from the array, not the db)
     * @param   mixed   $fieldvalue Value of the field
     * @param   array   $A          Array of all fields from the database
     * @param   array   $icon_arr   System icon array (not used)
     * @return  string              HTML for field display in the table
     */
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr)
    {
        global $_CONF;

        if ($A['theme'] != self::$default && !is_dir($_CONF['path_themes'] . $A['theme'])) {
            return '----';
        }
        $retval = '';
        switch($fieldname) {
        case 'use_graphic_logo':
        case 'display_site_slogan':
            $checked = $fieldvalue == 1 ? 'checked="checked"' : '';
            $retval .= "<input type=\"checkbox\" id=\"{$fieldname}_{$A['theme']}\"
                        name=\"{$fieldname}_{$A['theme']}\" $checked
                        onclick='LogoToggle(this, \"{$A['theme']}\", \"$fieldname\", \"{$_CONF['site_url']}\");'>";
            break;
            break;
        case 'logo_file':
            $retval = '<span id="logo_file_' . $A['theme'] . '">';
            if (!empty($fieldvalue)) {
                $retval .= COM_createLink(
                    COM_createImage(
                        $_CONF['site_url'] . '/images/' . $fieldvalue,
                        _('Logo Image'),
                        array(
                            'style' => 'width:auto;height:100px',
                        )
                    ),
                    $_CONF['site_url'] . '/images/' . $fieldvalue,
                    array(
                        'data-uk-lightbox' => '',
                    )
                );
            }
            $retval .= '</span>';
            break;

        default:
            $retval = $fieldvalue;
            break;
        }
        return $retval;
    }

}

?>
