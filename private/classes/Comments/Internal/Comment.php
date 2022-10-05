<?php
/**
 * Class to define a single comment.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2022 Lee Garner
 * @package     glfusion
 * @version     v2.1.0
 * @since       v2.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Comments\Internal;
use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Formatter;
use \glFusion\Log\Log;
use \glFusion\Comments\CommentEngine;

USES_lib_user();


class Comment implements \ArrayAccess
{
/*    private $properties = array(
        'cid' => 0,
        'type' => 'article',
        'sid' => '',
        'date' => '',
        'title' => '',
        'comment' => '',
        'pid' => 0,
        'queued' => 0,
        'postmode' => 'plaintext',
        'lft' => 0,
        'rht' => 0,
        'indent' => 0,
        'name' => '',
        'uid' => 0,
        'ipaddress' => '',
        'fullname' => '',
        'photo' => '',
        'email' => '',
        'nice_date' => '',
        'pindent' => 0,
    );
 */
    /** Comment record ID.
     * @var integer */
    private $cid = 0;

    /** Item type, e.g. article or plugin name.
     * @var string */
    private $type = 'article';

    /** Item ID, e.g. story ID.
     * @var string */
    private $sid = '';

    /** Comment submission date.
     * @var string */
    private $date = '';

    /** Comment title, defaults to item title.
     * @var string */
    private $title = '';

    /** Comment text.
     * @var string */
    private $comment = '';

    /** Parent comment ID, for nesting.
     * @var integer */
    private $pid = 0;

    /** Flag to indicate the comment is queued (moderated).
     * @var integer */
    private $queued = 0;

    /** Post mode, either plaintext or html.
     * @var string */
    private $postmode = 'plaintext';

    /** Left comment for MPTT.
     * @var integer */
    private $lft = 0;

    /** Right comment for MPTT.
     * @var integer */
    private $rht = 0;

    /** Indentation number, for nesting.
     * @var integer */
    private $indent = 0;

    /** Submitter's name.
     * @var string */
    private $name = '';

    /** Submitter's user ID.
     * @var integer */
    private $uid = 0;

    /** Submitter's IP address, sanitized.
     * @var string */
    private $ipaddress = '';

    /** Submitter's username. TODO combine with $name above.
     * @var string */
    private $username = '';

    /** Submitter's full name from the users table.
     * @var string */
    private $fullname = '';

    /** Submitter's photo from the users table.
     * @var string */
    private $photo = '';

    /** Submitter's email address from the users table.
     * @var string */
    private $email = '';

    /** Nicely-formatted submittion date.
     * @var string */
    private $nice_date = '';

    /** Indenting from the parent post.
     * @var integer */
    private $pindent = 0;

    /** Direct URL to the comment, used after saving.
     * @var integer */
    private $_redirect_url = NULL;

    /** Track errors that occure.
     * @var array */
    private $_errors = array();


    /**
     * Get a single comment by its record ID.
     *
     * @param   integer $cid        Comment ID
     * @return  object      Comment object
     */
    public static function getByCid(int $cid) : self
    {
        global $_TABLES;

        try {
            $row = Database::getInstance()->conn->executeQuery(
                "SELECT * FROM {$_TABLES['comments']} WHERE cid = ?",
                array($cid),
                array(Database::INTEGER)
            )->fetchAssociative();
        } catch (\Throwable $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
            $row = false;
        }
        if (is_array($row)) {
            return self::fromArray($row);
        } else {
            return new self;
        }
    }


    /**
     * Create a Comment object from an array, such as DB or $_POST.
     *
     * @param   array   $A      Array of comment data
     * @return  object      Comment object
     */
    public static function fromArray(array $A) : self
    {
        $retval = new self;
        $retval->setVars($A);
        return $retval;
    }


    /**
     * Set the comment properties from an array.
     *
     * @param   array   $A      Array of comment data
     * @return  object  $this
     */
    public function setVars(array $A) : self
    {
        if (isset($A['cid'])) $this->cid = (int)$A['cid'];
        if (isset($A['type'])) $this->type = $A['type'];
        if (isset($A['sid'])) $this->sid = $A['sid'];
        if (isset($A['date'])) $this->date = $A['date'];
        if (isset($A['title'])) $this->title = $A['title'];
        if (isset($A['comment'])) $this->comment = $A['comment'];
        if (isset($A['pid'])) $this->pid = (int)$A['pid'];
        if (isset($A['queued'])) $this->queued = (int)$A['queued'];
        if (isset($A['postmode'])) $this->postmode = $A['postmode'];
        if (isset($A['lft'])) $this->lft = (int)$A['lft'];
        if (isset($A['rht'])) $this->rht = (int)$A['rht'];
        if (isset($A['indent'])) $this->indent = (int)$A['indent'];
        if (isset($A['name'])) $this->name = $A['name'];
        if (isset($A['uid'])) $this->uid = (int)$A['uid'];
        if (isset($A['ipaddress'])) $this->ipaddress = $A['ipaddress'];
        if (isset($A['username'])) $this->username = $A['username'];
        if (isset($A['fullname'])) $this->fullname = $A['fullname'];
        if (isset($A['photo'])) $this->photo = $A['photo'];
        if (isset($A['email'])) $this->email = $A['email'];
        if (isset($A['nice_date'])) $this->nice_date = $A['nice_date'];
        if (isset($A['pindent'])) $this->pindent = (int)$A['pindent'];
        return $this;
    }


    /**
     * Get the item (story) ID for the comment.
     *
     * @return  string      Item ID
     */
    public function getSid() : string
    {
        return $this->sid;
    }


    /**
     * Get the item type or plugin name.
     *
     * @return  string      Item type
     */
    public function getType() : string
    {
        return $this->type;
    }


    /**
     * Get the comment record ID.
     *
     * @return  integer     Record ID
     */
    public function getCid() : int
    {
        return $this->cid;
    }


    /**
     * Get the comment author's ID.
     *
     * @return  integer     Author UID
     */
    public function getUid() : int
    {
        return $this->uid;
    }


    /**
     * Get the comment text.
     *
     * @return  string      Comment text
     */
    public function getComment() : string
    {
        return $this->comment;
    }


    /**
     * Get the item title.
     *
     * @return  string      Item title
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * Get the parent comment ID.
     *
     * @reurn   integer     Parent comment ID
     */
    public function getPid() : int
    {
        return $this->pid;
    }


    /**
     * Get the postmode used for the comment.
     *
     * @return  string      'html' or 'text'
     */
    public function getPostmode() : string
    {
        return $this->postmode;
    }


    /**
     * Get the date of the comment.
     *
     * @return  string      Date of the comment
     */
    public function getDate() : string
    {
        return $this->date;
    }


    /**
     * Get the URL to redirect after saving.
     *
     * @return  string      URL to item view or other page.
     */
    public function getRedirectUrl() : string
    {
        if ($this->_redirect_url === NULL) {
            $this->_redirect_url = self::makeRedirectUrl($this->type, $this->sid);
        }
        return $this->_redirect_url;
    }


    /**
     * Save a comment.
     *
     * @param   array   $A      Optional array of data from $_POST
     * @return  boolean     True for success, Fale on error
     */
    public function save(?array $A=NULL) : bool
    {
        global $_CONF, $_TABLES, $_USER, $LANG03, $LANG_ADM_ACTIONS;

        $commentUid = $this->uid;   // get the original commenter ID

        if (is_array($A)) {
            $this->setVars($A);
        }

        if (empty($this->title)) {
            $info = PLG_getItemInfo($this->type, $this->sid, 'id,title');
            $this->title = (isset($info['title']) ? $info['title'] : 'Comment');
        }

        if (!$this->checkValidData()) {
            return false;
        }

        if (isset($A['comment_text'])) {
            $this->comment = $A['comment_text'];
        }

        $db = Database::getInstance();

        // Get a valid uid
        if (empty ($_USER['uid'])) {
            $uid = 1;
        } else {
            $uid = $_USER['uid'];
        }

        // Check that anonymous comments are allowed
        if (CommentEngine::loginRequired()) {
            Log::write('system',Log::WARNING,'CMT_saveComment: IP address '.$_SERVER['REAL_ADDR'].' '
                . 'attempted to save a comment with anonymous comments disabled for site.');
            COM_setMsg('Login is required to save a comment.');
            return false;
        }

        // Check for people breaking the speed limit
        COM_clearSpeedlimit ($_CONF['commentspeedlimit'], 'comment');
        $last = COM_checkSpeedlimit ('comment');
        if ($last > 0) {
            Log::write('system',Log::WARNING,'CMT_saveComment: '.$uid.' from IP address '.$_SERVER['REAL_ADDR'].' '
                . 'attempted to submit a comment before the speed limit expired.');
            COM_setMsg('Attempting to save comments too fase.');
            return false;
        }

        if ( COM_isAnonUser() ) {
            if (isset($_POST['username']) ) {
                $this->name = @htmlspecialchars(strip_tags(trim(COM_checkWords(USER_sanitizeName($_POST['username'])))),ENT_QUOTES,COM_getEncodingt(),true);
                $this->name = USER_uniqueUsername($this->name);
            } else {
                $this->name = '';
            }
            $this->email = '';  // just to be sure
        } else {
            $this->name = $_USER['username'];
            $this->email = $_USER['email'];
        }

        // Error Checking
        if (empty ($this->sid) || empty ($this->comment) || empty ($this->type) ) {
            Log::write('system',Log::WARNING,'CMT_saveComment: '.$uid.' from '.$_SERVER['REAL_ADDR'].' tried to submit a comment with one or more missing values.');
            if ( SESS_isSet('glfusion.commentpresave.error') ) {
                $msg = SESS_getVar('glfusion.commentpresave.error') . '<br/>' . $LANG03[12];
            } else {
                $msg = $LANG03[12];
            }
            SESS_setVar('glfusion.commentpresave.error',$msg);

            return $ret = 1;
        }

        if (!is_numeric($this->pid) || ($this->pid < 0)) {
            $this->pid = 0;
        }

        // Call Spam based plugins
        $spamcheck = '<h1>' . $this->title . '</h1><p>' . $this->comment . '</p>';
        $spamData = array(
            'username' => $this->username,
            'email'    => $this->email,
            'ip'       => $_SERVER['REAL_ADDR'],
            'type'     => 'comment'
        );
        $result = PLG_checkforSpam($spamcheck, $_CONF['spamx'], $spamData);

        // update speed limit nonetheless
        COM_updateSpeedlimit ('comment');

        // Now check the result and display message if spam action was taken
        if ($result > 0) {
            // then tell them to get lost ...
            COM_displayMessageAndAbort ($result, 'spamx', 403, 'Forbidden');
        }

        // Let plugins have a chance to decide what to do before saving the comment, return errors.
        if ($someError = PLG_commentPreSave($uid, $this->title, $this->comment, $this->sid, $this->pid, $this->type, $this->postmode)) {
            return $someError;
        }

        // determine if we are queuing
        if (isset($_CONF['commentssubmission'] ) && $_CONF['commentssubmission'] > 0) {
            switch ( $_CONF['commentssubmission'] ) {
            case 1 : // anonymous only
                if ( COM_isAnonUser() ) {
                    $this->queued = 1;
                }
                break;
            case 2 : // all users
            default :
                $this->queued = 1;
                break;
            }
            if ( SEC_hasRights('comment.submit') ) {
                $this->queued = 0;
            }
        }

        $moderatorEdit = false;     // make sure the variable is defined

        if ($this->cid > 0) {
            $_edit = true;      // updating an existing comment
            // flag indicating the edit is done from the moderation.php page
            $moderatorEdit = isset($A['modedit']) && self::isModerator();
            $silentEdit = isset($_POST['silent_edit']);
            if (!self::isModerator() && $_USER['uid'] != $commentUid) {
                // non-moderator trying to edit someone else's comment
                return false;
            }

            /*$values = array(
                'comment'   => $this->comment,
                'title'     => $this->title,
                'name'      => $this->username,
                'postmode'  => $this->postmode,
            );
            $types = array(
                Database::STRING,
                Database::STRING,
                Database::STRING,
                Database::STRING,
            );*/
            try {
                $db->conn->update(
                    $_TABLES['comments'],
                    array(
                        'comment'   => $this->comment,
                        'title'     => $this->title,
                        'name'      => $this->username,
                        'postmode'  => $this->postmode,
                        'queued'    => $this->queued,
                    ),
                    array(
                        'cid' => $this->cid,
                        'sid' => $this->sid
                    ),
                    array(
                        Database::STRING,
                        Database::STRING,
                        Database::STRING,
                        Database::STRING,
                        Database::INTEGER,
                        Database::INTEGER,
                        Database::STRING
                    )
                );
            } catch (\Throwable $e) {
                Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
                $this->_errors[] = 'There was an error saving to the database.';
                return false;
            }
            if ($moderatorEdit) {
                \glFusion\Admin\AdminAction::write(
                    'comment',
                    'mod_edit',
                    sprintf($LANG_ADM_ACTIONS['comment_edit'], $this->cid, $this->title)
                );
            } else {
                if (!$silentEdit) {
                    PLG_itemSaved($this->cid, 'comment');
                    $db->conn->executestatement(
                        "REPLACE INTO `{$_TABLES['commentedits']}`
                            (cid,uid,time) VALUES (?,?,?)",
                        array($this->cid, $this->uid, $_CONF['_now']->toMySQL(true)),
                        array(Database::INTEGER, Database::INTEGER, Database::STRING)
                    );
                }
                PLG_commentEditSave($this->type, $this->cid, $this->sid);
            }
        } else {
            $_edit = false;     // creating a new comment
            $this->ipaddress = $_SERVER['REMOTE_ADDR'];
            $this->cid = 0;

            if ($this->pid > 0) { // reply to an existing comment
                $row = $db->conn->fetchAssoc(
                    "SELECT rht, indent FROM `{$_TABLES['comments']}` WHERE cid = ? AND sid = ?",
                    array($this->pid, $this->sid),
                    array(Database::INTEGER, Database::STRING)
                );
                if ($row === false) {
                    Log::write('system',Log::WARNING,'CMT_saveComment: '.$uid.' from '.$_SERVER['REAL_ADDR'].' tried '
                        . 'to reply to a non-existent comment or the pid/sid did not match');
                    COM_setMsg('Invalid parent comment for reply');
                    return false;
                }
                $rht    = $row['rht'];
                $indent = $row['indent'];

                $db->conn->executeStatement("LOCK TABLES `{$_TABLES['comments']}` WRITE");
                $db->conn->beginTransaction();
                try {
                    $db->conn->executeUpdate(
                        "UPDATE `{$_TABLES['comments']}` SET lft = lft + 2
                        WHERE sid = ? AND type = ? AND lft >= ?",
                        array($this->sid, $this->type, $rht),
                        array(Database::STRING, Database::STRING, Database::INTEGER)
                    );
                    $db->conn->executeUpdate(
                        "UPDATE `{$_TABLES['comments']}` SET rht = rht + 2
                        WHERE sid = ? AND type = ? AND rht >= ?",
                        array($this->sid, $this->type, $rht),
                        array(Database::STRING, Database::STRING, Database::INTEGER)
                    );
                } catch (\Doctrine\DBAL\Exception\RetryableException $e) {
                    $db->conn->commit();
                    $db->conn->executeStatement("UNLOCK TABLES `{$_TABLES['comments']}` WRITE");
                    usleep(250000);
                } catch (\Exception $e) {
                    Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
                    $db->conn->rollBack();
                    $db->conn->executeStatement("UNLOCK TABLES");
                    throw($e);
                }
            } else {  // first - parent level comment
                $db->conn->executeStatement("LOCK TABLES `{$_TABLES['comments']}` WRITE");
                $db->conn->beginTransaction();
                try {
                    $rht = $db->conn->fetchColumn(
                        "SELECT MAX(rht) FROM `{$_TABLES['comments']}` WHERE sid=?",
                        array($this->sid),
                        0
                    );
                } catch(Throwable $e) {
                    Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
                    $rht = false;
                }
                if (empty($rht)) {
                    $rht = 0;
                }
                $indent = 0;
            }
            $values = array(
                'sid' => $this->sid,
                'uid' => $this->uid,
                'name' => $this->username,
                'comment' => $this->comment,
                'date' => $_CONF['_now']->toMySQL(true),
                'title' => $this->title,
                'pid' => $this->pid,
                'queued' => $this->queued,
                'postmode' => $this->postmode,
                'lft' => $rht,
                'rht' => $rht+1,
                'indent' => $indent+1,
                'type' => $this->type,
                'ipaddress' => $this->ipaddress,
            );
            $types = array(
                Database::STRING,
                Database::INTEGER,
                Database::STRING,
                Database::STRING,
                Database::STRING, // date
                Database::STRING, // title
                Database::INTEGER, // pid
                Database::INTEGER, // queued
                Database::STRING, // postmode
                Database::INTEGER, //rht
                Database::INTEGER, // rht+1
                Database::INTEGER, // indent
                Database::STRING // IP
            );

            try {
                $db->conn->insert($_TABLES['comments'], $values, $types);
                $this->cid = $db->conn->lastInsertId();
                $db->conn->commit();
                $db->conn->executeStatement("UNLOCK TABLES");
            } catch (\Doctrine\DBAL\Exception\RetryableException $e) {
                $db->conn->commit();
                $db->conn->executeStatement("UNLOCK TABLES");
                usleep(250000);
            } catch (\Exception $e) {
                $db->conn->rollBack();
                $db->conn->executeStatement("UNLOCK TABLES");
                throw($e);
            }

            if (!$this->queued) {
                $c = Cache::getInstance();
                $c->deleteItemsByTags('whatsnew', 'comments');
                if ($this->type == 'article') {
                    $c->deleteItemsByTag('story_'.$this->sid);
                }
                PLG_itemSaved($this->cid, 'comment');
            } else {
                $c = Cache::getInstance()->deleteItemsByTag('menu');
                SESS_setVar('glfusion.commentpostsave',$LANG03[52]);
            }

            // check to see if user has subscribed....
            if ( !COM_isAnonUser() ) {
                if (isset($A['subscribe']) && $A['subscribe'] == 1) {
                    $itemInfo = PLG_getItemInfo($this->type, $this->sid, 'url,title');
                    if (isset($itemInfo['title']) ) {
                        $id_desc = $itemInfo['title'];
                    } else {
                        $id_desc = 'not defined';
                    }
                    $rc = PLG_subscribe('comment', $this->type, $this->sid, $uid, $this->type, $id_desc);
                } else {
                    PLG_unsubscribe('comment', $this->type, $this->sid);
                }
            }
            if (
                isset ($_CONF['notification']) &&
                in_array ('comment', $_CONF['notification'])
            ) {
                $this->sendNotification();
            }

            if (!$this->queued) {
                // handles sending out subscription emails
                PLG_sendSubscriptionNotification('comment',$this->type,$this->sid,$this->cid,$uid);
            }
        }

        Cache::getInstance()->deleteItemsByTags(array('comments'));
        if ($moderatorEdit) {
            $this->_redirect_url = $_CONF['site_admin_url'].'/moderation.php';
        } else {
            $this->_redirect_url = self::makeRedirectUrl($this->type, $this->sid);
        }
        return true;
    }


    /**
     * Send an email notification to mod / admin for a new comment submission.
     */
    public function sendNotification() : void
    {
        global $_CONF, $_TABLES, $LANG03, $LANG08, $LANG09;

        $filter = sanitizer::getInstance();
        $db = Database::getInstance();

        $author = $filter->sanitizeUsername($this->username . ' ('.$this->ipaddress.')');
        $type   = $this->type;
        $html2txt  = new Html2Text\Html2Text(strip_tags($this->title), false);
        $title = trim($html2txt->get_text());

        // build out the view

        $format = new Formatter();
        $format->setNamespace('glfusion');
        $format->setAction('comment');
        $format->setAllowedHTML($_CONF['htmlfilter_comment']);
        $format->setParseAutoTags(true);
        $format->setProcessBBCode(false);
        $format->setCensor(true);
        $format->setProcessSmilies(false);
        $format->setType($this->postmode);
        $comment = $format->parse($this->comment);

        if ($_CONF['emailstorieslength'] > 1) {
            $comment = COM_truncateHTML ( $comment, $_CONF['emailstorieslength']);
        }
        // we have 2 different types of email - one for queued and one for alerting
        if ( !$this->queued ) {
            $mailbody = "$LANG03[16]: " . $title ."<br>"
                  . "$LANG03[5]: "  . $author."<br>";
            if (($this->type != 'article') && ($this->type != 'poll')) {
                $mailbody .= "$LANG09[5]: {$this->type}<br>";
            }
            $mailbody .= '<br>'. $comment . '<br>';
            $mailbody .= $LANG08[33] . ' ' . $_CONF['site_url']
                  . '/comment.php?mode=view&cid=' . $this->cid . "<br><br>";
            $mailbody .= "------------------------------<br>";
            $mailbody .= "$LANG08[34]";
            $mailbody .= "<br>------------------------------<br>";

            $mailsubject = $_CONF['site_name'] . ' ' . $LANG03[9];

        } else {
            $mailbody  = $LANG03[53].'<br><br>';
            $mailbody .= $LANG03[16].': '. $title .'<br>';
            $mailbody .= $LANG03[5].': ' . $author.'<br><br>';
            $mailbody .= $comment . '<br><br>';
            $mailbody .= sprintf($LANG03[54].'<br>',$_CONF['site_admin_url'].'/moderation.php');

            $mailsubject = $LANG03[55];
        }

        // now we have the HTML mail message built
        // build text version
        $html2txt  = new \Html2Text\Html2Text($mailbody,false);
        $mailbody_text = trim($html2txt->get_text());

        $to = array();
        $msgData = array();
        $toCount = 0;

        if ( $commentData['queued'] == 0 ) {
            $to[] = array('email' => $_CONF['site_mail'], 'name' => '');
            $toCount++;
        } else {
            $commentadmin_grp_id = $db->conn->fetchColumn(
                "SELECT grp_id FROM `{$_TABLES['groups']}` WHERE grp_name='Comment Admin'",
                array(),
                0
            );
            if ( $commentadmin_grp_id === null ) {
                return;
            }
            $groups = SEC_getGroupList($commentadmin_grp_id);

	        $sql = "SELECT DISTINCT {$_TABLES['users']}.uid,username,fullname,email "
	              ."FROM `{$_TABLES['group_assignments']}`,`{$_TABLES['users']}` "
	              ."WHERE {$_TABLES['users']}.uid > 1 "
    	          ."AND {$_TABLES['users']}.uid = {$_TABLES['group_assignments']}.ug_uid "
	              ."AND ({$_TABLES['group_assignments']}.ug_main_grp_id IN (?))";

            $stmt = $db->conn->executeQuery($sql,array($groups),array(Database::PARAM_INT_ARRAY));
            $resultSet = $stmt->fetchAll(Database::ASSOCIATIVE);

            foreach($resultSet AS $row) {
                if ( $row['email'] != '' ) {
                    $toCount++;
                    $to[] = array('email' => $row['email'], 'name' => $row['username']);
                }
            }
        }
        if ( $toCount > 0 ) {
            $msgData['htmlmessage'] = $mailbody;
            $msgData['textmessage'] = $mailbody_text;
            $msgData['subject']     = $mailsubject;
            $msgData['from']['email'] = $_CONF['noreply_mail'];
            $msgData['from']['name'] = $_CONF['site_name'];
            $msgData['to'] = $to;
            COM_emailNotification( $msgData );
        }
    }


    /**
     * Deletes a given comment.
     *
     * The function expects the calling function to check to make sure the
     * requesting user has the correct permissions and that the comment exits
     * for the specified $type and $sid.
     *
     * @author  Vincent Furia, vinny01 AT users DOT sourceforge DOT net
     * @param   string      $type   article, poll, or plugin identifier
     * @param   string      $sid    id of object comment belongs to
     * @param   int         $cid    Comment ID
     * @return  string      0 indicates success, >0 identifies problem
     */
    public static function delete(int $cid, string $sid, string $type) : string
    {
        global $_CONF, $_TABLES, $_USER;

        $ret = 0;  // Assume good status unless reported otherwise

        // Sanity check, note we return immediately here and no DB operations
        // are performed
        if (!is_numeric ($cid) || ($cid < 0) || empty ($sid) || empty ($type)) {
            Log::write('system',Log::WARNING,'CMT_deleteComment: '.$_USER['uid'].' from '.$_SERVER['REAL_ADDR'].' tried '
                   . 'to delete a comment with one or more missing/bad values.');
            return 1;
        }

        $db = Database::getInstance();

        // Delete the comment from the DB and update the other comments to
        // maintain the tree structure
        // A lock is needed here to prevent other additions and/or deletions
        // from happening at the same time. A transaction would work better,
        // but aren't supported with MyISAM tables.

        $db->conn->executeStatement("LOCK TABLES `{$_TABLES['comments']}` WRITE");
        $db->conn->beginTransaction();
        try {
            $cmtData = $db->conn->fetchAssoc(
                "SELECT pid, lft, rht FROM `{$_TABLES['comments']}`
                WHERE cid = ? AND sid = ? AND type = ?",
                array($cid,$sid,$type),
                array(Database::INTEGER, Database::STRING, Database::STRING)
            );
            if ($cmtData === false) {
                Log::write('system',Log::WARNING,'CMT_deleteComment: '.$_USER['uid'].' from '.$_SERVER['REAL_ADDR'].' tried '
                       . 'to delete a comment that doesn\'t exist as described.');
                return $ret = 2;
            }
            $pid = $cmtData['pid'];
            $rht = $cmtData['rht'];
            $lft = $cmtData['lft'];

            $db->conn->update(
                $_TABLES['comments'],
                array('pid' => $pid),
                array('pid' => $cid),
                array(Database::INTEGER, Database::INTEGER)
            );
            $db->conn->delete(
                $_TABLES['comments'],
                array('cid' => $cid),
                array(Database::INTEGER)
            );
            $db->conn->executeStatement(
                "UPDATE {$_TABLES['comments']} SET indent = indent - 1
                WHERE sid = ? AND type = ? AND lft BETWEEN ? AND ?",
                array($sid, $type, $lft, $rht),
                array(Database::STRING, Database::STRING, Database::INTEGER, Database::INTEGER)
            );
            $db->conn->executeStatement(
                "UPDATE `{$_TABLES['comments']}` SET lft = lft - 2
                WHERE sid = ? AND type = ?  AND lft >= ?",
                array($sid, $type, $rht),
                array(Database::STRING, Database::STRING, Database::INTEGER)
            );
            $db->conn->executeUpdate(
                    "UPDATE `{$_TABLES['comments']}` SET rht = rht - 2 WHERE sid = ? AND type = ?  AND rht >= ?",
                    array($sid,$type,$rht),
                    array(Database::STRING,Database::STRING,Database::INTEGER)
            );
            $db->conn->commit();
            $db->conn->executeStatement("UNLOCK TABLES");
        } catch (\Doctrine\DBAL\Exception\RetryableException $e) {
            $db->conn->commit();
            $db->conn->executeStatement("UNLOCK TABLES");
            usleep(250000);
        } catch(Throwable $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
            $db->conn->rollBack();
            $db->conn->executeStatement("UNLOCK TABLES");
            throw($e);
        }

        PLG_itemDeleted((int) $cid, 'comment');

        Cache::getInstance()->deleteItemsByTags(array('whatsnew','story_'.$sid, 'comments'));
        echo COM_refresh(self::makeRedirectUrl($this->type, $this->sid));
        return 0;
    }


    /**
     * Filters comment text and appends necessary tags (sig and/or edit).
     *
     * @copyright Jared Wenerd 2008
     * @author Jared Wenerd <wenerd87 AT gmail DOT com>
     * @param string  $comment   comment text
     * @param bool    $edit     if true append edit tag
     * @param int     $cid      commentid if editing comment (for proper sig)
     * @return string of comment text
     * @@ WE are retiring this function
     */
    public function prepareText($edit = false) : string
    {
        global $_CONF;

        $filter = sanitizer::getInstance();
        $filter->setPostmode($this->postmode);
        $filter->setCensorData(true);
        $filter->setNamespace('glfusion','comment');
        $AllowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_comment']);
        $filter->setAllowedElements($AllowedElements);

        if ($this->postmode != 'text') {
            $comment = $filter->filterHTML($this->comment);
        }
        return $comment;
    }


    /**
     * Creates the preview above the edit form.
     *
     * @param   array   $A  Data posted
     * @return  string      HTML for preview
     */
    public function preview(?array $A=NULL) : string
    {
        global $_CONF, $_TABLES,$_USER,$LANG03;

        $retval = '';
        $mode = 'preview_edit';
        $start = new \Template( $_CONF['path_layout'] . 'comment' );
        $start->set_file( array( 'comment' => 'startcomment.thtml' ));
        $start->set_var( 'hide_if_preview', 'style="display:none"' );

        $db = Database::getInstance();

        // Clean up all the vars
        if (is_array($A)) {
            foreach ($A as $key => $value) {
                switch ($key) {
                case 'pid':
                case 'sid':
                    $A[$key] = (int) COM_applyFilter ($A[$key], true);
                    break;
                case 'title':
                case 'comment':
                    // noop = these have already been filtered above
                    // now we want the raw data
                    break;
                case 'username':
                    $A[$key] = @htmlspecialchars(strip_tags(trim(COM_checkWords(USER_sanitizeName($A[$key])))),ENT_QUOTES,COM_getEncodingt());
                    break;
                default:
                    $A[$key] = COM_applyFilter($A[$key]);
                }
            }
            $this->setVars($A);
        }

        //correct time and username for edit preview
        if ($mode == 'preview' || $mode == 'preview_new' || $mode == 'preview_edit') {
            $A['nice_date'] = $db->conn->fetchColumn(
                "SELECT UNIX_TIMESTAMP(date) FROM `{$_TABLES['comments']}` WHERE cid = ?",
                array($this->cid),
                0,
                array(Database::INTEGER)
            );
        }
        if (empty ($A['username'])) {
            $A['username'] = $db->conn->fetchColumn(
                "SELECT username FROM `{$_TABLES['users']}` WHERE uid=?",
                array($this->uid),
                0,
                array(Database::INTEGER)
            );
        }

        $author_id = PLG_getItemInfo($this->type, $this->sid, 'author');

        // Leverage the CommentCollection to create the preview
        // from this comment.
        $Coll = new CommentCollection;
        $Coll->pushComment($this);
        $thecomments = CommentEngine::getEngine()->renderComments($Coll);

        $start->set_var( 'comments', $thecomments );
        $retval .= '<a name="comment_entry"></a>';
        $retval .= COM_startBlock ($LANG03[14])
            . $start->finish( $start->parse( 'output', 'comment' ))
            . COM_endBlock ();

        return $retval;
    }


    /**
     * Returns number of comments for a specific item.
     *
     * @param   string  $type       Type of object comment is posted to
     * @param   string  $sid        ID of object comment belongs to
     * @param   integer $queued     Queued (0) or published (1)
     * @return  integer     Number of comments
     */
    public static function getCount(string $type, string $sid, int $queued = 0) : int
    {
        global $_TABLES;

        $db = Database::getInstance();

        if ( $type == '' || $sid == '' ) return 0;

        return Database::getInstance()->getCount(
            $_TABLES['comments'],
            array('sid', 'type', 'queued'),
            array($sid, $type, $queued),
            array(Database::STRING, Database::STRING, Database::INTEGER)
        );
    }


    /**
     * Check if the current user is a comment moderator and cache the result.
     *
     * @return  boolean     True if a moderator
     */
    public static function isModerator() : bool
    {
        static $retval = NULL;
        if ($retval === NULL) {
            $retval = SEC_hasRights('comment.moderate');
        }
        return $retval;
    }


    /**
     * Get the redirect url to return to an item view.
     *
     * @param   string  $type   Item type, e.g. 'article' or plugin name
     * @param   string  $sid    Optional item ID
     * @return  object  $this
     */
    public static function makeRedirectUrl(string $type, ?string $sid=NULL) : string
    {
        global $_CONF;

        $urlArray = PLG_getCommentUrlId($type);
        if (is_array($urlArray)) {
            $url = $urlArray[0];
            if (!is_null($sid)) {
                $url .= '?' . $urlArray[1] . '=' . $sid;
            }
        } else {
            $url = $_CONF['site_url'] . '/index.php';
        }
        return $url;
    }


    /**
     * Check that the minimum required information is provided before saving.
     *
     * @return  boolean     True if fields are valid
     */
    public function checkValidData() : bool
    {
        global $_PLUGINS;

        if ($this->type != 'article' && !in_array($this->type, $_PLUGINS)) {
            Log::write('system', Log::WARNING, __METHOD__ . ': invalid item type for comment');
            $this->_errors[] = 'Invalid item type for comment.';
            return false;
        }
        if (empty($this->sid) /*|| empty($this->title)*/ || empty($this->comment)) {
            Log::write('system', Log::WARNING, __METHOD__ . ': invalid data for comment values');
            $this->_errors[] = 'Some fields are missing or invalid';
            return false;
        }
        return true;
    }


    /**
     * Get a ready-to-display error message from the accumulated error strings.
     *
     * @return  string      Unnumbered HTML list of errors
     */
    public function printErrors() : string
    {
        $retval = '';
        if (!empty($this->_errors)) {
            $retval = '<ul><li>' . implode('</li><li>', $this->_errors) . '</li></ul>';
        }
        return $retval;
    }


