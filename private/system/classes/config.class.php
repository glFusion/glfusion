<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | config.class.php                                                         |
// |                                                                          |
// | Controls the UI and database for configuration settings                  |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2017 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2007-2008 by the following authors:                        |
// | Authors: Aaron Blankstein  - kantai AT gmail DOT com                     |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

if (!defined ('CONFIG_CACHE_FILE_NAME')) {
    define('CONFIG_CACHE_FILE_NAME','$$$config$$$.cache');
}

class config
{
    /**
     * Path to db-config.php file
     *
     * @var string
     */
    var $dbconfig_file;

    /**
     * Array of configurations
     *
     * @var array
     */
    var $config_array;

    /**
     * Array of oauth consumer keys
     *
     * @var array
     */
    var $consumer_keys = array('facebook_consumer_key','facebook_consumer_secret','linkedin_consumer_key','linkedin_consumer_secret','twitter_consumer_key','twitter_consumer_secret','google_consumer_key','google_consumer_secret','microsoft_consumer_key','microsoft_consumer_secret','github_consumer_key','github_consumer_secret','fb_appid','comment_fb_appid');

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->config_array = array();
    }

    /**
     * This function will return an instance of the config class. If an
     * instance with the given group/reference name does not exist, then it
     * will create a new one. This function insures    that there is only one
     * instance for a given group name.
     *
     *    @param string group_name   This is simply the group name that this
     *                               config object will control - for the main
     *                               settings this is 'Core'
     *
     *    @return config             The newly created or referenced config object
     */
    public static function &get_instance()
    {
        static $instance;

        if (!$instance) {
            $instance = new config();
        }

        return $instance;
    }

    /**
     * This function sets the secure configuration file (database related
     * settings) for the configuration class to read. This should only need to
     * be called for the 'Core' group. It also must be called before
     * load_baseconfig()
     *
     * @param string sf        The filename and path of the secure db settings
     */

    function set_configfile($sf)
    {
        $this->dbconfig_file = $sf;
    }

    /**
     * This function reads the secure configuration file and loads
     * lib-database.php. This needs to be called in the 'Core' group before
     * &init_config() can be used. It only needs to be called once
     */
    function load_baseconfig()
    {
        global $_DB, $_TABLES, $_CONF;

        include $this->dbconfig_file;
        $this->config_array['Core'] =& $_CONF;

        include_once $_CONF['path_system'] . 'lib-database.php';

        // for backward compatibility
        $_CONF['ostype'] = PHP_OS;
    }

    /**
     * This function initializes the configuration array (i.e. $_CONF) and
     * will return a reference to the newly created array. The class keeps
     * track of this reference, and the set function will mutate it.
     *
     * @return array(string => mixed)      This is a reference to the
     *                                     config array
     */
    function &initConfig()
    {
        global $_TABLES, $_CONF, $_SYSTEM;

        // Reads from a cache file if there is one
        if ( isset($_SYSTEM['no_cache_config']) && !$_SYSTEM['no_cache_config'] ) {
            if ( function_exists('COM_isWritable') ) {
                if ( COM_isWritable($_CONF['path'].'data/layout_cache/'.CONFIG_CACHE_FILE_NAME)) {
                    if ($this->_readFromCache()) {
                        $this->_post_configuration();
                        return $this->config_array;
                    }
                }
            }
        }

        $false_str = serialize(false);

        $sql = "SELECT name, value, group_name FROM {$_TABLES['conf_values']} WHERE (type <> 'subgroup') AND (type <> 'fieldset')";
        $result = DB_query($sql);
        while ($row = DB_fetchArray($result)) {
            if ($row[1] !== 'unset') {
                if (!array_key_exists($row[2], $this->config_array) ||
                    !array_key_exists($row[0], $this->config_array[$row[2]])) {
                    $row[1] = preg_replace_callback ( '!s:(\d+):"(.*?)";!',
                        function($match) {
                            return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
                        },$row[1] );
                    $value = @unserialize($row[1]);
                    if (($value === false) && ($row[1] != $false_str)) {
                        if (function_exists('COM_errorLog')) {
                            COM_errorLog("Unable to unserialize {$row[1]} for {$row[2]}:{$row[0]}");
                        }
                    } else {
                        $this->config_array[$row[2]][$row[0]] = $value;
                    }
                }
            }
        }
        $this->_writeIntoCache();
        $this->_post_configuration();

        return $this->config_array;
    }

    function &get_config($group)
    {
        $retval = false;

        if (array_key_exists($group, $this->config_array)) {

            return $this->config_array[$group];
        }

        return $retval;
    }

    function group_exists($group)
    {
        return array_key_exists($group, $this->config_array);
    }

    /**
     * This function sets a configuration variable to a value in the database
     * and in the current array. If the variable does not already exist,
     * nothing will happen.
     *
     * @param string name        Name of the config parameter to set
     * @param mixed value        The value to set the config parameter to
     */
    function set($name, $value, $group='Core')
    {
        global $_TABLES, $_VARS;

        if ($group == 'Core') {
            $fn = 'configmanager_' . $name . '_validate';
        } else {
            $fn = 'plugin_configmanager_' . $name . '_' . $group . '_validate';
        }
        if (function_exists($fn)) {
            $value = $fn($value);
        }

        if ( $name == 'mail_smtp_password') {
            if ( function_exists('COM_encrypt')) {
                $value = COM_encrypt($value,$_VARS['guid']);
            } elseif ( function_exists('INST_encrypt')) {
                $value = INST_encrypt($value,$_VARS['guid']);
            }
        }

        if ( in_array($name,$this->consumer_keys) ) {
            $svalue = strval($value);
            $escaped_val = DB_escapeString(serialize($svalue));
        } else {
            $escaped_val = DB_escapeString(serialize($value));
        }

        $escaped_name = DB_escapeString($name);
        $escaped_grp = DB_escapeString($group);
        $sql = "UPDATE {$_TABLES['conf_values']} " .
               "SET value = '{$escaped_val}' WHERE " .
               "name = '{$escaped_name}' AND group_name = '{$escaped_grp}'";
        $this->_DB_escapedQuery($sql);
        if ( $name != 'theme' )  {
            $this->config_array[$group][$name] = $value;
            $this->_post_configuration();
            $this->_writeIntoCache();
            $this->_purgeCache();
        } else {
            $this->_purgeCache();
        }
    }

    /**
     * This function sets the default of a configuration variable to a value in
     * the database but not in the current array.
     * If the variable does not already exist, nothing will happen.
     *
     * @param string name        Name of the config parameter to set
     * @param mixed  value       The value to set the config parameter to
     * @param string group       Config group name ('Core' or plugin name)
     */
    function set_default($name, $value, $group = 'Core')
    {
        global $_TABLES;

        $escaped_val = DB_escapeString(serialize($value));
        $escaped_name = DB_escapeString($name);
        $escaped_grp = DB_escapeString($group);
        $sql = "UPDATE {$_TABLES['conf_values']} " .
               "SET default_value = '{$escaped_val}' WHERE " .
               "name = '{$escaped_name}' AND group_name = '{$escaped_grp}'";
        $this->_DB_escapedQuery($sql);
        $this->_writeIntoCache();
        $this->_purgeCache();
    }

    function restore_param($name, $group)
    {
        global $_TABLES;

        $escaped_name = DB_escapeString($name);
        $escaped_grp = DB_escapeString($group);

        $result = DB_query("SELECT value, default_value FROM {$_TABLES['conf_values']} WHERE name = '{$escaped_name}' AND group_name = '{$escaped_grp}'");
        list($value, $default_value) = DB_fetchArray($result);

        $sql = "UPDATE {$_TABLES['conf_values']} ";
        if ($value == 'unset') {
            $default_value = DB_escapeString($default_value);
            $sql .= "SET value = '{$default_value}', default_value = 'unset:{$default_value}'";
        } else {
            $sql .= "SET value = default_value";
        }
        $sql .= " WHERE name = '{$escaped_name}' AND group_name = '{$escaped_grp}'";
        $this->_DB_escapedQuery($sql);
        $this->_writeIntoCache();
        $this->_purgeCache();
    }

    function unset_param($name, $group)
    {
        global $_TABLES;

        $escaped_name = DB_escapeString($name);
        $escaped_grp = DB_escapeString($group);
        $default_value = DB_getItem($_TABLES['conf_values'], 'default_value',
                "name = '{$escaped_name}' AND group_name = '{$escaped_grp}'");
        $sql = "UPDATE {$_TABLES['conf_values']} SET value = 'unset'";
        if (substr($default_value, 0, 6) == 'unset:') {
            $default_value = DB_escapeString(substr($default_value, 6));
            $sql .= ", default_value = '{$default_value}'";
        }
        $sql .= " WHERE name = '{$escaped_name}' AND group_name = '{$escaped_grp}'";
        $this->_DB_escapedQuery($sql);
        $this->_writeIntoCache();
        $this->_purgeCache();
    }

    /**
     * Adds a configuration variable to the config object
     *
     * @param string $param_name        name of the parameter to add
     * @param mixed  $default_value     the default value of the parameter
     *                                  (also will be the initial value)
     * @param string $display_name      name that will be displayed on the
     *                                  user interface
     * @param string $type              the type of the configuration variable
     *
     *    If the configuration variable is an array, prefix this string with
     *    '@' if the administrator should NOT be able to add or remove keys
     *    '*' if the administrator should be able to add named keys
     *    '%' if the administrator should be able to add numbered keys
     *    These symbols can be repeated like such: @@text if the configuration
     *    variable is an array of arrays of text.
     *    The base variable types are:
     *    'text'    textbox displayed     string  value stored
     *    'select'  selectbox displayed   string  value stored
     *    'hidden'  no display            string  value stored
     *
     * @param string $subgroup          subgroup of the variable
     *                                  (the second row of tabs on the user interface)
     * @param string $fieldset          the fieldset to display the variable under
     * @param array  $selection_array   possible selections for the 'select' type
     *                                  this MUST be passed if you use the 'select'
     *                                  type
     * @param int    $sort              sort rank on the user interface (ascending)
     *
     * @param boolean $set              whether or not this parameter is set
     */
    function add($param_name, $default_value, $type, $subgroup, $fieldset,
         $selection_array=null, $sort=0, $set=true, $group='Core')
    {
        global $_TABLES;

        $Qargs = array($param_name,
                       $set ? serialize($default_value) : 'unset',
                       $type,
                       $subgroup,
                       $group,
                       ($selection_array === null ?
                        -1 : $selection_array),
                       $sort,
                       $fieldset,
                       serialize($default_value));
        $Qargs = array_map('DB_escapeString', $Qargs);

        $sql = "DELETE FROM {$_TABLES['conf_values']} WHERE name = '{$Qargs[0]}' AND group_name = '{$Qargs[4]}' AND subgroup={$Qargs[3]}";
        $this->_DB_escapedQuery($sql);

        $sql = "INSERT INTO {$_TABLES['conf_values']} (name, value, type, " .
            "subgroup, group_name, selectionArray, sort_order,".
            " fieldset, default_value) VALUES ("
            ."'{$Qargs[0]}',"
            ."'{$Qargs[1]}',"
            ."'{$Qargs[2]}',"
            ."{$Qargs[3]},"
            ."'{$Qargs[4]}',"
            ."{$Qargs[5]},"
            ."{$Qargs[6]},"
            ."{$Qargs[7]},"
            ."'{$Qargs[8]}')";

        $this->_DB_escapedQuery($sql);

        $this->config_array[$group][$param_name] = $default_value;
        $this->_writeIntoCache();
        $this->_purgeCache();
    }

    function sync($param_name, $default_value, $type, $subgroup, $fieldset,
         $selection_array=null, $sort=0, $set=true, $group='Core')
    {
        global $_TABLES;

        $Qargs = array($param_name,                                     // 0
                       $set ? serialize($default_value) : 'unset',      // 1
                       $type,                                           // 2
                       $subgroup,                                       // 3
                       $group,                                          // 4
                       ($selection_array === null ?                     // 5
                        -1 : $selection_array),
                       $sort,                                           // 6
                       $fieldset,                                       // 7
                       serialize($default_value));                      // 8
        $Qargs = array_map('DB_escapeString', $Qargs);

        $sql = "UPDATE {$_TABLES['conf_values']} SET
            subgroup={$Qargs[3]},
            sort_order={$Qargs[6]},
            fieldset={$Qargs[7]},
            default_value='{$Qargs[8]}',
            type='{$Qargs[2]}',
            selectionArray={$Qargs[5]}
            WHERE group_name='{$Qargs[4]}' AND name='{$Qargs[0]}'";
        $this->_DB_escapedQuery($sql,1);
    }


    /**
     * Permanently deletes a parameter
     * @param string  $param_name   This is the name of the parameter to delete
     */
    function del($param_name, $group)
    {
        DB_delete($GLOBALS['_TABLES']['conf_values'],
                  array('name', 'group_name'),
                  array(DB_escapeString($param_name), DB_escapeString($group)));
        unset($this->config_array[$group][$param_name]);
        $this->_writeIntoCache();
        $this->_purgeCache();
    }

    /**
     * Permanently deletes a group of parameters
     * @param string  $group   This is the name of the group to delete
     */
    function delGroup($group)
    {
        global $_CONF, $_TABLES;

        if ( $group == 'Core' ) {
            return;
        }
        $result = DB_query("SELECT * FROM {$GLOBALS['_TABLES']['conf_values']} WHERE group_name='".DB_escapeString($group)."'");
        while ( $C = DB_fetchArray($result) ) {
            $param_name = $C['name'];
            DB_delete($GLOBALS['_TABLES']['conf_values'],
                      array('name', 'group_name'),
                      array(DB_escapeString($param_name), DB_escapeString($group)));
            unset($this->config_array[$group][$param_name]);
        }
        $this->_purgeCache();
    }

    /**
     * Gets extended (GUI related) information from the database
     * @param string subgroup            filters by subgroup
     * @return array(string => string => array(string => mixed))
     *    Array keys are fieldset => parameter named => information array
     */
    function _get_extended($subgroup, $group)
    {
        global $_TABLES, $LANG_confignames, $LANG_configselects;

        $q_string = "SELECT name, type, selectionArray, "
            . "fieldset, value, default_value FROM {$_TABLES['conf_values']}" .
            " WHERE group_name='{$group}' AND subgroup='{$subgroup}' " .
            " AND (type <> 'fieldset' AND type <> 'subgroup') " .
            " ORDER BY fieldset,sort_order ASC";
        $Qresult = DB_query($q_string);
        $res = array();
        if (!array_key_exists($group, $LANG_configselects)) {
            $LANG_configselects[$group] = array();
        }
        if (!array_key_exists($group, $LANG_confignames)) {
            $LANG_confignames[$group] = array();
        }
        while ($row = DB_fetchArray($Qresult)) {
            $cur = $row;
            if (substr($cur[5], 0, 6) == 'unset:') {
                $cur[5] = true;
            } else {
                $cur[5] = false;
            }
            $res[$cur[3]][$cur[0]] =
                array('display_name' =>
                      (array_key_exists($cur[0], $LANG_confignames[$group]) ?
                       $LANG_confignames[$group][$cur[0]]
                       : $cur[0]),
                      'type' =>
                      (($cur[4] == 'unset') ?
                       'unset' : $cur[1]),
                      'selectionArray' =>
                      (($cur[2] != -1) ?
                       //isset($LANG_configselects[$group][$cur[2]]) : null),
                       $LANG_configselects[$group][$cur[2]] : null),
                      'value' =>
                      (($cur[4] == 'unset') ?
                       'unset' : @unserialize($cur[4])),
                      'reset' => $cur[5]);
        }

        return $res;
    }

    // Changes any config settings that depend on other configuration settings.
    function _post_configuration()
    {
        global $_USER;

        if (empty($_USER['theme'])) {
            if (! empty($this->config_array['Core']['theme'])) {
                $theme = $this->config_array['Core']['theme'];
            }
        } else {
            $theme = $_USER['theme'];
        }
        if (! empty($theme)) {
            if (! empty($this->config_array['Core']['path_themes'])) {
                $this->config_array['Core']['path_layout'] = $this->config_array['Core']['path_themes'] . $theme . '/';
            }
            if (! empty($this->config_array['Core']['site_url'])) {
                $this->config_array['Core']['layout_url'] = $this->config_array['Core']['site_url'] . '/layout/' . $theme;
            }
        }

        $methods = array('standard', '3rdparty', 'oauth');
        $methods_disabled = 0;
        foreach ($methods as $m) {
            if (isset($this->config_array['Core']['user_login_method'][$m]) &&
                    !$this->config_array['Core']['user_login_method'][$m]) {
                $methods_disabled++;
            }
        }
        if ($methods_disabled == count($methods)) {
            // just to make sure people don't lock themselves out of their site
            $this->config_array['Core']['user_login_method']['standard'] = true;

            // TBD: ensure that we have a Root user able to log in with the
            //      enabled login method(s)
        }
    }

    function _get_groups()
    {
        global $_TABLES, $_PLUGIN_INFO;

        $groups = array_keys($this->config_array);
        $num_groups = count($groups);
        for ($i = 0; $i < $num_groups; $i++) {
            $g = $groups[$i];
            if ($g != 'Core') {
                if ( isset($_PLUGIN_INFO) && count($_PLUGIN_INFO) > 0 ) {
                    if ( isset($_PLUGIN_INFO[$g]) && $_PLUGIN_INFO[$g]['pi_enabled'] == 1 ) {
                        $enabled = 1;
                    } else {
                        $enabled = 0;
                    }
                } else {
                    $enabled = (int) DB_getItem($_TABLES['plugins'], 'pi_enabled',"pi_name = '$g'");
                }
                if ( !isset($enabled) || $enabled != 1 ) {
                    unset($groups[$i]);
                }
            }
        }

        return $groups;
    }

    function _get_sgroups($group)
    {
        global $_TABLES;

        $q_string = "SELECT name,subgroup FROM {$_TABLES['conf_values']} WHERE "
                  . "type = 'subgroup' AND group_name = '$group' "
                  . "ORDER BY subgroup";
        $retval = array();
        $res = DB_query($q_string);
        while ($row = DB_fetchArray($res)) {
            $retval[$row['name']] = $row['subgroup'];
        }

        return $retval;
    }

    /**
     * This function is responsible for creating the configuration GUI
     *
     * @param string sg        This is the subgroup name to load the gui for.
     *                        If nothing is passed, it will display the first
     *                         (alpha) subgroup
     *
     * @param array(string=>boolean) change_result
     *                        This is an array of what changes were made to the
     *                        configuration - if it is passed, it will display
     *                        the "Changes" message box.
     */
    function get_ui($grp, $sg='0', $activeTab = '', $change_result=null)
    {
        global $_CONF, $LANG_CONFIG, $LANG_configsubgroups, $LANG_configsections;

        if(!array_key_exists($grp, $LANG_configsubgroups)) {
            $LANG_configsubgroups[$grp] = array();
        }
        if (!SEC_inGroup('Root')) {
            return config::_UI_perm_denied();
        }

        include_once $_CONF['path_system'] . 'demo-mode.php';

        if (!isset($sg) OR empty($sg)) {
            $sg = '0';
        }
        $t = new Template($_CONF['path_layout'] . 'admin/config');
        $t->set_file(array('main' => 'configuration.thtml',
                           'menugroup' => 'menu_element.thtml'));

        $token = SEC_createToken();
        $t->set_var('sec_token_name', CSRF_TOKEN);
        $t->set_var('sec_token',$token);
        $t->set_var('lang_save_changes', $LANG_CONFIG['save_changes']);
        $t->set_var('lang_reset_form', $LANG_CONFIG['reset_form']);
        $t->set_var('lang_changes_made', $LANG_CONFIG['changes_made']);
        $t->set_var('lang_search', $LANG_CONFIG['search']);

        if ( isset($_POST['fieldname']) && $_POST['fieldname'] != '') {
            $fieldname = COM_applyFilter($_POST['fieldname']);
            $t->set_var('highlight',$fieldname);
        } else {
            $t->set_var('highlight','');
        }

// depreciated
        $t->set_var('gltoken_name', CSRF_TOKEN);
        $t->set_var('gltoken', $token);
// end decpreciated
        $t->set_var('open_group', $grp);

        $groups = $this->_get_groups();
        $outerloopcntr = 1;
        if (count($groups) > 0) {
            $t->set_block('menugroup', 'subgroup-selector', 'subgroups');
            if ( is_array($groups) ) {
                foreach ($groups as $group) {
                    $t->set_var("select_id", ($group === $grp ? 'id="current"' : ''));
                    $t->set_var("group_select_value", $group);
                    $t->set_var("group_display", ucwords($group));
                    $subgroups = $this->_get_sgroups($group);
                    $innerloopcntr = 1;
                    foreach ($subgroups as $sgname => $sgroup) {
                        if ($grp == $group AND $sg == $sgroup) {
                            $t->set_var('group_active_name', ucwords($group));
                            $t->set_var('group_name',$LANG_configsections[$group]['label']);
                            if (isset($LANG_configsubgroups[$group][$sgname])) {
                                $t->set_var('subgroup_active_name',
                                        $LANG_configsubgroups[$group][$sgname]);
                            } else if (isset($LANG_configsubgroups[$group][$sgroup])) {
                                $t->set_var('subgroup_active_name',
                                        $LANG_configsubgroups[$group][$sgroup]);
                            } else {
                                $t->set_var('subgroup_active_name', $sgname);
                            }
                            $t->set_var('select_id', 'id="current"');
                        } else {
                            $t->set_var('select_id', '');
                        }
                        $t->set_var('subgroup_name', $sgroup);
                        if (isset($LANG_configsubgroups[$group][$sgname])) {
                            $t->set_var('subgroup_display_name',
                                        $LANG_configsubgroups[$group][$sgname]);
                        } else {
                            $t->set_var('subgroup_display_name', $sgname);
                        }
                        if ($innerloopcntr == 1) {
                            $t->parse('subgroups', "subgroup-selector");
                        } else {
                            $t->parse('subgroups', "subgroup-selector", true);
                        }
                        $innerloopcntr++;
                    }
                    $t->set_var('cntr',$outerloopcntr);
                    $t->parse("menu_elements", "menugroup", true);
                    $outerloopcntr++;
                }
            }
        } else {
            $t->set_var('hide_groupselection','none');
        }

        $t->set_var('open_sg', $sg);

        $t->set_block('main','fieldset','sg_contents');
        $t->set_block('fieldset', 'notes', 'fs_notes');
        $t->set_block('main','tabs','sg_tabs');
        $ext_info = $this->_get_extended($sg, $grp);

        $docUrl = $this->_getConfigHelpDocument($grp,'');

        if ( $docUrl != '' ) {
            $t->set_var('confighelpurl',$docUrl);
        } else {
            $t->unset_var('confighelpurl');
        }
        $tabCounter = 0;
        if ( is_array($ext_info) ) {
            foreach ($ext_info as $fset => $params) {
                $fs_contents = '';
                foreach ($params as $name => $e) {
                    if ( defined('DEMO_MODE') ) {
                        if ( in_array($name,$demoConfigVars)) {
                            continue;
                        }
                    }

                    $fs_contents .=
                        $this->_UI_get_conf_element($grp, $name,
                                                   $e['display_name'],
                                                   $e['type'],
                                                   $e['value'],
                                                   $e['selectionArray'], false,
                                                   $e['reset']);
                }
                $rc = $this->_UI_get_fs($grp, $sg, $activeTab, $fs_contents, $fset, $t);
                if ( $rc ) {
                    $t->set_var('active_tab_index',$tabCounter);
                }
                $tabCounter++;
            }
        }

        $display  = COM_siteHeader('none', $LANG_CONFIG['title']);
        $t->set_var('config_menu',$this->_UI_configmanager_menu($grp,$sg));
        if ($change_result != null AND $change_result !== array()) {
            $t->set_var('change_block',$this->_UI_get_change_block($change_result));
        } else {
            $t->set_var('show_changeblock','none');
        }

        $t->set_var('autocomplete_data',$this->_get_autocompletedata());


        $display .= $t->finish($t->parse("OUTPUT", "main"));
        $display .= COM_siteFooter(false);

        return $display;
    }

    function _UI_get_change_block($changes)
    {
        global $LANG_confignames;
        if ($changes != null AND $changes !== array()) {
            $display = '<ul style="margin-top:5px;">';
            if ( is_array($changes) ) {
                foreach ($changes as $group => $item ) {
                    if ( is_array($item) ) {
                        foreach ($item as $param_name => $value ) {
                            if ( isset($LANG_confignames[$group][$param_name]) ) {
                                $display .= '<li>' . $LANG_confignames[$group][$param_name] . '</li>';
                            } else {
                                $display .= '<li>' . $param_name . '</li>';
                            }
                        }
                    }
                }
                $display .= '</ul>';
            }
            return $display;
        }
    }

    function _UI_get_fs($group, $sg, $activetab,$contents, $fs_id, &$t)
    {
        global $_TABLES, $LANG_fs;

        if (!array_key_exists($group, $LANG_fs)) {
            $LANG_fs[$group] = array();
        }
        $t->set_var('fs_contents', $contents);
        $fs_index = DB_getItem($_TABLES['conf_values'], 'name',
                        "type = 'fieldset' AND fieldset = $fs_id AND group_name = '$group' AND subgroup=".$sg);
        if (empty($fs_index) && isset($LANG_fs[$group][$fs_id])) {
            $t->set_var('fs_display', $LANG_fs[$group][$fs_id]);
            $t->set_var('tab',$LANG_fs[$group][$fs_id]);
        } else if (isset($LANG_fs[$group][$fs_index])) {
            $t->set_var('fs_display', $LANG_fs[$group][$fs_index]);
            $t->set_var('tab', $LANG_fs[$group][$fs_index]);
        } else {
            $t->set_var('fs_display', $fs_index);
            $t->set_var('tab',$fs_index);
        }
        $t->set_var('index',$fs_index);

        if ( 'sg_'.$fs_index == $activetab ) {
            $class = 'uk-active';
        } else {
            $class = '';
        }
        $t->set_var('class',$class);
        $t->set_var('fs_notes', '');
        $t->parse('sg_contents', 'fieldset', true);
        $t->parse('sg_tabs', 'tabs',true);
        if ( $class != "" ) return true;
        return false;
    }

    function _UI_perm_denied()
    {
        global $_USER, $MESSAGE;

        $display = COM_siteHeader('menu', $MESSAGE[30])
            . COM_startBlock($MESSAGE[30], '',
                             COM_getBlockTemplate ('_msg_block', 'header'))
            . $MESSAGE[96]
            . COM_endBlock(COM_getBlockTemplate('_msg_block', 'footer'))
            . COM_siteFooter();
        COM_accessLog("User {$_USER['username']} tried to illegally access the config administration screen.");

        return $display;
    }

    function _UI_get_conf_element($group, $name, $display_name, $type, $val,
                                  $selectionArray = null , $deletable = false,
                                  $allow_reset = false)
    {
        global $_CONF, $LANG_CONFIG;

        $t = new Template($GLOBALS['_CONF']['path_layout'] . 'admin/config');
        $t -> set_file('element', 'config_element.thtml');

        $blocks = array('delete-button', 'text-element','passwd-element',
                        'placeholder-element','select-element', 'list-element',
                        'unset-param','keyed-add-button', 'unkeyed-add-button','text-area','break');

        if ( is_array($blocks) ) {
            foreach ($blocks as $block) {
                $t->set_block('element', $block);
            }
        }

        $t->set_var('lang_restore', $LANG_CONFIG['restore']);
        $t->set_var('lang_enable', $LANG_CONFIG['enable']);
        $t->set_var('lang_add_element', $LANG_CONFIG['add_element']);
        $t->set_var('name', $name);
        $t->set_var('display_name', $display_name);
        if (!is_array($val)) {
            if (is_float($val)) {
                /**
                * @todo FIXME: for Locales where the comma is the decimal
                *              separator, patch output to a decimal point
                *              to prevent it being cut off by COM_applyFilter
                */
                $t->set_var('value', str_replace(',', '.', $val));
            } else {
                $t->set_var('value', htmlspecialchars($val));
            }
        }
        if ($deletable) {
            $t->set_var('delete', $t->parse('output', 'delete-button'));
        } else {
            if ($allow_reset) {
                $t->set_var('unset_link',
                        "(<a href='#' onclick='unset(\"{$name}\");return false;' title='"
                        . $LANG_CONFIG['disable'] . "'>X</a>)");
            }
            if (($a = strrchr($name, '[')) !== FALSE) {
                $o = str_replace(array('[', ']'), array('_', ''), $name);
            } else {
                $o = $name;
            }
            $helpUrl = $this->_get_ConfigHelp($group, $o);
            if (! empty($helpUrl)) {
                $t->set_var('doc_link', $helpUrl);
            } else {
                $t->set_var('doc_link', '');
            }

            $docUrl = $this->_getConfigHelpDocument($group, $o);
            if ( $docUrl != '' ) {
                $t->set_var('cfg_item',$o);
            } else {
                $t->unset_var('cfg_item');
            }
        }
        if ($type == "unset") {
            return $t->finish($t->parse('output', 'unset-param'));
        } elseif ($type == "fset") {
            return $t->finish($t->parse('output', 'fset'));
        } elseif ($type == "text") {
            return $t->finish($t->parse('output', 'text-element'));
        } elseif ($type == "textarea") {
            return $t->finish($t->parse('output', 'text-area'));
        } elseif ($type == "passwd") {
            return $t->finish($t->parse('output', 'passwd-element'));
        } elseif ($type == "placeholder") {
            return $t->finish($t->parse('output', 'placeholder-element'));
        } elseif ($type == 'select') {
            // if $name is like "blah[0]", separate name and index
            $n = explode('[', $name);
            $name = $n[0];
            $index = null;
            if (count($n) == 2) {
                $i = explode(']', $n[1]);
                $index = $i[0];
            }
            $type_name = $type . '_' . $name;
            if ($group == 'Core') {
                $fn = 'configmanager_' . $type_name . '_helper';
            } else {
                $fn = 'plugin_configmanager_' . $type_name . '_' . $group;
            }
            if (function_exists($fn)) {
                if ($index === null) {
                    $selectionArray = $fn();
                } else {
                    $selectionArray = $fn($index);
                }
            } else if (is_array($selectionArray)) {
                // leave sorting to the function otherwise
                uksort($selectionArray, 'strcasecmp');
            }
            if (! is_array($selectionArray)) {
                return $t->finish($t->parse('output', 'text-element'));
            }

            $t->set_block('select-element', 'select-options', 'myoptions');
            if ( is_array($selectionArray) ) {
                foreach ($selectionArray as $sName => $sVal) {
                    if (is_bool($sVal)) {
                        $t->set_var('opt_value', $sVal ? 'b:1' : 'b:0');
                    } else {
                        $t->set_var('opt_value', $sVal);
                    }
                    $t->set_var('opt_name', $sName);
                    $t->set_var('selected', ($val == $sVal ? 'selected="selected"' : ''));
                    $t->parse('myoptions', 'select-options', true);
                }
    	        if ($index == 'placeholder') {
                    $t->set_var('hide_row', ' style="display:none;"');
                }
            }
            return $t->parse('output', 'select-element');
        } elseif (strpos($type, "@") === 0) {
            $result = "";
            if ( is_array($val) ) {
                foreach ($val as $valkey => $valval) {
                    $result .= config::_UI_get_conf_element($group,
                                    $name . '[' . $valkey . ']',
                                    $display_name . '[' . $valkey . ']',
                                    substr($type, 1), $valval, $selectionArray,
                                    false);
                }
            }
            return $result;
        } elseif (strpos($type, "*") === 0 || strpos($type, "%") === 0) {
            $t->set_var('arr_name', $name);
            $t->set_var('array_type', $type);
            $button = $t->parse('output', (strpos($type, "*") === 0 ?
                                           'keyed-add-button' :
                                           'unkeyed-add-button'));
            $t->set_var('my_add_element_button', $button);
            $result = "";
            if ( is_array($val) ) {
                if ($type == '%select') {

                    $result .= config::_UI_get_conf_element($group,
                                    $name . '[placeholder]', 'placeholder',
                                    substr($type, 1), 'placeholder', $selectionArray,
                                    true);

                }
                foreach ($val as $valkey => $valval) {
                    $result .= config::_UI_get_conf_element($group,
                                    $name . '[' . $valkey . ']', $valkey,
                                    substr($type, 1), $valval, $selectionArray,
                                    true);
                }
            }
            $t->set_var('my_elements', $result);
            return $t->parse('output', 'list-element');
        }
    }

    /**
     * This function takes $_POST input and evaluates it
     *
     * param array(string=>mixed)       $change_array this is the $_POST array
     * return array(string=>boolean)    this is the change_array
     */
    function updateConfig($change_array, $group)
    {
        global $_TABLES;

        if (!SEC_inGroup('Root')) {
            return null;
        }

        if ($group == 'Core') {
            /**
             * $_CONF['language'] are overwritten with
             * the user's preferences in lib-common.php. Re-read values from
             * the database so that we're comparing the correct values below.
             */
            $value = DB_getItem($_TABLES['conf_values'], 'value',
                                "group_name='Core' AND name='language'");
            $this->config_array['Core']['language'] = unserialize($value);

            /**
             * Same with $_CONF['cookiedomain'], which is overwritten in
             * in lib-sessions.php (if empty).
             */
            $value = DB_getItem($_TABLES['conf_values'], 'value',
                                "group_name='Core' AND name='cookiedomain'");
            $this->config_array['Core']['cookiedomain'] = @unserialize($value);
        }

        $success_array = array();
        if ( is_array($this->config_array[$group]) ) {
            foreach ($this->config_array[$group] as $param_name => $param_value) {
                if (array_key_exists($param_name, $change_array)) {
                    if ( !in_array($param_name,$this->consumer_keys) ) {
                        $change_array[$param_name] =
                            $this->_validate_input($change_array[$param_name]);
                    }
                    if ($change_array[$param_name] != $param_value) {
                        $this->set($param_name, $change_array[$param_name], $group);
                        $success_array[$group][$param_name] = true;
                    }
                }
            }
        }

        $this->_purgeCache();

        return $success_array;
    }

    function _validate_input(&$input_val)
    {
        if (is_array($input_val)) {
            $r = array();
            $is_num = true;
            $max_key = -1;
            if ( is_array($input_val) ) {
                foreach ($input_val as $key => $val) {
                    if ($key !== 'placeholder') {
                        $r[$key] = $this->_validate_input($val);
                        if (is_numeric($key)) {
                            if ($key > $max_key) {
                                $max_key = $key;
                            }
                        } else {
                            $is_num = false;
                        }
                    }
                }
                if ($is_num && ($max_key >= 0) && ($max_key + 1 != count($r))) {
                    // re-number keys
                    $r2 = array();
                    foreach ($r as $val) {
                        $r2[] = $val;
                    }
                    $r = $r2;
                }
            }
        } else {
            $r = $input_val;
            if ($r == 'b:0' OR $r == 'b:1') {
                $r = ($r == 'b:1');
            }
            if (is_numeric($r)) {
                $r = $r + 0;
            }
        }

        return $r;
    }


    function _UI_configmanager_menu($conf_group,$sg=0)
    {
        global $_CONF, $LANG_ADMIN, $LANG_CONFIG,
               $LANG_configsections, $LANG_configsubgroups;

        $retval = '';

        $retval .= COM_startBlock($LANG_CONFIG['sections'], '',
                        COM_getBlockTemplate('configmanager_block', 'header'));
        $link_array = array();

        $groups = $this->_get_groups();
        if (is_array($groups) && count($groups) > 0) {
            foreach ($groups as $group) {
                if (empty($LANG_configsections[$group]['label'])) {
                    $group_display = ucwords($group);
                } else {
                    $group_display = $LANG_configsections[$group]['label'];
                }
                // Create a menu item for each config group - disable the link for the current selected one
                if ($conf_group == $group) {
                    $link = "<div>$group_display</div>";
                } else {
                    $link = "<div><a href=\"#\" onclick='open_group(\"$group\");return false;'>$group_display</a></div>";
                }

                if ($group == 'Core') {
                    $retval .= $link;
                } else {
                    $link_array[$group_display] = $link;
                }
            }
        }

        uksort($link_array, 'strcasecmp');
        if ( is_array($link_array) ) {
            foreach ($link_array as $link) {
                $retval .= $link;
            }
        }

        $retval .= '<div><a href="' . $_CONF['site_admin_url'] . '">'
                . $LANG_ADMIN['admin_home'] . '</a></div>';
        $retval .= COM_endBlock(COM_getBlockTemplate('configmanager_block',
                                                     'footer'));


        /* Now display the sub-group menu for the selected config group */
        if (empty($LANG_configsections[$conf_group]['title'])) {
            $subgroup_title = ucwords($conf_group);
        } else {
            $subgroup_title = $LANG_configsections[$conf_group]['title'];
        }
        $retval .= COM_startBlock($subgroup_title, '',
                    COM_getBlockTemplate('configmanager_subblock', 'header'));

        $sgroups = $this->_get_sgroups($conf_group);
        if (is_array($sgroups) && count($sgroups) > 0) {
            $i = 0;
            foreach ($sgroups as $sgname => $sgroup) {
                if (isset($LANG_configsubgroups[$conf_group][$sgname])) {
                    $group_display = $LANG_configsubgroups[$conf_group][$sgname];
                } else if (isset($LANG_configsubgroups[$conf_group][$sgroup])) {
                    $group_display = $LANG_configsubgroups[$conf_group][$sgroup];
                } else {
                    $group_display = $sgname;
                }
                // Create a menu item for each sub config group - disable the link for the current selected one
                if ($i == $sg) {
                    $retval .= "<div>$group_display</div>";
                } else {
                    $retval .= "<div><a href=\"#\" onclick='open_subgroup(\"$conf_group\",\"$sgroup\");return false;'>$group_display</a></div>";
                }
                $i++;
            }
        }
        $retval .= COM_endBlock(COM_getBlockTemplate('configmanager_block',
                                                     'footer'));
        return $retval;
    }

    /**
     * Helper function: Fix escaped SQL requests for MS SQL, if necessary
     *
     */
    function _DB_escapedQuery($sql,$noerror=0)
    {
        global $_DB, $_DB_dbms;

        if ($_DB_dbms == 'mssql') {
            $sql = str_replace("\\'", "''", $sql);
            $sql = str_replace('\\"', '"', $sql);
            $_DB->dbQuery($sql, 0, 1);
        } else {
            DB_query($sql,$noerror);
        }
    }

    function _getConfigHelpDocument($group, $option)
    {
        global $_CONF;

        static $coreUrl;

        $retval = '';

        $descUrl = '';

        $doclang = COM_getLanguageName();

        if ($group == 'Core') {
            if (isset($coreUrl)) {
                $descUrl = $coreUrl;
            } elseif (!empty($GLOBALS['_CONF']['site_url']) &&
                    !empty($GLOBALS['_CONF']['path_html'])) {
                $baseUrl = $GLOBALS['_CONF']['site_url'];
                $cfg = 'docs/' . $doclang . '/config.html';
                if (@file_exists($GLOBALS['_CONF']['path_html'] . $cfg)) {
                    $descUrl = $baseUrl . '/' . $cfg;
                } else {
                    $descUrl = $baseUrl . '/docs/english/config.html';
                }
                $coreUrl = $descUrl;
            } else {
                $descUrl = 'http://www.glfusion.org/docs/english/config.html';
            }
            $retval = $descUrl;
        } else {
            list ($doc_url, $popuptype) = PLG_getConfigElementHelp($group, $option, $doclang );
            if ( $popuptype == 0 ) $retval = $doc_url;
        }
        return $retval;
    }

    /**
    * Helper function: Get the URL to the help section for a config option
    *
    * @param    string  $group      'Core' or plugin name
    * @param    string  $option     name of the config option
    * @return   string              full URL to help or empty string
    *
    */
    function _get_ConfigHelp($group, $option)
    {
        global $_CONF;

        static $coreUrl;

        $retval = '';

        $descUrl = '';

        $doclang = COM_getLanguageName();

        if ($group == 'Core') {
            if (isset($coreUrl)) {
                $descUrl = $coreUrl;
            } elseif (!empty($GLOBALS['_CONF']['site_url']) &&
                    !empty($GLOBALS['_CONF']['path_html'])) {
                $baseUrl = $GLOBALS['_CONF']['site_url'];
                $cfg = 'docs/' . $doclang . '/config.html';
                if (@file_exists($GLOBALS['_CONF']['path_html'] . $cfg)) {
                    $descUrl = $baseUrl . '/' . $cfg;
                } else {
                    $descUrl = $baseUrl . '/docs/english/config.html';
                }
                $coreUrl = $descUrl;
            } else {
                $descUrl = 'http://www.glfusion.org/docs/english/config.html';
            }
            if (! empty($descUrl)) {
                $helpoption = str_replace("[","_",$option);
                $helpoption = str_replace("]","",$helpoption);
                $helpUrl = $descUrl . '#desc_' . $helpoption;
            }
            $retval = '<a href="#" onclick="popupWindow(\'' . $helpUrl . '\', \'Help\', 640, 480, 1)" class="toolbar"><img src="' . $_CONF['layout_url'] . '/images/button_help.png" alt=""></a>';
        } else {
            list ($doc_url, $popuptype) = PLG_getConfigElementHelp($group, $option, $doclang );
            if ( $doc_url != '' ) {
                if ( $popuptype == 2 ) {
                    $retval = '<a href="'.$doc_url.'" onclick="window.open(this.href);return false;" class="toolbar"><img src="' . $_CONF['layout_url'] . '/images/button_help.png" alt=""></a>';
                } else {
                    $retval = '<a href="#" onclick="popupWindow(\'' . $doc_url . '\', \'Help\', 640, 480, 1)" class="toolbar"><img src="' . $_CONF['layout_url'] . '/images/button_help.png" alt=""></a>';
                }
            } else if ( @file_exists($_CONF['path_html'] . 'docs/' . $doclang . '/'. $group . '.html') ) {
                $baseUrl = $GLOBALS['_CONF']['site_url'];
                $descUrl = $baseUrl . '/docs/' . $doclang . '/'. $group . '.html#desc_' . $option;
                $retval = '<a href="#" onclick="popupWindow(\'' . $descUrl . '\', \'Help\', 640, 480, 1)" class="toolbar"><img src="' . $_CONF['layout_url'] . '/images/button_help.png" alt=""></a>';
            } else {
                $retval = '';
            }
        }
        return $retval;
    }

    /**
    * Read from the cache file
    *
    * @return boolean true = found cache, false = otherwise
    */
    function _readFromCache()
    {
        global $_CONF;

        $cache_file = $_CONF['path'] . 'data/layout_cache/' . CONFIG_CACHE_FILE_NAME;
        clearstatcache();
        if (file_exists($cache_file)) {
            $s = file_get_contents($cache_file);
            if ($s !== false) {
//                $s = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $s );
                $s = preg_replace_callback ( '!s:(\d+):"(.*?)";!',
                    function($match) {
                    return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
                },$s );
                $this->config_array = @unserialize($s);
                return true;
            }
        }

        return false;
    }

    /**
    * Write into the cache file
    */
    function _writeIntoCache()
    {
        global $_CONF;

        $cache_file = $_CONF['path'] . 'data/layout_cache/' . CONFIG_CACHE_FILE_NAME;
        $s = serialize($this->config_array);
        $fh = @fopen($cache_file, 'wb');
        if ($fh !== false) {
            if (flock($fh, LOCK_EX)) {
                ftruncate($fh, 0);
                rewind($fh);
				fwrite($fh, $s);
                flock($fh, LOCK_UN);
            }
            fclose($fh);
        } else {
            if ( function_exists('COM_errorLog')) {
                COM_errorLog('config::_writeIntoCache: cannot write into cache file: ' . $cache_file);
            }
        }
    }

    /**
    * Purge the cache file
    */
    function _purgeCache()
    {
        global $_CONF;

        $cache_file = $_CONF['path'] . 'data/layout_cache/' . CONFIG_CACHE_FILE_NAME;
        if ( file_exists($cache_file)) {
            @unlink($cache_file);
        }
    }

    /**
     * This function builds the JavaScript array of configuration items
     * for the configuration search.
     */
    function _get_autocompletedata()
    {
        global $_PLUGINS, $_TABLES, $_CONF, $LANG_CONFIG,
               $LANG_fs, $LANG_configsubgroups, $LANG_configsections,$LANG_confignames;

        $listOfPlugins = implode(",",$_PLUGINS);
        $listOfPlugins = 'Core,' . $listOfPlugins;
        $itemArray = explode(",",$listOfPlugins);

        $confArray = array();

        foreach ($itemArray AS $item) {

            $label = '';
            $retval = '';
            $fieldset = '';
            $group = $item;

            $sql = "SELECT * FROM {$_TABLES['conf_values']} WHERE group_name='".$item."' ORDER BY subgroup, fieldset, sort_order ASC";
            $result = DB_query($sql);

            while (($row = DB_fetchArray($result))) {

                $groupname = $LANG_configsections[$group]['label'];

                if ( $row['type'] == 'subgroup' ) {
                    if ( !isset($LANG_configsubgroups[$group][$row['name']])) continue;
                    $subgroup = $LANG_configsubgroups[$group][$row['name']];
                    $confname = "";
                    $subgroup_id = $row['name'];
                    $subgroup_num = $row['subgroup'];
                    $fieldset_num = '';
                    $fieldset_id = '';
                    $tabID = '';
                    $value = $subgroup;
                    $label = $groupname . " &raquo; " . $subgroup . " &raquo; " . $fieldset;

                } elseif ( $row['type'] == 'fieldset') {
                    if ( !isset($LANG_fs[$group][$row['name']])) continue;
                    $confname = "";
                    $fieldset = $LANG_fs[$group][$row['name']];
                    $fieldset_id = $row['name'];
                    $fieldset_num = $row['fieldset'];
                    $tabID = '';
                    $value = $fieldset;
                    $label = $groupname . " &raquo; " . $subgroup . " &raquo; " . $fieldset;
                } else {
                    if ( !isset($LANG_confignames[$group][$row['name']])) continue;
                    $confname = $row['name'];
                    $label = $groupname . " &raquo; " . $subgroup . " &raquo; " . $fieldset;
                    $value = $LANG_confignames[$group][$row['name']];
                    $tabID = 'sg_'.$fieldset_id;
                }
                $confArray[] = array('value' => $value,
                                     'data' => array( 'confvar' => $confname,
                                                      'category' => $label,
                                                      'group' => $group,
                                                      'sg' => $subgroup_num,
                                                      'fs' => $fieldset_id,
                                                      'tab' => 'sg_'.$fieldset_id
                ));
            }
        }
        return json_encode($confArray);
    }

}

