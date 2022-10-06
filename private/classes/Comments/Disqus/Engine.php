<?php
/**
 * Discus engine to retrieve and display comments.
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
namespace glFusion\Comments\Disqus;


/**
 * Discus comment engine.
 * @package glfusion
 */
class Engine extends \glFusion\Comments\CommentEngine
{

    /**
     * Handle the comment display for Disqus.
     * Creates the necessary HTML to retrieve Disqus' display.
     *
     * @return  string      HTML to render comments
     */
    public function displayComments() : string
    {
        global $_CONF;

        $retval = '
            <a name="comments"></a>
            <div id="disqus_thread"></div>
            <script>
                var disqus_config = function () {
                    this.page.url = \''.$this->getPageUrl().'\';
                    this.page.identifier = \''.$this->getPageId().'\';
                    this.page.title = \''.addslashes($this->title).'\';
                };
                (function() {
                    var d = document, s = d.createElement(\'script\');
                    s.src = \'//'.$_CONF['comment_disqus_shortname'].'.disqus.com/embed.js\';
                    s.setAttribute(\'data-timestamp\', +new Date());
                    (d.head || d.body).appendChild(s);
                })();
            </script>
            <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript" rel="nofollow">comments powered by Disqus.</a></noscript>
            ';
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
        global $LANG01, $_CONF;

        static $done = false;
        if (!$done) {
            $outputHandle = \outputHandler::getInstance();
            $outputHandle->addRawScriptFooter(
                '<script id="dsq-count-scr" src="//gldev-test-site.disqus.com/count.js" async></script>'
            );
            $done = true;
        }
        //$url = COM_buildURL($url.'#disqus_thread');
        if ($type == 'filemgmt') $type = 'filemgmt_fileid';

        $link = '<a href="'.$url.'" data-disqus-identifier='.$type.'_'.$sid.'>';
        $retval = array(
            'url'   => $url,
            'url_extra'=> ' data-disqus-identifier="'.$type.'_'.$sid.'"',
            //'link'  => $link,
            'nonlink'   => '<span class="disqus-comment-count" data-disqus-identifier="'.$type.'_'.$sid.'"></span>',
            'comment_count'=> '<span class="disqus-comment-count" data-disqus-identifier="'.$type.'_'.$sid.'">0 '.$LANG01[83].'</span>',
            'comment_text'=> $LANG01[83],
            'link_with_count' => $link.'<span class="disqus-comment-count" data-disqus-identifier="'.$type.'_'.$sid.'">'.$cmtCount.' '.$LANG01[83].'</span></a>',
        );
        return $retval;
    }

}

