<?php if (!defined('BB2_CORE')) die('I said no cheating!');

// All tests which apply specifically to POST requests
function bb2_post($settings, $package)
{
	// Check blackhole lists for known spam/malicious activity
	// require_once(BB2_CORE . "/blackhole.inc.php");
	// bb2_test($settings, $package, bb2_blackhole($package));

	// MovableType needs specialized screening
	if (stripos($package['headers_mixed']['User-Agent'], "MovableType") !== FALSE) {
		if (strcmp($package['headers_mixed']['Range'], "bytes=0-99999")) {
			return "7d12528e";
		}
	}

	// Trackbacks need special screening
	$request_entity = $package['request_entity'];
	if (isset($request_entity['title']) && isset($request_entity['url']) && isset($request_entity['blog_name'])) {
		require_once(BB2_CORE . "/trackback.inc.php");
		return bb2_trackback($package);
	}

	// Catch a few completely broken spambots
	foreach ($request_entity as $key => $value) {
		$pos = strpos($key, "	document.write");
		if ($pos !== FALSE) {
			return "dfd9b1ad";
		}
	}

	// If Referer exists, it should refer to a page on our site
	if (!$settings['offsite_forms'] && array_key_exists('Referer', $package['headers_mixed']) && stripos($package['headers_mixed']['Referer'], $package['headers_mixed']['Host']) === FALSE) {
		return "cd361abb";
	}

	return false;
}

?>
