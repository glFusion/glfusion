<?php
/**
 * Class to handle storang and displaying popup messages.
 * Saves messages in the database to display to the specified user
 * at a later time. Supports searching by session ID to allow placing
 * messages from external sources to be viewed by the anonymous user.
 * For messages that should show on the next page load, just use the COM_*
 * functions.
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
 * Class to handle popup messages stored for later display.
 * @package glfusion
 */
class Popup extends \glFusion\Notifier
{
    // Flag to store only one message per uid/pi_code combination.
    const UNIQUE = 1;

    // Flag to override existing message vs. leave it alone, if unique is set.
    const OVERWRITE = 2;

    /** Message level, default "info".
     * @var integer */
    private $level = 1;

    /** Plugin Name.
     * @var string */
    private $pi_name = 'glfusion';

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
     * Add a recipient.
     * Leverages the email field to store the session ID.
     *
     * @param   integer $uid    User ID
     * @param   string  $name   User Name
     * @param   string  $email  Session ID for this notifier
     * @return  object  $this
     */
    public function addRecipient(int $uid, ?string $name=NULL, ?string $email=NULL) : self
    {
        if ($uid < 2 && $email === NULL) {
            $email = session_id();
        }
        $this->recipients[] = array(
            'uid' => $uid,
            'name' => $name,
            'email' => $email,
        );
        return $this;
    }


    /**
     * Check if a specific message exists.
     * Looks for the user ID, session ID and plugin name/code.
     *
     * @return  array   Array of matching messages
     */
    public function getMatching() : array
    {
        global $_TABLES;

        $params = array();
        $values = array();
        $types = array();

        if ($this->uid > 1) {
            $params[] = 'uid = ?';
            $values[] = $this->uid;
            $types[] = Database::INTEGER;
        } elseif (!empty($this->sess_id)) {
            $params[] = 'sess_id = ?';
            $values[] = $this->sess_id;
            $types[] = Database::STRING;
        } else {
            // If not a regular user, and no session ID given, return an empty
            // array. Otherwise everything for pi_name/pi_code will match.
            return array();
        }

        if (!empty($this->pi_name)) {
            $params[] = 'pi_name = ?';
            $values[] = $this->pi_name;
            $types[] = Database::STRING;
        }
        if (!empty($this->pi_code)) {
            $params[] = 'pi_code = ?';
            $values[] = $this->pi_code;
            $types[] = Database::STRING;
        }
        $data = array();
        if (!empty($params)) {
            $params = implode(' AND ', $params);
            $db = Database::getInstance();
            try {
                $data = $db->conn->executeQuery(
                    "SELECT * FROM {$_TABLES['sysmessages']} WHERE $params",
                    $values,
                    $types
                )->fetchAllAssociative() ;
            } catch (\Exception $e) {
                Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
                $data = false;
            }
        }
        return is_array($data) ? $data : array();
    }


    /**
     * Store a message in the database that can be retrieved later.
     * This provides a more flexible method for showing popup messages
     * than the numbered-message method.
     *
     * @return  boolean     True on success, False on error
     */
    public function send() : bool
    {
        global $MESSAGE;

        if (empty($this->textmessage)) {
            return false;
        }

        if (empty($this->subject)) {
            $this->subject= $MESSAGE[40];
        }
        foreach ($this->recipients as $recip) {
            $this->_store($recip);
        }
        return true;
    }


    /**
     * Store a message to a single recipient.
     *
     * @param   array   $recip  Array of recipient info
     */
    private function _store(array $recip) : void
    {
        global $_TABLES, $_CONF;

        $this->uid = (int)$recip['uid'];
        if ($this->uid == 1) {
            // For messages to anonymous, set the expiration to the
            // session timeout.
            $this->setExpiration(time() + (int)$_CONF['session_cookie_timeout']);
            $this->sess_id = $recip['email'];
        }

        $do_insert = true;      // insert or update?
        $db = Database::getInstance();
        $qb = $db->conn->createQueryBuilder();
        if ($this->unique) {
            // Can't use a unique key constraint since the plugin may
            // allow multiple messages to a user ID for a single code.
            $msgs = $this->getMatching();
            if (count($msgs) > 0) {
                if (!($this->unique & self::OVERWRITE)) {
                    // Do nothing since this message is already set
                    return;
                } else {
                    $do_insert = false;
                }
            }
        }

        if ($do_insert) {
            // Just insert a new, possibly duplicate, message
            $qb->insert($_TABLES['sysmessages'])
               ->setValue('uid', ':uid')
               ->setValue('pi_name', ':pi_name')
               ->setValue('pi_code', ':pi_code')
               ->setValue('sess_id', ':sess_id')
               ->setValue('title', ':title')
               ->setValue('message', ':message')
               ->setValue('persist', ':persist')
               ->setValue('expires', ':expires')
               ->setValue('level', ':level');
        } else {
            // Update the existing message
            $qb->update($_TABLES['sysmessages'])
               ->where('msg_id = :msg_id')
               ->setParameter('msg_id', $msgs[0]['msg_id'], Database::INTEGER)
               ->set('sess_id', ':sess_id')
               ->set('title', ':title')
               ->set('message', ':message')
               ->set('persist', ':persist')
               ->set('expires', ':expires')
               ->set('level', ':level');
        }
        $qb->setParameter('uid', $this->uid, Database::INTEGER)
           ->setParameter('pi_name', $this->pi_name, Database::STRING)
           ->setParameter('pi_code', $this->pi_code, Database::STRING)
           ->setParameter('sess_id', $this->sess_id, Database::STRING)
           ->setParameter('title', $this->subject, Database::STRING)
           ->setParameter('message', $this->textmessage, Database::STRING)
           ->setParameter('persist', $this->persist, Database::STRING)
           ->setParameter('expires', $this->getExpiration(), Database::STRING)
           ->setParameter('level', $this->level, Database::STRING);
        try {
            $stmt = $qb->execute();
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
        }
    }


