<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-menu.php                                                             |
// |                                                                          |
// | glFusion menu library.                                                   |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2018 by the following authors:                        |
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

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

define('MENU_TOP_LEVEL',-1);
define('MENU_HORIZONTAL_CASCADING',1);
define('MENU_HORIZONTAL_SIMPLE',2);
define('MENU_VERTICAL_CASCADING',3);
define('MENU_VERTICAL_SIMPLE',4);

define('ET_SUB_MENU',1);
define('ET_FUSION_ACTION',2);
define('ET_FUSION_MENU',3);
define('ET_PLUGIN',4);
define('ET_STATICPAGE',5);
define('ET_URL',6);
define('ET_PHP',7);
define('ET_LABEL',8);
define('ET_TOPIC',9);

define('USER_MENU',1);
define('ADMIN_MENU',2);
define('TOPIC_MENU',3);
define('STATICPAGE_MENU',4);
define('PLUGIN_MENU',5);
define('HEADER_MENU',6);

/*
 * pull the data for a specific menu from the DB
 *
 * returns a menu object

 */
function initMenu($menuname, $skipCache=false) {
    global $_GROUPS, $_TABLES, $_USER;

    $menu = NULL;

    $c = glFusion\Cache::getInstance();
    $key = 'menu_'.$menuname . '_' . $c->securityHash();

    if ( $skipCache == false ) {
        if ( $c->has($key) ) return unserialize($c->get($key));
    }

    $mbadmin = SEC_hasRights('menu.admin');
    $root    = SEC_inGroup('Root');

    if (COM_isAnonUser()) {
        $uid = 1;
    } else {
        $uid = $_USER['uid'];
    }
    $result = DB_query("SELECT * FROM {$_TABLES['menu']} WHERE menu_active=1 AND menu_name='".DB_escapeString($menuname)."'",1);
    $menuRow = DB_fetchArray($result);
    if ( $menuRow ) {
        $menu = new menu();

        $menu->id = $menuRow['id'];
        $menu->name = $menuRow['menu_name'];
        $menu->type = $menuRow['menu_type'];
        $menu->active = $menuRow['menu_active'];
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
        $menu->getElements();

        $cacheMenu = serialize($menu);

        $c->set($key,$cacheMenu,'menu');
    }

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

    if ( $skipCache == false ) {
        $c = glFusion\Cache::getInstance();
        $key = 'menu_'.$menuName.'_'.$c->securityHash();
        if ($c->has($key)) return unserialize($c->get($key));
    }
    $menuObject = initMenu($menuName, $skipCache);
    if ( $menuObject != NULL ) {
        $menuData = $menuObject->_parseMenu();
        $menuData['type'] = $menuObject->type;
        $cacheMenu = serialize($menuData);
        if ( $skipCache == false ) {
            $c->set($key,$cacheMenu,'menu');
        }
    }

    return $menuData;
}

function getMenuTemplate($menutype, $menuname) {
    global $_CONF;

    $noSpaceName = strip_tags(strtolower(str_replace(" ","_",$menuname)));

    switch ( $menutype ) {
        case MENU_HORIZONTAL_CASCADING :
            $template_file = 'menu_horizontal_cascading';
            break;
        case MENU_HORIZONTAL_SIMPLE :
            $template_file = 'menu_horizontal_simple';
            break;
        case MENU_VERTICAL_CASCADING :
            $template_file = 'menu_vertical_cascading';
            break;
        case MENU_VERTICAL_SIMPLE :
            $template_file = 'menu_vertical_simple';
            break;
        default:
            return $retval;
            break;
    }
    //see if custom template exists
    $tFile = $template_file . '_'.$noSpaceName.'.thtml';
    $sFile = $template_file . '.thtml';
    if ( file_exists($_CONF['path_layout'].'menu/custom/'.$tFile) ) {
        return $tFile;
    } elseif ( file_exists($_CONF['path_layout'].'menu/'.$tFile) ) {
        return $tFile;
    } elseif ( file_exists($_CONF['path_themes'].'cms/menu/'.$tFile ) ) {
        return $tFile;
    }
    return $sFile;
}

/*
 * Render the menu
 */

