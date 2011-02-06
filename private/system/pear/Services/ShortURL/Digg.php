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
 * Interface for creating/expanding Digg links
 *
 * @category Services
 * @package  Services_ShortURL
 * @author   Joe Stump <joe@joestump.net>
 * @license  http://tinyurl.com/new-bsd New BSD License
 * @link     http://pear.php.net/package/Services_ShortURL
 * @link     http://apidoc.digg.com/ShortURLs
 */
class      Services_ShortURL_Digg
extends    Services_ShortURL_Common
implements Services_ShortURL_Interface
{
    /**
     * Location of API
     *
     * @var string $api Location of API
     */
    protected $api = 'http://services.digg.com/url/short';
    
    /**
     * Constructor
     *
     * @param array  $options The service options array
     * @param object $req     The request object 
     *
     * @throws {@link Services_ShortURL_Exception_InvalidOptions}
     * @return void
     */
    public function __construct(array $options = array(), 
                                HTTP_Request2 $req = null) 
    {
        parent::__construct($options, $req);

        if (!isset($this->options['appkey'])) {
            throw new Services_ShortURL_Exception_InvalidOptions(
                'An appkey is required for Digg'
            );
        }
    }

    /**
     * Shorten a URL using {@link http://digg.com}
     *
     * @param string $url The URL to shorten
     *
     * @throws {@link Services_ShortURL_Exception_CouldNotShorten}
     * @return string The shortened URL
     * @see Services_ShortURL_Digg::sendRequest()
     */
    public function shorten($url)
    {
        $url = $this->api . '/create?appkey=' . 
               urlencode($this->options['appkey']) . '&url=' . 
               urlencode($url);

        $xml = $this->sendRequest($url);

        return (string)$xml->shorturl['short_url'];
    }

    /**
     * Expand a {@link http://digg.com} short URL
     *
     * @param string $url The short URL to expand
     *
     * @throws {@link Services_ShortURL_Exception_CouldNotExpand}
     * @return string The expanded URL
     * @see Services_ShortURL_Digg::sendRequest()
     */
    public function expand($url)
    {
        $m      = array();
        $regExp = '#http://digg.com/(?P<id>[du][0-9][a-zA-Z0-9]{1,6})?#';
        if (!preg_match($regExp, $url, $m)) {
            throw new Services_ShortURL_Exception_CouldNotExpand(
                $url . ' is not a valid Digg URL'
            );
        }

        $url = $this->api . '/' . $m['id'] . '?appkey=' . 
               urlencode($this->options['appkey']);

        $xml = $this->sendRequest($url);
        
        return (string)$xml->shorturl['link'];
    }

    /**
     * Send a request to {@link http://services.digg.com}
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
            var_dump($result->getBody());
            throw new Services_ShortURL_Exception_CouldNotShorten(
                'Non-200 code returned', $result->getStatus()
            );
        }

        $xml = simplexml_load_string($result->getBody());
        if (!$xml instanceof SimpleXMLElement) {
            throw new Services_ShortURL_Exception_CouldNotShorten(
                'Could not parse API response'
            );
        }

        if (!isset($xml->shorturl) || !isset($xml->shorturl['short_url'])) {
            throw new Services_ShortURL_Exception_CouldNotShorten(
                'Bad response from Digg API'
            );
        }

        return $xml;
    }
}

?>
