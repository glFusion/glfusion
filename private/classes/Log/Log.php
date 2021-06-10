<?php
/**
* glFusion CMS
*
* Log Class
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2017-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

namespace glFusion\Log;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

use \glFusion\Log\LineFormatterIP;
use \glFusion\Log\LogException;
use \glFusion\Log\LogHandle;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;

/**
 * Logger Cache
 *
 * @package glFusion
 */
class Log
{
    public const DVLP_DEBUG = 525;  // special mode to ONLY log development messages
    public const DEBUG = 100;
    public const INFO = 200;
    public const NOTICE = 250;
    public const WARNING = 300;
    public const ERROR = 400;
    public const CRITICAL = 500;
    public const ALERT = 550;
    public const EMERGENCY = 600;

    /**
     * private $log
     */
    private $log;

    private static $scopes = array();

    /**
     * Initialize the class
     *
     * @return none
     */

    public static function config($scope, $data = array())
    {
        global $_CONF;

        // scope equates to a plugin / system / etc. Basically use scope to create

        if (count($data) == 0) {
            throw new LogException('Invalid configuration data - no data provided');
        }
        if (!isset($data['type'])) {
            throw new LogException('Log::No log file type passed to config method.');
        }
        switch ($data['type']) {
            case 'file' :
                if (!isset($data['path'])) {
                    throw new LogException('Log::Log type of File is missing path attribute.');
                }
                if (!isset($data['file'])) {
                    throw new LogException('Log::Log type of File is missing file attribute.');
                }
                break;
            case 'syslog' :
                if (!isset($data['host'])) {
                    throw new LogException('Log::Log type of Syslog is missing host attribute.');
                }
                break;
            default :
                throw new LogException('Log::Log type is unknown.');
                break;
        }
        if (empty($scope)) {
            throw new LogException('Log::Scope cannot be blank.');
        }

        if (!isset($data['level'])) {
            $data['level'] = 'info';
        }

        if (!isset($data['path'])) {
            $data['path'] = $_CONF['path_log'];
        }

        if (!isset($data['output'])) {
            $data['output'] = "[%datetime%] %ipaddress% %level_name%: %message% %context% %extra%\n";
        }

        $loggerTimeZone = new \DateTimeZone($_CONF['timezone']);

        $dateFormat = "Y-m-d H:i:s";
        $output = $data['output'];

        $formatter = new LineFormatterIP($output, $dateFormat,false,true);

        $stream = new StreamHandler($data['path'].$data['file'], $data['level']);
        $stream->setFormatter($formatter);

        $log = new Logger($scope);

        self::$scopes[$scope] = new LogHandle();
        self::$scopes[$scope]->log = $log;
        self::$scopes[$scope]->level = $data['level'];
        self::$scopes[$scope]->fileName = $data['file'];

        self::$scopes[$scope]->log->pushHandler($stream);
        self::$scopes[$scope]->log->setTimezone($loggerTimeZone);
    }

    public static function getLogs()
    {
        return self::$scopes;
    }

    public static function close($scope)
    {
        self::$scopes[$scope]->log->close();
    }

    public static function reset($scope)
    {
        self::$scopes[$scope]->log->close();
    }

    public static function write($scope, $logLevel = self::INFO, $logEntry = '', $context = array(), $extra = array())
    {
        if (!isset(self::$scopes[$scope])) {
            throw new LogException('Log::Uninitialized scope: '. $scope);
        }
        if ($logLevel < self::$scopes[$scope]->level) {
            return;
        }

        $logEntry = preg_replace('!\s+!', ' ', $logEntry);

        switch ($logLevel) {
            case self::DEBUG :
                self::$scopes[$scope]->log->debug($logEntry,$context,$extra);
                break;

            case self::NOTICE :
                self::$scopes[$scope]->log->notice($logEntry,$context,$extra);
                break;

            case self::WARNING :
                self::$scopes[$scope]->log->warning($logEntry,$context,$extra);
                break;

            case self::ERROR :
                self::$scopes[$scope]->log->error($logEntry,$context,$extra);
                break;

            case self::CRITICAL :
                self::$scopes[$scope]->log->critical($logEntry,$context,$extra);
                break;

            case self::ALERT :
                self::$scopes[$scope]->log->alert($logEntry,$context,$extra);
                break;

            case self::EMERGENCY :
                self::$scopes[$scope]->log->emergency($logEntry,$context,$extra);
                break;

            case self::DVLP_DEBUG :
                self::$scopes[$scope]->log->alert($logEntry,$context,$extra);
                break;

            default :
            case self::INFO :
                self::$scopes[$scope]->log->info($logEntry,$context,$extra);
                break;
        }
    }

    public static function logAccessViolation($type = '')
    {
        self::write('system',Log::WARNING, "User attempted to access area without proper permissions", array('Type' => $type,'IP' => $_SERVER['REAL_ADDR']));
    }

    public static function debug($msg = '')
    {
        if (Log::DVLP_DEBUG < self::$scopes['system']->level) {
            return;
        }
        self::$scopes['system']->log->alert($msg,array(),array());
    }
}

