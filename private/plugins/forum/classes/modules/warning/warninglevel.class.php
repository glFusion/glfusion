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
use Forum\Status;

class WarningLevel
{
    /** Prefix record ID.
     * @var integer */
    private $wl_id = 0;

    /** Prefix title, used only in admin and option lists.
     * @var integer */
    private $wl_pct= 0;

    /** Number of seconds to retain warning.
     * @var integer */
    private $wl_duration = 604800;

    /** Number of periods making up the duration.
     * @var integer */
    private $wl_duration_qty = 1;

    /** Type of duration perod, e.g. "day", "week", etc.
     * @var string */
    private $wl_duration_period = 'day';

    /** Action to be taken. See the Actions class.
     * @var integer */
    private $wl_action = 0;

    /** Array of action information
     * @var array */
    private $wl_other = array('type' => 0);


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

        $sql = "SELECT * FROM {$_TABLES['ff_warninglevels']}
                WHERE wl_id=" . $id;
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

        $this->wl_id = (int)$A['wl_id'];
        $this->wl_pct = (int)$A['wl_pct'];
        $this->wl_action = (int)$A['wl_action'];
        $this->wl_duration_num = (int)$A['wl_duration_num'];
        $this->wl_duration_period = $A['wl_duration_period'];
        $this->wl_duration = Dates::dscpToSeconds($A['wl_duration_num'], $A['wl_duration_period']);
        /*if ($from_db) {
            // Extracting json-encoded strings
            $this->wl_duration = (int)$A['wl_duration'];
        } else {
            // Forums and Groups are supplied as simple arrays from the form:q
            $this->wl_duration = Dates::dscpToSeconds($A['wl_duration_num'], $A['wl_duration_type']);
        }*/
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
        $this->wl_id = (int)$id;
        return $this;
    }


    /**
     * Get the record ID.
     *
     * @return  integer     Record ID
     */
    public function getID() : int
    {
        return (int)$this->wl_id;
    }


    /**
     * Set the title string.
     *
     * @param   integer $pct    Percent as a whole number
     * @return  object  $this
     */
    public function withPercent(int $pct) : self
    {
        $this->wl_pct = (int)$pct;
        return $this;
    }


    /**
     * Get the percentage.
     *
     * @return  integer     Percent as a whole number
     */
    public function getPercent() : string
    {
        return $this->wl_pct;
    }


    /**
     * Get the duration of the action in seconds.
     *
     * @return  integer     Seconds
     */
    public function getDuration() : int
    {
        return (int)$this->wl_duration;
    }


    public function getAction() : int
    {
        return (int)$this->wl_action;
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


    public static function getByPercent(float $pct) : self
    {
        global $_TABLES;

        $sql = "SELECT * FROM {$_TABLES['ff_warninglevels']}
            WHERE wl_pct <= $pct
            ORDER BY wl_pct
            LIMIT 1";
        $res = DB_query($sql);
        if ($res && DB_numRows($res) == 1) {
            $A = DB_fetchArray($res, false);
            $retval = new self($A);
        } else {
            $retval = new self;
        }
        return $retval;
    }


    /**
     * Delete a single warning level record.
     *
     * @param   integer $wl_id  Record ID of prefix to remove
     */
    public static function Delete(int $wl_id) : void
    {
        global $_TABLES;

        DB_delete($_TABLES['ff_warninglevels'], 'wl_id', $wl_id);
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

        $T->set_var(array(
            'wl_id'     => $this->wl_id,
            'wl_duration'    => $this->wl_duration,
            'wl_pct' => $this->wl_pct,
            'action_sel_' . $this->wl_action => 'selected="selected"',
            'wl_duration_num' => $this->wl_duration_num,
            'sel_' . $this->wl_duration_period => 'selected="selected"',
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

        $options = array('chkdelete' => 'true', 'chkfield' => 'wl_id');
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
                    'url' => $base_url . '?editlevel=' .$A['wl_id'],
                )
            );
            break;

        case 'delete':
            $retval = FieldList::delete(
                array(
                    'delete_url' => $base_url.'?deletelevel='.$A['wl_id'],
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
            $retval .= Status::getDscp($fieldvalue);
            break;

        case 'wl_duration':
            $retval .= 'For ' . Dates::secondsToDscp($fieldvalue);
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

        if ($this->wl_id > 0) {
            $sql1 = "UPDATE {$_TABLES['ff_warninglevels']} SET ";
            $sql3 = " WHERE wl_id = {$this->wl_id}";
        } else {
            $sql1 = "INSERT INTO {$_TABLES['ff_warninglevels']} SET ";
            $sql3 = '';
        }

        $sql2 = "wl_pct = " . (int)$this->wl_pct . ",
            wl_duration = " . (int)$this->wl_duration . ",
            wl_duration_num = " . (int)$this->wl_duration_num . ",
            wl_duration_period = '" . DB_escapeString($this->wl_duration_period) . "',
            wl_action = " . (int)$this->wl_action;
        $sql = $sql1 . $sql2 . $sql3;
        DB_query($sql);
        if (DB_error())  {
            return false;
        } else {
            return true;
        }
    }


    public static function takeAction(int $uid)
    {
        $percent = Warning::getUserPercent($uid);
        $WL = self::getByPercent($percent);
        if ($WL->getID() < 1) {
            // No matching warninglevel record found.
            return false;
        }
        $expiration = time() + $WL->getDuration();

                
    }

}

