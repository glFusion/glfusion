<?php
/**
* glFusion CMS
*
* glFusion base Article Class
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2018-2019 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*
*/

namespace glFusion\Article;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

use \glFusion\Database\Database;
use \glFusion\Cache\Cache;
use \glFusion\Formatter;
use \glFusion\Social\Social;
use \glFusion\Log\Log;
use \glFusion\Admin\AdminAction;

class Article
{
    public const STORY_INVALID_SID       = -6;
    public const STORY_PERMISSION_DENIED = -2;
    public const STORY_EDIT_DENIED       = -3;
    public const STORY_LOADED_OK         =  1;
    public const STORY_ARCHIVE_ON_EXPIRE = 10;
    public const STORY_DELETE_ON_EXPIRE  = 11;

    // database elements
    // Article data elements stored in the DB

    // unique ID automatically assigned by DB
    protected $id = 0;

    // Story ID - the descriptive story id
    protected $sid = '';

    // user id of the user who created the story
    protected $uid = 1;

    // if 1 - this story is treated as a draft
    protected $draft_flag = 0;

    // primary Topic ID
    protected $tid;

    // alternate topic id
    protected $alternate_tid;

    // relative path to story image
    protected $story_image = '';

    // relative path to story video
    protected $story_video = '';

    // if 1 - auto play story video / 0 don't
    protected $sv_autoplay = 0;

    // story publish date
    protected $date = '';

    // story title
    protected $title = '';

    // story sub title
    protected $subtitle = '';

    //story intro text
    protected $introtext = '';

    // story body text
    protected $bodytext = '';

    // number of views of the story
    protected $hits = 0;

    // story rating
    protected $rating = 0;

    // number of votes on story
    protected $votes = 0;

    // number of times story has been eamiled
    protected $numemails = 0;

    // total number of comments for this story
    protected $comments = 0;

    protected $comment_expire = NULL;
    protected $cmt_close_flag = 0;      // not part of the DB but we need to track
                                        // it as a 'virtual' field.

    // total number of trackbacks for the story
    protected $trackbacks = 0;

    // parsed related into - basically links embedded in story
    protected $related = '';

    // whether story is featured (1 = featured, 0 = not)
    protected $featured = 0;

    // whether to show topic icon in story display
    protected $show_topic_icon = 1;

    // comment code
    //  -1 Disabled
    //  0 = enabled
    //  1 = closed
    protected $commentcode = 0;

    // trackback code
    //  -1 = disabled
    //   0 = enabled
    protected $trackbackcode = 0;

    // statuc code
    //  0 = normal
    //  1 = refreshing
    // 10 = archive
    protected $statuscode = 0;

    // The date the story should auto expire
    //  this can trigger either move to archive topic or delete
    protected $expire = NULL;

    // attribution info
    protected $attribution_url = '';
    protected $attribution_name = '';
    protected $attribution_author = '';

    // post mode  - either 'html' or 'text'
    protected $postmode = 'html';

    // advanced editor mode =
    //  true = use WYSIWYG
    //  false = use textbox editor
    protected $advanced_editor_mode = true;

    // Show on front page
    //  0 = no
    //  1 = yes
    //  2 = yes until frontpage_date
    protected $frontpage = 0;

    // date to stop showing on front page
    protected $frontpage_date = NULL;

    protected $owner_id = 1;
    protected $group_id;
    protected $perm_owner;
    protected $perm_group;
    protected $perm_members;
    protected $perm_anon;

    protected $associatedImages = null;

    // Other data elements needed to manage / handle article management

    // stores original SID - primarily used for story submissions
    protected $original_sid;

    // if coming from submission queue
    protected $moderate = 0;

    // if a direct save from submit.php
    protected $submission = false;

    protected $topic;
    protected $altTopic;
    protected $topicImage;
    protected $topicDescription;
    protected $altTopicDescription;

    // user specific information on this story

    protected $fullname = 'Anonymous';
    protected $username = 'Anonymous';
    protected $user_about = '';
    protected $user_photo = 'default.jpg';
    protected $ownerFullName;
    protected $authorFullName;
    protected $ownerUsername;
    protected $authorUsername;

    // any save or processing errors
    protected $errors = array();

    /*
    * formatter object for this story
    */
    protected $format;

    /*
     * sanitizer object for this story
     */
    protected $filter;

    protected $topic_access;
    protected $topic_perm_owner;
    protected $topic_perm_group;
    protected $topic_perm_members;
    protected $topic_perm_anon;

    /*
     * private $template - story template
     */
    private $template = NULL;

    /*
     * private $rateIds
     *   all stories the current user has rated
     */
    private $rateIds = NULL;

    public function __construct()
    {
        global $_CONF, $_USER, $_TABLES, $_GROUPS;

        $db = Database::getInstance();

        // initialize date class for all article dates
        $this->date             = new \Date('now',$_USER['tzid']);
        $this->frontpage_date   = new \Date(time() + 2592000,$_USER['tzid']);
        $this->comment_expire   = new \Date(time() + 2592000,$_USER['tzid']);
        $this->expire           = new \Date(time() + 2592000,$_USER['tzid']);

        // initialize defaults

        $this->perm_owner   = $_CONF['default_permissions_story'][0];
        $this->perm_group   = $_CONF['default_permissions_story'][1];
        $this->perm_members = $_CONF['default_permissions_story'][2];
        $this->perm_anon    = $_CONF['default_permissions_story'][3];
        $this->draft_flag   = $_CONF['draft_flag'];
        $this->frontpage    = $_CONF['frontpage'];
        $this->advanced_editor_mode = $_CONF['default_story_editor'];
        $this->show_topic_icon = $_CONF['show_topic_icon'];

        if (isset($_GROUPS['Story Admin'])) {
            $this->group_id = $_GROUPS['Story Admin'];
        } else {
            $this->group_id = 1;
        }

        $this->tid = $db->conn->fetchColumn(
            "SELECT tid FROM `{$_TABLES['topics']}` WHERE is_default = 1 " . $db->getPermSQL('AND'),
            array(),
            0,
            array(),
            new \Doctrine\DBAL\Cache\QueryCacheProfile(86400, 'defaulttopic')
        );

        $this->template = new \Template($_CONF['path_layout']);

        // setup formatter class for this story

        $this->format = new Formatter();
        $this->format->setNamespace('glfusion');
        $this->format->setAction('story');
        $this->format->setAllowedHTML($_CONF['htmlfilter_story']);
        $this->format->setParseAutoTags(true);
        $this->format->setProcessBBCode(false);
        $this->format->setCensor(true);
        $this->format->setProcessSmilies(true);
        $this->format->setConvertPre(true);
        $this->format->addFilter(FILTER_POST,array($this,'processImageTags'));

        // setup filter / sanitization
        $this->filter = new \sanitizer();
        $this->filter->setPostmode('text'); // default to text - can override later

        $allowedElements = $this->filter->makeAllowedElements($_CONF['htmlfilter_story']);
        $this->filter->setAllowedElements($allowedElements);
        $this->filter->setCensorData(true);
        $this->filter->setNamespace('glfusion','story');
    }


    public function setVars($storyVars = array())
    {
        foreach ($storyVars AS $varname => $value) {
            $this->set($varname,$value,$storyVars);
        }
    }


