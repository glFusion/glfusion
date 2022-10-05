<?php
/**
 * Implement the Internal comment engine.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2022 Lee Garner
 * @package     glfusion
 * @version     v2.1.0
 * @since       v2.1.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Comments\Internal;
use \glFusion\Database\Database;
use \glFusion\Formatter;
use \glFusion\User;


/**
 * Internal comment engine functions.
 * @package glfusion
 */
class Engine extends \glFusion\Comments\CommentEngine
{

    /**
     * Controls the entire comment section display.
     * Collects the comments for an item and displays them.
     *
     * @see     self::renderComments()
     * @return  string      HTML for the comments area
     */
    public function displayComments()
    {
        global $_CONF, $_TABLES, $_USER, $LANG03;

        $retval = '';

        $db = Database::getInstance();

        if (!COM_isAnonUser()) {
            try {
                $U = $db->conn->executeQuery(
                    "SELECT commentorder,commentmode,commentlimit
                    FROM `{$_TABLES['usercomment']}` WHERE uid = ?",
                    array($_USER['uid']),
                    array(Database::INTEGER)
                )->fetchAssociative();
            } catch (\Throwable $e) {
                Log::write('system', Log::ERROR, __METHOD__ . ': ' . $e->getMessage());
                $U = false;
            }
            if (is_array($U)) {
                // Set options if not already set.
                if (empty( $this->order)) {
                    $this->withOrder($U['commentorder']);
                }
                if (empty($this->mode)) {
                    $this->withMode($U['commentmode']);
                }
                $limit = (int)$U['commentlimit'];
            }
        } else {
            $limit = (int) $_CONF['comment_limit'];
        }

        if ($this->delete_option === NULL) {
            // Set the delete option based on the current user's moderator
            // status if not set otherwise.
            $this->delete_option = PLG_callFunctionForOnePlugin(
                'plugin_ismoderator_' . $this->type
            );
        }

        if( !is_numeric($this->page) || $this->page < 1 ) {
            $this->page = 1;
        } else {
            $this->page = (int) $this->page;
        }

        $start = (int) $limit * ( $this->page - 1 );

        $T = new \Template( $_CONF['path_layout'] . 'comment' );
        $T->set_file( array( 'commentarea' => 'startcomment.thtml' ));
        if ( $this->mode != 'nobar' ) {
            $CommentBar = new CommentBar;
            $T->set_var(
                'commentbar',
                $CommentBar->withSid($this->sid)
                           ->withTitle($this->title)
                           ->withType($this->type)
                           ->withOrder($this->order)
                           ->withMode($this->mode)
                           ->withCommentCode($this->ccode)
                           ->render()
            );
        }
        $T->set_var( 'sid', $this->sid );
        $T->set_var( 'comment_type', $this->type );

        if ($this->mode == 'nested' || $this->mode == 'flat') {
            // build query
            $Coll = new CommentCollection;
            $Coll->withType($this->type)
                 ->withSid($this->sid)
                 ->withLimit($start, $limit)
                 ->withMode($this->mode);
            switch( $this->mode ) {
            case 'flat':
                if ($this->cid) {
                    $count = 1;
                } else {
                    $count = $db->conn->executeQuery(
                        "SELECT COUNT(*) FROM `{$_TABLES['comments']}` WHERE sid=? AND type=? AND queued=0",
                        array($this->sid, $this->type),
                        array(Database::STRING, Database::STRING)
                    )->fetchOne();
                    $Coll->withOrderBy('c.date', $this->order);
                }
                break;

            case 'nested':
            default:
                if( $this->order == 'DESC' ) {
                    $Coll->withOrderBy('c.rht', 'DESC');
                } else {
                    $Coll->withOrderBy('c.lft', 'ASC');
                }
                $Coll->withGroupBy('c.cid');

                // We can simplify the query, and hence increase performance
                // when pid = 0 (when fetching all the comments for a given sid)
                if ( $this->cid ) {  // pid refers to commentid rather than parentid
                    // count the total number of applicable comments
                    $count = $db->conn->executeQuery(
                        "SELECT COUNT(*)
                        FROM `{$_TABLES['comments']}` AS c, `{$_TABLES['comments']}` AS c2
                        WHERE c.queued = 0 AND c.sid = ? AND (c.lft >= c2.lft AND c.lft <= c2.rht)
                        AND c2.cid = ? AND c.type = ?",
                        array($this->sid, $this->pid, $this->type),
                        array(Database::STRING, Database::INTEGER, Database::STRING)
                    )->fetchOne();
                    $count = (int)$count;
                } else {
                    // pid refers to parentid rather than commentid
                    if( $this->pid == 0 ) {
                        // the simple, fast case, count the total number of applicable comments
                        $count = $db->conn->executeQuery(
                            "SELECT COUNT(*) FROM `{$_TABLES['comments']}`
                            WHERE sid=? AND type=? AND queued=0",
                            array($this->sid, $this->type),
                            array(Database::STRING, Database::STRING)
                        )->fetchOne();
                    } else {
                        // count the total number of applicable comments
                        $count = $db->conn->executeQuery(
                            "SELECT COUNT(*)
                            FROM `{$_TABLES['comments']}` AS c, `{$_TABLES['comments']}` AS c2
                            WHERE c.queued = 0
                                AND c.sid = ?
                                AND (c.lft > c2.lft AND c.lft < c2.rht)
                                AND c2.cid = ?
                                AND c.type=?",
                            array($this->sid, $this->pid, $this->type),
                            array(Database::STRING, Database::INTEGER, Database::STRING)
                        )->fetchOne();
                    }
                }
                break;
            }

            // Actually get all the comments and insert into the template.
            $Coll->execute()->getObjects();     // execute and load comments
            $thecomments = $this->renderComments($Coll);
            if (empty($thecomments)) {
                if ( $this->ccode == self::ENABLED ) {
                    $T->set_var( 'lang_be_the_first',$LANG03[51]);
                }
            } else {
                $T->set_var( 'comments', $thecomments );
            }

            // Pagination
            $tot_pages =  ceil( $count / $limit );

            if( $this->type == 'article' ) {
                $pLink = $_CONF['site_url'] . "/article.php?story={$this->sid}&amp;type={$this->type}&amp;order={$this->order}&amp;mode={$this->mode}";
                $pageStr = 'page=';
            } else {        // plugin
                // Link to plugin defined link or lacking that a generic link
                // that the plugin should support (hopefully)
                $parts = PLG_getCommentUrlId($this->type);
                var_dump($parts);die;
                if (is_array($parts)) {
                    $pLink = "{$parts[0]}?{$parts[1]}={$this->sid}" .
                        "&amp;type={$this->type}&amp;order={$this->order}&amp;mode={$this->mode}";
                    if (isset($parts[2]) && !empty($parts[2])) {
                        $pageStr = $parts[2];
                    } else {
                        $pageStr = 'page=';
                    }
                }
            }
            $T->set_var(
                'pagenav',
                COM_printPageNavigation($pLink, $this->page, $tot_pages, $pageStr, false, '', '', '#comments')
            );
        }
        $retval = $T->finish($T->parse('output', 'commentarea'));
        return $retval;
    }


