<?php
/**
 * Class to handle storang and displaying popup messages.
 * Saves messages in the database to display to the specified user
 * at a later time.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2022 <lee@leegarner.com>
 * @package     glfusion
 * @version     v2.1.0
 * @since       v2.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Notifiers;
use glFusion\Database\Database;
use glFusion\Log\Log;


/**
 * Class to handle messages stored for later display.
 * @package glfusion
 */
class SysMessage extends \glFusion\Notifier
{
    const UNIQUE = 1;
    const OVERWRITE = 2;

    /** Message level, default "info".
     * @var integer */
    private $level = 1;

    /** Plugin-supplied code.
     * @var string */
    private $pi_code = '';

    /** Flag for the message to persist or disappear.
     * @var boolean */
    private $persist = 0;

    /** Session ID, set for anonymous users.
     * @var string */
    private $sess_id = '';

    /** Flag indicating only one copy of the message should be stored.
     * 0 = not at all, 1 = leave existing message alone, 2 = overwrite
     * @var integer */
    private $unique = 0;

    /** Recipient user ID for a specific message.
     * @var integer */
    private $uid = 0;


    /**
     * Check if a specific message exists.
     * Looks for the user ID, session ID and plugin code.
     *
     * @return  boolean     True if message exists, False if not
     */
    public function msgExists() : int
    {
        global $_TABLES;

        $params = array('uid');
        $values = array($this->uid);
        $types = array(Database::INTEGER);
        if (!empty($this->sess_id)) {
            $params[] = 'sess_id';
            $values[] = $this->sess_id;
            $types[] = Database::STRING;
        }
        if (!empty($this->pi_code)) {
            $params[] = 'pi_code';
            $values[] = $this->pi_code;
            $types[] = Database::STRING;
        }
        $db = Database::getInstance();
        $data = $db->getCount(
            $_TABLES['sysmessages'],
            $params,
            $values,
            $types
        );
        return $data != 0;
    }


    /**
     * Store a message in the database that can be retrieved later.
     * This provides a more flexible method for showing popup messages
     * than the numbered-message method.
     */
    public function send() : void
    {
        global $MESSAGE;

        if (empty($this->textmessage)) {
            return;
        }

        if (empty($this->title)) {
            $this->title = $MESSAGE[40];
        }
        foreach ($this->recipients as $recip) {
            $uid = (int)$recip['uid'];
            $this->_store($uid);
        }
    }


    /**
     * Store a message to a single recipient.
     *
     * @param   integer $uid    Recipient user ID
     */
    private function _store(int $uid) : void
    {
        global $_USER, $_TABLES;

        $db = Database::getInstance();
        $qb = $db->conn->createQueryBuilder();
        if ($this->unique) {
            $msg_id = (int)$db->getItem(
                $_TABLES['sysmessages'],
                'msg_id',
                array(
                    'uid' => $uid,
                    'pi_code' => $this->pi_code,
                )
            );
            if ($msg_id > 0) {
                if ($this->unique) {
                    // Do nothing since this message is already set
                    return;
                } else {
                    // Update the existing message
                    $qb->update($_TABLES['sysmessages'])
                       ->where('msg_id = :msg_id')
                       ->setParameter('msg_d', $msg_id, Database::INTEGER)
                       ->set('sess_id', ':sess_id')
                       ->set('title', ':title')
                       ->set('message', ':message')
                       ->set('persist', ':persist')
                       ->set('expires', ':expires')
                       ->set('level', ':level');
                }
            }
        } else {
            // Just insert a new, possibly duplicate, message
            $qb->insert($_TABLES['sysmessages'])
               ->setValue('uid', ':uid')
               ->setValue('pi_code', ':pi_code')
               ->setValue('sess_id', ':sess_id')
               ->setValue('title', ':title')
               ->setValue('message', ':message')
               ->setValue('persist', ':persist')
               ->setValue('expires', ':expires')
               ->setValue('level', ':level');
        }
        $qb->setParameter('uid', $uid, Database::INTEGER)
           ->setParameter('pi_code', $this->pi_code, Database::STRING)
           ->setParameter('sess_id', $this->sess_id, Database::STRING)
           ->setParameter('title', $this->subject, Database::STRING)
           ->setParameter('message', $this->textmessage, Database::STRING)
           ->setParameter('persist', $this->persist, Database::STRING)
           ->setParameter('expires', $this->exp_ts, Database::STRING)
           ->setParameter('level', $this->level, Database::STRING);
        try {
            $stmt = $qb->execute();
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
        }
    }


