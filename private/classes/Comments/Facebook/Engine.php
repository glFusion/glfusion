<?php
/**
 * Facebook comment engine functions.
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
namespace glFusion\Comments\Facebook;


/**
 * Facebook comment engine.
 * @package glfusion
 */
class Engine extends \glFusion\Comments\CommentEngine
{
    /**
     * Handle the comment display for Facebook.
     * Creates the necessary HTML to retrieve Facebook's display.
     *
     * @return  string      HTML to render comments
     */
    public function displayComments()
    {
        global $_CONF;
        static $have_apicode = false;

        $retval = '';
        $retval .= '<a name="comments"></a>
            <div class="fb-comments" data-href="' . $this->getPageUrl() .
            '" data-width="" data-numposts="20"></div>';
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
        global $LANG01;

        $url = COM_buildURL($url);
        $link = '<a href="'.$url.'">';
        $retval = array(
            'url'           => $url,
            'url_extra'     => '',
            'link'          => $link,
            'nonlink'       => '<span class="fb-comments-count" data-href="'.$url.'"></span>',
            'comment_count' => '<span class="fb-comments-count" data-href="'.$url.'"></span> ' . $LANG01[83],
            'comment_text'  => $LANG01[83],
            'link_with_count' => $link.'<span class="fb-comments-count" data-href="'.$url.'"></span>'.' '.$LANG01[83].'</a>',
        );
        return $retval;
    }


    /**
     * Get the code needed in the integrated_comments theme field.
     * Also sets a meta var for facebook app_id.
     *
     * @return  string      API code.
     */
    public function getApiCode() : string
    {
        global $_CONF;
        static $done = false;   // Make sure to do it only once

        $retval = '';
        if (!$done) {
            $outputHandler = \outputHandler::getInstance();
            $outputHandler->addRaw('<meta property="fb:app_id" content="{'.$_CONF['comment_fb_appid'].'}" />');
            $retval .= '<div id="fb-root"></div>
                <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v15.0&appId=' . $_CONF['comment_fb_appid'] .
                '&autoLogAppEvents=1" nonce="' . uniqid() . '"></script>';
            $done = true;
        }
        return $retval;
    }

}

