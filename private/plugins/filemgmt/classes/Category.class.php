<?php
/**
 * Class to manage file categories.
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
 * Class for product categories.
 * Each product belongs to one category.
 * @package filemgmt
 */
class Category
{
    /** Category ID.
     * @var integer */
    private $cid = 0;

    /** Parent Category ID.
     * @var integer */
    private $pid = 0;

    /** Category Name.
     * @var string */
    private $title = '';

    /** Thumbnail image URL.
     * @var string */
    private $imgurl = '';

    /** Category group access. Default is "all users".
     * @var integer */
    private $grp_access = 2;

    /** Group with write (upload) access. Default is "root".
     * @var integer */
    private $grp_writeaccess = 1;


    /**
     * Constructor.
     * Reads in the specified class, if $id is set.  If $id is zero,
     * then a new entry is being created.
     *
     * @param   integer|array   $id Record ID or array
     */
    public function __construct($id=0)
    {
        global $_USER, $_VARS;

        $this->isNew = true;

        if (is_array($id)) {
            $this->setVars($id, true);
        } elseif ($id > 0) {
            $this->cid = $id;
            if (!$this->Read()) {
                $this->cid = 0;
            }
        }
        $this->isAdmin = plugin_ismoderator_filemgmt() ? 1 : 0;
    }


    /**
     * Sets all variables to the matching values from the supplied array.
     *
     * @param   array   $row    Array of values, from DB or $_POST
     * @param   boolean $fromDB True if read from DB, false if from a form
     */
    public function setVars($row, $fromDB=false)
    {
        if (!is_array($row) || empty($row)) return;

        $this->cid = $row['cid'];
        $this->pid = $row['pid'];
        $this->title = $row['title'];
        $this->grp_access = $row['grp_access'];
        $this->grp_writeaccess = $row['grp_writeaccess'];
        if ($fromDB) {
            $this->imgurl = $row['imgurl'];
        }
        return $this;
    }


    /**
     * Read a specific record and populate the local values.
     * Caches the object for later use.
     *
     * @param   integer $id Optional ID.  Current ID is used if zero.
     * @return  boolean     True if a record was read, False on failure
     */
    public function Read($id = 0)
    {
        global $_TABLES;

        $id = (int)$id;
        if ($id == 0) $id = $this->cid;
        if ($id == 0) {
            $this->error = 'Invalid ID in Read()';
            return;
        }

        $sql = "SELECT * FROM {$_TABLES['filemgmt_cat']}
                WHERE cid = '$id'";
        $result = DB_query($sql);
        if (!$result || DB_numRows($result) != 1) {
            return false;
        } else {
            $row = DB_fetchArray($result, false);
            $this->setVars($row, true);
            $this->isNew = false;
            return true;
        }
    }


    /**
     * Get a category instance.
     * Checks cache first and creates a new object if not found.
     *
     * @param   integer $cid    Category ID
     * @return  object          Category object
     */
    public static function getInstance($cid)
    {
        static $cats = array();
        if (!isset($cats[$cid])) {
            $cats[$cid] = new self($cid);
        }
        return $cats[$cid];
    }


    /**
     * Determine if this category is a new record, or one that was not found
     *
     * @return  integer     1 if new, 0 if existing
     */
    public function isNew()
    {
        return $this->isNew ? 1 : 0;
    }


