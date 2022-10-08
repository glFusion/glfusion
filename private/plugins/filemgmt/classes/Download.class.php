<?php
/**
 * Class to manage file items.
 *
 * @author      Mark R. Evans <mark AT glfusion DOT org>
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2008-2022 Mark R. Evans <mark AT glfusion DOT org>
 * @package     filemgmt
 * @version     v1.9.0
 * @since       v0.9.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Filemgmt;

use glFusion\FileSystem;
use glFusion\Database\Database;
use glFusion\Log\Log;
use glFusion\Cache\Cache;
use glFusion\FieldList;
use Filemgmt\Models\Status;

/**
 * Class for downloadable items.
 * @package filemgmt
 */
class Download
{
    const PERMS = '0644';

    /** Downoad ID.
     * @var integer */
    private $lid = 0;

    /** Category ID.
     * @var integer */
    private $cid = 1;

    /** Download title.
     * @var string */
    private $title = '';

    /** Actual file name.
     * Local files have just a file name, remote files have a full URL.
     * @var string */
    private $url = '';

    /** Maintainer's homepage
     * @var string */
    private $homepage = '';

    /** File version.
     * @var string */
    private $version = '';

    /** File size.
     * @var integer */
    private $size = 0;

    /** Platform for which the file is intended (not used?).
     * @var string */
    private $platform = '';

    /** Logo or thumbnail file URL.
     * @var string */
    private $logourl = '';

    /** Submitter's user ID.
     * @var integer */
    private $submitter = 0;

    /** Status flag.
     * @var integer */
    private $status = 1;

    /** Upload timestamp.
     * @var integer */
    private $date = 0;

    /** Hit counter.
     * @var integer */
    private $hits = 0;

    /** Rating value.
     * @var float */
    private $rating = 0;

    /** Number of votes received.
     * @var integer */
    private $votes = 0;

    /** Comment enabled/disabled flag.
     * @var integer */
    private $comments = 1;

    /** Description of the download.
     * @var string */
    private $description = '';

    /** Editing mode (submission, admin, moderation)
     * @var string */
    private $_editmode = 'submission';


    /**
     * Constructor.
     * Reads in the specified class, if $id is set.  If $id is zero,
     * then a new entry is being created.
     *
     * @param   integer|array   $id Record ID or array
     */
    public function __construct($lid=0)
    {
        global $_USER;

        if (is_array($lid)) {
            $this->setVars($lid, true);
        } elseif ($lid > 0) {
            $this->lid = (int)$lid;
            if (!$this->Read()) {
                $this->lid = 0;
            }
        } else {
            $this->submitter = $_USER['uid'];
        }
     }


    /**
     * Sets all variables to the matching values from the supplied array.
     *
     * @param   array   $row    Array of values, from DB or $_POST
     * @param   boolean $fromDB True if read from DB, false if from a form
     */
    public function setVars($row, $fromDB=false)
    {
        if (!is_array($row)) return;

        if (isset($row['lid'])) {
            $this->setLid($row['lid']);
        }
        if (isset($row['size'])) {
            $this->setSize($row['size']);
        }
        if (isset($row['hits'])) {
            $this->setHits($row['hits']);
        }
        if (isset($row['submitter'])) {
            $this->setSubmitter($row['submitter']);
        }
        if (isset($row['comments'])) {
            $this->setComments($row['comments']);
        }
        $this->setCid($row['cid'])
             ->setTitle($row['title'])
             ->setHomepage($row['homepage'])
             ->setVersion($row['version'])
             ->setDescription($row['description']);
        if ($fromDB) {
            $this->setLogoUrl($row['logourl'])
                 ->setVotes($row['votes'])
                 ->setRating($row['rating'])
                 ->setDate($row['date'])
                 ->setStatus($row['status'])
                 ->setUrl($row['url'])
                 ->setPlatform($row['platform']);
        } else {
        }
        return $this;
    }


    /**
     * Read a specific record and populate the local values.
     * Caches the object for later use.
     *
     * @param   integer $lid    Optional ID.  Current ID is used if zero.
     * @return  boolean     True if a record was read, False on failure
     */
    public function Read($id = 0)
    {
        global $_TABLES;

        $id = (int)$id;
        if ($id == 0) $id = $this->lid;
        if ($id == 0) {
            $this->error = 'Invalid ID in Read()';
            return;
        }

        $db = Database::getInstance();
        $sql = "SELECT det.*, dscp.description
            FROM {$_TABLES['filemgmt_filedetail']} det
            LEFT JOIN {$_TABLES['filemgmt_filedesc']} dscp
            ON det.lid = dscp.lid
            WHERE det.lid = ?";
        try {
            $stmt = $db->conn->executeQuery(
                $sql,
                array($id),
                array(Database::INTEGER)
            );
        } catch(\Throwable $e) {
            return false;
        }
        $data = $stmt->fetchAll(Database::ASSOCIATIVE);
        $stmt->closeCursor();

        /*$result = DB_query($sql);
        if (!$result || DB_numRows($result) != 1) {*/
        if (count($data) < 1) {
            return false;
        } else {
            //$row = DB_fetchArray($result, false);
            $this->setVars($data[0], true);
            return true;
        }
    }


    /**
     * Get all file downloads, subject to an optional query string and limit.
     *
     * @param   string  $where  Optional WHERE clause, not including keyword
     * @param   string  $limit  Optionsl LIMIT clause, not including keyword
     * @return  array       Array of all matching download records
     */
    public static function getAll($where = '', $limit = '')
    {
        global $_TABLES;

        $db = Database::getInstance();
        $sql = "SELECT det.*, dscp.description
            FROM {$_TABLES['filemgmt_filedetail']} det
            LEFT JOIN {$_TABLES['filemgmt_filedesc']} dscp
            ON det.lid = dscp.lid";
        if ($where != '') {
            $sql .= " WHERE $where";
        }
        if ($limit != '') {
            $sql .= " LIMIT $limit";
        }

        try {
            $stmt = $db->conn->executeQuery($sql);
        } catch(\Throwable $e) {
            return array();
        }
        return $stmt->fetchAll(Database::ASSOCIATIVE);
    }


    /**
     * Get an array of all new uploads.
     *
     * @uses    self::getAll()
     * @return  array       Array of newly-upload downloads.
     */
    public static function getNewUploads()
    {
        global $_FM_CONF;

        $interval = 86400 * (int)$_FM_CONF['whatsnewperioddays'];
        return self::getAll("status = 1 AND date > UNIX_TIMESTAMP() - $interval ORDER BY date DESC", 15);
    }


    /**
     * Get a download instance.
     * Checks static var first to limit DB reads for duplcicate requests.
     *
     * @param   integer $lid    Download ID
     * @return  object          Category object
     */
    public static function getInstance($lid)
    {
        static $files = array();
        if (!isset($files[$lid])) {
            $files[$lid] = new self($lid);
        }
        return $files[$lid];
    }


    /**
     * Set the file record ID.
     *
     * @param   integer $id     DB record ID
     * @return  object  $this
     */
    public function setLid($id)
    {
        $this->lid = (int)$id;
        return $this;
    }


    /**
     * Get the file record ID.
     *
     * @return  integer     DB record ID
     */
    public function getID()
    {
        return $this->lid;
    }


    /**
     * Set the category ID for this download.
     *
     * @param   integer $id     Category record ID
     * @return  object  $this
     */
    public function setCid($id)
    {
        $this->cid = (int)$id;
        return $this;
    }

