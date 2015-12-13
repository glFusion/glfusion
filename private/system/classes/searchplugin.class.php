<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | searchplugin.class.php                                                   |
// |                                                                          |
// | glFusion plugin class.                                                   |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Copyright (C) 2008 by the following authors:                             |
// |                                                                          |
// | Authors: Sami Barakat, s.m.barakat AT gmail DOT com                      |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

/**
* This class allows data to be passed between the search engine and plugins.
* So far it is just a structure but more will be added soon.
*
* @author   Sami Barakat <s.m.barakat AT gmail DOT com>
*
*/
class SearchPlugin {

    // PRIVATE PROPERTIES
    var $_query = '';
    var $_pluginLabel;
    var $_pluginName;
    var $_rank;
    var $_url_rewite;

    function __construct($pluginLabel, $pluginName, $rank = 3, $url_rewite = false)
    {
        $this->_pluginName = $pluginName;
        $this->_pluginLabel = $pluginLabel;
        $this->_rank = $rank;
        $this->_url_rewite = $url_rewite;
    }

    function setQuery($sqlQuery, $sqlFTQuery = '')
    {
        // this variable will be a global config var that sets the system to use full text searches if avaliable
        $fulltext = false;

        if ($fulltext && !empty($sqlFTQuery))
            $this->_query = $sqlFTQuery;
        else
            $this->_query = $sqlQuery;
    }

    function getQuery()
    {
        return $this->_query;
    }

    function getName()
    {
        return $this->_pluginName;
    }

    function getLabel()
    {
        return $this->_pluginLabel;
    }

    function getRank()
    {
        return $this->_rank;
    }

    function UrlRewriteEnable()
    {
        return $this->_url_rewite;
    }
}

?>
