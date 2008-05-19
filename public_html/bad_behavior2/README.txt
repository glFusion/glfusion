=== Bad Behavior ===
Tags: comment,trackback,referrer,spam,robot,antispam
Contributors: error, MarkJaquith, Firas, skeltoac
Donate link: http://www.bad-behavior.ioerror.us/
Requires at least: 1.2
Tested up to: 2.3.2
Stable tag: 2.0.13

Bad Behavior is a set of PHP scripts which prevents spambots and other
malicious accesses to your PHP-based Web site. It prevents comment spam,
trackback spam, guestbook spam, wiki spam, referrer spam, and some types
of malicious Web site hacking.

== Installation ==

*Warning*: If you are upgrading from a 1.x.x version of Bad Behavior,
you must remove it from your system entirely, and delete all of its
database tables, before installing Bad Behavior 2.0.x. You do not need
to remove a 2.0.x version of Bad Behavior before upgrading to this
release.

Bad Behavior has been designed to install on each host software in the
manner most appropriate to each platform. It's usually sufficient to
follow the generic instructions for installing any plugin or extension
for your host software.

On MediaWiki, it is necessary to add a second line to LocalSettings.php
when installing the extension. Your LocalSettings.php should include
the following:

`	include_once( 'includes/DatabaseFunctions.php' );
	include( './extensions/Bad-Behavior/bad-behavior-mediawiki.php' );

For complete documentation and installation instructions, please visit
http://www.bad-behavior.ioerror.us/

== Warning ==

The WordPress-hosted copy of Bad Behavior should never be used in
conjunction with Dave's Spam Karma plugin. If you intend to use Spam
Karma and Bad Behavior together, always use the official copy from the
Bad Behavior home page at http://www.bad-behavior.ioerror.us/download/ .

The WordPress-hosted copy of Bad Behavior is unofficial and updated
only when a major bug or security fix is available. To ensure that you
always have the latest available release, always use the official copy
from the Bad Behavior home page at http://www.bad-behavior.ioerror.us/ .

== Release Notes ==

= Bad Behavior 2.0 Known Issues =

* Bad Behavior may be unable to protect cached pages on MediaWiki.

* On WordPress when using WordPress Advanced Cache (WP-Cache), Bad Behavior
requires a patch to WP-Cache 2 in order to protect cached pages.

  Edit the wp-content/plugins/wp-cache/wp-cache-phase1.php file and find the
following two lines at around line 32:

`	if (! ($meta = unserialize(@file_get_contents($meta_pathname))) )
		return;`

  Immediately after this, insert the following line:

`	require_once( ABSPATH .  'wp-content/plugins/Bad-Behavior/bad-behavior-generic.php');`

  Then visit your site. Everything should work normally, but spammers will
not be able to access your cached pages either.