    /*
     * Removes an article from the database, removing
     * all associated items such as comments, etc.
     * Static to allow calling from cron
     *
     * @param   string  $sid   the story ID
     * @param   bool    $batch Denotes if system processing such as cron.php
     *                         this releives some permission checks
     *
     */
    public static function delete($sid, $system = false, $token = '')
    {
        global $_CONF, $_TABLES, $_USER, $LANG_ADM_ACTIONS;

        if ($system != true) {

            $db = Database::getInstance();

            $sql = "SELECT tid,owner_id,group_id,perm_owner,perm_group,perm_members,perm_anon
                     FROM `{$_TABLES['stories']}` WHERE sid = ?";

            $row = $db->conn->fetchAssoc(
                            $sql,
                            array($sid),
                            array(Database::STRING)
            );
            if ($row !== false && $row !== null) {
                $access = SEC_hasAccess ($row['owner_id'],
                                         $row['group_id'],
                                         $row['perm_owner'],
                                         $row['perm_group'],
                                         $row['perm_members'],
                                         $row['perm_anon']
                          );

                $access = min ($access, SEC_hasTopicAccess ($row['tid']));
                if ($access < SEC_ACCESS_RW) {
                    Log::write('system',Log::WARNING,sprintf("User %s tried to delete story ID %s without sufficient permissions.",$_USER['uid'],$sid));
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // check the token
            if (SEC_checkTokenGeneral($token,'cron_'.md5($sid)) === false) {
                Log::write('system',Log::WARNING,'Invalid token passed to cron.php on story delete');
                return false;
            }
        }

        self::deleteImages($sid);

        $db->conn->delete(
                $_TABLES['comments'],
                array('sid' => $sid, 'type' => 'article'),
                array(Database::STRING, Database::STRING)
        );
        $db->conn->delete(
                $_TABLES['stories'],
                array('sid' => $sid),
                array(Database::STRING)
        );
        $db->conn->delete(
                $_TABLES['trackback'],
                array('sid' => $sid, 'type' => 'article'),
                array(Database::STRING, Database::STRING)
        );

        PLG_itemDeleted($sid, 'article');

        if ($system) {
            AdminAction::write('system','article_delete',
                sprintf($LANG_ADM_ACTIONS['article_delete_sys'],$sid));
        } else {
            AdminAction::write('system','article_delete',
                sprintf($LANG_ADM_ACTIONS['article_delete'],$sid));

        }

        return true;
    }

    private static function deleteImages ($sid)
    {
        global $_TABLES;

        $db = Database::getInstance();

        $stmt = $db->conn->executeQuery(
                    "SELECT ai_filename FROM `{$_TABLES['article_images']}` WHERE ai_sid = ?",
                    array($sid),
                    array(Database::STRING)
        );
        while ($row = $stmt->fetch(Database::ASSOCIATIVE)) {
            self::deleteImage ($row['ai_filename']);
        }
        $db->conn->delete(
            $_TABLES['article_images'],
            array('ai_sid' => $sid),
            array(Database::STRING)
        );
    }

    private static function deleteImage($image)
    {
        global $_CONF;

        if (empty ($image)) {
            return;
        }

        $filename = $_CONF['path_images'] . 'articles/' . $image;
        if (!@unlink ($filename)) {
            Log::write('system',Log::WARNING,'Unable to remove the following image from the article: ' . $filename);
        }

        // remove unscaled image, if it exists
        $lFilename_large = substr_replace ($image, '_original.',strrpos ($image, '.'), 1);
        $lFilename_large_complete = $_CONF['path_images'] . 'articles/'. $lFilename_large;
        if (file_exists ($lFilename_large_complete)) {
            if (!@unlink ($lFilename_large_complete)) {
                Log::write('system',Log::WARNING, 'Unable to remove the following image from the article: ' . $lFilename_large_complete);
            }
        }
    }

    /*
     * Inserts a new story to the database
     */
    public function save()
    {
        global $_CONF, $_TABLES, $_USER, $LANG24, $MESSAGE;

        $db = Database::getInstance();

        $retval = true;

        // reset errors
        $this->errors = [];

        // field validation

        if (empty($this->introtext) || empty($this->title) || empty($this->sid)) {
            $this->errors[] = $LANG24[31];
            return false;
        }

        // verify user has RW access to the topic

        $topicPerm = \Topic::Access($this->get('tid'));
        if ($topicPerm != SEC_ACCESS_RW) {
            $this->errors[] = $MESSAGE[30];
            return false;
        }

        // if not a user submission - ensure story.edit permission
        if ($this->submission !== true) {
            if (!SEC_hasRights('story.edit')) {
                $this->errors[] = $MESSAGE[30];
                return false;
            }
        }

        $archiveTID = \TOPIC::archiveID();

        if ($archiveTID == $this->tid) {
            $this->featured = 0;
            $this->frontpage = 0;
            $this->statuscode = $this::STORY_ARCHIVE_ON_EXPIRE;
        }

        if ( $this->featured != 1 ) {
            $this->featured = 0;
        }
        if ( $this->statuscode == '' ) {
            $this->statuscode = 0;
        }
        if ( $this->owner_id == '' || $this->owner_id == 1) {
            $this->owner_id = $_USER['uid'];
        }

        if ($this->cmt_close_flag === 0) {
            $comment_expire = null;
        } else {
            $comment_expire = $this->comment_expire->toMySQL(false);
        }
        if ($this->frontpage !== 2) {
            $frontpage_date = null;
        } else {
            $frontpage_date = $this->frontpage_date->toMySQL(false);
        }
        if ($this->statuscode == 0) {
            $expire_date = null;
        } else {
            $expire_date = $this->expire->toMySQL(false);
        }
        // Begin Transaction - will roll back all changes if
        // any errors along the way...

        $db->conn->beginTransaction();

        try {
            /* if a featured, non-draft, that goes live straight away, unfeature
             * other stories in same topic:
             */
            if ($this->featured == 1) {
                // there can only be one non-draft featured story
                if ($this->draft_flag == 0 && $this->date->toUnix() <= time()) {
                    if ( $this->frontpage == 1 || $this->frontpage == 2 ) {
                        // un-feature any featured frontpage story
                        $db->conn->executeUpdate(
                            "UPDATE `{$_TABLES['stories']}` SET featured = 0
                              WHERE featured > 0 AND draft_flag = 0
                              AND (frontpage = 1 OR ( frontpage = 2 AND frontpage_date >= ? ) ) AND date <= ?",
                            array(
                                $_CONF['_now']->toMySQL(false),
                                $_CONF['_now']->toMySQL(false)
                            ),
                            array(
                                Database::STRING,
                                Database::STRING
                            )
                        );
                    }

                    // un-feature any featured story in the same topic

                    $db->conn->executeUpdate(
                        "UPDATE `{$_TABLES['stories']}` SET featured = 0
                          WHERE featured > 0 AND draft_flag = 0 AND tid = ?
                          AND date <= ?",
                        array($this->tid,$_CONF['_now']->toMySQL(false)),
                        array(Database::STRING,Database::STRING)
                    );
                }
            }

            // existing story - check the SID to see if it has changed
            if ($this->id !== 0) {
                // check if the SID has changed..

                $originalSID = $db->getItem(
                                    $_TABLES['stories'],
                                    'sid',
                                    array('id' => $this->id)
                                );

                if ($originalSID !== false && $originalSID !== $this->sid) {

                    // if the SID has changed - update all affected items

                    // comments
                    $db->conn->update(
                            $_TABLES['comments'],
                            array(
                                'sid' => $this->sid
                            ),
                            array(
                                'type' => 'article',
                                'sid'  => $originalSID
                            ),
                            array(
                                Database::STRING,
                                Database::STRING,
                                Database::STRING
                            )
                    );
                    $db->conn->update(
                            $_TABLES['article_images'],
                            array(
                                'ai_sid' => $this->sid
                            ),
                            array(
                                'ai_sid'  => $originalSID
                            ),
                            array(
                                Database::STRING,
                                Database::STRING,
                            )
                    );

                    // trackbacks
                    $db->conn->update(
                            $_TABLES['trackback'],
                            array(
                                'sid' => $this->sid
                            ),
                            array(
                                'type' => 'article',
                                'sid'  => $originalSID
                            ),
                            array(
                                Database::STRING,
                                Database::STRING,
                                Database::STRING
                            )
                    );
                    // ratings
                    $db->conn->update(
                            $_TABLES['rating'],
                            array(
                                'item_id' => $this->sid
                            ),
                            array(
                                'type' => 'article',
                                'item_id'  => $originalSID
                            ),
                            array(
                                Database::STRING,
                                Database::STRING,
                                Database::STRING
                            )
                    );

                    $db->conn->update(
                            $_TABLES['rating_votes'],
                            array(
                                'item_id' => $this->sid
                            ),
                            array(
                                'type' => 'article',
                                'item_id'  => $originalSID
                            ),
                            array(
                                Database::STRING,
                                Database::STRING,
                                Database::STRING
                            )
                    );

                    $c->deleteItemsByTag('story_'.$originalSID);
                }
            }

            // save to the DB

            $dataToSave = array(
                'sid'           => $this->sid,
                'uid'           => $this->uid,
                'draft_flag'    => $this->draft_flag,
                'tid'           => $this->tid,
                'alternate_tid' => $this->alternate_tid,
                'story_image'   => $this->story_image,
                'story_video'   => $this->story_video,
                'sv_autoplay'   => $this->sv_autoplay,
                'date'          => $this->date->toMySQL(false),
                'title'         => $this->title,
                'subtitle'      => $this->subtitle,
                'introtext'     => $this->introtext,
                'bodytext'      => $this->bodytext,
                'hits'          => $this->hits,
                'rating'        => $this->rating,
                'votes'         => $this->votes,
                'numemails'     => $this->numemails,
                'comments'      => $this->comments,
                'comment_expire'=> $comment_expire, // use local version
                'trackbacks'    => $this->trackbacks,
                'related'       => $this->related,
                'featured'      => $this->featured,
                'show_topic_icon' => $this->show_topic_icon,
                'commentcode'   => $this->commentcode,
                'trackbackcode' => $this->trackbackcode,
                'statuscode'    => $this->statuscode,
                'expire'        => $expire_date,
                'attribution_url' => $this->attribution_url,
                'attribution_name' => $this->attribution_name,
                'attribution_author' => $this->attribution_author,
                'postmode'      => $this->postmode,
                'advanced_editor_mode' => $this->advanced_editor_mode,
                'frontpage'     => $this->frontpage,
                'frontpage_date' => $frontpage_date, // use local version
                'owner_id'      => $this->owner_id,
                'group_id'      => $this->group_id,
                'perm_owner'    => $this->perm_owner,
                'perm_group'    => $this->perm_group,
                'perm_members'  => $this->perm_members,
                'perm_anon'     => $this->perm_anon
           );
           $dataToSaveTypes = array(
                Database::STRING,       // sid
                Database::INTEGER,      // uid
                Database::STRING,       // draft_flag
                Database::STRING,       // tid
                Database::STRING,       // alternate_tid
                Database::STRING,       // story_image
                Database::STRING,       // story_video
                Database::STRING,       // sv_autoplay
                Database::STRING,       // date
                Database::STRING,       // title
                Database::STRING,       // subtitle
                Database::STRING,       // introtext
                Database::STRING,       // bodytext
                Database::INTEGER,      // hits
                Database::INTEGER,      // rating
                Database::INTEGER,      // votes,
                Database::INTEGER,      // numemails
                Database::INTEGER,      // comments
                Database::STRING,       // comment_expire
                Database::INTEGER,      // trackbacks
                Database::STRING,       // related
                Database::INTEGER,      // featured
                Database::INTEGER,      // show_topic_icon
                Database::INTEGER,      // commentcode
                Database::INTEGER,      // trackbackcode
                Database::INTEGER,      // statuscode
                Database::STRING,       // expire
                Database::STRING,       // attribution_url
                Database::STRING,       // attribution_name
                Database::STRING,       // attribution_author
                Database::STRING,       // postmode
                Database::INTEGER,      // advanced_editor_mode
                Database::INTEGER,      // frontpage
                Database::STRING,       // frontpage_date
                Database::INTEGER,      // owner_id
                Database::INTEGER,      // group_id
                Database::INTEGER,      // perm_owner
                Database::INTEGER,      // perm_group
                Database::INTEGER,      // perm_members
                Database::INTEGER,      // perm_anon
            );

            if ((int) $this->id == 0) {
                $db->conn->insert(
                    $_TABLES['stories'],
                    $dataToSave,
                    $dataToSaveTypes
                );
            } else {
                $dataToSaveTypes[] = Database::INTEGER; // where clause ID
                $db->conn->update(
                    $_TABLES['stories'],
                    $dataToSave,
                    array(
                        'id'    => (int) $this->id
                    ),
                    $dataToSaveTypes
                );
            }

            // done - commit the changes
            $ret = $db->conn->commit();

        } catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            // duplicate sid
            $db->conn->rollback();
            $this->errors[] = $LANG24[24];
            Log::write('system',Log::ERROR,$e->getMessage());
            $retval = false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            // general error
            $db->conn->rollback();
            $this->errors[] = $LANG24[25];
            Log::write('system',Log::ERROR,$e->getMessage());
            $retval = false;
        }

        if ($retval !== false) {
            $rc = $this->saveImages();

            if ( $rc !== true ) {
                return false;
            }
        }
        return $retval;
    }


    /*
     * Inserts a new story to the database
     */
    public function saveSubmission()
    {
        global $_CONF, $_TABLES, $_USER, $LANG24;

        $db = Database::getInstance();

        $retval = true;

        $this->errors = [];

// we may not need this
        $topicInfo = $db->conn->fetchAssoc(
                        "SELECT group_id,perm_owner,perm_group,perm_members,perm_anon
                            FROM `{$_TABLES['topics']}` WHERE tid=? " . $db->getTopicSQL('AND'),
                        array($this->tid),
                        array(Database::STRING)
        );

// check this and see if we have a config setting to control this
        if ($topicInfo === false || $topicInfo === null) {
            $this->errors[] = 'Not topic perms';
            return false;
        }

        $this->sid = COM_makeSid();

        // field validation
        if (empty($this->introtext) || empty($this->title) || empty($this->sid)) {
            $this->errors[] = $LANG24[31];
            return false;
        }

// start here as save / preview

        $archiveTID = \TOPIC::archiveID();

        if ($archiveTID == $this->tid) {
            $this->featured = 0;
            $this->frontpage = 0;
            $this->statuscode = $this::STORY_ARCHIVE_ON_EXPIRE;
        }

        if (COM_isAnonUser()) {
            $this->uid = 1;
        } else {
            $this->uid = $_USER['uid'];
        }
        if ( $this->featured != 1 ) {
            $this->featured = 0;
        }
        if ( $this->statuscode == '' ) {
            $this->statuscode = 0;
        }
        if ( $this->owner_id == '' ) {
            $this->owner_id = $_USER['uid'];
        }

        if (($_CONF['storysubmission'] == 1) && !SEC_hasRights('story.submit')) {

            // start the transaction processing here
            $db->conn->beginTransaction();

            try {

                $dataToSave = array(
                    'sid'           => $this->sid,
                    'uid'           => $this->uid,
                    'tid'           => $this->tid,
                    'date'          => $this->date->toMySQL(false),
                    'title'         => $this->title,
                    'introtext'     => $this->introtext,
                    'bodytext'      => $this->bodytext,
                    'postmode'      => $this->postmode,
                );

               $dataToSaveTypes = array(
                    Database::STRING,       // sid
                    Database::INTEGER,      // uid
                    Database::STRING,       // tid
                    Database::STRING,       // date
                    Database::STRING,       // title
                    Database::STRING,       // introtext
                    Database::STRING,       // bodytext
                    Database::STRING,       // postmode
                );

                $db->conn->insert(
                    $_TABLES['storysubmission'],
                    $dataToSave,
                    $dataToSaveTypes
                );

                // done - commit the changes
                $ret = $db->conn->commit();

            } catch(\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $db->conn->rollback();
                $this->errors[] = $LANG24[24];
                $retval = false;
            } catch (\Doctrine\DBAL\DBALException $e) {
                $db->conn->rollback();
                $this->errors[] = 'There was an error saving the data...';
                Log::write('system',Log::ERROR,$e->getMessage());
                $retval = false;
            }
        } else {
            return $this->save();
        }

        return $retval;
    }



    /*
     * Upon submit - saves the attached images and / or deletes images marked
     *               for deletion - errors are handled.
     *
     *   What do we pass to this - we need the $_POST vars
     */
    private function saveImages()
    {
        global $_CONF, $_TABLES, $_USER;

        $db = Database::getInstance();

        // Delete any images if needed
        if (array_key_exists('delete', $_POST)) {
            foreach($_POST['delete'] AS $imgNum => $value) {
                $imgNum = (int) $imgNum;
                $ai_filename = $db->getItem(
                                    $_TABLES['article_images'],
                                    'ai_filename',
                                    array(
                                        'ai_sid' => $this->sid,
                                        'ai_img_num' => $imgNum
                                    ),
                                    array(
                                        Database::STRING,
                                        Database::INTEGER
                                    )
                );

                if ( $ai_filename !== false && $ai_filename !== null) {
                    $this->deleteImage($ai_filename);
                }
                $db->conn->delete(
                    $_TABLES['article_images'],
                    array(
                        'ai_sid' => $this->sid,
                        'ai_img_num' => $imgNum
                    ),
                    array(
                        Database::STRING,
                        Database::INTEGER
                    )
                );
            }
        }

        $currentImageCount = $db->conn->fetchColumn(
                                "SELECT MAX(ai_img_num) FROM `{$_TABLES['article_images']}` WHERE ai_sid = ?",
                                array($this->sid),
                                0,
                                array(Database::STRING)
        );
        if ($currentImageCount === false || $currentImageCount === null) {
            $index_start = 1;
        } else {
            $index_start = $currentImageCount + 1;
        }

        if (count($_FILES) > 0 AND $_CONF['maximagesperarticle'] > 0) {
            $upload = new \upload();
            if (isset ($_CONF['debug_image_upload']) && $_CONF['debug_image_upload']) {
                $upload->setLogFile ($_CONF['path'] . 'logs/error.log');
                $upload->setDebug (true);
            }
            $upload->setMaxFileUploads ($_CONF['maximagesperarticle']);
            $upload->setAutomaticResize(true);
            if ($_CONF['keep_unscaled_image'] == 1) {
                $upload->keepOriginalImage (true);
            } else {
                $upload->keepOriginalImage (false);
            }
            $upload->setAllowedMimeTypes (array (
                    'image/gif'   => '.gif',
                    'image/jpeg'  => '.jpg,.jpeg',
                    'image/pjpeg' => '.jpg,.jpeg',
                    'image/x-png' => '.png',
                    'image/png'   => '.png'
                    ));
            $upload->setFieldName('file');

    // fatal error
            if (!$upload->setPath($_CONF['path_images'] . 'articles')) {
                $this->errors[] = $upload->printErrors(false);
                return false;
            }

            $upload->setMaxDimensions($_CONF['max_image_width'], $_CONF['max_image_height']);
            $upload->setMaxFileSize($_CONF['max_image_size']); // size in bytes, 1048576 = 1MB

            // Set file permissions on file after it gets uploaded (number is in octal)
            $upload->setPerms('0644');

            $filenames = array();

            $ai_img_num = $index_start;

            for ($z = 0; $z < $_CONF['maximagesperarticle']; $z++ ) {
                $curfile['name'] = '';
                if ( isset($_FILES['file']['name'][$z]) ) {
                    $curfile['name'] = $_FILES['file']['name'][$z];
                }
                if (!empty($curfile['name'])) {
                    $pos = strrpos($curfile['name'],'.') + 1;
                    $fextension = substr($curfile['name'], $pos);

                    $filenames[] = $this->sid . '_' . $ai_img_num . '.' . $fextension;
                    $ai_img_num++;
                } else {
                    $filenames[] = '';
                }
            }
            $upload->setFileNames($filenames);
            $upload->uploadFiles();
    // fatal errors
            if ($upload->areErrors()) {
                $this->errors[] = $upload->printErrors(false);
                return false;
            }
            for ($z = 0; $z < $_CONF['maximagesperarticle']; $z++ ) {
                if ( $filenames[$z] != '' ) {

                    $ai_img_num = $db->conn->fetchColumn(
                                        "SELECT MAX(ai_img_num) + 1 FROM `{$_TABLES['article_images']}` WHERE ai_sid = ?",
                                        array($this->sid),
                                        0,
                                        array(Database::STRING)
                    );
                    if ($ai_img_num === false || $ai_img_num === null || $ai_img_num < 1) {
                        $ai_img_num = 1;
                    }
                    $db->conn->insert(
                        $_TABLES['article_images'],
                        array(
                            'ai_sid'        => $this->sid,
                            'ai_img_num'    => $ai_img_num,
                            'ai_filename'   => $filenames[$z]
                        ),
                        array(
                            Database::STRING,
                            Database::INTEGER,
                            Database::STRING
                        )
                    );
                }
            }
        }
        return true;
    }

    public function getAccess()
    {
        return SEC_hasAccess($this->owner_id, $this->group_id,
                             $this->perm_owner, $this->perm_group,
                             $this->perm_members, $this->perm_anon);

    }

    /*
     * return true on success
     *        false on failure
     */
    public function retrieveArticleFromDB($sid)
    {
        global $_TABLES;

        $db = Database::getInstance();
        $storyVars = $db->conn->fetchAssoc(
                "SELECT * FROM `{$_TABLES['stories']}` WHERE sid = ?",
                array($sid),
                array(Database::STRING)
        );

        if ($storyVars === false) {
            return $this::STORY_INVALID_SID;
        }

        // verify story permission first
        $access = SEC_hasAccess(
                    $storyVars['owner_id'],
                    $storyVars['group_id'],
                    $storyVars['perm_owner'],
                    $storyVars['perm_group'],
                    $storyVars['perm_members'],
                    $storyVars['perm_anon']
                  );

        if ($access === 0) {
            return $this::STORY_PERMISSION_DENIED;
        }

        // load the vars
        $this->setVars($storyVars);

        // only validates we have read or write access to the topic
        if ($this->checkTopicAccess()) {
            return $this::STORY_LOADED_OK;
        }

        return $this::STORY_PERMISSION_DENIED;
    }

    public function retrieveArticleFromVars($args)
    {
        global $_TABLES;

        $this->setVars($args);

        // special case items
        // these are check boxes from the edit form
        //  we need to set the vars appropriately based on value

        if (!isset($args['show_topic_icon'])) {
            $this->set('show_topic_icon',0);
        }
        if (!isset($args['draft_flag'])) {
            $this->set('draft_flag',0);
        }
        if (!isset($args['sv_autoplay'])) {
            $this->set('sv_autoplay',0);
        }

        // verify story permission first
        $access = SEC_hasAccess(
                    $this->owner_id,
                    $this->group_id,
                    $this->perm_owner,
                    $this->perm_group,
                    $this->perm_members,
                    $this->perm_anon
                  );

        if ($access === 0) {
            return STORY_PERMISSION_DENIED;
        }

        if ($this->checkTopicAccess()) {
            return $this::STORY_LOADED_OK;
        }

        return $this::STORY_PERMISSION_DENIED;
    }

    /*
     * return true on success
     *        false on failure
     */
    public function retrieveSubmission($sid)
    {
        global $_TABLES, $_USER;

        $db = Database::getInstance();
        $storyVars = $db->conn->fetchAssoc(
                "SELECT * FROM `{$_TABLES['storysubmission']}` WHERE sid = ?",
                array($sid),
                array(Database::STRING)
        );

        if ($storyVars === false) {
            return $this::STORY_INVALID_SID;
        }

        // load the vars
        $this->setVars($storyVars);

        if ($this->owner_id == 1) {
            $this->set('owner_id',$_USER['uid']);
        }

        if ($this->checkTopicAccess()) {
            return $this::STORY_LOADED_OK;
        }

        return $this::STORY_PERMISSION_DENIED;
    }

    private function checkTopicAccess()
    {
        global $_TABLES;

        $db = Database::getInstance();

        $access = false;

        $storyTopics = [];

        $storyTopics[] = $this->tid;

        if (!empty($this->alternate_tid)) {
            $storyTopics[] = $this->alternate_tid;
        }
        $cacheKey = md5(join($storyTopics));

        $sql = "SELECT tid,topic,description,imageurl, owner_id, group_id, perm_anon,perm_members, perm_group, perm_owner
                FROM `{$_TABLES['topics']}` WHERE tid in (?)";

        $stmt = $db->conn->executeQuery(
                    $sql,
                    array($storyTopics),
                    array(Database::PARAM_STR_ARRAY),
                    new \Doctrine\DBAL\Cache\QueryCacheProfile(3600, 'tids_'.$cacheKey)
        );
        while ($topicRec = $stmt->fetch(Database::ASSOCIATIVE)) {
            if ($topicRec['tid'] == $this->tid) {
                $this->topic = $topicRec['topic'];
                $this->topicImage = $topicRec['imageurl'];
                $this->topicDescription = $topicRec['description'];
                $this->topic_perm_owner = $topicRec['perm_owner'];
                $this->topic_perm_group = $topicRec['perm_group'];
                $this->topic_perm_members = $topicRec['perm_members'];
                $this->topic_perm_anon = $topicRec['perm_anon'];
            } else {
                $this->altTopic = $topicRec['topic'];
                $this->altTopicDescription = $topicRec['description'];
                $this->alt_topic_perm_owner = $topicRec['perm_owner'];
                $this->alt_topic_perm_group = $topicRec['perm_group'];
                $this->alt_topic_perm_members = $topicRec['perm_members'];
                $this->alt_topic_perm_anon = $topicRec['perm_anon'];
            }

            $access = SEC_hasAccess(
                        $topicRec['owner_id'], $topicRec['group_id'],
                        $topicRec['perm_owner'], $topicRec['perm_group'],
                        $topicRec['perm_members'],
                        $topicRec['perm_anon']
                      );

            $this->topic_access = $access;

            if ($access > 0) {
                $access = true;
            }
        }

        if ($access === false) {
            return false;
        }
        return true;
    }

    /*
     * Process story [imageX] tags.
     * This is an internal function but must be declared as public
     * since it is used as a filter to the Formatter class.
     */
    public function processImageTags($parsedText)
    {
        global $_CONF, $_TABLES, $LANG24;

        $count = $this->getImages();

        if ($count == 0) {
            return $parsedText;       // nothing to process
        }

        $errors = array();
        $stdImageLoc = true;

        if (!strstr($_CONF['path_images'], $_CONF['path_html'])) {
            $stdImageLoc = false;
        }

        $i = 1;
        foreach ($this->associatedImages AS $image ) {
            $sizeattributes = COM_getImgSizeAttributes($_CONF['path_images'] . 'articles/' . $image);

            $norm = '[image' . $i . ']';
            $left = '[image' . $i . '_left]';
            $right = '[image' . $i . '_right]';

            $unscalednorm = '[unscaled' . $i . ']';
            $unscaledleft = '[unscaled' . $i . '_left]';
            $unscaledright = '[unscaled' . $i . '_right]';

            $imgpath = '';

            if ($stdImageLoc) {
                $imgpath = substr($_CONF['path_images'], strlen($_CONF['path_html']));
                $imgSrc = $_CONF['site_url'] . '/' . $imgpath . 'articles/' . $image;
            } else {
                $imgSrc = $_CONF['site_url'] . '/getimage.php?mode=articles&amp;image=' . $image;
            }

            // Build image tags for each flavour of the image:
            $img_noalign = '<img ' . $sizeattributes . 'src="' . $imgSrc . '" alt="">';
            $img_leftalgn = '<img ' . $sizeattributes . 'class="floatleft" src="' . $imgSrc . '" alt="">';
            $img_rightalgn = '<img ' . $sizeattributes . 'class="floatright" src="' . $imgSrc . '" alt="">';

            // Are we keeping unscaled images?
            if ($_CONF['keep_unscaled_image'] == 1) {
                $lFilename_large = substr_replace($image, '_original.',strrpos($image, '.'), 1);

                $lFilename_large_complete = $_CONF['path_images'] . 'articles/'.$lFilename_large;

                // We need to map that filename to the right location
                // or the fetch script:
                if ($stdImageLoc) {
                    $lFilename_large_URL = $_CONF['site_url'].'/'.$imgpath.'articles/'.$lFilename_large;
                } else {
                    $lFilename_large_URL = $_CONF['site_url'].'/getimage.php?mode=show&amp;image='.$lFilename_large;
                }

                // And finally, replace the [imageX_mode] tags with the
                // image and its hyperlink (only when the large image
                // actually exists)
                $lLink_url  = '';
                $lLink_attr = '';
                if (file_exists($lFilename_large_complete)) {
                    $lLink_url = $lFilename_large_URL;
                    $lLink_attr = array('rel' => 'lightbox','data-uk-lightbox' => '');
                }
            }

            if (!empty($lLink_url)) {
                $parsedText = str_replace($norm,  COM_createLink($img_noalign,   $lLink_url, $lLink_attr), $parsedText);
                $parsedText = str_replace($left,  COM_createLink($img_leftalgn,  $lLink_url, $lLink_attr), $parsedText);
                $parsedText = str_replace($right, COM_createLink($img_rightalgn, $lLink_url, $lLink_attr), $parsedText);
            } else {
                // We aren't wrapping our image tags in hyperlinks, so
                // just replace the [imagex_mode] tags with the image:
                $parsedText = str_replace($norm,  $img_noalign,   $parsedText);
                $parsedText = str_replace($left,  $img_leftalgn,  $parsedText);
                $parsedText = str_replace($right, $img_rightalgn, $parsedText);
            }

            // And insert the unscaled mode images:
            if (($_CONF['allow_user_scaling'] == 1) and ($_CONF['keep_unscaled_image'] == 1)) {
                if (file_exists($lFilename_large_complete)) {
                    $imgSrc = $lFilename_large_URL;
                    $sizeattributes = COM_getImgSizeAttributes($lFilename_large_complete);
                }

                $parsedText = str_replace($unscalednorm, '<img ' . $sizeattributes . 'src="' .
                                     $imgSrc . '" alt="">', $parsedText);
                $parsedText = str_replace($unscaledleft, '<img ' . $sizeattributes .
                                     'align="left" src="' . $imgSrc . '" alt="">', $parsedText);
                $parsedText = str_replace($unscaledright, '<img ' . $sizeattributes .
                                     'align="right" src="' . $imgSrc. '" alt="">', $parsedText);
            }
            $i++;
        }
        return $parsedText;
    }

    /**
     * Retrieves images for this story
     *
     * @return int   Count of images associated to the story
     */
    public function getImages()
    {
        global $_TABLES;

        $db = Database::getInstance();

        $nrows = 0;
        // if associatedImages is an array - it has been processed
        if ($this->associatedImages === null) {

            $stmt = $db->conn->executeQuery(
                        "SELECT ai_filename FROM `{$_TABLES['article_images']}`
                           WHERE ai_sid = ? ORDER BY ai_img_num",
                        array($this->sid),
                        array(Database::STRING)
                    );

            $imageRecs = $stmt->fetchAll(Database::ASSOCIATIVE);

            $nrows = count($imageRecs);

            if ($nrows > 0) {
                $this->associatedImages = array();
                foreach($imageRecs AS $image) {
                    $this->associatedImages[] = $image['ai_filename'];
                }
                unset($ImageRecs);
            }
        } else {
            $nrows = count($this->associatedImages);
        }
        return $nrows;
    }

    /*
     * return an associate array
     */
    public function getTopicPerms($tid)
    {

    }

    /*
     * Standard getter - retrieves both DB and class vars
     *
     * @param string  $varname - the variable to retrieve
     * @return mixed  Returns the value if found otherwise blank
     */
    public function get($varname)
    {
        global $_CONF, $_USER;

        switch ($varname) {
            case 'id' :
                return $this->id;
                break;

            case 'sid' :
            case 'story_id' :
                return $this->sid;
                break;

            case 'uid' :
            case 'story_author_id' :
                return $this->uid;
                break;

            case 'draft_flag' :
                return $this->draft_flag;
                break;

            case 'tid' :
                return $this->tid;
                break;

            case 'alternate_tid' :
                return $this->alternate_tid;
                break;

            case 'story_image' :
            case 'story_image_url' :
                return $this->story_image;
                break;

            case 'story_video' :
            case 'story_video_url' :
                return $this->story_video;
                break;

            case 'sv_autoplay' :
            case 'story_video_autoplay' :
                return $this->sv_autoplay;
                break;

            case 'date' :
            case 'story_publish_date' :
                return $this->date->toMySQL(true);
                break;

            case 'iso8601_date' :
            case 'story_publish_date_iso8601' :
                return $this->date->toISO8601(true);
                break;

            case 'unixdate' :
            case 'story_publish_date_unix' :
                return $this->date->toUnix();
                break;

            case 'title' :
            case 'story_title' :
                return $this->title;
                break;

            case 'subtitle' :
            case 'story_subtitle' :
                return $this->subtitle;
                break;

            case 'introtext' :
            case 'bodytext' :
                return $this->{$varname};
                break;

            case 'hits' :
            case 'story_views' :
                return $this->hits;
                break;

            case 'rating' :
            case 'story_rating' :
                return (int) $this->rating;
                break;

            case 'votes' :
            case 'story_votes' :
                return (int) $this->votes;
                break;

            case 'numemails' :
            case 'story_emailed_count' :
                return (int) $this->numemails;
                break;

            case 'comments' :
            case 'story_comment_count' :
                return (int) $this->comments;
                break;

            case 'comment_expire' :
            case 'comment_expire_date' :
                return $this->comment_expire->format('Y-m-d H:i');
                break;

            case 'comment_expire_date_unix' :
                return $this->comment_expire->toUnix();
                break;

            case 'trackbacks' :
            case 'story_trackback_count' :
                return (int) $this->trackbacks;
                break;

            case 'related' :
            case 'story_whats_related' :
                return $this->related;
                break;

            case 'featured' :
            case 'story_featured' :
                return (int) $this->featured;
                break;

            case 'show_topic_icon' :
                return (int) $this->show_topic_icon;
                break;

            case 'commentcode' :
                return (int) $this->commentcode;
                break;

            case 'trackbackcode' :
                return (int) $this->trackbackcode;
                break;

            case 'statuscode' :
                return (int) $this->statuscode;
                break;

            case 'expire' :
            case 'story_expire_date' :
                return $this->expire->format('Y-m-d H:i',true);
                break;

            case 'attribution_url' :
                return $this->attribution_url;
                break;

            case 'attribution_name' :
                return $this->attribution_name;
                break;

            case 'attribution_author' :
                return $this->attribution_author;
                break;

            case 'postmode' :
                return $this->postmode;
                break;

            case 'advanced_editor_mode' :
                return (bool) ($this->advanced_editor_mode == 1 ? true : false);
                break;

            case 'frontpage' :
                return $this->frontpage;
                break;

            case 'frontpage_date' :
                return $this->frontpage_date->format('Y-m-d H:i');
                break;

            case 'owner_id' :
                return (int) $this->owner_id;
                break;

            case 'group_id' :
                return (int) $this->group_id;
                break;

            case 'perm_owner' :
                return (int) $this->perm_owner;
                break;

            case 'perm_group' :
                return (int) $this->perm_group;
                break;

            case 'perm_members' :
                return (int) $this->perm_members;
                break;

            case 'perm_anon' :
                return (int) $this->perm_anon;
                break;

            case 'moderate' :
                return (int) $this->moderate;
                break;

            default :
                if (isset($this->{$varname})) {
                    return $this->{$varname};
                }
                break;
        }
        return null;
    }


    /*
     * Brains of the operation - here we set the objects properties
     * and do all necessary conversions, setup depending on the source
     * of the data (i.e.; DB or $_POST).
     */
    public function set($varname, $value, $storyVars = array())
    {
        global $_CONF, $_USER, $_TABLES;

        // 1) validate the variable exists
        // 2) do any cleansing or processing we need to do

        if (property_exists($this,$varname)) {

            switch ($varname) {
                case 'postmode' :
                    if (!in_array($value,array('html','text'))) {
                        $value = 'text';
                    }
                    $this->{$varname} = $value;
                    break;

                case 'tid' :
                    $this->{$varname} = $value;
                    break;

                case 'alternate_tid' :
                    $this->{$varname} = $value;
                    break;

                case 'frontpage' :
                    $this->frontpage = $value;
                    break;

                case 'frontpage_date' :
                    if (isset($storyVars[CSRF_TOKEN])) {
                        // user submitted date via a form
                        //reset dt object to new date provided by user
                        if (empty($value)) {
                            $value = time();
                        }
                        $this->frontpage_date = new \Date($value,$_USER['tzid']);
                    } else {
                        // source is DB
                        if ($value == NULL || empty($value) || $value == '1000-01-01 00:00:00') {
                            $value = time() + 2592000;
                        }
                        $dtTmp = new \Date($value,null);
                        $this->frontpage_date->setTimestamp($dtTmp->toUnix());
                        unset($dtTmp);
                    }
                    break;

                case 'expire' :
                    if (isset($storyVars[CSRF_TOKEN])) {
                        // user submitted date via a form
                        //reset dt object to new date provided by user
                        if (empty($value)) {
                            $value = time();
                        }
                        $this->expire = new \Date($value,$_USER['tzid']);
                    } else {
                        // source is DB
                        if ($value == NULL || empty($value) || $value == '1000-01-01 00:00:00') {
                            $value = time() + 2592000;
                        }
                        $dtTmp = new \Date($value,null);
                        $this->expire->setTimestamp($dtTmp->toUnix());
                        unset($dtTmp);
                    }
                    break;

                case 'perm_owner' :
                    if (is_array($value)) {
                        $this->perm_owner = $value[0];
                    } else {
                        $this->perm_owner = $value;
                    }
                    break;

                case 'perm_group' :
                    if (is_array($value)) {
                        $this->perm_group = $value[0];
                    } else {
                        $this->perm_group = $value;
                    }
                    break;

                case 'perm_members' :
                    if (is_array($value)) {
                        $this->perm_members = $value[0];
                    } else {
                        $this->perm_members = $value;
                    }
                    break;

                case 'perm_anon' :
                    if (is_array($value)) {
                        $this->perm_anon = $value[0];
                    } else {
                        $this->perm_anon = $value;
                    }
                    break;

                case 'uid' :
                    // set username and fullname and photo too
                    $this->uid = $value;

                    // build other author attributes
                    $db = Database::getInstance();
                    $userData = $db->conn->fetchAssoc(
                           "SELECT username, fullname, remoteusername, remoteservice, photo, about
                             FROM `{$_TABLES['users']}` AS u LEFT JOIN `{$_TABLES['userinfo']}` AS i ON u.uid=i.uid
                             WHERE u.uid=?",
                           array($this->uid),
                           array(Database::INTEGER),
                           new \Doctrine\DBAL\Cache\QueryCacheProfile(3600, 'uid_'.$this->uid)
                    );
                    if ($userData !== false) {
                        $this->username = $userData['username'];
                        $this->fullname = $userData['username'];
                        if (!empty($userData['fullname']) && ($_CONF['show_fullname'] == 1)) {
                            $this->fullname = $userData['fullname'];
                        } else if ($_CONF['user_login_method']['3rdparty'] && !empty($userData['remoteusername'])) {
                            if (!empty($userData['username'])) {
                                $remoteusername = $userData['username'];
                            }
                            if ($_CONF['show_servicename']) {
                                $ret = $userData['username'].'@'.$userData['remoteservice'];
                            } else {
                                $ret = $userData['username'];
                            }
                        }
                        $this->user_photo = $userData['photo'];
                        $this->user_about = $userData['about'];
                    } else {
                        $this->username = '';
                        $this->fullname = '';
                    }
                    if (empty($this->user_photo)) {
                        $this->user_photo = 'default.jpg';
                    }

                    break;

                case 'owner_id' :
                    // set username and fullname
                    $this->owner_id = $value;
                    break;

                case 'sid' :
                    $this->sid = $value;
                    break;

                case 'date' :
                    if (isset($storyVars[CSRF_TOKEN])) { // user provided date
                        if (empty($value)) {
                            $value = time();
                        }
                        $this->date = new \Date($value,$_USER['tzid']);
                    } else {
                        if ($value == NULL || empty($value) || $value == '1000-01-01 00:00:00') {
                            $value = time();
                        }
                        $dtTmp = new \Date($value,null);
                        $this->date->setTimestamp($dtTmp->toUnix());
                        unset($dtTmp);
                    }
                    break;

                case 'comment_expire' :
                    if (isset($storyVars[CSRF_TOKEN])) {
                        // user submitted date via a form
                        //reset dt object to new date provided by user
                        if (empty($value)) {
                            $value = time();
                        }
                        $this->comment_expire = new \Date($value,$_USER['tzid']);
                    } else {
                        // source is DB
                        if ($value !== null && $value !== '1000-01-01 00:00:00') {
                            $this->cmt_close_flag = 1;
                        }
                        if ($value == NULL || empty($value) || $value == '1000-01-01 00:00:00') {
                            $value = time();
                        }
                        $dtTmp = new \Date($value,null);
                        $this->comment_expire->setTimestamp($dtTmp->toUnix());
                        unset($dtTmp);
                    }
                    break;

                case 'show_topic_icon' :
                    if ($value === 'on') {
                        $value = 1;
                    }
                    $this->show_topic_icon = (int) $value;
                    break;

                case 'archiveflag' :
                    if ($value === 'on') {
                        $value = 1;
                    }
                    $this->archiveflag = (int) $value;
                    break;

                case 'draft_flag' :
                    if ($value === 'on') {
                        $value = 1;
                    }
                    $this->draft_flag = (int) $value;
                    break;

                case 'sv_autoplay' :
                    if ($value === 'on') {
                        $value = 1;
                    }
                    $this->sv_autoplay = (int) $value;
                    break;

                default :
                    $this->{$varname} = $value;
                    break;
            }
        } else {
            // here we handle $_POST vars that do not map directly to the DB fields

            switch ($varname) {
                case CSRF_TOKEN :
                    // we now know we have a form submission
                    // check settings for potential disabled fields
                    if (!isset($storyVars['cmt_close_flag'])) {
                        $this->cmt_close_flag = 0;
                    }
                    break;

                default :
                    // ignore anything we don't explicity know how to handle
                    break;
            }
        }
    }


    /**
     * getDisplayItem return display ready data
     *
     * This method does the heavy lifting of processing all
     * story elements for display.  This method SHOULD NOT
     * be used to retrieve data for processing, in that case
     * use the get() method.
     *
     * @param  mixed $varname - variable to retrieve
     * @return mixed|null
     * @see    get()
     */
    public function getDisplayItem($varname)
    {
        global $_CONF;

        $retval = '';

        /*
         * several data elements have more descriptive aliases
         * that are used throughout...
         */

        switch ($varname) {
            case 'sid' :
            case 'story_id' :
                $retval = (string) $this->sid;
                break;

            case 'uid' :
            case 'story_author_id' :
                $retval = (int) $this->uid;
                break;

            case 'draft_flag' :
                $retval = (bool) $this->draft_flag;
                break;

            case 'tid' :
                $retval = (string) $this->tid;
                break;

            case 'alternate_tid' :
                $retval = (string) $this->alternate_tid;
                break;

            case 'story_image' :
            case 'story_image_url' :
                $retval = $this->story_image;
                break;

            case 'story_video' :
            case 'story_video_url' :
                $retval = $this->story_video;
                break;

            case 'sv_autoplay' :
            case 'story_video_autoplay' :
                $retval = (bool) $this->sv_autoplay;
                break;

            case 'date' :
            case 'story_publish_date' :
                $retval = $this->date->format($this->date->getUserFormat(),true);
                break;

            case 'iso8601_date' :
            case 'story_publish_date_iso8601' :
                $retval = $this->date->toISO8601(true);
                break;

            case 'unixdate' :
            case 'story_publish_date_unix' :
                $retval = $this->date->toUnix();
                break;

            case 'title' :
            case 'story_title' :
                $retval = $this->filter->htmlspecialchars($this->title);
                break;

            case 'subtitle' :
            case 'story_subtitle' :
                $retval = $this->filter->htmlspecialchars($this->subtitle);
                break;

            case 'introtext' :
            case 'bodytext' :
                $this->format->setType($this->postmode);
                $retval = $this->format->parse($this->{$varname});
                break;

            case 'introtext_text' :
                $this->format->setType($this->postmode);
                $str = $this->format->parse($this->introtext);
                $html2txt = new \Html2Text\Html2Text($str);
                $str =  trim($html2txt->get_text());
                $str = str_replace(array("\015\012", "\015", "\012"), " ", $str);
                $retval = $str;
                break;

            case 'hits' :
            case 'story_views' :
                $retval = COM_numberFormat($this->hits,0);
                break;

            case 'rating' :
            case 'story_rating' :
                $retval = (int) $this->rating;
                break;

            case 'votes' :
            case 'story_votes' :
                $retval = (int) COM_numberFormat($this->votes,0);
                break;

            case 'numemails' :
            case 'story_emailed_count' :
                $retval = COM_numberFormat($this->numemails,0);
                break;

            case 'comments' :
            case 'story_comment_count' :
                $retval = COM_numberFormat($this->comments,0);
                break;

            case 'comment_expire' :
            case 'comment_expire_date' :
                $retval = $this->comment_expire->format($this->comment_expire->getUserFormat(),true);
                break;

            case 'comment_expire_date_unix' :
                $retval = $this->comment_expire->toUnix();
                break;

            case 'trackbacks' :
            case 'story_trackback_count' :
                $retval = COM_numberFormat($this->trackbacks,0);
                break;

            case 'related' :
            case 'story_whats_related' :
                $retval = $this->related;
                break;

            case 'featured' :
            case 'story_featured' :
                $retval = (int) $this->featured;
                break;

            case 'show_topic_icon' :
                $retval = (int) $this->show_topic_icon;
                break;

            case 'commentcode' :
                $retval = (int) $this->commentcode;
                break;

            case 'trackbackcode' :
                $retval = (int) $this->trackbackcode;
                break;

            case 'statuscode' :
                $retval = (int) $this->statuscode;
                break;

            case 'expire' :
                $retval = $this->expire->format($this->expire->getUserFormat(),true);
                break;

            case 'attribution_url' :
                $retval = $this->filter->htmlspecialchars($this->attribution_url);
                break;

            case 'attribution_name' :
                $retval = $this->filter->htmlspecialchars($this->attribution_name);
                break;

            case 'attribution_author' :
                $retval = $this->filter->htmlspecialchars($this->attribution_author);
                break;

            case 'postmode' :
                $retval = $this->postmode;
                break;

            case 'advanced_editor_mode' :  // we want to use this as a true / false to trigger showing the visual editor
                $retval = (int) $this->advanced_editor_mode;
                break;

            case 'frontpage' :
            case 'show_on_frontpage' :
                $retval = (int) $this->frontpage;
                break;

            case 'frontpage_date' :
                $retval = $this->frontpage_date->format($this->frontpage_date->getUserFormat(),true);
                break;

            case 'owner_id' :
                $retval = (int) $this->owner_id;
                break;

            case 'group_id' :
                $retval = (int) $this->group_id;
                break;

            case 'perm_owner' :
                $retval = (int) $this->perm_owner;
                break;

            case 'perm_group' :
                $retval = (int) $this->perm_group;
                break;

            case 'perm_members' :
                $retval = (int) $this->perm_members;
                break;

            case 'perm_anon' :
                $retval = $this->filter->htmlspecialchars($this->{$varname});
                break;

            //
            // Dynamic fields used to display a story - not part of the story record

            case 'rating_bar' :
                $retval = $this->getRatingBar();
                break;

            case 'topic_url' :
                $retval = $_CONF['site_url'] . '/index.php?topic=' . urlencode($this->tid);
                break;

            case 'alttopic_url' :
                $retval = $_CONF['site_url'] . '/index.php?topic=' . urlencode($this->alternate_tid);
                break;

            case 'topic' :
                $retval = $this->filter->htmlspecialchars($this->topic);
                break;

            case 'alternate_topic' :
                $retval = $this->filter->htmlspecialchars($this->altTopic);
                break;

            case 'topic_imageurl' :
                $retval = $this->topicImage;
                break;

            case 'topic_description_text' :
                $html2txt = new \Html2Text\Html2Text($this->topicDescription);
                $retval = trim($html2txt->get_text());
                break;

            case 'comments_url' :
                $retval = COM_buildUrl($_CONF['site_url'].'/article.php?story=' . urlencode($this->sid)) . '#comments';
                break;

            case 'author_fullname' :
                if ($_CONF['show_fullname'] == 1) {
                    $retval = $this->fullname;
                } else {
                    $retval = $this->username;
                }
                break;

            case 'trackback_url' :
                $retval = COM_buildUrl($_CONF['site_url'].'/article.php?story='.urlencode($this->sid)).'#trackback';
                break;

            case 'email_story_url' :
                $retval = $_CONF['site_url'].'/profiles.php?sid='.urlencode($this->sid).'&amp;what=emailstory';
                break;

            case 'print_story_url' :
                $retval = COM_buildUrl($_CONF['site_url'].'/article.php?story='.urlencode($this->sid).'&amp;mode=print');
                break;

            case 'feed_url' :
                $retval = $this->getFeedUrl();
                break;

            case 'edit_url' :
                $retval = '';
                if (SEC_hasRights('story.edit') && ($this->getAccess() == 3) &&
                   (SEC_hasTopicAccess($this->tid) == 3)) {
                    $retval = $_CONF['site_admin_url'].'/story.php?edit=x&amp;sid='.urlencode($this->sid);
                }
                break;

            case 'author_photo' :
                $retval = $_CONF['site_url'].'/images/userphotos/'.$this->user_photo;
                break;

            case 'about' :
                $currentNS = $this->format->getNamespace();
                $currentAction = $this->format->getAction();
                $currentType = $this->format->getType();
                $currentAT = $this->format->getParseAutoTags();

                $this->format->setOptions( array (
                    'formatType'    => 'text',
                    'namespace'     => 'glfusion',
                    'action'        => 'about_user',
                    'parseAutoTags' => true
                ));

                $retval = $this->format->parse($this->user_about);

                $this->format->setOptions( array (
                    'formatType'    => $currentType,
                    'namespace'     => $currentNS,
                    'action'        => $currentAction,
                    'parseAutoTags' => $currentAT
                ));

                break;

            default :
                // we have not explictly set it up - return blank
                $retval = '';
                break;
        }
        return $retval;
    }


    /**
     * render - formats the full story for display
     *
     * @param  string  $displayType - Index 'y' or full article 'n' or preview 'p'
     * @param  string  $tpl - Template to use
     * @return string  fully formatted story ready for display
     */
    public function getDisplayArticle($displayType = 'n', $tpl='')
    {
        global $_CONF, $_SYSTEM, $_TABLES, $_USER,
                $LANG01, $LANG05, $LANG11, $LANG_TRB, $_IMAGE_TYPE, $_GROUPS;

        USES_lib_social();

        static $storycounter = 0;

        $topic = \TOPIC::currentID();

        // determine template / display type /
        $article_filevar = 'article';
        if (empty($tpl)) {
            if ($this->isFeatured()) {
                $tpl = 'featuredstorytext.thtml';
                $article_filevar = 'featuredarticle';
            } elseif ($this->statuscode == $this::STORY_ARCHIVE_ON_EXPIRE
                        && $this->get('comment_expire_date_unix') <= time()) {
                $tpl = 'archivestorytext.thtml';
                $article_filevar = 'archivearticle';
            } else {
                $tpl = 'storytext.thtml';
                $article_filevar = 'article';
            }
        }

        if (isset($_SYSTEM['custom_topic_templates']) && $_SYSTEM['custom_topic_templates'] == true ) {
            if ($topic != '') {
                $storyTid = $this->filter->sanitizeFilename(strtolower($topic));
            } else {
                $storyTid = $this->filter->sanitizeFilename(strtolower($this->tid));
            }
            $pos = strpos($tpl,".");
            if ( $pos !== false ) {
                $base_template = substr($tpl,0,$pos);
            } else {
                $base_template = 'storytext';
            }
            if (file_exists($_CONF['path_layout'].'/custom/'.$base_template.'_'.$storyTid.'.thtml') !== false) {
                $tpl = $base_template.'_'.$storyTid.'.thtml';
            }
        }

        $templateHash = substr($tpl,0,strpos($tpl,'.'));

        $this->template->set_file('article', $tpl);

        ## begin processing data

        $introtext = $this->getDisplayItem('introtext');
        if ($displayType != 'y') {
            $bodytext  = $this->getDisplayItem('bodytext');
        } else {
            $bodytext = '';
        }

        # - AdBlock / display info for template
        switch ($displayType) {
            case 'p' :
                $story_display = 'preview';
                $this->template->set_var( 'story_counter', 0 );
                break;
            case 'n' :
                $story_display = 'article';
                $this->template->set_var('story_counter', 0 );
                $this->template->set_var('adblock_content',PLG_displayAdBlock('article',0), false, true);
                $this->template->set_var('breadcrumbs',true);
                break;
            case 'y' :
            default :
                $story_display = 'index';
                $storycounter++;
                $this->template->set_var('story_counter', $storycounter, false, true );
                $this->template->set_var('adblock',PLG_displayAdBlock('article',$storycounter), false, true);
                break;
        }
        $this->template->set_var( 'story_display', $story_display, false, true );

# - dynamic meta data fields (views and comments)

        if ($_CONF['hideviewscount'] != 1) {
            $this->template->set_var(array(
                'lang_views' => $LANG01[106],
                'story_hits' => $this->getDisplayItem('hits')
            ),'',false,true);
        }

        if ($this->get('commentcode') >= 0
                && $displayType != 'n'
                && $displayType != 'p') {
            $commentsUrl = $this->getDisplayItem('comments_url');

            $cmtLinkArray = CMT_getCommentLinkWithCount(
                                'article',
                                $this->get('sid'),
                                $_CONF['site_url'].'/article.php?story='.urlencode($this->get('sid')),
                                $this->get('story_comment_count'),
                                true
                            );

            $this->template->set_var(array(
                'comments_with_count_link'  => $cmtLinkArray['link_with_count'],
                'comments_url'              => $cmtLinkArray['url'],
                'comments_url_extra'        => $cmtLinkArray['url_extra'],
                'comments_text'             => $cmtLinkArray['comment_count'],
                'comments_count'            => $cmtLinkArray['comment_count'],
                'lang_comments'             => $LANG01[3],
            ),'',false,true);

            $comments_with_count = sprintf( $LANG01[121], $this->getDisplayItem('story_comment_count'));

            if ($this->commentcode == 0 && ($_CONF['commentsloginrequired'] == 0 || !COM_isAnonUser())) {
                $postCommentUrl = $_CONF['site_url'] . '/comment.php?sid='.urlencode($this->get('sid')) . '&amp;pid=0&amp;type=article#comment_entry';
                $this->template->set_var( 'post_comment_link',
                    COM_createLink($LANG01[60], $postCommentUrl,
                        array('rel' => 'nofollow')));
                $this->template->set_var( 'lang_post_comment', $LANG01[60] );
                $this->template->set_var( 'post_comment_url', $postCommentUrl);
            }
        }

# - Trackbacks

        if ($_CONF['trackback_enabled'] && $displayType != 'n' && $displayType != 'p') {

            $this->template->set_var(array(
                'trackback_url'     => $this->getDisplayItem('trackback_url'),
                'trackback_text'    => $this->getDisplayItem('trackbacks').' '.$LANG_TRB['trackbacks'],
            ));
        }

# - Ratings

        if ($_CONF['rating_enabled'] != 0 && $displayType != 'p') {
            $this->template->set_var(
                'rating_bar',
                $this->getDisplayItem('rating_bar'),
                false,
                true
            );
        } else {
            $this->template->unset_var('rating_bar');
        }

# - Topic URL - dynamic as it is a user preference

        if ($this->getShowTopicIcon()) {
            $imageurl = $this->getDisplayItem('topic_imageurl');
            if (!empty($imageurl)) {
                $this->template->set_var(
                    'story_topic_image_url',
                    $imageurl,
                    false,
                    true
                );
            }
        }

        $this->template->set_var(array(
            'story_date'    => $this->getDisplayItem('story_publish_date'),
            'iso8601_date'  => $this->getDisplayItem('story_publish_date_iso8601')
        ),'',false,true);

        $alttopic = '';
        $alttopic_description = '';

        $topic_description = $this->getDisplayItem('topic_description');
        if ($this->alternate_tid  != NULL) {
            $alttopic = $this->getDisplayItem('alternate_topic');
            $alttopic_description = $this->getDisplayItem('alternate_topic_description');
        }

        $this->template->set_var(array(
            'story_topic_url'           => $this->getDisplayItem('topic_url'),
            'alt_story_topic_url'       => $this->getDisplayItem('alttopic_url'),
            'story_topic_id'            => $this->getDisplayItem('tid'),
            'alt_story_topic_id'        => $this->getDisplayItem('alternate_tid'),
            'story_topic_name'          => $this->getDisplayItem('topic'),
            'story_alternate_topic_name'=> $this->getDisplayItem('alternate_topic'),
            'story_topic_description'   => $this->getDisplayItem('topic_description'),
            'story_alternate_topic_description' => $this->getDisplayItem('alternate_topic_description'),
            'story_topic_description_text' => $this->getDisplayItem('topic_description_text'), // text version
            'lang_posted_in'            => $LANG01['posted_in']
         ));

## basic stuff like SID, article permalink, title

        $articleUrl = COM_buildUrl($_CONF['site_url'] . '/article.php?story='.urlencode($this->sid));

        $this->template->set_var(array(
            'story_id'      =>  $this->get('sid'),
            'article_url'   =>  $articleUrl,
            'story_title'   =>  $this->getDisplayItem('title'),
            'story_subtitle'=>  $this->getDisplayItem('subtitle')
        ));

# - Finally set the actual story contents

        if (($displayType == 'n' ) || ( $displayType == 'p')) {  // full article view or preview
            if (empty($bodytext)) {
                $this->template->set_var( 'story_introtext', $introtext,false,true );
                $this->template->set_var( 'story_text_no_br', $introtext,false,true );
            } else {
                $this->template->set_var( 'story_introtext', $introtext .'<br>'.$bodytext,false,true );
                $this->template->set_var( 'story_text_no_br', $introtext . $bodytext,false,true );
            }
            $this->template->set_var( 'story_introtext_only', $introtext,false,true );
            $this->template->set_var( 'story_bodytext_only', $bodytext,false,true );
        } else {
            $this->template->set_var( 'story_introtext', $introtext,false,true );
            $this->template->set_var( 'story_text_no_br', $introtext,false,true );
            $this->template->set_var( 'story_introtext_only', $introtext,false,true );
        }

        // Allow topic to set vars prior to checking the cached stuff

        PLG_templateSetVars($article_filevar,$this->template);

        #
        # - Instance Caching..
        #    All content generated below is cached
        #

        $hash = CACHE_security_hash();
        $instance_id = 'story_'.$this->get('sid').'_'.$displayType.'_'.$article_filevar.'_'.$templateHash.'_'.$hash.'_'.$_USER['theme'];

        if ($displayType == 'p' || !$this->template->check_instance($instance_id,'article')) {

## - contributed info

            if ($_CONF['contributedbyline'] == 1) {
                $this->template->set_var(array(
                    'lang_contributed_by'   => $LANG01[1],
                    'lang_by'               => $LANG01[1],
                    'contributedby_author'  => $this->getDisplayItem('author_fullname'),
                ));

                if ($this->uid > 1) {
                    $this->template->set_var(
                        'contributedby_url',
                        $_CONF['site_url'].'/users.php?mode=profile&amp;uid='.urlencode($this->get('uid'))
                    );
                }

                if ($this->attribution_author == '') {
                    $this->template->set_var(array(
                        'author_about'  => $this->getDisplayItem('about'),
                        'follow_me'     => Social::getFollowMeIcons( $this->get('uid'))
                    ));
                }
            }
            $this->template->set_var('author_photo_raw',$this->getDisplayItem('author_photo'));

## - attribution

            $attribution_url = $this->getDisplayItem('attribution_url');
            $attribution_name = $this->getDisplayItem('attribution_name');
            $attribution_author = $this->getDisplayItem('attribution_author');

            if (!empty($attribution_url)) {
                $this->template->set_var('attribution_url', $attribution_url);
            }
            if (!empty($attribution_name)) {
                $this->template->set_var('attribution_name', $attribution_name);
            }
            if (!empty($attribution_author)) {
                $this->template->set_var('attribution_author', $attribution_author);
            }

            $this->template->set_var('lang_source',$LANG01['source']);

## - story image / video

            $story_image = $this->getDisplayItem('story_image_url');
            $story_video = $this->getDisplayItem('story_video_url');
            $sv_autoplay = $this->get('story_video_autoplay');

            $this->template->set_var('story_image',$story_image);
            $this->template->set_var('story_video',$story_video);
            if ($sv_autoplay) {
                $this->template->set_var('autoplay',"autoplay");
            } else {
                $this->template->unset_var('autoplay');
            }

## - title link (or not)
## - Set meta data

            // compact view
            if ($displayType != 'n' && $displayType != 'p') {
                $this->template->set_var('story_url',$articleUrl);
                $this->template->set_var(array(
                    'email_story_url'   => $this->getDisplayItem('email_story_url'),
                    'print_story_url'   => $this->getDisplayItem('print_story_url'),
                    'edit_url'          => $this->getDisplayItem('edit_url'),
                    'feed_url'          => $this->getDisplayItem('feed_url'),
                ));
            } elseif ($displayType == 'p') {
                $this->template->set_var(array(
                    'email_story_url'   => '#',
                    'edit_url'          => '#',
                ));
            } else {
                $this->template->set_var(array(
                    'email_story_url'   => $this->getDisplayItem('email_story_url'),
                    'edit_url'          => $this->getDisplayItem('edit_url'),
                ));
            }

            // index view and bodytext
            if ($displayType == 'y' && $this->hasBody()) {
                $this->template->set_var('readmore_url',$articleUrl);
                $this->template->set_var('lang_continue_reading',$LANG01['continue_reading']);
            }

# - create an instance cache - unless preview

            if ($displayType != 'p') {
                $this->template->create_instance($instance_id,'article');
            }

        }

# finalize the display

        $this->template->parse('finalstory','article');
        SESS_clearContext();
        $output = $this->template->finish($this->template->get_var('finalstory'));
        $output = PLG_outputFilter($output, 'article');
        unset($this->template);
        return $output;
    }

    /**
     * isFeatured - determines if article is featured or not
     *
     * @return bool  true if featured, false if not
     */
    public function isFeatured()
    {
        return (bool) ($this->featured == 1 ? true : false);
    }

    /**
     * getWhatRelated - Builds the What's Related section for story
     *
     * @param  string  $related - related DB field for story
     * @param  integer $uid - user ID viewing story
     * @return string
     */
    public function getWhatsRelated( $related, $uid, $tid, $atid = '' )
    {
        global $_CONF, $_TABLES, $_USER, $LANG24;

        if ( function_exists( 'CUSTOM_whatsRelated' )) {
            return CUSTOM_whatsRelated( $related,$uid,$tid );
        }

        $db = Database::getInstance();

        // get the links from the story text
        if (!empty ($related)) {
            $rel = explode ("\n", $related);
        } else {
            $rel = array ();
        }

        if (!COM_isAnonUser() || (($_CONF['loginrequired'] == 0) && ($_CONF['searchloginrequired'] == 0))) {
            // add a link to "search by author"
            if ( $_CONF['contributedbyline'] == 1 ) {
                $author = $this->getDisplayItem('author_fullname');
                $rel[] = '<a href="'.$_CONF['site_url'].'/search.php?mode=search&amp;type=stories&amp;author='.(int) $uid.'">'.$LANG24[37] . ' ' .$author.'</a>';
            }

            // add a link to "search by topic"

            $topic = $db->getItem(
                        $_TABLES['topics'],
                        'topic',
                        array('tid' => $tid),
                        array(Database::STRING)
            );
            if ($topic !== false) {
                $rel[] = '<a href="' . $_CONF['site_url']
                       . '/index.php?topic=' . urlencode($tid)
                       . '">' . $LANG24[38] . ' ' . $this->filter->htmlspecialchars($topic) . '</a>';
            }

            if ($atid != '') {
                $atopic = $db->getItem(
                            $_TABLES['topics'],
                            'topic',
                            array('tid' => $atid),
                            array(Database::STRING)
                );
                if ($atopic !== false) {
                    $rel[] = '<a href="' . $_CONF['site_url']
                           . '/index.php?topic=' . urlencode($atid)
                           . '">' . $LANG24[38] . ' ' . $this->filter->htmlspecialchars($atopic) . '</a>';
                }
            }
        }

        $related = '';
        if (sizeof($rel) > 0) {
            $related = COM_checkWords( COM_makeList( $rel, 'list-whats-related' ));
        }

        return($related);
    }


    /**
     * hasBody - check if story has bodytext
     *
     * @return bool  true if bodytext, false if not
     */
    public function hasBody()
    {
        if (!empty($this->bodytext)) {
            return true;
        }
        return false;
    }

    /**
     * hasContent - check if article has either intro / body text
     *
     * @return bool  true if content, false if not
     */
    public function hasContent()
    {
        if (!empty($this->introtext) || !empty($this->bodytext)) {
            return true;
        }
        return false;
    }


    /**
     * getRatingBar - return HTML for rating bar based on user
     *
     * @return string  Rating bar or blank if not enabled
     */
    private function getRatingBar()
    {
        global $_CONF, $_USER;

        if ($_CONF['rating_enabled'] == 0 ) {
            return '';
        }

        if ($this->rateIds === NULL) {
            $this->rateIds = RATING_getRatedIds('article');
        }

        if (@in_array($this->sid,$this->rateIds)) {
            $static = true;
            $voted = 1;
        } else {
            $static = 0;
            $voted = 0;
        }

        $uid = isset($_USER['uid']) ? $_USER['uid'] : 1;

        if ($static == 0) {
            // check if owner
            if ($_CONF['rating_enabled'] == 2 && $uid != $this->owner_id) {
                $static = 0;
            } elseif ( !COM_isAnonUser() && $uid != $this->owner_id) {
                $static = 0;
            } else {
                $static = true;
            }
        }
         return RATING_ratingBar(
                    'article',
                    $this->sid,
                    $this->get('votes'),
                    $this->get('rating'),
                    $voted,
                    5,
                    $static,
                    'sm'
                );

    }


    /**
     * showTopicIcon - Should topic icon be shown or not
     *
     * @return bool  true - show topic icon, false - do not show
     */
    private function getShowTopicIcon()
    {
        global $_USER;

        if ((!isset( $_USER['noicons']) || ($_USER['noicons'] != 1)) && $this->show_topic_icon == 1) {
            return true;
        }
        return false;
    }


    /**
     * getFeedUrl - returns feed URL for topic
     *
     * @return string  blank if no feed URL exists, or the actual URL
     */
    private function getFeedUrl()
    {
        global $_CONF, $_TABLES, $LANG11;

        $retval = '';

        $db = Database::getInstance();

        if ($_CONF['backend'] == 1) {

            // if multiple - return the most recent updated
            $sql = "SELECT filename,title,format
                    FROM `{$_TABLES['syndication']}`
                    WHERE type = 'article' AND topic = ? AND is_enabled = 1
                    ORDER BY updated DESC";

            $row = $db->conn->fetchAssoc(
                        $sql,
                        array($this->tid),
                        array(Database::STRING),
                        new \Doctrine\DBAL\Cache\QueryCacheProfile(3600, 'synd_'.$this->tid)
            );
            if ($row !== false && $row !== null) {
                $retval = SYND_getFeedUrl($row['filename']);
            }
        }
        return $retval;
    }


   /*
     * Returns data ready to populate an entry / edit form
     */
    public function getEditItem($varname)
    {
        global $_CONF, $_USER;
        // here we do what ever is necessary to return a editable
        // data value.

        switch ($varname) {
            case 'introtext' :
            case 'bodytext' :
                return $this->filter->htmlspecialchars($this->{$varname},ENT_QUOTES);
                break;

            case 'title' :
            case 'subtitle' :
                return $this->filter->htmlspecialchars($this->get($varname),ENT_QUOTES);
                break;

            case 'frontpage_date' :
                return $this->frontpage_date->format('Y-m-d H:i',true);
                break;

            case 'comment_expire' :
                return $this->comment_expire->toMySQL(true);
                break;

            case 'date' :
                return $this->date->format('Y-m-d H:i',true);
                break;

            case 'expire' :
                return $this->expire->format('Y-m-d H:i',true);
                break;

            default :
                if (isset($this->{$varname})) {
                    return $this->filter->htmlspecialchars($this->{$varname},ENT_QUOTES);
                }
                return '';
                break;
        }
    }

    public function setFormAction(STRING $formAction)
    {
        $this->formAction = $formAction;
    }

    /*
     * Build story editor form
     */

// mode can be one of the following:
//
//  edit - we are editing an existing article
//  new - creating a new story
//
//  if the save fails validation, return with edit or new.
//
//  what type of malicious activity could be done by forcing mode
//  from one or the other?  If editing, but set as new - this would cause
//  a duplicate story...
//
//  setting mode as edit when really new could cause a SQL error since the update
//  will fail.  Actually - it should not fail, it should return 0 rows affected.
//
// - we should not rely on any $_POST vars - if we need them, then we should handle
//   when we construct the object.

    public function editForm($mode = 'new', $preview = false)
    {
        global $_CONF, $_GROUPS, $_TABLES, $_USER, $LANG24, $LANG33, $LANG_ACCESS,
               $LANG_ADMIN, $MESSAGE,$_IMAGE_TYPE;

        $db = Database::getInstance();

        $display = '';
        $editStory = false;
        $action = '';

        if (!in_array($mode,array('new','edit'))) {
            $display .= COM_showMessageText('Invalid form action',true,'error');
            Log::write('system',Log::ERROR,"User {$_USER['username']} submitted an invalid form action to article editor.");
            return $display;
        }

        // permission check - make sure user has the proper rights

        // If we are editing - we should know both the story perm and the topic perms
        // since these were initialized when reading the article from the DB.
        // 1) If editing a story - make sure the actual story permissions allow editing
        // 2) If editing a story - make sure the topic or alt_topic allow editing
        //
        // If we are creating - we need to know the topic perms and the submission
        // setting value - there are a couple of other permissiosn associated to
        // topics that we need to check too - we made some changes early on
        // around how topics were handled with submissions.
        // 3) If new - make sure topic or alt_topic allow writing or submission
        //


        // Load HTML templates
        $T = new \Template($_CONF['path_layout'] . 'admin/story');
        $T->set_file('editor','articleeditor.thtml');
        $T->set_var('form_action',$_CONF['site_admin_url'].'/story.php');

        if ($preview) {
            $T->set_var('show_preview',true);
            $T->set_var('modified','1');
        } else {
            $T->set_var('modified','0');
            $T->unset_var ('show_preview');
        }

//@TODO - Language tag needed
        if ($this->postmode == 'html' || $this->postmode == 'HTML') {
            $allowedHTML = COM_allowedHTML(SEC_getUserPermissions(),false,'glfusion','story');
            if ($allowedHTML =='') {
                $allowedHTML = '<strong>Allowed HTML</strong>: All valid and safe HTML is allowed.<br>';
            }
        } else {
            $allowedHTML = '';
        }

        $allowedHTML .= COM_allowedAutotags(SEC_getUserPermissions(),false,'glfusion','story');

        $T->set_var(array(
            'lang_accessrights'     => $LANG_ACCESS['accessrights'],
            'lang_allowed_html'     => $allowedHTML,
            'lang_alt_topic'        => $LANG_ADMIN['alt_topic'],
            'lang_archivetitle'     => $LANG24[58],
            'lang_attribution'      => $LANG24[108],
            'lang_attribution_author' => $LANG24[107],
            'lang_attribution_name' => $LANG24[106],
            'lang_attribution_url'  => $LANG24[105],
            'lang_author'           => $LANG24[7],
            'lang_bodytext'         => $LANG24[17],
            'lang_bodytext'         => $LANG24[17],
            'lang_cancel'           => $LANG_ADMIN['cancel'],
            'lang_cmt_disable'      => $LANG24[63],
            'lang_comments'         => $LANG24[19],
            'lang_date'             => $LANG24[15],
            'lang_delete'           => $LANG_ADMIN['delete'],
            'lang_draft'            => $LANG24[34],
            'lang_emails'           => $LANG24[39],
            'lang_enabled'          => $LANG_ADMIN['enabled'],
            'lang_group'            => $LANG_ACCESS['group'],
            'lang_group'            => $LANG_ACCESS['group'],
            'lang_hits'             => $LANG24[18],
            'lang_images'           => $LANG24[47],
            'lang_introtext'        => $LANG24[16],
            'lang_mode'             => $LANG24[3],
            'lang_nojavascript'     => $LANG24[77],
            'lang_option'           => $LANG24[59],
            'lang_optionarchive'    => $LANG24[61],
            'lang_optiondelete'     => $LANG24[62],
            'lang_owner'            => $LANG_ACCESS['owner'],
            'lang_perm_key'         => $LANG_ACCESS['permissionskey'],
            'lang_permissions'      => $LANG_ACCESS['permissions'],
            'lang_postmode'         => $LANG24[4],
            'lang_preview'          => $LANG_ADMIN['preview'],
            'lang_publishdate'      => $LANG24[69],
            'lang_publishoptions'   => $LANG24[76],
            'lang_save'             => $LANG_ADMIN['save'],
            'lang_show_topic_icon'  => $LANG24[56],
            'lang_sid'              => $LANG24[12],
            'lang_story_stats'      => $LANG24[87],
            'lang_timeout'          => $LANG_ADMIN['timeout_msg'],
            'lang_title'            => $LANG_ADMIN['title'],
            'lang_topic'            => $LANG_ADMIN['topic'],
            'lang_trackbacks'       => $LANG24[29],
            'permissions_msg'       => $LANG_ACCESS['permmsg'],
        ));

        // setup hidden elements

        $T->set_var(array(
            'id'            => $this->getEditItem('id'),
    		'postmode'      => $this->getEditItem('postmode'),
    		'hits'          => $this->getEditItem('hits'),
    		'comments'      => $this->getEditItem('comments'),
    		'trackbacks'    => $this->getEditItem('trackbacks'),
    		'numemails'     => $this->getEditItem('numemails'),
     		'original_sid'  => $this->getEditItem('original_sid'),
     		'moderate'      => $this->getEditItem('moderate'),
            'advanced_editor_mode' => $this->getEditItem('advanced_editor_mode'),
    		'security_token'      => SEC_createToken(),
    		'security_token_name' => CSRF_TOKEN,
        ));

// build editor page

        $T->set_var(array(
            'sid'           => $this->getEditItem('sid'),
            'title'         => $this->getEditItem('title'),
            'subtitle'      => $this->getEditItem('subtitle'),
            'draft_checked' => ($this->draft_flag == 1) ? ' checked="checked" ' : '',
            'introtext'     => $this->getEditItem('introtext'),
            'bodytext'      => $this->getEditItem('bodytext'),
        ));

// publish options

        if ( SEC_hasRights('story.edit') ) {
            $allowedTopicList = COM_topicList ('tid,topic,sortnum', $this->tid, 2, true,0);
            $allowedAltTopicList = '<option value="">'.$LANG33[44].'</option>'.COM_topicList ('tid,topic,sortnum', $this->alternate_tid, 2, true,0);
        } else {
            $allowedTopicList = COM_topicList ('tid,topic,sortnum', $this->tid, 2, true,3);
            $allowedAltTopicList = '<option value="">'.$LANG33[44].'</option>'.COM_topicList ('tid,topic,sortnum', $this->alternate_tid, 2, true,3);
        }
// failsafe - if user does not have access to any topics - they shouldn't be here.
        if ( $allowedTopicList == '' ) {
            $display .= COM_showMessageText(sprintf($LANG24[42],$_CONF['site_admin_url']),$LANG_ACCESS['accessdenied'],true,'error');
            Log::write('system',Log::ERROR,"User {$_USER['username']} tried to access story $sid. No allowed topics.");
            return $display;
        }

        // frontpage date
        switch ( $this->frontpage ) {
            case 0 :
                $T->set_var('topiconly_checked',' checked="checked" ');
                break;
            case 1 :
                $T->set_var('onfrontpage_checked',' checked="checked" ');
                break;
            case 2 :
                $T->set_var('frontpageuntil_checked',' checked="checked" ');
                break;
            default :
                $T->set_var('onfrontpage_checked',' checked="checked" ');
                break;
        }

        $featured_options_data =  COM_optionList($_TABLES['featurecodes'], 'code,name', $this->featured);

        // comment / trackback option lists
        $T->set_var ('comment_options',
                COM_optionList ($_TABLES['commentcodes'], 'code,name', $this->commentcode));
        $T->set_var ('trackback_options',
                COM_optionList ($_TABLES['trackbackcodes'], 'code,name', $this->trackbackcode));

        $T->set_var(array(
            'topic' => $this->getEditItem('tid'),
            'topic_select' => $allowedTopicList,
            'alternate_tid' => $this->getEditItem('alternate_tid'),
            'alternate_tid_select' => $allowedAltTopicList,
            'show_topic_icon_checked' => $this->show_topic_icon == 1 ? ' checked="checked" ' : '',
            'featured' => $this->getEditItem('featured'),
            'featured_select' => $featured_options_data,
            'frontpage_date' => $this->getEditItem('frontpage_date'),
            'commentcode' => $this->getEditItem('commentcode'),
            'trackbackcode' => $this->getEditItem('trackbackcode'),
            'date' => $this->getEditItem('date'),
            'comment_expire' => $this->getEditItem('comment_expire'),
            'comment_expire_checked' => $this->cmt_close_flag == 1 ? ' checked="checked" ' : '',
            'attribution_url' => $this->getEditItem('attribution_url'),
            'attribution_name' => $this->getEditItem('attribution_name'),
            'attribution_author' => $this->getEditItem('attribution_author'),
        ));

// images
        if ($_CONF['maximagesperarticle'] > 0) {
            $existingImages = 0;

            // build the existing images
            $T->set_block('editor','article_images','ai');

            $stmt = $db->conn->executeQuery(
                        "SELECT * FROM `{$_TABLES['article_images']}` WHERE ai_sid = ?",
                        array($this->sid),
                        array(Database::STRING)
                    );

            if ($stmt) {
                $imageRows = $stmt->fetchAll(Database::ASSOCIATIVE);
                $existingImages = count($imageRows);
                $counter = 1;
                foreach ($imageRows AS $row) {
                    $T->set_var('counter', $counter);
                    $T->set_var('ai_img_num', $row['ai_img_num']);
                    $T->set_var('ai_filename', $row['ai_filename']);
                    $T->set_var('ai_link',
                        COM_createLink(
                            $row['ai_filename'], $_CONF['site_url'].'/images/articles/'.$row['ai_filename']
                        )
                    );
                    if ($_CONF['keep_unscaled_image'] == 1) {
                        $originalFilename = substr_replace($row['ai_filename'], '_original.',
                                                strrpos($row['ai_filename'], '.'), 1);
                        $T->set_var('ai_image_url',$_CONF['site_url'].'/images/articles/'.$originalFilename);
                    } else {
                        $T->set_var('ai_image_url',$_CONF['site_url'].'/images/articles/'.$row['ai_filename']);
                    }
                    $counter++;
                    $T->parse('ai','article_images',true);
                }
            }
            // additional remaining open image block
            $additionalImages = $_CONF['maximagesperarticle'] - $existingImages;
            $T->set_block('editor','article_images_input','aii');
            for ($i = $existingImages + 1; $i <= $_CONF['maximagesperarticle']; $i++) {
                $T->set_var('counter',$counter);
                $counter++;
                $T->parse('aii','article_images_input',true);
            }

            if ($_CONF['allow_user_scaling'] == 1) {
                $T->set_var('allow_user_scaling', true);
            } else {
                $T->unset_var('allow_user_scaling');
            }
        }

        $T->set_var(array(
            'story_image'   => $this->getEditItem('story_image'),
            'story_video'   => $this->getEditItem('story_video'),
            'sv_autoplay_checked' => $this->sv_autoplay == 1 ? ' checked="checked" ' : '',
        ));

// archive options
        $T->set_var(array(
            'expire_checked' => $this->statuscode != 0 ? ' checked="checked" ' : '',
            'statuscode_auto_archive_checked' => $this->statuscode == 10 ? ' checked="checked" ' : '',
            'statuscode_auto_delete_checked'  => $this->statuscode == 11 ? ' checked="checked" ' : '',
            'expire' => $this->getEditItem('expire'),
        ));

// permissions
    // author
    // owner
    // group
    // perms array

        // set data vars
        $T->set_var(array(
            'story_author'          => COM_getDisplayName($this->uid),
            'story_author_select'   => COM_optionList($_TABLES['users'], 'uid,username',$this->uid),
            'author'                => COM_getDisplayName($this->uid),
            'story_uid'             => $this->getEditItem($this->uid),
        ));

        $storyauthor = COM_getDisplayName($this->uid);
        $storyauthor_select= COM_optionList($_TABLES['users'], 'uid,username',$this->uid);
        $T->set_var('story_author_select',$storyauthor_select);

        $ownername = COM_getDisplayName ($this->owner_id);
        if ( SEC_hasRights('story.edit') ) {
            $T->set_var('owner_dropdown',COM_buildOwnerList('owner_id',$this->owner_id));
        } else {
            $ownerInfo = '<input type="hidden" name="owner_id" value="'.$this->owner_id.'">'.$ownername;
            $T->set_var('owner_dropdown',$ownerInfo);
        }

        if ( SEC_inGroup($this->group_id)) {
            $T->set_var('group_dropdown',SEC_getGroupDropdown ($this->group_id, 3));
        } else {
            $grpddown = '<input type="hidden" name="group_id" value="'.$this->group_id.'"/>';
            $grpddown .= $db->getItem($_TABLES['groups'],'grp_name',array('grp_id'=>$this->group_id),array(Database::INTEGER));
            $T->set_var('group_dropdown',$grpddown);
        }

        $T->set_var('permissions_editor', SEC_getPermissionsHTML(
            $this->perm_owner,$this->perm_group,
            $this->perm_members, $this->perm_anon));

// preview
//@TODO - Language Translate
        if (empty($this->introtext) && (empty($this->bodytext))) {
            $T->set_var('preview_content','<b>No preview has been generated. Please selec the Preview Button</b>');
        } else {
            $T->set_var('preview_content',$this->getDisplayArticle('p'));
        }

// if an existing story - allow delete
        if ($this->id !== 0) {
            $T->set_var ('delete_option',true);
            $T->set_var('lang_delete_confirm',$MESSAGE[76]);
        }

// Let plugins hook into the editor

        PLG_templateSetVars('storyeditor',$T);

// if the post mode is text (or not HTML) - remove the wysiwyg flag to the template.
        if (strtolower($this->postmode) != 'html') {
            $T->unset_var('wysiwyg');
        }

// which editor mode do we want to use?

        if ($this->advanced_editor_mode == 1) {
            $T->set_var('default_visual_editor',true);
        } else {
            $T->unset_var('default_visual_editor');
        }

// allows us to use the FileManager plugin to ckeditor

        SEC_setCookie ($_CONF['cookie_name'].'adveditor', SEC_createTokenGeneral('advancededitor'),
                       time() + 1200, $_CONF['cookie_path'],
                       $_CONF['cookiedomain'], $_CONF['cookiesecure'],false);

        $T->parse('output','editor');
        $display .= $T->finish($T->get_var('output'));

        return $display;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function addError($errorMessage)
    {
        $this->errors[] = $errorMessage;
    }

}