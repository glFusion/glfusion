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

require_once 'Services/ShortURL/Common.php';
require_once 'Services/ShortURL/Interface.php';
require_once 'Services/ShortURL/Exception/NotImplemented.php';
require_once 'Services/ShortURL/Exception/CouldNotShorten.php';
require_once 'Services/ShortURL/Exception/CouldNotExpand.php';
require_once 'Services/ShortURL/Exception/InvalidOptions.php';

/**
 * Interface for creating/expanding short.ie links
 *
 * @category Services
 * @package  Services_ShortURL
 * @author   Joe Stump <joe@joestump.net>
 * @license  http://tinyurl.com/new-bsd New BSD License
 * @link     http://pear.php.net/package/Services_ShortURL
 * @link     http://wiki.short.ie/index.php/Main_Page
 */
class      Services_ShortURL_Shortie
extends    Services_ShortURL_Common
implements Services_ShortURL_Interface
{
    /**
     * Location of API
     *
     * @var string $api Location of API
     */
    protected $api = 'http://short.ie/api';


    /**
     * Shorten a URL using {@link http://short.ie}
     *
     * @param string $url The URL to shorten
     *
     * @throws {@link Services_ShortURL_Exception_CouldNotShorten}
     * @return string The shortened URL
     * @see Services_ShortURL_Shortie::sendRequest()
     */
    public function shorten($url)
    {
        $params = array(
            'format'    => 'xml',
            'url'       => $url,
            'private'   => isset($this->options['private']) ? 'true' : 'false',
        );

        // If the email and secret key is passed, use it.
        if (isset($this->options['email']) && isset($this->options['secretKey'])) {
            $params['email']     = $this->options['email'];
            $params['secretKey'] = $this->options['secretKey'];
        }

        $sets = array();
        foreach ($params as $key => $val) {
            $sets[] = $key . '=' . $val;
        }

        $url = $this->api . '?' . implode('&', $sets);
        $xml = $this->sendRequest($url);
        return (string)$xml->shortened;
    }

    /**
     * Send a request to {@link http://short.ie}
     *
     * @param string $url The URL to send the request to
     *
     * @throws {@link Services_ShortURL_Exception_CouldNotShorten}
     * @return object Instance of SimpleXMLElement
     */
    protected function sendRequest($url)
    {
        $this->req->setUrl($url);
        $this->req->setMethod('GET');

        $result = $this->req->send(); 
        if ($result->getStatus() != 200) {
            throw new Services_ShortURL_Exception_CouldNotExpand(
                'Non-300 code returned', $result->getStatus()
            );
        }

        $xml = @simplexml_load_string($result->getBody());
        if (!$xml instanceof SimpleXMLElement) {
            throw new Services_ShortURL_Exception_CouldNotShorten(
                'Could not parse API response'
            );
        }

        return $xml;
    }
}

?>
