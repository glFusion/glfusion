<?php
/**
 * Class to define a single comment.
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
 * This class displays the comment control bar.
 * Prints the control that allows the user to interact with glFusion Comments.
 * @package glfusion
 */
class CommentBar
{
    /** Story/Item ID.
     * @var string */
    private $sid = '';

    /** Item title.
     * @var string */
    private $title = '';

    /** Item type or plugin name.
     * @var string */
    private $type = 'article';

    /** Sort order.
     * @var string */
    private $order = 'ASC';

    /** View mode.
     * @var string */
    private $mode = '';

    /** Comment code (enabled, disabled, closed).
     * @var string */
    private $ccode = '';


    /**
     * Set the story/item ID.
     *
     * @param   string  $sid    Story/Item ID.
     * @return  object  $this
     */
    public function withSid(string $sid)
    {
        $this->sid = $sid;
        return $this;
    }


    /**
     * Set the item title.
     *
     * @param   string  $title  Item title
     * @return  object  $this
     */
    public function withTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }


    /**
     * Set the item type.
     *
     * @param   string  $title  Item type
     * @return  object  $this
     */
    public function withType(string $type)
    {
        $this->type = $type;
        return $this;
    }


    /**
     * Set the sort direction.
     *
     * @param   string  $order  ASC or DESC
     * @return  object  $this
     */
    public function withOrder(string $order)
    {
        $this->order = strtoupper($order);
        return $this;
    }


    /**
     * Set the view mode.
     *
     * @param   string  $view mode
     * @return  object  $this
     */
    public function withMode(string $mode)
    {
        $this->mode = $mode;
        return $this;
    }


    /**
     * Set the comment code (enabled, disabled, closed)
     *
     * @param   string  $ccode  Comment code
     * @return  object  $this
     */
    public function withCommentCode(bool $ccode) : self
    {
        $this->ccode = $ccode;
        return $this;
    }


    /**
     * Create the comment bar for display.
     *
     * @return  string      HTML for the comment bar
     */
    public function render()
    {
        global $_CONF, $_TABLES, $_USER, $LANG01, $LANG03;

        $db = Database::getInstance();

        $parts = explode( '/', $_SERVER['PHP_SELF'] );
        $page = array_pop( $parts );

        $sql = "SELECT COUNT(*) FROM `{$_TABLES['comments']}`
            WHERE sid = ?
                AND type = ?
                AND queued = 0";
        $nrows = $db->conn->fetchColumn($sql,array($this->sid,$this->type),0,array(Database::STRING,Database::STRING));

        $T = new \Template( $_CONF['path_layout'] . 'comment' );
        $T->set_file( array( 'commentbar' => 'commentbar.thtml' ));

        if ( SESS_isSet('glfusion.commentpostsave') ) {
            $msg = COM_showMessageText(SESS_getVar('glfusion.commentpostsave'),'',1,'warning');
            SESS_unSet('glfusion.commentpostsave');
            $T->set_var('info_message',$msg);
        }

        $T->set_var(array(
            'lang_comments' => $LANG01[3],
            'lang_refresh' => $LANG01[39],
            'lang_reply' => $LANG01[60],
            'html_type'=> htmlentities($this->type),
            'html_sid' => htmlentities($this->sid),
        ) );

        if ( $nrows > 0 ) {
            $T->set_var( 'lang_disclaimer', $LANG01[26] );
        } else {
            $T->set_var( 'lang_disclaimer', '' );
        }

        if ( !COM_isAnonUser() ) {
            $T->set_var(array(
                'can_subscribe' => 'true',
                'subscribed' => PLG_isSubscribed('comment', $this->type, $this->sid),
            ) );
        }

        if ( $this->ccode == CommentEngine::CLOSED ) {
            $T->set_var( 'reply_hidden_or_submit', 'hidden');
            $T->set_var( 'comment_option_text', $LANG03[49]);
        } elseif ( $this->ccode == CommentEngine::DISABLED ) {
            $T->set_var( 'reply_hidden_or_submit', 'hidden');
            $T->set_var( 'comment_option_text', $LANG03[49]);
        } elseif (
            $this->ccode == CommentEngine::ENABLED &&
            !CommentEngine::loginRequired()
        ) {
            $T->set_var( 'reply_hidden_or_submit', 'submit' );
            $T->unset_var( 'comment_option_text');
        } else {
            $T->set_var( 'reply_hidden_or_submit', 'hidden' );
            $T->set_var( 'comment_option_text', sprintf($LANG03[50],$_CONF['site_url']) );
        }
        $T->set_var( 'num_comments', COM_numberFormat( $nrows ));
        $T->set_var( 'comment_type', $this->type );
        $T->set_var( 'sid', $this->sid );

        $cmt_title = $this->title;
        $T->set_var('story_title', $cmt_title);
        // Article's are pre-escaped.
        if ($this->type != 'article') {
            $cmt_title = @htmlspecialchars($cmt_title,ENT_COMPAT,COM_getEncodingt());
        }
        $T->set_var('comment_title', $cmt_title);

        if( $this->type == 'article' ) {
            $articleUrl = COM_buildUrl( $_CONF['site_url']."/article.php?story=$this->sid" );
            $T->set_var( 'story_link', $articleUrl );
            $T->set_var( 'article_url', $articleUrl );

            if( $page == 'comment.php' ) {
                $T->set_var('story_link',
                    COM_createLink(
                        $this->title,
                        $articleUrl,
                        array('class'=>'non-ul b')
                    )
                );
                $T->set_var( 'start_storylink_anchortag', '<a href="'
                    . $articleUrl . '" class="non-ul">' );
                $T->set_var( 'end_storylink_anchortag', '</a>' );
            }
        } else { // for a plugin
            // Link to plugin defined link or lacking that a generic link that the plugin should support (hopefully)
            list($plgurl, $plgid) = PLG_getCommentUrlId($this->type);
            $T->set_var( 'story_link', "$plgurl?$plgid=$this->sid" );
            $cmt_title = @htmlspecialchars($cmt_title,ENT_COMPAT,COM_getEncodingt());
        }
        $T->set_var('comment_title', $cmt_title);
        if (! COM_isAnonUser()) {
            $username = $_USER['username'];
            $fullname = $_USER['fullname'];
        } else {
            $N = $db->conn->fetchAssoc("SELECT username,fullname FROM `{$_TABLES['users']}` WHERE uid=1");
            $username = $N['username'];
            $fullname = $N['fullname'];
        }
        if( empty( $fullname )) {
            $fullname = $username;
        }
        $T->set_var( 'user_name', $username );
        $T->set_var( 'user_fullname', $fullname );

        if (! COM_isAnonUser()) {
            $author = COM_getDisplayName( $_USER['uid'], $username, $fullname );
            $T->set_var( 'user_nullname', $author );
            $T->set_var( 'author', $author );
            $T->set_var( 'login_logout_url',
                              $_CONF['site_url'] . '/users.php?mode=logout' );
            $T->set_var( 'lang_login_logout', $LANG01[35] );
        } else {
            $T->set_var( 'user_nullname', '' );
            $T->set_var( 'login_logout_url',
                                  $_CONF['site_url'] . '/users.php?mode=new' );
            $T->set_var( 'lang_login_logout', $LANG01[61] );
        }

        if( $page == 'comment.php' ) {
            $T->set_var( 'parent_url',
                              $_CONF['site_url'] . '/comment.php' );
            $hidden = '';
            $hmode = isset($_REQUEST['mode']) ? COM_applyFilter($_REQUEST['mode']) : 'entry';
            if( $hmode == 'view' ) {
                $hidden .= '<input type="hidden" name="cid" value="' . @htmlspecialchars(COM_applyFilter($_REQUEST['cid']),ENT_COMPAT,COM_getEncodingt()) . '"/>';
                $hidden .= '<input type="hidden" name="pid" value="' . @htmlspecialchars(COM_applyFilter($_REQUEST['cid']),ENT_COMPAT,COM_getEncodingt()) . '"/>';
            }
            else if( $hmode == 'display' ) {
                $hidden .= '<input type="hidden" name="pid" value="' . @htmlspecialchars(COM_applyFilter($_REQUEST['pid']),ENT_COMPAT,COM_getEncodingt()) . '"/>';
            }
            $T->set_var( 'hidden_field', $hidden .
                '<input type="hidden" name="mode" value="' . @htmlspecialchars($hmode,ENT_COMPAT,COM_getEncodingt()) . '"/>' );
        } else if( $this->type == 'article' ) {
            $T->set_var( 'parent_url',
                              $_CONF['site_url'] . '/article.php#comments' );
            $T->set_var( 'hidden_field',
                '<input type="hidden" name="story" value="' . $this->sid . '"/>' );
        } else { // plugin
            // Link to plugin defined link or lacking that a generic link that the plugin should support (hopefully)
            list($plgurl, $plgid) = PLG_getCommentUrlId($this->type);
            $T->set_var( 'parent_url', $plgurl );
            $T->set_var( 'hidden_field',
                '<input type="hidden" name="' . $plgid . '" value="' . $this->sid . '"/>' );
        }

        // Order
        $selector_data = COM_optionList( $_TABLES['sortcodes'], 'code,name', $this->order );
        $T->set_var( 'order_selector', $selector_data);

        // Mode
        $selector_data = COM_optionList( $_TABLES['commentmodes'], 'mode,name', $this->mode );
        $T->set_var( 'mode_selector', $selector_data);
        $T->set_var( 'mode_select_field_name', ($page == 'comment.php' ? 'format' : 'mode' ) ) ;

        return $T->finish( $T->parse( 'output', 'commentbar' ));
    }
}