    /**
     * Get the category ID for this download.
     *
     * @return  object  $this->cid
     */
    public function getCid()
    {
        return $this->cid;

    }


    /**
     * Set the file title string.
     *
     * @param   @string $title  Title string
     * @return  object  $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }


    /**
     * Get the file title.
     *
     * @return  string      Title string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Set the file title string.
     *
     * @param   @string $title  Title string
     * @return  object  $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }


    /**
     * Get the file URL.
     *
     * @return  string      File URL
     */
    public function getUrl()
    {
        return $this->url;
    }


    /**
     * Set the home page value.
     *
     * @param   string  $hp     Home pate
     * @return  object  $this
     */
    public function setHomepage($hp)
    {
        $this->homepage = $hp;
        return $this;
    }


    /**
     * Set the file version.
     *
     * @param   string  $ver    File version
     * @return  object  $this
     */
    public function setVersion($ver)
    {
        $this->version = $ver;
        return $this;
    }


    /**
     * Set the file size value.
     *
     * @param   integer $size   File size in bytes
     * @return  object  $this
     */
    public function setSize($size)
    {
        $this->size = (int)$size;
        return $this;
    }


    /**
     * Set the platform for the file.
     *
     * @param   string  $platform   Platform value
     * @return  object  $this
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }


    /**
     * Get the platform for this file.
     *
     * @return  string      File platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }


    /**
     * Set the logo filename.
     *
     * @param   string  $url    Logo URL (filename)
     * @return  object  $this
     */
    public function setLogoUrl($url)
    {
        $this->logourl = $url;
        return $this;
    }


    /**
     * Set the submitter's user ID.
     *
     * @param   integer $uid    User ID of the submitter
     * @return  object  $this
     */
    public function setSubmitter($uid)
    {
        $this->submitter = (int)$uid;
        return $this;
    }


    /**
     * Get the submitter's user ID.
     *
     * @return  integer     Submitter user ID
     */
    public function getSubmitter()
    {
        return (int)$this->submitter;
    }


    /**
     * Set the status flag.
     * 1 = approved, 0 = submission
     *
     * @param   integer $flag   Status flag
     * @return  object  $this
     */
    public function setStatus($flag)
    {
        $this->status = (int)$flag;
        return $this;
    }


    /**
     * Set the file upload date as a timestamp.
     *
     * @param   integer $ts     Timestamp
     * @return  object  $this
     */
    public function setDate($ts)
    {
        $this->date = (int)$ts;
        return $this;
    }


    /**
     * Set the download count.
     *
     * @param   integer $hits   Download counter value
     * @return  object  $this
     */
    public function setHits($hits)
    {
        $this->hits = (int)$hits;
        return $this;
    }


    /**
     * Set the rating value.
     *
     * @param   float   $rating Rating score
     * @return  object  $this
     */
    public function setRating($rating)
    {
        $this->rating = (float)$rating;
        return $this;
    }


    /**
     * Set the number of votes recieved.
     *
     * @param   integer $votes  Number of votes received
     * @return  object  $this
     */
    public function setVotes($votes)
    {
        $this->votes = (int)$votes;
        return $this;
    }


    /**
     * Set the comment flag value.
     *
     * @param   integer $flag   Flag value (0, 1 or 2)
     * @return  object  $this
     */
    public function setComments($flag)
    {
        $this->comments = (int)$flag;
        return $this;
    }


    /**
     * Get the comment flag value.
     *
     * @return  integer     Comment flag
     */
    public function getCommentFlag()
    {
        return (int)$this->comments;
    }


    /**
     * Set the file description.
     *
     * @param   strng   $dscp   File description
     * @return  object  $this
     */
    public function setDescription($dscp)
    {
        $this->description = $dscp;
        return $this;
    }


