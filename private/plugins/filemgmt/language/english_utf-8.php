<?php
/**
* glFusion CMS
*
* UTF-8 Language File for FileMgt Plugin
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2021 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*  Based on prior work Copyright (C) 2004 by the following authors:
*   Consult4Hire Inc.
*   Blaine Lang  - blaine AT portalparts DOT com
*
*/

if (!defined ('GVERSION')) {
    die ('This file cannot be used on its own.');
}

$LANG_FM00 = array (
    'access_denied'     => 'Access Denied',
    'access_denied_msg' => 'Only Root Users have Access to this Page.  Your user name and IP have been recorded.',
    'admin'             => 'Plugin Admin',
    'install_header'    => 'Install/Uninstall Plugin',
    'installed'         => 'The Plugin and Block are now installed,<p><i>Enjoy,<br><a href="MAILTO:support@glfusion.org">glFusion Team</a></i>',
    'uninstalled'       => 'The Plugin is Not Installed',
    'install_success'   => 'Installation Successful<p><b>Next Steps</b>:
        <ol><li>Use the Filemgmt Admin to complete the plugin configuration</ol>
        <p>Review the <a href="%s">Install Notes</a> for more information.',
    'install_failed'    => 'Installation Failed -- See your error log to find out why.',
    'uninstall_msg'     => 'Plugin Successfully Uninstalled',
    'install'           => 'Install',
    'uninstall'         => 'UnInstall',
    'editor'            => 'Plugin Editor',
    'warning'           => 'De-Install Warning',
    'enabled'           => '<p style="padding: 15px 0px 5px 25px;">Plugin is installed and enabled.<br>Disable first if you want to De-Install it.</p><div style="padding:5px 0px 5px 25px;"><a href="'.$_CONF['site_admin_url'].'/plugins.php">Plugin Editor</a></div',
    'WhatsNewLabel'     => 'Files',
    'WhatsNewPeriod'    => ' last %s days',
    'new_upload'        => 'New File submitted at ',
    'new_upload_body'   => 'A new file has been submitted to the upload queue at ',
    'details'           => 'File Details',
    'filename'          => 'Filename',
    'uploaded_by'       => 'Uploaded By',
    'not_found'         => 'Download Not Found',
);

// Admin Navbar
$LANG_FM02 = array(
    'instructions' => 'To modify or delete a file, click on the files\'s edit icon below. To view or modify categories, select the Categories option above.',
    'nav1'  => 'Settings',
    'nav2'  => 'Categories',
    'nav3'  => 'Add File',
    'nav4'  => 'Downloads (%s)',
    'nav5'  => 'Broken Files (%s)',
    'edit'  => 'Edit',
    'file'  => 'Filename',
    'category' => 'Category Name',
    'version' => 'Version',
    'size'  => 'Size',
    'date' => 'Date',
);