function displayMenu( $menuName, $skipCache=false ) {
    global $_CONF;

    $retval = '';

    if ( $skipCache == false ) {
        $c = glFusion\Cache::getInstance();
        $key = 'menufile_'.$menuName.'__'.$c->securityHash(true,true);
        if ($c->has($key)) {
            return $c->get($key);
        }
    }
    $structure = assembleMenu($menuName, $skipCache);
    if ( $structure == NULL ) {
        return $retval;
    }

    $menuType = $structure['type'];
    unset($structure['type']);

    $T = new Template( $_CONF['path_layout'].'/menu/');

    $template_file = getMenuTemplate($menuType, $menuName);

    $T->set_file (array(
        'page'      => $template_file,
    ));

    $T->set_var('menuname',$menuName);

    $T->set_block('page', 'Elements', 'element');
    $lastElement = end($structure);
    foreach($structure as $item) {

        $T->set_var(array(
                        'label' => $item['label'],
                        'url'   => $item['url']
                    ));
        if (isset($item['target']) ) {
            $T->set_var(array(
                        'target' => ($item['target'] == '' ? '' : ' target="'.$item['target'].'" ')
                    ));
        } else {
            $T->set_var('target','');
        }
        if ( isset($item['children']) && $item['children'] != NULL && is_array($item['children']) ) {
            $childrenHTML = displayMenuChildren($menuType,$item['children'],$template_file);
            $T->set_var('haschildren',true);
            $T->set_var('children',$childrenHTML);
        }
        if ( $item == $lastElement ) {
            $T->set_var('last',true);
        } else {
            $T->unset_var('last');
        }
        $T->parse('element', 'Elements',true);
        $T->unset_var('haschildren');
        $T->unset_var('children');
    }
    $T->set_var('wrapper',true);

    $T->parse('output','page');
    $retval = $T->finish($T->get_var('output'));

    if ( $skipCache == false )
        $c->set($key,$retval,'menu');

    return $retval;
}

/*
 * handle the children elements when building the menu HTML
 */
function displayMenuChildren( $type, $elements, $template_file ) {
    global $_CONF;

    $retval = '';

    $C = new Template( $_CONF['path_layout'].'/menu/');

    $C->set_file (array(
        'page'      => $template_file,
    ));

    $C->set_block('page', 'Elements', 'element');
    $lastElement = end($elements);
    foreach ($elements AS $child) {
        $C->unset_var('haschildren');

        $C->set_var(array(
                        'label' => $child['label'],
                        'url'   => $child['url']
                    ));
        if ( isset($child['target']) ) {
            $C->set_var(array(
                       'target' => ($child['target'] == '' ? '' : ' target="'.$child['target'].'" ')
                       ));
        } else {
            $C->set_var('target','');
        }
        if ( isset($child['children']) && $child['children'] != NULL && is_array($child['children']) ) {
            $C->set_var('hasparent',true);
            $childHTML = displayMenuChildren($type, $child['children'],$template_file);
            $C->set_var('haschildren',true);
            $C->set_var('children',$childHTML);
        }
        if ( $child == $lastElement ) {
            $C->set_var('last',true);
        } else {
            $C->unset_var('last');
        }
        $C->parse('element', 'Elements',true);
        $C->unset_var('haschildren');
        $C->unset_var('children');
        $C->unset_var('hasparent');
    }
    $C->parse('output','page');
    $retval = $C->finish($C->get_var('output'));

    return $retval;
}

function getUserMenu()
{
    global $_SP_CONF,$_USER, $_TABLES, $LANG01, $LANG_MB01, $LANG_LOGO,
           $LANG_AM, $LANG29, $_CONF, $_DB_dbms,$_GROUPS;

    $item_array = array();
    if ( !COM_isAnonUser() ) {
        $plugin_options = PLG_getAdminOptions();
        $num_plugins = count($plugin_options);
        if (SEC_isModerator() OR
                SEC_hasRights('story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit', 'OR') OR
                ($num_plugins > 0))
        {
            $url = $_CONF['site_admin_url'] . '/index.php';
            $label =  $LANG29[34];
            $item_array[] = array('label' => $label, 'url' => $url);
        }
        // what's our current URL?
        $elementUrl = COM_getCurrentURL();
        $plugin_options = PLG_getUserOptions();
        $nrows = count( $plugin_options );
        for( $i = 0; $i < $nrows; $i++ ) {
            $plg = current( $plugin_options );
            $label = $plg->adminlabel;
            if ( !empty( $plg->numsubmissions )) {
                $label .= ' (' . $plg->numsubmissions . ')';
            }
            $url = $plg->adminurl;
            $item_array[] = array('label' => $label, 'url' => $url);
            next( $plugin_options );
        }
        $url = $_CONF['site_url'] . '/usersettings.php?mode=edit';
        $label = $LANG01[48];
        $item_array[] = array('label' => $label, 'url' => $url);
        $url = $_CONF['site_url'] . '/users.php?mode=logout';
        $label = $LANG01[19];
        $item_array[] = array('label' => $label, 'url' => $url);
    } else {
        $url = $_CONF['site_url'] . '/users.php?mode=login';
        $label = $LANG01[58];
        $item_array[] = array('label' => $label, 'url' => $url);
    }
    return $item_array;
}

