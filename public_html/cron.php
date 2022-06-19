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

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Article\Article;
use \glFusion\Log\Log;

// keep running after browser closes connection
@ignore_user_abort(true);

// check if user abort worked, if yes send output early
$defer = !@ignore_user_abort();

if(!$defer){
    sendGIF(); // send gif
}

Log::write('system',Log::DEBUG,"Starting CRON");

// Catch any possible output (e.g. errors)
ob_start();
COM_rdfUpToDateCheck();
CMT_updateCommentcodes();

$archivetid = \Topic::archiveID();

$db = Database::getInstance();

$params = [];
$types  = [];
$sql = "SELECT id,sid,tid,title,expire,statuscode FROM `{$_TABLES['stories']}`
         WHERE (expire <= NOW()) AND (statuscode = ?";

$params[] = Article::STORY_DELETE_ON_EXPIRE;
$types[]  = Database::INTEGER;

if (empty ($archivetid)) {
    $sql .= ")";
} else {
    $sql .= " OR statuscode = ?) AND tid != ?";
    $params[] = Article::STORY_ARCHIVE_ON_EXPIRE;
    $params[] = $archivetid;
    $types[]  = Database::INTEGER;
    $types[]  = Database::STRING;
}

$stmt = $db->conn->executeQuery($sql,$params,$types);

while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
    if ($row['statuscode'] == Article::STORY_ARCHIVE_ON_EXPIRE) {
        if (!empty ($archivetid) ) {
            Log::write('system',Log::INFO, sprintf("Archive Story: %s, Topic: %s, Title: %s, Expired: %s",
                                    $row['sid'],
                                    $archivetid,
                                    $row['title'],
                                    $row['expire']));

            $db->conn->update(
                $_TABLES['stories'],
                array('tid' => $archivetid, 'frontpage' => 0, 'featured' => 0),
                array('id'  => $row['id']),
                array(
                    Database::STRING,
                    Database::INTEGER,
                    Database::INTEGER,
                    Database::STRING
                )
            );

            $c = Cache::getInstance();
            $c->deleteItemsByTag('story_'.$row['sid']);
            $c->deleteItemsByTag('whatsnew');
            $c->deleteItemsByTag('menu');
        }
    } else if ($row['statuscode'] == Article::STORY_DELETE_ON_EXPIRE) {
        $token = SEC_createTokenGeneral('cron_'.md5($row['sid']),300);
        Log::write('system',Log::INFO, sprintf("Delete Story and comments: %s, Topic: %s, Title: %s, Expired: %s",
                                                    $row['sid'],
                                                    $row['tid'],
                                                    $row['title'],
                                                    $row['expire'])
        );
        Article::delete($row['sid'],true,$token);
        $c = Cache::getInstance();
        $c->deleteItemsByTags(array('story_'.$row['sid'],'whatsnew','menu'));
    }
}

// clean up sessions
$expirytime = (time() - 600);

$deleteSQL = "DELETE FROM `{$_TABLES['sessions']}` WHERE (start_time < ?)";
$stmt = $db->conn->prepare($deleteSQL);
$stmt->bindValue(1,$expirytime,Database::INTEGER);
try {
    $stmt->execute();
} catch (\Throwable $ignore) {}
// clean up remembered
$expireTime = (time() + 600);
$deleteSQL = "DELETE FROM `{$_TABLES['users_remembered']}` WHERE (exipres < ?)";
$stmt = $db->conn->prepare($deleteSQL);
$stmt->bindValue(1,$expireTime,Database::INTEGER);
try {
    $stmt->execute();
} catch (\Throwable $ignore) {}
// clean up throttle
$expireTime = (time() + 3600);
$deleteSQL = "DELETE FROM `{$_TABLES['users_throttling']}` WHERE (exipres_at < ?)";
$stmt = $db->conn->prepare($deleteSQL);
$stmt->bindValue(1,$expireTime,Database::INTEGER);
try {
    $stmt->execute();
} catch (\Throwable $ignore) {}
glFusion\Notifiers\Popup::expire(); // delete expired system messages
Log::write('system',Log::DEBUG,'Completed Clean up activities');

if ( $_CONF['cron_schedule_interval'] > 0  ) {
    if (( $_VARS['last_scheduled_run'] + $_CONF['cron_schedule_interval'] ) <= time()) {
        Log::write('system',Log::DEBUG,'Running last_scheduled_run items.');
        $db->conn->query("UPDATE `{$_TABLES['vars']}` SET value=UNIX_TIMESTAMP() WHERE name='last_scheduled_run'");
        Log::write('system',Log::DEBUG,'Running PLG_runScheduledTasks.');
        PLG_runScheduledTask();
    }
}
Log::write('system',Log::DEBUG,'Updating last_maint_run date / time');
$db->conn->query( "UPDATE `{$_TABLES['vars']}` SET value=UNIX_TIMESTAMP() WHERE name='last_maint_run'" );
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
