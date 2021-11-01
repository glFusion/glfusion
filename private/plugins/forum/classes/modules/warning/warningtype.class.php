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

class WarningType
{
    /** Record ID.
     * @var integer */
    private $wt_id = 0;

    /** Short descriptive text.
     * @var string */
    private $wt_dscp = '';

    /** Point assignment for this type.
     * @var integer */
    private $wt_points = 0;

    /** Number of seconds before this warning type expires.
     * @var integer */
    private $wl_expires = 0;


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
        } else {
            $A = (int)$A;
            $this->Read($A);
        }
    }


    /**
     * Read a single level record into an instantiated object.
     *
     * @param   integer $id     Warning leve record ID.
     * @return  boolean     True on success, False on error or not found
     */
    public function Read(int $id) : bool
    {
        global $_TABLES;

        $sql = "SELECT * FROM {$_TABLES['ff_warningtypes']}
                WHERE wt_id=" . $id;
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
     * Set all property values from DB or form.
     *
     * @param   array   $A          Array of property name=>value
     * @param   bool    $from_db    True of coming from the DB, False for form
     */
    public function setVars(array $A, bool $from_db = true) : self
    {
        global $_FF_CONF, $_TABLES, $_CONF;

        $this->wt_id = (int)$A['wt_id'];
        $this->wt_dscp = $A['wt_dscp'];
        $this->wt_points = (int)$A['wt_points'];
        if ($from_db) {
            // Extracting json-encoded strings
            $this->wt_expires = (int)$A['wt_expires'];
        } else {
            // Forums and Groups are supplied as simple arrays from the form
            //$this->warn_groups = $A['warn_groups'];
            //$this->warn_forums = $A['warn_forums'];
        }
        return $this;
    }


    /**
     * Set the record ID.
     *
     * @param   $id     Record ID
     * @return  object  $this
     */
    public function withID(int $id) : self
    {
        $this->wt_id = (int)$id;
        return $this;
    }


    /**
     * Get the record ID.
     *
     * @return  integer     Record ID
     */
    public function getID() : int
    {
        return (int)$this->wt_id;
    }


    public function getDscp() : string
    {
        return $this->wt_dscp;
    }


    public function getExpires() : int
    {
        return (int)$this->wt_expires;
    }


    /**
     * Set the title string.
     *
     * @param   integer $pct    Percent as a whole number
     * @return  object  $this
     */
    public function withPoints(int $pct) : self
    {
        $this->wt_points = (int)$pct;
        return $this;
    }


    /**
     * Get the percentage.
     *
     * @return  integer     Percent as a whole number
     */
    public function getPoints() : string
    {
        return $this->wt_points;
    }


    /**
     * Get an instance of a warning level
     * Caches locally since the same prefix may be requested many times
     * for a single page load.
     *
     * @param   int     $id     Warning level record ID
     * @return  object  Warning Level object
     */
    public static function getInstance(int $id) : self
    {
        static $cache = array();

        $id = (int)$id;
        if (!array_key_exists($id, $cache)) {
            $cache[$id] = new self($id);
        }
        return $cache[$id];
    }


    /**
     * Get all available warning types.
     *
     * @return  array   Array of WarninType objects
     */
    public static function getAvailable()
    {
        global $_TABLES;

        $retval = array();
        $sql = "SELECT * FROM {$_TABLES['ff_warningtypes']}
            ORDER BY wt_points ASC";
        $res = DB_query($sql);
        if ($res && DB_numRows($res) > 0) {
            while ($A = DB_fetchArray($res, false)) {
                $retval[$A['wt_id']] = new self($A);
            }
        }
        return $retval;
    }


    /**
     * Delete a single warning level record.
     *
     * @param   integer $wt_id  Record ID of prefix to remove
     */
    public static function Delete(int $wt_id) : void
    {
        global $_TABLES;

        DB_delete($_TABLES['ff_warninglevels'], 'wt_id', $wt_id);
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
        $T->set_file('editform', 'warninglevel.thtml');

        $wl_duration = self::secondsToDscp($this->wl_duration);
        $T->set_var(array(
            'wt_id'     => $this->wt_id,
            'wl_duration'    => $this->wl_duration,
            'wl_pct' => $this->wl_pct,
            'action_sel_' . $this->wl_action => 'selected="selected"',
            'wl_duration_num' => $wl_duration['num'],
            'sel_' . $wl_duration['dscp'] => 'selected="selected"',
        ) );
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
        $form_arr = array();

        $header_arr = array(
            array(
                'text'  => $LANG_ADMIN['edit'],
                'field' => 'edit',
                'sort'  => false,
                'align' => 'center',
            ),
            array(
                'text'  => 'Percent',
                'field' => 'wl_pct',
                'align' => 'right',
                'sort'  => true,
            ),
            array(
                'text'  => 'Action',
                'field' => 'wl_action',
                'sort'  => false,
                'align' => 'left',
            ),
            array(
                'text'  => 'Length',
                'field' => 'wl_duration',
                'sort'  => false,
                'align' => 'left',
            ),
            array(
                'text'  => $LANG_ADMIN['delete'],
                'field' => 'delete',
                'sort'  => false,
                'align' => 'center',
            ),
        );

        $options = array('chkdelete' => 'true', 'chkfield' => 'wt_id');
        $defsort_arr = array('field' => '', 'direction' => 'asc');
        $query_arr = array(
            'table' => 'ff_warninglevels',
            'sql' => "SELECT * FROM {$_TABLES['ff_warninglevels']}
                ORDER BY wl_pct ASC",
            'query_fields' => array(),
        );
        $text_arr = array(
            //'has_extras' => true,
            'form_url' => $_CONF['site_admin_url'] . '/plugins/forum/warnings.php',
        );

        $retval .= ADMIN_list(
            'badges', array(__CLASS__, 'getAdminField'), $header_arr,
            $text_arr, $query_arr, $defsort_arr, '', '', $options, $form_arr
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
        $base_url = $_CONF['site_admin_url'] . '/plugins/forum/warnings.php';

        switch($fieldname) {
        case 'edit':
            $retval = FieldList::edit(
                array(
                    'url' => $base_url . '?editlevel=' .$A['wt_id'],
                )
            );
            break;

        case 'delete':
            $retval = FieldList::delete(
                array(
                    'delete_url' => $base_url.'?deletelevel='.$A['wt_id'],
                    'attr' => array(
                        'title'   => $LANG_ADMIN['delete'],
                        'onclick' => "return confirm('{$LANG_GF01['DELETECONFIRM']}');"
                    ),
                )
            );
            break;

        case 'wl_pct':
            $retval .= (int)$fieldvalue;
            break;

        case 'wl_action':
            $retval .= self::$actions[$fieldvalue];
            break;

        case 'wl_duration':
            $retval = "For $fieldvalue seconds";
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

        // Strip paragraph tags added by the advanced editor
        $this->warn_html = strip_tags($this->warn_html, '<span><em><strong><u>');

        if ($this->wt_id > 0) {
            $sql1 = "UPDATE {$_TABLES['ff_warninglevels']} SET ";
            $sql3 = "WHERE wt_id = {$this->wt_id}";
        } else {
            $sql1 = "INSERT INTO {$_TABLES['ff_warninglevels']} SET ";
            $sql3 = '';
        }

        $json_groups = DB_escapeString(json_encode($this->warn_groups));
        $json_forums = DB_escapeString(json_encode($this->warn_forums));
        $sql2 = "wl_pct = '" . (int)$this->wl_pct . "',
                wl_action = '" . DB_escapeString(@serialize($this->wl_action)) . "'";
        $sql = $sql1 . $sql2 . $sql3;
        DB_query($sql);
        if (DB_error())  {
            return false;
        } else {
            self::reOrder();
            return true;
        }
    }


    /**
     * Get the descriptive elements for a number of seconds.
     * For example, 86400 returns array(1, 'day').
     *
     * @param   integer $seconds    Number of seconds
     * @return  array   Array of (number, descrption)
     */
    public static function secondsToDscp(int $seconds) : array
    {
        if ($seconds >= self::SECONDS_YEAR) {   // 1 year
            $retval = array(
                'num' => (int)($seconds / self::SECONDS_YEAR),
                'dscp' => 'year',
            );
        } elseif ($seconds >= self::SECONDS_MONTH) {  // 30 days
            $retval = array(
                'num' => (int)($seconds / self::SECONDS_MONTH),
                'dscp' => 'month',
            );
        } elseif ($seconds >= self::SECONDS_WEEK) {   // 7 days
            $retval = array(
                'num' => (int)($seconds / self::SECONDS_WEEK),
                'dscp' => 'month',
            );
        } elseif ($seconds >= self::SECONDS_DAY) {   // 7 days
            $retval = array(
                'num' => (int)($seconds / self::SECONDS_DAY),
                'dscp' => 'month',
            );
        }
        return $retval;
    }

}