function getAdminMenu()
{
    global $_SP_CONF,$_USER, $_TABLES, $LANG01, $LANG_MB01, $LANG_LOGO,
           $LANG_AM, $LANG_SOCIAL, $LANG29, $_CONF,$_DB_dbms,$_GROUPS, $config;

    $item_array = array();

    if ( !COM_isAnonUser() ) {
        $plugin_options = PLG_getAdminOptions();
        $num_plugins = count( $plugin_options );

        if ( SEC_isModerator() OR SEC_hasRights( 'story.edit,block.edit,topic.edit,user.edit,plugin.edit,user.mail,syndication.edit,social.admin', 'OR' ) OR ( $num_plugins > 0 ) ) {
            // what's our current URL?
            $elementUrl = COM_getCurrentURL();

            $topicsql = '';
            if ( SEC_isModerator() || SEC_hasRights( 'story.edit' ) ) {
                $tresult = DB_query( "SELECT tid FROM {$_TABLES['topics']}"
                                     . COM_getPermSQL() );
                $trows = DB_numRows( $tresult );
                if ( $trows > 0 ) {
                    $tids = array();
                    for( $i = 0; $i < $trows; $i++ ) {
                        $T = DB_fetchArray( $tresult );
                        $tids[] = $T['tid'];
                    }
                    if ( sizeof( $tids ) > 0 ) {
                        $topicsql = " (tid IN ('" . implode( "','", $tids ) . "'))";
                    }
                }
            }
            $modnum = 0;
            if ( SEC_hasRights( 'story.edit,story.moderate', 'OR' ) || (( $_CONF['usersubmission'] == 1 ) && SEC_hasRights( 'user.edit,user.delete' ))) {
                if ( SEC_hasRights( 'story.moderate' )) {
                    if ( empty( $topicsql )) {
                        $modnum += DB_count( $_TABLES['storysubmission'] );
                    } else {
                        $sresult = DB_query( "SELECT COUNT(*) AS count FROM {$_TABLES['storysubmission']} WHERE" . $topicsql );
                        $S = DB_fetchArray( $sresult );
                        $modnum += $S['count'];
                    }
                }
                if (( $_CONF['listdraftstories'] == 1 ) && SEC_hasRights( 'story.edit' )) {
                    $sql = "SELECT COUNT(*) AS count FROM {$_TABLES['stories']} WHERE (draft_flag = 1)";
                    if ( !empty( $topicsql )) {
                        $sql .= ' AND' . $topicsql;
                    }
                    $result = DB_query( $sql . COM_getPermSQL( 'AND', 0, 3 ));
                    $A = DB_fetchArray( $result );
                    $modnum += $A['count'];
                }

                if ( $_CONF['usersubmission'] == 1 ) {
                    if ( SEC_hasRights( 'user.edit' ) && SEC_hasRights( 'user.delete' )) {
                        $modnum += DB_count( $_TABLES['users'], 'status', '2' );
                    }
                }
            }
            // now handle submissions for plugins
            $modnum += PLG_getSubmissionCount();

            if ( SEC_hasRights( 'story.edit' )) {
                $url = $_CONF['site_admin_url'] . '/story.php';
                $label = $LANG01[11];
                if ( empty( $topicsql )) {
                    $numstories = DB_count( $_TABLES['stories'] );
                } else {
                    $nresult = DB_query( "SELECT COUNT(*) AS count from {$_TABLES['stories']} WHERE" . $topicsql . COM_getPermSql( 'AND' ));
                    $N = DB_fetchArray( $nresult );
                    $numstories = $N['count'];
                }

                $label .= ' (' . COM_numberFormat($numstories) . ')';
                $item_array[] = array('label' => $label, 'url' => $url);
            }
            if ( SEC_hasRights( 'block.edit' )) {
                $result = DB_query( "SELECT COUNT(*) AS count FROM {$_TABLES['blocks']}" . COM_getPermSql());
                list( $count ) = DB_fetchArray( $result );

                $url = $_CONF['site_admin_url'] . '/block.php';
                $label = $LANG01[12] . ' (' . COM_numberFormat($count) . ')';
                $item_array[] = array('label' => $label, 'url' => $url);
            }
            if ( SEC_hasRights('autotag.admin') ) {
                $url = $_CONF['site_admin_url'] . '/autotag.php';
                $label = $LANG_AM['title'];
                $item_array[] = array('label' => $label, 'url' => $url);
            }
            if ( SEC_inGroup( 'Root' )) {
                $url = $_CONF['site_admin_url'] . '/clearctl.php';
                $label =  $LANG01['ctl'];
                $item_array[] = array('label' => $label, 'url' => $url);
            }
            if ( SEC_inGroup( 'Root' )) {
                $url = $_CONF['site_admin_url'] . '/menu.php';
                $label =  $LANG_MB01['menu_builder'];
                $item_array[] = array('label' => $label, 'url' => $url);
            }
            if ( SEC_inGroup( 'Root' )) {
                $url = $_CONF['site_admin_url'] . '/logo.php';
                $label =  $LANG_LOGO['logo_admin'];
                $item_array[] = array('label' => $label, 'url' => $url);
            }
            if ( SEC_hasRights( 'topic.edit' )) {
                $result = DB_query( "SELECT COUNT(*) AS count FROM {$_TABLES['topics']}" . COM_getPermSql());
                list( $count ) = DB_fetchArray( $result );
                $url = $_CONF['site_admin_url'] . '/topic.php';
                $label = $LANG01[13] . ' (' . COM_numberFormat($count) . ')';
                $item_array[] = array('label' => $label, 'url' => $url);
            }

            if ( SEC_hasRights( 'user.edit' )) {
                $url = $_CONF['site_admin_url'] . '/user.php';
                $label = $LANG01[17] . ' (' . COM_numberFormat(DB_count($_TABLES['users']) -1) . ')';
                $item_array[] = array('label' => $label, 'url' => $url);
            }

            if ( SEC_hasRights( 'group.edit' )) {
                if (SEC_inGroup('Root')) {
                    $grpFilter = '';
                } else {
                    $elementUsersGroups = SEC_getUserGroups ();
                    $grpFilter = 'WHERE (grp_id IN (' . implode (',', $elementUsersGroups) . '))';
                }
                $result = DB_query( "SELECT COUNT(*) AS count FROM {$_TABLES['groups']} $grpFilter;" );
                $A = DB_fetchArray( $result );

                $url = $_CONF['site_admin_url'] . '/group.php';
                $label = $LANG01[96] . ' (' . COM_numberFormat($A['count']) . ')';
                $item_array[] = array('label' => $label, 'url' => $url);
            }
            if ( SEC_hasRights( 'social.admin' )) {
                $url = $_CONF['site_admin_url'] . '/social.php';
                $label =  $LANG_SOCIAL['label'];
                $item_array[] = array('label' => $label, 'url' => $url);
            }

            if ( SEC_inGroup('Root') ) {
                $url = $_CONF['site_admin_url'].'/envcheck.php';
                $label = $LANG01['env_check'];
                $item_array[] = array('label' => $label, 'url' => $url);
            }

            if ( SEC_hasRights( 'user.mail' )) {
                $url = $_CONF['site_admin_url'] . '/mail.php';
                $label = $LANG01[105] . ' (N/A)';
                $item_array[] = array('label' => $label, 'url' => $url);
            }

            if (( $_CONF['backend'] == 1 ) && SEC_hasRights( 'syndication.edit' )) {
                $url = $_CONF['site_admin_url'] . '/syndication.php';
                $label = $LANG01[38] . ' (' . COM_numberFormat(DB_count($_TABLES['syndication'])) . ')';
                $item_array[] = array('label' => $label, 'url' => $url);
            }

            if (( $_CONF['trackback_enabled'] || $_CONF['pingback_enabled'] || $_CONF['ping_enabled'] ) && SEC_hasRights( 'story.ping' )) {
                $url = $_CONF['site_admin_url'] . '/trackback.php';
                $label = $LANG01[116] . ' (' . COM_numberFormat( DB_count( $_TABLES['pingservice'] )) . ')';
                $item_array[] = array('label' => $label, 'url' => $url);
            }
            if ( SEC_hasRights( 'plugin.edit' )) {
                $url = $_CONF['site_admin_url'] . '/plugins.php';
                $label = $LANG01[77] . ' (' . COM_numberFormat( DB_count( $_TABLES['plugins'] )) . ')';
                $item_array[] = array('label' => $label, 'url' => $url);
            }
            if (SEC_inGroup('Root')) {
                $url = $_CONF['site_admin_url'] . '/configuration.php';
                $label = $LANG01[129] . ' (' . COM_numberFormat(count($config->_get_groups())) . ')';
                $item_array[] = array('label' => $label, 'url' => $url);
            }

            // This will show the admin options for all installed plugins (if any)

            for( $i = 0; $i < $num_plugins; $i++ ) {
                $plg = current( $plugin_options );

                $url = $plg->adminurl;
                $label = $plg->adminlabel;

                if ( empty( $plg->numsubmissions )) {
                    $label .= '';
                } else {
                    $label .= ' (' . COM_numberFormat( $plg->numsubmissions ) . ')';
                }
                $item_array[] = array('label' => $label, 'url' => $url);
                next( $plugin_options );
            }

            if ( SEC_inGroup( 'Root' ) ) {
                $url = $_CONF['site_admin_url'] . '/database.php';
                $label = $LANG01[103] . '';
                $item_array[] = array('label' => $label, 'url' => $url);
            }
            if ( SEC_inGroup( 'Root' )) {
                $url = $_CONF['site_admin_url'] . '/logview.php';
                $label = $LANG01['logview'] . '';
                $item_array[] = array('label' => $label, 'url' => $url);
            }

            if ( $_CONF['link_documentation'] == 1 ) {
                $doclang = COM_getLanguageName();
                if ( @file_exists($_CONF['path_html'] . 'docs/' . $doclang . '/index.html') ) {
                    $docUrl = $_CONF['site_url'].'/docs/'.$doclang.'/index.html';
                } else {
                    $docUrl = $_CONF['site_url'].'/docs/english/index.html';
                }
                $url = $docUrl;
                $label = $LANG01[113] . '';
                $item_array[] = array('label' => $label, 'url' => $url);
            }

            if ( SEC_inGroup( 'Root' )) {
                $url = $_CONF['site_admin_url'] . '/vercheck.php';
                $label = $LANG01[107] . ' (' . GVERSION . PATCHLEVEL . ')';
                $item_array[] = array('label' => $label, 'url' => $url);
            }
            if (SEC_isModerator()) {
                $url = $_CONF['site_admin_url'] . '/moderation.php';
                $label = $LANG01[10] . ' (' . COM_numberFormat( $modnum ) . ')';
                $item_array[] = array('label' => $label, 'url' => $url);
            }

            if ( $_CONF['sort_admin']) {
                usort($item_array,'_mb_cmp');
            }
            $url = $_CONF['site_admin_url'] . '/index.php';
            $label = $LANG29[34];
            $cc_item = array('label' => $LANG29[34], 'url' => $url);
            $item_array = array_merge(array($cc_item),$item_array);
        }
    }
    return $item_array;
}