/**
* Helper function: Provide language dropdown
*
* @return   Array   Array of (filename, displayname) pairs
*
* @note     Note that key/value are being swapped!
*
*/

function configmanager_select_language_helper()
{
    global $_CONF;

    return array_flip(MBYTE_languageList($_CONF['default_charset']));
}

/**
* Helper function: Provide themes dropdown
*
* @return   Array   Array of (filename, displayname) pairs
*
* @note     Beautifying code duplicated from usersettings.php
*
*/
function configmanager_select_theme_helper()
{
    $themes = array();

    $themeFiles = COM_getThemes(true);

    usort($themeFiles,
          function($a,$b) { return strcasecmp($a,$b); });

    foreach ($themeFiles as $theme) {
        $words = explode ('_', $theme);
        $bwords = array ();
        foreach ($words as $th) {
            if ((strtolower ($th{0}) == $th{0}) &&
                (strtolower ($th{1}) == $th{1})) {
                $bwords[] = strtoupper ($th{0}) . substr ($th, 1);
            } else {
                $bwords[] = $th;
            }
        }

        $themes[implode(' ', $bwords)] = $theme;
    }
    return $themes;
}


/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_html_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_log_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_language_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_backup_path_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_data_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_images_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_pear_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}

/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_themes_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_site_url_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] == '/' ) {
        return (substr($value,0,strlen($value)-1));
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_site_admin_url_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] == '/' ) {
        return (substr($value,0,strlen($value)-1));
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_rdf_file_validate($value)
{
    $value = trim($value);
    return $value;
}

/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/
function configmanager_path_to_mogrify_validate($value)
{
    $value = trim($value);
    if ( strlen($value) > 0 ) {
        if ( $value[strlen($value)-1] != '/' ) {
            return $value . '/';
        }
    }
    return $value;
}

/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/
function configmanager_path_to_jhead_validate($value)
{
    $value = trim($value);
    if ( strlen($value) > 0 ) {
        if ( $value[strlen($value)-1] != '/' ) {
            return $value . '/';
        }
    }
    return $value;
}

/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/
function configmanager_enable_twofactor_validate($value)
{
    global $LANG_CONFIG;
    if ($value == 1) {
        if (!function_exists('hash_hmac')) {
            COM_setMsg($LANG_CONFIG['hash_ext_missing'],'error',true);
            $value = 0;
        }
    }
    return $value;
}

/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/
function configmanager_path_to_jpegtrans_validate($value)
{
    $value = trim($value);
    if ( strlen($value) > 0 ) {
        if ( $value[strlen($value)-1] != '/' ) {
            return $value . '/';
        }
    }
    return $value;
}
/**
* Helper function: Provide timezone dropdown
*
* @return   array   Array of (timezone-long-name, timezone-short-name) pairs
*
*/

function configmanager_select_timezone_helper()
{
    $locations = array();
    $all = timezone_identifiers_list();
    $i = 0;
    foreach($all AS $zone) {
        $zone = explode('/',$zone);
        $zonen[$i]['continent'] = isset($zone[0]) ? $zone[0] : '';
        $zonen[$i]['city'] = isset($zone[1]) ? $zone[1] : '';
        $zonen[$i]['subcity'] = isset($zone[2]) ? $zone[2] : '';
        $i++;
    }
    asort($zonen);
    $structure = '';
    foreach($zonen AS $zone) {
        extract($zone);
        if($continent == 'Africa' || $continent == 'America' || $continent == 'Antarctica' || $continent == 'Arctic' || $continent == 'Asia' || $continent == 'Atlantic' || $continent == 'Australia' || $continent == 'Europe' || $continent == 'Indian' || $continent == 'Pacific') {
            if (isset($city) != '') {
                if (!empty($subcity) != '') {
                    $city = $city . '/'. $subcity;
                }
                $tzname = $continent.'/'.str_replace('_',' ',$city);
                $locations[$tzname] = $continent.'/'.$city;
            } else {
                if (!empty($subcity) != '') {
                    $city = $city . '/'. $subcity;
                }
                $locations[$tzname] = $continent.'/'.$city;
            }
        }
    }
    return $locations;
}

?>
