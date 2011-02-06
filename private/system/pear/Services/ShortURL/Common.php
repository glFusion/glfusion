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

require_once 'HTTP/Request2.php';
require_once 'Services/ShortURL/Exception.php';

/**
 * A common class for all short URL drivers
 *
 * @category Services
 * @package  Services_ShortURL
 * @author   Joe Stump <joe@joestump.net>
 * @license  http://tinyurl.com/new-bsd New BSD License
 * @link     http://pear.php.net/package/Services_ShortURL
 */
abstract class Services_ShortURL_Common
{
    /**
     * Service options
     *
     * Some services require an API key, username/password, or other 
     * non-standard information. Those options are set on a per-service 
     * basis and passed to the constructor.
     *
     * @var array $options Service options
     * @see Services_ShortURL::$options
     * @see Services_ShortURL::setServiceOptions()
     */
    protected $options = array();

    /**
     * Instance of {@link HTTP_Request2}
     *
     * @var object $req Instance of {@link HTTP_Request2}
     * @see Services_ShortURL_Common::__construct()
     */
    protected $req = null;

    /**
     * Constructor
     *
     * @param array  $options Service options
     * @param object $req     Provide your own {@link HTTP_Request2} instance
     *
     * @return void
     */
    public function __construct(array $options = array(), 
                                HTTP_Request2 $req = null) 
    {
        if ($req !== null) {
            $this->accept($req);
        } else {
            $this->req = new HTTP_Request2();
            $this->req->setAdapter('Curl');
            $this->req->setHeader('User-Agent', get_class($this) . ' @version@');
        }

        $this->options = $options;
    }

    /**
     * Acceptor pattern
     *
     * By default, {@link Services_ShortURL} will create a cURL version of
     * {@link HTTP_Request2}. If you need to override this you can use the
     * accept method or pass an instance into the constructor.
     *
     * @param object $object Instance of {@link HTTP_Request2}
     *
     * @throws InvalidArgumentException on invalid object
     * @return void
     */
    public function accept($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException("Can't accept non-objects.");
        }

        if ($object instanceof HTTP_Request2) {
            $this->req = $object;
        } else {
            throw new InvalidArgumentException(
                "Can't accept object of type " . get_class($object)
            );
        }
    }

    /**
     * Default expand method
     *
     * All of the URL shortening services, for the most part, do a 301 redirect
     * using the Location header. Rather than implement this over and over we
     * provide it here and assume others who need non-normal expansion will
     * override this method.
     *
     * @param string $url The URL to expand
     *
     * @throws {@link Services_ShortURL_Exception_CouldNotExpand} on non-300's.
     * @return string $url The expanded URL
     */
    public function expand($url)
    {
        $this->req->setUrl($url);
        $this->req->setMethod('GET');
        $result = $this->req->send();

        if (intval(substr($result->getStatus(), 0, 1)) != 3) {
            throw new Services_ShortURL_Exception_CouldNotExpand(
                'Non-300 code returned', $result->getStatus()
            );
        }

        return trim($result->getHeader('Location'));       
    }

    /**
     * Fetch information about the short URL
     *
     * @param string $url The short URL to fetch information for
     *
     * @throws {@link Services_ShortURL_Exception_NotImplemented}
     * @return mixed
     */
    public function stats($url)
    {
        throw new Services_ShortURL_Exception_NotImplemented(
            'Stats is not implemented for ' . get_class($this)
        );
    }

    /**
     * Fetch information about the short URL
     *
     * @param string $url The short URL to fetch information for
     *
     * @throws {@link Services_ShortURL_Exception_NotImplemented}
     * @return mixed
     */
    public function info($url)
    {
        throw new Services_ShortURL_Exception_NotImplemented(
            'Info is not implemented for ' . get_class($this)
        );
    }
}

?>