    /**
     * Save the current values to the database.
     *
     * @param  array   $A      Optional array of values from $_POST
     * @return boolean         True if no errors, False otherwise
     */
    public function save($A = array())
    {
        global $_TABLES, $_FM_CONF;

        if (is_array($A)) {
            $this->setVars($A);
        }

        if (
            isset($_FILES['imgurl']) &&
            isset($_FILES['imgurl']['name']) &&
            !empty($_FILES['imgurl']['name'])
        ) {
            $upload = new UploadDownload();
            $upload->setPerms('0644');
            $upload->setFieldName('imgurl');
            $upload->setPath($_FM_CONF['SnapCat']);
            $upload->setAllowAnyMimeType(true);     // allow any file type
            $upload->setMaxFileSize(100000000);
            $upload->setMaxDimensions(8192,8192);

            if ($upload->numFiles() > 0) {
                $upload->uploadFiles();
                if ($upload->areErrors()) {
                    $errmsg = "Upload Error: " . $upload->printErrors(false);
                    $this->addError($errmsg);
                    Log::write('system',Log::ERROR, $errmsg);
                    ErrorHandler::show("1106");
                } else {
                    $uploaded_file = $upload->getUploadedFiles()[0];
                    $this->size = (int)$uploaded_file['size'];
                    $filename = $uploaded_file['name'];
                    $this->imgurl = rawurlencode($filename);

                    $fileExtension = strtolower($uploaded_file['extension']);
                    if (array_key_exists($fileExtension, $_FM_CONF['extensions_map'])) {
                        if ($_FM_CONF['extensions_map'][$fileExtension] == 'reject' ) {
                            Log::write('system',Log::ERROR, 'AddNewFile - New Upload file is rejected by config rule: ' .$uploadfilename);
                            ErrorHandler::show("1109");
                        } else {
                            $fileExtension = $_FM_CONF['extensions_map'][$fileExtension];
                            $pos = strrpos($url,'.') + 1;
                            $this->url = strtolower(substr($this->url, 0,$pos)) . $fileExtension;

                            $pos2 = strrpos($filename,'.') + 1;
                            $filename = substr($filename,0,$pos2) . $fileExtension;
                        }
                    }
                }
            }
        } elseif (isset($A['deletesnap'])) {
            $this->deleteImage();
        }

        // Insert or update the record, as appropriate, as long as a
        // previous error didn't occur.
        if (empty($this->Errors)) {
            if ($this->isNew) {
                $sql1 = "INSERT INTO {$_TABLES['filemgmt_cat']} SET ";
                $sql3 = '';
            } else {
                $sql1 = "UPDATE {$_TABLES['filemgmt_cat']} SET ";
                $sql3 = " WHERE cid = '{$this->cid}'";
            }
            $sql2 = "pid = '" . $this->pid . "',
                title = '" . DB_escapeString($this->title) . "',
                grp_access = '{$this->grp_access}',
                grp_writeaccess = '{$this->grp_writeaccess}',
                imgurl = '" . DB_escapeString($this->imgurl) . "'";
            $sql = $sql1 . $sql2 . $sql3;
            //echo $sql;die;
            //COM_errorLog($sql);
            DB_query($sql);
            if (!DB_error()) {
                if ($this->isNew) {
                    $this->cid = DB_insertID();
                }
                if (isset($_POST['old_parent']) && $_POST['old_parent'] != $this->pid) {
                    self::rebuildTree();
                }
                /*if (isset($_POST['old_grp']) && $_POST['old_grp'] > 0 &&
                        $_POST['old_grp'] != $this->grp_access) {
                    $this->_propagatePerms($_POST['old_grp']);
                }*/
            } else {
                $this->addError('Failed to insert or update record');
            }
        }

        if (empty($this->Errors)) {
            return true;
        } else {
            return false;
        }
    }   // function Save()


    /**
     *  Delete the current category record from the database.
     */
    public function delete()
    {
        global $_TABLES;

        if ($this->cid <= 1) {
            return false;
        }

        $this->deleteImage(false);
        DB_delete($_TABLES['filemgmt_cat'], 'cid', $this->cid);
        $this->cid = 0;
        return true;
    }


    /**
     * Deletes a single image from disk.
     * $del_db is used to save a DB call if this is called from Save().
     *
     */
    public function deleteImage()
    {
        global $_TABLES, $_FM_CONF;

        $filename = $this->imgurl;
        if (is_file("{$_FM_CONF['SnapCat']}/{$filename}")) {
            @unlink("{$_FM_CONF['SnapCat']}/{$filename}");
        }

        $this->imgurl= '';
    }


    /**
     *  Creates the edit form.
     *
     *  @param  integer $id Optional ID, current record used if zero
     *  @return string      HTML for edit form
     */
    public function edit()
    {
        global $_TABLES, $_CONF, $_SYSTEM, $_FM_CONF;

        $T = new \Template($_CONF['path'] . 'plugins/filemgmt/templates/admin');
        $T->set_file('category', 'category_form.thtml');
        $id = $this->cid;

        // If we have a nonzero category ID, then we edit the existing record.
        // Otherwise, we're creating a new item.  Also set the $not and $items
        // values to be used in the parent category selection accordingly.
        if ($id > 0) {
            $retval = COM_startBlock('Edit Category' . ': ' . $this->title);
            $T->set_var('cid', $id);
        } else {
            $retval = COM_startBlock('Create Category');
            $T->set_var('cid', '');
        }

        $T->set_var(array(
            'framework'     => $_SYSTEM['framework'],
            'title'         => $this->title,
            'old_parent'    => $this->pid,
            'old_grp'       => $this->grp_access,
            'grp_access_options' => SEC_getGroupDropdown($this->grp_access, 3, 'grp_access'),
            'grp_writeaccess_options' => SEC_getGroupDropdown($this->grp_writeaccess, 3, 'grp_writeaccess'),
            'pid_options'   => COM_optionList(
                $_TABLES['filemgmt_cat'],
                'cid,title',
                $this->pid,
                1//,
                //"cid = {$this->pid}"
            ),
            'lang_save' => 'Save',
            'lang_delete' => 'Delete',
            'lang_cancel' => 'Cancel',
        ) );
        if (!empty($this->imgurl) && is_file($_FM_CONF['SnapCat'].$this->imgurl)) {
            $T->set_var('thumbnail', $_FM_CONF['SnapCatURL'] . $this->imgurl);
        } else {
            $T->unset_var('thumbnail');
        }
        //if (!self::isUsed($this->cid)) {
            $T->set_var('can_delete', 'true');
        //}
        $T->parse('output', 'category');
        $retval .= $T->finish($T->get_var('output'));
        $retval .= COM_endBlock();
        return $retval;

    }