/*    public function toArray() : array
    {
        return array(
            'cid'])) $this->cid = (int)$A['cid'];
        'type'])) $this->type = $A['type'];
        'sid'])) $this->sid = $A['sid'];
        'date'])) $this->date = $A['date'];
        'title'])) $this->title = $A['title'];
        'comment'])) $this->comment = $A['comment'];
        'pid'])) $this->pid = (int)$A['pid'];
        'queued'])) $this->queued = (int)$A['queued'];
        'postmode'])) $this->postmode = $A['postmode'];
        'lft'])) $this->lft = (int)$A['lft'];
        'rht'])) $this->rht = (int)$A['rht'];
        'indent'])) $this->indent = (int)$A['indent'];
        'name'])) $this->name = $A['name'];
        'uid'])) $this->uid = (int)$A['uid'];
        'ipaddress'])) $this->ipaddress = $A['ipaddress'];
        'username'])) $this->username = $A['username'];
        'fullname'])) $this->fullname = $A['fullname'];
        'photo'])) $this->photo = $A['photo'];
        'email'])) $this->email = $A['email'];
        'nice_date'])) $this->nice_date = $A['nice_date'];
        'pindent'])) $this->pindent = (int)$A['pindent'];
        return $this;
}*/

    /**
     * Set a property value.
     *
     * @param   string  $key    Key name
     * @param   mixed   $value  Value to set
     */
    public function offsetSet($key, $value)
    {
        $this->$key = $value;
    }


    /**
     * Check if a key exists.
     *
     * @param   mixed   $key    Key name
     * @return  boolean     True if the key exists.
     */
    public function offsetExists($key)
    {
        return isset($this->$key);
    }


    /**
     * Remove a property.
     *
     * @param   mixed   $key    Key name
     */
    public function offsetUnset($key)
    {
        unset($this->$key);
    }


    /**
     * Get a value from the properties.
     *
     * @param   mixed   $key    Key name
     * @return  mixed       Value of property, NULL if not set
     */
    public function offsetGet($key)
    {
        return isset($this->$key) ? $this->$key : NULL;
    }


    /**
     * Get the comment properties as an array.
     *
     * @return  array       Properties array
     */
    /*public function toArray()
    {
        return $this->properties;
    }*/


    public function approve() : bool
    {
        global $_TABLES;

        $db = Database::getInstance();
        try {
            $stmt = $db->conn->update(
                $_TABLES['comments'],
                array('queued' => 0),
                array('cid' => $this->cid),
                array(Database::INTEGER, Database::INTEGER)
            );
        } catch (\Throwable $e) {
            if ($db->getIgnore()) {
                $db->_errorlog("SQL Error: " . $e->getMessage());
            } else {
                $db->dbError($e->getMessage(),$sql);
                return false;
            }
        }

        // let plugins know they should update their counts if necessary
        PLG_commentApproved($this->cid, $this->type, $this->sid);
        $c = Cache::getInstance();
        $c->deleteItemsByTags(array('whatsnew','menu', 'comments'));
        if ($this->type == 'article') {
            $c->deleteItemsByTag('story_'.$this->sid);
        }
        // let others know we saved a comment to the prod table
        PLG_itemSaved($this->cid, 'comment');
        return true;
    }

}