    /**
     * Get the file description.
     *
     * @return  string      File description
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Save the current values to the database.
     *
     * @param  array   $A      Optional array of values from $_POST
     * @return boolean         True if no errors, False otherwise
     */
    public function save($A = array())
    {
        global $_CONF, $_USER, $_TABLES, $_FM_CONF, $LANG_FILEMGMT;

        if (is_array($A)) {
            $this->setVars($A, false);
        }

        if (defined('DEMO_MODE')) {
            return Status::UPL_NODEMO;
        }

        $AddNewFile = false;

        $missing = array();
        //$missing[] = 'Test message';

        $myts = new MyTextSanitizer;

        // Check for missing required fields.
        // The form should catch these, but just in case...
        if ($this->title == "") {
            $missing[] = $LANG_FILEMGMT['title'];
        }
        if ($this->description == "") {
            $missing[] = $LANG_FILEMGMT['description'];
        }
        if ($this->cid == 0) {
            $missing[] = $LANG_FILEMGMT['category'];
        }

        if ((int) $this->lid != 0 && $_FILES['newfile']['size'] == 0  && empty($this->url)) {
            $missing[] = $LANG_FILEMGMT['no_file_uploaded'];
        }
        if (!empty($missing)) {
            // If errors are found, set the error message and return.
            $msg = $LANG_FILEMGMT['err_req_fields'];
            $msg .= '<ul><li>' . implode('</li><li>', $missing) . '</li></ul>';
            COM_setMsg($msg, 'error');
            return Status::UPL_MISSING;
        }

        $Cat = new Category($this->cid);

        if ($Cat->canUpload()) {
            $this->status = Status::APPROVED;
            $retval = Status::UPL_OK;
            if ($_FM_CONF['outside_webroot']) {
                $file_path = $_CONF['path'].'data/filemgmt_data/files/';
            } else {
                $file_path = $_FM_CONF['FileStore'];
            }
            $snap_path = $_FM_CONF['SnapStore'];
        } else {
            $this->status = Status::SUBMISSION;
            $retval = Status::UPL_PENDING;
            $file_path = $_FM_CONF['FileStore_tmp'];
            $snap_path = $_FM_CONF['SnapStore_tmp'];
        }

        $fileurl = isset($A['fileurl']) ? COM_applyFilter($A['fileurl']) : '';

// only want to do this for replacement files or new files...

// we have a file to upload - so we need to process it.
//        if ($_FILES['newfile']['size'] != 0  && empty($this->url)) {
        if ($_FILES['newfile']['size'] != 0) {
            if ($_FM_CONF['outside_webroot']) {
                FileSystem::mkDir($_CONF['path'].'data/filemgmt_data/files/');
            } else {
                FileSystem::mkDir($_FM_CONF['FileStore']);
            }
            FileSystem::mkDir($_FM_CONF['SnapStore']);

            $upload = new UploadDownload();
            $upload->setPerms(self::PERMS);
            $upload->setFieldName('newfile');

            if ( FileSystem::mkDir($file_path) === false ) {
                Log::write('system',Log::ERROR,'FileMgmt: Unable to create ' . $file_path. ' ');
                ErrorHandler::show("1106");
            }

            $upload->setPath($file_path);
            $upload->setAllowAnyMimeType(true);     // allow any file type
            $upload->setMaxFileSize(100000000);
            $upload->setMaxDimensions(8192,8192);

            $AddNewFile = false;
            if ($upload->numFiles() > 0) {
                $upload->uploadFiles();
                if ($upload->areErrors()) {
                    $errmsg = "Upload Error: " . $upload->printErrors(false);
                    Log::write('system',Log::ERROR, $errmsg);
                    ErrorHandler::show("1106");
                } else {
                    $uploaded_file = $upload->getUploadedFiles()[0];
                    $this->size = (int)$uploaded_file['size'];
                    $filename = $uploaded_file['name'];
                    $this->url = rawurlencode($filename);

                    $pos = strrpos($filename,'.') + 1;
                    $fileExtension = strtolower(substr($filename, $pos));
                    if (array_key_exists($fileExtension, $_FM_CONF['extensions_map'])) {
                        if ($_FM_CONF['extensions_map'][$fileExtension] == 'reject' ) {
                            Log::write('system',Log::ERROR, 'AddNewFile - New Upload file is rejected by config rule: ' .$filename);
                            ErrorHandler::show("1109");
                        } else {
                            $fileExtension = $_FM_CONF['extensions_map'][$fileExtension];
                            $pos = strrpos($this->url,'.') + 1;
                            $this->url = strtolower(substr($this->url, 0,$pos)) . $fileExtension;

                            $pos2 = strrpos($filename,'.') + 1;
                            $filename = substr($filename,0,$pos2) . $fileExtension;
                        }
                    }
                    $AddNewFile = true;
                }
            }
        }
// this handles the remote URL
        if (!empty($fileurl)) {

            $rc = (int) $this->validateUrl($fileurl);
            Log::write('system',Log::DEBUG,'URL Validation returned ' . $rc);
            if ($rc < 400) {
                $this->setUrl($fileurl);
                $size = 0;
                $AddNewFile = true;
            } else {
                Log::write('system',Log::ERROR,'FileMgmt: Remote URL validation failed for ' . $fileurl);
            }
        }
// end of new file upload

// start of new file screen shot

        if ( FileSystem::mkDir($snap_path) === false ) {
            Log::write('system',Log::ERROR,'FileMgmt: Unable to create ' . $snap_path. ' ');
            ErrorHandler::show("1106");
        }

        $upload = new UploadDownload();
        $upload->setFieldName('newfileshot');
        $upload->setPerms(self::PERMS);
        $upload->setPath($snap_path);
        $upload->setAllowAnyMimeType(false);
        $upload->setAllowedMimeTypes(       // allow only images for snaps
            array(
                'image/gif'   => array('.gif'),
                'image/jpeg'  => array('.jpg', '.jpeg'),
                'image/pjpeg' => array('.jpg', '.jpeg'),
                'image/x-png' => array('.png'),
                'image/png'   => array('.png'),
            )
        );

        if (isset($_CONF['debug_image_upload']) && $_CONF['debug_image_upload']) {
            $upload->setLogFile($_CONF['path'] . 'logs/error.log');
            $upload->setDebug(true);
        }
        $upload->setMaxDimensions(2048,2048);
        $upload->setAutomaticResize(true);
        $upload->setMaxFileSize(100000000);
        $upload->uploadFiles();
        if ($upload->numFiles() > 0) {
            if ($upload->areErrors()) {
                $errmsg = "Upload Error: " . $upload->printErrors(false);
                Log::write('system',Log::ERROR, $errmsg);
                ErrorHandler::show("1106");
            } else {
                $snapfilename = $myts->makeTboxData4Save($upload->getUploadedFiles()[0]['name']);
                $this->logourl = $myts->makeTboxData4Save(rawurlencode($snapfilename));
                $AddNewFile = true;
            }
        } elseif (isset($A['deletesnap'])) {
            $this->deleteImage();
        }
// end of the file shot

        if ($AddNewFile || $this->lid > 0) {
            if (strlen($this->version) > 24) {
                $this->version = substr($this->version,0,24);
            }

            if ($this->lid == 0) {
                $sql1 = "INSERT INTO `{$_TABLES['filemgmt_filedetail']}` SET
                    date = UNIX_TIMESTAMP(), ";
                $sql3 = '';
                // Determine write access to category for new uploads
            } else {
                $sql1 = "UPDATE `{$_TABLES['filemgmt_filedetail']}` SET ";
                $sql3 = " WHERE lid = {$this->lid} ";
            }

            $sql2 = "cid = {$this->cid},
                title = '" . DB_escapeString($this->title) . "',
                url = '" . DB_escapeString($this->url) . "',
                homepage = '" . DB_escapeString($this->homepage) . "',
                version = '" . DB_escapeString($this->version) . "',
                size = {$this->size},
                logourl = '" . DB_escapeString($this->logourl) . "',
                submitter = {$this->submitter},
                status = {$this->status},
                hits = {$this->hits},
                rating = {$this->rating},
                votes = {$this->votes},
                comments = {$this->comments}";

            if (!isset($A['silentedit']) && $this->lid <> 0) {
                $sql2 .= ",date = UNIX_TIMESTAMP() ";
            }

            $sql = $sql1 . $sql2 . $sql3;
            DB_query($sql);

            if ($this->lid == 0) {
                $this->lid = DB_insertID();
            }

            // Update the description table
            $desc = DB_escapeString($this->description);
            DB_query(
                "INSERT INTO {$_TABLES['filemgmt_filedesc']} SET
                    lid = {$this->lid},
                    description = '$desc'
                ON DUPLICATE KEY UPDATE
                    description = '$desc'"
            );

            if ($this->status == Status::SUBMISSION) {
                Notifier::notifyAdmins($this);
            } else {
                PLG_itemSaved($this->lid, 'filemgmt');
            }

            $c = Cache::getInstance()->deleteItemsByTag('whatsnew');
            if (isset($duplicatefile) && $duplicatefile) {
                $retval = Status::UPL_DUPFILE;
            } elseif (isset($duplicatesnap) && $duplicatesnap) {
                $retval = Status::UPL_DUPSNAP;
            } elseif (!$AddNewFile) {
                // no new file uploaded during editing
                $retval = Status::UPL_UPDATED;
            }
        } else {
            // Could not upload the file
            $retval = Status::UPL_ERROR;
        }
// need to check the retval from calling function.
        return $retval;

    }   // function save()


    /**
     * Delete the current download record from the database.
     */
    public function delete()
    {
        global $_CONF, $_TABLES, $_FM_CONF;

        if ($this->status == 0) {
            // Deleting a submission
            $tmpfile = $_FM_CONF['FileStore_tmp'] . $this->url;
            $tmpsnap  = $_FM_CONF['SnapStore_tmp'] . $this->logourl;
        } else {
            if ($_FM_CONF['outside_webroot']) {
                $tmpfile = $_CONF['path'].'data/filemgmt_data/files/'.$this->url;
            } else {
                $tmpfile = $_FM_CONF['FileStore'] . $this->url;
            }
            $tmpsnap  = $_FM_CONF['SnapStore'] . $this->logourl;
        }

        DB_delete($_TABLES['filemgmt_filedetail'], 'lid', $this->lid);
        DB_delete($_TABLES['filemgmt_filedesc'], 'lid', $this->lid);
        DB_delete($_TABLES['filemgmt_votedata'], 'lid', $this->lid);
        DB_delete($_TABLES['filemgmt_brokenlinks'], 'lid', $this->lid);
        DB_delete($_TABLES['comments'], array('sid', 'type'), array($this->lid, 'filemgmt'));

        // Check for duplicate files of the same filename (actual filename in repository)
        // We don't want to delete actual file if there is more then 1 record linking to it.
        // Site may be allowing more than 1 file listing to duplicate files.
        if ($this->url != '') {     // should always have one, but check anyway
            $refs = DB_count($_TABLES['filemgmt_filedetail'], 'url', $this->url);
            if ($refs == 0 && is_file($tmpfile)) {
                @unlink($tmpfile);
            }
        }
        if ($this->logourl != '') {
            $refs = DB_count($_TABLES['filemgmt_filedetail'], 'logourl', $this->logourl);
            if ($refs == 0 && is_file($tmpsnap)) {
                @unlink($tmpsnap);
            }
        }

        if ($this->status == 1) {
            PLG_itemDeleted($this->lid, 'filemgmt');
        }
        $c = Cache::getInstance()->deleteItemsByTag('whatsnew');
        return true;
    }


