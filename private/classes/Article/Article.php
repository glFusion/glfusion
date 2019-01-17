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
use \glFusion\Log\Log;

class Article
{
    public const STORY_INVALID_SID       = -6;
    public const STORY_PERMISSION_DENIED = -2;
    public const STORY_EDIT_DENIED       = -3;
    public const STORY_LOADED_OK         =  1;
    public const STORY_ARCHIVE_ON_EXPIRE = 10;
    public const STORY_DELETE_ON_EXPIRE  = 11;

    public const ARTICLE_NEW  = 1;
    public const ARTICLE_EDIT = 2;

    // database elements
    // Article data elements stored in the DB

    protected $sid;
    protected $uid;
    protected $draft_flag;
    protected $tid;
    protected $alternate_tid;
    protected $story_image;
    protected $story_video;
    protected $sv_autoplay;
    protected $date;

    protected $title;             // needs filtering
    protected $subtitle;          // needs filtering
    protected $introtext;         // needs filtering, processing
    protected $bodytext;          // needs filtering, processing

    protected $hits;
    protected $rating;
    protected $votes;
    protected $numemails;
    protected $comments;
    protected $comment_expire = NULL;
    protected $trackbacks;
    protected $related;
    protected $featured;
    protected $show_topic_icon;
    protected $commentcode;
    protected $trackbackcode;
    protected $statuscode;
    protected $expire;
    protected $attribution_url;       // needs filtering
    protected $attribution_name;      // needs filtering
    protected $attribution_author;    // needs filtering
    protected $postmode = 'html';
    protected $advanced_editor_mode = true;
    protected $frontpage;
    protected $frontpage_date = NULL;

    protected $owner_id;
    protected $group_id;
    protected $perm_owner;
    protected $perm_group;
    protected $perm_members;
    protected $perm_anon;

    protected $associatedImages = null;

    // Other data elements needed to manage / handle article management

    protected $originalSid;

    protected $editMode = Article::ARTICLE_NEW;

    protected $topic;
    protected $altTopic;
    protected $topicImage;
    protected $topicDescription;
    protected $altTopicDescription;

    protected $fullname;
    protected $ownerFullName;
    protected $authorFullName;
    protected $ownerUsername;
    protected $authorUsername;

    protected $dt;

    protected $errors = array();

    /*
    * formatter object for this story
    */
    protected $format;

    /*
     * sanitizer object for this story
     */
    protected $filter;

    protected $topic_perm_owner;
    protected $topic_perm_group;
    protected $topic_perm_members;
    protected $topic_perm_anon;

    public function __construct()
    {
        global $_CONF, $_USER;

        // initialize date class for story publish date
        $this->dt = new \Date('now',$_USER['tzid']);
    }


    public function setVars(Array $storyVars = array())
    {
        foreach ($storyVars AS $varname => $value) {
            $this->set($varname,$value,$storyVars);
        }
    }


    public function delete($sid)
    {
        // delete a story from DB and then remove from all other associated items
    }

    public function save($sid)
    {
        // insert new story in DB - checking for duplicate SID
    }

    public function update($sid)
    {
        // save an existing story to DB - checking for updated SID
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

        if ($this->checkTopicAccess()) {
            return $this::STORY_LOADED_OK;
        }

        return $this::STORY_PERMISSION_DENIED;
    }

    public function retrieveArticleFromVars($args)
    {
        global $_TABLES;

        // verify story permission first
        $access = SEC_hasAccess(
                    $args['owner_id'],
                    $args['group_id'],
                    $args['perm_owner'],
                    $args['perm_group'],
                    $args['perm_members'],
                    $args['perm_anon']
                  );

        if ($access === 0) {
            return STORY_PERMISSION_DENIED;
        }

        $this->setVars($args);

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

            if ($access > 0) {
                $access = true;
            }
        }

        if ($access === false) {
            return false;
        }
        return true;
    }


    // static methods
    public static function incrementCommentCounter($sid)
    {
        // increment comment counter for the passed SID
        // do we really need this?

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
                $lFilename_large = substr_replace($image, '_original.',
                                        strrpos($image, '.'), 1);

                $lFilename_large_complete = $_CONF['path_images'] . 'articles/' .
                                                $lFilename_large;

                // We need to map that filename to the right location
                // or the fetch script:
                if ($stdImageLoc) {
                    $lFilename_large_URL = $_CONF['site_url'] . '/' . $imgpath .
                                            'articles/' . $lFilename_large;
                } else {
                    $lFilename_large_URL = $_CONF['site_url'] .
                                            '/getimage.php?mode=show&amp;image=' .
                                            $lFilename_large;
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
            $sql = "SELECT ai_filename FROM `{$_TABLES['article_images']}`
                        WHERE ai_sid = ? ORDER BY ai_img_num";

            $stmt = $db->conn->executeQuery(
                        $sql,
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
        switch ($varname) {
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
                return $this->date;
                break;

            case 'iso8601_date' :
            case 'story_publish_date_iso8601' :
                return $this->dt->toISO8601(true);
                break;

            case 'unixdate' :
            case 'story_publish_date_unix' :
                return $this->dt->toUnix();
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
                return $this->comment_expire;
                break;

            case 'comment_expire_date_unix' :
                $dtCmtExpire = new \Date($this->comment_expire,$_USER['tzid']);
                return $dtCmtExpire->toUnix();
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
                return $this->expire;
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
                return $this->frontpage_date;
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

            default :
                if (isset($this->{$varname})) {
                    return $this->{$varname};
                }
                break;
        }
    }


    /*
     * Brains of the operation - here we set the objects properties
     * and do all necessary conversions, setup depending on the source
     * of the data (i.e.; DB or $_POST).
     */
    public function set($varname, $value, ARRAY $storyVars = array())
    {
        global $_CONF, $_USER, $_TABLES;

        // we should put some smarts in here
        // 1) validate the variable exists
        // 2) do any cleansing or processing we need to do

        if (property_exists($this,$varname)) {
            // if any special processing needs to happen
            // when a specific variable is set - do it here...

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
                    if ($this->originalSid == NULL && !isset($storyVars['old_sid'])) {
                        $this->originalSid = $value;
                    }
                    break;

                case 'old_sid' :
                    $this->originalSid = $value;
                    break;

                case 'date' :
                    $this->dt->setTimestamp(strtotime($value));
                    $this->date = $value;
                    break;

                default :
                    $this->{$varname} = $value;
                    break;
            }
        } else {
            // here we handle $_POST vars that do not map directly to the DB fields

            switch ($varname) {
                default :
                    // ignore anything we don't explicity know how to handle
                    break;
            }
        }
    }
}