$LANG_FILEMGMT = array(
    'newpage'               => "New Page",
    'adminhome'             => "Admin Home",
    'plugin_name'           => "File Management",
    'searchlabel'           => "Downloads",
    'searchlabel_results'   => "Downloads Results",
    'downloads'             => "Downloads",
    'report'                => "Top Downloads",
    'usermenu1'             => "Downloads",
    'usermenu2'             => "&nbsp;&nbsp;Top Rated",
    'usermenu3'             => "Upload a file",
    'admin_menu'            => "Filemgmt Admin",
    'writtenby'             => "Written By",
    'date'                  => "Last Updated",
    'title'                 => "Title",
    'content'               => "Content",
    'hits'                  => "Hits",
    'Filelisting'           => "File Listing",
    'DownloadReport'        => "Download History for single file",
    'StatsMsg1'             => "Top Ten Accessed Files in Repository",
    'StatsMsg2'             => "It appears there are no files defined for the filemgmt plugin on this site or no one has ever accessed them.",
    'usealtheader'          => "Use Alt. Header",
    'url'                   => "URL",
    'edit'                  => "Edit",
    'lastupdated'           => "Last Updated",
    'pageformat'            => "Page Format",
    'leftrightblocks'       => "Left & Right Blocks",
    'blankpage'             => "Blank Page",
    'noblocks'              => "No Blocks",
    'leftblocks'            => "Left Blocks",
    'addtomenu'             => 'Add To Menu',
    'label'                 => 'Label',
    'nofiles'               => 'Number of files in our repository (Downloads)',
    'save'                  => 'Save',
    'preview'               => 'Preview',
    'delete'                => 'Delete',
    'cancel'                => 'Cancel',
    'access_denied'         => 'Access Denied',
    'invalid_install'       => 'Someone has tried to illegally access the File Management install/uninstall page.  User id: ',
    'start_install'         => 'Attempting to install the Filemgmt Plugin',
    'start_dbcreate'        => 'Attempting to create tables for Filemgmt plugin',
    'install_skip'          => '... skipped as per filemgmt.cfg',
    'access_denied_msg'     => 'You are illegally trying access the File Mgmt administration pages.  Please note that all attempts to illegally access this page are logged',
    'installation_complete' => 'Installation Complete',
    'installation_complete_msg' => 'The data structures for the File Mgmt plugin for glFusion have been successfully installed into your database!  If you ever need to uninstall this plugin, please read the README document that came with this plugin.',
    'installation_failed'   => 'Installation Failed',
    'installation_failed_msg' => 'The installation of the File Mgmt plugin failed.  Please see your glFusion error.log file for diagnostic information',
    'system_locked'         => 'System Locked',
    'system_locked_msg'     => 'The File Mgmt plugin has already been installed and is locked.  If you are trying to uninstall this plugin, please read the README document that shipped with this plugin',
    'uninstall_complete'    => 'Uninstall Complete',
    'uninstall_complete_msg' => 'The datastructures for the File Mgmt plugin have been successfully removed from your glFusion database<br><br>You will need to manually remove all files in your file repository.',
    'uninstall_failed'      => 'Uninstall Failed.',
    'uninstall_failed_msg'  => 'The uninstall of the File Mgmt plugin failed.  Please see your glFusion error.log file for diagnostic information',
    'install_noop'          => 'Plugin Install',
    'install_noop_msg'      => 'The filemgmt plugin install executed but there was nothing to do.<br><br>Check your plugin install.cfg file.',
    'all_html_allowed'      => 'All HTML is allowed',
    'no_new_files'          => 'No new files',
    'no_comments'           => 'No new comments',
    'more'                  => '<em>more ...</em>',
    'newly_uploaded'        => 'Newly Uploaded',
    'click_to_view'         => 'Click here to view',
    'no_file_uploaded'      => 'No File Uploaded',
    'description'           => 'Description',
    'category'              => 'Category',
    'err_req_fields'        => 'Some required fields were not supplied',
    'go_back'               => 'Go Back',
    'err_demomode'          => 'Uploads are disabled in demo mode',
    'edit_category'         => 'Edit Category',
    'create_category'       => 'Create Category',
    'can_view'              => 'Can View',
    'can_upload'            => 'Can Upload',
    'delete_category'       => 'Delete Category',
    'new_category'          => 'New Category',
    'new_file'              => 'New File',
    'remote_ip'             => 'Remote IP',
    'back_to_listing'       => 'Back To Listing',
    'remote_url'            => 'Remote URL',
    'file_missing'          => 'File Missing',
);

$LANG_FILEMGMT_ERRORS = array(
    "1101" => "Upload approval Error: The temporary file was not found. Check error.log",
    "1102" => "Upload submit Error: The temporary filestore file was not created. Check error.log",
    "1103" => "The download info you provided is already in the database!",
    "1104" => "The download info was not complete - Need to enter a title for the new file",
    "1105" => "The download info was not complete - Need to enter a description for the new file",
    "1106" => "Upload Add Error: The new file was not created. Check error.log",
    "1107" => "Upload Add Error: The temporary file was not found. Check error.log",
    "1108" => "Duplicate file - already existing in filestore",
    "1109" => "File type not allowed",
    "1110" => "You must define and select a category for the uploaded file",
    "1111" => "File Size exceeds site configured maximum size of %s",
    "9999" => "Unknown Error"
);