    /**
     * Indicate that this file is being edited as a user submission.
     *
     * @reurn   object  $this
     */
    public function asSubmission()
    {
        $this->setStatus(0);
        $this->_editmode = 'submit';
        return $this;
    }


    /**
     * Indicate that this file is being edited as a moderator to approve/delete.
     *
     * @return  object  $this
     */
    public function asModeration()
    {
        $this->_editmode = 'moderate';
        return $this;
    }


    /**
     * Creates the edit form.
     *
     * @param  array    $post   Array of values to pre-fill, e.g. $_POST
     * @return string      HTML for edit form
     */
    public function edit($post=array())
    {
        global $_CONF,$_FM_CONF, $_TABLES,$_USER, $_DB_name, $LANG_FILEMGMT;

        $display = '';
        $totalvotes = '';
        $myts = new MyTextSanitizer;
        $mytree = new XoopsTree($_DB_name,$_TABLES['filemgmt_cat'],"cid","pid");

        if ($this->_editmode == 'submit') {
            $T = new \Template($_CONF['path'] . 'plugins/filemgmt/templates');
            $T->set_file('form', 'upload.thtml');
            $cancel_url = $_FM_CONF['url'] . '/index.php';
            $lang_filetitle = _MD_FILETITLE;
        } else {
            // admin-level editing
            $T = new \Template($_CONF['path'] . 'plugins/filemgmt/templates/admin');
            $T->set_file(array(
                'form' => 'mod_file.thtml',
                'ratings' => 'vote_data.thtml',
            ));
            if ($this->_editmode == 'moderate') {
                $cancel_url = $_CONF['site_url'] . '/moderation.php';
            } else {
                $cancel_url = $_FM_CONF['admin_url'] . '/index.php';
            }
            $lang_filetitle = _MD_REPLFILENAME;
        }

        $T->set_var(array(
            'lang_file_id'  => _MD_FILEID,
            'lang_filename' => _MD_DLFILENAME,
            'lang_filetitle' => _MD_FILETITLE,
            'lang_replfile' => (($this->lid === 0) ? 'File' : _MD_REPLFILENAME),
            'lang_remote_url' => $LANG_FILEMGMT['remote_url'],
            'lang_homepage' => _MD_HOMEPAGEC,
            'lang_filesize' => _MD_FILESIZEC,
            'lang_bytes'    => _MD_BYTES,
            'lang_version'  => _MD_VERSIONC,
            'lang_description' => _MD_DESCRIPTIONC,
            'lang_category' => _MD_CATEGORYC,
            'lang_screenshot' => _MD_SHOTIMAGE,
            'lang_comments' => _MD_COMMENTOPTION,
            'lang_yes'      => _MD_YES,
            'lang_no'       => _MD_NO,
            'lang_owner'    => _MD_OWNER,
            'lang_silent_edit' => _MD_SILENTEDIT,
            'lang_submit'   => _MD_SUBMIT,
            'lang_delete'   => _MD_DELETE,
            'lang_cancel'   => _MD_CANCEL,
            'lang_hits'     => _MD_HITSC,
            'token_name'    => CSRF_TOKEN,
            'security_token' => SEC_createToken(),
            'redirect'      => $this->_editmode,
            'cancel_url'    => $cancel_url,
            'redirect_url'  => $_SERVER['HTTP_REFERER'].'#fileid_'.$this->lid,
//            'redirect_url'  => '',
            'lang_approve'  => _MD_APPROVEREQ,
        ));
        if ($this->lid === 0) {
            $T->set_var('newfile',true);
        } else {
            $T->unset_var('newfile');
        }

        list($cacheFile, $cacheURL) = COM_getStyleCacheLocation();

        $T->set_var(array(
            'stylesheet' => $cacheURL,
        ));


        $pathstring = "<a href=\"{$_FM_CONF['url']}/index.php\">"._MD_MAIN."</a>&nbsp;:&nbsp;";
        $nicepath = $mytree->getNicePathFromId($this->cid, "title", "{$_FM_CONF['url']}/viewcat.php");
        $pathstring .= $nicepath;
        if ($this->lid > 0) {
            $hdr_title = $this->title;
        } else {
            $hdr_title = $LANG_FILEMGMT['new_file'];
        }
        $pathstring .= "<a href=\"{$_FM_CONF['url']}/index.php?id={$this->lid}\">{$hdr_title}</a>";

        $categorySelectHTML = '';
        $rootCats = Category::getChildren(0, true);
        foreach ($rootCats as $cid=>$Cat) {
            $categorySelectHTML .= '<option value="'.$cid.'"';
            if ($cid == $this->cid) {
                $categorySelectHTML .= ' selected="selected" ';
            }
            $categorySelectHTML .= '>' . $Cat->getName();

            if (!$Cat->canUpload()) {
                $categorySelectHTML .= " *";
            }
            $categorySelectHTML .= "</option>\n";
            $arr = $mytree->getChildTreeArray($cid);
            foreach ($arr as $option) {
                $Cat = new Category($option);
                $option['prefix'] = str_replace(".","--",$option['prefix']);
                $catpath = $option['prefix']."&nbsp;".$myts->makeTboxData4Show($Cat->getName());
                $categorySelectHTML .= '<option value="'.$Cat->getID() . '"';
                if ($Cat->getID() == $this->cid) {
                    $categorySelectHTML .= ' selected="selected" ';
                }
                $categorySelectHTML .= '>';

                if (!$Cat->canUpload()) {
                    $categorySelectHTML .= "$catpath *";
                } else {
                    $categorySelectHTML .= $catpath;
                }
                $categorySelectHTML .= "</option>\n";
            }
        }
        if ($_FM_CONF['outside_webroot']) {
            $tFile = $_CONF['path'].'data/filemgmt_data/files/'.$this->url;
        } else {
            $tFile = $_FM_CONF['FileStore'].$this->url;
        }

        $is_found = false;
        $parts = parse_url($this->url);
        if (!isset($parts['scheme'])) {
            // Local file, check that the file exists
            if ($_FM_CONF['outside_webroot']) {
                $tFile = $_CONF['path'].'data/filemgmt_data/files/' . rawurldecode($this->url);
            } else {
                $tFile = $_FM_CONF['FileStore'] . rawurldecode($this->url);
            }
            $is_found = file_exists($tFile);
            clearstatcache();
        } else {
            // Remote file, validate link or assume the file exists
            $tFile = $this->url;
            if (Download::validateUrl($this->url) < 400) {
                $is_found = true;
            }
        }
        if ($is_found === false) {
            $T->set_var('file_missing',true);
        } else {
            $T->unset_var('file_missing');
        }

        $T->set_var(array(
            'lid'   => $this->lid,
            'title' => $this->title,
            'path' => $pathstring,
            'url'   => rawurldecode($myts->makeTboxData4Edit($this->url)),
            'homepage'  => $myts->makeTboxData4Edit($this->homepage),
            'version' => $myts->makeTboxData4Edit($this->version),
            'filesize'  => $myts->makeTboxData4Edit($this->size),
            'logo_url'  => rawurldecode($myts->makeTboxData4Edit($this->logourl)),
            'description' => $myts->makeTareaData4Edit($this->description),
            'category'  => $this->cid,
            'category_select_options' => $categorySelectHTML,
            'owner_select' =>  COM_buildOwnerList('submitter', $this->submitter,true),
            'hits' => $myts->makeTboxData4Edit($this->hits),
            'can_delete' => $this->lid > 0,
            'cmt_chk_' . $this->comments => 'checked="checked"',
        ));

        if (!empty($this->logourl)) {//  && file_exists($_FM_CONF['SnapStore'].$this->logourl)) {
            $T->set_var('thumbnail', $_FM_CONF['FileSnapURL'].$this->logourl);
        } else {
            $T->unset_var('thumbnail');
        }

        if ($_FM_CONF['silent_edit_default']) {
            $T->set_var('silent_edit_checked', ' checked="checked" ');
        } else {
            $T->set_var('silent_edit_checked', '');
        }

        if ($this->lid > 0) {
            $ratingData = RATING_getVoteData(
                'filemgmt',
                $this->lid,
                'ratingdate',
                'desc',
                array("AND" => "u.uid > 1")
            );
            $votes = count($ratingData);

            $T->set_var(array(
                'totalvotes' => (int)$totalvotes,
                'lang_dlratings' => sprintf(_MD_DLRATINGS, $votes),
                'lang_user' => _MD_USER,
                'lang_ip' => _MD_IP,
                'lang_rating' => _MD_RATING,
                'lang_date' => _MD_DATE,
                'lang_delete' => _MD_DELETE,
                'votes' => $votes,
            ) );

            $cssid = 1;
            $T->set_block('ratings', 'reg_votes', 'rVotes');

            foreach ($ratingData AS $data) {

                $formatted_date = self::formatTimestamp($data['ratingdate']);
                $T->set_var(array(
                    'ratinguname' => $data['username'],
                    'ratinghostname' => $data['ip_address'],
                    'rating' => $data['rating'],
                    'ratingid' => $data['id'],
                    'cssid' => $cssid,
                    'formatted_date' => $formatted_date,
                ) );
                $T->parse('rVotes', 'reg_votes', true);
                $cssid = ($cssid == 1) ? 2 : 1;
            }
            $T->parse('rating_votes', 'ratings');
        }

        $T->parse('output', 'form');
        $display .= $T->finish($T->get_var('output'));
        return $display;
    }