function getTopicMenu()
{
    global $_SP_CONF,$_USER, $_TABLES, $LANG01, $LANG_MB01, $LANG_LOGO,
           $LANG_AM, $LANG29, $_CONF, $_GROUPS;

    $item_array = array();
    $langsql = COM_getLangSQL( 'tid' );
    if ( empty( $langsql )) {
        $op = 'WHERE';
    } else {
        $op = 'AND';
    }

    $sql = "SELECT tid,topic,imageurl FROM {$_TABLES['topics']}" . $langsql;
    if ( !COM_isAnonUser() ) {
        $tids = DB_getItem( $_TABLES['userindex'], 'tids',
                            "uid=".(int) $_USER['uid']);
        if ( !empty( $tids )) {
            $sql .= " $op (tid NOT IN ('" . str_replace( ' ', "','", $tids )
                 . "'))" . COM_getPermSQL( 'AND' );
        } else {
            $sql .= COM_getPermSQL( $op );
        }
    } else {
        $sql .= COM_getPermSQL( $op );
    }
    if ( $_CONF['sortmethod'] == 'alpha' ) {
        $sql .= ' ORDER BY topic ASC';
    } else {
        $sql .= ' ORDER BY sortnum';
    }
    $result = DB_query( $sql );

    if ( $_CONF['showstorycount'] ) {
        $sql = "SELECT tid, COUNT(*) AS count FROM {$_TABLES['stories']} "
             . 'WHERE (draft_flag = 0) AND (date <= NOW()) '
             . COM_getPermSQL( 'AND' )
             . ' GROUP BY tid';
        $rcount = DB_query( $sql );
        while( $C = DB_fetchArray( $rcount )) {
            $storycount[$C['tid']] = $C['count'];
        }
    }

    if ( $_CONF['showsubmissioncount'] ) {
        $sql = "SELECT tid, COUNT(*) AS count FROM {$_TABLES['storysubmission']} "
             . ' GROUP BY tid';
        $rcount = DB_query( $sql );
        while( $C = DB_fetchArray( $rcount )) {
            $submissioncount[$C['tid']] = $C['count'];
        }
    }

    while( $A = DB_fetchArray( $result ) ) {
        $topicname = $A['topic'];
        $url =  $_CONF['site_url'] . '/index.php?topic=' . $A['tid'];
        $label = $topicname;

        $countstring = '';
        if ( $_CONF['showstorycount'] || $_CONF['showsubmissioncount'] ) {
            $countstring .= ' (';
            if ( $_CONF['showstorycount'] ) {
                if ( empty( $storycount[$A['tid']] )) {
                    $countstring .= 0;
                } else {
                    $countstring .= COM_numberFormat( $storycount[$A['tid']] );
                }
            }
            if ( $_CONF['showsubmissioncount'] ) {
                if ( $_CONF['showstorycount'] ) {
                    $countstring .= '/';
                }
                if ( empty( $submissioncount[$A['tid']] )) {
                    $countstring .= 0;
                } else {
                    $countstring .= COM_numberFormat( $submissioncount[$A['tid']] );
                }
            }

            $countstring .= ')';
        }
        $label .= $countstring;
        $item_array[] = array('label' => $label, 'url' => $url);
    }
    return $item_array;
}

