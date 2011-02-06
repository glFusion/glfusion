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

/**
 * Interface that all service drivers must implement
 *
 * @category Services
 * @package  Services_ShortURL
 * @author   Joe Stump <joe@joestump.net>
 * @license  http://tinyurl.com/new-bsd New BSD License
 * @link     http://pear.php.net/package/Services_ShortURL
 */
interface Services_ShortURL_Interface
{
    /**
     * Shorten a given URL
     *
     * @param string $url The URL to shorten
     * 
     * @throws {@link Services_ShortURL_Exception_CouldNotShorten}
     * @return string
     */
    public function shorten($url);

    /**
     * Expand a given short URL
     *
     * @param string $url The short URL to expand
     * 
     * @throws {@link Services_ShortURL_Exception_CouldNotExpand}
     * @return string
     */
    public function expand($url);

    /**
     * Get stat information for a given short URL
     *
     * @param string $url The URL to get stats for
     * 
     * @throws {@link Services_ShortURL_Exception_CouldNotShorten}
     * @throws {@link Services_ShortURL_Exception_NotImplemented}
     * @return mixed
     */
    public function stats($url);

    /**
     * Get information for a given short URL
     *
     * @param string $url The URL to get information for
     * 
     * @throws {@link Services_ShortURL_Exception_CouldNotShorten}
     * @throws {@link Services_ShortURL_Exception_NotImplemented}
     * @return mixed
     */
    public function info($url);
}

?>
