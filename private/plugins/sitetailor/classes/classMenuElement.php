<?php
// +--------------------------------------------------------------------------+
// | Site Tailor Plugin - glFusion CMS                                        |
// +--------------------------------------------------------------------------+
// | classMenuElement.php                                                     |
// |                                                                          |
// | Menu elements class / functions                                          |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C)  2008-2009 by the following authors:                       |
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
    die ('This file can not be used on its own.');
}

class mbElement {
    var $id;                // item id
    var $pid;               // parent id (0 = root level)
    var $menu_id;           // which menu does this belong to?
    var $label;             // text label for the menu item
    var $type;              // menu element type (submenu header, gl core, plugin, etc)
    var $subtype;           // generic subtype holder, contents depend on $type
    var $order;             // order of display (1 to ....)
    var $active;            // active entry or inactive
    var $url;               // URL to go on click
    var $target;            // Target window for URL
    var $group_id;          // group membership
    var $access;            // derived access level
    var $children;          // this elements child elements
    var $hidden;            // I have no idea -- Joe

    function mbElement () {
        $this->children         = array();
        $this->id               = 0;
        $this->menu_id          = 0;
        $this->group_id         = 0;
    }

    function constructor( $element, $meadmin, $root, $groups ) {
        $this->id               = $element['id'];
        $this->pid              = $element['pid'];
        $this->menu_id          = $element['menu_id'];
        $this->label            = (!empty($element['element_label']) && $element['element_label'] != ' ') ? $element['element_label'] : '';
        $this->type             = $element['element_type'];
        $this->subtype          = $element['element_subtype'];
        $this->order            = $element['element_order'];
        $this->active           = $element['element_active'];
        $this->url              = (!empty($element['element_url']) && $element['element_url'] != ' ') ? $element['element_url'] : '';
        $this->target           = $element['element_target'];
        $this->group_id         = $element['group_id'];
        $this->setAccessRights($meadmin,$root,$groups);
    }

    function setChild( $id ) {
        $this->children[$id] = $id;
    }

    function saveElement( ) {
        global $_TABLES, $stMenu;

        $this->label            = DB_escapeString($this->label);
        $this->url              = DB_escapeString($this->url);

        $sqlFieldList  = 'id,pid,menu_id,element_label,element_type,element_subtype,element_order,element_active,element_url,element_target,group_id';
        $sqlDataValues = "$this->id,$this->pid,'".DB_escapeString($this->menu_id)."','$this->label',$this->type,'$this->subtype',$this->order,$this->active,'$this->url','$this->target',$this->group_id";
        DB_save($_TABLES['st_menu_elements'], $sqlFieldList, $sqlDataValues);
    }

    function reorderMenu( ) {
        global $_TABLES, $stMenu;

        $pid = intval($this->id);
        $menu_id = intval($this->menu_id);

        $orderCount = 10;

        $sql = "SELECT id,`element_order` FROM {$_TABLES['st_menu_elements']} WHERE menu_id=".$menu_id." AND pid=" . $pid . " ORDER BY `element_order` ASC";
        $result = DB_query($sql);
        while ($M = DB_fetchArray($result)) {
            $M['element_order'] = $orderCount;
            $orderCount += 10;
            DB_query("UPDATE {$_TABLES['st_menu_elements']} SET `element_order`=" . $M['element_order'] . " WHERE menu_id=".$menu_id." AND id=" . $M['id'] );
        }
    }


    function createElementID( $menu_id ) {
        global $_TABLES;

        $sql = "SELECT MAX(id) + 1 AS next_id FROM " . $_TABLES['st_menu_elements'];
        $result = DB_query( $sql );
        $row = DB_fetchArray( $result );
        $id = $row['next_id'];
        if ( $id < 1 ) {
            $id = 1;
        }
        if ( $id == 0 ) {
            COM_errorLog("Site Tailor: Error - Returned 0 as element id");
            $id = 1;
        }
        return $id;
    }


    function setAccessRights( $meadmin, $root, $groups ) {
        global $_USER, $stMenu;

        if ($meadmin || $root) {
            $this->access = 3;
        } else {
            if ( $this->group_id == 998 ) {
                if( COM_isAnonUser() ) {
                    $this->access = 3;
                } else {
                    $this->access = 0;
                }
            } else {
                if ( in_array( $this->group_id, $groups ) ) {
                    $this->access = 3;
                }
            }
        }
    }

    function getChildren() {
        return (array_keys($this->children));
    }

