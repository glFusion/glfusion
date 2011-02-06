<?php

/**
 * An abstract interface for dealing with short URL services
 *
 * PHP version 5.2.0+
 *
 * LICENSE: This source file is subject to the New BSD license that is
 * available through the world-wide-web at the following URI:
 * http://www.opensource.org/licenses/bsd-license.php. If you did not receive  
 * a copy of the New BSD License and are unable to obtain it through the web, 
 * please send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Services
 * @package   Services_ShortURL
 * @author    Joe Stump <joe@joestump.net> 
 * @copyright 2009 Joe Stump <joe@joestump.net> 
 * @license   http://tinyurl.com/new-bsd New BSD License
 * @version   CVS: $Id:$
 * @link      http://pear.php.net/package/Services_ShortURL
 * @link      http://github.com/joestump/services_shorturl
 */     

require_once 'Net/URL2.php';
require_once 'Services/ShortURL/Exception.php';

/**
 * An interface for managing short URLs
 *
 * @category Services
 * @package  Services_ShortURL
 * @author   Joe Stump <joe@joestump.net>
 * @license  http://tinyurl.com/new-bsd New BSD License
 * @link     http://pear.php.net/package/Services_ShortURL
 * @link     http://github.com/joestump/services_shorturl
 */             
abstract class Services_ShortURL
{
    /**
     * Global options for services
     *
     * Most short URL services have APIs that do not require anything special
     * to interface with. Others, however, require API keys, usernames, etc.
     * This is a global variable that can store those options to reduce
     * repetitiveness.
     *
     * @see Services_ShortURL::setServiceOptions()
     * @var array $options Service options
     */
    static protected $options = array();

    /**
     * List of supported services
     *
     * @var array $services Map of services to drivers
     */
    static protected $services = array(
        'bit.ly'      => 'Bitly',
        'is.gd'       => 'Isgd',
        'tinyurl.com' => 'TinyURL',
        'digg.com'    => 'Digg',
        'tr.im'       => 'Trim',
        'short.ie'    => 'Shortie'
    );

    /**
     * Create an instance of a service driver
     *
     * @param string $service Name of service driver
     * @param array  $options Options for the given service
     *
     * @throws {@link Services_ShortURL_Exception}
     * @return object Instance of {@link Services_ShortURL_Interface}
     */
    static public function factory($service, array $options = array())
    {
        if (!in_array($service, self::$services)) {
            throw new Services_ShortURL_Exception(
                'Service ' . $service . ' is invalid'
            ); 
        }

        $file = 'Services/ShortURL/' . $service . '.php';
        include_once $file;

        $class = 'Services_ShortURL_' . $service;
        if (!class_exists($class, false)) {
            throw new Services_ShortURL_Exception(
                'Service class invalid or missing'
            ); 
        }

        if (empty($options) && isset(self::$options[$service])) {
            $options = self::$options[$service];
        }

        $instance = new $class($options);
        if (!$instance instanceof Services_ShortURL_Interface) {
            throw new Services_ShortURL_Exception(
                'Service instance is invalid'
            ); 
        }

        return $instance;
    }

    /**
     * Detect service from a short URL
     *
     * Takes a URL and inpects it to see if there is a service driver that can
     * be used to expand it.
     *
     * @param string $url The short URL to inspect
     *
     * @return object Instance of {@link Services_ShortURL_Interface}
     */
    static public function detect($url)
    {
        $url  = new Net_URL2($url);
        $host = $url->getHost();

        if (!isset(self::$services[$host])) {
            throw new Services_ShortURL_Exception_UnknownService();
        }

        return self::factory(self::$services[$host]);
    }

    /**
     * Extract all short URLs and expand them from a string
     *
     * This exception will take a given string (e.g. a blog post or a tweet),
     * inspect it for supported short URLs and expand each of them. It then
     * returns an array with the short URL as the key and the expanded URL
     * as the value.
     *
     * @param string $string The string to inspect for short URLs
     *
     * @throws {@link Services_ShortURL_Exception_CouldNotExpand}
     * @return array An array keyed by short URL with expanded URL as value
     */
    static public function extract($string)
    {
        $m      = array();
        $regExp = '#(?P<url>http://(' . 
                  implode('|', array_keys(self::$services)) . 
                  ')/[a-z0-9A-Z]+)\b#';

        if (!preg_match_all($regExp, $string, $m)) {
            return array();
        }

        $ret = array();
        foreach ($m['url'] as $url) {
            $api       = self::detect($url);
            $ret[$url] = $api->expand($url);
        }

        return $ret;
    }

    /**
     * Add a service driver
     *
     * You can easily create your own drivers and enable them globally using
     * this method. Please submit your drivers if you're able to do so. We
     * appreciate the help!
     *
     * @param string $host   The hostname used by the shortening service
     * @param string $driver The name of the driver to be used
     *
     * @return void
     */
    static public function addService($host, $driver)
    {
        self::$services[$host] = $driver;
    }

    /**
     * Set global service options
     *
     * To easily use the extract feature or not have to worry about
     * setting options for everything you can globally set a service's
     * options using this method. {@link Services_ShortURL::factory()} will
     * then use options set by this method.
     *
     * @param string $service Service to set options for
     * @param array  $options The options for the service
     *
     * @return void
     */
    static public function setServiceOptions($service, array $options)
    {
        self::$options[$service] = $options;
    }

    /**
     * Constructor 
     *
     * @access private
     * @return void
     */
    final private function __construct()
    {

    }
}

?>
