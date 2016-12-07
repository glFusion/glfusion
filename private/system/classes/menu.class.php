<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | menu2.class.php                                                          |
// |                                                                          |
// | Menu elements class / functions                                          |
// +--------------------------------------------------------------------------+
// | Copyright (C)  2008-2016 by the following authors:                       |
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

class menu {
    var $id;
    var $name;
    var $type;
    var $active;
    var $group_id;
    var $menu_alignment;
    var $menu_elements = array();

    public function __construct( $menu_id = 0 )
    {
        if ( $menu_id > 0 ) {
            $this->getMenu($menu_id);
        } else {
            $this->id = 0;
        }
    }


    public static function getInstance( $menu_id = 0 )
    {
        static $instance = array();

        if (!isset($instance[$menu_id]) ) {
            $instance[$menu_id] = new menu($menu_id);
        }
        return $instance[$menu_id];
    }

    function getMenu($menu_id)
    {
        global $_TABLES;

        $result = DB_query("SELECT * FROM {$_TABLES['menu']} WHERE id = ". (int)$menu_id,1);
        if ( $result !== FALSE && DB_numRows($result) > 0 ) {
            $menu = DB_fetchArray($result);
            $this->id       = $menu['id'];
            $this->name     = $menu['menu_name'];
            $this->type     = $menu['menu_type'];
            $this->active   = $menu['menu_active'];
            $this->group_id = $menu['group_id'];
            $this->getElements();
        } else {
            return false;
        }
        return true;
    }

    function getElements()
    {
        global $_TABLES, $_GROUPS;

        $mbadmin = SEC_hasRights('menu.admin');
        $root    = SEC_inGroup('Root');

        $sql = "SELECT * FROM {$_TABLES['menu_elements']} WHERE menu_id=".(int) $this->id." ORDER BY element_order ASC";
        $elementResult = DB_query( $sql, 1);

        while ($A = DB_fetchArray($elementResult) ) {
            $element  = new menuElement();
            $element->constructor($A,$mbadmin,$root,$_GROUPS,1);
            if ( $element->access > 0 ) {
                $this->menu_elements[$element->id] = $element;
            }
        }

        foreach( $this->menu_elements as $id => $element) {
            if ($id != 0 && $element->pid != 0 && isset($this->menu_elements[$element->pid]->id) ) {
                $this->menu_elements[$element->pid]->setChild($element);
            }
        }
    }

    /*
     * turns the menu builder data structure into a fully set of menu
     * elements
     */
    function _parseMenu(  )
    {
        $returnArray    = array();

        foreach ( $this->menu_elements as $element ) {
            if ( $element->pid == 0 ) {
                $elementArray = $element->_parseElement();
                if ( $elementArray != NULL ) {
                    $returnArray[] = $elementArray;
                }
            }
        }
        return $returnArray;
    }


    /*
     * editTree for menu class
     */
    function editTree( ) {
         $returnArray = array();

        $depth = 1;
        foreach ( $this->menu_elements as $element ) {
            if ( $element->pid == 0 ) {
                $elementArray = $element->_editElement($depth);
                if ( $elementArray != NULL ) {
                    $returnArray = array_merge($returnArray,$elementArray);
                }
            }
        }
        return $returnArray;
    }

    function reorderMenu( $pid ) {
        global $_TABLES;

        $menu_id = $this->id;
        $orderCount = 10;

        $sql = "SELECT id,`element_order` FROM {$_TABLES['menu_elements']} WHERE menu_id=".$menu_id." AND pid=" . $pid . " ORDER BY `element_order` ASC";
        $result = DB_query($sql);
        while ($M = DB_fetchArray($result)) {
            $M['element_order'] = $orderCount;
            $orderCount += 10;
            DB_query("UPDATE {$_TABLES['menu_elements']} SET `element_order`=" . $M['element_order'] . " WHERE menu_id=".$menu_id." AND id=" . (int) $M['id'] );
        }
    }
}

/*
 * menu element class
 */

class menuElement {
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
    var $hidden;            // hidden menu

