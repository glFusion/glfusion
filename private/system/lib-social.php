<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-social.php                                                           |
// |                                                                          |
// | glFusion Enhancement Library                                             |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2015-2016 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

// this file can't be used on its own
if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

function SOC_getShareIcons( $title = '', $summary = '', $itemUrl = '', $image = '', $type = '' )
{
    global $_CONF, $_TABLES, $LANG_SOCIAL;

    $replacementArray = array('%%t', '%%s', '%%u', '%%i');

    $retval = '';

    $T = new Template( $_CONF['path_layout'].'social' );
    $T->set_file('social_icons','socialshare.thtml');

    $sql = "SELECT * FROM {$_TABLES['social_share']} WHERE enabled=1";
    $result = DB_query($sql);
    $numRows = DB_numRows($result);

    if ( $numRows <= 0 ) return $retval;

    $T->set_block('social_icons','social_buttons','sb');

    $output = outputHandler::getInstance();
    $output->addLinkScript($_CONF['site_url'].'/javascript/socialshare.js');

    for ( $x = 0; $x < $numRows; $x++ ) {
        $row = DB_fetchArray($result);

        $id = $row['id'];
        $name = $row['name'];
        $display_name = $row['display_name'];
        $icon = $row['icon'];
        $url  = $row['url'];

        // now parse the URL and replace with stuff

        foreach($replacementArray AS $item ) {
            switch ($item) {
                case '%%t' :
                    $replacementItem = $title;
                    break;
                case '%%s' :
                    $replacementItem = $summary;
                    break;
                case '%%u' :
                    $replacementItem = $itemUrl;
                    break;
                case '%%i' :
                    $replacementItem = $image;
                    break;
                default :
                    $replacementItem = '';
                    break;
            }
            $url = str_replace($item,$replacementItem,$url);
        }

        // now build the actual button
        $T->set_var('service_name',$name);
        $T->set_var('service_id',$id);
        $T->set_var('service_display_name',$display_name);
        $T->set_var('icon',$icon);
        $T->parse('sb','social_buttons',true);
    }
    $T->set_var('lang_share_it', $LANG_SOCIAL['share_it_label']);
    $retval = $T->finish ($T->parse('output','social_icons'));

    return $retval;
}
?>