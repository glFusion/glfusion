<?php if (!defined('BB2_CORE')) die('I said no cheating!');

function bb2_blacklist($settings,$package) {

    global $_CONF;

    // should not need to edit this file - all additions are handled via online configuration

    $bb2_blacklist_cidrs = array();

	// Blacklisted user agents
	// These user agent strings occur at the beginning of the line.
	$bb2_spambots_0 = array(
		"-",	// brute force password attempts, malicious botnet
		"8484 Boston Project",	// video poker/porn spam
		"ArchiveTeam",	// ignores robots.txt and hammers server
		"adwords",		// referrer spam
		"autoemailspider",	// spam harvester
		"blogsearchbot-martin",	// from honeypot
		"BrowserEmulator/",	// open proxy software
		"CherryPicker",		// spam harvester
		"core-project/",	// FrontPage extension exploits
		"Diamond",		// delivers spyware/adware
		"Digger",		// spam harvester
		"ecollector",		// spam harvester
		"EmailCollector",	// spam harvester
		"Email Siphon",		// spam harvester
		"EmailSiphon",		// spam harvester
		"Forum Poster",		// forum spambot
		"grub crawler",		// misc comment/email spam
		"HttpProxy",		// misc comment/email spam
		"Internet Explorer",	// XMLRPC exploits seen
		"ISC Systems iRc",	// spam harvester
		"Jakarta Commons",	// customised spambots
		"Java 1.",		// unidentified robots
		"Java/1.",		// unidentified robots
		"libwww-perl",		// unidentified robots
		"LWP",			// unidentified robots
		"lwp",			// unidentified robots
		"Microsoft Internet Explorer/",	// too old; assumed robot
		"Microsoft URL",	// unidentified robots
		"Missigua",		// spam harvester
		"MJ12bot/v1.0.8",	// malicious botnet
		"Morfeus",		// vulnerability scanner
		"Movable Type",		// customised spambots
		"Mozilla/0",		// malicious software
		"Mozilla/1",		// malicious software
		"Mozilla/2",		// malicious software
		"Mozilla/3",		// malicious software
		"Mozilla/4.0(",		// from honeypot
		"Mozilla/4.0+(compatible;+",	// suspicious harvester
		"Mozilla/4.0 (Hydra)",	// brute force tool
		"MSIE",			// malicious software
		"MVAClient",		// automated hacking attempts
		"Nessus",		// vulnerability scanner
		"NutchCVS",		// unidentified robots
		"Nutscrape/",		// misc comment spam
		"OmniExplorer",		// spam harvester
		"Opera/9.64(",		// comment spam bot
		"PMAFind",		// vulnerability scanner
		"psycheclone",		// spam harvester
		"PussyCat ",		// misc comment spam
		"PycURL",		// misc comment spam
		"Python-urllib",	// commonly abused
		"revolt",		// vulnerability scanner
		"sqlmap/",		// SQL injection
		"Super Happy Fun ",	// spam harvester
		"TrackBack/",		// trackback spam
		"user",			// suspicious harvester
		"User Agent: ",		// spam harvester
		"User-Agent: ",		// spam harvester
		"w3af",			// vulnerability scanner
		"WebSite-X Suite",	// misc comment spam
		"Winnie Poh",		// Automated Coppermine hacks
		"Wordpress",		// malicious software
		"\"",			// malicious software
	);

	// These user agent strings occur anywhere within the line.
	$bb2_spambots = array(
		"\r",			// A really dumb bot
		"<sc",			// XSS exploit attempts
		"; Widows ",		// misc comment/email spam
		": ;",			// shellshock
		":;",			// shellshock
		"a href=",		// referrer spam
		"ArchiveBot",	// ignores robots.txt and hammers server
		"Bad Behavior Test",	// Add this to your user-agent to test BB
		"compatible ; MSIE",	// misc comment/email spam
		"compatible-",		// misc comment/email spam
		"DTS Agent",		// misc comment/email spam
		"Email Extractor",	// spam harvester
		"Firebird/",		// too old; assumed robot
		"Gecko/2525",		// revisit this in 500 years
		"grub-client",		// search engine ignores robots.txt
		"hanzoweb",		// very badly behaved crawler
		"Havij",		// SQL injection tool
		"Indy Library",		// misc comment/email spam
		"Ming Mong",		// brute force tool
		"MSIE 7.0;  Windows NT 5.2",	// Cyveillance
		"Murzillo compatible",	// comment spam bot
		".NET CLR 1)",		// free poker, etc.
		".NET CLR1",		// spam harvester
		"Netsparker",		// vulnerability scanner
		"Nikto/",		// vulnerability scanner
		"Perman Surfer",	// old and very broken harvester
		"POE-Component-Client",	// free poker, etc.
		"Teh Forest Lobster",	// brute force tool
		"Turing Machine",	// www.anonymizer.com abuse
		"Ubuntu/9.25",		// comment spam bot
		"unspecified.mail",	// stealth harvesters
		"User-agent: ",		// spam harvester/splogger
		"WebaltBot",		// spam harvester
		"WISEbot",		// spam harvester
		"WISEnutbot",		// spam harvester
		"Win95",		// too old; assumed robot
		"Win98",		// too old; assumed robot
		"WinME",		// too old; assumed robot
		"Win 9x 4.90",		// too old; assumed robot
		"Windows 3",		// too old; assumed robot
		"Windows 95",		// too old; assumed robot
		"Windows 98",		// too old; assumed robot
		"Windows NT 4",		// too old; assumed robot
		"Windows NT;",		// too old; assumed robot
		#"Windows NT 4.0;)",	// wikispam bot
		"Windows NT 5.0;)",	// wikispam bot
		"Windows NT 5.1;)",	// wikispam bot
		"Windows XP 5",		// spam harvester
		"WordPress/4.01",	// pingback spam
		"Xedant Human Emulator",// spammer script engine
		"ZmEu",			// exploit scanner
		"\\\\)",		// spam harvester
	);

	// These are regular expression matches.
	$bb2_spambots_regex = array(
		"/^[A-Z]{10}$/",	// misc email spam
		"/[bcdfghjklmnpqrstvwxz ]{8,}/",
//		"/(;\){1,2}$/",		// misc spammers/harvesters
		"/MSIE.*Windows XP/",	// misc comment spam
		"/MSIE [2345]/",	// too old; assumed robot
	);

	// Blacklisted URL strings
	// These strings are considered case-insensitive.
	$bb2_spambots_url = array(
		"0x31303235343830303536",	// Havij
		"../",				// path traversal
		"..\\",				// path traversal
		"%60information_schema%60",	// SQL injection probe
		"+%2F*%21",			// SQL injection probe
		"%27--",			// SQL injection
		"%27 --",			// SQL injection
		"%27%23",			// SQL injection
		"%27 %23",			// SQL injection
		"benchmark%28",			// SQL injection probe
		"insert+into+",			// SQL injection
		"r3dm0v3",			// SQL injection probe
		"select+1+from",		// SQL injection probe
		"union+all+select",		// SQL injection probe
		"union+select",			// SQL injection probe
		"waitfor+delay+",		// SQL injection probe
		"w00tw00t",			// vulnerability scanner
	);

	$bb2_spambot_refer = array(
	    "gamesthelife.tr.gg",
    );
	// Do not edit below this line.

	if ( isset($_CONF['bb2_spambot_ip'] ) && is_array($_CONF['bb2_spambot_ip'] ) ) {
	    $bb2_blacklist_cidrs = array_merge($bb2_blacklist_cidrs,$_CONF['bb2_spambot_ip']);
	}
	if ( isset($_CONF['bb2_spambots_0'] ) && is_array($_CONF['bb2_spambots_0'] ) ) {
	    $bb2_spambots_0 = array_merge($bb2_spambots_0,$_CONF['bb2_spambots_0']);
	}
	if ( isset($_CONF['bb2_spambots'] ) && is_array($_CONF['bb2_spambots'] ) ) {
	    $bb2_spambots = array_merge($bb2_spambots,$_CONF['bb2_spambots']);
	}
	if ( isset($_CONF['bb2_spambots_regex'] ) && is_array($_CONF['bb2_spambots_regex'] ) ) {
		$cfg_bb2_spambots_regex = array();
		foreach($_CONF['bb2_spambots_regex'] AS $regex) {
			$cfg_bb2_spambots_regex[] = '/'.preg_quote($regex,'/').'/';
		}
	    $bb2_spambots_regex = array_merge($bb2_spambots_regex,$cfg_bb2_spambots_regex);
//	    $bb2_spambots_regex = array_merge($bb2_spambots_regex,$_CONF['bb2_spambots_regex']);
	}
	if ( isset($_CONF['bb2_spambots_url'] ) && is_array($_CONF['bb2_spambots_url'] ) ) {
	    $bb2_spambots_url = array_merge($bb2_spambots_url,$_CONF['bb2_spambots_url']);
	}
	if ( isset($_CONF['bb2_spambot_referer'] ) && is_array($_CONF['bb2_spambot_referer'] ) ) {
	    $bb2_spambot_refer = array_merge($bb2_spambot_refer,$_CONF['bb2_spambot_referer']);
	}

    $refer = '';
	@$ua = $package['headers_mixed']['User-Agent'];
	@$uri = $package['request_uri'];
	if ( isset($package['headers_mixed']['Referer'])) $refer = $package['headers_mixed']['Referer'];

	if (!empty($bb2_blacklist_cidrs)) {
		foreach ($bb2_blacklist_cidrs as $range) {
			if (match_cidr($package['ip'], $range)) return "96c0bd30";
		}
	}

	foreach ($bb2_spambots_0 as $spambot) {
		$pos = strpos($ua, $spambot);
		if ($pos !== FALSE && $pos == 0) {
			return "17f4e8c8";
		}
	}

	if ( $refer != '' ) {
    	foreach ($bb2_spambot_refer AS $spambot ) {
    	    if ( stripos($refer,$spambot) !== false) {
    	        return "17f4e8c9";
    	    }
    	}
    }

	foreach ($bb2_spambots as $spambot) {
		if (strpos($ua, $spambot) !== FALSE) {
			return "17f4e8c8";
		}
	}

	foreach ($bb2_spambots_regex as $spambot) {
		if (preg_match($spambot, $ua)) {
			return "17f4e8c8";
		}
	}

	foreach ($bb2_spambots_url as $spambot) {
		if (stripos($uri, $spambot) !== FALSE) {
			return "96c0bd40";
		}
	}
	return FALSE;
}

?>
