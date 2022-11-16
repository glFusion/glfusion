<?php
/**
 * Class to create a comment entry form.
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
use \glFusion\Cache\Cache;
use \glFusion\Formatter;
use \glFusion\Log\Log;
use \glFusion\FieldList;
use \glFusion\Comments\CommentEngine;


/**
 * Create a comment entry form.
 * @package glfusion
 */
class CommentForm
{
    /** Editing mode (adminedit, previewedit, etc.)
     * @var string */
    private $mode = '';

    /** Record ID of the comment being edited. 0 indicates a new submission.
     * @var integer */
    private $cid = 0;

    /** item id.
     * @var string */
    private $sid = '';

    /** Item title.
     * @var string */
    private $title = '';

    /** Item type, e.g. plugin name.
     * @var string */
    private $type = 'article';

    /** Post mode.
     * @var string */
    private $postmode = '';

    /** Parent comment ID, when replying.
     * @var string */
    private $pid = 0;

    /** Comment object being edited.
     * @var object */
    private $Comment = NULL;


    /**
     * Set the comment record ID and load a Comment object.
     *
     * @param   integer $cid    Comment ID
     * @return  object  $this
     */
    public function withCid(int $cid) : self
    {
        $this->cid = $cid;
        $this->Comment = Comment::getByCid($cid);
        return $this;
    }


    /**
     * Load the Comment from an existing object and set the CID from it.
     *
     * @param   object  $Comment    Comment object
     * @return  object  $this
     */
    public function withComment(Comment $Comment) : self
    {
        $this->Comment = $Comment;
        $this->cid = $this->Comment->getCid();
        return $this;
    }


    /**
     * Set the editing mode.
     *
     * @param   string  $mode   Edit mode
     * @return  object  $this
     */
    public function withMode(string $mode) : self
    {
        $this->mode = $mode;
        return $this;
    }


    /**
     * Set the story/item ID.
     *
     * @param   string  $sid    Item ID
     * @return  object  $this
     */
    public function withSid(string $sid) : self
    {
        $this->sid = $sid;
        return $this;
    }


    /**
     * Set the item type or plugin name.
     *
     * @param   string  $type   Item type
     * @return  object  $this
     */
    public function withType(string $type) : self
    {
        $this->type = $type;
        return $this;
    }


    /**
     * Set the comment's parent ID.
     *
     * @param   integer $pid    Parent record ID
     * @return  object  $this
     */
    public function withPid(int $pid) : self
    {
        $this->pid = $pid;
        return $this;
    }


    /**
     * Set the item title string.
     *
     * @param   string  $title  Item title
     * @return  object  $this
     */
    public function withTitle(string $title) : self
    {
        $this->title = $title;
        return $this;
    }


