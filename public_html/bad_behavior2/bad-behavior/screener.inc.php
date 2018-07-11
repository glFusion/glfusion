<?php if (!defined('BB2_CWD')) die("I said no cheating!");

// Bad Behavior browser screener

function bb2_screener_cookie($settings, $package, $cookie_name, $cookie_value)
{
	// FIXME: Set the real cookie
	if (!$settings['eu_cookie']) {
		setcookie($cookie_name, $cookie_value, 0, bb2_relative_path(),'',$settings['secure_cookie']);
	}
}

function bb2_screener_javascript($settings, $package, $cookie_name, $cookie_value)
{
	global $bb2_javascript;

	// FIXME: do something
	$bb2_javascript = "<script>
<!--
function bb2_addLoadEvent(e){var t=window.onload;if(typeof window.onload!=\"function\"){window.onload=e}else{window.onload=function(){t();e()}}}bb2_addLoadEvent(function(){for(i=0;i<document.forms.length;i++){if(document.forms[i].method==\"post\"){var e=document.createElement(\"input\");e.setAttribute(\"type\",\"hidden\");e.name=\"$cookie_name\";e.value=\"$cookie_value\";document.forms[i].appendChild(e)}}})
// --></script>";
}

function bb2_screener($settings, $package)
{
	$cookie_name = BB2_COOKIE;

	// Set up a simple cookie
	$screener = array(time(), $package['ip']);
	if (isset($package['headers_mixed']['X-Forwarded-For'])) {
		array_push($screener, $package['headers_mixed']['X-Forwarded-For']);
	}
	if (isset($package['headers_mixed']['Client-Ip'])) {
		array_push($screener, $package['headers_mixed']['Client-Ip']);
	}

	$cookie_value = implode(" ", $screener);

	bb2_screener_cookie($settings, $package, BB2_COOKIE, $cookie_value);
	bb2_screener_javascript($settings, $package, BB2_COOKIE, $cookie_value);
}
?>
