<?php
/**
* glFusion CMS
*
* Admin Actions
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2020 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*
*/

namespace glFusion\Admin;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Database\Database;
use \glFusion\Log\Log;

class AdminAction
{

    /*
     * private dbHandle
     */
    private static $dbHandle = null;

    /*
     * private initiaized
     */
    private static $initialized = false;

    /**
     * Initialize the class
     *
     * @return none
     */
    private static function initialize()
    {
        if (self::$initialized)
            return;

        self::$dbHandle = Database::getInstance();
        self::$initialized = true;
    }

    /**
     * Write action to database table
     *
     * @param   string $module
     * @param   string $action
     * @param   string $desc
     * @return none
     */
    public static function write($module='system',$action='undefined',$desc='')
    {
        global $_CONF, $_USER, $_TABLES;

        if (isset($_CONF['enable_admin_actions']) && $_CONF['enable_admin_actions'] == 1) {
            self::initialize();

            try {
                self::$dbHandle->conn->insert(
                            $_TABLES['admin_action'],
                            array(
                                'datetime'  => $_CONF['_now']->toMySQL(true),
                                'module'    => $module,
                                'action'    => $action,
                                'description' => $desc,
                                'user'      => (empty($_USER['username']) ? 'system' : $_USER['username']),
                                'ip'        => $_SERVER['REAL_ADDR']
                            )
                );
//                Log::write('system',Log::ERROR,sprintf("Admin Action: %s - %s - %s - %s",$module,$action,$desc,$_SERVER['REAL_ADDR']));
            } catch(\Doctrine\DBAL\DBALException $e) {
                Log::write('system',Log::ERROR,'10001 :: Failure writing administrative action to the admin_action database table.');
                Log::write('system',Log::ERROR,sprintf("Admin Action: %s - %s - %s - %s",$module,$action,$desc,$_SERVER['REAL_ADDR']));
            }
        }
    }
}
