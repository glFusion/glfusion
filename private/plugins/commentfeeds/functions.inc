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
    global $_TABLES;

    $feeds = array ();

    $result = DB_query ("SELECT tid, topic FROM {$_TABLES['topics']} ".COM_getPermSQL('WHERE')." ORDER BY topic ASC");
    $num = DB_numRows ($result);

    if ($num > 0) {
        $feeds[] = array ('id' => 'all', 'name' => 'all topics');

        for ($i = 0; $i < $num; $i++) {
            $A = DB_fetchArray ($result);
            $feeds[] = array ('id' => $A['tid'], 'name' => $A['topic']);
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

    $sql = "SELECT c.cid, c.sid, c.title as title, c.comment, UNIX_TIMESTAMP(c.date) AS modified, "
           ." t.topic, s.title as articleTitle, c.uid "
           ." FROM {$_TABLES['comments']} as c "
           ." JOIN {$_TABLES['stories']} as s ON s.sid = c.sid "
           ." JOIN {$_TABLES['topics']} as t ON t.tid = s.tid "
           .COM_getPermSQL('WHERE', 1, 2, 's')
           .COM_getPermSQL('AND', 1, 2, 't')
           ." AND c.queued = 0 AND type='article' ";

    if( $S['topic'] != 'all' ) {
        $sql .= " AND topic='{$S['topic']}' ";
    }
    $sql .= " ORDER BY modified DESC  LIMIT 0, ".intval($S['limits'])." ";

    $result = DB_query( $sql );

    $content = array();
    $cids = array();
    $nrows = DB_numRows( $result );

    for( $i = 0; $i < $nrows; $i++ ) {
        $row = DB_fetchArray( $result );
        $cids[] = $row['cid'];

        $title =  $row['title'];
        $body = $row['comment'];
        $body = SYND_truncateSummary( $body, $S['content_length'] );
        $articleLink = COM_buildUrl( $_CONF['site_url']
                  ."/article.php?story={$row['sid']}");

        $link = $articleLink."#comments";
        $articleTitle = $row['articleTitle'];
        $body = sprintf($_CF_CONF['article_tag'], $articleLink, $articleTitle).$body;

        $content[] = array( 'title'   => $title,
                            'summary' => $body,
                            'link'    => $link,
                            'uid'     => $row['uid'],
                            'author'  => COM_getDisplayName( $row['uid'] ),
                            'date'    => $row['modified'],
                            'format'  => 'html'
                          );
    }

    $link = $_CONF['site_url'];
    $update = implode( ',', $cids );

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
    global $_TABLES;

    $is_current = true;

    if ($updated_type != 'commentfeeds') {
        // we're not interested
        $updated_type = '';
        $updated_topic = '';
        $updated_id = '';
    }

    $sql = "SELECT c.cid, UNIX_TIMESTAMP(c.date) AS modified "
           ." FROM {$_TABLES['comments']} as c "
           ." JOIN {$_TABLES['stories']} as s ON s.sid = c.sid "
           ." JOIN {$_TABLES['topics']} as t ON t.tid = s.tid "
           .COM_getPermSQL('WHERE', 1, 2, 's')
           .COM_getPermSQL('AND', 1, 2, 't')
           ." AND c.queued = 0 AND type='article' ";

    if( $topic != 'all' )
    {
        $sql .= " AND topic='".DB_escapeString($topic)."' ";
    }
    $sql .= " ORDER BY modified DESC  LIMIT 0, ".intval($limit)." ";
    $result = DB_query ($sql);
    $num = DB_numRows ($result);

    $cids = array ();
    for ($i = 0; $i < $num; $i++) {
        $A = DB_fetchArray ($result);

        if ($A['cid'] == $updated_id) {
            // this feed has to be updated - no further checks needed
            return false;
        }

        $cids[] = $A['cid'];
    }
    $current = implode (',', $cids);

    return ($current != $update_data) ? false : true;
}

/**
* Called by the plugin Editor to run the SQL Update for a plugin update
*/
function plugin_upgrade_commentfeeds() {
    global $_CONF, $_TABLES, $_CF_CONF;

    require_once $_CONF['path'].'plugins/commentfeeds/upgrade.php';

    return commentfeeds_upgrade();
}
?>