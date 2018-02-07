<?php
/**
* glFusion Cron controller
*
* @package    glFusion
* @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*
* Based on DokuWiki's indexer code
* @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
* @author     Andreas Gohr <andi@splitbrain.org>
*
*/

require_once 'lib-common.php';
session_write_close();  //close session

// keep running after browser closes connection
@ignore_user_abort(true);

// check if user abort worked, if yes send output early
$defer = !@ignore_user_abort();

if(!$defer){
    sendGIF(); // send gif
}

// Catch any possible output (e.g. errors)
ob_start();
COM_rdfUpToDateCheck();

// Scan for any stories that have expired and should be archived or deleted
$asql = "SELECT sid,tid,title,expire,statuscode FROM {$_TABLES['stories']} ";
$asql .= 'WHERE (expire <= NOW()) AND (statuscode = ' . STORY_DELETE_ON_EXPIRE;
if (empty ($archivetid)) {
    $asql .= ')';
} else {
    $asql .= ' OR statuscode = ' . STORY_ARCHIVE_ON_EXPIRE . ") AND tid != '".DB_escapeString($archivetid)."'";
}
$expiresql = DB_query ($asql);
while (list ($sid, $expiretopic, $title, $expire, $statuscode) = DB_fetchArray ($expiresql)) {
    if ($statuscode == STORY_ARCHIVE_ON_EXPIRE) {
        if (!empty ($archivetid) ) {
            COM_errorLOG("Archive Story: $sid, Topic: $archivetid, Title: $title, Expired: $expire");
            DB_query ("UPDATE {$_TABLES['stories']} SET tid = '".DB_escapeString($archivetid)."', frontpage = '0', featured = '0' WHERE sid='".DB_escapeString($sid)."'");
            $c = glFusion\Cache::getInstance();
            $c->deleteItemsByTag('story_'.$sid);
            $c->deleteItemsByTag('whatsnew');
            $c->deleteItemsByTag('menu');
        }
    } else if ($statuscode == STORY_DELETE_ON_EXPIRE) {
        COM_errorLOG("Delete Story and comments: $sid, Topic: $expiretopic, Title: $title, Expired: $expire");
        STORY_removeStory($sid);
        $c = glFusion\Cache::getInstance();
        $c->deleteItemsByTags(array('story_'.$sid,'whatsnew','menu'));
    }
}

if ( $_CONF['cron_schedule_interval'] > 0  ) {
    if (( $_VARS['last_scheduled_run'] + $_CONF['cron_schedule_interval'] ) <= time()) {
        DB_query( "UPDATE {$_TABLES['vars']} SET value=UNIX_TIMESTAMP() WHERE name='last_scheduled_run'" );
        PLG_runScheduledTask();
    }
}
DB_query( "UPDATE {$_TABLES['vars']} SET value=UNIX_TIMESTAMP() WHERE name='last_maint_run'" );
ob_end_clean();
if ($defer) sendGIF();

exit;

function sendGIF(){
    $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
    header('Content-Type: image/gif');
    header('Content-Length: '.strlen($img));
    header('Connection: Close');
    print $img;
    flush();
    // Browser should drop connection after this
    // Thinks it's got the whole image
}