    /**
     * This function prints &$comments (db results set of comments) in comment format
     * -For previews, &$comments is assumed to be an associative array containing
     *  data for a single comment.
     */
    public function renderComments(CommentCollection $Coll) : string
    {
        global $_CONF, $_TABLES, $_USER, $LANG01, $LANG03, $MESSAGE, $_IMAGE_TYPE;

        $indent = 0;  // begin with 0 indent
        $retval = ''; // initialize return value
        static $userInfo = array();

        $T = new \Template($_CONF['path_layout'] . 'comment');
        $T->set_file(array(
            'comment' => 'comment.thtml',
            //'thread'  => 'thread.thtml'
        ) );

        // generic template variables
        $T->set_var( 'lang_authoredby', $LANG01[42] );
        $T->set_var( 'lang_on', $LANG01[36] );
        $T->set_var( 'lang_permlink', $LANG01[120] );
        $T->set_var( 'order', $this->order );

        if( $this->ccode == self::ENABLED && (!self::loginRequired())) {
            $T->set_var( 'lang_replytothis', $LANG01[43] );
            $T->set_var( 'lang_reply', $LANG01[25] );
        } else {
            $T->set_var( 'lang_replytothis', '' );
            $T->set_var( 'lang_reply', '' );
        }

        // Make sure we have a default value for comment indentation
        if( !isset( $_CONF['comment_indent'] )) {
            $_CONF['comment_indent'] = 25;
        }

        // Build the comment data record

/*
        //@TODO - appears if $preview is true- we pass the results instead of the result set
        if ($this->preview) {
            if ( isset($comments['comment_text'])) {
                $comments['comment'] = $comments['comment_text'];
            }

            $A = $comments;
            if( empty( $A['nice_date'] )) {
                $A['nice_date'] = time();
            }
            if( !isset( $A['cid'] )) {
                $A['cid'] = 0;
            }
            if( !isset( $A['photo'] )) {
                if( isset( $_USER['photo'] )) {
                    $A['photo'] = $_USER['photo'];
                } else {
                    $A['photo'] = '';
                }
            }
            if (! isset($A['email'])) {
                if (isset($_USER['email'])) {
                    $A['email'] = $_USER['email'];
                } else {
                    $A['email'] = '';
                }
            }
            $A['name'] = $A['username'];
            $this->mode = 'flat';
            $T->set_var('preview_mode',true);
            $resultSet[] = $A;
        } else {
            //@TODO - $comments is passed from another function -
            //          really isn't a good practice -
            $resultSet = $comments->fetchAll(Database::ASSOCIATIVE);
            $T->unset_var('preview_mode');
        }
 */
        $resultSet = $Coll->getComments();
        if (count($resultSet) == 0 ) {
            return '';
        }

        // initialize our format class

        $filter = \sanitizer::getInstance();
        $filter->setNamespace('glfusion','comment');
        $filter->setPostmode('text');

        $format = new Formatter();
        $format->setNamespace('glfusion');
        $format->setAction('comment');
        $format->setAllowedHTML($_CONF['htmlfilter_comment']);
        $format->setParseAutoTags(true);
        $format->setProcessBBCode(false);
        $format->setCensor(true);
        $format->setProcessSmilies(true);

        $token = '';
        if ($this->delete_option && !$this->preview) {
            $token = SEC_createToken();
        }

        $row = 1;

        // Using an array instead of a Comment object since we'll be
        // pushing values into it that don't exist for the comment, such as
        // signature and photo.
        foreach ($resultSet as $A) {
            if (!isset($A['postmode']) || empty($A['postmode'])) {
                //get format mode
                if ( preg_match( '/<.*>/', $A['comment'] ) != 0 ) {
                    $postmode = 'html';
                } else {
                    $postmode = 'plaintext';
                }
            } else {
                $postmode = $A['postmode'];
            }

            //remove any old signatures signature
            $pos = strpos( $A['comment'],'<!-- COMMENTSIG --><div class="comment-sig">');
            if ( $pos > 0) {
                $A['comment'] = substr($A['comment'], 0, $pos);
            } else {
                $pos = strpos( $A['comment'],'<p>---<br />');
                if ( $pos > 0 ) {
                    $A['comment'] = substr($A['comment'], 0, $pos);
                }
            }

            // get user information for this comment author
            if ( !isset($A['uid']) || empty($A['uid'])) {
                $A['uid'] = 1;
            }

            if ( $A['uid'] > 1 ) {
                $User = User::getInstance($A['uid']);
                $A['username'] = $User->username;
                $A['remoteusername'] = $User->remoteusername;
                $A['remoteservice'] = $User->remoteservice;
                $A['fullname'] = $User->fullname;
                $A['sig'] = $User->sig;
                $A['photo'] = $User->photo;
            }
            $format->setType($postmode);

            $commentFooter = '';

            // fixes previous encodings...
            if ( $postmode == 'plaintext') {
                $A['comment'] = htmlspecialchars_decode($A['comment']);
                $postmode = 'text';
            }

            $T->unset_var('delete_link');
            $T->unset_var('ipaddress');
            $T->unset_var('reply_link');
            $T->unset_var('edit_link');

            $db = Database::getInstance();
            //check for comment edit
            $B = $db->conn->executeQuery(
                "SELECT cid,uid,UNIX_TIMESTAMP(time) as time
                FROM `{$_TABLES['commentedits']}` WHERE cid = ?",
                array($A['cid']),
                array(Database::INTEGER)
            )->fetchAssociative();
            if ($B) { //comment edit present
                //get correct editor name
                if ($A['uid'] == $B['uid']) {
                    $editname = $A['username'];
                } else {
                    $editname = $db->conn->executeQuery(
                        "SELECT username FROM `{$_TABLES['users']}` WHERE uid = ?",
                        array($B['uid']),
                        array(Database::INTEGER)
                    )->fetchOne();
                }
                //add edit info to text
                $dtObject = new \Date($B['time'],$_USER['tzid']);

                $commentFooter .= LB . '<div class="comment-edit">' . $LANG03[30] . ' '
                                          . $dtObject->format($_CONF['date'],true) . ' ' . $LANG03[31] . ' '
                                          . $editname . '</div><!-- /COMMENTEDIT -->';
            }

            // determines indentation for current comment
            if($this->mode == 'nested') {
                $indent = ($A['indent'] - $A['pindent']) * $_CONF['comment_indent'];
            }

            // comment variables
            $T->set_var(array(
                'indent'        => $indent,
                'author_name'   => $filter->sanitizeUsername($A['username']),
                'author_id'     => $A['uid'],
                'cid'           => $A['cid'],
                'pid'           => $A['pid'],
                'cssid'         => $row % 2,
                'author_match'  => $this->sid_author_id > 1 && $this->sid_author_id == $A['uid'],
            ) );

            if ($A['uid'] > 1) {
                $fullname = COM_getDisplayName(
                    $A['uid'], $A['username'], isset($A['fullname']) ? $A['fullname'] : ''
                );
                $T->set_var( 'author_fullname', $fullname );
                $T->set_var( 'author', $fullname );
                $alttext = $fullname;

                $photo = '';
                if ($_CONF['allow_user_photo']) {
                    if (isset ($A['photo']) && empty ($A['photo'])) {
                        $A['photo'] = '';
                    }

                    $photo = $User->getPhoto();
                    $photo_raw = $User->getPhoto(64, 0);
                    if (!empty($photo)) {
                        $T->set_var('author_photo', $photo);
                        $T->set_var('author_photo_raw',$photo_raw);
                        $camera_icon = '<img src="' . $_CONF['layout_url']
                            . '/images/smallcamera.' . $_IMAGE_TYPE . '" alt=""/>';
                        $T->set_var(
                            'camera_icon',
                            COM_createLink(
                                $camera_icon,
                                $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' . $A['uid']
                            )
                        );
                    } else {
                        $T->set_var( 'author_photo', '<img src="'.$_CONF['default_photo'].'" alt="" class="userphoto"/>' );
                        $T->set_var( 'author_photo_raw', $_CONF['default_photo']);
                        $T->set_var( 'camera_icon', '' );
                    }
                } else {
                    $T->set_var( 'author_photo_raw', '' );
                    $T->set_var( 'author_photo', '' );
                    $T->set_var( 'camera_icon', '' );
                }

                $T->set_var( 'start_author_anchortag', '<a href="'
                    . $_CONF['site_url'] . '/users.php?mode=profile&amp;uid='
                    . $A['uid'] . '">' );
                $T->set_var( 'end_author_anchortag', '</a>' );
                $T->set_var( 'author_link',
                    COM_createLink(
                        $fullname,
                        $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' . $A['uid']
                    )
                );
                $T->set_var( 'author_url', $_CONF['site_url'] . '/users.php?mode=profile&amp;uid=' . $A['uid']);
            } else {
                // anonymous user
                $username = $filter->sanitizeUsername($A['name']);
                if ( $username == '' ) {
                    $username = $LANG01[24];
                }

                $T->set_var( 'author', $username);
                $T->set_var( 'author_fullname', $username);
                $T->set_var( 'author_link', @htmlspecialchars($username,ENT_COMPAT,COM_getEncodingt() ));
                $T->unset_var( 'author_url');

                if( $_CONF['allow_user_photo'] ) {
                    $T->set_var( 'author_photo_raw', $_CONF['default_photo'] );
                    $T->set_var( 'author_photo', '<img src="'.$_CONF['default_photo'].'" alt="" class="userphoto"/>' );
                    $T->set_var( 'camera_icon', '' );
                } else {
                    $T->set_var( 'author_photo_raw', '' );
                    $T->set_var( 'author_photo', '' );
                    $T->set_var( 'camera_icon', '' );
                }
                $T->set_var( 'start_author_anchortag', '' );
                $T->set_var( 'end_author_anchortag', '' );
            }

            // hide reply link from anonymous users if they can't post replies
            $hidefromanon = false;
            if (self::loginRequired()) {
                $hidefromanon = true;
            }

            // this will hide HTML that should not be viewed in preview mode
            if( $this->preview || $hidefromanon ) {
                $T->set_var( 'hide_if_preview', 'style="display:none"' );
            } else {
                $T->set_var( 'hide_if_preview', '' );
            }

            $dtObject = new \Date($A['nice_date'],$_USER['tzid']);

            $T->set_var( 'date', $dtObject->format($_CONF['date'],true));
            $T->set_var( 'iso8601_date', $dtObject->toISO8601() );
            $T->set_var( 'sid', $A['sid'] );
            $T->set_var( 'type', $A['type'] );

            //COMMENT edit rights
            $edit_type = 'edit'; // normal user edit
            if (!COM_isAnonUser()) {
                if ( $_USER['uid'] == $A['uid'] && $_CONF['comment_edit'] == 1
                    && ($_CONF['comment_edittime'] == 0 || ((time() - $A['nice_date']) < $_CONF['comment_edittime'] )) &&
                    $this->ccode == self::ENABLED &&
                    ($db->conn->fetchColumn("SELECT COUNT(*) FROM `{$_TABLES['comments']}`
                        WHERE queued=0 AND pid=?",array($A['cid']),0,array(Database::INTEGER)) == 0)) {
                    $edit_option = true;
                } else if (SEC_hasRights('comment.moderate') ) {
                    $edit_option = true;
                    $edit_type   = 'adminedit';
                } else {
                    $edit_option = false;
                }
            } else {
                $edit_option = false;
            }

            //edit link
            if ($edit_option && !$this->preview) {
                $editlink = $_CONF['site_url'] . '/comment.php?mode='.$edit_type.'&amp;cid='
                    . $A['cid'] . '&amp;sid=' . $A['sid'] . '&amp;type=' . $this->type
                    . '#comment_entry';
                $T->set_var('edit_link',$editlink);
                $T->set_var('lang_edit',$LANG01[4]);
                $edit = COM_createLink( $LANG01[4], $editlink) . ' | ';
            } else {
                $editlink = '';
                $edit = '';
            }

            // If deletion is allowed, displays delete link
            if( $this->delete_option && !$this->preview) {
                $deloption = '';
                if ( SEC_hasRights('comment.moderate') ) {
                    if( !empty( $A['ipaddress'] ) ) {
                        if( empty( $_CONF['ip_lookup'] )) {
                            $deloption = $A['ipaddress'] . '  | ';
                            $T->set_var('ipaddress', $A['ipaddress']);
                        } else {
                            $iplookup = str_replace( '*', $A['ipaddress'], $_CONF['ip_lookup'] );
                            $T->set_var('iplookup_link', $iplookup);
                            $T->set_var('ipaddress', $A['ipaddress']);
                            $deloption = COM_createLink($A['ipaddress'], $iplookup) . ' | ';
                        }
                        //insert re-que link here
                    }
                }
                $dellink = $_CONF['site_url'] . '/comment.php?mode=delete&amp;cid='
                    . $A['cid'] . '&amp;sid=' . $A['sid'] . '&amp;type=' . $this->type
                    . '&amp;' . CSRF_TOKEN . '=' . $token;
                $delattr = array('onclick' => "return confirm('{$MESSAGE[76]}');");

                $delete_link = $dellink;
                $T->set_var('delete_link',$delete_link);
                $T->set_var('lang_delete_link_confirm',$MESSAGE[76]);
                $T->set_var('lang_delete',$LANG01[28]);
                $deloption .= COM_createLink( $LANG01[28], $dellink, $delattr) . ' | ';
                $T->set_var( 'delete_option', $deloption . $edit);
            } else if ( $edit_option) {
                $T->set_var( 'delete_option', $edit );
            } elseif (! COM_isAnonUser()) {
                $reportthis = '';
                if ($A['uid'] != $_USER['uid']) {
                    $reportthis_link = $_CONF['site_url']
                        . '/comment.php?mode=report&amp;cid=' . $A['cid']
                        . '&amp;type=' . $this->type;
                    $report_attr = array('title' => $LANG01[110]);
                    $T->set_var('report_link',$reportthis_link);
                    $T->set_var('lang_report',$LANG01[109]);
                    $reportthis = COM_createLink($LANG01[109], $reportthis_link,
                                             $report_attr) . ' | ';
                }
                $T->set_var( 'delete_option', $reportthis );
            } else {
                $T->set_var( 'delete_option', '' );
            }

            //and finally: format the actual text of the comment, but check only the text, not sig or edit

            $text = str_replace('<!-- COMMENTSIG --><div class="comment-sig">', '', $A['comment']);
            $text = str_replace('</div><!-- /COMMENTSIG -->', '', $text);
            $text = str_replace('<div class="comment-edit">', '', $text);
            $A['comment'] = str_replace('</div><!-- /COMMENTEDIT -->', '', $text);

            // create a reply to link
            $reply_link = '';
            if ($this->ccode == self::ENABLED && !self::loginRequired()) {
                $reply_link = $_CONF['site_url'] . '/comment.php?sid=' . $A['sid']
                        . '&amp;pid=' . $A['cid'] . '&amp;title='
                        . urlencode($A['title']) . '&amp;type=' . $A['type']
                        . '#comment_entry';
                $T->set_var('reply_link',$reply_link);
                $T->set_var('lang_reply',$LANG01[43]);
                $reply_option = COM_createLink($LANG01[43], $reply_link,
                                           array('rel' => 'nofollow')) . ' | ';
                $T->set_var('reply_option', $reply_option);
            } else {
                $T->set_var( 'reply_option', '' );
            }
            $T->set_var( 'reply_link', $reply_link );

            // format title for display, must happen after reply_link is created
            $A['title'] = @htmlspecialchars( $A['title'],ENT_COMPAT,COM_getEncodingt() );

            $T->set_var( 'title', $A['title'] );

            // add signature if available
            $sig = isset($A['sig']) ? $filter->censor($A['sig']) : '';
            $finalsig = '';
            if ($A['uid'] > 1 && !empty($sig)) {
                $finalsig .= '<div class="comment-sig">';
                $finalsig .= nl2br('---' . LB . $sig);
                $finalsig .= '</div>';
            }

            // sanitize and format comment
            $A['comment'] = $format->parse($A['comment']);

            // highlight search terms if specified. After formatting to avoid introducing
            // strange comment fields
            if( !empty( $_REQUEST['query'] )) {
                $A['comment'] = COM_highlightQuery( $A['comment'], strip_tags($_REQUEST['query']) );
            }

            $T->set_var( 'comments',  $A['comment'].$finalsig.$commentFooter);

            // parse the templates. Note that 'threaded' is not a valid mode.
            /*if(($this->mode == 'threaded') && $indent > 0) {
                $T->set_var('pid', $A['pid']);
                $retval .= $T->parse('output', 'thread');
            } else {*/
                $T->set_var('pid', $A['cid']);
                $retval .= $T->parse('output', 'comment');
            //}
            if ($this->preview) {
                // only comment to show
                return $retval;
            }
            $row++;
        }
        unset($format);
        return $retval;
    }


    /**
     * Get a link to the comment display, with the number of comments.
     *
     * @param   string  $type       Item type
     * @param   string  $sid        Item ID
     * @param   string  $url        URL to comment display
     * @param   integer $cmtCount   Optional number of comments
     */ 
    public function getLinkWithCount(string $type, string $sid, string $url, ?int $cmtCount = NULL) : array
    {
        global $_TABLES, $LANG01;

        if ($cmtCount === NULL) {
            $cmtCount = (int)Database::getInstance()->getCount(
                $_TABLES['comments'],
                array('type', 'sid'),
                array($type, $sid),
                array(Database::STRING, Database::STRING)
            );
        }

        $link = '<a href="'.$url.'#comments">';
        $retval = array(
            'url'           => $url,
            'url_extra'     => '',
            'link'          => $link,
            'nonlink'       => '',
            'comment_count' => $cmtCount . ' '. $LANG01[83],
            'comment_text'  => $LANG01[83],
            'link_with_count' => $link . ' ' . $cmtCount . ' ' . $LANG01[83].'</a>',
        );
        return $retval;
    }
}