    /**
     * Display all messages for the current user.
     * Deletes the messages when done.
     *
     * @param   boolean $persist    Keep the message box open? False = fade out
     * @return  string      HTML for message box
     */
    public static function showAll(?bool $persist = NULL) : string
    {
        global $MESSAGE, $_USER;
        $retval = '';

        self::expire();

        $msgs = self::getByUid($_USER['uid']);
        if (empty($msgs)) {
            return '';
        }

        // Include a zero element in case level is undefined
        $levels = array('info', 'info', 'success', 'warning', 'error');

        if (count($msgs) == 1) {
            // Just show the single message
            $message = $msgs[0]['message'];
            $title = $msgs[0]['title'];
            $level = $msgs[0]['level'];
            if ($msgs[0]['persist']) $persist = true;
        } else {
            // Create a list of messages.
            $message = '';
            $title = '';
            $level = 1;     // Start at the "best" level
            foreach ($msgs as $msg) {
                $message .= '<li>' . $msg['message'] . '</li>';
                // If any message requests "persist", then all persist
                if ($msg['persist']) $persist = true;
                // Set to the highest (worst) error level
                if ($msg['level'] > $level) $level = $msg['level'];
                // First title found in a message gets used instead of default
                if (empty($title) && !empty($msg['title'])) $title = $msg['title'];
            }
            $message = '<ul>' . $message . '</ul>';
        }

        // Delete all the messages that were shown.
        self::deleteByUid($_USER['uid']);

        // Revert to the system message title if no other title found.
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
     * @param   integer $uid    User ID, normally the current user
     * @return  array   Array of messages, title=>message
     */
    public static function getByUid(int $uid) : array
    {
        global $_TABLES;

        $messages = array();
        $params = array();
        $values = array();
        $types = array();

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
                ORDER BY created DESC",
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
    protected function _deleteExpired() : void
    {
        global $_TABLES;

        $db = Database::getInstance();
        try {
            $db->conn->executeStatement(
                "DELETE FROM {$_TABLES['sysmessages']}
                WHERE expires < UNIX_TIMESTAMP()"
            );
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
        }
    }


    /**
     * Delete all messages for a plugin.
     * Called during plugin deletion.
     *
     * @param   string  $pi_code    Plugin Code
     * @param   integer $uid        User ID, NULL for all users.
     */
    public static function deletePlugin(string $pi_name) : void
    {
        global $_TABLES;

        if (empty($pi_name)) {
            return;
        }

        $db = Database::getInstance();
        try {
            $db->conn->executeStatement(
                "DELETE FROM {$_TABLES['sysmessages']} WHERE pi_name LIKE ?",
                array($pi_name . '%'),
                array(Database::STRING)
            );
        } catch (\Exception $e) {
            Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
        }
    }


    /**
     * Delete messages matching a plugin code, optionally limiting to a single user.
     *
     * @param   string  $pi_code    Plugin Code
     * @param   integer $uid        User ID, NULL for all users.
     */
    public static function delete(string $pi_code, ?int $uid=NULL) : void
    {
        global $_TABLES;

        if (empty($pi_code)) {
            return;
        }
        $params = array('pi_code' => $pi_code);
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
    public static function deleteByUid(int $uid) : void
    {
        global $_TABLES;

        // delete messages for the user or session that have not expired.
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
            $db->conn->executeStatement(
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
    public function setLevel(string $level) : self
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
        case 'success':
        case 2:
            // No corresponding Log level
            $this->level = 2;
            break;
        case 'info':
        case 1:
        case Log::INFO:
        default:
            $this->level = 1;
            break;
        }
        return $this;
    }


    /**
     * Set the recipient user ID.
     * Normally used only when checking if a message exists.
     *
     * @todo    Where used?
     * @param   integer $uid    Recipient user ID
     * @return  object  $this
     */
    public function setUid(int $uid) : self
    {
        $this->uid = $uid;
        return $this;
    }


    /**
     * Set the plugin name.
     * This may be the plugin name or other optional ID.
     *
     * @param   string  $pi_code    Plugin-supplied code
     * @return  object  $this
     */
    public function setPlugin(string $pi_name, ?string $pi_code=NULL) : self
    {
        $this->pi_name = $pi_name;
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
    public function setPersists(?bool $persist = NULL) : self
    {
        $this->persist = $persist ? 1 : 0;
        return $this;
    }


    /**
     * Use the session ID, used for anonymous users.
     *
     * @param   boolean $flag   True to use the session ID
     * @return  object  $this
     */
    public function setSessId(bool $flag=true) : self
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
    public function setUnique(bool $flag=true) : self
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
    public function setOverwrite(bool $flag=true) : self
    {
        if ($flag) {
            $this->unique |= self::OVERWRITE;
        } else {
            $this->unique -= self::OVERWRITE;
        }
        return $this;
    }

}