    /**
     * Display all messagse for a user.
    *
     * @param   boolean $persist    Keep the message box open? False = fade out
     * @return  string      HTML for message box
     */
    public static function showAll($persist = false)
    {
        global $MESSAGE;
        $retval = '';

        self::expire();

        $msgs = self::getAll();
        if (empty($msgs)) {
            return '';
        }

        // Include a zero element in case level is undefined
        $levels = array('info', 'info', 'success', 'warning', 'error');
        $persist = false;

        if (count($msgs) == 1) {
            $message = $msgs[0]['message'];
            $title = $msgs[0]['title'];
            $level = $msgs[0]['level'];
            if ($msgs[0]['persist']) $persist = true;
        } else {
            $message = '';
            $title = '';
            $level = Log::INFO;     // Start at the "best" level
            foreach ($msgs as $msg) {
                $message .= '<li class="lglmessage">' .
                    $msg['message'] .
                    '</li>';
                // If any message requests "persist", then all persist
                if ($msg['persist']) $persist = true;
                // Set to the highest (worst) error level
                if ($msg['level'] > $level) $level = $msg['level'];
                // First title found in a message gets used instead of default
                if (empty($title) && !empty($msg['title'])) $title = $msg['title'];
            }
            $message = '<ul class="lglmessage">' . $message . '</ul>';
        }

        self::deleteUser();

        // Revert to the system message title if no other title found
        if (empty($title)) {
            $title = $MESSAGE[40];
        }
        $leveltxt = isset($levels[$level]) ? $levels[$level] : 'info';
        return COM_showMessageText($message, $title, $persist, $leveltxt);
    }


    /**
     * Retrieve all messages for display.
     * Gets all messages from the DB where the user ID matches for
     * non-anonymous users, OR the session ID matches. This allows a message
     * caused by an anonymous action to be displayed to the user after login.
     *
     * @return  array   Array of messages, title=>message
     */
    public static function getAll() : array
    {
        global $_TABLES, $_USER;

        $messages = array();
        $params = array();
        $values = array();
        $types = array();

        $uid = (int)$_USER['uid'];
        if ($uid > 1) {
            $params[] = 'uid = ?';
            $values[] = $uid;
            $types[] = Database::INTEGER;
        } else {
            // Get the session ID for messages to anon users. If a message was
            // stored before the user was logged in this will allow them to see it.
            $params[] = 'sess_id = ?';
            $values[] = session_id();
            $types[] = Database::STRING;
        }
        $params = implode(' OR ' , $params);
        if (empty($params)) {
            return $messages;
        }

        $db = Database::getInstance();
        try {
            $data = $db->conn->executeQuery(
                "SELECT title, message, persist, level
                FROM {$_TABLES['sysmessages']}
                WHERE $params
                ORDER BY dt DESC",
                $values,
                $types
            )->fetchAll(Database::ASSOCIATIVE);
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
            $data = false;
        }
        if (is_array($data)) {
            return $data;
        } else {
            return array();
        }
        return $messages;
    }


    /**
     * Delete expired messages.
     */
    public static function expire() : void
    {
        global $_TABLES;

        $db = Database::getInstance();
        try {
            $db->conn->executeUpdate(
                "DELETE FROM {$_TABLES['sysmessages']}
                WHERE expires < UNIX_TIMESTAMP()"
            );
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
        }
    }


    /**
     * Delete all messages for a plugin, optionally limiting to a single user.
     *
     * @param   string  $pi_name    Plugin Name
     * @param   integer $uid        User ID, NULL for all users.
     */
    public static function delete(string $pi_name, ?int $uid=NULL) : void
    {
        global $_TABLES;

        if (empty($pi_name)) {
            return;
        }
        $params = array('pi_code' => $pi_name);
        $types = array(Database::STRING);
        if (!empty($uid)) {
            $params['uid'] = (int)$uid;
            $types[] = Database::INTEGER;
        }

        $db = Database::getInstance();
        try {
            $db->conn->delete($_TABLES['sysmessages'], $params, $types);
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
        }
    }


