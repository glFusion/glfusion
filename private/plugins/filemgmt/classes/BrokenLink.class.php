<?php
/**
 * Class to manage file items.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     filemgmt
 * @version     v1.9.0
 * @since       v0.9.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Filemgmt;


/**
 * Class for downloadable items.
 * @package filemgmt
 */
class BrokenLink
{

    /**
     * Constructor.
     * Reads in the specified class, if $id is set.  If $id is zero,
     * then a new entry is being created.
     *
     * @param   integer|array   $id Record ID or array
     */
    public function __construct($lid=0)
    {
        global $_USER, $_VARS;

        $this->isNew = true;

        if (is_array($lid)) {
            $this->setVars($lid, true);
        } elseif ($lid > 0) {
            $this->lid = (int)$lid;
            if (!$this->Read()) {
                $this->lid = 0;
            }
        }
    }


    /**
     * Delete a broken link record.
     *
     * @param   integer $lid    Link record ID
     */
    public static function delete()
    {
        global $_TABLES, $_CONF, $_FM_CONF;

        $tmpsnap  = $_FM_CONF['SnapStore'] . $this->logourl;
        DB_query("DELETE FROM {$_TABLES['filemgmt_filedetail']}  WHERE lid=$lid");
        DB_query("DELETE FROM {$_TABLES['filemgmt_filedesc']}    WHERE lid=$lid");
        DB_query("DELETE FROM {$_TABLES['filemgmt_votedata']}    WHERE lid=$lid");
        DB_query("DELETE FROM {$_TABLES['filemgmt_brokenlinks']} WHERE lid=$lid");

        DB_query("DELETE FROM {$_TABLES['comments']} WHERE sid = 'fileid_".DB_escapeString($this->lid)."' AND type = 'filemgmt'");


        // Check for duplicate files of the same filename (actual filename in repository)
        // We don't want to delete actual file if there are more then 1 record linking to it.
        // Site may be allowing more then 1 file listing to duplicate files
        if ($numrows > 1) {
            return false;
        } else {
            if ($tmpfile != "" && file_exists($tmpfile) && (!is_dir($tmpfile))) {
                $err=@unlink ($tmpfile);
            }
            if ($tmpsnap != "" && file_exists($tmpsnap) && (!is_dir($tmpsnap))) {
                $err=@unlink ($tmpsnap);
            }
        }
        PLG_itemDeleted($lid,'filemgmt');
        $c = Cache::getInstance()->deleteItemsByTag('whatsnew');
        return true;
    }


    /**
     * Admin List View.
     *
     * @return  string      HTML for the category list.
     */
    public static function adminList()
    {
        global $_CONF,$_TABLES,$_TABLES,$LANG_FM02;

        USES_lib_admin();
        $retval = '';

        $header_arr = array(
            array(
                'text' => _MD_FILETITLE,
                'field' => 'title',
                'sort' => true,
            ),
            array(
                'text' => _MD_REPORTER,
                'field' => 'reporter',
            ),
            array(
                'text' => _MD_FILESUBMITTER,
                'field' => 'submitter',
            ),
            array(
                'text' => _MD_IGNORE,
                'field' => 'ignore',
                'align' => 'center',
            ),
            array(
                'text' => _MD_DELETE,
                'field' => 'delete',
                'align' => 'center',
            ),
        );
        $defsort_arr = array(
            'field' => 'reportid',
            'direction' => 'ASC',
        );
        $text_arr = array();
        $options = array();
        $query_arr = array(
            'table' => 'filemgmt_brokenlinks',
            'sql' => "SELECT bl.*, fd.title, fd.url,
                sub.username AS submitter, sub.email as owneremail,
                send.username AS reporter, send.email as senderemail
                FROM {$_TABLES['filemgmt_brokenlinks']} bl
                LEFT JOIN {$_TABLES['filemgmt_filedetail']} fd ON fd.lid = bl.lid
                LEFT JOIN {$_TABLES['users']} send ON send.uid = bl.sender
                LEFT JOIN {$_TABLES['users']} sub ON sub.uid = fd.submitter",
            'query_fields' => array(),
            'default_filter' => ''
        );
        $form_arr = array();

        $retval .= ADMIN_list(
            'filelist_brokenlinkslist',
            array(__CLASS__, 'getAdminField'),
            $header_arr,
            $text_arr, $query_arr, $defsort_arr, ''
        );
        $retval .= COM_endBlock();
        return $retval;
    }


    /**
     * Get an individual field for the category list.
     *
     * @param   string  $fieldname  Name of field (from the array, not the db)
     * @param   mixed   $fieldvalue Value of the field
     * @param   array   $A          Array of all fields from the database
     * @param   array   $icon_arr   System icon array (not used)
     * @return  string              HTML for field display in the table
     */
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr)
    {
        global $_CONF, $_USER, $_TABLES, $LANG_ADMIN;

        $retval = '';
        static $grp_names = array();
        static $cat_names = array();
        static $dt = NULL;
        if ($dt === NULL) {
            $dt = new \Date('now',$_USER['tzid']);
        }

        switch($fieldname) {
        case 'edit':
            $retval .= COM_createLink(
                '<i class="uk-icon uk-icon-edit tooltip" title="Edit"></i>',
                $_CONF['site_admin_url'] . "/plugins/filemgmt/index.php?modDownload={$A['lid']}"
            );
            break;

        case 'date':
            $dt->setTimestamp($fieldvalue);
            $retval = $dt->format('M d, Y',true);
            break;

        case 'delete':
            if (!self::isUsed($A['cid'])) {
                $retval .= COM_createLink(
                    '<i class="uk-icon uk-icon-remove uk-text-danger"></i>',
                    'index.php?delCat=' . $A['cid'],
                    array(
                        'onclick' => "return confirm('OK to delete?');",
                        'title' => 'Delete Item',
                        'class' => 'tooltip',
                    )
                );
            }
            break;

        default:
            $retval = htmlspecialchars($fieldvalue, ENT_QUOTES, COM_getEncodingt());
            break;
        }
        return $retval;
    }

}