    /**
     * Determine if the current user has read access to this file.
     * First checks that this is a valid record (ID > 0).
     *
     * @param   array|null  $groups     Array of groups, needed for sitemap
     * @return  boolean     True if user has access, False if not
     */
    public function canRead($groups = NULL)
    {
        return $this->lid > 0 && Category::getInstance($this->cid)->canRead($groups) && (SEC_hasRights('filemgmt.edit') || $this->status == 1);
    }


    /**
     * Check if the current user can upload files at all.
     *
     * @return  boolean     True if uploads are allowed, False if not
     */
    public static function canSubmit()
    {
        global $_FM_CONF;

        return (SEC_hasRights("filemgmt.upload") || $_FM_CONF['uploadselect']);
    }

    /**
     * Deletes a single image from disk.
     * $del_db is used to save a DB call if this is called from Save().
     *
     */
    public function deleteImage()
    {
        global $_TABLES, $_FM_CONF;

        $filename = $this->logourl;
        if (is_file("{$_FM_CONF['SnapStore']}/{$filename}")) {
            @unlink("{$_FM_CONF['SnapStore']}/{$filename}");
        }

        $this->logourl= '';
    }


    /**
     * Mark this file as approved.
     * Move file and snapshot from tmp directory to the main storage area.
     *
     * @return  object  $this
     */
    public function approve()
    {
        global $_TABLES, $_FM_CONF, $_CONF;

        $AddNewFile = false;
        /*$tmpnames = explode(';', $this->platform);
        $tmpfilename = $tmpnames[0];
        if ( isset($tmpnames[1]) ) {
            $tmpshotname = $tmpnames[1];
        } else {
            $tmpshotname = '';
        }*/
        //$tmp = $_FM_CONF['FileStore_tmp'] . $tmpfilename;
        $tmp = $_FM_CONF['FileStore_tmp'] . $this->url;
        if (file_exists($tmp) && (is_file($tmp))) {
            // if this temporary file was really uploaded?
            if ($_FM_CONF['outside_webroot']) {
                $newfile = $_CONF['path'].'data/filemgmt_data/files/'.$this->url;
            } else {
                $newfile = $_FM_CONF['FileStore'] . $this->url;
            }
            Log::write('system',Log::INFO, 'FileMgt Approve: File move from '.$tmp. ' to ' .$newfile );
            $rename = @rename($tmp, $newfile);
            Log::write('system',Log::INFO, 'FileMgt Approve: Results of rename is: '. $rename);
            $chown = @chmod($newfile, octdec(self::PERMS));
            if (!is_file($newfile)) {
                Log::write('system',Log::ERROR, 'Filemgmt upload approve error: New file does not exist after move of tmp file: '.$newfile);
                $AddNewFile = false;    // Set false again - in case it was set true above for actual file
                ErrorHandler::show("1101");
            } else {
               $AddNewFile = true;
            }
        } else {
            Log::write('system', Log::ERROR, 'Filemgmt upload approve error: Temporary file does not exist: '.$tmp);
            ErrorHandler::show("1101");
        }

        $tmp = $_FM_CONF['SnapStore_tmp'] . $this->logourl;
        if (file_exists($tmp) && (is_file($tmp))) {
            // if this temporary file was really uploaded?
            $newfile = $_FM_CONF['SnapStore'] . $this->logourl;
            Log::write('system',Log::INFO, 'FileMgt Approve: File move from '.$tmp. ' to ' .$newfile );
            $rename = @rename($tmp, $newfile);
            Log::write('system',Log::INFO, 'FileMgt Approve: Results of rename is: '. $rename);
            $chown = @chmod($newfile, self::PERMS);
            if (!is_file($newfile)) {
                Log::write('system',Log::ERROR, 'Filemgmt upload approve error: New file does not exist after move of tmp file: '.$newfile);
                $AddNewFile = false;    // Set false again - in case it was set true above for actual file
                ErrorHandler::show("1101");
            } else {
               $AddNewFile = true;
            }
        }
        // No "else", a missing logourl is acceptable

        if ($AddNewFile) {
            // Finish processing if file movement was successful
            DB_query(
                "UPDATE {$_TABLES['filemgmt_filedetail']} SET
                status = 1,
                platform = ''
                WHERE lid = {$this->lid}"
            );

            Cache::getInstance()->deleteItemsByTag('whatsnew');

            // Send a email to submitter notifying them that file was approved
            Notifier::sendApproval($this);

            // Notify other plugins that a new file was added.
            PLG_itemSaved($this->lid, 'filemgmt');
        }
        return $this;
    }


    /**
     * Update the download counter.
     *
     * @return  object  $this
     */
    public function addHit()
    {
        global $_TABLES, $_USER;

        $uid = (int)$_USER['uid'];
        DB_query(
            "INSERT INTO {$_TABLES['filemgmt_history']}
            (uid, lid, remote_ip, date)
            VALUES
            ($uid, '{$this->lid}', '" . DB_escapeString($_SERVER['REMOTE_ADDR'])."', NOW())"
        );
        DB_query(
            "UPDATE {$_TABLES['filemgmt_filedetail']}
            SET hits=hits+1
            WHERE lid='{$this->lid}' AND status>0"
        );
        return $this;
    }


