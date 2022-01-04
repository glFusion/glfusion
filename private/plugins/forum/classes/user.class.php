<?php
/**
*   Class to handle forum user information
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 20172018 Lee Garner <lee@leegarner.com>
*   @package    glfusion
*   @version    0.0.2
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
namespace Forum;

class User
{
    /**
    *   Properties of the instantiated object
    *   @var array */
    private $properties = array();

    /**
    *   Cache of user objects
    *   @var array */
    private static $cache = array();

    /**
    *   Array of admin levels by forum
    *   @var array */
    private $admin_lvl = array();

    private $total_likes = NULL;

    /**
    *   Constructor.
    *   Sets the field values from the supplied array, or reads the record
    *   if $A is a user ID.
    *
    *   @param  mixed   $A  Array of properties or group ID
    */
    public function __construct($A='')
    {
        $this->uid = 0;
        $this->rating = 0;
        $this->location = '';
        $this->aim = '';
        $this->icq = '';
        $this->yim = '';
        $this->msnm = '';
        $this->interests = '';
        $this->occupation = '';
        $this->signature = '';
        if (is_array($A)) {
            $this->setVars($A, true);
        } else {
            $A = (int)$A;
            $this->Read($A);
        }
    }

    // TODO: remove? See getInstance()
    public static function Get($uid, $forum)
    {
        if (!array_key_exists($uid, self::$cache)) {
            self::$cache[$uid] = new self($uid);
            self::$cache[$uid]->forum = $forum;
        }
        return self::$cache[$uid];
    }


    /**
    *   Read a single forum user record into an instantiated object.
    *   Collects information from the system and forum user tables, as well
    *   as post count, online session count, and reputation rating.
    *
    *   @param  integer $uid    User ID
    *   @return boolean     True on success, False on error or not found
    */
    public function Read($uid)
    {
        global $_TABLES, $_USER;

        $uid = (int)$uid;

        $sql = "SELECT
            users.*, userprefs.*, userinfo.*,
            gf_userinfo.rating, gf_userinfo.signature,
            (
                SELECT COUNT(DISTINCT id)
                FROM {$_TABLES['ff_topic']} topic
                WHERE topic.uid = users.uid
            ) as posts,
            (
                SELECT count(distinct sessions.sess_id)
                FROM {$_TABLES['sessions']} sessions
                WHERE sessions.uid = users.uid
            ) as sessions,
            (
                SELECT count(distinct rating.user_id)
                FROM {$_TABLES['ff_rating_assoc']} rating
                WHERE rating.user_id = users.uid AND rating.voter_id = {$_USER['uid']}
            ) AS votes
            FROM {$_TABLES['users']} users
            LEFT JOIN {$_TABLES['userprefs']} userprefs
                    ON users.uid=userprefs.uid
            LEFT JOIN {$_TABLES['userinfo']} userinfo
                     ON users.uid=userinfo.uid
            LEFT JOIN {$_TABLES['ff_userinfo']} gf_userinfo
                    ON users.uid=gf_userinfo.uid
            WHERE users.uid = $uid";
        //echo $sql;die;
        $res = DB_query($sql);
        if ($res && DB_numRows($res) == 1) {
            $A = DB_fetchArray($res, false);
            $this->setVars($A);
            return true;
        } else {
            return false;
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
        case 'uid':
        case 'rating':
        case 'status':
        case 'posts':
        case 'forum':
        case 'sessions':
        case 'votes':
        case 'showonline':
            $this->properties[$key] = (int)$value;
            break;
        case 'location':
        case 'aim':
        case 'icq':
        case 'yim':
        case 'msnm':
        case 'interests':
        case 'occupation':
        case 'sig':
        case 'signature':
        case 'pgpkey':
        case 'tzid':
        case 'fullname':
        case 'username':
        case 'display_name':
        case 'levelname':
        case 'level':
        case 'photo':
        case 'regdate':
        case 'avatar':
        case 'userlink':
        case 'email':
        case 'homepage':
        case 'onlinestatus':
        case 'tagline':
            $this->properties[$key] = trim($value);
            break;
        case 'emailfromuser':
            $this->properties[$key] = $value == 1 ? 1 : 0;
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
    *   Set all property values from DB or form
    *
    *   @param  array   $A          Array of property name=>value
    *   @param  boolean $from_db    True of coming from the DB, False for form
    */
    public function setVars($A, $from_db = true)
    {
        global $_FF_CONF, $_TABLES, $_CONF, $LANG_GF01;

        foreach ($A as $key=>$value) {
            $this->$key = $value;
        }

        if ($this->photo != '') {
            $this->avatar = USER_getPhoto($this->uid,'','','','0');
        } elseif (!isset($_CONF['default_photo']) || $_CONF['default_photo'] == '') {
            $this->avatar = $_CONF['site_url'] . '/assets/image/default.jpg';
        } else {
            $this->avatar = $_CONF['default_photo'];
        }

        if ($this->uid > 1) {
            $udt = new \Date(strtotime($this->regdate), $this->tzid);
            $this->regdate = $udt->format($_CONF['shortdate'],true);
            if ($this->sessions > 0 && (int) $this->showonline == 1 ) {
                $this->onlinestatus = $LANG_GF01['ONLINE'];
            } else {
                $this->onlinestatus = $LANG_GF01['OFFLINE'];
            }
        } else {
            $this->onlinestatus = '';
            $this->showonline = 0;
            $this->regdate = '';
        }
        $this->username = COM_getDisplayName($this->uid);
        //$this->getRank();

        // Set the forum signature (tagline)
        if ($_FF_CONF['bbcode_signature'] && $this->signature != '') {
            $format = new \glFusion\Formatter();
            $format->setNamespace('forum');
            $format->setAction('signature');
            $format->setType('text');
            $format->setProcessBBCode(true);
            if ( $_FF_CONF['allow_img_bbcode'] != true ) {
                $format->setBbcodeBlackList(array('img'));
            }
            $this->tagline = $format->parse($this->signature);
        } elseif ($this->sig != '') {
            $this->tagline = nl2br($this->sig);
        }
        return true;
    }


    /**
    *   Get the object instance for a specific poster.
    *
    *   @param  integer $uid    Poster user ID
    *   @return object          Poser object instance
    */
    public static function getInstance($uid)
    {
        static $cache = array();

        $uid = (int)$uid;
        if (!array_key_exists($uid, $cache)) {
            $cache[$uid] = new self($uid);
        }
        return $cache[$uid];
    }


    /**
    *   Determine if the user is online.
    *   Used to display the "online" or "offline" indicator in the posts.
    *
    *   @return boolean     True if user is online, False if not.
    */
    public function isOnline()
    {

        if ($this->uid == 1) {      // anonymous never online
            return false;
        } else {
            if ( $this->showonline ) {
                return $this->sessions > 0 ? true : false;
            }
            return false;
        }
    }


    /**
    *   Check if this user is valid.
    *   This can happen if a post belongs to a deleted user account.
    *
    *   @return boolean     True if a valid user, False if not
    */
    public function isValid()
    {
        return $this->uid > 0 ? true : false;
    }


    /**
    *   Check if this user is anonymous (or invalid)
    *
    *   @return boolean     True if anonymous, False if registered
    */
    public function isAnon()
    {
        return $this->uid < 2 ? true : false;
    }


    /**
    *   Get the poster's username.
    *   Allows for a user name to be entered in the post by anonymous
    *   posters, falls back to the actual user name or "Anonymous"
    *
    *   @return string  Poster's display name
    */
    public function UserName($default='')
    {
        global $LANG_GF01;

        if (!$this->isAnon()) {
            // Valid, registered username already set to COM_getDisplayName()
            return $this->username;
        } else {
            $username = $this->isValid() ? $this->username : '';
            if ($default != '' ) {
                $filter = \sanitizer::getInstance();
                $filter->setPostmode('text');
                $username = $filter->censor($default);
                $username = $filter->filterText($username);
            }
            if ($username == '') {
                // If the above filters removed everything, or no default was
                // given for an invalid (removed) user, then return "Anonymous"
                $username = $LANG_GF01['ANON'];
            }
        }
        return $username;
    }


    /**
    *   Get the total likes for this poster
    *
    *   @return integer     Total likes given to this poster
    */
    public function Likes()
    {
        global $_TABLES;

        if ($this->total_likes === NULL) {
            $this->total_likes = (int)DB_count($_TABLES['ff_likes_assoc'],
                                'poster_id', $this->uid);
        }
        return $this->total_likes;
    }


    private function getRank()
    {
        global $LANG_GF01, $_FF_CONF;
        USES_forum_functions();

        $starimage = '<img src="%s" alt="'.$LANG_GF01['FORUM'].' %s" title="'.$LANG_GF01['FORUM'].' %s"/>';

        if ($this->uid == 1) {
            $this->level = '';
            $this->levelname = '';
        } elseif (SEC_inGroup(1,$this->uid)) {
            $this->level = sprintf($starimage,_ff_getImage('rank_admin','ranks'),$LANG_GF01['admin'],$LANG_GF01['admin']);
            $this->levelname=$LANG_GF01['admin'];
        } elseif (Moderator::hasPerm($this->forum, $this->uid)) {
            $this->level = sprintf($starimage, _ff_getImage('rank_mod','ranks'),
                    $LANG_GF01['moderator'],$LANG_GF01['moderator']);
            $this->levelname=$LANG_GF01['moderator'];
        } else {
            foreach (array(2, 3, 4, 5) as $lvl) {
                if ($this->posts < $_FF_CONF["level$lvl"]) {
                    $this->levelname = $_FF_CONF['level1name'];
                    $this->level = sprintf($starimage, _ff_getImage("rank$lvl",'ranks'),
                        $this->levelname, $this->levelname);
                    break;
                }
            }
        }
    }


    /**
    *   Delete a single userinfo record. Does not delete the image.
    *   TODO: Not used, not needed?
    *
    *   @param  integer $uid    ID of user to delete
    */
    public static function Delete($uid)
    {
        global $_TABLES;

        DB_delete($_TABLES['ff_userinfo'], 'uid', (int)$uid);
    }


    /**
    *   Check if the current user is allowed to vote for this poster.
    *   Anonymous can't give or receive votes, and users can't vote for
    *   themselves.
    *
    *   @return boolean     True if vote can be given, False if not.
    */
    public function okToVote()
    {
        global $_USER;

        if (COM_isAnonUser() ||     // current user can't be anonymous
            $this->isAnon() ||      // poster can't be anonymous
            $_USER['uid'] == $this->uid     // can't vote for yourself
        ) {
            return false;
        } else {
            return true;
        }
    }


    /**
    *   Get the poster's level for a given forum.
    *   Caches value in $this->admin_lvl
    *
    *   @param  integer $forum  ID of forum
    *   @return integer     Admin level (2 = admin, 1 = moderator, 0 = none)
    */
    public function adminLevel($forum)
    {
        if (!array_key_exists($forum, $this->admin_lvl)) {
            if (SEC_inGroup(1, $this->uid)) {
                $this->admin_lvl[$forum] = 2;
            } elseif (forum_modPermission($forum, $this->uid)) {
                $this->admin_lvl[$forum] = 1;
            } else {
                $this->admin_lvl[$forum] = 0;
            }
        }
        return $this->admin_lvl[$forum];
    }


    /**
    *   Check if the current user can post to the forum at all.
    *
    *   @return boolean     True if ok to post, False if not.
    */
    public static function canPost($uid)
    {
        global $_TABLES, $_FF_CONF, $LANG_GF00, $LANG_GF02, $_CONF;

        //Check is anonymous users can post
        if ($_FF_CONF['registered_to_post'] && COM_isAnonUser() ){
            $display  = COM_siteHeader();
            $display .= SEC_loginRequiredForm();
            $display .= COM_siteFooter();
            return $display;
        }

        // Check if IP of user has been banned
        $isBanned = DB_count($_TABLES['ff_banned_ip'],'host_ip',DB_escapeString($_SERVER['REAL_ADDR']));
        if ( $isBanned > 0 ) {
            $display .= FF_siteHeader();
            $display .= COM_startBlock($LANG_GF00['access_denied']);
            $display .= $LANG_GF02['msg14'];
            $display .= sprintf ($LANG_GF02['msg15'],$_CONF['site_mail']);
            $display .= COM_endBlock();
            $display .= FF_siteFooter();
            return $display;
            exit();
        }
    }


    /**
    *   Get the current user's moderator options to a topic.
    *   TODO: Experimental, relies on a Moderator object not yet
    *       committed to Git.
    *
    *   @param  integer $topic  Topic ID
    *   @return array       Array of moderator options for this user
    */
    public static function getModOpts($topic)
    {
        global $_USER, $LANG_GF03;

        $options = array();

        if (COM_isAnonUser()) {
            return $options;
        }
        $uid = (int)$_USER['uid'];

        if (Moderator::hasPerm($topic['forum'], $uid, 'mod_edit')) {
            $options[] = array('editpost', $LANG_GF03['edit']);
            if ($forum['pid'] == 0) {
                if ($forum['locked'] == 0) {
                    $options[] = array('locktopic', $LANG_GF03['lock_topic']);
                } else {
                    $options[] = array('unlocktopic', $LANG_GF03['unlock_topic']);
                }
            }
        }

        if (Moderator::hasPerm($topic['forum'], $uid, 'mod_delete')) {
            $options[] = array('deletepost', $LANG_GF03['delete']);
        }
        if (Moderator::hasPerm($topic['forum'], $uid, 'mod_ban')) {
            $options[] = array('banip', $LANG_GF03['ban']);
        }
        if ($forum['pid'] == 0) {
            if (Moderator::hasPerm($topic['forum'], $uid, 'mod_move')) {
                $options[] = array('movetopic', $LANG_GF03['move']);
                $options[] = array('mergetopic', $LANG_GF03['merge_topic']);
            }
        } elseif (Moderator::hasPerm($topic['forum'], $uid, 'mod_move')) {
            $options[] = array('movetopic', $LANG_GF03['split']);
            $options[] = array('mergetopic', $LANG_GF03['merge_post']);
        }
        return $options;
   }

}

?>
