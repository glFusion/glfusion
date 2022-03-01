# glFusion v2 CMS ChangeLog

## v2.0.1

### Fixed

- story and story_introtext autotags did not honor the provided link text

## v2.0.0 (February 27, 2022)

### Changed

- glFusion v2 requires PHP v7.4 or newer
- Default location of notification messages is top-center for new installations
- FileMgmt Plugin internals reworked - improved template use, layout and overall code improvements (Lee)
- FileMgmt Plugin support url_rewrite on download links - if you need rewrite rules for your web server, implement for visit.php - see [glFusion URL Rewrite docs](https://www.glfusion.org/wiki/glfusion:configuration:site) for more details
- Syndication System internals reworked - improved overall code and modernized (Lee)
- Allow embedded [img] tag inside [url] tag in BBcode formatter
- Removed 'most' HTML from the code base and moved to templates
- Consolidate dynamic data under a single directory (actually two - private/data and public_html/data)
- Logview now initializes the log with date/time cleared
- Admin Actions now displays the IP
- Story Editor - display thumbnail of attached images
- Referencing a non-existent topic on the index page now results in a 404 error
- Completely rewritten story handler - including submission and presentation
- Configuration 'passwd' fields will now be encrypted in the DB
- glFusion will now remove a plugin entry from the Plugins table if the plugin's files are no longer available and it is selected for uninstall
- glFusion now requires a UTF-8 configuration
- Translation now handled on the glFusion CMS Crowdin site
- Removed all variable references from language files - now use sprintf() to build final string
- Comment system code has been reworked and optimized
- Consolidation of caching code into lib-cache / Cache.php class
- Maintenance / Cron task execution has been optimized to reduce user impact / experience
- Template class code significantly streamlined - utilizes new caching engine to allow for memory based caching of templates
- Replaced all old style Cache calls to new Cache class interface
- FileMgmt Admin screens now adhere to glFusion UI standards
- Password is required to change any user account profile fields - not needed for preference type settings
- Disabled autocomplete for TOTP (2FA) code input fields
- 2FA requires a valid TOTP to disable

### Added

- New moderation warning feature for the Forum plugin (Lee)
- Disallowed names - you can now set user names that are not allowed on the site for both registration and anonymous names
- Turkish Translation
- Updated Czech Translations
- New Feature Administration - see what feature / rights are associated to each group
- Forum plugin - select exit type - whether to show Page Not Found or Login Page when user does not have permissions to view a topic
- Simple DB search / replace capabilities
- Implemented direct link from Forum Likes
- Ability to include theme specific HTML attributes - such as data-uk-lightbox to the HTML filtering allowed attributes
- Czech Translations
- Added INTL extension to the environment checks - need to update wiki to show used by Agenda plugin
- Admin / Moderator actions are now logged and available for viewing in Command & Control
  - Configuration option to enable / disable - Config -> Miscellaneous -> Debug -> Enable Admin Actions
- {site_name} is now a default template var
- Allow { and } to be escaped in templates by using {{ and }} so {{x}} will become {x}
- Integrate Whoops debug console for development
- Implemented JavaScript based code formatter / highlighter
- .tm-story-text CSS style to story text in featured and standard story templates
- Allow configuration of default story edit mode (visual or html)
- SpamX Tester - allows admin to submit test content to see which service blocks
- New Formatter class - consolidates all display formatting into a single location
- New Caching system utilizing phpFastCache library
- Forum Likes - number of likes to display is now a configurable item

### Depreciated

- Depreciated the $LANG_configselects() language array
- Non-UTF-8 language support
- Calendar plugin is now depreciated - alternatives include Agenda and evList

### Removed

- Removed all Content Syndication configuration fields as they are redundant. All Content Syndication settings are configured per feed in the Content Syndication admin.
- The ability to exclude content - users had the option to select authors, topics and blocks they did not want to view - this was disabled by default - this capability has now been fully removed
- Removed mediaelement.js - now use native HTML 5 audio / video
- Removed all Flash / SWF / FLV media support in Media Gallery
- All MooTools related widgets
- Remove the submission related Plugin APIs - except the Moderation Queue APIs
      PLG_showSubmitForm()
- Configuration Option to show draft stories in moderation queue
- Removed GeShi code formatter
- Removed comment feeds plugin - functionality integrated into core
- CAPTCHA Plugin: removed static images and ImageMagick support

### Fixed

- Potential XSS Reflected Vulnerability reported by nu11secu1ty
- Media Gallery Media Manage and Caption screens did not honor album sort order
- Back button on Group Admin would re-submit the form
- PHP v8 Compatibility Fixes
- Error changing article SID

### Security

- Utilize PDO prepared SQL statements to decrease SQL injection opportunities
