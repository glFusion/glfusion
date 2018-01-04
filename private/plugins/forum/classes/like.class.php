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
    *   @param  mixed   $A  Array of properties or group ID
    */
    public function __construct($A)
    {
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
                if (!is_null($A['username'])) {
                    $likers[$A['voter_id']] = new self($A);
                }
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
    public static function LikedByUser($post_id, $uid = 0)
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
    *   Return the count of likes given for a post.
    *
    *   @param  integer $post_id    ID of post
    *   @retur  integer     Total count of likes for the post
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
    public static function LikerNames($post_id, $link = false)
    {
        global $_CONF;

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

}

?>
