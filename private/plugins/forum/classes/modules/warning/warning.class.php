<?php
/**
 * Class to handle forum warning system.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
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
    private $expires = 0;

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

        $sql = "SELECT * FROM {$_TABLES['ff_warnings']}
                WHERE w_id=" . $w_id;
        //echo $sql;die;
        $res = DB_query($sql);
        if ($res && DB_numRows($res) == 1) {
            $A = DB_fetchArray($res, false);
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

        return (isset($_FF_CONF['warning_enabled']) && $_FF_CONF['warning_enabled']);
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
        return round(($points / (int)$_FF_CONF['warning_max_points']) * 100, 2);
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

        $sql = "SELECT sum(w.w_points) AS totalpoints
            FROM {$_TABLES['ff_warnings']} w
            WHERE w.w_uid = $uid
            AND w.w_expires > UNIX_TIMESTAMP()
            AND w.revoked_date = 0";
        $res = DB_query($sql);
        if ($res && DB_numRows($res) == 1) {
            $A = DB_fetchArray($res, false);
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
            $this->revoked_reason = (int)$A['revoked_reason'];
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


    public function withTopicId(int $id) : self
    {
        $this->w_topic_id = (int)$id;
        return $this;
    }


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


    public static function getAll($uid, $activeonly=true)
    {
        global $_TABLES;

        $uid = (int)$uid;
        $retval = array();
        $sql = "SELECT * FROM {$_TABLES['ff_warnings']} WHERE 1=1";
        if ($uid > 1) {
            $sql .= " AND w_uid = $uid";
        }
        if ($activeonly) {
            $sql .= " AND w_expires > UNIX_TIMESTAMP()";
        }
        $sql .= " ORDER BY w_expires DESC";
        $res = DB_query($sql);
        if ($res) {
            while ($A = DB_fetchArray($res, false)) {
                $retval[$A['w_id']] = new self($A);
            }
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

        DB_delete($_TABLES['ff_warnings'], 'w_id', $w_id);
    }


    /**
     * Revoke a warning.
     *
     * @return  bool    True on success, False on error
     */
    public function Revoke()
    {
        global $_TABLES, $_USER;

        $uid = (int)$_USER['uid'];
        $reason = DB_escapeString($this->revoked_reason);
        $dt = time();
        $sql = "UPDATE {$_TABLES['ff_warnings']} SET
            revoked_date = $dt,
            revoked_reason = '$reason',
            revoked_by = $uid
            WHERE w_id = {$this->w_id}";
        DB_query($sql);
        return DB_error() ? false : true;
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
            'subject'   => DB_getItem($_TABLES['ff_topic'], 'subject', "id = {$this->w_topic_id}"),
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
     * Creates the edit form.
     *
     * @return  string      HTML for edit form
     */
    public function Edit()
    {
        global $_TABLES, $_CONF;

        $T = new \Template($_CONF['path'] . '/plugins/forum/templates/admin/warning/');
        $T->set_file('editform', 'editwarning.thtml');

        $T->set_var(array(
            'w_id'      => $this->w_id,
            'uid'       => $this->w_uid,
            'username'  => COM_getDisplayName($this->w_uid),
            'subject'   => DB_getItem($_TABLES['ff_topic'], 'subject', "id = {$this->w_topic_id}"),
            'topic_id'  => $this->w_topic_id,
            'dscp'      => $this->w_dscp,
            'notes'     => $this->w_notes,
            'can_revoke' => $this->wt_id > 0,
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
            $retval .= COM_getDisplayName($fieldvalue, $A['username'], $A['fullname']);
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
        global $_TABLES, $LANG_GF01;

        if (!empty($A)) {
            $this->setVars($A, false);
        }

        $this->_WT = WarningType::getInstance($this->wt_id);
        $points = $this->_WT->getPoints();
        if ($this->w_id > 0) {
            $expires = $this->w_expires;  // using existing value
            $sql1 = "UPDATE {$_TABLES['ff_warnings']} SET ";
            $sql3 = "WHERE w_id = {$this->w_id}";
        } else {
            $expires = time() + $this->_WT->getExpirationSeconds();
            $sql1 = "INSERT INTO {$_TABLES['ff_warnings']} SET ";
            $sql3 = '';
        }

        $sql2 = "w_uid = '" . (int)$this->w_uid . "',
                wt_id = '{$this->wt_id}',
                w_topic_id = {$this->w_topic_id},
                ts = UNIX_TIMESTAMP(),
                w_points = $points,
                w_expires = {$expires},
                revoked_date = {$this->revoked_date},
                revoked_by = {$this->revoked_by},
                revoked_reason = '" . DB_escapeString($this->revoked_reason) . "',
                w_dscp = '" . DB_escapeString($this->w_dscp) . "',
                w_notes = '" . DB_escapeString($this->w_notes) . "'";
        $sql = $sql1 . $sql2 . $sql3;
        DB_query($sql);
        if (DB_error())  {
            return false;
        } else {
            // Take action, if necessary
            $this->takeAction();
            return true;
        }
    }


    /**
     * Execute the action based on the current warning level.
     *
     * @return  boolean     True on success, False on error
     */
    public function takeAction() : bool
    {
        global $_TABLES;

        if ($this->_WT === NULL) {
            $this->_WT = WarningType::getInstance($this->wt_id);
        }
        $percent = self::getUserPercent($this->w_uid);
        $WL = WarningLevel::getByPercent($percent);
        if ($WL->getID() < 1) {
            // No matching warninglevel record found.
            return false;
        }
        $expiration = time() + $WL->getDuration();
        if ($WL->getAction() > 0) {
            $field = Status::getKey($WL->getAction()) . '_expires';
            $sql = "UPDATE {$_TABLES['ff_userinfo']}
                SET $field = $expiration
                WHERE uid = {$this->w_uid}";
            COM_errorLog("updating user: $sql");
            return true;
        }
        return false;
    }


    private function _notifyUser()
    {
    }

}