    /**
     * Delete all messages for the current user.
     * Checks for messages where the session ID matches the current session,
     * or the user ID matches for logged-in users.
     * This is used to clear messages after display, or when a user is deleted.
     */
    public static function deleteUser() : void
    {
        global $_TABLES, $_USER;

        // delete messages for the user or session that have not expired.
        $uid = (int)$_USER['uid'];
        $params = array('sess_id = ?');
        $values = array(session_id());
        $types = array(Database::STRING);

        if ($uid > 1) {
            $params[] = 'uid = ?';
            $values[] = $uid;
            $types[] = Database::INTEGER;
        }

        $db = Database::getInstance();
        $query = '(' . implode(' OR ', $params) . ')';
        try {
            $db->conn->executeUpdate(
                "DELETE FROM {$_TABLES['sysmessages']} WHERE $query",
                $values,
                $types
            );
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
        }
    }


    /**
     * Set the message level (info, error, etc).
     * Several options can be supplied for the level values.
     *
     * @param   string  $level  Message level.
     * @return  object  $this
     */
    public function withLevel($level)
    {
        switch ($level) {
        case 'error':
        case 'err':
        case false:
        case 'alert':
        case 4:
        case Log::ERROR:
            $this->level = 4;
            break;
        case 'warn':
        case 'warning':
        case 3:
        case Log::WARNING:
            $this->level = 3;
            break;
        case 'info':
        case 1:
        case Log::INFO:
        default:
            $this->level = 1;
            break;
        case 'success':
        case 2:
            // No corresponding Log level
            $this->level = 2;
            break;
        }
        return $this;
    }


    /**
     * Set the recipient user ID.
     * Normally used only when checking if a message has been sent.
     *
     * @param   integer $uid    Recipient user ID
     * @return  object  $this
     */
    public function withUid(int $uid) : self
    {
        $this->uid = $uid;
        return $this;
    }


    /**
     * Set the plugin code.
     * This may be the plugin name or other optional ID.
     *
     * @param   string  $pi_code    Plugin-supplied code
     * @return  object  $this
     */
    public function withPlugin($pi_code) : self
    {
        $this->pi_code = $pi_code;
        return $this;
    }


    /**
     * Set the flag to determine if the message stays on-screen.
     * Assumes persistence is desired if called without a parameter.
     *
     * @param   boolean $persist    True to persist, False to disappear
     * @return  object  $this
     */
    public function withPersists(?bool $persist = NULL) : self
    {
        if ($persist === NULL) {
            $this->persist = 1;
        } else {
            $this->persist = $persist ? 1 : 0;
        }
        return $this;
    }


    /**
     * Set the message text to display.
     *
     * @param   string  $msg    Message text
     * @return  object  $this
     */
    public function withMessage($msg)
    {
        $this->message = $msg;
        return $this;
    }


    /**
     * Set the message title.
     *
     * @param   string  $title  Title to be displayed
     * @return  object  $this
     */
    public function withTitle($title)
    {
        $this->title = $title;
        return $this;
    }


    /**
     * Use the session ID, used for anonymous users.
     *
     * @param   boolean $flag   True to use the session ID
     * @return  object  $this
     */
    public function withSessId($flag)
    {
        $this->sess_id = $flag ? session_id() : '';
        return $this;
    }


    /**
     * Set the flag indicating whether to store only one copy of this message.
     *
     * @param   boolean $flag   True to store only one, False for multiple
     * @return  object  $this
     */
    public function withUnique(bool $flag) : self
    {
        if ($flag) {
            $this->unique |= self::UNIQUE;
        } else {
            $this->unique -= self::UNIQUE;
        }
        return $this;
    }


    /**
     * Set the flag indicating whether to overwrite this message when updated.
     *
     * @param   boolean $flag   True to overwrite, False to leave alone.
     * @return  object  $this
     */
    public function withOverwrite(bool $flag) : self
    {
        if ($flag) {
            $this->unique |= self::OVERWRITE;
        } else {
            $this->unique -= self::OVERWRITE;
        }
        return $this;
    }

}

