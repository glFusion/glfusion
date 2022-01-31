<?php
/**
 * Class to handle forum warning system.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021-2022 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     v0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Forum\Modules\Warning;
use glFusion\Database\Database;
use glFusion\FieldList;
use Forum\UserInfo;
use Forum\Status;
use Forum\Topic;


class Warning
{
    /** Warning record ID.
     * @var integer */
    private $w_id = 0;

    /** ID of user who was warned.
     * @var integr */
    private $w_uid = 0;

    /** ID of the warning type, e.g. Spam, Harrassment, etc.
     * @var integer */
    private $wt_id = 0;

    /** Forum topic ID that generated the warning.
     * @var integer */
    private $w_topic_id = 0;

    /** Timestamp when the warning was issued.
     * @var integer */
    private $ts = 0;

    /** Short description of the warning.
     * @var string */
    private $w_dscp = '';

    /** Points assigned to this warning, from the WarningType.
     * @var integer */
    private $w_points = 0;

    /** Timestamp when the warning expires.
     * @var integer */
    private $w_expires = 0;

    /** User ID who issued the warning.
     * @var integer */
    private $w_issued_by = 0;

    /** Timestamp when the warning was revoked, if any.
     * @var integer */
    private $revoked_date = 0;

    /** ID of the administrator who revoked the warning.
     * @var integer */
    private $revoked_by = 0;

    /** Reason why the warning was revoked.
     * @var string */
    private $revoked_reason = '';

    /** General comments about the warning.
     * @var string */
    private $w_notes = '';

    /** Warning Type object related to this Warning.
     * @var object */
    private $_WT = NULL;

    /** Return URL for redirect after saving or cancelling.
     * @var string */
    private $_return_url = '';


    /**
     * Constructor.
     * Sets the field values from the supplied array, or reads the record
     * if $A is a warning record ID.
     *
     * @param   mixed   $A  Array of properties or group ID
     */
    public function __construct($A='')
    {
        global $_USER;

        $this->w_issued_by = (int)$_USER['uid'];
        if (is_array($A)) {
            $this->setVars($A, true);
        } elseif (is_numeric($A)) {
            $A = (int)$A;
            if ($A > 0) {
                $this->Read($A);
            }
        }
    }


    /**
     * Read a single warning record into an instantiated object.
     *
     * @param   integer $id     Warning record ID
     * @return  boolean     True on success, False on error or not found
     */
    public function Read(int $w_id) : bool
    {
        global $_TABLES;

        $db = Database::getInstance();
        try {
            $stmt = $db->conn->query(
                "SELECT * FROM {$_TABLES['ff_warnings']}
                WHERE w_id=" . $w_id
            );
        } catch(Throwable $e) {
            if (defined('DVLP_DEBUG')) {
                throw($e);
            }
            return false;
        }
        $A = $stmt->fetch(Database::ASSOCIATIVE);
        if (is_array($A) && !empty($A)) {
            $this->setVars($A, true);
            return true;
        } else {
            return false;
        }
    }


    /**
     * Check if the warning feature is enabled.
     * This abstracts the config variable.
     *
     * @return  boolean     True if enabled, False if not
     */
    public static function featureEnabled() : bool
    {
        global $_FF_CONF;

        return (isset($_FF_CONF['warnings_enabled']) && $_FF_CONF['warnings_enabled']);
    }


    /**
     * Get the current warning percentage for a user.
     *
     * @param   integer $uid    User ID
     * @return  float       Percentage of maximum warning points
     */
    public static function getUserPercent(int $uid) : float
    {
        global $_FF_CONF;

        $points = self::getUserPoints($uid);
        return round(($points / (int)$_FF_CONF['warnings_max_points']) * 100, 2);
    }


    /**
     * Get the raw number of current warning points for a user.
     *
     * @param   integer $uid    User ID
     * @return  integer     Number of currently-active warning points
     */
    public static function getUserPoints(int $uid) : int
    {
        global $_TABLES;

        $db = Database::getInstance();
        $sql = "SELECT sum(w.w_points) AS totalpoints
            FROM {$_TABLES['ff_warnings']} w
            WHERE w.w_uid = $uid
            AND w.w_expires > UNIX_TIMESTAMP()
            AND w.revoked_date = 0";
        try {
            $stmt = $db->conn->query($sql);
        } catch(Throwable $e) {
            if (defined('DVLP_DEBUG')) {
                throw($e);
            }
            return false;
        }
        $A = $stmt->fetch(Database::ASSOCIATIVE);
        if (is_array($A) && isset($A['totalpoints'])) {
            return (int)$A['totalpoints'];
        } else {
            return 0;
        }
    }


    /**
     * Get the current restriction in effect for a user.
     * Restrictions are checked in descending order of severity in case
     * there are multiple in effect.
     *
     * @param   integer $uid    User ID
     * @return  integer     Current restrition
     */
    public static function getUserStatus(int $uid) : int
    {
        global $_TABLES;

        $retval = Status::NONE;
        $U = UserInfo::getInstance($uid);
        if ($U->isBanned()) {
            $retval = Status::BAN;
        } elseif ($U->isSuspended()) {
            $retval = Status::SUSPEND;
        } elseif ($U->isModerated()) {
            $retval = Status::MODERATE;
        }
        return $retval;
    }


    /**
     * Set all property values from DB or form.
     *
     * @param   array   $A          Array of property name=>value
     * @param   bool    $from_db    True of coming from the DB, False for form
     */
    public function setVars(array $A, bool $from_db = true) : self
    {
        global $_FF_CONF, $_TABLES, $_CONF;

        $this->w_id = (int)$A['w_id'];
        $this->w_uid = (int)$A['w_uid'];
        $this->wt_id = (int)$A['wt_id'];
        $this->w_topic_id = (int)$A['w_topic_id'];
        $this->w_dscp = $A['w_dscp'];
        $this->w_notes = $A['w_notes'];

        if ($from_db) {
            // These are only found in the database, the matching fields
            // are created automatically from forms.
            $this->w_expires = (int)$A['w_expires'];
            $this->revoked_by = (int)$A['revoked_by'];
            $this->revoked_date = (int)$A['revoked_date'];
            $this->revoked_reason = $A['revoked_reason'];
            $this->w_issued_by = (int)$A['w_issued_by'];
        }
        return $this;
    }


    /**
     * Set the record ID.
     *
     * @param   string  $warn_title  New title string
     * @return  object  $this
     */
    public function withID(int $w_id) : self
    {
        $this->w_id = $w_id;
        return $this;
    }


    /**
     * Set the user ID.
     *
     * @param   integer $id     User ID
     * @return  object  $this
     */
    public function withUid(int $id) : self
    {
        $this->w_uid = (int)$id;
        return $this;
    }


    /**
     * Set the ID of the topic causing the warning.
     *
     * @param   integer $id     Topic record ID
     * @return  object  $this
     */
    public function withTopicId(int $id) : self
    {
        $this->w_topic_id = (int)$id;
        return $this;
    }


    /**
     * Set the return URL after submitting the warning.
     *
     * @param   string  $url    URL for redirection
     * @return  object  $this
     */
    public function withReturnUrl(string $url) : self
    {
        $this->_return_url = $url;
        return $this;
    }


    /**
     * Get the warning record ID.
     *
     * @return  integer     Record ID
     */
    public function getID() : int
    {
        return (int)$this->w_id;
    }


    /**
     * Get the topic ID. Usually used for redirects after saving.
     *
     * @return  integer     Topic ID
     */
    public function getTopicId() : int
    {
        return (int)$this->w_topic_id;
    }


    /**
     * Set the title string.
     *
     * @param   string  $dscp   New title string
     * @return  object  $this
     */
    public function withDscp(string $dscp) : self
    {
        $this->w_dscp = $dscp;
        return $this;
    }


    /**
     * Set the reason for revoking the warning.
     *
     * @param   string  $text   Reason for revoking
     * @return  object  $this
     */
    public function withRevokedReason(string $text) : self
    {
        $this->revoked_reason = $text;
        return $this;
    }


    /**
     * Get the warning title.
     *
     * @return  string      Title string
     */
    public function getDscp() : string
    {
        return $this->w_dscp;
    }


    /**
     * Get an instance of a warning.
     * Caches locally since the same warning may be requested many times
     * for a single page load.
     *
     * @param   int     $w_id     Warning record ID
     * @return  object  Warning object
     */
    public static function getInstance(int $w_id) : self
    {
        static $cache = array();

        $w_id = (int)$w_id;
        if (!array_key_exists($w_id, $cache)) {
            $cache[$w_id] = new self($w_id);
        }
        return $cache[$w_id];
    }


    /**
     * Get all the warnings in the database for a user.
     *
     * @param   integer $uid    User ID
     * @param   boolean $activeonly     True to get only active warnings
     * @return  array   Array of Warning objects
     */
    public static function getAll(int $uid, bool $activeonly=true) : array
    {
        global $_TABLES;

        $uid = (int)$uid;
        $retval = array();

        $db = Database::getInstance();
        $qb = $db->conn->createQueryBuilder();
        $qb->select('*')
            ->from($_TABLES['ff_warnings'])
            ->where('1=1')
            ->orderBy('w_expires', 'DESC');

        if ($uid > 1) {
            $qb->andWhere('w_uid = ' .$qb->createNamedParameter($uid));
        }
        if ($activeonly) {
            $qb->andWhere('w_expires > UNIX_TIMESTAMP()');
        }
        try {
            $stmt = $qb->execute();
            $A = $stmt->fetchAll(Database::ASSOCIATIVE);
            if (is_array($A) && !empty($A)) {
                foreach ($A as $warning) {
                    $retval[$warning['w_id']] = new self($warning);
                }
            }
        } catch(Throwable $e) {
            if (defined('DVLP_DEBUG')) {
                throw($e);
            }
            return false;
        }
        return $retval;
    }


    /**
     * Delete a single warning record.
     * Does not change any user restrictions that have been set.
     *
     * @param   integer $w_id  Record ID of warning to remove
     */
    public static function Delete(int $w_id) : void
    {
        global $_TABLES;
    
        $db = Database::getInstance();

        $sql = "DELETE FROM `{$_TABLES['ff_warnings']}` WHERE w_id = $w_id";
        $db->conn->executeUpdate($sql);
    }


    /**
     * Revoke a warning.
     *
     * @return  bool    True on success, False on error
     */
    public function Revoke()
    {
        global $_TABLES, $_USER;

        $sql = "UPDATE {$_TABLES['ff_warnings']} SET
            revoked_date = ?,
            revoked_reason = ?,
            revoked_by = ?
            WHERE w_id = ?";
        try {
            $db = Database::getInstance();
            $stmt = $db->conn->executeQuery(
                $sql,
                array(time(), $this->revoked_reason, $_USER['uid'], $this->w_id),
                array(Database::INTEGER, Database::STRING, Database::INTEGER, Database::INTEGER)
            );
            return true;
        } catch(Throwable $e) {
            return false;
        }
    }


    /**
     * Creates the form to view and revoke.
     *
     * @return  string      HTML for edit form
     */
    public function adminView()
    {
        global $_TABLES, $_CONF;

        $T = new \Template($_CONF['path'] . '/plugins/forum/templates/admin/warning/');
        $T->set_file('editform', 'viewwarning.thtml');

        $dt = new \Date($this->w_expires, $_CONF['timezone']);
        $T->set_var(array(
            'w_id'      => $this->w_id,
            'uid'       => $this->w_uid,
            'username'  => COM_getDisplayName($this->w_uid),
            'subject'   => Topic::getInstance($this->w_topic_id)->getSubject(),
            'topic_id'  => $this->w_topic_id,
            'dscp'      => $this->w_dscp,
            'notes'     => $this->w_notes,
            'warningtype' => WarningType::getInstance($this->wt_id)->getDscp(),
            'revoked_reason' => $this->revoked_reason,
            'return_url' => $this->_return_url,
            'expiration' => $dt->format('Y-m-d H:i', true),
        ) );
        $T->parse('output','editform');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Gets the warning form for modal display.
     *
     * @param   integer $uid    ID of user being warned
     * @param   integer $t_id   Topic ID generating the warning
     * @return  string      HTML for edit form
     */
    public static function getPopupForm(int $uid, int $t_id) : string
    {
        global $_CONF;

        $W = new self;      // modal is only for new warnings
        return $W->withUid($uid)
                 ->withTopicId($t_id)
                 ->withReturnUrl($_CONF['site_url'] . "/forum/viewtopic.php?showtopic=$t_id&topic=$t_id#$t_id")
                 ->Edit(true);
    }


    /**
     * Creates the edit form.
     *
     * @return  string      HTML for edit form
     */
    public function Edit(bool $popup=false)
    {
        global $_TABLES, $_CONF;

        $T = new \Template($_CONF['path'] . '/plugins/forum/templates/admin/warning/');
        $T->set_file('editform', 'editwarning.thtml');
        if ($popup) {
            $T->set_var(array(
                'return_url' => $this->_return_url,
                'is_modal' => true,
            ) );
        }

        $T->set_var(array(
            'w_id'      => $this->w_id,
            'uid'       => $this->w_uid,
            'username'  => COM_getDisplayName($this->w_uid),
            'subject'   => Topic::getInstance($this->w_topic_id)->getSubject(),
            'topic_id'  => $this->w_topic_id,
            'dscp'      => $this->w_dscp,
            'notes'     => $this->w_notes,
            'can_revoke' => $this->w_id > 0,
         ) );
        $Types = WarningType::getAvailable();
        $T->set_block('editform', 'WarningTypes', 'wt');
        foreach ($Types as $WT) {
            $T->set_var(array(
                'wt_id' => $WT->getID(),
                'wt_dscp' => $WT->getDscp(),
                'selected' => $WT->getID() == $this->wt_id ? 'checked="checked"' : '',
            ) );
            $T->parse('wt', 'WarningTypes', true);
        }
        $T->parse('output','editform');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Create the list.
     *
     * @return  string      HTML for admim list
     */
    public static function adminList(int $uid=0, bool $activeonly=true) : string
    {
        global $LANG_ADMIN, $_TABLES, $_CONF, $_USER, $LANG_GF01, $LANG_GF93;

        USES_lib_admin();

        $uid = (int)$uid;
        $retval = '';
        $form_arr = array();

        $header_arr = array(
            array(
                'text'  => $LANG_GF01['dscp'],
                'field' => 'wt_dscp',
                'sort'  => true,
                'align' => 'left',
            ),
            array(
                'text'  => $LANG_GF01['points'],
                'field' => 'w_points',
                'sort'  => true,
                'align' => 'left',
            ),
            array(
                'text'  => $LANG_GF01['issued'],
                'field' => 'ts',
                'sort'  => true,
                'align' => 'left',
            ),
            array(
                'text'  => $LANG_GF01['expires'],
                'field' => 'w_expires',
                'sort'  => true,
                'align' => 'left',
            ),
            array(
                'text'  => $LANG_GF01['issued_by'],
                'field' => 'w_issued_by',
                'sort'  => true,
                'align' => 'left',
            ),
            array(
                'text'  => $LANG_GF01['status'],
                'field' => 'status',
                'sort'  => false,
                'align' => 'left',
            ),
            array(
                'text'  => $LANG_ADMIN['edit'],
                'field' => 'edit',
                'sort'  => false,
                'align' => 'center',
            ),
            array(
                'text'  => $LANG_ADMIN['delete'],
                'field' => 'delete',
                'sort'  => false,
                'align' => 'center',
            ),
        );

        if ($uid < 2) {
            array_unshift($header_arr, array(
                'text'  => $LANG_GF01['username'],
                'field' => 'w_uid',
                'sort'  => true,
                'align' => 'left',
            ) );
        }

        $options = array('chkdelete' => 'true', 'chkfield' => 'w_id');
        $defsort_arr = array('field' => 'w_expires', 'direction' => 'ASC');
        $sql = "SELECT w.*, wt.wt_dscp, u.username, u.fullname
            FROM {$_TABLES['ff_warnings']} w
            LEFT JOIN {$_TABLES['ff_warningtypes']} wt
            ON wt.wt_id = w.wt_id
            LEFT JOIN {$_TABLES['users']} u
            ON u.uid = w.w_uid
            WHERE 1=1";
        if ($uid > 1) {
            $sql .= " AND w_uid = $uid";
        }
        if ($activeonly) {
            $sql .= " AND w_expires > UNIX_TIMESTAMP()";
        }
        $query_arr = array('table' => 'ff_warnings',
            'sql' => $sql,
            'query_fields' => array('w_dscp', 'fullname', 'username'),
        );
        $text_arr = array(
            'has_extras' => true,
            'form_url' => $_CONF['site_admin_url'] . '/plugins/forum/warnings.php?log=' . $uid,
        );

        $retval .= ADMIN_list(
            'warnings_user_admlist',
            array(__CLASS__, 'getAdminField'), $header_arr,
            $text_arr, $query_arr, $defsort_arr, '', '', $options, $form_arr
        );
        return $retval;
    }


    /**
     * Get the correct display for a single field in the warning list.
     *
     * @param  string  $fieldname  Field variable name
     * @param  string  $fieldvalue Value of the current field
     * @param  array   $A          Array of all field names and values
     * @param  array   $icon_arr   Array of system icons
     * @return string              HTML for field display within the list cell
     */
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr, $extra=array())
    {
        global $_CONF, $LANG_ACCESS, $LANG_ADMIN, $LANG_GF01;

        $retval = '';
        $base_url = $_CONF['site_admin_url'] . '/plugins/forum/warnings.php';

        switch($fieldname) {
        case 'edit':
            $retval = FieldList::edit(
                array(
                    'url' => $base_url . '?viewwarning=' .$A['w_id'],
                )
            );
            break;

        case 'delete':
            $retval .= FieldList::delete(
                array(
                    'delete_url' => $base_url.'?deletewarning=' . $A['w_id'],
                    'attr' => array(
                        'title'   => $LANG_ADMIN['delete'],
                        'onclick' => "return confirm('{$LANG_GF01['DELETECONFIRM']}');"
                    ),
                )
            );
            break;

        case 'w_issued_by':
            $retval .= COM_getDisplayName($fieldvalue);
            break;

        case 'ts':
            $dt = new \Date($fieldvalue, $_CONF['timezone']);
            $retval .= $dt->toMySql(true);
            break;

        case 'w_expires':
            $dt = new \Date($fieldvalue, $_CONF['timezone']);
            $retval .= $dt->toMySql(true);
            if ($fieldvalue < time() || $A['revoked_by'] > 0) {
                $retval = '<span style="background-color:lightgrey;">' . $retval . '</span>';
            }
            break;

        case 'status':
            if ($A['revoked_by'] > 0) {
                $retval .= $LANG_GF01['revoked_by'] . ' '  . COM_getDisplayName($A['revoked_by']);
            } elseif ($A['w_expires'] < time()) {
                $retval .= $LANG_GF01['expired'];
            } else {
                $retval .= $LANG_GF01['active'];
            }
            break;

        case 'w_uid':
            $retval .= COM_createLink(
                COM_getDisplayName($A['w_uid']),
                $_CONF['site_admin_url'] . '/plugins/forum/warnings.php?log=' . $A['w_uid'],
            );
            break;

        default:
            $retval .= $fieldvalue;
            break;
        }
        return $retval;
    }


    /**
     * Save a warning from the edit form.
     *
     * @param   array   $A      Array of fields, e.g. $_POST
     * @return  string      Error messages, empty string on success
     */
    public function Save($A = array())
    {
        global $_TABLES, $LANG_GF01, $_USER;

        if (!empty($A)) {
            $this->setVars($A, false);
        }

        $this->_WT = WarningType::getInstance($this->wt_id);

        try {
            $db = Database::getInstance();
            $qb = $db->conn->createQueryBuilder();
            if ($this->w_id > 0) {
                $expires = $this->w_expires;  // using existing value
                $sql = "UPDATE {$_TABLES['ff_warnings']} SET ";
                $qb->update($_TABLES['ff_warnings'])
                             ->where('w_id = :w_id');
            } else {
                $expires = time() + $this->_WT->getExpirationSeconds();
                $sql = "INSERT INTO {$_TABLES['ff_warnings']} SET ";
                $qb->insert($_TABLES['ff_warnings']);
            }
            $qb->values(array(
                'w_uid' => ':w_uid',
                'wt_id' => ':wt_id',
                'w_topic_id' => ':w_topic_id',
                'ts' => 'UNIX_TIMESTAMP()',
                'w_points' => ':w_points',
                'w_expires' => ':w_expires',
                'w_issued_by' => ':w_issued_by',
                'revoked_date' => ':revoked_date',
                'revoked_by' => ':revoked_by',
                'revoked_reason' => ':revoked_reason',
                'w_dscp' => ':w_dscp',
                'w_notes' => ':w_notes',
                'w_issued_by' => ':w_issued_by',
            ) )
                ->setParameter('w_uid', $this->w_uid)
                ->setParameter('wt_id', $this->wt_id)
                ->setParameter('w_topic_id', $this->w_topic_id)
                ->setParameter('w_points', $this->_WT->getPoints())
                ->setParameter('w_expires', $expires)
                ->setParameter('w_issued_by', $this->w_issued_by)
                ->setParameter('revoked_date', $this->revoked_date)
                ->setParameter('revoked_by', $this->revoked_by)
                ->setParameter('revoked_reason', $this->revoked_reason)
                ->setParameter('w_dscp', $this->w_dscp)
                ->setParameter('w_notes', $this->w_notes)
                ->setParameter('w_id', $this->w_id)
                ->setParameter('w_issued_by', $_USER['uid']);
            $stmt = $qb->execute();
            // Take action, if necessary
            $this->takeAction();
            // Notify the user, if selected
            if (isset($A['notify'])) {
                $this->notifyUser((int)$A['notify']);
            }
            return true;
        } catch(Throwable $e) {
            return false;
        }
    }


    /**
     * Notify the user being warned.
     *
     * @param   integer $type   Type of notification (1=PM, 2=Email)
     */
    private function notifyUser(int $type) : void
    {
        global $_CONF, $LANG_GF01;

        $T = new \Template($_CONF['path'] . '/plugins/forum/templates/admin/warning/');
        switch ($type) {
        case 1:         // Notify via Email
            $T->set_file('email', 'notify_email.thtml');
            $Topic = Topic::getInstance($this->w_topic_id);
            $T->set_var(array(
                'post_topic' => $Topic->getSubject(),
                'warn_type' => $this->_WT->getDscp(),
                'warn_dscp' => $this->getDscp(),
            ) );
            $T->parse('output', 'email');
            $html_msg = $T->finish($T->get_var('output'));
            $html2TextConverter = new \Html2Text\Html2Text($html_msg);
            $text_msg = $html2TextConverter->getText();
            COM_emailNotification(array(
                'to' => array($Topic->getEmail()),
                'from' => array(
                        'email' => $_CONF['noreply_mail'],
                        'name'  => $_CONF['site_name'],
                ),
                'htmlmessage' => $html_msg,
                'textmessage' => $text_msg,
                'subject' => $_CONF['site_name'] . ': ' . $LANG_GF01['warn_email_subject'],
            ) );
            break;
        case 2:         // Notify via the PM plugin, maybe future
            break;
        }
    }


    /**
     * Execute the action based on the current warning level.
     *
     * @return  boolean     True on success, False on error
     */
    public function takeAction() : bool
    {
        global $_TABLES, $LANG_GF01;

        if (!self::canWarnUser($this->w_uid)) {
            // Can only act on logged-in users, and not administrators.
            return false;
        }

        if ($this->_WT === NULL) {
            $this->_WT = WarningType::getInstance($this->wt_id);
        }

        // Get the user's current threshold percentage, including this warning.
        $percent = self::getUserPercent($this->w_uid);
        // Get the warning level that has been reached with this warning.
        $WL = WarningLevel::getByPercent($percent);
        if ($WL->getID() < 1 || $WL->getAction() < 1) {
            // No matching warninglevel record found.
            return false;
        }

        $status = $WL->getAction();
        if ($status == Status::SITE_BAN) {
            // Update the users table with a permanent site ban.
            $sql = "UPDATE {$_TABLES['users']}
                SET status = " . USER_ACCOUNT_DISABLED .
                " WHERE uid = {$this->w_uid}";
        } else {
            $expiration = time() + $WL->getDuration();
            $field = Status::getKey($status) . '_expires';
            $sql = "UPDATE {$_TABLES['ff_userinfo']}
                SET $field = $expiration
                WHERE uid = {$this->w_uid}";
        }
        $db = Database::getInstance();
        try {
            $db->conn->executeUpdate($sql);
            COM_errorLog(
                "User {$this->w_uid} forum status updated: " .
                $LANG_GF01[Status::getKey($status)]
            );
            return true;
        } catch(Throwable $e) {
            COM_errorLog("SQL Error: $sql");
            return false;
        }
    }


    /**
     * Check if a given user can be warned.
     * Warnings don't apply to anonymous users or admins.
     *
     * @param   integer $uid    User ID to check
     * @return  boolean     True if warnings cam be applied, False if not
     */
    public static function canWarnUser(?int $uid=NULL) : bool
    {
        global $_USER;
        static $retval = array();

        if ($uid === NULL) {
            $uid = (int)$_USER['uid'];
        }
        if (!array_key_exists($uid, $retval)) {
            $val = true;
            if ($uid < 2 || SEC_inGroup('Root', $uid)) {
                $val = false;
            } else {
                $perms = SEC_getUserPermissions('', $uid);
                if (strstr($perms, 'forum.edit') !== false) {
                    $val = false;
                }
            }
        }
        $retval[$uid] = $val;
        return $retval[$uid];
    }

}