    /**
     * File Admin List View.
     *
     * @param   integer $cid     Optional category ID to limit listing
     * @return  string      HTML for the file list.
     */
    public static function adminList($cid=0, $status = -1)
    {
        global $_FM_CONF, $LANG_FM02, $_TABLES, $LANG_ADMIN, $LANG_FILEMGMT, $_DB_name;

        USES_lib_admin();

        $cid = (int)$cid;
        $selcat = '';
        $display = '';

        $mytree = new XoopsTree($_DB_name,$_TABLES['filemgmt_cat'], "cid", "pid");
        $filter = _MD_CATEGORYC .
            ': <select name="cat" onchange="this.form.submit()">' . LB .
            '<option value="0">' . _MD_ALL . '</option>' .
            $mytree->makeMySelBoxOptions("title", "title", $cid, 0, "cat", 'this.form.submit();') .
            '</select>' . LB;

        $header_arr = array(
            array(
                'text' => $LANG_FM02['edit'],
                'field' => 'edit',
                'sort' => false,
                'align' => 'center',
            ),
            array(
                'text' => $LANG_FM02['file'],
                'field' => 'title',
                'sort' => true,
            ),
            array(
                'text' => $LANG_FM02['category'],
                'field' => 'cat_name',
                'sort' => true,
            ),
            array(
                'text' => $LANG_FM02['version'],
                'field' => 'version',
                'sort' => true,
            ),
            array(
                'text' => $LANG_FILEMGMT['hits'],
                'field' => 'hits',
                'sort' => true,
                'align' => 'right',
            ),
            array(
                'text' => $LANG_FM02['size'],
                'field' => 'size',
                'sort' => true,
                'align'=>'right',
            ),
            array(
                'text' => $LANG_FM02['date'],
                'field' => 'date',
                'sort' => true,
                'align' => 'right',
            ),
            array(
                'text' => $LANG_FILEMGMT['delete'],
                'field' => 'delete',
                'align' => 'center',
            ),
        );
        $text_arr = array(
            'has_extras' => true,
//            'form_url'   => $_FM_CONF['admin_url'] . '/index.php?cat=' . (int)$cid,
            'form_url'   => $_FM_CONF['admin_url'] . '/index.php',
            'help_url'   => ''
        );
        $options = array(
            'chkdelete' => true,
            'chkall' => true,
            'chkfield' => 'lid',
            'chkname' => 'dl_bulk',
        );

        $defsort_arr = array(
            'field'     => 'date',
            'direction' => 'DESC',
        );

        if ( $cid != 0 ) {
            $where = " c.cid=".(int)$cid . " ";
        } else {
            $where = " 1=1 ";
        }

        if ($status > -1) {
            $where .= " AND status = $status ";
        }
        $sql = "SELECT d.*,c.title AS cat_name
            FROM {$_TABLES['filemgmt_filedetail']} AS d
            LEFT JOIN {$_TABLES['filemgmt_cat']} as c
            ON d.cid=c.cid WHERE ".
            $where;
        $query_arr = array(
            'table'          => 'filemgmt_filedetail',
            'sql'            => $sql,
            'query_fields'   => array('d.title'),
            'default_filter' => '',
        );

        $display .= COM_createLink(
            _MD_ADDNEWFILE,
            $_FM_CONF['admin_url'] . '/index.php?modDownload=0',
            array(
                'class' => 'uk-button uk-button-success',
                'style' => 'float:left',
            )
        );

        $display .= ADMIN_list(
            'filelist',
            array(__CLASS__, 'getAdminField'),
            $header_arr,
            $text_arr, $query_arr, $defsort_arr,$filter, '', $options
        );
        $display .= COM_endBlock();
        SESS_setVar('filemgmt.cat',$cid);
        return $display;
    }


    /**
     * Get the download history report for this file.
     *
     * @return  string      HTML for history report
     */
    public function getDownloadHistory()
    {
        global $_TABLES, $_FM_CONF, $LANG_FILEMGMT;

        USES_lib_admin();

        $sql = "SELECT fh.date, fh.uid, fh.remote_ip, u.username
            FROM {$_TABLES['filemgmt_history']} fh
            LEFT JOIN {$_TABLES['users']} u
            ON u.uid = fh.uid
            WHERE fh.lid = {$this->lid}";
        $header_arr = array(
            array(
                'text'  => _MD_DATE,
                'field' => 'date',
                'sort'  => true,
            ),
            array(
                'text'  => _MD_USER,
                'field' => 'username',
                'sort'  => false,
            ),
            array(
                'text'  => $LANG_FILEMGMT['remote_ip'],
                'field' => 'remote_ip',
                'sort'  => false,
            ),
        );
        $defsort_arr = array(
            'field' => 'date',
            'direction' => 'desc',
        );

        $content = '';
        $query_arr = array(
            'table' => 'filemgmt_history',
            'sql'   => $sql,
            'query_fields' => array(),
            'default_filter' => '',
        );
        $filter = '';
        $options = '';
        $text_arr = array(
            'has_extras' => false,
            'form_url' => $_FM_CONF['url'] . "/downloadhistory.php?lid={$this->lid}",
        );

        $retval = ADMIN_list(
            'filemgmt_downloadhistory',
            NULL,
            $header_arr, $text_arr, $query_arr, $defsort_arr,
            $filter, '', $options, ''
        );
        return $retval;
    }


    /**
     * Get an individual field for the file admin list.
     *
     * @param   string  $fieldname  Name of field (from the array, not the db)
     * @param   mixed   $fieldvalue Value of the field
     * @param   array   $A          Array of all fields from the database
     * @param   array   $icon_arr   System icon array (not used)
     * @return  string              HTML for field display in the table
     */
    public static function getAdminField($fieldname, $fieldvalue, $A, $icon_arr)
    {
        global $_FM_CONF, $_CONF, $_USER, $_TABLES, $LANG_ADMIN, $LANG_FM00;

        $retval = '';
        static $grp_names = array();
        static $cat_names = array();
        static $dt = NULL;
        if ($dt === NULL) {
            $dt = new \Date('now',$_USER['tzid']);
        }
        switch($fieldname) {
        case 'edit':
            $retval .= FieldList::edit(array(
                'url' => $_FM_CONF['admin_url'] . "/index.php?modDownload={$A['lid']}",
                'attr' => array(
                    'title' => _MD_EDIT,
                ),
            ) );
            break;

        case 'title' :
            if ($_FM_CONF['outside_webroot']) {
                $tFile = $_CONF['path'].'data/filemgmt_data/files/'.$A['url'];
            } else {
                $tFile = $_FM_CONF['FileStore'].$A['url'];
            }

            // Check if this is a local or remotely-hosted file.
            $is_found = false;
            $parts = parse_url($A['url']);
            if (!isset($parts['scheme'])) {
                // Local file, check that the file exists
                if ($_FM_CONF['outside_webroot']) {
                    $fullurl = $_CONF['path'].'data/filemgmt_data/files/' . rawurldecode($A['url']);
                } else {
                    $fullurl = $_FM_CONF['FileStore'] . rawurldecode($A['url']);
                }
                $is_found = file_exists($fullurl);
                clearstatcache();
            } else {
                // Remote file, validate link or assume the file exists
//                if (Download::validateUrl($A['url']) < 400) {
                    $is_found = true;
//                }
            }
            if ($is_found === false) {
                $retval = $fieldvalue . ' <span class="fm-file-missing tooltip" title="'.$LANG_FM00['not_found'].'"><sup>**</sup></span>';
            } else {
                $retval = $fieldvalue;
            }
            break;

        case 'date':
            $dt->setTimestamp($fieldvalue);
            $retval = $dt->format('M d, Y',true);
            break;

        case 'delete':
            $retval = FieldList::delete(array(
                'delete_url' => $_FM_CONF['admin_url'] . "/index.php?delDownload={$A['lid']}",
                'attr' => array(
                    'onclick' => "return confirm('OK to delete');",
                    'title' => _MD_DELETE,
                ),
            ) );
            break;

        case 'hits' :
            $retval = '<a href="'.$_CONF['site_url'].'/filemgmt/downloadhistory.php?lid='$A['lid'].'" target="_blank">';
            $retval .= COM_numberFormat($fieldvalue);
            $retval .= '</a>';
            break;

        case 'size' :
            $retval = self::prettySize($fieldvalue);
            break;

        default:
            $retval = htmlspecialchars($fieldvalue, ENT_QUOTES, COM_getEncodingt());
            break;
        }
        return $retval;
    }