    function __construct () {
        $this->children         = array();
        $this->id               = 0;
        $this->menu_id          = 0;
        $this->group_id         = 0;
    }

    function constructor( $element, $meadmin, $root, $groups, $edit=0 ) {
        $this->id               = isset($element['id']) ? $element['id'] : '';
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
        if ( $edit == 1 ) {
            $this->access = 3;
        } else {
            $this->setAccessRights($meadmin,$root,$groups);
        }
    }

    function replace_macros() {
        global $_CONF;
        $this->url = str_replace( "%version%", GVERSION, $this->url );
        $this->url = str_replace( "%site_url%", $_CONF['site_url'], $this->url );
        $this->url = str_replace( "%site_admin_url%", $_CONF['site_admin_url'], $this->url );
        return;
    }

    function setChild($el) {
        $this->children[$el->id] = $el;
    }

    function saveElement( ) {
        global $_TABLES;

        $this->label = DB_escapeString($this->label);
        $this->url = DB_escapeString($this->url);

        $sqlFieldList  = 'id,pid,menu_id,element_label,element_type,element_subtype,element_order,element_active,element_url,element_target,group_id';
        $sqlDataValues = "$this->id,$this->pid,'".DB_escapeString($this->menu_id)."','$this->label',$this->type,'$this->subtype',$this->order,$this->active,'$this->url','$this->target',$this->group_id";
        DB_save($_TABLES['menu_elements'], $sqlFieldList, $sqlDataValues);
    }

    function createElementID( $menu_id ) {
        global $_TABLES;

        $sql = "SELECT MAX(id) + 1 AS next_id FROM " . $_TABLES['menu_elements'];
        $result = DB_query( $sql );
        $row = DB_fetchArray( $result );
        $id = $row['next_id'];
        if ( $id < 1 ) {
            $id = 1;
        }
        if ( $id == 0 ) {
            COM_errorLog("MenuBuilder: Error - Returned 0 as element id");
            $id = 1;
        }
        return $id;
    }


    function setAccessRights( $meadmin, $root, $groups ) {
        global $_USER;

        if ($meadmin || $root) {
            $this->access = 3;
        } else if ( in_array( $this->group_id, $groups ) ) {
            $this->access = 3;
        } else {
            $this->access = 0;
        }
    }

    function getChildren()
    {
        return $this->children;
    }

    function _editElement( $depth ) {
        global $_CONF, $LANG_MB01, $LANG_MB_TYPES,$LANG_MB_GLTYPES,$LANG_MB_GLFUNCTION;

        $data_arr = array();

        $menu = menu::getInstance($this->menu_id);

        $plugin_menus = _mbPLG_getMenuItems();

        $px = ($depth - 1 ) * 15;

        $elementDetails = $this->label . '::';
        $elementDetails .= '<b>' . $LANG_MB01['type'] . ':</b> '
                        . $LANG_MB_TYPES[$this->type] . '<br/>';
        switch ($this->type) {
            case 1 :
                break;
            case 2 :
                $elementDetails .= '<b>' . $LANG_MB_TYPES[$this->type] . '</b> '
                                . $LANG_MB_GLFUNCTION[$this->subtype] . '<br/>';
                break;
            case 3 :
                $elementDetails .= '<b>' . $LANG_MB_TYPES[$this->type] . ':</b> '
                                    . $LANG_MB_GLTYPES[$this->subtype] . '<br/>';
                break;
            case 4 :
                $elementDetails .= '<b>' . $LANG_MB_TYPES[$this->type] . ':</b> '
                                    . $this->subtype . '<br/>';
                break;
            case 5 :
                $elementDetails .= '<b>' . $LANG_MB_TYPES[$this->type] . ':</b> '.$this->subtype.'<br/>';
                break;
            case 6 :
                $elementDetails .= '<b>' . $LANG_MB_TYPES[$this->type] . ':</b> '
                                    . $this->url . '<br/>';
                break;
            case 7 :
                $elementDetails .= '<b>' . $LANG_MB_TYPES[$this->type] . ':</b> '
                                    . $this->subtype . '<br/>';
                break;
            case 9 :
                $elementDetails .= '<b>' . $LANG_MB_TYPES[$this->type] . ':</b> '
                                    . $this->subtype . '<br />'   ;
        }

        $item['indent'] = $px;
        $item['label']  = $this->label;
        $item['enabled'] = $this->active;
        $item['info'] = $elementDetails;
        $item['edit'] = $this->id;
        $item['delete'] = $this->id;
        $item['order'] = $this->order;
        $item['type'] = $this->type;
        $item['id']   = $this->id;
        $item['menu_id'] = $this->menu_id;
        $data_arr[] = $item;

        if ( !empty($this->children)) {
            $children = $this->getChildren();
            if ( is_array($children) ) {
                foreach($children as $child) {
                    $depth++;
                    if ( is_object($child) ) {
                        $carray = $child->_editElement($depth);
                        $data_arr = array_merge($data_arr,$carray);
                    }
                    $depth--;
                }
            }
        }
        return $data_arr;
    }


