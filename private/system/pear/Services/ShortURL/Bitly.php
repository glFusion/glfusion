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
 * Interface for creating/expanding bit.ly links
 *
 * @category Services
 * @package  Services_ShortURL
 * @author   Joe Stump <joe@joestump.net>
 * @license  http://tinyurl.com/new-bsd New BSD License
 * @link     http://pear.php.net/package/Services_ShortURL
 * @link     http://bit.ly
 */
class      Services_ShortURL_Bitly
extends    Services_ShortURL_Common
implements Services_ShortURL_Interface
{
    /**
     * API URL
     *
     * @var string $api The URL for the API
     */
    protected $api = 'http://api.bit.ly';

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

        if (!isset($this->options['login'])) {
            throw new Services_ShortURL_Exception_InvalidOptions(
                'A login is required for bit.ly'
            );
        }

        if (!isset($this->options['apiKey'])) {
            throw new Services_ShortURL_Exception_InvalidOptions(
                'An apiKey is required for bit.ly'
            );
        }
    }

    /**
     * Shorten a URL using {@link http://bit.ly}
     *
     * @param string $url The URL to shorten
     *
     * @throws {@link Services_ShortURL_Exception_CouldNotShorten}
     * @return string The shortened URL
     * @see Services_ShortURL_Bitly::sendRequest()
     */
    public function shorten($url)
    {
        $params = array(
            'version' => '2.0.1',
            'format'  => 'xml',
            'longUrl' => $url,
            'login'   => $this->options['login'],
            'apiKey'  => $this->options['apiKey']        
        );

        $sets = array();
        foreach ($params as $key => $val) {
            $sets[] = $key . '=' . $val;
        }

        $url = $this->api . '/shorten?' . implode('&', $sets);
        $xml = $this->sendRequest($url);
        return (string)$xml->results->nodeKeyVal->shortUrl;
    }

    /**
     * Send a request to {@link http://bit.ly}
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
            throw new Services_ShortURL_Exception_CouldNotShorten(
                'Non-300 code returned', $result->getStatus()
            );
        }

        $xml = @simplexml_load_string($result->getBody());
        if (!$xml instanceof SimpleXMLElement) {
            throw new Services_ShortURL_Exception_CouldNotShorten(
                'Could not parse API response'
            );
        }

        if ((int)$xml->errorCode > 0) {
            throw new Services_ShortURL_Exception_CouldNotShorten(
                (string)$xml->errorMessage, (int)$xml->errorCode
            );
        }

        return $xml;
    }
}

?>
