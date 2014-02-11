<?php

require_once 'lib-common.php';
require_once $_CONF['path_system'] . 'classes/menu2.class.php';

/*
 * pull the data for a specific menu from the DB
 *
 * returns a menu object

 */
function initMenu($menuname, $skipCache=false) {
    global $_GROUPS, $_TABLES, $_USER;

    $cacheInstance = 'menuobject_' .$menuname . '_' . CACHE_security_hash() . '__data';
    if ( $skipCache == false ) {
        $retval = CACHE_check_instance($cacheInstance, 0);
        if ( $retval ) {
            $menu = unserialize($retval);
            return $menu ;
        }
    }
    $mbadmin = SEC_hasRights('menu.admin');
    $root    = SEC_inGroup('Root');

    if (COM_isAnonUser()) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }
    $sql = "SELECT * FROM {$_TABLES['menu']} WHERE menu_active=1 AND menu_name='".DB_escapeString($menuname)."'";
    $result = DB_query("SELECT * FROM {$_TABLES['menu']} WHERE menu_active=1 AND menu_name='".DB_escapeString($menuname)."'",1);
    $menuRow = DB_fetchArray($result);
    if ( $menuRow ) {
        $menu = new menu2();

        $menu->id = $menuRow['id'];
        $menu->name = $menuRow['menu_name'];
        $menu->type = $menuRow['menu_type'];
        $menu->active = $menuRow['active'];
        $menu->group_id = $menuRow['group_id'];

        if ($mbadmin || $root) {
            $menu->permission = 3;
        } else {
            if ( $menuRow['group_id'] == 998 ) {
                if( COM_isAnonUser() ) {
                    $menu->permission = 3;
                } else {
                    $menu->permission =  0;
                    return NULL;
                }
            } else {
                if ( in_array( $menuRow['group_id'], $_GROUPS ) ) {
                    $menu->permission =  3;
                } else {
                    return NULL;
                }
            }
        }
        $sql = "SELECT * FROM {$_TABLES['menu_elements']} WHERE menu_id=".(int) $menu->id." AND element_active = 1 ORDER BY element_order ASC";
        $elementResult      = DB_query( $sql, 1);
        $element            = new menuElement2();
        $element->id        = 0;
        $element->menu_id   = $menuID;
        $element->label     = 'Top Level Menu';
        $element->type      = -1;
        $element->pid       = 0;
        $element->order     = 0;
        $element->url       = '';
        $element->owner_id  = $mbadmin;
        $element->group_id  = $root;
        if ( $mbadmin ) {
            $element->access = 3;
        }
        $menu->menu_elements[0] = $element;

        while ($A = DB_fetchArray($elementResult) ) {
            $element  = new menuElement2();
            $element->constructor($A,$mbadmin,$root,$_GROUPS);
            if ( $element->access > 0 ) {
                $menu->menu_elements[$A['id']] = $element;
            }
        }
    }

    if ( isset($menu->menu_elements) && is_array( $menu->menu_elements ) ) {
        foreach($menu->menu_elements as $element) {
            if ( $element->id != 0 && is_object($menu->menu_elements[$element->pid]) ) {
                $menu->menu_elements[$element->pid]->setChild($element);
            }
        }
    }

    $cacheMenu = serialize($menu);
    CACHE_create_instance($cacheInstance, $cacheMenu, 0);
    return $menu;
}

/*
 * New assembleMenu function - this one only builds the menu structe
 * into an array - it does not do any styling.
 *
 * takes a menu object and turns it into a full data structure
 *
 * first check to see if the data structure is on disk (cache)
 *
 * QUESTION: Do we need to cache this??? If we dont', then we'll
 * get updated stats like number of users, etc. in the menu
 * How expensive is this call??
 *
 *
 */
function assembleMenu($name, $skipCache=false) {

    $menuData = NULL;

    $lang = COM_getLanguageId();
    if (!empty($lang)) {
        $menuName = $name . '_'.$lang;
    } else {
        $menuName = $name;
    }

    $cacheInstance = 'menudata_' .$menuName . '_' . CACHE_security_hash() . '__data';

    if ( $skipCache == false ) {
        $cacheCheck = CACHE_check_instance($cacheInstance, 0);
        if ( $cacheCheck ) {
            $menuData = unserialize($cacheCheck);
            return $menuData;
        }
    }

    $menuObject = initMenu($menuName, $skipCache);
    if ( $menuObject != NULL ) {
        $menuData = $menuObject->_parseMenu($menuObject->menu_elements[0]);
        $menuData['type'] = $menuObject->type;
        $cacheMenu = serialize($menuData);
        CACHE_create_instance($cacheInstance, $cacheMenu, 0);
    }
    return $menuData;
}