    /**
     * Create the listing display for this file.
     *
     * @return  string      HTML for file record
     */
    public function showListingRecord()
    {
        global $_CONF, $_FM_CONF, $_TABLES, $LANG01, $LANG_FILEMGMT, $LANG_FM00;

        static $mytree = NULL;
        $dt = new \Date($this->date);

        $format = new \glFusion\Formatter();
        $format->setNamespace('filemgmt');
        $format->setAction('description');
        $format->setType('text');
        $format->setParseAutoTags(true);
        $format->setProcessBBCode(true);

        $T = new \Template($_CONF['path'] . 'plugins/filemgmt/templates');
        $T->set_file('record', 'filelisting_record.thtml');

        if ($mytree === NULL) {
            $mytree = new XoopsTree('',$_TABLES['filemgmt_cat'],"cid","pid");
        }
        $path = $mytree->getPathFromId($this->cid, "title");
        $parts = explode('/', substr($path, 1));
        $T->set_block('record', 'catPathElements', 'PE');
        foreach ($parts as $key=>$value) {
            $T->set_var(array(
                'first_element' => $key == 0,
                'path_element' => $value,
            ) );
            $T->parse('PE', 'catPathElements', true);
        }

        //$path = str_replace("/"," <img src='" .$_FM_CONF['url'] ."/images/arrow.gif' alt=''> ",$path);

        $T->set_var(array(
            'lid'       => $this->lid,
            'dtitle'    => $this->title,
            'hits'      => COM_numberFormat($this->hits),
//            'file_description' => nl2br($this->description),
            'file_description' => $format->parse($this->description, true,7200),
            'download_link' => COM_buildURL($_CONF['site_url'].'/filemgmt/visit.php?lid='.$this->lid),
            'file_link' => COM_buildURL($_CONF['site_url'].'/filemgmt/index.php?id='.$this->lid),
            'is_found' => true,
            'LANG_DLNOW' => _MD_DLNOW,
            'LANG_SUBMITTEDBY' => _MD_SUBMITTEDBY,
            'is_newdownload' => $this->date > (time() - (86400 * $_FM_CONF['whatsnewperioddays'])),
            'is_popular'    => $this->hits >= $_FM_CONF['popular_download'],
            'download_title' => _MD_CLICK2DL . urldecode($this->url),
            'url'       => $this->url,
            'enable_rating' => $_FM_CONF['enable_rating'],
            'rating'    => $this->rating,
            'votestring' => ($this->rating > 0) ? sprintf(_MD_NUMVOTES, $this->votes) : '',
            'logourl'   => $this->logourl,
            'snapshot_url' => $_FM_CONF['FileSnapURL'] . $this->logourl,
            'LANG_VER' =>  _MD_VERSION,
            'LANG_VERSION' => _MD_VERSIONC,
            'LANG_SUBMITDATE' => _MD_SUBMITDATE,
            'datetime'  => $dt->format('M.d.Y', true),
            'version'   => $this->version,
            'LANG_RATING' => $_FM_CONF['enable_rating'] ? _MD_RATINGC : '',
            'have_dlreport' => $this->hits > 0 && SEC_hasRights('filemgmt.edit'),
            'download_times' => sprintf(_MD_DLTIMES,$this->hits),
            'download_count' => COM_numberFormat($this->hits),
            'LANG_FILESIZE' => _MD_FILESIZE,
            'LANG_DOWNLOAD' => _MD_DOWNLOAD,
            'LANG_DOWNLOADS' => $LANG_FILEMGMT['downloads'],
            'LANG_FILELINK' => _MD_FILELINK,
            'LANG_RATETHISFILE' => _MD_RATETHISFILE,
            'LANG_REPORTBROKEN' => _MD_REPORTBROKEN,
            'LANG_HOMEPAGE' => _MD_HOMEPAGE,
            'LANG_CATEGORY' => _MD_CATEGORYC,
            'LANG_COMMENTS' => _MD_COMMENTSC,
            'category_path' => $path,
            'submitter_name' => COM_getDisplayName($this->submitter),
            'submitter_link' => $this->submitter > 1,
            'LANG_EDIT' => plugin_ismoderator_filemgmt() ? _MD_EDIT : '',
            'LANG_CLICK2SEE' => _MD_CLICK2SEE,
            'lang_pop'  => _MD_POP,
            'lang_new'  => _MD_NEW,
            'lang_new_title' => $LANG_FILEMGMT['newly_uploaded'],
            'lang_popular' => _MD_POPULAR,
            'lang_not_found' => $LANG_FM00['not_found'],
        ) );

        // Check if this is a local or remotely-hosted file.
        $parts = parse_url($this->url);
        if (!isset($parts['scheme'])) {
            // Local file, check that the file exists
            $T->set_var('file_size',self::prettySize($this->size));
            if ($_FM_CONF['outside_webroot']) {
                $fullurl = $_CONF['path'].'data/filemgmt_data/files/' . rawurldecode($this->url);
            } else {
                $fullurl = $_FM_CONF['FileStore'] . rawurldecode($this->url);
            }
            $is_found = file_exists($fullurl);
        } else {
            // Remote file, assume the file exists
            if ($this->size != 0) {
                $T->set_var('file_size',self::prettySize($this->size));
            } else {
                $T->set_var('file_size', 'Remote');
            }
            $is_found = true;
        }
        $T->set_var(array(
            'is_found' => $is_found,
            'homepage_url' => $this->homepage,
            'homepage' => $this->homepage,
        ) );
        if ($this->comments) {
            USES_lib_comments();
            $commentCount = CMT_getCount('filemgmt',"fileid_".$this->lid);
            $recentPostMessage =_MD_COMMENTSWANTED;
            if ($commentCount > 0) {
                $result4 = DB_query("SELECT cid, UNIX_TIMESTAMP(date) AS day,username FROM {$_TABLES['comments']},{$_TABLES['users']} WHERE {$_TABLES['users']}.uid = {$_TABLES['comments']}.uid AND sid = 'fileid_{$this->lid}' AND queued=0 ORDER BY date desc LIMIT 1");
                $C = DB_fetchArray($result4);
                $dt->setTimestamp($C['day']);
                $recentPostMessage = $LANG01[27].': '.$dt->format($_CONF['daytime'],true). ' ' . $LANG01[104] . ' ' . $C['username'];
            } else {
                $commentCount = 0;
            }
            $comment_link = CMT_getCommentLinkWithCount(
                'filemgmt',
                $this->lid,
                $_FM_CONF['url'] .'/index.php?id=' .$this->lid,
                $commentCount,
                1
            );
            $T->set_var('comment_link',$comment_link['link_with_count']);
            $T->set_var('comment_tooltip', $comment_link['comment_count']);
            $T->set_var('show_comments','true');
            $T->set_var('comment_count', $commentCount);
        } else {
            $T->set_var('show_comments','none');
            $T->unset_var('show_comments');
        }

        $T->set_var('rating_bar', $this->getRatingBar());

        $T->parse('output', 'record');
        return $T->finish($T->get_var('output'));
    }


