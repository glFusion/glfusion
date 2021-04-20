<?php
/**
* glFusion CMS - CAPTCHA Plugin
*
* Upgrade
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2002-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Log\Log;

require_once $_CONF['path'].'plugins/captcha/captcha.php';

function captcha_upgrade()
{
    global $_TABLES, $_CONF, $_CP_CONF;

    $currentVersion = DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='captcha'");

    switch( $currentVersion ) {
        case "2.0.0" :
        case "2.0.1" :
        case "2.0.2" :
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='2.1.0' WHERE pi_name='captcha' LIMIT 1");
        case "2.1.0" :
        case "2.1.1" :
        case "2.1.2" :
            $_SQL['cp_sessions'] =
                            "CREATE TABLE {$_TABLES['cp_sessions']} ( " .
                            "  `session_id` varchar(40) NOT NULL default '', " .
                            "  `cptime`  INT(11) NOT NULL default 0, " .
                            "  `validation` varchar(40) NOT NULL default '', " .
                            "  `counter`    INT(11) NOT NULL default 0, " .
                            "  PRIMARY KEY (`session_id`) " .
                            " );";

            foreach ($_SQL as $table => $sql) {
                Log::write('system',Log::INFO,'Creating '.$table.' table');
                DB_query($sql,1);
                if (DB_error()) {
                    Log::write('system',Log::ERROR, 'Error Creating '.$table.' table');
                }
                Log::write('system',Log::INFO,'Success - Created '.$table.' table');
            }
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='3.0.0' WHERE pi_name='captcha' LIMIT 1");
        case "3.0.0" :
        case "3.0.1" :
        case "3.0.2" :
        case "3.1.0" :
            // need to migrate the configuration to our new online configuration.
            require_once $_CONF['path_system'] . 'classes/config.class.php';
            require_once $_CONF['path'] . 'plugins/captcha/install_defaults.php';
            plugin_initconfig_captcha();
            include $_CONF['path'].'plugins/captcha/captcha.php';
        case "3.2.0" :
            $c = config::get_instance();
            $c->add('expire', '900','text',
                   0, 0, 0, 70, true, 'captcha');
        case '3.2.1' :
        case '3.2.2' :
        case '3.2.3' :
            $c = config::get_instance();
            $c->add('publickey', '','text',
                    0, 0, 0, 42, true, 'captcha');
            $c->add('privatekey', '','text',
                    0, 0, 0, 44, true, 'captcha');
            $c->add('recaptcha_theme', 'white','select',
                    0, 0, 6, 46, true, 'captcha');
        case '3.2.4' :
        case '3.2.5' :
            $c = config::get_instance();
            $c->add('pc_publickey', '','text',0, 0, 0, 48, true, 'captcha');
            $c->add('pc_privatekey', '','text',0, 0, 0, 49, true, 'captcha');

        case '3.3.0' :
            $c = config::get_instance();
            $c->add('ay_publickey', '','text',0, 0, 0, 50, true, 'captcha');
            $c->add('ay_privatekey', '','text',0, 0, 0, 51, true, 'captcha');

            $c->del('pc_publickey','captcha');
            $c->del('pc_privatekey','captcha');
            if ( $_CP_CONF['gfxDriver'] == 4 ) {
                $c->set('gfxDriver',6,'captcha');
            }
        case '3.4.0' :
            // no changes needed
        case '3.4.1' :
            // need to add column to table
            // ALTER TABLE `gl_cp_sessions` ADD `ip` VARCHAR(16) NOT NULL ;
            $sql = "ALTER TABLE {$_TABLES['cp_sessions']} ADD `ip` VARCHAR(16) NOT NULL";
            DB_query($sql,1);
            $sql = "ALTER TABLE {$_TABLES['cp_sessions']} CHANGE `counter` `counter` INT(11) NOT NULL DEFAULT '0';";
            DB_query($sql,1);

        case '3.5.1' :
            $c = config::get_instance();
            $c->del('ay_publickey','captcha');
            $c->del('ay_privatekey','captcha');
            if ( $_CP_CONF['gfxDriver'] == 5 ) {
                $c->set('gfxDriver',6,'captcha');
            }
        case '3.5.2' :
            $c = config::get_instance();
            $c->set('recaptcha_theme','light','captcha');
        case '3.5.3' :
            // no changes
        case '3.5.4' :

        case '3.6.0' :
            $currentDriverSer = DB_getitem($_TABLES['conf_values'],'value','name="gfxDriver"');
            $currentDriver = @unserialize($currentDriverSer);
            if ( $currentDriver == 1 || $currentDriver == 2 ) {
                $c = config::get_instance();
                $c->set('gfxDriver',6,'captcha');
            }

        default :
            captcha_update_config();
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='".$_CP_CONF['pi_version']."',pi_gl_version='".$_CP_CONF['gl_version']."' WHERE pi_name='captcha' LIMIT 1");
            break;
    }
    if ( DB_getItem($_TABLES['plugins'],'pi_version',"pi_name='captcha'") == $_CP_CONF['pi_version']) {
        return true;
    } else {
        return false;
    }
}

function captcha_update_config()
{
    global $_CONF, $_CP_CONF, $_TABLES;

    USES_lib_install();

    require_once $_CONF['path'].'plugins/captcha/sql/captcha_config_data.php';
    _update_config('captcha', $captchaConfigData);

}
?>