function phpblock_getMenu( $arg1, $arg2 )
{
    return( displayMenu($arg2) );
}

function _mbPLG_getMenuItems()
{
    global $_PLUGINS;

    $menu = array();
    foreach ($_PLUGINS as $pi_name) {
        $function = 'plugin_getmenuitems_' . $pi_name;
        if (function_exists($function)) {
            $menuitems = $function();
            if (is_array ($menuitems)) {
                $url = current($menuitems);
                $label = key($menuitems);
                $mbmenu[$pi_name] = $url;
                $menu = array_merge ($menu, $mbmenu );
            }
        }
    }
    return $menu;
}

function _mb_cmp($a,$b)
{
    return strcasecmp($a['label'],$b['label']);
}

/**
* Enable and Disable menu
*/
function MB_changeActiveStatusElement ($element_arr)
{
    global $_CONF, $_TABLES;

    $menu_id = COM_applyFilter($_POST['menu'],true);

    // disable all elements
    $sql = "UPDATE {$_TABLES['menu_elements']} SET element_active = '0' WHERE menu_id=".(int) $menu_id;
    DB_query($sql);
    if (isset($element_arr)) {
        foreach ($element_arr as $element => $side) {
            $element = COM_applyFilter($element, true);
            // the enable those in the array
            $sql = "UPDATE {$_TABLES['menu_elements']} SET element_active = '1' WHERE id=".(int) $element;
            DB_query($sql);
        }
    }
    $c = glFusion\Cache::getInstance();
    $c->deleteItemsByTag('menu');
    $c->deleteItemsByTag('css');
    $c->deleteItemsByTag('js');

    return;
}
function MB_changeActiveStatusMenu ($menu_arr)
{
    global $_CONF, $_TABLES;
    // disable all menus
    $sql = "UPDATE {$_TABLES['menu']} SET menu_active = '0'";
    DB_query($sql);
    if (isset($menu_arr)) {
        foreach ($menu_arr AS $menu => $side) {
            $menu = COM_applyFilter($menu, true);
            // the enable those in the array
            $sql = "UPDATE {$_TABLES['menu']} SET menu_active = '1' WHERE id=".(int) $menu;
            DB_query($sql);
        }
    }
    $c = glFusion\Cache::getInstance()->deleteItemsByTag('menu');

    return;
}
?>