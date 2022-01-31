<?php
/**
 * Class to handle forum warning types such as "spam", etc.
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
use glFusion\FieldList;
use glFusion\Database\Database;


/**
 * Warning type management
 * @package glfusion
 */
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

    /** Number of periods making up the duration.
     * @var integer */
    private $wt_expires_qty = 1;

    /** Type of duration perod, e.g. "day", "week", etc.
     * @var string */
    private $wt_expires_period = 'day';

    /** Number of seconds before this warning type expires.
     * Calculated from qty and period.
     * @var integer */
    private $_wt_expires_seconds = 0;


    /**
     * Constructor.
     * Sets the field values from the supplied array, or reads the record
     * if $A is a warning type record ID.
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
     * Read a single warning type record into an instantiated object.
     *
     * @param   integer $id     Warning leve record ID.
     * @return  boolean     True on success, False on error or not found
     */
    public function Read(int $id) : bool
    {
        global $_TABLES;

        $db = Database::getInstance();
        $stmt = $db->conn->prepare(
            "SELECT * FROM {$_TABLES['ff_warningtypes']}
            WHERE wt_id= ?"
        );
        $stmt->bindParam(1, $id, Database::INTEGER);
        $stmt->execute();
        $A = $stmt->fetch(Database::ASSOCIATIVE);
        if (is_array($A)) {
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
        $this->wt_expires_qty = (int)$A['wt_expires_qty'];
        $this->wt_expires_period = $A['wt_expires_period'];
        $this->_wt_expires_seconds = Dates::dscpToSeconds($this->wt_expires_qty, $this->wt_expires_period);
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


    /**
     * Get the text description.
     *
     * @return  string      Description
     */
    public function getDscp() : string
    {
        return $this->wt_dscp;
    }


    /**
     * Get the number of seconds for this type's duration.
     *
     * @return  integer     Number of seconds
     */
    public function getExpirationSeconds() : int
    {
        return (int)$this->_wt_expires_seconds;
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
     * Get an instance of a warning type.
     * Caches locally since the same type may be requested many times
     * for a single page load.
     *
     * @param   int     $id     Warning type record ID
     * @return  object  Warning Type object
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
        $db = Database::getInstance();
        $stmt = $db->conn->prepare(
            "SELECT * FROM {$_TABLES['ff_warningtypes']}
            ORDER BY wt_points ASC"
        );
        $stmt->execute();
        $data = $stmt->fetchAll();
        if (is_array($data)) {
            foreach ($data as $A) {
                $retval[$A['wt_id']] = new self($A);
            }
        }
        return $retval;
    }


    /**
     * Delete a single warning type record.
     *
     * @param   integer $wt_id  Record ID of type to remove
     */
    public static function Delete(int $wt_id) : void
    {
        global $_TABLES;
    
        $db = Database::getInstance();

        $sql = "DELETE FROM `{$_TABLES['ff_warningtypes']}` WHERE wt_id = ?";
        $stmt = $db->conn->prepare($sql);
        $stmt->bindParam(1, $wt_id, Database::INTEGER);
        $stmt->execute();
    }


    /**
     * Creates the edit form.
     *
     * @return  string      HTML for edit form
     */
    public function Edit()
    {
        global $_CONF, $LANG_GF01, $LANG_ADMIN;

        $T = new \Template($_CONF['path'] . '/plugins/forum/templates/admin/warning/');
        $T->set_file('editform', 'warningtype.thtml');

        $T->set_var(array(
            'wt_id'     => $this->wt_id,
            'wt_points' => $this->wt_points,
            'wt_dscp'   => $this->wt_dscp,
            'wt_expires_qty' => $this->wt_expires_qty,
            'sel_' . $this->wt_expires_period => 'selected="selected"',
            'lang_edit' => $this->wt_id > 0 ? $LANG_GF01['EDIT'] : $LANG_GF01['create_new'],
        ) );

        if ($this->wt_id != 0) {
            $T->set_var('lang_delete',$LANG_ADMIN['delete']);
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
        $form_arr = array();

        $header_arr = array(
            array(
                'text'  => $LANG_ADMIN['edit'],
                'field' => 'edit',
                'sort'  => false,
                'align' => 'center',
            ),
            array(
                'text'  => 'ID',
                'field' => 'wt_id',
                'align' => 'left',
                'sort'  => true,
            ),
            array(
                'text'  => $LANG_GF01['dscp'],
                'field' => 'wt_dscp',
                'sort'  => false,
                'align' => 'left',
            ),
            array(
                'text'  => $LANG_GF01['points'],
                'field' => 'wt_points',
                'align' => 'right',
                'sort'  => true,
            ),
            array(
                'text'  => $LANG_GF01['expires_after'],
                'field' => 'wt_expires',
                'align' => 'left',
                'sort'  => false,
            ),
            array(
                'text'  => $LANG_ADMIN['delete'],
                'field' => 'delete',
                'sort'  => false,
                'align' => 'center',
            ),
        );

        $options = array('chkdelete' => 'true', 'chkfield' => 'wt_id','chkname' => 'deletetype');
        $defsort_arr = array('field' => 'wt_points', 'direction' => 'asc');
        $query_arr = array(
            'table' => 'ff_warningtypes',
            'sql' => "SELECT * FROM {$_TABLES['ff_warningtypes']}",
            'query_fields' => array(),
        );
        $text_arr = array(
            //'has_extras' => true,
            'form_url' => $_CONF['site_admin_url'] . '/plugins/forum/warnings.php?listtypes',
        );

        $retval .= ADMIN_list(
            'badges', array(__CLASS__, 'getAdminField'), $header_arr,
            $text_arr, $query_arr, $defsort_arr, '', '', $options, $form_arr
        );
        return $retval;
    }


    /**
     * Get the correct display for a single field in the banner admin list
     *
     * @param   string  $fieldname  Field variable name
     * @param   string  $fieldvalue Value of the current field
     * @param   array   $A          Array of all field names and values
     * @param   array   $icon_arr   Array of system icons
     * @return  string              HTML for field display within the list cell
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
                    'url' => $base_url . '?edittype=' .$A['wt_id'],
                )
            );
            break;
        case 'delete':
            $retval = FieldList::delete(
                array(
                    'delete_url' => $base_url.'?deletetype='.$A['wt_id'],
                    'attr' => array(
                        'title'   => $LANG_ADMIN['delete'],
                        'onclick' => "return confirm('{$LANG_GF01['DELETECONFIRM']}');"
                    ),
                )
            );
            break;

        case 'wt_expires':
            $retval .= $A['wt_expires_qty'] . ' ' . Dates::getDscp($A['wt_expires_qty'], $A['wt_expires_period']);
            break;

        default:
            $retval = $fieldvalue;
            break;
        }
        return $retval;
    }


    /**
     * Save a warning type from the edit form.
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

        try {
            $db = Database::getInstance();
            $qb = $db->conn->createQueryBuilder();
            if ($this->wt_id > 0) {
                $qb->update($_TABLES['ff_warningtypes'])
                             ->where('wt_id = :wt_id');
                $qb->set('wt_points', ':wt_points')
                   ->set('wt_expires_qty', ':wt_expires_qty')
                   ->set('wt_expires_period', ':wt_expires_period')
                   ->set('wt_dscp', ':wt_dscp');
            } else {
                $qb->insert($_TABLES['ff_warningtypes'])
                   ->values (array(
                        'wt_points' => ':wt_points',
                        'wt_expires_qty' => ':wt_expires_qty',
                        'wt_expires_period' => ':wt_expires_period',
                        'wt_dscp' => ':wt_dscp'
                   ));
            }
            $qb->setParameter('wt_id', $this->wt_id)
               ->setParameter('wt_points', $this->wt_points)
               ->setParameter('wt_expires_qty', $this->wt_expires_qty)
               ->setParameter('wt_expires_period', $this->wt_expires_period)
               ->setParameter('wt_dscp', $this->wt_dscp);
            $stmt = $qb->execute();
            return true;
        } catch(Throwable $e) {
            return false;
        } 
    }

}