$LANG_FILEMGMT_AUTOTAG = array(
    'desc_file'                 => 'Link: to a File download detail page.  link_text defaults to the file title. usage: [file:<i>file_id</i> {link_text}]',
    'desc_file_download'        => 'Link: to a direct File download.  link_text defaults to the file title. usage: [file_download:<i>file_id</i> {link_text}]',
);


// Localization of the Admin Configuration UI
$LANG_configsections['filemgmt'] = array(
    'label'                 => 'FileMgmt',
    'title'                 => 'FileMgmt Configuration'
);
$LANG_confignames['filemgmt'] = array(
    'whatsnew'              => 'Enable WhatsNew Listing',
    'perpage'               => 'Displayed Downloads per Page',
    'popular_download'      => 'Hits to be Popular',
    'newdownloads'          => 'Number of Downloads as New on Top Page',
    'trimdesc'              => 'Trim File Descriptions in Listing',
    'dlreport'              => 'Restrict Access to Download Report',
    'selectpriv'            => 'Restrict Access to Group \'Logged-In Users\' Only',
    'uploadselect'          => 'Allow Logged-In Uploads',
    'uploadpublic'          => 'Allow Anonymous Uploads',
    'useshots'              => 'Display Category Images',
    'shotwidth'             => 'Thumbnail Img Width',
    'Emailoption'           => 'Email Submitter if File Approved',
    'FileStore'             => 'Directory to Store Files',
    'SnapStore'             => 'Directory to Store File Thumbnails',
    'SnapCat'               => 'Directory to Store Category Thumbnails',
    'FileStoreURL'          => 'URL to Files',
    'FileSnapURL'           => 'URL to File Thumbnails',
    'SnapCatURL'            => 'URL to Category Thumbnails',
    'whatsnewperioddays'    => 'What\'s New Days',
    'whatsnewtitlelength'   => 'What\'s New Title Length',
    'showwhatsnewcomments'  => 'Show Comments in What\'s New Block',
    'numcategoriesperrow'   => 'Categories per Row',
    'numsubcategories2show' => 'Sub Categories per Row',
    'outside_webroot'       => 'Store Files Outside Web Root',
    'enable_rating'         => 'Enable Ratings',
    'displayblocks'         => 'Display glFusion Blocks',
    'silent_edit_default'   => 'Silent Edit Default',
    'extensions_map'        => 'Extensions used for downloads',
    'EmailOption'           => 'Email submitter upon approval?',
);
$LANG_configsubgroups['filemgmt'] = array(
    'sg_main'               => 'Main Settings'
);
$LANG_fs['filemgmt'] = array(
    'fs_public'             => 'Public FileMgmt Settings',
    'fs_admin'              => 'FileMgmt Admin Settings',
    'fs_permissions'        => 'Default Permissions',
    'fm_access'             => 'FileMgmt Access Control',
    'fm_general'            => 'FileMgmt General Settings',
);
// Note: entries 0, 1 are the same as in $LANG_configselects['Core']
$LANG_configSelect['filemgmt'] = array(
    0 => array(1=>'True', 0=>'False'),
    1 => array(true=>'True', false=>'False'),
    2 => array(5 => ' 5', 10 => '10', 15 => '15', 20 => '20', 25 => '25',30 => '30',50 => '50'),
    3 => array(0=>'Left Blocks', 1=>'Right Blocks', 2=>'Left & Right Blocks', 3=>'None')
);

