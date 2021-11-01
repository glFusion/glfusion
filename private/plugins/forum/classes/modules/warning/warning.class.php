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

class Warning
{
    /** Prefix record ID.
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
    private $topic_id = 0;

    /** Timestamp when the warning was issued.
     * @var integer */
    private $ts = 0;

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
    private $notes = '';


    /**
     * Constructor.
     * Sets the field values from the supplied array, or reads the record
     * if $A is a prefix record ID.
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
                WHERE w_id=" . $id;
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


    public static function getUserWarnings(int $uid) : array
    {
    }


    public static function getUserPercent(int $uid) : int
    {
        global $_TABLES;

        $sql = "SELECT sum(wt.wt_points) AS totalpoints
            FROM {$_TABLES['ff_warnings']} w
            LEFT JOIN {$_TABLES['ff_warningtypes']} wt
            ON wt.wt_id = w.wt_id
            WHERE w.w_uid = $uid
            AND w.expires > UNIX_TIMESTAMP()
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
        $this->topic_id = (int)$A['topic_id'];
        $this->w_dscp = $A['w_dscp'];
        $this->notes = $A['notes'];
        if ($from_db) {
            // Extracting json-encoded strings
            $this->expires = $A['expires'];
        } else {
            // Forums and Groups are supplied as simple arrays from the form
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
        $this->topic_id = (int)$id;
        return $this;
    }


    /**
     * Get the prefix record ID.
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
     * Get the prefix title.
     *
     * @return  string      Title string
     */
    public function getDscp() : string
    {
        return $this->w_dscp;
    }


