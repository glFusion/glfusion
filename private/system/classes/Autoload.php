<?php
/**
 * glFusion Autoloader
 *
 * LICENSE: This program is free software; you can redistribute it
 *  and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @category   glFusion CMS
 * @package    glFusion
 * @maintainer Mark R. Evans  mark AT glFusion DOT org
 * @copyright  2016-2017 - Mark R. Evans
 * @license    http://opensource.org/licenses/gpl-2.0.php - GNU Public License v2 or later
 * @since      File available since Release 1.7.0
 */

namespace glFusion;

if (!defined('GVERSION')) {
    die('This file can not be used on its own.');
}

/**
 * Class Autoload
 *
 * @package glFusion
 */
class Autoload
{
    /**
     * @var bool
     */
    private static $initialized = false;

    /**
     * Load a class
     *
     * @param  string $className
     */
    public static function load($className)
    {
        if (!self::$initialized) {
            self::initialize();
        }

        if (class_exists($className, false)) {
            return;
        }

        if (strpos($className, 'glFusion\\') === 0) {
            // New classes under \glFusion namespace
            $className = str_replace('glFusion\\', '', $className);
            $className = ucfirst($className);
            $path = __DIR__ . DIRECTORY_SEPARATOR . $className . '.php';

            if (file_exists($path)) {
                /** @noinspection PhpIncludeInspection */
                include $path;

                if (method_exists($className, 'init')) {
                    $className::init();
                }
            }
        } else {
            $path = __DIR__ . DIRECTORY_SEPARATOR . strtolower($className) . '.class.php';
            if (file_exists($path)) {
                /** @noinspection PhpIncludeInspection */
                include $path;
            } else {
                if ( stripos($className,'stringparser') === 0 ) {
                    include __DIR__ . '/../../lib/bbcode/'.$className.'.class.php';
                } elseif (stripos($className, 'timerobject') === 0) {
                    include __DIR__ . '/timer.class.php';
                } elseif (stripos($className, 'XML_RPC_Server') === 0) {
                    include __DIR__ . '/XML/RPC/Server.php';
                } elseif (stripos($className, 'XML_RPC_') === 0) {
                    include __DIR__ . '/XML/RPC.php';
                }
            }
        }
    }

    /**
     * Initialize autoloader
     */
    public static function initialize()
    {
        if (!self::$initialized) {
            require_once __DIR__ . '/../../vendor/autoload.php';
            spl_autoload_register('glFusion\\Autoload::load', true, true);
            self::$initialized = true;
        }
    }

    /**
     * Register an autoloader
     *
     * @param  callable $autoLoader
     * @param  bool     $throw
     * @param  bool     $prepend
     * @throws \InvalidArgumentException
     */
    public static function register($autoLoader, $throw = true, $prepend = false)
    {
        if (!self::$initialized) {
            self::initialize();
        }

        if (!is_callable($autoLoader)) {
            throw new \InvalidArgumentException(__METHOD__ . ': $autoLoader must be callable');
        }

        if (!spl_autoload_register($autoLoader, $throw, $prepend)) {
            throw new \InvalidArgumentException(__METHOD__ . ': could not register the autoloader function');
        }
    }
}
?>