$PLG_filemgmt_MESSAGE1 = 'Filemgmt Plugin Install Aborted<br>File: plugins/filemgmt/filemgmt.php is not writeable';
$PLG_filemgmt_MESSAGE3 = 'This plugin requires glFusion Version 1.0 or greater, upgrade aborted.';
$PLG_filemgmt_MESSAGE4 = 'Plugin version 1.5 code not detected - upgrade aborted.';
$PLG_filemgmt_MESSAGE5 = 'Filemgmt Plugin Upgrade Aborted<br>Current plugin version is not 1.3';

// Language variables used by the plugin - general users access code.

define("_MD_THANKSFORINFO","Thanks for the information. We'll look into your request shortly.");
define("_MD_BACKTOTOP","Back to Downloads Top");
define("_MD_THANKSFORHELP","Thank you for helping to maintain this directory's integrity.");
define("_MD_FORSECURITY","For security reasons your user name and IP address will also be temporarily recorded.");

define("_MD_SEARCHFOR","Search for");
define("_MD_MATCH","Match");
define("_MD_ALL","ALL");
define("_MD_ANY","ANY");
define("_MD_NAME","Name");
define("_MD_DESCRIPTION","Description");
define("_MD_SEARCH","Search");

define("_MD_MAIN","Main");
define("_MD_SUBMITFILE","Submit File");
define("_MD_POPULAR","Popular");
define("_MD_POP", "Pop");   // abbrevision for listing badge
define("_MD_NEW","New");
define("_MD_TOPRATED","Top Rated");

define("_MD_NEWTHISWEEK","New this week");
define("_MD_UPTHISWEEK","Updated this week");

define("_MD_POPULARITYLTOM","Popularity (Least to Most Hits)");
define("_MD_POPULARITYMTOL","Popularity (Most to Least Hits)");
define("_MD_TITLEATOZ","Title (A to Z)");
define("_MD_TITLEZTOA","Title (Z to A)");
define("_MD_DATEOLD","Date (Old Files Listed First)");
define("_MD_DATENEW","Date (New Files Listed First)");
define("_MD_RATINGLTOH","Rating (Lowest Score to Highest Score)");
define("_MD_RATINGHTOL","Rating (Highest Score to Lowest Score)");

define("_MD_NOSHOTS","No Thumbnails Available");
define("_MD_EDITTHISDL","Edit This Download");

define("_MD_LISTINGHEADING","<b>File Listing: There are %s files in our database</b>");
define("_MD_LATESTLISTING","Latest Listing");
define("_MD_DESCRIPTIONC","Description:");
define("_MD_EMAILC","Email: ");
define("_MD_CATEGORYC","Category: ");
define("_MD_LASTUPDATEC","Last Update: ");
define("_MD_DLNOW","Download Now!");
define("_MD_VERSION","Ver");
define("_MD_SUBMITDATE","Date");
define("_MD_DLTIMES","Downloaded %s times");
define("_MD_FILESIZE","File Size");
define("_MD_SUPPORTEDPLAT","Supported Platforms");
define("_MD_HOMEPAGE","Home Page");
define("_MD_HITSC","Hits: ");
define("_MD_RATINGC","Rating: ");
define("_MD_ONEVOTE","1 vote");
define("_MD_NUMVOTES","(%s)");
define("_MD_NOPOST","N/A");
define("_MD_NUMPOSTS","%s votes");
define("_MD_COMMENTSC","Comments: ");
define ("_MD_ENTERCOMMENT", "Create first comment");
define("_MD_RATETHISFILE","Rate this File");
define("_MD_MODIFY","Modify");
define("_MD_REPORTBROKEN","Report Broken File");
define("_MD_TELLAFRIEND","Tell a Friend");
define("_MD_VSCOMMENTS","View/Send Comments");
define("_MD_EDIT","Edit");

define("_MD_THEREARE","There are %s files in our database");
define("_MD_LATESTLIST","Latest Listings");

