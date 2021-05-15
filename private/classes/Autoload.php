<?php
/**
* glFusion CMS
*
* glFusion Class Autoloader
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2016-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
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
            $className = str_replace('\\', '/', $className);
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
            $path = __DIR__ . DIRECTORY_SEPARATOR . '../system/classes/'.strtolower($className) . '.class.php';
            if (file_exists($path)) {
                /** @noinspection PhpIncludeInspection */
                include $path;
            } else {
                if ( stripos($className,'stringparser') === 0 ) {
                    include __DIR__ . DIRECTORY_SEPARATOR . '../lib/bbcode/'.strtolower($className).'.class.php';
                } elseif (stripos($className, 'timerobject') === 0) {
                    include __DIR__ . DIRECTORY_SEPARATOR .'../system/classes/timer.class.php';
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
            require_once __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';
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