    /**
     * Check if there are any products directly under a category ID.
     *
     * @param   integer$cid     Category ID to check
     * @return  integer     Number of products under the category
     */
    public static function hasFiles($cid)
    {
        global $_TABLES;

        return DB_count($_TABLES['filemgmt_filedetail'], 'cid', (int)$cid);
    }


    /**
     * Determine if a category is used by any products.
     * Used to prevent deletion of a category if it would orphan a product.
     *
     *  @param  integer$cid     Category ID to check.
     * @return  boolean     True if used, False if not
     */
    public static function isUsed($cid=0)
    {
        global $_TABLES;

       $cid = (int)$cid;

        // Check if any products are under this category
        if (self::hasFiles($cid) > 0) {
            return true;
        }

        // Check if any categories are under this one.
        if (DB_count($_TABLES['filemgmt_cat'], 'pid', $cid) > 0) {
            return true;
        }

        return false;
    }


    /**
     * Add an error message to the Errors array.
     * Also could be used to log certain errors or perform other actions.
     *
     * @param   string  $msg    Error message to append
     */
    public function addError($msg)
    {
        $this->Errors[] = $msg;
    }


    /**
     *  Create a formatted display-ready version of the error messages.
     *
     *  @return string      Formatted error messages.
     */
    public function PrintErrors()
    {
        $retval = '';

        foreach($this->Errors as $key=>$msg) {
            $retval .= "<li>$msg</li>\n";
        }
        return $retval;
    }


