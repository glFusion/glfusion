Please view this file on the master branch, on stable branches it's out of date.

v 1.5.0 (unreleased)
  - Implemented plugin version dependency check - Lee
  - Updated UIKIT to v2.13.1
  - Fixed issue with What's new block duplicating data
  - Disable exist check on auto tag upload
  - Implement og:image tag for stories
  - Implemented the ability to merge remote to local users from the user preference screen.
  - Implemented PLG_moveUser() to support account merging
  - Allow remote authenticated users to be moderated (queued)
  - Rewrote all processes using PEAR HTTP Request2 to use the new http class
  - Update 3rd party libraries to latest releases:
     - geshi Library
     - getID3
     - htmLawed
     - http class
     - oauth class
  - Static Pages - fixed autotags so they do not override the page title
  - Implemented HTML filter debug option
  - Fixed user submitted story form to show allowed items at the bottom of the form
  - Bumped MySQL requirement to 5.0.1 as the minimum
  - Bumped PHP requirement to v5.3 as the minimum
  - New 'default' theme - based on Nouveau but uses jQuery as JS engine
  - Revamped how scripts are loaded...
  - Implemented new jQuery widgets
  - Fixed error in youtube autotag where it would log parse errors
  - Migrate all Mootools functionality into the Nouveau theme
  - Reworked how embedded story images are handled
  - Cleaned up allowed html / allowed auto tag display on story and comment entry
  - Comment edit now takes you directly to the comment entry area
  - HTML filter now allows you to specific both element and attributes
  - Privacy policy and Terms of use now implemented as static pages
  - Support for PHP v5.3+ unlimited post size

v 1.4.4 (unreleased) 
  - Fixed error where Static Pages comments did not show up in search.
  - Fixed issue where editing a comment on a plugin would cause a 404 error on save.
  - Fixed a search error that would trigger on certain search words.

v 1.4.3
  - CAPTCHA Plugin - Added mathmatical captcha
  - Forum - Fixed error where forum ranking did not always display properly.
  - SpamX - Add Allow TOR IP configuration option
  - CAPTCHA Plugin - Removed PICATCHA since the service is no longer supporting new signups.
  - CAPTCHA Plugin - Added Are You Human game support
  - Media Gallery - Prevent moving images to root album
  - Added new configuration parameter to set minimum username length
  - Links Plugin - update root category in database when changed via online configuration
  - Implemented option to disable instance caching
  - Added og:image meta data to articles
  - Fixed error that prevented batch user uploads.
  - Fixed error in Batch User Admin where short term user option did not work
  - Updated CKEditor to v4.4.4
  - Forum - Fixed issue where a required JS files was not loaded when wysiwyg editor was selected
  - Static Pages - Do not override the page title when static page content is provided via auto tag
  - Media Gallery - implemented a fix where data was being written to an un-initialized object causing an error on PHP v5.4+ systems.
  - Cleaned up the style sheet caching logic and implemented improvements to prevent corrupt cache files.
  - Implemented file locking when creating the style cache file to ensure multiple instances do not cause a file write error
