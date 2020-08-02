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
class Logo {

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


    private function _override($A)
    {
        $this->use_graphic_logo = (int)$A['use_graphic_logo'];
        $this->display_site_slogan = (int)$A['display_site_slogan'];
        if (!empty($A['logo_file'])) {
            $this->logo_file = $A['logo_file'];
        }
        return $this;
    }


    public function Exists()
    {
        return $this->exists ? 1 : 0;
    }


    public function displaySlogan()
    {
        return $this->display_site_slogan ? 1 : 0;
    }


    public function useGraphic()
    {
        global $_CONF;

        if ($this->use_graphic_logo && file_exists($this->getImagePath())) {
            return 1;
        } else {
            return 0;
        }
    }


    public function getImageName()
    {
        return $this->logo_file;
    }


    public function getImagePath()
    {
        global $_CONF;
        return $_CONF['path_html'] . '/images/' . $this->logo_file;
    }


    public function getImageUrl()
    {
        global $_CONF;
    }


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
        } else {
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




    /**
    * Grabs any variables from the query string
    *
    * @access   private
    */
    function _getArguments()
    {
        if (isset ($_SERVER['PATH_INFO'])) {
            if ($_SERVER['PATH_INFO'] == '') {
                if (isset ($_ENV['ORIG_PATH_INFO'])) {
                    $this->_arguments = explode('/', $_ENV['ORIG_PATH_INFO']);
                } else {
                    $this->_arguments = array();
                }
            } else {
                $this->_arguments = explode ('/', $_SERVER['PATH_INFO']);
            }
            array_shift ($this->_arguments);
            if ( isset($this->_arguments[0]) && $this->_arguments[0] == substr($_SERVER['SCRIPT_NAME'],1) ) {
                array_shift($this->_arguments);
            }
        } else if (isset ($_ENV['ORIG_PATH_INFO'])) {
            $scriptName = array();
            $scriptName = explode('/',$_SERVER['SCRIPT_NAME']);
            array_shift($scriptName);
            $this->_arguments = explode('/', substr($_ENV['ORIG_PATH_INFO'],1));
            if ( $this->_arguments[0] == $scriptName[0] ) {
                $this->_arguments = array();
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $this->_arguments = explode('/', substr($_SERVER['ORIG_PATH_INFO'], 1));
            array_shift ($this->_arguments);
            $script_name = strrchr($_SERVER['SCRIPT_NAME'],"/");
            if ( $script_name == '' ) {
                $script_name = $_SERVER['SCRIPT_NAME'];
            }
            $indexArray = 0;
            $search_script_name = substr($script_name,1);
            if ( array_search($search_script_name,$this->_arguments) !== FALSE ) {
                $indexArray = array_search($search_script_name,$this->_arguments);
            }
            for ($x=0; $x < $indexArray; $x++) {
                array_shift($this->_arguments);
            }
            if ( isset($this->_arguments[0]) && $this->_arguments[0] == substr($script_name,1) ) {
                array_shift($this->_arguments);
            }
        } else {
            $this->_arguments = array ();
        }
    }

    /**
    * Enables url rewriting, otherwise URL's are passed back
    *
    * @param        boolean     $switch     turns URL rewriting on/off
    *
    */
    function setEnabled($switch)
    {
        if ($switch) {
            $this->_enabled = true;
        } else {
            $this->_enabled = false;
        }
    }

    /**
    * Returns whether or not URL rewriting is enabled
    *
    * @return   boolean true if URl rewriting is enabled, otherwise false
    *
    */
    function isEnabled()
    {
        return $this->_enabled;
    }

    /**
    * Returns the number of variables found in query string
    *
    * This is particularly useful just before calling setArgNames() method
    *
    * @return   int     Number of arguments found in URL
    *
    */
    function numArguments()
    {
        return count($this->_arguments);
    }

    /**
    * Assigns logical names to query string variables
    *
    * @param        array       $names      String array of names to assign to variables pulled from query string
    * @return       boolean     true on success otherwise false
    *
    */
    function setArgNames($names = array())
    {
        if (count($names) < count($this->_arguments)) {
            print "URL Class: number of names passed to setArgNames must be equal or greater than number of arguments found in URL (" . count($this->_arguments) . ")";
            exit;
        }
        if (is_array($names)) {
            $newArray = array();
            for ($i = 1; $i <= count($this->_arguments); $i++) {
                $newArray[current($names)] = current($this->_arguments);
                next($names);
        		next($this->_arguments);
            }
            $this->_arguments = $newArray;
            reset($this->_arguments);
        } else {
            return false;
        }
        return true;
    }

    /**
    * Gets the value for an argument
    *
    * @param        string      $name       Name of argument to fetch value for
    * @return       mixed       returns value for a given argument
    *
    */
    function getArgument($name)
    {
        // if in GET VARS array return it
        if (!empty($_GET[$name])) {
            return $_GET[$name];
        }

        // ok, pull from query string
        if (in_array($name,array_keys($this->_arguments))) {
            return $this->_arguments[$name];
        }

        return '';
    }

    /**
    * Builds crawler friendly URL if URL rewriting is enabled
    *
    * This function will attempt to build a crawler friendly URL.  If this feature is
    * disabled because of platform issue it just returns original $url value
    *
    * @param        string      $url    URL to try and convert
    * @return       string      rewritten if _isenabled is true otherwise original url
    *
    */
    function buildURL($url)
    {
        $retval = '';
        if (!$this->isEnabled()) {
            return $url;
        }

        $pos = strpos($url,'?');
        $query_string = substr($url,$pos+1);
        $finalList = array();
        $paramList = explode('&',$query_string);
        for ($i = 1; $i <= count($paramList); $i++) {
            $keyValuePairs = explode('=',current($paramList));
            if (is_array($keyValuePairs)) {
                $argName = current($keyValuePairs);
                next($keyValuePairs);
                $finalList[$argName] = current($keyValuePairs);
            }
            next($paramList);
        }
        $newArgs = '/';
        for ($i = 1; $i <= count($finalList); $i++) {
            $newArgs .= current($finalList);
            if ($i <> count($finalList)) {
                $newArgs .= '/';
            }
            next($finalList);
        }
        $retval = str_replace('?' . $query_string,$newArgs,$url);
        return COM_sanitizeURL($retval);
    }
}

?>