    /**
     * Determine if the current user has read access to this category.
     *
     * @param   array|null  $groups     Array of groups, needed for sitemap
     * @return  boolean     True if user has access, False if not
     */
    public function canRead($groups = NULL)
    {
        global $_GROUPS;

        if ($groups === NULL) {
            $groups = $_GROUPS;
        }
        if (in_array($this->grp_access, $groups)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Determine if the current user has upload access to this category.
     *
     * @return  boolean     True if user has access, False if not
     */
    public function canUpload()
    {
        global $_GROUPS;

        if (\Group::inGroup($this->grp_writeaccess)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Helper function to check if this is the Root category.
     *
     * @return  boolean     True if this category is Root, False if not
     */
    public function isRoot()
    {
        return $this->cid == 1;
    }


    /**
     * Category Admin List View.
     *
     * @param   integer $cid     Optional category ID to limit listing
     * @return  string      HTML for the category list.
     */
    public static function adminList($cid=0)
    {
        global $_CONF, $_FM_CONF, $_TABLES, $_USER, $LANG_ADMIN;

        USES_lib_admin();

        $display = '';
        $sql = "SELECT cat.*, parent.title as pcat
            FROM {$_TABLES['filemgmt_cat']} cat
            LEFT JOIN {$_TABLES['filemgmt_cat']} parent
            ON cat.pid = parent.cid";

        $header_arr = array(
//            array(
//                'text'  => 'ID',
//                'field' => 'cid',
//                'sort'  => true,
//            ),
            array(
                'text'  => $LANG_ADMIN['edit'],
                'field' => 'edit',
                'sort'  => false,
                'align' => 'center',
            ),
            array(
                'text'  => 'Title',
                'field' => 'title',
                'sort'  => true,
            ),
            array(
                'text'  => 'Parent',
                'field' => 'pid',
                'sort'  => false,
            ),
            array(
                'text'  => 'Can View',
                'field' => 'grp_access',
                'sort'  => false,
            ),
            array(
                'text'  => 'Can upload',
                'field' => 'grp_writeaccess',
                'sort'  => false,
            ),
            array(
                'text'  => 'Delete',
                'field' => 'delete',
                'sort' => false,
                'align' => 'center',
            ),
        );

        $defsort_arr = array(
            'field' => 'cid',
            'direction' => 'asc',
        );

        $display .= COM_startBlock('', '', COM_getBlockTemplate('_admin_block', 'header'));
        $display .= COM_createLink(
            'New Item',
            $_FM_CONF['admin_url'] . '/index.php?modCat=0',
            array(
                'class' => 'uk-button uk-button-success',
                'style' => 'float:left',
            )
        );

        $query_arr = array(
            'table' => 'filemgmt_cat',
            'sql' => $sql,
            'query_fields' => array('cat.title'),
            'default_filter' => 'WHERE 1=1',
        );

        $text_arr = array(
            'has_extras' => true,
            'form_url' => 'index.php?op=categoryConfigAdmin',
        );

        $display .= ADMIN_list(
            'filemgmt_catlist',
            array(__CLASS__,  'getAdminField'),
            $header_arr, $text_arr, $query_arr, $defsort_arr,
            '', '', '', ''
        );
        $display .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
        return $display;
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
        global $_TABLES, $LANG_ADMIN, $_FM_CONF;

        $retval = '';
        static $grp_names = array();
        static $cat_names = array();

        switch($fieldname) {
        case 'edit':
            $retval .= FieldList::edit(array(
                'url' => $_FM_CONF['admin_url'] . "/index.php?modCat={$A['cid']}",
                'attr' => array(
                    'class' => 'tooltip',
                    'title' => 'Edit',
                ),
            ) );
            break;

        case 'grp_writeaccess':
        case 'grp_access':
            $fieldvalue = (int)$fieldvalue;
            if (!isset($grp_names[$fieldvalue])) {
                $grp_names[$fieldvalue] = DB_getItem(
                    $_TABLES['groups'],
                    'grp_name',
                    "grp_id = $fieldvalue"
                );
            }
            $retval = $grp_names[$fieldvalue];
            break;

        case 'pid':
            if ($fieldvalue != $A['cid']) {
                $fieldvalue = (int)$fieldvalue;
                if (!isset($cat_names[$fieldvalue])) {
                    $cat_names[$fieldvalue] = DB_getItem(
                        $_TABLES['filemgmt_cat'],
                        'title',
                        "cid = $fieldvalue"
                    );
                }
                $retval = $cat_names[$fieldvalue];
            }
            break;

        case 'delete':
            $retval = FieldList::delete(array(
                'delete_url' => $_FM_CONF['admin_url'] . "/index.php?delCat={$A['cid']}",
                'attr' => array(
                    'onclick' => "return confirm('This deletes this category, all sub-categories and all files contained in the cateogory / sub-category. Are you sure you want to continue?');",
                    'title' => 'Delete Category',
                ),
            ) );
            break;

        case 'title':
            $retval = strip_tags($fieldvalue);
            if (utf8_strlen($retval) > 80) {
                $retval = substr($retval, 0, 80 ) . '...';
            }
            break;

        default:
            $retval = htmlspecialchars($fieldvalue, ENT_QUOTES, COM_getEncodingt());
            break;
        }
        return $retval;
    }


    /**
     * Load all categories from the database into an array.
     *
     * @return  array       Array of category objects
     */
    public static function getAll()
    {
        global $_TABLES;

        $retval = array();
        $sql = "SELECT cid FROM {$_TABLES['filemgmt_cat']}";
        $res = DB_query($sql);
        while ($A = DB_fetchArray($res, false)) {
            $retval[$A['cid']] = self::getInstance($A['cid']);
        }
        return $retval;
    }


    /**
     * Get the record ID for a category.
     *
     * @return  integer     Category DB record ID
     */
    public function getID()
    {
        return $this->cid;
    }


    /**
     * Get the category name.
     *
     * @return  string  Category name
     */
    public function getName()
    {
        return $this->title;
    }


    /**
     * Get the parent category ID.
     *
     * @return  integer     Parent ID
     */
    public function getParentID()
    {
        return (int)$this->pid;
    }


    /**
     * Get the category title.
     *
     * @return  string      Category title
     */
    public function getDscp()
    {
        return $this->title;
    }


    /**
     * Get the selection options for child categories, indenting each level.
     *
     * @param   integer $pid        Parent category ID
     * @param   string  $indent     String to use for indenting
     * @param   integer $current_cat    Current category ID, to set "selected"
     * @return  string      HTML for selection options
     */
    public static function getChildOptions($pid, $indent, $current_cat)
    {
        global $_TABLES;

        $pid = (int)$pid;
        $retval = '';
        $spaces = ($indent+1) * 2;

        $sql = "SELECT * FROM {$_TABLES['filemgmt_cat']}
            WHERE pid = $pid
            ORDER BY title ASC";
        $result = DB_query($sql);
        while (($C = DB_fetchArray($result)) != NULL) {
            $retval .= '<option value="'.$C['cid'].'"';
            if ( $C['cid'] == $current_cat ) {
                $retval .= ' selected="selected"';
            }
            $retval .= '>';
            for ($x=0;$x<=$spaces;$x++) {
                $retval .= '&nbsp;';
            }
            $retval .= $C['title'].'</option>';
            $retval .= self::getChildOptions($C['cid'], $indent+1, $current_cat);
        }
        return $retval;
    }

}