define("_MD_REQUESTMOD","Request Download Modification");
define("_MD_FILE","File");
define("_MD_FILEID","File ID: ");
define("_MD_FILETITLE","Title: ");
define("_MD_DLURL","Download URL: ");
define("_MD_HOMEPAGEC","Home Page: ");
define("_MD_VERSIONC","Version: ");
define("_MD_FILESIZEC","File Size: ");
define("_MD_NUMBYTES","%s bytes");
define("_MD_PLATFORMC","Platform: ");
define("_MD_CONTACTEMAIL","Contact Email: ");
define("_MD_SHOTIMAGE","Thumbnail Img: ");
define("_MD_SENDREQUEST","Send Request");

define("_MD_VOTEAPPRE","Your vote is appreciated.");
define("_MD_THANKYOU","Thank you for taking the time to vote here at %s"); // %s is your site name
define("_MD_VOTEFROMYOU","Input from users such as yourself will help other visitors better decide which file to download.");
define("_MD_VOTEONCE","Please do not vote for the same resource more than once.");
define("_MD_RATINGSCALE","The scale is 1 - 10, with 1 being poor and 10 being excellent.");
define("_MD_BEOBJECTIVE","Please be objective, if everyone receives a 1 or a 10, the ratings aren't very useful.");
define("_MD_DONOTVOTE","Do not vote for your own resource.");
define("_MD_RATEIT","Rate It!");

define("_MD_INTFILEAT","Interesting Download File at %s"); // %s is your site name
define("_MD_INTFILEFOUND","Here is an interesting download file I have found at %s"); // %s is your site name

define("_MD_RECEIVED","We received your download information. Thanks!");
define("_MD_WHENAPPROVED","You&apos;ll receive an E-mail when it&apos;s approved.");
define("_MD_SUBMITONCE","Submit your file/script only once.");
define("_MD_APPROVED", "Your file has been approved");
define("_MD_ALLPENDING","All file/script information are posted pending verification.");
define("_MD_DONTABUSE","Username and IP are recorded, so please don't abuse the system.");
define("_MD_TAKEDAYS","It may take several days for your file/script to be added to our database.");
define("_MD_FILEAPPROVED", "Your file has been added to the file repository");

define("_MD_RANK","Rank");
define("_MD_CATEGORY","Category");
define("_MD_HITS","Hits");
define("_MD_RATING","Rating");
define("_MD_VOTE","Vote");

define("_MD_SEARCHRESULT4","Search results for <b>%s</b>:");
define("_MD_MATCHESFOUND","%s matche(s) found.");
define("_MD_SORTBY","Sort by:");
define("_MD_TITLE","Title");
define("_MD_DATE","Date");
define("_MD_POPULARITY","Popularity");
define("_MD_CURSORTBY","Files currently sorted by: ");
define("_MD_FOUNDIN","Found in:");
define("_MD_PREVIOUS","Previous");
define("_MD_NEXT","Next");
define("_MD_NOMATCH","No matches found to your query");

define("_MD_TOP10","%s Top 10"); // %s is a downloads category name
define("_MD_CATEGORIES","Categories");

define("_MD_SUBMIT","Submit");
define("_MD_CANCEL","Cancel");

define("_MD_BYTES","Bytes");
define("_MD_ALREADYREPORTED","You have already submitted a broken report for this resource.");
define("_MD_MUSTREGFIRST","Sorry, you don't have the permission to perform this action.<br>Please register or login first!");
define("_MD_NORATING","No rating selected.");
define("_MD_CANTVOTEOWN","You cannot vote on the resource you submitted.<br>All votes are logged and reviewed.");

// Language variables used by the plugin - Admin code.

