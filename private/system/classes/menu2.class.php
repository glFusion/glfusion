<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | menu2.class.php                                                          |
// |                                                                          |
// | Menu elements class / functions                                          |
// +--------------------------------------------------------------------------+
// | Copyright (C)  2008-2014 by the following authors:                       |
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

class menu2 {
    var $id;
    var $name;
    var $type;
    var $active;
    var $group_id;
    var $menu_alignment;
    var $menu_elements = array();               // menu elements array

    public function __construct( $menu_id = 0 )
    {
        if ( $menu_id > 0 ) {
            $this->getMenu($menu_id);
        } else {
            $this->id = 0;
        }
    }


    function &getInstance( $menu_id = 0 )
    {
        static $instance = array();

        if (!isset($instance[$menu_id]) ) {
            $instance[$menu_id] = new menu2($menu_id);
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
        $elementResult      = DB_query( $sql, 1);
        $element            = new menuElement2();
        $element->id        = 0;
        $element->menu_id   = $this->id;
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
        $this->menu_elements[0] = $element;

        while ($A = DB_fetchArray($elementResult) ) {
            $element  = new menuElement2();
            $element->constructor($A,$mbadmin,$root,$_GROUPS,1);
            if ( $element->access > 0 ) {
                $this->menu_elements[$element->id] = $element;
            }
        }

        foreach( $this->menu_elements as $id => $element) {
            if ($id != 0 && isset($this->menu_elements[$element->pid]->id) ) {
                $this->menu_elements[$element->pid]->setChild($id);
            }
        }
    }


// we should be able to remove the $_DB_dbms and the check later
// on since we only support mysql and no other db at this time.
    function _parseMenu( $element )
    {
        global $_SP_CONF,$_USER, $_TABLES, $LANG01, $LANG_MB01, $LANG_LOGO,
               $LANG_AM, $LANG29, $_CONF,
               $_DB_dbms,$_GROUPS, $config;

        $returnArray    = array();
        $childArray     = array();

        if ( $element->active != 1 && $element->id != 0 ) {
            return NULL;
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

        if ( $element->group_id != 998 && $element->id != 0 && !SEC_inGroup($element->group_id) ) {
            return NULL;
        }

        if ( $element->group_id == 1 && !isset($_GROUPS['Root']) ) {
            return NULL;
        }

        // need to build the URL
        switch ( $element->type ) {
            case ET_SUB_MENU :
                $element->replace_macros();
                break;
            case ET_FUSION_ACTION :
                switch ($element->subtype) {
                    case 0: // home
                        $element->url = $_CONF['site_url'] . '/';
                        break;
                    case 1: // contribute
                        if( $anon && ( $_CONF['loginrequired'] || $_CONF['submitloginrequired'] )) {
                            return $retval;
                        }
                        if( empty( $topic )) {
                            $element->url = $_CONF['site_url'] . '/submit.php?type=story';
                        } else {
                            $element->url = $_CONF['site_url']
                                 . '/submit.php?type=story&amp;topic=' . $topic;
                        }
                        $label = $LANG01[71];
                        break;
                    case 2: // directory
                        if( $anon && ( $_CONF['loginrequired'] ||
                                $_CONF['directoryloginrequired'] )) {
                            return $retval;
                        }
                        $element->url = $_CONF['site_url'] . '/directory.php';
                        if( !empty( $topic )) {
                            $element->url = COM_buildUrl( $element->url . '?topic='
                                                 . urlencode( $topic ));
                        }
                        break;
                    case 3: // prefs
                        if( $anon && ( $_CONF['loginrequired'] ||
                                $_CONF['profileloginrequired'] )) {
                            return $retval;
                        }
                        $element->url = $_CONF['site_url'] . '/usersettings.php?mode=edit';
                        break;
                    case 4: // search
                        if( $anon && ( $_CONF['loginrequired'] ||
                                $_CONF['searchloginrequired'] )) {
                            return $retval;
                        }
                        $element->url = $_CONF['site_url'] . '/search.php';
                        break;
                    case 5: // stats
                        if ( !SEC_hasRights('stats.view') ) {
                            return $retval;
                        }
                        $element->url = $_CONF['site_url'] . '/stats.php';
                        break;
                    default : // unknown?
                        $element->url = $_CONF['site_url'] . '/';
                        break;
                }
                break;
            case ET_FUSION_MENU :
                switch ($element->subtype) {
                    case USER_MENU :
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
                                if( !empty( $plg->numsubmissions )) {
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

                        break;
                    case ADMIN_MENU :

                        if( !COM_isAnonUser()) {
                            $item_array = array();

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
                                $elementUrl = COM_getCurrentURL();

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
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }
                                if( SEC_hasRights( 'block.edit' )) {
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
                                if( SEC_inGroup( 'Root' )) {
                                    $url = $_CONF['site_admin_url'] . '/clearctl.php';
                                    $label =  $LANG01['ctl'];
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }
                                if( SEC_inGroup( 'Root' )) {
                                    $url = $_CONF['site_admin_url'] . '/menu.php';
                                    $label =  $LANG_MB01['menu_builder'];
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }
                                if( SEC_inGroup( 'Root' )) {
                                    $url = $_CONF['site_admin_url'] . '/logo.php';
                                    $label =  $LANG_LOGO['logo_admin'];
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }
                                if( SEC_hasRights( 'topic.edit' )) {
                                    $result = DB_query( "SELECT COUNT(*) AS count FROM {$_TABLES['topics']}" . COM_getPermSql());
                                    list( $count ) = DB_fetchArray( $result );
                                    $url = $_CONF['site_admin_url'] . '/topic.php';
                                    $label = $LANG01[13] . ' (' . COM_numberFormat($count) . ')';
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }

                                if( SEC_hasRights( 'user.edit' )) {
                                    $url = $_CONF['site_admin_url'] . '/user.php';
                                    $label = $LANG01[17] . ' (' . COM_numberFormat(DB_count($_TABLES['users']) -1) . ')';
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }

                                if( SEC_hasRights( 'group.edit' )) {
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

                                if ( SEC_inGroup('Root') ) {
                                    $url = $_CONF['site_admin_url'].'/envcheck.php';
                                    $label = $LANG01['env_check'];
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }

                                if( SEC_hasRights( 'user.mail' )) {
                                    $url = $_CONF['site_admin_url'] . '/mail.php';
                                    $label = $LANG01[105] . ' (N/A)';
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }

                                if(( $_CONF['backend'] == 1 ) && SEC_hasRights( 'syndication.edit' )) {
                                    $url = $_CONF['site_admin_url'] . '/syndication.php';
                                    $label = $LANG01[38] . ' (' . COM_numberFormat(DB_count($_TABLES['syndication'])) . ')';
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }

                                if(( $_CONF['trackback_enabled'] || $_CONF['pingback_enabled'] || $_CONF['ping_enabled'] ) && SEC_hasRights( 'story.ping' )) {
                                    $url = $_CONF['site_admin_url'] . '/trackback.php';
                                    $label = $LANG01[116] . ' (' . COM_numberFormat( DB_count( $_TABLES['pingservice'] )) . ')';
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }
                                if( SEC_hasRights( 'plugin.edit' )) {
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

                                    if( empty( $plg->numsubmissions )) {
                                        $label .= '';
                                    } else {
                                        $label .= ' (' . COM_numberFormat( $plg->numsubmissions ) . ')';
                                    }
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                    next( $plugin_options );
                                }

                                if(( $_CONF['allow_mysqldump'] == 1 ) AND ( $_DB_dbms == 'mysql' || $_DB_dbms == 'mysqli' ) AND SEC_inGroup( 'Root' )) {
                                    $url = $_CONF['site_admin_url'] . '/database.php';
                                    $label = $LANG01[103] . '';
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }
                                if( SEC_inGroup( 'Root' )) {
                                    $url = $_CONF['site_admin_url'] . '/logview.php';
                                    $label = $LANG01['logview'] . '';
                                    $item_array[] = array('label' => $label, 'url' => $url);
                                }

                                if( $_CONF['link_documentation'] == 1 ) {
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

                                if( SEC_inGroup( 'Root' )) {
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
                        break;

                    case TOPIC_MENU :
                        $item_array = array();
                        $langsql = COM_getLangSQL( 'tid' );
                        if( empty( $langsql )) {
                            $op = 'WHERE';
                        } else {
                            $op = 'AND';
                        }

                        $sql = "SELECT tid,topic,imageurl FROM {$_TABLES['topics']}" . $langsql;
                        if ( !COM_isAnonUser() ) {
                            $tids = DB_getItem( $_TABLES['userindex'], 'tids',
                                                "uid=".(int) $_USER['uid']);
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
                            $topicname = $A['topic'];
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
                            $item_array[] = array('label' => $label, 'url' => $url);
                        }
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
                        if( count( $plugin_menu ) == 0 ) {
                            $element->access = 0;
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
                if ( isset($plugin_menus[$element->subtype]) ) {
                    $element->url = $plugin_menus[$element->subtype];
                } else {
                    $element->access = 0;
                    $allowed = 0;
                }
                break;
            case ET_STATICPAGE :
                $element->url = COM_buildURL ($_CONF['site_url'] . '/page.php?page=' . $element->subtype);
                break;
            case ET_URL :
                $element->replace_macros();
                break;
            case ET_PHP :
/* ---
                $functionName = $element->subtype;
                if (function_exists($functionName)) {
                    $menu = "<li>" . '<a class="' . $parentaclass . '" href="#">' . strip_tags($element->label) . '</a>' . LB;
                    $menu .= $functionName();
                    $menu .= '</li>';
                }
--- */
                break;
            case ET_TOPIC :
                $element->url = $_CONF['site_url'] . '/index.php?topic=' . $element->subtype;
                break;
            default :
                break;
        }
        if ( $element->id != 0 && $element->group_id == 998 && (SEC_inGroup('Root') ) ) {
            return NULL;
        }
        if ( $allowed == 0 ) {
            return NULL;
        }

        if ( $element->type == ET_FUSION_MENU ||
             $element->type == ET_PHP ) {
            // we know that a built in fusion menu cannot have children
            $childArray = $item_array;
        } else {
            if ( !empty($element->children)) {
                $howmany = $element->getChildcount();

                if ( $howmany > 0 ) {
                    $children = $element->getChildren();
                    foreach($children as $child) {
                        $childArray[] = $this->_parseMenu($child);
                    }
                }
            } else {
                $childArray = NULL;
            }
        }
        $returnArray = array('label' => $element->label,
                             'url'   => $element->url,
                             'children' => (is_array($childArray) ? $childArray : NULL)  );

        return $returnArray;
    }
}

class menuElement2 {
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

    function menuElement2 () {
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

    function reorderMenu( ) {
        global $_TABLES;

        $pid = (int) $this->id;
        $menu_id = (int) $this->menu_id;

        $orderCount = 10;

        $sql = "SELECT id,`element_order` FROM {$_TABLES['menu_elements']} WHERE menu_id=".$menu_id." AND pid=" . $pid . " ORDER BY `element_order` ASC";
        $result = DB_query($sql);
        while ($M = DB_fetchArray($result)) {
            $M['element_order'] = $orderCount;
            $orderCount += 10;
            DB_query("UPDATE {$_TABLES['menu_elements']} SET `element_order`=" . $M['element_order'] . " WHERE menu_id=".$menu_id." AND id=" . (int) $M['id'] );
        }
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

        if ( $this->group_id == 998 ) {
            if( COM_isAnonUser() ) {
                $this->access = 3;
            } else {
                $this->access = 0;
            }
        } else {
            if ($meadmin || $root) {
                $this->access = 3;
            } else if ( in_array( $this->group_id, $groups ) ) {
                $this->access = 3;
            } else {
                $this->access = 0;
            }
        }
    }

    function getChildren()
    {
        return $this->children;
    }

    function editTree( $depth, $count ) {
        global $_CONF, $level;
        global $LANG_MB01, $LANG_MB_TYPES,$LANG_MB_GLTYPES,$LANG_MB_GLFUNCTION;

        $data_arr = array();

        $toolTipStyle = COM_getToolTipStyle();

        $menu = menu::getInstance($this->menu_id);

        $plugin_menus = _mbPLG_getMenuItems();

        $px = ($level - 1 ) * 15;

        if ( $this->label != 'Top Level Menu' ) {
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
                    $elementDetails .= '<b>' . $LANG_MB_TYPES[$this->type] . ':</b> UNDEFINED <br/>';
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
        }

        if ( !empty($this->children)) {
            $children = $this->getChildren();
            foreach($children as $child) {
                $level++;
                if ( isset($menu->menu_elements[$child]) ) {
                    $carray = $menu->menu_elements[$child]->editTree($depth,$count);
                    $data_arr = array_merge($data_arr,$carray);
                }
                $level--;
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
        global $mbMenu2;

        $pid = $this->pid;
        $children = $mbMenu2[$this->menu_id]['elements'][$pid]->getChildren();
        $arrayIndex = count($children)-1;
        if ( $this->id == $children[$arrayIndex] ) {
            return true;
        }
        return false;
    }
}
?>