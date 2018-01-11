<?php
/**
*   Class to handle Forum post Likes
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2018 Lee Garner <lee@leegarner.com>
*   @package    glfusion
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
namespace Forum;

class Like
{
    /**
    *   Properties of the instantiated object
    *   @var array() */
    private $properties = array();

    private static $cache = array();

    /**
    *   Constructor.
    *   Sets the field values from the supplied array, or reads the record
    *   if $A is a rank ID.
    *
    *   @param  array   $A  Array of properties
    */
    public function __construct($A)
    {
        if (is_null($A['username'])) $A['username'] = $LANG_GF01['unk_username'];
        foreach ($A as $key => $value) {
            $this->$key = $value;
        }
    }


    /**
    *   Setter function. Sanitizes values and sets into $properties
    *
    *   @param  string  $key    Name of property
    *   @param  mixed   $value  Property value
    */
    public function __set($key, $value)
    {
        switch ($key) {
        case 'poster_id':
        case 'topic_id':
        case 'voter_id':
        case 'likers':
            $this->properties[$key] = (int)$value;
            break;
        case 'username':
            $this->properties[$key] = trim($value);
            break;
        }
    }


    /**
    *   Getter function. Returns property value or NULL if not set
    *
    *   @param  string  $key    Name of property
    *   @return mixed           Value of property
    */
    public function __get($key)
    {
        return isset($this->properties[$key]) ? $this->properties[$key] : NULL;
    }


    /**
    *   Helper function to get all children under a parent topic.
    *
    *   @see    self::TopicLikes()
    *   @see    self::remAllLikes()
    *   @param  integer $parent_id  ID of parent topic
    */
    private static function _getAllTopics($parent_id)
    {
        global $_TABLES;

        $parent_id = (int)$parent_id;
        if ($parent_id == 0) return array();

        // include the parent
        $topics = array($parent_id);

        // Now get all the children
        $sql = "SELECT id FROM {$_TABLES['ff_topic']}
                WHERE pid = '$parent_id'";
        $res = DB_query($sql);
        while ($A = DB_fetchArray($res, false)) {
            $topics[] = (int)$A['id'];
        }
        return $topics;
    }


    /**
    *   Return a count of all the likes given by a specific user.
    *
    *   @param  integer $uid    User ID
    *   @return integer         Count of likes given
    */
    public static function CountLikesGiven($uid)
    {
        global $_TABLES;
        static $cache = array();

        $uid = (int)$uid;
        if (!array_key_exists($uid, $cache)) {
            $cache[$uid] = (int)DB_count($_TABLES['ff_likes_assoc'], 'voter_id', $uid);
        }
        return $cache[$uid];
    }


    /**
    *   Get all the likes for all posts under a specified parent ID
    *   This is to be called by the topic display to get all likes
    *   at once. All likes for all posts under the parent ID are cached.
    *
    *   @param  integer $parent_id  Parent ID
    *   @return void
    */
    public static function TopicLikes($parent_id)
    {
        global $_TABLES, $LANG_GF01;

        $topics = self::_getAllTopics($parent_id);
        // All topics go into the cache array whether they have likes or not.
        foreach ($topics as $topic_id) {
            self::$cache[$topic_id] = array();
        }
        $topics = implode(',', $topics);
        $sql = "SELECT u.username, lk.*
                FROM {$_TABLES['ff_likes_assoc']} lk
                LEFT JOIN {$_TABLES['users']} u
                    ON u.uid = lk.voter_id
                WHERE lk.topic_id IN ($topics)";
        $res = DB_query($sql);
        while ($A = DB_fetchArray($res, false)) {
            self::$cache[$A['topic_id']][$A['voter_id']] = new self($A);
        }
    }


    /**
    *   Get all the likes for a specific post ID
    *   Typically called by other functions, this will cache all likes
    *   in self::$cache to reduce DB calls.
    *
    *   @param  integer $post_id    ID of post
    *   @return array       Array of Like objects
    */
    public static function PostLikes($post_id)
    {
        global $_TABLES;

        $post_id = (int)$post_id;
        if (!array_key_exists($post_id, self::$cache)) {
            $sql = "SELECT u.username, lk.*
                    FROM {$_TABLES['ff_likes_assoc']} lk
                    LEFT JOIN {$_TABLES['users']} u
                        ON u.uid = lk.voter_id
                    WHERE lk.topic_id = {$post_id}";
            $likers = array();
            $lk_res = DB_query($sql);
            while ($A = DB_fetchArray($lk_res, false)) {
                $likers[$A['voter_id']] = new self($A);
            }
            self::$cache[$post_id] = $likers;
        }
        return self::$cache[$post_id];
    }


    /**
    *   Determine if a specific post has been liked by a specific user
    *
    *   @param  integer $post_id    Post (topic) ID
    *   @param  integer $uid        User ID, default is current user
    *   @return boolean     True if the user has liked the post
    */
    public static function isLikedByUser($post_id, $uid = 0)
    {
        global $_USER;

        if ($uid == 0) $uid = $_USER['uid'];
        $uid = (int)$uid;
        $posts = self::PostLikes($post_id);
        if (array_key_exists($uid, $posts)) {
            return true;       // this user was found in likes
        } else {
            return false;
        }
    }


    /**
    *   Add a like to a post.
    *   Does not validate whether a like has already been recorded. Only one
    *   like can be recorded for each topic/voter combination, so this just
    *   writes a new record if needed.
    *
    *   @param  integer $voter_id   User ID of the voter
    *   @param  integer $poster_id  User ID of the topic poster
    *   @param  integer $topic_id   Topic ID
    */
    public static function addLike($voter_id, $poster_id, $topic_id)
    {
        global $_TABLES;

        $voter_id = (int)$voter_id;
        $poster_id = (int)$poster_id;
        $topic_id = (int)$topic_id;
        self::PostLikes($topic_id); // populate the cache array
        if (!array_key_exists($voter_id, self::$cache[$topic_id])) {
            $sql = "INSERT IGNORE INTO {$_TABLES['ff_likes_assoc']}
                        (voter_id, poster_id, topic_id)
                        VALUES ($voter_id, $poster_id, $topic_id)";
            DB_query($sql,1);
            if (!DB_error()) {
                $A = array(
                    'voter_id' => $voter_id,
                    'poster_id' => $poster_id,
                    'topic_id' => $topic_id,
                    'username' => DB_getItem($_TABLES['users'], 'username', "uid = $voter_id"),
                );
                self::$cache[$topic_id][$poster_id] = new self($A);
            }
        }
    }


    /**
    *   Remove a like from the table.
    *
    *   @param  integer $voter_id   User ID of the voter
    *   @param  integer $poster_id  User ID of the topic poster
    *   @param  integer $topic_id   Topic ID
    */
    public static function remLike($voter_id, $poster_id, $topic_id)
    {
        global $_TABLES;

        self::PostLikes($topic_id);
        if (array_key_exists($topic_id, self::$cache)) {
            if (array_key_exists($voter_id, self::$cache[$topic_id])) {
                //Delete Their vote in the associative table
                DB_delete($_TABLES['ff_likes_assoc'],
                        array('poster_id', 'voter_id', 'topic_id'),
                        array($poster_id, $voter_id, $topic_id));
                unset(self::$cache[$topic_id][$voter_id]);
            }
        }
    }


    /**
    *   Remove all entries from the Likes table for a topic.
    *   Used when a topic is deleted by the moderator.
    *   If this is a parent topic, then all likes for the child topics
    *   are also removed.
    *
    *   @param  integer $topic_id   ID of topic
    *   @param  boolean $is_parent  True if this is a parent topic.
    */
    public static function remAllLikes($topic_id, $is_parent = false)
    {
        global $_TABLES;

        $topic_id = (int)$topic_id;

        if ($is_parent) {
            $like_topics = self::_getAllTopics($topic_id);
            $like_topics = implode(',', $like_topics);
        } else {
            $like_topics = $topic_id;
        }
        $sql = "DELETE FROM {$_TABLES['ff_likes_assoc']}
                WHERE topic_id IN ($like_topics)";  
        DB_query($sql);
    }


    /**
    *   Return the count of likes given for a post.
    *
    *   @param  integer $post_id    ID of post
    *   @return integer     Total count of likes for the post
    */
    public static function CountPostLikes($post_id)
    {
        return count(self::PostLikes($post_id));
    }


    /**
    *   Count the likes received by a specific user
    *
    *   @param  integer $poster_id  ID of the user
    *   @return integer     Total cound of likes received by the user
    */
    public static function CountLikesReceived($poster_id)
    {
        global $_TABLES;
        static $cache = array();

        if (!array_key_exists($poster_id, $cache)) {
            $cache[$poster_id] = DB_count($_TABLES['ff_likes_assoc'], 'poster_id', $poster_id);
        }
        return $cache[$poster_id];
    }


    /**
    *   Get an array of all the names of users that have liked a post.
    *   If $link is true, then the usernames will be wrapped in a link to the
    *   user profile page.
    *
    *   @param  integer $post_id    ID of the post
    *   @param  boolean $link       True to wrap the usernames in a link
    *   @return array       Array of usernames
    */
    public static function LikerNames($post_id, $link = true)
    {
        global $_CONF;

        // Override link request if condition for showing profile link isn't met.
        if ($link && ($_CONF['profileloginrequired'] && COM_isAnonUser())) {
            $link = false;
        }

        $likers = array();
        foreach (self::PostLikes($post_id) as $like) {
            if ($link) {
                $likers[] = COM_createLink($like->username,
                        COM_buildUrl($_CONF['site_url'] . '/users.php?mode=profile&uid=' . $like->voter_id)
                    );
            } else {
                $likers[] = $like->username;
            }
        }
        return $likers;
     }


    /**
    *   Get the text to appear in the posts.
    *   This is the "Liked by user1, user2,..." text line.
    *
    *   @param  integer $post_id    Post ID
    *   @return string      Formatted text string
    */
    public static function getLikesText($post_id)
    {
        global $LANG_GF01;

        $likers = self::LikerNames($post_id);
        $total_likes = count($likers);
        $threshold = 3;

        if ($total_likes == 0) {
            return '';      // No likes, no text
        } elseif ($total_likes == 1) {
            // Singular format, e.g. "User1 like this"
            $fmt = $LANG_GF01['likes_formats'][0];
        } elseif ($total_likes <= $threshold) {
            // Format for small number, e.g. "Liked by user1, user2, user3"
            $fmt = $LANG_GF01['likes_formats'][1];
        } else {
            // Format for large number, e.g. "user1, user2 and 3 others"
            $fmt = $LANG_GF01['likes_formats'][2];
        }
        $str = implode(', ', array_slice($likers, 0, $threshold));
        $extra_count = count(array_slice($likers, $threshold));
        return sprintf($fmt, $str, $total_likes, $extra_count);
    }


    /**
    *   Import Community Moderation votes as Likes
    */
    public function importFromModeration()
    {
        global $_TABLES;
        $sql = "INSERT IGNORE INTO {$_TABLES['ff_likes_assoc']}
                    (poster_id, voter_id, topic_id)
                SELECT user_id, voter_id, topic_id FROM {$_TABLES['ff_rating_assoc']}
                WHERE grade = 1";
        DB_query($sql);
    }

}

?>
