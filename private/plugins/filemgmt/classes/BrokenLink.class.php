<?php
/**
 * Class to manage broken link functions.
 * Displays the list and reporting form, and handles admin actions.
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
 * Class for handling broken link functions.
 * @package filemgmt
 */
class BrokenLink
{
    /**
     * Ignore a broken link report.
     * This just deletes the report from the database.
     *
     * @param   integer $lid    File record ID
     */
    public static function ignore($lid)
    {
        global $_TABLES;

        $lid = (int)$lid;
        DB_delete($_TABLES['filemgmt_brokenlinks'], 'lid', $lid);
    }


    /**
     * Delete a broken link record.
     *
     * @param   integer $lid    Link record ID
     */
    public static function delete($lid)
    {
        global $_TABLES;

        $lid = (int)$lid;
        self::ignore($lid);
        DB_delete($_TABLES['filemgmt_filedetail'], 'lid', $lid);
        PLG_itemDeleted($lid, 'filemgmt');
    }


    /**
     * Display the reporting form for a broken link.
     *
     * @param   integer $lid    File record ID
     * @return  string      HTML for reporting form
     */
    public static function showForm($lid)
    {
        global $_CONF;

        $lid = (int)$lid;
        $display = COM_startBlock(_MD_REPORTBROKEN);
        $T = new \Template($_CONF['path'] . '/plugins/filemgmt/templates/');
        $T->set_file('form', 'brokenfile.thtml');
        $T->set_var(array(
            'lid' => $lid,
            'lang_reportbroken' => _MD_REPORTBROKEN,
            'lang_thanksforhelp' => _MD_THANKSFORHELP,
            'lang_forsecurity' => _MD_FORSECURITY,
            'lang_cancel' => _MD_CANCEL,
        ) );
        $T->parse('output', 'form');
        $display .= $T->finish($T->get_var('output'));
        $display .= COM_endBlock();
        return $display;
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
                'text' => _MD_EDIT,
                'field' => 'edit',
                'sort' => true,
            ),
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
        global $_FM_CONF, $_USER, $_TABLES, $LANG_ADMIN;

        $retval = '';
        static $grp_names = array();
        static $cat_names = array();
        static $dt = NULL;
        if ($dt === NULL) {
            $dt = new \Date('now', $_USER['tzid']);
        }

        switch($fieldname) {
        case 'edit':
            $retval .= COM_createLink(
                '<i class="uk-icon uk-icon-edit tooltip" title="Edit"></i>',
                $_FM_CONF['admin_url'] . "/index.php?modDownload={$A['lid']}"
            );
            break;

        case 'date':
            $dt->setTimestamp($fieldvalue);
            $retval = $dt->format('M d, Y',true);
            break;

        case 'ignore':
            $retval = COM_createLink(
                '<i class="uk-icon-remove uk-text-warning"></i>',
                $_FM_CONF['admin_url'] . '/index.php?ignoreBrokenLink=' . $A['lid'],
                array(
                    'onclick' => "return confirm('Delete this broken file report?')",
                )
            );
            break;

        case 'delete':
            $retval .= COM_createLink(
                '<i class="uk-icon uk-icon-remove uk-text-danger"></i>',
                $_FM_CONF['admin_url'] . '/index.php?delBrokenLink=' . $A['lid'],
                array(
                    'onclick' => "return confirm('OK to delete?');",
                    'title' => 'Delete Item',
                    'class' => 'tooltip',
                )
            );
            break;

        default:
            $retval = htmlspecialchars($fieldvalue, ENT_QUOTES, COM_getEncodingt());
            break;
        }
        return $retval;
    }

}