    function getChildcount() {
        global $stMenu;

        $numChildren = 0;
        $children = $this->getChildren();
        $x = count($children);
        for ($i=0; $i < $x; $i++ ) {
            if ( $stMenu[$this->menu_id]['elements'][$children[$i]]->active > 0 ) {
                if ( $stMenu[$this->menu_id]['elements'][$children[$i]]->hidden == 1 ) {
                    if ( $stMenu[$this->menu_id]['elements'][$children[$i]]->access == 3 ) {
                        $numChildren++;
                    }
                } else {
                    $numChildren++;
                }
            }
        }
        return $numChildren;
    }

// This will build the actual edit tree line.  It will be a table, where each row

    function editTree( $depth, $count ) {
        global $_CONF, $stMenu, $level;
        global $LANG_ST01, $LANG_ST_TYPES,$LANG_ST_GLTYPES,$LANG_ST_GLFUNCTION;

        $plugin_menus = _stPLG_getMenuItems();

        static $count;
        $retval = '';
        $px = ($level - 1 ) * 15;

        if ( $this->label != 'Top Level Menu' ) {

            $style = ($count % 2) + 1;

            $retval .= '<tr class="pluginRow' . $style . '" onmouseover="className=\'pluginRollOver\';" onmouseout="className=\'pluginRow' . $style . '\';">';

            $retval .= '<td>';

            $elementDetails = $this->label . '::';
            $elementDetails .= '<b>' . $LANG_ST01['type'] . ':</b> ' . $LANG_ST_TYPES[$this->type] . '<br' . XHTML . '>';
            switch ($this->type) {
                case 1 :
                    break;
                case 2 :
                    $elementDetails .= '<b>' . $LANG_ST_TYPES[$this->type] . '</b> ' . $LANG_ST_GLFUNCTION[$this->subtype] . '<br' . XHTML . '>';
                    break;
                case 3 :
                    $elementDetails .= '<b>' . $LANG_ST_TYPES[$this->type] . ':</b> ' . $LANG_ST_GLTYPES[$this->subtype] . '<br' . XHTML . '>';
                    break;
                case 4 :
                    $elementDetails .= '<b>' . $LANG_ST_TYPES[$this->type] . ':</b> ' . $this->subtype . '<br' . XHTML . '>';
                    break;
                case 5 :
                    $elementDetails .= '<b>' . $LANG_ST_TYPES[$this->type] . ':</b> UNDEFINED <br' . XHTML . '>';
                    break;
                case 6 :
                    $elementDetails .= '<b>' . $LANG_ST_TYPES[$this->type] . ':</b> ' . $this->url . '<br' . XHTML . '>';
                    break;
                case 7 :
                    $elementDetails .= '<b>' . $LANG_ST_TYPES[$this->type] . ':</b> ' . $this->subtype . '<br' . XHTML . '>';
                    break;
            }

            $moveup     = '<a href="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php?mode=move&amp;where=up&amp;mid=' . $this->id . '&amp;menu=' . $this->menu_id . '">';
            $movedown   = '<a href="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php?mode=move&amp;where=down&amp;mid=' . $this->id . '&amp;menu=' . $this->menu_id . '">';
            $edit       = '<a href="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php?mode=edit&amp;mid=' . $this->id . '&amp;menu=' . $this->menu_id . '">';
            $delete     = '<a href="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/menu.php?mode=delete&amp;mid=' . $this->id . '&amp;menuid='.$this->menu_id.'" onclick="return confirm(\'' . $LANG_ST01['confirm_delete'] . '\');">';
            $info       = '<a class="gl_mootip" title="' . $elementDetails . '" href="#"><img src="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/images/info.png" alt=""' . XHTML . '></a>';

            $retval .= "<div style=\"padding:0 5px;margin-left:" . $px . "px;\">" . ($this->type == 1 ? '<b>' : '') . strip_tags($this->label) . ($this->type == 1 ? '</b>' : '') . '</div>' . LB;

            $retval .= '</td>';
            $retval .= '<td class="aligncenter">';
            $retval .=  '<input type="checkbox" name="enableditem[' . $this->id . ']" onclick="submit()" value="1"' . ($this->active == 1 ? ' checked="checked"' : '') . XHTML . '>';
            $retval .= '</td>';
            $retval .= '<td class="aligncenter">';
            $retval .= $info;
            $retval .= '</td>';
            $retval .= '<td class="aligncenter">' . $edit . '<img src="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/images/edit.png" alt="' . $LANG_ST01['edit'] . '"' . XHTML . '></a></td>';
            $retval .= '<td class="aligncenter">' . $delete . '<img src="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/images/delete.png" alt="' . $LANG_ST01['delete'] . '"' . XHTML . '></a></td>';
            $retval .= '<td class="aligncenter">' . $moveup . '<img src="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/images/up.png" alt="' . $LANG_ST01['move_up'] . '"' . XHTML . '></a></td><td class="aligncenter">' . $movedown . '<img src="' . $_CONF['site_admin_url'] . '/plugins/sitetailor/images/down.png" alt="' . $LANG_ST01['move_down'] . '"' . XHTML . '></a></td>';
            $retval .= '</tr>';
            $count++;

        }

        if ( !empty($this->children)) {
            $children = $this->getChildren();
            foreach($children as $child) {
                $level++;
                if ( isset($stMenu[$this->menu_id]['elements'][$child]) ) {
                    $retval .= $stMenu[$this->menu_id]['elements'][$child]->editTree($depth,$count);
                }
                $level--;
            }
        }
        return $retval;
    }