/*
 * QUESTION: do we cache this one or not?
 *
 *
 *
 */

function displayMenu( $menuName, $skipCache=false ) {
    global $_CONF;

    $retval = '';

    $structure = assembleMenu($menuName, $skipCache);
    if ( $structure == NULL ) {
        return $retval;
    }

    $T = new Template( $_CONF['path_layout'].'/menu/');

    switch ( $structure['type'] ) {
        case MENU_HORIZONTAL_CASCADING :
            $template_file = 'menu_horizontal_cascading.thtml';
            break;
        case MENU_HORIZONTAL_SIMPLE :
            $template_file = 'menu_horizontal_cascading.thtml';
            break;
        case MENU_VERTICAL_CASCADING :
            $template_file = 'menu_horizontal_cascading.thtml';
            break;
        case MENU_VERTICAL_SIMPLE :
            $template_file = 'menu_horizontal_cascading.thtml';
            break;
        default:
            return $retval;
            break;
    }

    $T->set_file (array(
        'page'      => $template_file,
    ));

    // should probably get the name of the menu so we can pass it too...

    $T->set_block('page', 'Elements', 'element');

    foreach($structure['children'] AS $item ) {
        $T->set_var(array(
                        'label' => $item['label'],
                        'url'   => $item['url']
                    ));
        if ( $item['children'] != NULL && is_array($item['children']) ) {
            $T->set_var('haschildren',true);
            $childrenHTML = displayMenuChildren($structure['type'],$item['children']);
            $T->set_var('children',$childrenHTML);
        }
        $T->parse('element', 'Elements',true);
        $T->unset_var('haschildren');
        $T->unset_var('children');
    }

    $T->set_var('wrapper',true);

    $T->parse('output','page');
    $retval = $T->finish($T->get_var('output'));
    return $retval;
}

/*
 * handle the children elements when building the menu HTML
 */
function displayMenuChildren( $type, $elements ) {
    global $_CONF;

    $retval = '';

    $C = new Template( $_CONF['path_layout'].'/menu/');

    switch ( $type ) {
        case MENU_HORIZONTAL_CASCADING :
            $template_file = 'menu_horizontal_cascading.thtml';
            break;
        case MENU_HORIZONTAL_SIMPLE :
            $template_file = 'menu_horizontal_cascading.thtml';
            break;
        case MENU_VERTICAL_CASCADING :
            $template_file = 'menu_horizontal_cascading.thtml';
            break;
        case MENU_VERTICAL_SIMPLE :
            $template_file = 'menu_horizontal_cascading.thtml';
            break;
        default:
            return $retval;
            break;
    }
    $C->set_file (array(
        'page'      => $template_file,
    ));

    $C->set_block('page', 'Elements', 'element');

    foreach ($elements AS $child) {
        $C->set_var(array(
                        'label' => $child['label'],
                        'url'   => $child['url']
                    ));
        if ( $child['children'] != NULL && is_array($child['children']) ) {
            $C->set_var('haschildren',true);
            $childHTML = displayMenuChildren($type, $child['children']);
            $C->set_var('children',$childHTML);
        }
        $C->parse('element', 'Elements',true);
        $C->unset_var('haschildren');
        $C->unset_var('children');
    }
    $C->parse('output','page');
    $retval = $C->finish($C->get_var('output'));

    return $retval;
}


$display = displayMenu('navigation',false);

dummyheader();
echo $display;



// temp stuff...