define("_MD_RATEFILETITLE","Record your file rating");
define("_MD_ADMINTITLE","File Management Administration");
define("_MD_UPLOADTITLE","File Management - Add new file");
define("_MD_CATEGORYTITLE","File Listing - Category View");
define("_MD_DLCONF","Downloads Configuration");
define("_MD_GENERALSET","Configuration Settings");
define("_MD_ADDMODFILENAME","Add new file");
define ("_MD_ADDCATEGORYSNAP", 'Optional Image<div style="font-size:8pt;">Top Level Categories only</div>');
define ("_MD_ADDIMAGENOTE", '<span style="font-size:8pt;">Image height will be resized to 50</span>');
define("_MD_ADDMODCATEGORY","<b>Categories:</b> Add, Modify, and Delete Categories");
define("_MD_DLSWAITING","Downloads Waiting for Validation");
define("_MD_BROKENREPORTS","Broken File Reports");
define("_MD_MODREQUESTS","Download Info Modification Requests");
define("_MD_EMAILOPTION","Email submitter if file approved: ");
define("_MD_COMMENTOPTION","Enable comments:");
define("_MD_SUBMITTER","Submitter: ");
define("_MD_DOWNLOAD","Download");
define("_MD_FILELINK","File Link");
define("_MD_SUBMITTEDBY","Submitted by: ");
define("_MD_APPROVE","Approve");
define("_MD_DELETE","Delete");
define("_MD_NOSUBMITTED","No New Submitted Downloads.");
define("_MD_ADDMAIN","Add MAIN Category");
define("_MD_TITLEC","Title: ");
define("_MD_CATSEC", "View Access");
define("_MD_UPLOADSEC", "Upload Access");
define("_MD_IMGURL","<br>Image Filename <font size='-2'> (located in your filemgmt_data/category_snaps directory - Image height will be resized to 50)</font>");
define("_MD_ADD","Add");
define("_MD_ADDSUB","Add SUB-Category");
define("_MD_IN","in");
define("_MD_ADDNEWFILE","Add New File");
define("_MD_MODCAT","Modify Category");
define("_MD_MODDL","Modify Download Info");
define("_MD_USER","User");
define("_MD_IP","IP Address");
define("_MD_USERAVG","User AVG Rating");
define("_MD_TOTALRATE","Total Ratings");
define("_MD_NOREGVOTES","No Registered User Votes");
define("_MD_NOUNREGVOTES","No Unregistered User Votes");
define("_MD_VOTEDELETED","Vote data deleted.");
define("_MD_NOBROKEN","No reported broken files.");
define("_MD_IGNOREDESC","Ignore (Ignores the report and only deletes this reported entry</b>)");
define("_MD_DELETEDESC","Delete (Deletes <b>the reported file entry in the repository</b> but not the actual file)");
define("_MD_REPORTER","Report Sender");
define("_MD_FILESUBMITTER","File Submitter");
define("_MD_IGNORE","Ignore");
define("_MD_FILEDELETED","File Deleted.");
define("_MD_FILENOTDELETED","Record was removed but File was not Deleted.<p>More then 1 record pointing to same file.");
define("_MD_BROKENDELETED","Broken file report deleted.");
define("_MD_USERMODREQ","User Download Info Modification Requests");
define("_MD_ORIGINAL","Original");
define("_MD_PROPOSED","Proposed");
define("_MD_OWNER","Owner: ");
define("_MD_NOMODREQ","No Download Modification Request.");
define("_MD_DBUPDATED","Database Updated Successfully!");
define("_MD_MODREQDELETED","Modification Request Deleted.");
define("_MD_IMGURLMAIN",'Image<div style="font-size:8pt;">Image height will be resized to 50px</div>');
define("_MD_PARENT","Parent Category");
define("_MD_SAVE","Save Changes");
define("_MD_CATDELETED","Category Deleted.");
define("_MD_WARNING","WARNING: Are you sure you want to delete this Category and ALL its Files and Comments?");
define("_MD_YES","Yes");
define("_MD_NO","No");
define("_MD_NEWCATADDED","New Category Added Successfully!");
define("_MD_CONFIGUPDATED","New configuration saved");
define("_MD_ERROREXIST","ERROR: The download info you provided is already in the database!");
define("_MD_ERRORNOFILE","ERROR: File not found on record in the database!");
define("_MD_ERRORTITLE","ERROR: You need to enter TITLE!");
define("_MD_ERRORDESC","ERROR: You need to enter DESCRIPTION!");
define("_MD_NEWDLADDED","New download added to the database.");
define("_MD_NEWDLADDED_DUPFILE","Warning: Duplicate File. New download added to the database.");
define("_MD_NEWDLADDED_DUPSNAP","Warning: Duplicate Snap. New download added to the database.");
define("_MD_DLUPDATED", "File has been updated.");
define("_MD_HELLO","Hello %s");
define("_MD_WEAPPROVED","We approved your download submission to our downloads section. The file name is: ");
define("_MD_THANKSSUBMIT","Thanks for your submission!");
define("_MD_UPLOADAPPROVED","Your uploaded file was approved");
define("_MD_DLSPERPAGE","Displayed Downloads per Page: ");
define("_MD_HITSPOP","Hits to be Popular: ");
define("_MD_DLSNEW","Number of Downloads as New on Top Page: ");
define("_MD_DLSSEARCH","Number of Downloads in Search Results: ");
define("_MD_TRIMDESC","Trim File Descriptions in Listing: ");
define("_MD_DLREPORT","Restrict access to Download report");
define("_MD_WHATSNEWDESC","Enable WhatsNew Listing");
define("_MD_SELECTPRIV","Restrict access to group 'Logged-In Users' only: ");
define("_MD_ACCESSPRIV","Enable Anonymous access: ");
define("_MD_UPLOADSELECT","Allow Logged-In uploads: ");
define("_MD_UPLOADPUBLIC","Allow Anonymous uploads: ");
define("_MD_USESHOTS","Display Category Images: ");
define("_MD_IMGWIDTH","Thumbnail Img Width: ");
define("_MD_MUSTBEVALID","Thumbnail image must be a valid image file under %s directory (ex. shot.gif). Leave it blank if no image file.");
define("_MD_REGUSERVOTES","Registered User Votes (total votes: %s)");
define("_MD_ANONUSERVOTES","Anonymous User Votes (total votes: %s)");
define("_MD_YOURFILEAT","Your file submitted at %s"); // this is an approved mail subject. %s is your site name
define("_MD_VISITAT","Visit our downloads section at %s");
define("_MD_DLRATINGS","Download Rating (total votes: %s)");
define("_MD_CONFUPDATED","Configuration Updated Successfully!");
define("_MD_NOFILES","No Files Found");
define("_MD_APPROVEREQ","* Upload needs to be approved in this category");
define("_MD_REQUIRED","* Required field");
define("_MD_SILENTEDIT","Silent Edit: ");

// Additional glFusion Defines
define("_MD_NOVOTE","Not rated yet");
define("_IFNOTRELOAD","If the page does not automatically reload, please click <a href=\"%s\">here</a>");
define("_GL_ERRORNOACCESS","ERROR: No access to this Document Repository Section");
define("_GL_ERRORNOUPLOAD","ERROR: You do not have upload privilages");
define("_GL_ERRORNOADMIN","ERROR: This function is restricted");
define("_GL_NOUSERACCESS","does not have access to the Document Repository");
define("_MD_ERRUPLOAD","Filemgmt: Unable to upload - check permissions for the file store directories");
define("_MD_DLFILENAME","Filename: ");
define("_MD_REPLFILENAME","Replacement  File: ");
define("_MD_SCREENSHOT","Screenshot");
define("_MD_SCREENSHOT_NA",'&nbsp;');
define("_MD_COMMENTSWANTED","Comments are appreciated");
define("_MD_CLICK2SEE","Click to see: ");
define("_MD_CLICK2DL","Click to download: ");
define("_MD_ORDERBY","Order By: ");
