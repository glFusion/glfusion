<?php
/**
 * @file
 * This is a PHP library that handles calling Picatcha.
 */

/**
 * The Picatcha server URL
 */
define("PICATCHA_API_SERVER", "api.picatcha.com");


/**
 * in case json functions do not exist
 */
if (!function_exists('json_encode')) {
  if (!class_exists('Services_JSON')) {
    require_once 'Services_JSON/JSON.php';
  }
  /**
   * encodes an arbitrary variable into JSON format (and sends JSON Header)
   *
   * @param mixed $var
   *   any number, boolean, string, array, or object to be encoded
   *
   * @return mixed
   *   JSON string representation of input var or an error if a problem occurs
   */
  function json_encode($var = FALSE) {
    $json = new Services_JSON();
    return $json->encode($var);
  }
}

if (!function_exists('json_decode')) {
  if (!class_exists('Services_JSON')) {
    require_once 'Services_JSON/JSON.php';
  }
  /**
   * decodes a JSON string into appropriate variable
   *
   * @param string $str
   *   JSON-formatted string
   *
   * @return mixed
   *   number, boolean, string, array, or object corresponding to
   *   given JSON input string
   */
  function json_decode($str) {
    $json = new Services_JSON();
    return $json->decode($value);
  }
}


/**
 * Submits an HTTP POST to a Picatcha server
 *
 * @param string $host
 *   Host to send the request
 * @param string $path
 *   Path to send the request
 * @param array $data
 *   Data to send with the request
 * @param integer $port
 *   Port to send the request (default: 80)
 *
 * @return array
 *   response
 */
function _picatcha_http_post($host, $path, $data, $port = 80) {
  $http_request  = "POST $path HTTP/1.0\r\n";
  $http_request .= "Host: $host\r\n";
  $http_request .= "User-Agent: Picatcha/PHP\r\n";
  $http_request .= "Content-Length: " . strlen($data) . "\r\n";
  $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
  $http_request .= "\r\n";
  $http_request .= $data;

  $response = '';
  $fs = @fsockopen($host, $port, $errno, $errstr, 10);
  if (FALSE == $fs) {
    die('Could not open socket');
  }

  fwrite($fs, $http_request);

  // 1160 is the size of one TCP-IP packet.
  while (!feof($fs))
    $response .= fgets($fs, 1160);
  fclose($fs);
  $response = explode("\r\n\r\n", $response, 2);
  return $response;
}


/**
 * Gets the challenge HTML (javascript and non-javascript version).
 *
 * This is called from the browser, and the resulting Picatcha HTML widget
 * is embedded within the HTML form it was called from.
 *
 * @param string $pubkey
 *   A public key for Picatcha
 * @param string $error
 *   The error given by Picatcha (default: null)
 *
 * @return string
 *   The HTML to be embedded in the user's form
 */
function picatcha_get_html($pubkey, $error = NULL, $format = '2', $style = '#2a1f19', $link = '1', $IMG_SIZE = '75', $NOISE_LEVEL = 0, $NOISE_TYPE = 0, $lang = 'en', $langOverride = '0', $use_ssl=false) {
  if($use_ssl){
    $api_server = "https://".PICATCHA_API_SERVER;
  }else{
    $api_server = "http://".PICATCHA_API_SERVER;
  }
  $elm_id = 'picatcha';
  $html = '';
  $html .= '<script type="text/javascript" src="' . $api_server . '/static/client/picatcha.js"></script>';
  $html .= '<link href="' . $api_server . '/static/client/picatcha.css" rel="stylesheet" type="text/css">';
  $html .= '<script type="text/javascript">Picatcha.PUBLIC_KEY="'.$pubkey.'";Picatcha.setCustomization({"format":"'.$format.'","color":"'.$style.'","link":"'.$link.'","image_size":"'.$IMG_SIZE.'","lang":"'.$lang.'","langOverride":"'.$langOverride.'","noise_level":"'.$NOISE_LEVEL.'","noise_type":"'.$NOISE_TYPE.'"}); jQuery(window).load(function(){Picatcha.create("'.$elm_id.'",{})});</script>';
  if ($error != NULL) {
    $html .= '<div id="' . $elm_id . '_error">' . $error . '</div>';
  }
  $html .= '<div id="' . $elm_id . '"></div>';
  return $html;
}


/**
 * A PicatchaResponse is returned from picatcha_check_answer()
 */
class PicatchaResponse {
  var $isValid;
  var $error;
}


/**
 * Calls an HTTP POST function to verify if the user's choices were correct
 *
 * @param string $privkey
 *   Private key
 * @param string $remoteip
 *   Remote IP
 * @param string $challenge
 *   Challenge token
 * @param array $response
 *   Response
 * @param array $extra_params
 *   Extra variables to post to the server
 *
 * @return PicatchaResponse
 *   An instance of PicatchaResponse
 */
function picatcha_check_answer($privkey, $remoteip, $user_agent, $challenge, $response, $timeout = 90, $extra_params = array()) {
  if ($privkey == NULL || $privkey == '') {
    die("To use Picatcha you must get an API key from <a href='http://picatcha.com'>http://picatcha.com</a>");
  }

  if ($remoteip == NULL || $remoteip == '') {
    die("For security reasons, you must pass the remote ip to Picatcha");
  }
  if ($user_agent == NULL || $user_agent == '') {
    die("You must pass the user agent to Picatcha");
  }

  // Discard spam submissions.
  if ($challenge == NULL || strlen($challenge) == 0 || $response == NULL || count($response) == 0) {
    $picatcha_response = new PicatchaResponse();
    $picatcha_response->isValid = FALSE;
    $picatcha_response->error = 'incorrect-answer';
    return $picatcha_response;
  }

  $params = array(
    'k' => $privkey,
    'ip' => $remoteip,
    'ua' => $user_agent,
    't' => $challenge,
    'r' => $response,
    'to' => $timeout,
  ) + $extra_params;
  $data = json_encode($params);
  $response = _picatcha_http_post(PICATCHA_API_SERVER, "/v", $data);
  $res = json_decode($response[1], FALSE);

  $picatcha_response = new PicatchaResponse();
  if ($res->s) {
    $picatcha_response->isValid = TRUE;
  }
  else {
    $picatcha_response->isValid = FALSE;
    $picatcha_response->error = $res->e;
  }
  return $picatcha_response;
}


/**
 * Gets a URL where the user can sign up for Picatcha.
 *
 * If your application has a configuration page where you enter a key,
 * you should provide a link using this function.
 *
 * @param string $domain
 *   The domain where the page is hosted
 * @param string $appname
 *   The name of your application
 */
function picatcha_get_signup_url($domain = NULL, $appname = NULL) {
  return "http://picatcha.com/";
}