function dummyheader()
{
echo '<style>';
echo '
/* horizontal cascading menu CSS - left alignment is default */

.menu-horizontal-cascading,
.menu-horizontal-cascading * {
    margin: 0;
    padding:0;
    list-style: none;
}

/* general style for menu */
.menu-horizontal-cascading {
    width:100%;
    height:auto;
    z-index:500;
    background:#151515 url(images/menu/menu_bg.gif) repeat;
}

/* general link styles */
.menu-horizontal-cascading a {
    text-decoration:    none;
    white-space:        nowrap;
    display:            block;
    float:              left;
    line-height:        2.2em;
    font-weight:        700;
    height:             2.2em;
    font-size:          1em;
    padding:            0 1.2em;
    color:              #ccc !important;  /*Top Menu Text*/
}

/* top level UL */
.menu-horizontal-cascading ul {
    position:   absolute;
    top:        -999em;
    width:      14.74em;
}

/* top level li elements */
.menu-horizontal-cascading li {
    list-style: none;
    position:   relative;
    display:    block;
    margin:     0;
    padding:    0;
    float:      left;  /*Top Menu alignment*/
}

/* second level UL elements */
.menu-horizontal-cascading ul ul {
    border-left:    1px solid #333;
    border-right:   1px solid #000;
}

/* second level LI elements */
.menu-horizontal-cascading li li {
    background:     none;
    margin:         0;
    position:       relative;
    float:          none;
    width:          100%;
    background:     #151515;
    border-top:     1px solid #333333;  /*Sub Menu Highlight*/
    border-bottom:  1px solid #000000;  /*Sub Menu Shadow*/
}

/* second level LI element href hover */
.menu-horizontal-cascading li li a:hover {
    background: none;
}

/* second level LI element href */
.menu-horizontal-cascading li li a {
    font-family:    Helvetica, Arial, sans-serif;
    font-size:      100%;
    font-weight:    400;
    display:        block;
    text-decoration:none;
    float:          none;
    height:         2.3em;
    line-height:    2.3em;
    text-indent:    1.3em;
    width:          98%;
    margin-left:    1%;
    padding:        0;
}

/* hover item for li (all levels) */
.menu-horizontal-cascading li:hover {
    z-index:        510;
    background:     #3667c0 url(images/menu/menu_hover_bg.gif) repeat;
    color:          #fff;

}

/* second level (and below) ul (child of a li) */
.menu-horizontal-cascading li ul {
    float:          none;
    left:           -999em;
    position:       absolute;
    width:          14.6em;
    z-index:        500;
}

.menu-horizontal-cascading li:hover ul,
.menu-horizontal-cascading li.sfHover ul {
    left:           -1px;
    top:            2.2em;  /* calculated from line height or main menu */
}

.menu-horizontal-cascading li:hover li ul,
.menu-horizontal-cascading li.sfHover li ul,
.menu-horizontal-cascading li li:hover li ul,
.menu-horizontal-cascading li li.sfHover li ul,
.menu-horizontal-cascading li li li:hover li ul,
.menu-horizontal-cascading li li li.sfHover li ul {
    top:            -999em;
}
.menu-horizontal-cascading li li:hover ul,
.menu-horizontal-cascading li li.sfHover ul,
.menu-horizontal-cascading li li li:hover ul,
.menu-horizontal-cascading li li li.sfHover ul,
.menu-horizontal-cascading li li li li:hover ul,
.menu-horizontal-cascading li li li li.sfHover ul {
    left:           14.65em;
    top:            -1px;
    z-index:        500;
}

.menu-horizontal-cascading li:hover a,
.menu-horizontal-cascading li.sfHover a,
.menu-horizontal-cascading li:hover a:hover,
.menu-horizontal-cascading li.sfHover a:hover {
    color:          #fff;  /*Top Menu Text Hover / Sub Menu Text*/
}

.menu-horizontal-cascading li:hover li:hover a:hover,
.menu-horizontal-cascading li.sfHover li.sfHover a:hover {
    color:          #fff;     /* Sub Menu Text Hover */
}

.menu-horizontal-cascading ul ul {
    background:     #151515;  /*Sub Menu BG*/
    border-left:    1px solid #333;  /*Sub Menu Highlight*/
    border-right:   1px solid #000;  /*Sub Menu Shadow*/
}

.menu-horizontal-cascading li li a.parent,
.menu-horizontal-cascading li li a.parent:hover {
    background:transparent url(images/menu/menu_parent.png) no-repeat scroll 95% 50%;
}
.menu-horizontal-cascading {
    float:          left;
}

/* for right aligned menus */
.menu-horizontal-cascading-right li {
    float:          right;
}

<!--[if IE 7]>
.menu-horizontal-cascading {
    width:          100%;
}
.menu-horizontal-cascading li ul {
    width:          177px;
}
<![endif]-->

/* end of horizontal cascading menu */
';
echo '</style>';
}