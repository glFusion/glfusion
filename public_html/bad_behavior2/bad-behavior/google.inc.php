<?php if (!defined('BB2_CORE')) die('I said no cheating!');

// Analyze user agents claiming to be Googlebot

function bb2_google($package)
{
	if (match_cidr($package['ip'], "66.249.64.0/19") === FALSE &&
	    match_cidr($package['ip'], "66.102.0.0/20") === FALSE &&
	    match_cidr($package['ip'], "64.233.160.0/19") === FALSE &&
	    match_cidr($package['ip'], "72.14.192.0/18") === FALSE &&
  	    match_cidr($package['ip'], "104.196.0.0/14") === FALSE &&
	    match_cidr($package['ip'], "203.208.32.0/19") === FALSE &&
	    match_cidr($package['ip'], "74.125.0.0/16") === FALSE &&
	    match_cidr($package['ip'], "209.85.128.0/17") === FALSE &&
	    match_cidr($package['ip'], "216.239.32.0/19") === FALSE) {
		return "f1182195";
	}
	return false;
}

?>