    function isLastChild() {
        global $stMenu;

        $pid = $this->pid;
        $children = $stMenu[$this->menu_id]['elements'][$pid]->getChildren();
        $arrayIndex = count($children)-1;
        if ( $this->id == $children[$arrayIndex] ) {
            return true;
        }
        return false;
    }

    function replace_macros() {
        global $_CONF;
        $this->url = str_replace( "%version%", GVERSION, $this->url );
        $this->url = str_replace( "%site_url%", $_CONF['site_url'], $this->url );
        $this->url = str_replace( "%site_admin_url%", $_CONF['site_admin_url'], $this->url );
        return;
    }

    function showTree( $depth,$ulclass='',$liclass='',$parentaclass='',$lastclass,$selected='' ) {
        global $_SP_CONF,$_USER, $_TABLES, $LANG01, $LANG29, $_CONF,$meLevel,
               $_DB_dbms,$_GROUPS, $config,$stMenu;

        $oulclass       = $ulclass;
        $oliclass       = $liclass;
        $oparentaclass  = $parentaclass;
        $olastclass     = $lastclass;

        if ( $ulclass != '' )
            $ulclass = $ulclass . $this->menu_id;
        if ( $liclass != '' )
            $liclass = $liclass . $this->menu_id;
        if ( $parentaclass != '' )
            $parentaclass = $parentaclass . $this->menu_id;
        if ( $lastclass != '' )
            $lastclass = $lastclass . $this->menu_id;

        $retval = '';
        $menu = '';

        if ( $this->active != 1 && $this->id != 0 ) {
            return '';
        }

        if (isset($_REQUEST['topic']) ){
            $topic = COM_applyFilter($_REQUEST['topic']);
        } else {
            $topic = '';
        }

        if( COM_isAnonUser() ) {
            $anon = 1;
        } else {
            $anon = 0;
        }
        $allowed = true;

        if ( $this->group_id != 998 && $this->id != 0 && !SEC_inGroup($this->group_id) ) {
            return '';
        }

        if ( $this->group_id == 1 && !isset($_GROUPS['Root']) ) {
            return '';
        }

        // need to build the URL
        switch ( $this->type ) {
            case '1' : // subtype - do nothing
                $this->replace_macros();
                break;
            case '2' : // glfusion action
                switch ($this->subtype) {
                    case 0: // home
                        $this->url = $_CONF['site_url'] . '/';
                        break;
                    case 1: // contribute
                        if( empty( $topic )) {
                            $this->url = $_CONF['site_url'] . '/submit.php?type=story';
                        } else {
                            $this->url = $_CONF['site_url']
                                 . '/submit.php?type=story&amp;topic=' . $topic;
                        }
                        $label = $LANG01[71];
                        if( $anon && ( $_CONF['loginrequired'] || $_CONF['submitloginrequired'] )) {
                            $allowed = false;
                        }
                        break;
                    case 2: // directory
                        $this->url = $_CONF['site_url'] . '/directory.php';
                        if( !empty( $topic )) {
                            $this->url = COM_buildUrl( $this->url . '?topic='
                                                 . urlencode( $topic ));
                        }
                        if( $anon && ( $_CONF['loginrequired'] ||
                                $_CONF['directoryloginrequired'] )) {
                            $allowed = false;
                        }
                        break;
                    case 3: // prefs
                        $this->url = $_CONF['site_url'] . '/usersettings.php?mode=edit';
                        if( $anon && ( $_CONF['loginrequired'] ||
                                $_CONF['profileloginrequired'] )) {
                            $allowed = false;
                        }
                        break;
                    case 4: // search
                        $this->url = $_CONF['site_url'] . '/search.php';
                        if( $anon && ( $_CONF['loginrequired'] ||
                                $_CONF['searchloginrequired'] )) {
                            $allowed = false;
                        }
                        break;
                    case 5: // stats
                        $this->url = $_CONF['site_url'] . '/stats.php';
                        if ( !SEC_hasRights('stats.view') ) {
                            $allowed = false;
                        }

                        break;
                    default : // unknown?
                        $this->url = $_CONF['site_url'] . '/';
                        break;
                }
                break;
            case '3' : // glfusion menu
                switch ($this->subtype) {
                    case 1 : // user menu
                        if ( $this->id != 0 && $this->access > 0 && $parentaclass != '' ) {
                            $menu .= "<li>" . '<a class="' . $parentaclass . '" name="'.$parentaclass.'" href="#">' . strip_tags($this->label) . '</a>' . LB;
                        } else {
                            $menu .= "<li>" . '<a href="#">' . strip_tags($this->label) . '</a></li>' . LB;
                        }
                        if ( $this->id == 0 && $ulclass != '' ) {
                            $menu .= '<ul class="' . $ulclass . '">' . LB;
                        } else {
                            $menu .= '<ul>' . LB;
                        }
                        if( !empty( $_USER['uid'] ) && ( $_USER['uid'] > 1 )) {
                            $plugin_options = PLG_getAdminOptions();
                            $num_plugins = count($plugin_options);
                            if (SEC_isModerator() OR
                                    SEC_hasRights('story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit', 'OR') OR
                                    ($num_plugins > 0))
                            {
                                $url = $_CONF['site_admin_url'] . '/index.php';
                                $label =  $LANG29[34];
                                $menu .= '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                            }
                            // what's our current URL?
                            $thisUrl = COM_getCurrentURL();

                            // This function will show the user options for all installed plugins
                            // (if any)

                            $plugin_options = PLG_getUserOptions();
                            $nrows = count( $plugin_options );

                            for( $i = 0; $i < $nrows; $i++ ) {
                                $plg = current( $plugin_options );
                                $label = $plg->adminlabel;
                                if( !empty( $plg->numsubmissions )) {
                                    $label .= ' (' . $plg->numsubmissions . ')';
                                }
                                $url = $plg->adminurl;
                                $menu .= '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                next( $plugin_options );
                            }
                            $url = $_CONF['site_url'] . '/usersettings.php?mode=edit';
                            $label = $LANG01[48];
                            $menu .= '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                            $url = $_CONF['site_url'] . '/users.php?mode=logout';
                            $label = $LANG01[19];
                            $menu .= '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                        } else {
                            $url = $_CONF['site_url'] . '/users.php?mode=login';
                            $label = 'Login';
                            $menu .= '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                        }
                        $menu .= '</ul>' . LB . '</li>' . LB;
                        break;
                    case 2 : // admin menu

                        if( !empty( $_USER['username'] )) {

                            /*
                             * Set the initial $menu opening
                             */

                            if ( $this->id != 0 && $this->access > 0 && $parentaclass != '' ) {
                                $menu .= "<li>" . '<a class="' . $parentaclass . '" name="'.$parentaclass.'" href="#">' . strip_tags($this->label) . '</a>' . LB;
                            } else {
                                $menu .= "<li>" . '<a href="#">' . strip_tags($this->label) . '</a></li>' . LB;
                            }
                            if ( $this->id == 0 && $ulclass != '' ) {
                                $menu .= '<ul class="' . $ulclass . '">' . LB;
                            } else {
                                $menu .= '<ul>' . LB;
                            }

                            $link_array = array();

                            /*
                             * Get all plugin menu options
                             */

                            $plugin_options = PLG_getAdminOptions();
                            $num_plugins = count( $plugin_options );

                            /*
                             * Build the standard glFusion admin options
                             */

                            /*
                             * Story moderation entry
                             */

                            if( SEC_isModerator() OR SEC_hasRights( 'story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit', 'OR' ) OR ( $num_plugins > 0 )) {
                                // what's our current URL?
                                $thisUrl = COM_getCurrentURL();

                                $topicsql = '';
                                if( SEC_isModerator() || SEC_hasRights( 'story.edit' )) {
                                    $tresult = DB_query( "SELECT tid FROM {$_TABLES['topics']}"
                                                         . COM_getPermSQL() );
                                    $trows = DB_numRows( $tresult );
                                    if( $trows > 0 ) {
                                        $tids = array();
                                        for( $i = 0; $i < $trows; $i++ ) {
                                            $T = DB_fetchArray( $tresult );
                                            $tids[] = $T['tid'];
                                        }
                                        if( sizeof( $tids ) > 0 ) {
                                            $topicsql = " (tid IN ('" . implode( "','", $tids ) . "'))";
                                        }
                                    }
                                }

                                $modnum = 0;
                                if( SEC_hasRights( 'story.edit,story.moderate', 'OR' ) || (( $_CONF['usersubmission'] == 1 ) && SEC_hasRights( 'user.edit,user.delete' ))) {
                                    if( SEC_hasRights( 'story.moderate' )) {
                                        if( empty( $topicsql )) {
                                            $modnum += DB_count( $_TABLES['storysubmission'] );
                                        } else {
                                            $sresult = DB_query( "SELECT COUNT(*) AS count FROM {$_TABLES['storysubmission']} WHERE" . $topicsql );
                                            $S = DB_fetchArray( $sresult );
                                            $modnum += $S['count'];
                                        }
                                    }
                                    if(( $_CONF['listdraftstories'] == 1 ) && SEC_hasRights( 'story.edit' )) {
                                        $sql = "SELECT COUNT(*) AS count FROM {$_TABLES['stories']} WHERE (draft_flag = 1)";
                                        if( !empty( $topicsql )) {
                                            $sql .= ' AND' . $topicsql;
                                        }
                                        $result = DB_query( $sql . COM_getPermSQL( 'AND', 0, 3 ));
                                        $A = DB_fetchArray( $result );
                                        $modnum += $A['count'];
                                    }

                                    if( $_CONF['usersubmission'] == 1 ) {
                                        if( SEC_hasRights( 'user.edit' ) && SEC_hasRights( 'user.delete' )) {
                                            $modnum += DB_count( $_TABLES['users'], 'status', '2' );
                                        }
                                    }
                                }
                                // now handle submissions for plugins
                                $modnum += PLG_getSubmissionCount();

                                if( SEC_hasRights( 'story.edit' )) {
                                    $url = $_CONF['site_admin_url'] . '/story.php';
                                    $label = $LANG01[11];
                                    if( empty( $topicsql )) {
                                        $numstories = DB_count( $_TABLES['stories'] );
                                    } else {
                                        $nresult = DB_query( "SELECT COUNT(*) AS count from {$_TABLES['stories']} WHERE" . $topicsql . COM_getPermSql( 'AND' ));
                                        $N = DB_fetchArray( $nresult );
                                        $numstories = $N['count'];
                                    }

                                    $label .= ' (' . COM_numberFormat($numstories) . ')';
                                    $link_array[$LANG01[11]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }

                                if( SEC_hasRights( 'block.edit' )) {
                                    $result = DB_query( "SELECT COUNT(*) AS count FROM {$_TABLES['blocks']}" . COM_getPermSql());
                                    list( $count ) = DB_fetchArray( $result );

                                    $url = $_CONF['site_admin_url'] . '/block.php';
                                    $label = $LANG01[12] . ' (' . COM_numberFormat($count) . ')';
                                    $link_array[$LANG01[12]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }

                                if( SEC_hasRights( 'topic.edit' )) {
                                    $result = DB_query( "SELECT COUNT(*) AS count FROM {$_TABLES['topics']}" . COM_getPermSql());
                                    list( $count ) = DB_fetchArray( $result );

                                    $url = $_CONF['site_admin_url'] . '/topic.php';
                                    $label = $LANG01[13] . ' (' . COM_numberFormat($count) . ')';
                                    $link_array[$LANG01[13]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }

                                if( SEC_hasRights( 'user.edit' )) {
                                    $url = $_CONF['site_admin_url'] . '/user.php';
                                    $label = $LANG01[17] . ' (' . COM_numberFormat(DB_count($_TABLES['users']) -1) . ')';
                                    $link_array[$LANG01[17]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }

                                if( SEC_hasRights( 'group.edit' )) {
                                    if (SEC_inGroup('Root')) {
                                        $grpFilter = '';
                                    } else {
                                        $thisUsersGroups = SEC_getUserGroups ();
                                        $grpFilter = 'WHERE (grp_id IN (' . implode (',', $thisUsersGroups) . '))';
                                    }
                                    $result = DB_query( "SELECT COUNT(*) AS count FROM {$_TABLES['groups']} $grpFilter;" );
                                    $A = DB_fetchArray( $result );

                                    $url = $_CONF['site_admin_url'] . '/group.php';
                                    $label = $LANG01[96] . ' (' . COM_numberFormat($A['count']) . ')';
                                    $link_array[$LANG01[96]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }

                                if ( SEC_inGroup('Root') ) {
                                    $url = $_CONF['site_admin_url'].'/envcheck.php';
                                    $label = $LANG01['env_check'];
                                    $link_array[$LANG01['env_check']] = '<li><a href="'.$url.'">'.$label.'</a></li>'.LB;
                                }

                                if( SEC_hasRights( 'user.mail' )) {
                                    $url = $_CONF['site_admin_url'] . '/mail.php';
                                    $label = $LANG01[105] . ' (N/A)';
                                    $link_array[$LANG01[105]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }

                                if(( $_CONF['backend'] == 1 ) && SEC_hasRights( 'syndication.edit' )) {
                                    $url = $_CONF['site_admin_url'] . '/syndication.php';
                                    $label = $LANG01[38] . ' (' . COM_numberFormat(DB_count($_TABLES['syndication'])) . ')';
                                    $link_array[$LANG01[38]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }

                                if(( $_CONF['trackback_enabled'] || $_CONF['pingback_enabled'] || $_CONF['ping_enabled'] ) && SEC_hasRights( 'story.ping' )) {
                                    $url = $_CONF['site_admin_url'] . '/trackback.php';
                                    $label = $LANG01[116] . ' (' . COM_numberFormat( DB_count( $_TABLES['pingservice'] )) . ')';
                                    $link_array[$LANG01[116]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }
                                if( SEC_hasRights( 'plugin.edit' )) {
                                    $url = $_CONF['site_admin_url'] . '/plugins.php';
                                    $label = $LANG01[77] . ' (' . COM_numberFormat( DB_count( $_TABLES['plugins'] )) . ')';
                                    $link_array[$LANG01[77]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }
                                if (SEC_inGroup('Root')) {
                                    $url = $_CONF['site_admin_url'] . '/configuration.php';
                                    $label = $LANG01[129] . ' (' . COM_numberFormat(count($config->_get_groups())) . ')';
                                    $link_array[$LANG01[129]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }

                                // This will show the admin options for all installed plugins (if any)

                                for( $i = 0; $i < $num_plugins; $i++ ) {
                                    $plg = current( $plugin_options );

                                    $url = $plg->adminurl;
                                    $label = $plg->adminlabel;

                                    if( empty( $plg->numsubmissions )) {
                                        $label .= ' (N/A)';
                                    } else {
                                        $label .= ' (' . COM_numberFormat( $plg->numsubmissions ) . ')';
                                    }
                                    $link_array[$plg->adminlabel] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;

                                    next( $plugin_options );
                                }

                                if(( $_CONF['allow_mysqldump'] == 1 ) AND ( $_DB_dbms == 'mysql' ) AND SEC_inGroup( 'Root' )) {
                                    $url = $_CONF['site_admin_url'] . '/database.php';
                                    $label = $LANG01[103] . ' (N/A)';
                                    $link_array[$LANG01[103]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }
                                if( SEC_inGroup( 'Root' )) {
                                    $url = $_CONF['site_admin_url'] . '/logview.php';
                                    $label = $LANG01['logview'] . ' (N/A)';
                                    $link_array[$LANG01['logview']] = '<li><a href="' . $url .'">' . $label . '</a></li>' . LB;
                                }

                                if( $_CONF['link_documentation'] == 1 ) {
                                    $doclang = COM_getLanguageName();
                                    if ( @file_exists($_CONF['path_html'] . 'docs/' . $doclang . '/index.html') ) {
                                        $docUrl = $_CONF['site_url'].'/docs/'.$doclang.'/index.html';
                                    } else {
                                        $docUrl = $_CONF['site_url'].'/docs/english/index.html';
                                    }
                                    $url = $docUrl;
                                    $label = $LANG01[113] . ' (N/A)';
                                    $link_array[$LANG01[113]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }

                                if( SEC_inGroup( 'Root' )) {
                                    $url = 'http://www.glfusion.org/versionchecker.php?version=' . GVERSION;
                                    $label = $LANG01[107] . ' (' . GVERSION . ')';
                                    $link_array[$LANG01[107]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }
                                if (SEC_isModerator()) {
                                    $url = $_CONF['site_admin_url'] . '/moderation.php';
                                    $label = $LANG01[10] . ' (' . COM_numberFormat( $modnum ) . ')';
                                    $link_array[$LANG01[10]] = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                }

                                if( $_CONF['sort_admin'] ) {
                                    uksort( $link_array, 'strcasecmp' );
                                }
                                // C&C entry
                                $url = $_CONF['site_admin_url'] . '/index.php';
                                $label = $LANG29[34];
                                $menu_item = '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;

                                $link_array = array( $menu_item ) + $link_array;

                                foreach( $link_array as $link ) {
                                    $menu .= $link;
                                }

                                $menu .= '</ul>' . LB . '</li>' . LB;
                            }
                        }
                        break;

                    case 3 : // topics menu
                        if ( $this->id != 0 && $this->access > 0 && $parentaclass != '' ) {
                            $menu .= "<li>" . '<a class="' . $parentaclass . '" name="'.$parentaclass.'" href="#">' . strip_tags($this->label) . '</a>' . LB;
                        } else {
                            $menu .= "<li>" . '<a href="#">' . strip_tags($this->label) . '</a></li>' . LB;
                        }
                        if ( $this->id == 0 && $ulclass != '' ) {
                            $menu .= '<ul class="' . $ulclass . '">' . LB;
                        } else {
                            $menu .= '<ul>' . LB;
                        }
                        $langsql = COM_getLangSQL( 'tid' );
                        if( empty( $langsql )) {
                            $op = 'WHERE';
                        } else {
                            $op = 'AND';
                        }

                        $sql = "SELECT tid,topic,imageurl FROM {$_TABLES['topics']}" . $langsql;
                        if( !empty( $_USER['uid'] ) && ( $_USER['uid'] > 1 )) {
                            $tids = DB_getItem( $_TABLES['userindex'], 'tids',
                                                "uid = {$_USER['uid']}" );
                            if( !empty( $tids )) {
                                $sql .= " $op (tid NOT IN ('" . str_replace( ' ', "','", $tids )
                                     . "'))" . COM_getPermSQL( 'AND' );
                            } else {
                                $sql .= COM_getPermSQL( $op );
                            }
                        } else {
                            $sql .= COM_getPermSQL( $op );
                        }
                        if( $_CONF['sortmethod'] == 'alpha' ) {
                            $sql .= ' ORDER BY topic ASC';
                        } else {
                            $sql .= ' ORDER BY sortnum';
                        }
                        $result = DB_query( $sql );

                        if( $_CONF['showstorycount'] ) {
                            $sql = "SELECT tid, COUNT(*) AS count FROM {$_TABLES['stories']} "
                                 . 'WHERE (draft_flag = 0) AND (date <= NOW()) '
                                 . COM_getPermSQL( 'AND' )
                                 . ' GROUP BY tid';
                            $rcount = DB_query( $sql );
                            while( $C = DB_fetchArray( $rcount )) {
                                $storycount[$C['tid']] = $C['count'];
                            }
                        }

                        if( $_CONF['showsubmissioncount'] ) {
                            $sql = "SELECT tid, COUNT(*) AS count FROM {$_TABLES['storysubmission']} "
                                 . ' GROUP BY tid';
                            $rcount = DB_query( $sql );
                            while( $C = DB_fetchArray( $rcount )) {
                                $submissioncount[$C['tid']] = $C['count'];
                            }
                        }

                        while( $A = DB_fetchArray( $result ) ) {
                            $topicname = stripslashes( $A['topic'] );
                            $url =  $_CONF['site_url'] . '/index.php?topic=' . $A['tid'];
                            $label = $topicname;

                            $countstring = '';
                            if( $_CONF['showstorycount'] || $_CONF['showsubmissioncount'] ) {
                                $countstring .= ' (';
                                if( $_CONF['showstorycount'] ) {
                                    if( empty( $storycount[$A['tid']] )) {
                                        $countstring .= 0;
                                    } else {
                                        $countstring .= COM_numberFormat( $storycount[$A['tid']] );
                                    }
                                }
                                if( $_CONF['showsubmissioncount'] ) {
                                    if( $_CONF['showstorycount'] ) {
                                        $countstring .= '/';
                                    }
                                    if( empty( $submissioncount[$A['tid']] )) {
                                        $countstring .= 0;
                                    } else {
                                        $countstring .= COM_numberFormat( $submissioncount[$A['tid']] );
                                    }
                                }

                                $countstring .= ')';
                            }
                            $label .= $countstring;
                            $menu .= '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                        }
                        $menu .= '</ul>' . LB . '</li>' . LB;
                        break;

                    case 4 : // static pages menu
                        if ( $this->id != 0 && $this->access > 0 && $parentaclass != '' ) {
                            $menu .= "<li>" . '<a class="' . $parentaclass . '" href="#">' . strip_tags($this->label) . '</a>' . LB;
                        } else {
                            $menu .= "<li>" . '<a href="#">' . strip_tags($this->label) . '</a></li>' . LB;
                        }
                        if ( $this->id == 0 && $ulclass != '' ) {
                            $menu .= '<ul class="' . $ulclass . '">' . LB;
                        } else {
                            $menu .= '<ul>' . LB;
                        }
                        $order = '';
                        if (!empty ($_SP_CONF['sort_menu_by'])) {
                            $order = ' ORDER BY ';
                            if ($_SP_CONF['sort_menu_by'] == 'date') {
                                $order .= 'sp_date DESC';
                            } else if ($_SP_CONF['sort_menu_by'] == 'label') {
                                $order .= 'sp_label';
                            } else if ($_SP_CONF['sort_menu_by'] == 'title') {
                                $order .= 'sp_title';
                            } else { // default to "sort by id"
                                $order .= 'sp_id';
                            }
                        }
                        $result = DB_query ('SELECT sp_id, sp_label FROM ' . $_TABLES['staticpage'] . ' WHERE sp_onmenu = 1 AND sp_status = 1' . COM_getPermSql ('AND') . $order);
                        $nrows = DB_numRows ($result);
                        $menuitems = array ();
                        for ($i = 0; $i < $nrows; $i++) {
                            $A = DB_fetchArray ($result);
                            $url = COM_buildURL ($_CONF['site_url'] . '/staticpages/index.php?page=' . $A['sp_id']);
                            $label = $A['sp_label'];
                            $menu .= '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                        }
                        $menu .= '</ul>' . LB . '</li>' . LB;
                        break;
                    case 5 : // plugin menu
                        if ( $this->id != 0 && $this->access > 0 && $parentaclass != '' ) {
                            $menu .= "<li>" . '<a class="' . $parentaclass . '" href="#">' . strip_tags($this->label) . '</a>' . LB;
                        } else {
                            $menu .= "<li>" . '<a href="#">' . strip_tags($this->label) . '</a>' . LB;
                        }
                        if ( $this->id == 0 && $ulclass != '' ) {
                            $menu .= '<ul class="' . $ulclass . '">' . LB;
                        } else {
                            $menu .= '<ul>' . LB;
                        }
                        $plugin_menu = PLG_getMenuItems();
                        if( count( $plugin_menu ) == 0 ) {
                            $this->access = 0;
                        } else {
                            for( $i = 1; $i <= count( $plugin_menu ); $i++ ) {
                                $url = current($plugin_menu);
                                $label = key($plugin_menu);
                                $menu .= '<li><a href="' . $url . '">' . $label . '</a></li>' . LB;
                                next( $plugin_menu );
                            }
                        }
                        $menu .= '</ul>' . LB . '</li>' . LB;
                        break;

                    case 6 : // header menu

                    default : // unknown
                }
                break;
            case '4' : // plugin
                $plugin_menus = _stPLG_getMenuItems();
                if ( isset($plugin_menus[$this->subtype]) ) {
                    $this->url = $plugin_menus[$this->subtype];
                } else {
                    $this->access = 0;
                }
                break;
            case '5' : // static page
                $this->url = COM_buildURL ($_CONF['site_url'] . '/staticpages/index.php?page=' . $this->subtype);
                break;
            case '6' : // external URL
                /*
                 * Check to see if any internal macros
                 */
                $this->replace_macros();
                break;
            case '7' : // php function
                $functionName = $this->subtype;
                if (function_exists($functionName)) {
                    /* Pass the type of menu to custom php function */
                    $menu = "<li>" . '<a class="' . $parentaclass . '" name="'.$parentaclass.'" href="#">' . strip_tags($this->label) . '</a>' . LB;
                    $menu .= $functionName();
                    $menu .= '</li>';
                }
                break;
            default : // unknown
                break;
        }
        if ( $this->id != 0 && $this->group_id == 998 && (SEC_inGroup('Root') || SEC_inGroup('sitetailor Admin')) ) {
            return $retval;
        }
        if ( $allowed == 0 ) {
            return $retval;
        }
        /* here we actually define the menu item.... */
        if ( $this->type == 3 || $this->type == 7) {
            $retval .= $menu;
        } else {
            if ( $this->id != 0 && $this->access > 0 ) {
                if ($this->isLastChild() && $lastclass != '') {
                    $lastClass = ' class="'.$lastclass.'"';
                } else {
                    $lastClass = '';
                }

                if ( $this->type == 1 && $parentaclass != '' ) {
                    $retval .= "<li".$lastClass.">" . '<a class="' . $parentaclass . '" name="'.$parentaclass.'" href="' . ($this->url == '' ? '#' : $this->url) . '">' . strip_tags($this->label) . '</a>' . LB;
                } else {
                    if ($this->type == 8 ) {
                        $retval .= "<li".$lastClass.'><a><strong>' . strip_tags($this->label) . '</strong></a></li>' . LB;
                    } else {
                        $retval .= "<li".$lastClass.">" . '<a href="' . $this->url . '"' . ($this->target != '' ? ' target="' . $this->target . '"' : '') . '>' . strip_tags($this->label) . '</a></li>' . LB;
                    }
                }
            }
        }
        if ( !empty($this->children)) {
            $howmany = $this->getChildcount();
            if ( $howmany > 0 ) {
                $children = $this->getChildren();
                if ( $this->id == 0 && $ulclass != '' ) {
                    $retval .= '<ul class="' . $ulclass . '">' . LB;
                } else {
                    $retval .= '<ul>' . LB;
                }
                foreach($children as $child) {
                    $meLevel++;
                    $retval .= $stMenu[$this->menu_id]['elements'][$child]->showTree($depth,$oulclass,$oliclass,$oparentaclass,$olastclass,$selected);
                    $meLevel--;
                }
                if ( $this->id == 0 ) {
                    $retval .= '</ul>' . LB;
                } else {
                    $retval .= '</ul>' . LB . '</li>' . LB;
                }
            }
        } else {
            if ($parentaclass != '' && $this->type == 1) {
                $retval .= '</li>';
            }
        }
        return $retval;
    }
}
?>