    /**
     * Get an instance of a prefix.
     * Caches locally since the same prefix may be requested many times
     * for a single page load.
     *
     * @param   int     $w_id     Prefix record ID
     * @return  object  Prefix object
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


    public static function getAll($uid, $forum)
    {
        global $_TABLES;

        $retval = array();
        $sql = "SELECT * FROM {$_TABLES['ff_prefixes']}";
        $res = DB_query($sql);
        if ($res) {
            while ($A = DB_fetchArray($res, false)) {
                $retval[$A['w_id']] = new self($A);
            }
        }
        return $retval;
    }


    /**
     * Delete a single prefix record and update the related topics.
     *
     * @param   integer $w_id  Record ID of prefix to remove
     */
    public static function Delete(int $w_id) : void
    {
        global $_TABLES;

        DB_query("UPDATE {$_TABLES['ff_topic']}
            SET prefix = 0
            WHERE prefix = $w_id");
        DB_delete($_TABLES['ff_prefixes'], 'w_id', $w_id);
    }


    /**
     * Get the final HTML to display the prefix.
     *
     * @return string  HTML for prefix
     */
    public function getHTML()
    {
        return '<span class="ff-prefix-global">' . $this->warn_html . '</span>';
    }

    /**
     * Creates the edit form.
     *
     * @return  string      HTML for edit form
     */
    public function Edit()
    {
        global $_TABLES, $_CONF;

        $db = Database::getInstance();

        $T = new \Template($_CONF['path'] . '/plugins/forum/templates/admin/warnings/');
        $T->set_file('editform', 'editwarning.thtml');

        $T->set_var(array(
            'w_id'      => $this->w_id,
            'uid'       => $this->w_uid,
            'username'  => COM_getDisplayName($this->w_uid),
            'subject'   => DB_getItem($_TABLES['ff_topic'], 'subject', "id = {$this->topic_id}"),
            'topic_id'  => $this->topic_id,
         ) );
        $Types = WarningType::getAvailable();
        $T->set_block('editform', 'WarningTypes', 'wt');
        foreach ($Types as $WT) {
            $T->set_var(array(
                'wt_id' => $WT->getID(),
                'wt_dscp' => $WT->getDscp(),
            ) );
            $T->parse('wt', 'WarningTypes', true);
        }
        $T->parse('output','editform');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Create the list.
     */
    public static function adminList()
    {
        global $LANG_ADMIN, $_TABLES, $_CONF, $_USER, $LANG_GF01, $LANG_GF93;

        USES_lib_admin();

        $uid = (int)$_USER['uid'];
        $retval = '';
        $retval .= '<script src="' .
            $_CONF['site_url'].'/forum/javascript/ajax_toggle.js"></script>';
        $form_arr = array();

        $header_arr = array(
            array(
                'text'  => $LANG_ADMIN['edit'],
                'field' => 'edit',
                'sort'  => false,
                'align' => 'center',
            ),
            array(
                'text'  => 'Prefix',
                'field' => 'warn_title',
                'sort'  => true,
                'align' => 'left',
            ),
            array(
                'text'  => $LANG_ADMIN['enabled'],
                'field' => 'warn_enabled',
                'align' => 'center',
                'sort'  => false,
            ),
            array(
                'text'  => $LANG_GF93['order'],
                'field' => 'warn_order',
                'sort'  => false,
            ),
            array(
                'text'  => $LANG_ADMIN['delete'],
                'field' => 'delete',
                'sort'  => false,
                'align' => 'center',
            ),
        );

        $options = array('chkdelete' => 'true', 'chkfield' => 'w_id');
        $defsort_arr = array('field' => '', 'direction' => 'asc');
        $query_arr = array('table' => 'ff_badges',
            'sql' => "SELECT * FROM {$_TABLES['ff_prefixes']}
                    ORDER BY warn_order ASC",
            'query_fields' => array('warn_title'),
        );
        $extras = array(
            'max_orderby' => (int)DB_getItem(
                $_TABLES['ff_prefixes'],
                'MAX(warn_order)',
                "1=1"
            ),
        );
        $text_arr = array(
            //'has_extras' => true,
            'form_url' => $_CONF['site_admin_url'] . '/plugins/forum/prefixes.php',
        );

        $retval .= ADMIN_list(
            'badges', array(__CLASS__, 'getAdminField'), $header_arr,
            $text_arr, $query_arr, $defsort_arr, '', $extras, $options, $form_arr
        );
        return $retval;
    }


    /**
     *   Get the correct display for a single field in the banner admin list
     *
     *   @param  string  $fieldname  Field variable name
     *   @param  string  $fieldvalue Value of the current field
     *   @param  array   $A          Array of all field names and values
     *   @param  array   $icon_arr   Array of system icons
     *   @return string              HTML for field display within the list cell
     */
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr, $extra=array())
    {
        global $_CONF, $LANG_ACCESS, $LANG_ADMIN, $LANG_GF01;

        $retval = '';
        $base_url = $_CONF['site_admin_url'] . '/plugins/forum/prefixes.php';

        switch($fieldname) {
        case 'edit':
            $retval = FieldList::edit(
                array(
                    'url' => $base_url . '?edit=x&amp;w_id=' .$A['w_id'],
                )
            );
            break;

        case 'warn_order':
            if ($fieldvalue > 10) {
                $retval .= FieldList::up(
                    array(
                        'url' => $base_url . '?move=up&w_id=' . $A['w_id'],
                    )
                );
            } else {
                $retval .= '<i class="uk-icon uk-icon-justify">&nbsp;</i>';
            }
            if ($fieldvalue < $extra['max_orderby']) {
                $retval .= FieldList::down(
                    array(
                        'url' => $base_url . '?move=down&w_id=' . $A['w_id'],
                    )
                );
            } else {
                $retval .= '<i class="uk-icon uk-icon-justify">&nbsp;</i>';
            }
            break;

        case 'warn_enabled':
            if ($fieldvalue == 1) {
                $switch = 'checked="checked"';
            } else {
                $switch = '';
            }
            $retval .= "<input type=\"checkbox\" $switch value=\"1\" name=\"warn_ena_check\"
                id=\"togena{$A['w_id']}\"
                onclick='forum_ajaxtoggle(this, \"{$A['w_id']}\",\"warn_enabled\",\"prefix\");' />\n";
            break;

        case 'delete':
            $retval = FieldList::delete(
                array(
                    'delete_url' => $base_url.'?w_id='.$A['w_id'].'&delete',
                    'attr' => array(
                        'title'   => $LANG_ADMIN['delete'],
                        'onclick' => "return confirm('{$LANG_GF01['DELETECONFIRM']}');"
                    ),
                )
            );
            break;

        case 'warn_title':
            if (empty($A['warn_html'])) {
                $retval = $fieldvalue;
            } else {
                $retval = '<span class="ff-prefix-global">' . $A['warn_html'] . '</span>';
            }
            break;

        default:
            $retval = $fieldvalue;
            break;
        }
        return $retval;
    }


    /**
     * Save a prefix from the edit form.
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

        if ($this->w_id > 0) {
            $expires = $this->expires;  // using existing value
            $sql1 = "UPDATE {$_TABLES['ff_warnings']} SET ";
            $sql3 = "WHERE w_id = {$this->w_id}";
        } else {
            $expires = time() + WarningType::getInstance($this->wt_id)->getExpires();
            $sql1 = "INSERT INTO {$_TABLES['ff_warnings']} SET ";
            $sql3 = '';
        }

        $sql2 = "w_uid = '" . (int)$this->w_uid . "',
                wt_id = '{$this->wt_id}',
                topic_id = {$this->topic_id},
                ts = UNIX_TIMESTAMP(),
                expires = {$expires},
                revoked_date = {$this->revoked_date},
                revoked_by = {$this->revoked_by},
                revoked_reason = '" . DB_escapeString($this->revoked_reason) . "',
                w_dscp = '" . DB_escapeString($this->w_dscp) . "',
                notes = '" . DB_escapeString($this->notes) . "'";
        $sql = $sql1 . $sql2 . $sql3;
        DB_query($sql);
        if (DB_error())  {
            return false;
        } else {
            return true;
        }
    }

}