    function getChildcount() {

        $numChildren = 0;
        $children = $this->getChildren();

        if ( !is_array($children) ) {
            return $numChildren;
        }

        foreach ($children as $element) {
            if ( $element->active > 0 ) {
                if ( $element->hidden == 1 ) {
                    if ($element->access == 3 ) {
                        $numChildren++;
                    }
                } else {
                    $numChildren++;
                }
            }
        }
        return $numChildren;

    }

    function isLastChild() {
        $menu = menu::getInstance( $this->pid );

        $pid = $this->pid;
        $children = $menu->menu_elements[$pid]->getChildren();
        $arrayIndex = count($children)-1;
        if ( $this->id == $children[$arrayIndex] ) {
            return true;
        }
        return false;
    }


    function _parseElement(  )
    {
        global $_SP_CONF,$_USER, $_TABLES, $LANG01, $_CONF,$_GROUPS;

        $returnArray    = array();
        $childArray     = array();
        $item_array     = array();

        if ( $this->active != 1 && $this->id != 0 ) {
            return NULL;
        }

        $nonloggedinusergroup = DB_getItem($_TABLES['groups'],'grp_id','grp_name="Non-Logged-in Users"');

        if ( $this->group_id == $nonloggedinusergroup && !COM_isAnonUser()) return NULL;

        if (isset($_REQUEST['topic']) ){
            $topic = COM_applyFilter($_REQUEST['topic']);
        } else {
            $topic = '';
        }

        if ( COM_isAnonUser() ) {
            $anon = 1;
        } else {
            $anon = 0;
        }
        $allowed = true;

        if ( $this->id != 0 && !SEC_inGroup($this->group_id) ) {
            return NULL;
        }

        if ( $this->group_id == 1 && !isset($_GROUPS['Root']) ) {
            return NULL;
        }
        // static page fix
        if ($this->type == ET_PLUGIN && $this->subtype == 'staticpages') {
            $this->type = ET_FUSION_MENU;
            $this->subtype = STATICPAGE_MENU;
        }

        switch ( $this->type ) {

            case ET_SUB_MENU :
                $this->replace_macros();
                break;
            case ET_FUSION_ACTION :
                switch ($this->subtype) {
                    case 0: // home
                        $this->url = $_CONF['site_url'] . '/';
                        break;
                    case 1: // contribute
                        if ( $anon && ( $_CONF['loginrequired'] || $_CONF['submitloginrequired'] )) {
                            return NULL;
                        }
                        if ( empty( $topic )) {
                            $this->url = $_CONF['site_url'] . '/submit.php?type=story';
                        } else {
                            $this->url = $_CONF['site_url']
                                 . '/submit.php?type=story&amp;topic=' . $topic;
                        }
                        $label = $LANG01[71];
                        break;
                    case 2: // directory
                        if ( $anon && ( $_CONF['loginrequired'] ||
                                $_CONF['directoryloginrequired'] )) {
                            return NULL;
                        }
                        $this->url = $_CONF['site_url'] . '/directory.php';
                        if ( !empty( $topic )) {
                            $this->url = COM_buildUrl( $this->url . '?topic='
                                                 . urlencode( $topic ));
                        }
                        break;
                    case 3: // prefs
                        if ( $anon && ( $_CONF['loginrequired'] ||
                                $_CONF['profileloginrequired'] )) {
                            return NULL;
                        }
                        $this->url = $_CONF['site_url'] . '/usersettings.php?mode=edit';
                        break;
                    case 4: // search
                        if ( $anon && ( $_CONF['loginrequired'] ||
                                $_CONF['searchloginrequired'] )) {
                            return NULL;
                        }
                        $this->url = $_CONF['site_url'] . '/search.php';
                        break;
                    case 5: // stats
                        if ( !SEC_hasRights('stats.view') ) {
                            return NULL;
                        }
                        $this->url = $_CONF['site_url'] . '/stats.php';
                        break;
                    default : // unknown?
                        $this->url = $_CONF['site_url'] . '/';
                        break;
                }
                break;
            case ET_FUSION_MENU :
                $this->url = '';
                switch ($this->subtype) {
                    case USER_MENU :
// if anonymous user - show login entry
                        if ( COM_isAnonUser() ) {
                            $this->label = $LANG01[58];
                            $this->url   = $_CONF['site_url'] . '/users.php';
                            $this->target = '';
                            break;
                        }
// logged-in user see My Account entry
                        $item_array = getUserMenu();
                        $this->label = $LANG01[47];
                        break;

                    case ADMIN_MENU :
                        $this->url = $_CONF['site_admin_url'];
                        $item_array = getAdminMenu();
                        break;

                    case TOPIC_MENU :
                        $item_array = getTopicMenu();
                        break;

                    case STATICPAGE_MENU :
                        $item_array = array();
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
                            $url = COM_buildURL ($_CONF['site_url'] . '/page.php?page=' . $A['sp_id']);
                            $label = $A['sp_label'];
                            $item_array[] = array('label' => $label, 'url' => $url);
                        }
                        break;

                    case PLUGIN_MENU :
                        $item_array = array();
                        $plugin_menu = PLG_getMenuItems();
                        if ( count( $plugin_menu ) == 0 ) {
                            $this->access = 0;
                        } else {
                            for( $i = 1; $i <= count( $plugin_menu ); $i++ ) {
                                $url = current($plugin_menu);
                                $label = key($plugin_menu);
                                $item_array[] = array('label' => $label, 'url' => $url);
                                next( $plugin_menu );
                            }
                        }
                        break;

                    case HEADER_MENU :

                    default :
                }
                break;
            case ET_PLUGIN :
                $plugin_menus = _mbPLG_getMenuItems();
                if ( isset($plugin_menus[$this->subtype]) ) {
                    $this->url = $plugin_menus[$this->subtype];
                } else {
                    $this->access = 0;
                    $allowed = 0;
                }
                break;
            case ET_STATICPAGE :
                $this->url = COM_buildURL ($_CONF['site_url'] . '/page.php?page=' . $this->subtype);
                break;
            case ET_URL :
                $this->replace_macros();
                break;
            case ET_PHP :
                $functionName = $this->subtype;
                if (function_exists($functionName)) {
                    $item_array = $functionName();
                }
                break;

            case ET_TOPIC :
                $this->url = $_CONF['site_url'] . '/index.php?topic=' . $this->subtype;
                break;
            default :
                break;
        }
        if ( $allowed == 0 || $this->access == 0 ) {
            return NULL;
        }

        if ( $this->type == ET_FUSION_MENU || $this->type == ET_PHP ) {
            $childArray = $item_array;
        } else {
            if ( !empty($this->children)) {
                $howmany = $this->getChildcount();
                if ( $howmany > 0 ) {
                    $children = $this->getChildren();
                    foreach($children as $child) {
                        $elementArray = $child->_parseElement();
                        if ( $elementArray != NULL ) {
                            $childArray[] = $elementArray;
                        }
                    }
                }
            } else {
                $childArray = NULL;
            }
        }
        $returnArray = array('label' => $this->label,
                             'url'   => $this->url,
                             'target'=> $this->target,
                             'children' => (is_array($childArray) ? $childArray : NULL)  );

        return $returnArray;
    }

}
?>