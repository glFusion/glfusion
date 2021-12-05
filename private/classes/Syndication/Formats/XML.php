<?php
/**
 * Class to handle creating XML feeds.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Syndication\Formats;

use \glFusion\Database\Database;

/**
 * Syndication Feed class.
 * @package glfusion
 */
class XML extends \glFusion\Syndication\Feed
{
    public static $versions = array('1.0');

    /**
     * Generate and write the feed file.
     *
     * @return  void
     */
    protected function _generate() : void
    {
        global $_CONF, $_SYND_DEBUG;

        $rss = new \UniversalFeedCreator();
        if ($this->getContentLength() > 1 ) {
            $rss->descriptionTruncSize = $this->getContentLength();
        }
        $rss->descriptionHtmlSyndicated = false;
        $rss->language = $this->getLanguage();
        $rss->title = $this->getTitle();
        $rss->description = $this->getDescription();

        $imgurl = '';
        if ($this->getLogo() != '' ) {
        	$image = new \FeedImage();
        	$image->title = $this->getTitle();
        	$image->url = $_CONF['site_url'] . $this->getLogo();
    	    $image->link = $_CONF['site_url'];
        	$rss->image = $image;
        }
        $rss->link = $_CONF['site_url'];
        if ( !empty($this->getFilename())) {
            $filename = $this->getFilename();
        } else {
            $filename = 'site.rss';
        }
        $rss->syndicationURL = self::getFeedUrl( $filename );
        $rss->copyright = 'Copyright ' . strftime( '%Y' ) . ' '.$_CONF['site_name'];

        $content = PLG_getFeedContent($this->getType(), $this->getFid(), $link, $data, $this->format, $this->format_version);
        if ($content === NULL) {
            // Special NULL return if the plugin handles its own feed writing
            return;
        } elseif (is_array($content)) {
            foreach ($content AS $feedItem) {
                $item = new \FeedItem();

                foreach($feedItem as $var => $value) {
                    if ( $var == 'date') {
                        $dt = new \Date($value,$_CONF['timezone']);
                        $item->date = $dt->toISO8601(true);
                    } else if ( $var == 'summary' ) {
                        $item->description = $value;
                    } else if ( $var == 'link' ) {
                        $item->guid = $value;
                        $item->$var = $value;
                    } else {
                        $item->$var = $value;
                    }
                }
                $rss->addItem($item);
            }
        }

        if (empty($link)) {
            $link = $_CONF['site_url'];
        }
        $rss->editor = $_CONF['site_mail'];
        $rc = $rss->saveFeed($this->format.'-'.$this->format_version, self::getFeedPath( $filename ) ,0);
        $this->setUpdateData($data);
    }

}