    /**
     * Displays the comment form.
     *
     * @return  string      HTML for editing form
     */
    public function render() : string
    {
        global $_CONF, $_TABLES, $_USER, $LANG03, $LANG12, $LANG_LOGIN, $LANG_ACCESS, $LANG_ADMIN;

        $retval         = '';
        $cid            = 0;
        $edit_comment   = '';
        $moderatorEdit  = false;
        $adminEdit      = false;

        // bail if anonymous user and we require login
        if (CommentEngine::loginRequired()) {
            $retval .= SEC_loginRequiredForm();
            return $retval;
        }

        if ($this->Comment === NULL) {
            // Instantiate a comment if the Cid or Comment was not specified
            $this->Comment = new Comment;
            $this->cid = 0;
        }

        $postmode = $this->Comment->getPostmode();

        $db = Database::getInstance();

        switch ($this->mode) {
        case 'modedit' :
            if (Comment::isModerator()) {
                $moderatorEdit = true;
                $_CONF['skip_preview'] = 1;
            }
            $this->mode = 'edit';
            break;
        case 'preview_edit_mod' :
            if (Comment::isModerator()) {
                $moderatorEdit = true;
                $_CONF['skip_preview'] = 1;
            }
            $this->mode = 'preview_edit';
            break;
        case 'adminedit' :
            if (Comment::isModerator()) {
                $adminEdit = true;
                $_CONF['skip_preview'] = 1;
            }
            $this->mode = 'edit';
            break;
        case 'preview_edit_admin' :
            if (Comment::isModerator()) {
                $adminEdit = true;
                $_CONF['skip_preview'] = 1;
            }
            $this->mode = 'preview_edit';
            break;
        default :
            break;
        }

        if (empty($this->postmode)) {
            $this->postmode = $_CONF['comment_postmode'];
        }

        $filter = \sanitizer::getInstance();
        $AllowedElements = $filter->makeAllowedElements($_CONF['htmlfilter_comment']);
        $filter->setAllowedelements($AllowedElements);
        $filter->setNamespace('glfusion','comment');
        $filter->setCensorData(true);
        $filter->setPostmode($postmode);

        if (COM_isAnonUser()) {
            $uid = 1;
        } else {
            $uid = $_USER['uid'];
        }
        $commentuid = $uid;

        if ( ($this->mode == 'edit' || $this->mode == 'preview_edit') && $this->cid > 0) {
            $commentuid = $this->Comment->getUid();
        }

        COM_clearSpeedlimit ($_CONF['commentspeedlimit'], 'comment');

        $last = 0;
        if (
            $this->mode != 'edit' &&
            $this->mode != 'preview' &&
            $this->mode != 'preview_new' &&
            $this->mode != 'preview_edit'
        ) {
            //not edit mode or preview changes
            $last = COM_checkSpeedlimit ('comment');
        }
        if ($last > 0) {
            $retval .= COM_showMessageText($LANG03[7].$last.sprintf($LANG03[8],$_CONF['commentspeedlimit']),$LANG12[26],false,'error');
            return $retval;
        }

        // Preview mode:
        if (
            (
                $this->mode == 'preview' ||
                $this->mode == 'preview_new' ||
                $this->mode == 'preview_edit'
            ) && !empty($this->Comment->getComment())
        ) {
            $start = new \Template( $_CONF['path_layout'] . 'comment' );
            $start->set_file( array( 'comment' => 'startcomment.thtml' ));
            $start->set_var( 'hide_if_preview', 'style="display:none"' );

            // Clean up all the vars
            $A = array();
            foreach ($_POST as $key => $value) {
                if (($key == 'pid') || ($key == 'cid')) {
                    $A[$key] = (int) COM_applyFilter ($_POST[$key], true);
                } else if (($key == 'title') || ($key == 'comment') || ($key == 'comment_text')) {
                    $A[$key] = $_POST[$key];
                } else if ($key == 'username') {
//@@ we probably don't need to do this here
//   since we really want the raw data
//   we can do the filtering on display or edit below
                    $A[$key] = @htmlspecialchars(COM_checkWords(strip_tags($_POST[$key])),ENT_QUOTES,COM_getEncodingt());
                    $A[$key] = USER_uniqueUsername($A[$key]);
                } else {
                    $A[$key] = COM_applyFilter($_POST[$key]);
                }
            }

            //correct time and username for edit preview
            if ($this->mode == 'preview' || $this->mode == 'preview_new' || $this->mode == 'preview_edit') {
                $A['nice_date'] = $this->Comment->getDate();

                // not an anonymous user
                /*if ( $commentuid > 1 ) {
                    // get username from DB - we don't allow
                    // logged-in-users to set or change their name
                    $A['username'] = $db->conn->fetchColumn(
                        "SELECT username FROM `{$_TABLES['users']}` WHERE uid=?",
                        array($commentuid),
                        0,
                        array(Database::INTEGER)
                    );
                } else {
                    // we have an anonymous user - so $_POST['username'] should be set
                    // already from above
                }*/

            }
            if (empty ($A['username'])) {
                $A['username'] = $db->conn->fetchColumn(
                    "SELECT username FROM `{$_TABLES['users']}` WHERE uid=?",
                    array($commentuid),
                    0,
                    array(Database::INTEGER)
                );
            }

            $author_id = PLG_getItemInfo($this->Comment->getType(), $this->Comment->getSid(), 'author');
            $A['comment'] = $A['comment_text'];

            // Create the preview of this comment. Use the CommentCollection
            // but manually supply the comment.
            $Comment = Comment::fromArray($A);
            $Coll = new CommentCollection;
            $Coll->pushComment($Comment);
            $thecomments = CommentEngine::getEngine()->renderComments($Coll);
            $start->set_var( 'comments', $thecomments );
            $retval .= '<a name="comment_entry"></a>';
            $retval .= COM_startBlock ($LANG03[14])
                . $start->finish( $start->parse( 'output', 'comment' ))
                . COM_endBlock ();
        } else if ($this->mode == 'preview_new' || $this->mode == 'preview_edit') {
            $retval .= COM_showMessageText($LANG03[12],$LANG03[17],true,'error');
            $this->mode = 'error';
        }

        $T = new \Template($_CONF['path_layout'] . 'comment');
        $T->set_file('form','commentform.thtml');

        if ($this->mode == 'preview_new' ) {
            $T->set_var('mode','new');
            $T->set_var('show_anchor','');
        } else if ($this->mode == 'preview_edit' ) {
            $T->set_var('mode','edit');
            $T->set_var('show_anchor','');
        } else {
            $T->set_var('mode',$this->mode);
            $T->set_var('show_anchor',1);
        }
        $T->set_var('start_block_postacomment', COM_startBlock($LANG03[1]));
        if ($_CONF['show_fullname'] == 1) {
            $T->set_var('lang_username', $LANG_ACCESS['name']);
        } else {
            $T->set_var('lang_username', $LANG03[5]);
        }
        $T->set_var('sid', $this->Comment->getSid());
        $T->set_var('pid', $this->Comment->getPid());
        $T->set_var('type', $this->Comment->getType());

        if ($this->mode == 'edit' || $this->mode == 'preview_edit') { //edit modes
        	$T->set_var('start_block_postacomment', COM_startBlock($LANG03[41]));
            $T->set_var('cid', '<input type="hidden" name="cid" value="' .
                @htmlspecialchars(COM_applyFilter($this->cid),ENT_COMPAT,COM_getEncodingt()) . '"/>'
            );
        } else {
            $T->set_var('start_block_postacomment', COM_startBlock($LANG03[1]));
    	    $T->set_var('cid', '');
        }
  	    $T->set_var('CSRF_TOKEN', SEC_createToken());
      	$T->set_var('token_name', CSRF_TOKEN);

        if (! COM_isAnonUser()) {
            if ( $moderatorEdit == true || $adminEdit == true ) {
                // we know we are editing
                $T->set_var('uid', $commentuid);

                if  (isset($A['username'])) {
                    $username = $A['username'];
                } else {
                    $username = $db->conn->fetchColumn(
                        "SELECT name FROM `{$_TABLES['comments']}` WHERE cid=?",
                        array($cid),
                        0,
                        array(Database::INTEGER)
                    );
                }
                if ( empty($username)) {
                    $username = $LANG03[24]; //anonymous user
                }
                $T->set_var('username',$filter->editableText($username));
                if ( $commentuid > 1 ) {
                    $T->set_var('username_disabled','disabled="disabled"');
                } else {
                    $T->unset_var('username_disabled');
                }
            } else {
                $T->set_var('uid', $_USER['uid']);
                $name = COM_getDisplayName($_USER['uid'], $_USER['username'],$_USER['fullname']);
                $T->set_var('username', $filter->editableText($name));
                $T->set_var('action_url',$_CONF['site_url'] . '/users.php?mode=logout');
                $T->set_var('lang_logoutorcreateaccount',$LANG03[03]);
                $T->set_var('username_disabled','disabled="disabled"');
            }

            if ( !$moderatorEdit && !$adminEdit) {
                $T->set_var('suballowed',true);
                $isSub = 0;
                if ( $this->mode == 'preview_edit' || $this->mode == 'preview_new' ) {
                    $isSub = isset($_POST['subscribe']) ? 1 : 0;
                } else if (PLG_isSubscribed('comment',$this->Comment->getType(), $this->Comment->getSid())) {
                    $isSub = 1;
                }
                if ( $isSub == 0 ) {
                    $subchecked = '';
                } else {
                    $subchecked = 'checked="checked"';
                }
                $T->set_var('subchecked',$subchecked);
            }
        } else {
            //Anonymous user
            $T->set_var('uid', 1);

            if ( isset($A['username'])) {
                $name = USER_uniqueUsername($A['username']);
            } else {
                $name = $LANG03[24]; //anonymous user
            }
            $T->set_var('username', $filter->editableText($name));

            $T->set_var('action_url', $_CONF['site_url'] . '/users.php?mode=new');
            $T->set_var('lang_logoutorcreateaccount',$LANG03[04]);
            $T->unset_var('username_disabled');
        }

        if ( $postmode == 'html' ) {
            $T->set_var('htmlmode',true);
        }
        $T->set_var(array(
            'lang_title' => $LANG03[16],
            'title' => $filter->editableText($this->Comment->getTitle()),
            'lang_timeout' => $LANG_ADMIN['timeout_msg'],
            'lang_comment' => $LANG03[9],
            'comment' => $filter->editableText($this->Comment->getComment()),
            'lang_postmode' => $LANG03[2],
            'postmode' => $postmode,
            'postmode_options' => COM_optionList($_TABLES['postmodes'],'code,name',$postmode),
            'lang_importantstuff' => $LANG03[18],
            'lang_instr_line1' => $LANG03[19],
            'lang_instr_line2' => $LANG03[20],
            'lang_instr_line3' => $LANG03[21],
            'lang_instr_line4' => $LANG03[22],
            'lang_instr_line5' => $LANG03[23],
        ) );
        if ( $postmode == 'html' ) {
            $T->set_var('allowed_html', $filter->getAllowedHTML() . '<br/>'. COM_AllowedAutotags('', false, 'glfusion','comment'));
        } else {
            $T->set_var('allowed_html', COM_AllowedAutotags('', false, 'glfusion','comment'));
        }

        if ($this->mode == 'edit' || $this->mode == 'preview_edit') {
            //editing comment or preview changes
            $T->set_var('lang_preview', $LANG03[28]);
        } else {
    	    //new comment
            $T->set_var('lang_preview', $LANG03[14]);
        }

        if (function_exists('msg_replaceEmoticons'))  {
            $T->set_var('smilies',msg_showsmilies());
        }

        $T->unset_var('save_type');
        // allow plugins the option to set some template vars
        PLG_templateSetVars ('comment', $T);

        // set up the save / preview buttons
        if ($this->mode == 'preview_edit' || ($this->mode == 'edit' && $_CONF['skip_preview'] == 1) ) {
            //for editing
            $T->set_var('save_type','saveedit');
            $T->set_var('lang_save',$LANG03[29]);
        } elseif (($_CONF['skip_preview'] == 1) || ($this->mode == 'preview_new')) {
            //new comment
            $T->set_var('save_type','savecomment');
            $T->set_var('lang_save',$LANG03[11]);
        }

        // set some fields if mod or admin edit
        if ( $moderatorEdit == true ) {
            $T->set_var('modedit_mode','modedit');
            $T->set_var('modedit','x');
        }
        if ( $adminEdit == true ) {
            $T->set_var('modedit_mode','adminedit');
            $T->set_var('modedit','x');
            $T->set_var('silent_edit',true);
            $T->set_var('lang_silent_edit', $LANG03[57]);
        }

        $T->set_var('end_block', COM_endBlock());
        $T->parse('output', 'form');
        $retval .= $T->finish($T->get_var('output'));

        return $retval;
    }

}

