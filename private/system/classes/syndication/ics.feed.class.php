<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | ics.feed.class.php                                                       |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2009 by the following authors:                             |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2004-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Michael Jervis     - mike AT fuckingbrit DOT com                |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This software is licensed under the terms of the ZLIB License:           |
// |                                                                          |
// | This software is provided 'as-is', without any express or implied        |
// | warranty. In no event will the authors be held liable for any damages    |
// | arising from the use of this software.                                   |
// |                                                                          |
// | Permission is granted to anyone to use this software for any purpose,    |
// | including commercial applications, and to alter it and redistribute it   |
// | freely, subject to the following restrictions:                           |
// |                                                                          |
// | 1. The origin of this software must not be misrepresented; you must not  |
// |    claim that you wrote the original software. If you use this software  |
// |    in a product, an acknowledgment in the product documentation would be |
// |    appreciated but is not required.                                      |
// |                                                                          |
// | 2. Altered source versions must be plainly marked as such, and must not  |
// |    be misrepresented as being the original software.                     |
// |                                                                          |
// | 3. This notice may not be removed or altered from any source             |
// |    distribution.                                                         |
// +--------------------------------------------------------------------------+

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}
/**
* Provides feed handlers for ics
*
*/

class ICS extends FeedParserBase
{
    var $_inItem;
    var $_currentItem;

    function ICS()
    {
        $this->FeedParserBase();
        $this->_inItem = false;
    }

    /**
      * Format an event into an ICS VEVENT tag.
      *
      * Takes an associative article array and turns it into an XML definition
      * of an article.
      *
      * @param array $article Associative array describing an article.
      */
    function _formatArticle( $article )
    {
        $xml = "BEGIN:VEVENT\n";
        $xml .= 'SUMMARY:'.$this->_safeXML( $article['title'] )."\n";
        if( array_key_exists( 'summary', $article ) ) {
            if( strlen( $article['summary'] ) > 0 ) {
                $xml .= 'DESCRIPTION:'.$this->_safeXML( $article['summary'] )."\n";
            }
        }

        if ( array_key_exists( 'dtstart', $article ) ) {
            if ( strlen($article['dtstart']) > 0 ) {
                $xml .= 'DTSTART:' . $this->_safeXML( $article['dtstart'] ) . "\n";
            }
        }

        if ( array_key_exists( 'dtend', $article ) ) {
            if ( strlen($article['dtend']) > 0 ) {
                $xml .= 'DTEND:' . $this->_safeXML( $article['dtend'] ) . "\n";
            }
        }

        if ( array_key_exists( 'location', $article ) ) {
            if ( strlen($article['location']) > 0 ) {
                $xml .= 'LOCATION:' . $this->_safeXML( $article['location'] ) . "\n";
            }
        }

        if ( array_key_exists( 'categories', $article ) ) {
            if ( strlen($article['categories']) > 0 ) {
                $xml .= 'CATEGORIES:' . $this->_safeXML( $article['categories'] ) . "\n";
            }
        }

        if( is_array($article['extensions']) ) {
            foreach( $article['extensions'] as $extensionTag ) {
                $xml .= "$extensionTag\n";
            }
        }

        $xml .= "END:VEVENT\n";

        return $xml;
    }

    /**
      * Return the formatted start of a feed.
      *
      * This will start the xml and create header information about the feed
      * itself.
      */
    function _feedHeader()
    {
        $xml = 'BEGIN:VCALENDAR' . LB;
        $xml .= 'VERSION:1.0'. LB;
        $xml .= 'PRODID:glFusion Calendar' . LB;

        $xml .= $this->_injectExtendingTags();
        return $xml;
    }

    /**
      * Return the formatted end of a feed.
      *
      * just closes things off nicely.
      */
    function _feedFooter()
    {
        $xml = "END:VCALENDAR\n";
        return $xml;
    }

    /**
      * Handle the begining of an XML element
      *
      * This is called from the parserfactory once the type of data has been
      * determined. Standard XML_PARSER element handler.
      *
      * @author Mark R. Evans
      * @copyright Mark R. Evans 2004
      */
    function startElement($parser, $name, $attributes)
    {
        if( $name == 'ITEM' ) {
            $this->_inItem = true;
            $this->_currentItem = array();
        }
        $this->_currentTag = $name;
    }

    /**
      * Handle the close of an XML element
      *
      * Called by the parserfactory during parsing.
      */
    function endElement($parser, $name)
    {
        if( $name == 'ITEM' ) {
            $this->_inItem = false;
            $this->articles[] = $this->_currentItem;
        }
        $this->_currentTag = '';
    }

    /**
      * Handles character data.
      *
      * Called by the parserfactory during parsing.
      */
    function charData($parser, $data)
    {
        if( $this->_inItem ) {
            if( $this->_currentTag == 'TITLE' ) {
                if( empty( $this->_currentItem['title'] ) ) {
                    $this->_currentItem['title'] = $data;
                } else {
                    $this->_currentItem['title'] .= $data;
                }
            } else if( $this->_currentTag == 'LINK' ) {
                if( empty( $this->_currentItem['link'] ) ) {
                    $this->_currentItem['link'] = $data;
                } else {
                    $this->_currentItem['link'] .= $data;
                }
            } else if( $this->_currentTag == 'DESCRIPTION' ) {
                if( empty( $this->_currentItem['summary'] ) ) {
                    $this->_currentItem['summary'] = $data;
                } else {
                    $this->_currentItem['summary'] .= $data;
                }
            } else if ( $this->_currentTag == 'DTSTART' ) {
                if ( empty($this->_currentItem['dtstart'] ) ) {
                    $this->_currentItem['dtstart'] = $data;
                } else {
                    $this->_currentItem['dtstart'] .= $data;
                }
            } else if ( $this->_currentTag == 'DTSEND' ) {
                if ( empty($this->_currentItem['dtend'] ) ) {
                    $this->_currentItem['dtend'] = $data;
                } else {
                    $this->_currentItem['dtend'] .= $data;
                }
            } else if ( $this->_currentTag == 'LOCATION' ) {
                if ( empty($this->_currentItem['location'] ) ) {
                    $this->_currentItem['location'] = $data;
                } else {
                    $this->_currentItem['location'] .= $data;
                }
            } else if ( $this->_currentTag == 'CATEGORIES' ) {
                if ( empty($this->_currentItem['categories'] ) ) {
                    $this->_currentItem['categories'] = $data;
                } else {
                    $this->_currentItem['categories'] .= $data;
                }
            }
        } else {
            if( $this->_currentTag == 'TITLE' ) {
                $this->title .= $data;
            } else if( $this->_currentTag == 'LINK' ) {
                $this->sitelink .= $data;
            } else if( $this->_currentTag == 'DESCRIPTION' ) {
                $this->description .= $data;
            }
        }
    }
}
?>