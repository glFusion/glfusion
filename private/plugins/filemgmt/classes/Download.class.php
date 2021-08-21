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
use glFusion\Database\Database;
use glFusion\Log\Log;
use glFusion\Cache\Cache;
use Filemgmt\Models\Status;


/**
 * Class for downloadable items.
 * @package filemgmt
 */
class Download
{
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

        $this->isNew = true;

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
        } catch(Throwable $e) {
            // Ignore errors or failed attempts
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
            $this->isNew = false;
            return true;
        }
    }


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
        } catch(Throwable $e) {
            // Ignore errors or failed attempts
        }
        return $stmt->fetchAll(Database::ASSOCIATIVE);
    }


    public static function getNewUploads()
    {
        global $_FM_CONF;

        $interval = 86400 * (int)$_FM_CONF['whatsnewperioddays'];
        return self::getAll("date > UNIX_TIMESTAMP() - $interval ORDER BY date DESC", 15);
    }


    /**
     * Get a category instance.
     * Checks cache first and creates a new object if not found.
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
     * Determine if this category is a new record, or one that was not found
     *
     * @return  integer     1 if new, 0 if existing
     */
    public function isNew()
    {
        return $this->isNew ? 1 : 0;
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


    public function getUrl()
    {
        return $this->url;
    }


    public function setHomepage($hp)
    {
        $this->homepage = $hp;
        return $this;
    }


    public function setVersion($ver)
    {
        $this->version = $ver;
        return $this;
    }


    public function setSize($size)
    {
        $this->size = (int)$size;
        return $this;
    }


    public function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }

    public function getPlatform()
    {
        return $this->platform;
    }


    public function setLogoUrl($url)
    {
        $this->logourl = $url;
        return $this;
    }


    public function setSubmitter($uid)
    {
        $this->submitter = (int)$uid;
        return $this;
    }


    public function getSubmitter()
    {
        return (int)$this->submitter;
    }

    public function setStatus($flag)
    {
        $this->status = (int)$flag;
        return $this;
    }


    public function setDate($ts)
    {
        $this->date = (int)$ts;
        return $this;
    }


    public function setHits($hits)
    {
        $this->hits = (int)$hits;
        return $this;
    }


    public function setRating($rating)
    {
        $this->rating = (float)$rating;
        return $this;
    }


    public function setVotes($votes)
    {
        $this->votes = (int)$votes;
        return $this;
    }


    public function setComments($flag)
    {
        $this->comments = (int)$flag;
        return $this;
    }


    public function getCommentFlag()
    {
        return (int)$this->comments;
    }


    public function setDescription($dscp)
    {
        $this->description = $dscp;
        return $this;
    }


    public static function getTotalItems($sel_id, $status=-1)
    {
        global $_TABLES, $_DB_name;

        if ($status != -1) {
            return DB_count($_TABLES['filemgmt_filedetail']);
        } else {
            return DB_count($_TABLES['filemgmt_filedetail'], 'status', (int)$status);
        }
        return $count;
    }


    /**
     * Save the current values to the database.
     *
     * @param  array   $A      Optional array of values from $_POST
     * @return boolean         True if no errors, False otherwise
     */
    public function save($A = array())
    {
        global $_CONF, $_USER, $_TABLES, $_FM_CONF;
        global $filemgmtFilePermissions;

        if (is_array($A)) {
            $this->setVars($A, false);
        }

        if (defined('DEMO_MODE')) {
            return Status::UPL_NODEMO;
        }

        // Be optimistic
        $retval = Status::UPL_OK;

        $myts = new MyTextSanitizer;
        $eh = new ErrorHandler;

        /*$title = $myts->makeTboxData4Save($_POST['title']);
        $homepage = $myts->makeTboxData4Save($_POST['homepage']);
        $version = $myts->makeTboxData4Save($_POST['version']);
        $description = $myts->makeTareaData4Save($_POST['description']);
        $commentoption = $_POST['comments'];
        $fileurl = COM_applyFilter($_POST['fileurl']);
        $submitter = $_USER['uid'];*/

        $errormsg = "";

        // Check if Title blank
        if ($this->title=="") {
            $eh->show("1104");
        }
        // Check if Description blank
        if ($this->description=="") {
            $eh->show("1105");
        }
        // Check if a file was uploaded
        if ($_FILES['newfile']['size'] == 0  && empty($this->url)) {
            $eh->show("1017");
        }

        if ( !empty($_POST['cid']) ) {
            $cid = $_POST['cid'];
        } else {
            $cid = 0;
            $eh->show("1110");
        }

        /*$filename = ''; //$myts->makeTboxData4Save($_FILES['newfile']['name']);
        $url = ''; //$myts->makeTboxData4Save(rawurlencode($filename));
        $snapfilename = '';// = $myts->makeTboxData4Save($_FILES['newfileshot']['name']);
        $logourl = '';//$myts->makeTboxData4Save(rawurlencode($snapfilename));
         */

        $upload = new UploadDownload();
        $upload->setPerms('0644');
        $upload->setFieldName('newfile');
        $upload->setPath($_FM_CONF['FileStore']);
        $upload->setAllowAnyMimeType(true);     // allow any file type
        $upload->setMaxFileSize(100000000);
        $upload->setMaxDimensions(8192,8192);

        $AddNewFile = false;
        if ($upload->numFiles() > 0) {
            $upload->uploadFiles();
            if ($upload->areErrors()) {
                $errmsg = "Upload Error: " . $upload->printErrors(false);
                Log::write('system',Log::ERROR, $errmsg);
                $eh->show("1106");
            } else {
                $uploaded_file = $upload->getUploadedFiles()[0];
                $this->size = (int)$uploaded_file['size'];
                $filename = $uploaded_file['name'];
                $this->url = rawurlencode($filename);

                $pos = strrpos($filename,'.') + 1;
                $fileExtension = strtolower(substr($filename, $pos));
                if (array_key_exists($fileExtension, $_FM_CONF['extensions_map'])) {
                    if ($_FM_CONF['extensions_map'][$fileExtension] == 'reject' ) {
                        Log::write('system',Log::ERROR, 'AddNewFile - New Upload file is rejected by config rule: ' .$uploadfilename);
                        $eh->show("1109");
                    } else {
                        $fileExtension = $_FM_CONF['extensions_map'][$fileExtension];
                        $pos = strrpos($url,'.') + 1;
                        $this->url = strtolower(substr($this->url, 0,$pos)) . $fileExtension;

                        $pos2 = strrpos($filename,'.') + 1;
                        $filename = substr($filename,0,$pos2) . $fileExtension;
                    }
                }
                $AddNewFile = true;
            }
        }

        if ($upload->numFiles() == 0 && !$upload->areErrors() && !empty($fileurl)) {
            $this-setUrl($fileurl);
            $size = 0;
            $AddNewFile = true;
        }

        $upload = new UploadDownload();
        $upload->setFieldName('newfileshot');
        $upload->setPerms('0644');
        $upload->setPath($_FM_CONF['SnapStore']);
        $upload->setAllowAnyMimeType(false);
        $upload->setAllowedMimeTypes(
            array(
                'image/gif'   => array('.gif'),
                'image/jpeg'  => array('.jpg', '.jpeg'),
                'image/pjpeg' => array('.jpg', '.jpeg'),
                'image/x-png' => array('.png'),
                'image/png'   => array('.png'),
            )
        );

        if (
            isset($_CONF['debug_image_upload']) &&
            $_CONF['debug_image_upload']
        ) {
            $upload->setLogFile($_CONF['path'] . 'logs/error.log');
            $upload->setDebug(true);
        }
        $upload->setMaxDimensions(640,480);
        $upload->setAutomaticResize(true);
        $upload->setMaxFileSize(100000000);
        $upload->uploadFiles();
        if ($upload->numFiles() > 0) {
            if ($upload->areErrors()) {
                $errmsg = "Upload Error: " . $upload->printErrors(false);
                Log::write('system',Log::ERROR, $errmsg);
                $eh->show("1106");
            } else {
                $snapfilename = $myts->makeTboxData4Save($upload->getUploadedFiles()[0]['name']);
                $this->logourl = $myts->makeTboxData4Save(rawurlencode($snapfilename));
                $AddNewFile = true;
            }
        }

        if ($AddNewFile || $this->lid > 0) {
            /*if ($AddNewFile) {
                $chown = @chmod($_FM_CONF['FileStore'].$filename, $_FM_CONF['FilePermissions']);
        }*/
            if (strlen($this->version) > 9) {
                $this->version = substr($this->version,0,8);
            }

            if ($this->lid == 0) {
                $sql1 = "INSERT INTO {$_TABLES['filemgmt_filedetail']} SET
                    date = UNIX_TIMESTAMP(), ";
                $sql3 = '';
                // Determine write access to category for new uploads
                $Cat = new Category($this->cid);
                if ($Cat->hasWriteAccess()) {
                    $this->status = 1;
                } else {
                    $this->status = 0;
                }
            } else {
                $sql1 = "UPDATE {$_TABLES['filemgmt_filedetail']} SET ";
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
            PLG_itemSaved($this->lid, 'filemgmt');
            $c = Cache::getInstance()->deleteItemsByTag('whatsnew');
            if (isset($duplicatefile) && $duplicatefile) {
                $retval = Status::UPL_DUPFILE;
            } elseif (isset($duplicatesnap) && $duplicatesnap) {
                $retval = Status::UPL_DUPSNAP;
            }
        } else {
            // Could not upload the file
            $retval = Status::UPL_ERROR;
        }
        return $retval;

    }   // function Save()


    /**
     * Propagate permissions to sub-categories.
     *
     * @param   integer $grp_id     Group ID to allow permission
     */
    private function _propagatePerms($grp_id)
    {
        global $_TABLES;

        if ($grp_id == $this->grp_access) return;   // nothing to do

        $c = self::getTree($this->cid);
        $upd_cats = array();
        foreach ($c as $cat) {
            if ($cat->cid == $this->cid) continue; // already saved
            $upd_cats[] = $cat->cid;
        }
        if (!empty($upd_cats)) {
            $upd_cats = implode(',', $upd_cats);
            $sql = "UPDATE {$_TABLES['filemgmt_cat']}
                    SET grp_access = {$this->grp_access}
                    WHERE cid IN ($upd_cats)";
            DB_query($sql);
        }
    }


    /**
     *  Delete the current category record from the database.
     */
    public function delete()
    {
        global $_TABLES, $_FM_CONF;

        $tmpfile = $_FM_CONF['FileStore'] . $this->url;
        $tmpsnap  = $_FM_CONF['SnapStore'] . $this->logourl;

        DB_delete($_TABLES['filemgmt_filedetail'], 'lid', $lid);
        DB_delete($_TABLES['filemgmt_filedesc'], 'lid', $lid);
        DB_delete($_TABLES['filemgmt_votedata'], 'lid', $lid);
        DB_delete($_TABLES['filemgmt_brokenlinks'], 'lid', $lid);
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
                @unlink($_FM_CONF['SnapStore'] . $this->logourl);
            }
        }

        PLG_itemDeleted($lid,'filemgmt');
        $c = Cache::getInstance()->deleteItemsByTag('whatsnew');
        return true;
    }


    /**
     *  Creates the edit form.
     *
     *  @param  integer $id Optional ID, current record used if zero
     *  @return string      HTML for edit form
     */
    public function edit()
    {
        global $_CONF,$_FM_CONF, $_TABLES,$_USER, $_DB_name;

        $display = '';
        $totalvotes = '';
        $myts = new MyTextSanitizer;
        $mytree = new XoopsTree($_DB_name,$_TABLES['filemgmt_cat'],"cid","pid");
        $eh = new ErrorHandler;

        $T = new \Template($_CONF['path'] . 'plugins/filemgmt/templates/admin');
        $T->set_file(array(
            'form' => 'mod_file.thtml',
            'ratings' => 'vote_data.thtml',
        ));
        $T->set_var(array(
            'lang_file_id' => _MD_FILEID,
            'lang_filetitle' => _MD_FILETITLE,
            'lang_dl_filename' => _MD_DLFILENAME,
            'lang_replace_filename' => _MD_REPLFILENAME,
            'lang_homepage' => _MD_HOMEPAGEC,
            'lang_filesize' => _MD_FILESIZEC,
            'lang_bytes' => _MD_BYTES,
            'lang_version' => _MD_VERSIONC,
            'lang_description' => _MD_DESCRIPTIONC,
            'lang_category' => _MD_CATEGORYC,
            'lang_screenshot' => _MD_SHOTIMAGE,
            'lang_comments' => _MD_COMMENTOPTION,
            'lang_yes' => _MD_YES,
            'lang_no' => _MD_NO,
            'lang_owner' => _MD_OWNER,
            'lang_silent_edit' => _MD_SILENTEDIT,
            'lang_submit' => _MD_SUBMIT,
            'lang_delete' => _MD_DELETE,
            'lang_cancel' => _MD_CANCEL,
            'lang_hits'     => _MD_HITSC,
        ));

        $pathstring = "<a href=\"{$_FM_CONF['url']}/index.php\">"._MD_MAIN."</a>&nbsp;:&nbsp;";
        $nicepath = $mytree->getNicePathFromId($this->cid, "title", "{$_FM_CONF['url']}/viewcat.php");
        $pathstring .= $nicepath;
        if ($this->lid > 0) {
            $hdr_title = $this->title;
        } else {
            $hdr_title = 'New File';
        }
        $pathstring .= "<a href=\"{$_FM_CONF['url']}/index.php?id={$this->lid}\">{$hdr_title}</a>";

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
            'category_select' => $mytree->makeMySelBox("title", "title", $this->cid,0,"cid"),
            'owner_select' =>  COM_buildOwnerList('submitter', $this->submitter),
            'hits' => $myts->makeTboxData4Edit($this->hits),
            'can_delete' => $this->lid > 0,
        ));

        if (!empty($this->logourl) AND file_exists($_FM_CONF['SnapStore'].$this->logourl)) {
            $T->set_var('thumbnail', $_FM_CONF['FileSnapURL'].$this->logourl);
        } else {
            $T->unset_var('thumbnail');
        }
        if ($this->comments) {
            $T->set_var('comments_yes_checked',' checked="checked" ');
        } else {
            $T->set_var('comments_no_checked',' checked="checked" ');
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
     * Toogles a boolean value to the opposite of the current value.
     *
     * @param   integer $oldvalue   Old value to change
     * @param   string  $varname    Field name to change
     * @param   integer $id         ID number of element to modify
     * @return  integer             New value, or old value upon failure
     */
    protected static function do_toggle($oldvalue, $varname, $id)
    {
        $newval = self::Toggle($oldvalue, $varname, $id);
        return $newval;
    }


    /**
     * Sets the "enabled" field to the specified value.
     *
     * @param   integer $oldvalue   Original value to be changed
     * @param   integer $id         ID number of element to modify
     * @return  integer         New value, or old value upon failure
     */
    public static function toggleEnabled($oldvalue, $id)
    {
        return self::do_toggle($oldvalue, 'enabled', $id);
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

        if ($cid == 1) {
            return true;
        }

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
    public function AddError($msg)
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
     * Determine if the current user has read access to this file.
     * First checks that this is a valid record.
     *
     * @param   array|null  $groups     Array of groups, needed for sitemap
     * @return  boolean     True if user has access, False if not
     */
    public function canRead($groups = NULL)
    {
        return $this->lid > 0 && Category::getInstance($this->cid)->canRead($groups);
    }


    /**
     * Get the URL to the category image.
     * Returns an empty string if no image defined or found.
     *
     * @return  string  URL of image, empty string if file not found
     */
    public function getImage()
    {
        return Images\Category::getUrl($this->image);
    }


    /**
     * Create the breadcrumb display, with links.
     * Creating a static breadcrumb field in the category record won't
     * work because of the group access control. If a category is
     * encountered that the current user can't access, it is simply
     * skipped.
     *
     * @param   integer $id ID of current category
     * @return  string      Location string ready for display
     */
    public function XXBreadcrumbs()
    {
        $T = new Template;
        $T->set_file('cat_bc_tpl', 'cat_bc.thtml');
        $T->set_var('pi_url', SHOP_URL . '/index.php');
        $breadcrumbs = array(
            COM_createLink(
                $LANG_SHOP['home'],
                SHOP_URL
            )
        );
        if ($this->cid > 0 && !$this->isRoot()) {
            // A specific subcategory is being viewed
            $cats = $this->getPath();
            foreach ($cats as $cat) {
                // Root category already shown in top header
                if ($cat->isRoot()) continue;
                // Don't show a link if the user can't access it.
                if (!$cat->hasAccess()) continue;
                $breadcrumbs[] = COM_createLink(
                    $cat->title,
                    SHOP_URL . '/index.php?category=' . (int)$cat->cid
                );
            }
        }
        $T->set_block('cat_bc_tpl', 'cat_bc', 'bc');
        foreach ($breadcrumbs as $bc_url) {
            $T->set_var('bc_url', $bc_url);
            $T->parse('bc', 'cat_bc', true);
        }
        $children = $this->getChildren();
        if (!empty($children)) {
            $T->set_var('bc_form', true);
            $T->set_block('cat_bc_tpl', 'cat_sel', 'sel');
            foreach ($children as $c) {
                if (!$c->hasAccess()) continue;
                $T->set_var(array(
                    'cid'   => $c->cid,
                    'title' => $c->title,
                ) );
                $T->parse('sel', 'cat_sel', true);
            }
        }
        $T->parse('output', 'cat_bc_tpl');
        $retval = $T->finish($T->get_var('output'));
        return $retval;
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


    public function approve()
    {
        global $_TABLES;

        DB_query(
            "UPDATE {$_TABLES['filemgmt_filedetail']}
            SET status = 1
            WHERE lid = {$this->lid}"
        );
        return $this;
    }


    /**
     * Update the download counter.
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
    }


    /**
     * Category Admin List View.
     *
     * @param   integer $cid     Optional category ID to limit listing
     * @return  string      HTML for the category list.
     */
    public static function adminList($cid=0, $status = -1)
    {
        global $_FM_CONF, $LANG_FM02, $_TABLES, $LANG_ADMIN;

        USES_lib_admin();

        $cid = (int)$cid;
        $selcat = '';
        $display = '';

        $sql = "SELECT * FROM {$_TABLES['filemgmt_cat']} WHERE pid=0 ORDER BY title ASC";
        $result = DB_query($sql);
        while (($C = DB_fetchArray($result)) != NULL ) {
            $selcat .= '<option value="'.$C['cid'].'"';
            if ( $C['cid'] == $cid) {
                $selcat .= ' selected="selected"';
            }
            $selcat .= '>';
            $selcat .= $C['title'].'</option>';
            $selcat .= Category::getChildOptions( $C['cid'],1,$cid);

        }
        $allcat = '<option value="0">'._MD_ALL.'</option>';

        $filter = _MD_CATEGORYC
            . ' <select name="cat" style="width: 125px" onchange="this.form.submit()">'
            . $allcat . $selcat . '</select>';

        $header_arr = array(
            array(
                'text' => $LANG_FM02['edit'],
                'field' => 'edit',
                'sort' => false,
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
                'text' => $LANG_FM02['size'],
                'field' => 'size',
                'sort' => true,
                'align'=>'right',
            ),
            array(
                'text' => $LANG_FM02['date'],
                'field' => 'date',
                'sort' => true,
            ),
        );
        $text_arr = array(
            'has_extras' => true,
            'form_url'   => $_FM_CONF['admin_url'] . '/index.php?cat='.(int) $cid,
            'help_url'   => ''
        );

        $defsort_arr = array(
            'field'     => 'date',
            'direction' => 'DESC',
        );

        if ( $cid != 0 ) {
            $where = " c.cid=".(int) $current_cat . " ";
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
            'New Item',
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
            $text_arr, $query_arr, $defsort_arr,$filter
        );
        $display .= COM_endBlock();
        return $display;
    }


    /**
     * Get the download history report for this file.
     *
     * @return  string      HTML for history report
     */
    public function getDownloadHistory()
    {
        global $_TABLES, $_FM_CONF;

        USES_lib_admin();

        $sql = "SELECT fh.date, fh.uid, fh.remote_ip, u.username
            FROM {$_TABLES['filemgmt_history']} fh
            LEFT JOIN {$_TABLES['users']} u
            ON u.uid = fh.uid
            WHERE fh.lid = {$this->lid}";
        $header_arr = array(
            array(
                'text'  => 'Date',
                'field' => 'date',
                'sort'  => true,
            ),
            array(
                'text'  => 'User',
                'field' => 'username',
                'sort'  => false,
            ),
            array(
                'text'  => 'Remote IP',
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
            'table' => 'shop.products',
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

        $query_arr = array(
            'table' => 'shop.products',
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
        global $_FM_CONF, $_USER, $_TABLES, $LANG_ADMIN;

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
                $_FM_CONF['admin_url'] . "/index.php?modDownload={$A['lid']}"
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


    /**
     * Create the listing display for this file.
     *
     * @return  string      HTML for file record
     */
    public function showListingRecord()
    {
        global $_CONF, $_FM_CONF, $_TABLES, $LANG01, $LANG_FILEMGMT;

        static $mytree = NULL;
        $dt = new \Date($this->date);

        if ($mytree === NULL) {
            $mytree = new XoopsTree('',$_TABLES['filemgmt_cat'],"cid","pid");
        }
        $path = $mytree->getPathFromId($this->cid, "title");
        $path = substr($path, 1);
        $path = str_replace("/"," <img src='" .$_FM_CONF['url'] ."/images/arrow.gif' alt=''> ",$path);

        $T = new \Template($_CONF['path'] . 'plugins/filemgmt/templates');
        $T->set_file('record', 'filelisting_record.thtml');
        $T->set_var(array(
            'lid'       => $this->lid,
            'dtitle'    => $this->title,
            'hits'      => $this->hits,
            'file_description' => $this->description,
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
            'LANG_VERSION' =>  _MD_VERSION,
            'LANG_SUBMITDATE' => _MD_SUBMITDATE,
            'datetime'  => $dt->format('M.d.Y', true),
            'version'   => $this->version,
            'LANG_RATING' => $_FM_CONF['enable_rating'] ? _MD_RATINGC : '',
            'have_dlreport' => $this->hits > 0 && SEC_hasRights('filemgmt.edit'),
            'download_times' => sprintf(_MD_DLTIMES,$this->hits),
            'download_count' => $this->hits,
            'LANG_FILESIZE' => _MD_FILESIZE,
            'LANG_DOWNLOAD' => _MD_DOWNLOAD,
            'LANG_FILELINK' => _MD_FILELINK,
            'LANG_RATETHISFILE' => _MD_RATETHISFILE,
            'LANG_REPORTBROKEN' => _MD_REPORTBROKEN,
            'LANG_HOMEPAGE' => _MD_HOMEPAGE,
            'LANG_CATEGORY' => _MD_CATEGORYC,
            'category_path' => $path,
            'submitter_name' => COM_getDisplayName($this->submitter),
            'submitter_link' => $this->submitter > 1,
            'LANG_EDIT' => plugin_ismoderator_filemgmt() ? _MD_EDIT : '',
            'LANG_CLICK2SEE' => _MD_CLICK2SEE,
            'lang_pop'  => _MD_POP,
            'lang_new'  => _MD_NEW,
            'lang_new_title' => $LANG_FILEMGMT['newly_uploaded'],
            'lang_popular' => _MD_POPULAR,
        ) );

        // Check if this is a local or remotely-hosted file.
        $parts = parse_url($this->url);
        if (!isset($parts['scheme'])) {
            // Local file, check that the file exists
            $T->set_var('file_size',self::prettySize($this->size));
            $fullurl = $_FM_CONF['FileStore'] . rawurldecode($this->url);
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
            $mysize = sprintf('%01.2f', $size/1048573) . " MB";
        }
        elseif ($size >= 1024) {    // > 1 KB
            $mysize = sprintf('%01.2f' , $size/1024) . " KB";
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
                if (@in_array($lid,$FM_ratedIds)) {
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

}