    /**
     * Show comments for this file.
     *
     * @return  string      Comment listing, empty string if disabled
     */
    public function showComments()
    {
        if (!$this->comments) {
            return '';
        }

        USES_lib_comment();

        $cmt_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if (isset($_POST['order'])) {
            $cmt_order  =  $_POST['order'] == 'ASC' ? 'ASC' : 'DESC';
        } elseif (isset($_GET['order']) ) {
            $cmt_order =  $_GET['order'] == 'ASC' ? 'ASC' : 'DESC';
        } else {
            $cmt_order = '';
        }
        if (isset($_POST['mode'])) {
            $cmt_mode = COM_applyFilter($_POST['mode']);
        } elseif ( isset($_GET['mode']) ) {
            $cmt_mode = COM_applyFilter($_GET['mode']);
        } else {
            $cmt_mode = '';
        }
        $valid_cmt_modes = array('flat','nested','nocomment','threaded','nobar');
        if (!in_array($cmt_mode,$valid_cmt_modes)) {
            $cmt_mode = '';
        }

        return CMT_userComments(
            "fileid_{$this->lid}",
            $this->title,
            'filemgmt',
            $cmt_order,
            $cmt_mode,
            0,
            $cmt_page,
            false,
            plugin_ismoderator_filemgmt(),
            0,
            $this->submitter
        );
    }


    /**
     * Convert a number of bytes to a more readable format.
     *
     * @param   integer $size   Size in bytes
     * @return  string      Formatted size, e.g. "2.5 KB"
     */
    public static function prettySize($size)
    {
        if ($size > 1048576) {      // > 1 MB
            $mysize = sprintf('%01.2f', $size/1048573) . " mb";
        }
        elseif ($size >= 1024) {    // > 1 KB
            $mysize = sprintf('%01.2f' , $size/1024) . " kb";
        }
        else {
            $mysize = sprintf(_MD_NUMBYTES, $size);
        }
        return $mysize;
    }


    /**
     * Format a timestamp to a day string.
     *
     * @param   integer $ts     Unix timestamp
     * @return  string      Formatted string
     */
    public static function formatTimestamp($ts)
    {
        global $_USER;

        $dt = new \Date($ts, $_USER['tzid']);
        return $dt->format('M.d.y', true);
    }


    /**
     * Generate a random filename used for new uploads.
     *
     * @return  string      Random filename
     */
    public static function randomFilename()
    {
        $length=10;
        srand((double)microtime()*1000000);
        $possible_charactors = "abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $string = "";
        while(strlen($string)<$length) {
            $string .= substr($possible_charactors, rand()%(strlen($possible_charactors)),1);
        }
        return($string);
    }


    /**
     * Get the rating bar to show for this file.
     *
     * @return  string      Rating bar HTML, empty string if not available.
     */
    public function getRatingBar()
    {
        global $_FM_CONF, $_USER;

        $retval = '';

        if ($_FM_CONF['enable_rating']) {
            $static = false;
            $voted  = 0;
            if (COM_isAnonUser()) {
                $static = true;
                $voted = 1;
            } else if (isset($_USER['uid']) && $_USER['uid'] == $this->submitter) {
                $static = true;
                $voted = 0;
            } else {
                $FM_ratedIds = RATING_getRatedIds('filemgmt');
                if (@in_array($this->lid,$FM_ratedIds)) {
                    $static = true;
                    $voted = 1;
                } else {
                    $static = 0;
                    $voted = 0;
                }
            }

            $retval = RATING_ratingBar(
                'filemgmt',
                $this->lid,
                $this->votes,
                $this->rating,
                $voted,
                5,
                $static,
                'sm'
            );
        }
        return $retval;
    }


    /**
     * Get the total files under the specified category, limited by status.
     *
     * @param   integer $sel_id     Selected category
     * @param   integer $status     Status to limit results
     * @return  intger      Count of file records
     */
    public static function getTotalByCategory($sel_id, $status=NULL)
    {
        global $_TABLES, $_DB_name;

        $mytree = new XoopsTree($_DB_name,$_TABLES['filemgmt_cat'],"cid","pid");
        $count = 0;
        $arr = array();
        if ($status !== NULL) {
            $status_sql = 'AND a.status = ' . (int)$status;
        } else {
            $status_sql = '';
        }
        $sel_id = (int)$sel_id;
        $sql = "SELECT count(*) from {$_TABLES['filemgmt_filedetail']} a
            LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid
            WHERE a.cid = {$sel_id} $status_sql {$mytree->filtersql}";
        list($thing) = DB_fetchArray(DB_query($sql));
        $count = $thing;
        $arr = $mytree->getAllChildId($sel_id);
        $size = sizeof($arr);
        for( $i=0; $i<$size; $i++){
            $sql = "SELECT count(*) from {$_TABLES['filemgmt_filedetail']} a
                LEFT JOIN {$_TABLES['filemgmt_cat']} b ON a.cid=b.cid
                WHERE  a.cid = '{$arr[$i]}' $status_sql $mytree->filtersql";
            list($thing) = DB_fetchArray(DB_query($sql));
            $count += $thing;
        }
        return $count;
    }

    private function _buildOwnerList($fieldName,$owner_id=2)
    {
        global $_TABLES, $_CONF;

        $db = Database::getInstance();

        $stmt = $db->conn->executeQuery("SELECT * FROM `{$_TABLES['users']}` WHERE status=3 ORDER BY username ASC");
        $T = new \Template($_CONF['path_layout'] . '/fields');
        $T->set_file('selection', 'selection.thtml');
        $T->set_var('var_name', $fieldName);
        $options = '';
        while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
            $options .= '<option value="' . $row['uid'] . '"';
            if ($owner_id == $row['uid']) {
                $options .= ' selected="selected"';
            }
            $options .= '>' . COM_getDisplayName($row['uid']) . '</opton>' . LB;
        }
        $T->set_var('option_list', $options);
        $T->parse('output', 'selection');
        $owner_select = $T->finish($T->get_var('output'));
        return $owner_select;
    }

    /**
     * Validate remote URL
     *
     * @return  string      status code
     */
    public static function validateUrl($url)
    {
        $retval = '';

        set_time_limit(0);
        $req=new \http_class;
        $req->timeout=0;
        $req->data_timeout=0;
        $req->debug=0;
        $req->html_debug=0;
        $req->follow_redirect = 1;
        $req->accept = "*/*";
        $req->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
        $error=$req->GetRequestArguments($url,$arguments);
        $arguments["Headers"]["Pragma"]="nocache";

        $error=$req->Open($arguments);
        if ( $error != "" ) {
            $retval = 404;
        } else {
            $headers=array();
            $error=$req->SendRequest($arguments);
            if ( $error != "" ) {
                $retval = 404;;
            } else {
                $error=$req->ReadReplyHeaders($headers);
                if ( $error != "") {
                    $retval = 404;
                } else {
                    $retval = $req->response_status;
                }
            }
        }
        return $retval;
    }

}
