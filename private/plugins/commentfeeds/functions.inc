<?php
// +--------------------------------------------------------------------------+
// | CommentFeeds Plugin - glFusion CMS                                       |
// +--------------------------------------------------------------------------+
// | functions.inc                                                            |
// |                                                                          |
// | Integration functions to glFusion                                        |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Author:                                                                  |
// | Michael Jervis <mike AT fuckingbrit DOT com>                             |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
 * This file does one thing: Implement Syndication extensions for comments
 *
 * @package Comment Feeds
 * @filesource
 * @version 1.0
 * @since GL 1.4.1
 * @copyright Copyright &copy; 2006
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Michael Jervis <mike AT fuckingbrit DOT com>
 *
 */

require_once $_CONF['path'].'plugins/commentfeeds/config.php';
require_once $_CONF['path'].'plugins/commentfeeds/commentfeeds.php';

// +---------------------------------------------------------------------------+
// | glFusion Plugin API Implementation                                        |
// +---------------------------------------------------------------------------+

/**
* Called by the plugin Editor to display the current plugin code version
* This may be different than the version installed and registered currently.
* If newer then you may want to run the update
*
* @return   string  version number
*
*/
function plugin_chkVersion_commentfeeds ()
{
    global $_CF_CONF;

    return $_CF_CONF['pi_version'];
}


/**
* Do we support feeds?
*
* @return   array   id/name pairs of all supported feeds
*
*/
function plugin_getfeednames_commentfeeds()
{
    global $_TABLES, $_PLUGIN_INFO;

    $feeds = array ();

    $feeds[] = array ('id' => 'all', 'name' => 'All Comments');
    $feeds[] = array ('id' => 'article', 'name' => 'Stories');
    foreach ( $_PLUGIN_INFO as $plugin ) {
        if ( $plugin['pi_enabled']  == 1 ) {
            if ( function_exists('plugin_commentsupport_'.$plugin['pi_name']) ) {
                $function = 'plugin_commentsupport_'.$plugin['pi_name'];
                if ( $function() == true ) {
                    $feeds[] = array('id' => $plugin['pi_name'], 'name' => ucfirst($plugin['pi_name']));
                }
            }
        }
    }
    return $feeds;
}

/**
* Provide feed data
*
* @param    int     $feed       feed ID
* @param    ref     $link
* @param    ref     $update
* @return   array               feed entries
*
*/
function plugin_getfeedcontent_commentfeeds ($feed, &$link, &$update)
{
    global $_CONF, $_TABLES, $_CF_CONF;

    $result = DB_query( "SELECT topic,limits,content_length FROM {$_TABLES['syndication']} WHERE fid = '".DB_escapeString($feed)."'" );
    $S = DB_fetchArray( $result );

    $sql = "SELECT * FROM {$_TABLES['comments']} where queued = 0 ";

    if( $S['topic'] != 'all' ) {
        $sql .= " AND type='{$S['topic']}' ";
    }
    $sql .= " ORDER BY date DESC ";

    $result = DB_query($sql);
    $counter = 0;
    while ( ( $row = DB_fetchArray($result)) != NULL ) {
        $itemInfo = PLG_getItemInfo($row['type'],$row['sid'],'url,perms',1);
        if ( isset($itemInfo['perms'] ) ) {
            if ( $itemInfo['perms']['perm_anon'] > 0 && isset($itemInfo['url']) && $itemInfo['url'] != '' ) {
                $cids[] = $row['cid'];
                $title = $row['title'];
                $body = str_replace('<!-- COMMENTSIG --><div class="comment-sig">', '', $row['comment']);
                $body = str_replace('</div><!-- /COMMENTSIG -->', '', $body);
                $body = str_replace('<div class="comment-edit">', '', $body);
                $body = str_replace('</div><!-- /COMMENTEDIT -->', '', $body);
                if( preg_match( '/<.*>/', $body ) == 0 ) {
                    $body = nl2br( $body );
                }
                $body = str_replace('<!-- comment -->', '', $body);
                $author = ($row['uid'] == 1 ? $row['name'] : COM_getDisplayName( $row['uid'] ));

                switch ( (int) $S['content_length'] ) {
                    case 0 :
                        $body = '';
                        break;
                    case 1 :
                        $body = trim($body).'<br><br>'.$author;
                        break;
                    default :
                        $body = trim(SYND_truncateSummary( $body, $S['content_length'] ));
                        break;
                }

                $url   = $itemInfo['url'];
                $content[] = array( 'title'   => $title,
                                    'summary' => $body,
                                    'link'    => $url . '#cid_'.$row['cid'],
                                    'uid'     => $row['uid'],
                                    'author'  => $author,
                                    'date'    => $row['date'],
                                    'format'  => 'html'
                                  );

            }
            $counter++;
            if ( $counter > $S['limits'] )
                break;
        }
    }
    $link = $_CONF['site_url'];
    $update = time();

    return $content;
}

/**
* Checking if comment feeds are up to date
*
* @param    int     $feed           id of feed to be checked
* @param    string  $topic          topic
* @param    string  $update_data    data describing current feed contents
* @param    string  $limit          number of entries or number of hours
* @param    string  $updated_type   (optional) type of feed to be updated
* @param    string  $updated_topic  (optional) feed's "topic" to be updated
* @param    string  $updated_id     (optional) id of entry that has changed
* @return   boolean                 true: feed data is up to date; false: isn't
*
*/
function plugin_feedupdatecheck_commentfeeds ($feed, $topic, $update_data, $limit, $updated_type = '', $updated_topic = '', $updated_id = '')
{
    global $_TABLES, $_VARS;

    $is_current = true;

    if ($updated_type != 'commentfeeds') {
        // we're not interested
        $updated_type = '';
        $updated_topic = '';
        $updated_id = '';
    }

    if ( (int) $_VARS['cmt_update'] > (int) $update_data ) {
        return false;
    }
    return true;
}

/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function plugin_upgrade_commentfeeds() {
    global $_CONF, $_TABLES, $_CF_CONF;

    require_once $_CONF['path'].'plugins/commentfeeds/upgrade.php';

    return commentfeeds_upgrade();
}

function plugin_itemsaved_commentfeeds( $id, $type )
{
    global $_TABLES, $_VARS;

    if ( $type != 'comment' ) return;

    $sql = "SELECT * FROM {$_TABLES['comments']} WHERE queued = 0 AND cid=".(int) $id;
    $result = DB_query($sql);
    if ( DB_numRows( $result ) != 0 ) {
        $row = DB_fetchArray($result);
        $itemInfo = PLG_getItemInfo($row['type'],$row['sid'],'id,perms',1);
        if ( isset($itemInfo['perms'] ) ) {
            if ( isset($itemInfo['perms']['perm_anon']) && $itemInfo['perms']['perm_anon'] > 0 ) {
                $now = time();
                DB_query("REPLACE INTO {$_TABLES['vars']} (name,value) VALUES('cmt_update','".$now."')");
                $_VARS['cmt_update'] = $now;
            }
        }
    }
    return;
}
?>