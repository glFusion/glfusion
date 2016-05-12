<?php
// +--------------------------------------------------------------------------+
// | Media Gallery Plugin for glFusion CMS                                    |
// +--------------------------------------------------------------------------+
// | Administer Media Gallery categories.                                     |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2005-2016 by the following authors:                        |
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
//

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';
require_once $_MG_CONF['path_admin'] . 'navigation.php';

$display = '';

// Only let admin users access this page
if (!SEC_hasRights('mediagallery.config')) {
    // Someone is trying to illegally access this page
    COM_errorLog("Someone has tried to illegally access the Media Gallery Configuration page.  User id: {$_USER['uid']}, Username: {$_USER['username']}",1);
    $display  = COM_siteHeader();
    $display .= COM_showMessageText($LANG_MG00['access_denied_msg'],$LANG_MG00['access_denied'],true);
    $display .= COM_siteFooter(true);
    echo $display;
    exit;
}


function MG_editCategory( $cat_id, $mode ) {
    global $album_jumpbox, $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $LANG_MG03, $LANG_ACCESS;

    $retval = '';

    $T = new Template($_MG_CONF['template_path'].'/admin');

    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var('site_admin_url', $_CONF['site_admin_url']);

    if ($cat_id==0 && $mode == 'create') {
        // set the album_id
        $sql = "SELECT MAX(cat_id) + 1 AS nextcat_id FROM " . $_TABLES['mg_category'];
        $result = DB_query( $sql );
        $row = DB_fetchArray( $result );
        $A['cat_id'] = $row['nextcat_id'];
        if ( $A['cat_id'] < 1 ) {
            $A['cat_id'] = 1;
        }
        if ( $A['cat_id'] == 0 ) {
            COM_errorLog("Media Gallery Error - Returned 0 as cat_id");
            $A['cat_id'] = 1;
        }
        $A['cat_name'] = '';
        $A['cat_description'] = '';
    } else {
        $A['cat_id'] = $cat_id;
        // pull info from DB
        $sql = "SELECT * FROM {$_TABLES['mg_category']} WHERE cat_id=" . (int) $cat_id;
        $result = DB_query($sql);
        $numRows = DB_numRows($result);
        if ( $numRows > 0 ) {
            $A = DB_fetchArray($result);
        }
    }

    $T->set_var('cat_id',$A['cat_id']);


    // If edit, pull up the existing album information...

    $T->set_file(array(
        'admin'         =>  'editcategory.thtml',
    ));

    $T->set_var(array(
        'action'                => 'category',
        'cat_id'                => $A['cat_id'],
        'cat_name'              => $A['cat_name'],
        'cat_description'       => $A['cat_description'],
        'lang_save'             => $LANG_MG01['save'],
        'lang_edit_category'    => ($mode=='create' ? $LANG_MG01['create_category'] : $LANG_MG01['edit_category']),
        's_form_action'         => $_MG_CONF['admin_url'] . 'category.php',
        'lang_cat_edit_help'    => $LANG_MG01['cat_edit_help'],
        'lang_title'            => $LANG_MG01['title'],
        'lang_description'      => $LANG_MG01['description'],
        'lang_cancel'           => $LANG_MG01['cancel'],
        'lang_delete'           => $LANG_MG01['delete'],
        'lang_delete_confirm'   => $LANG_MG01['delete_item_confirm'],
        'gltoken_name'          => CSRF_TOKEN,
        'gltoken'               => SEC_createToken(),
    ));

//    if ( $_MG_CONF['htmlallowed'] == 1 ) {
//        $T->set_var('allowed_html',COM_allowedHTML(SEC_getUserPermissions(),false,'mediagallery','category_title'));
//    }

    $T->parse('output', 'admin');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}

function MG_saveCategory( $cat_id ) {
    global $_USER, $_CONF, $_TABLES, $_MG_CONF, $LANG_MG00, $LANG_MG01, $_POST;
    $update = 0;

    $A['cat_id']        = COM_applyFilter($_POST['cat_id'],true);

//    if ($_MG_CONF['htmlallowed'] == 1 ) {
//        $A['cat_name']          = DB_escapeString(COM_checkHTML(COM_killJS($_POST['cat_name'])));
//        $A['cat_description']   = DB_escapeString(COM_checkHTML(COM_killJS($_POST['cat_desc'])));
//    } else {
        $A['cat_name']          = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords(COM_killJS($_POST['cat_name'])))));
        $A['cat_description']   = DB_escapeString(htmlspecialchars(strip_tags(COM_checkWords(COM_killJS($_POST['cat_desc'])))));
//    }

    if ($A['cat_name'] == "" ) {
        return(MG_errorHandler( $LANG_MG01['category_error'] ));
    }

    $sql = "SELECT MAX(cat_order) + 1 AS nextcat_order FROM " . $_TABLES['mg_category'];
    $result = DB_query( $sql );
    $row = DB_fetchArray( $result);
    if ($row == NULL || $result == NULL ) {
        $A['cat_order'] = 10;
    } else {
        $A['cat_order'] = $row['nextcat_order'];
        if ( $A['cat_order'] < 0 ) {
            $A['cat_order'] = 10;
        }
    }
    if ( $A['cat_order'] == NULL )
        $A['cat_order'] = 10;

    //
    //  -- Let's make sure we don't have any SQL overflows...
    //

    $A['cat_name'] = substr($A['cat_name'],0,254);

    if ( $A['cat_id'] == 0 ) {
        COM_errorLog("Media Gallery Internal Error - cat_id = 0 - Contact support@glfusion.org  ");
        return(MG_genericError($LANG_MG00['access_denied_msg']));
    }

    DB_save($_TABLES['mg_category'],"cat_id,cat_name,cat_description,cat_order","'{$A['cat_id']}','{$A['cat_name']}','{$A['cat_description']}',{$A['cat_order']}");

    echo COM_refresh($_MG_CONF['admin_url'] . 'category.php');
    exit;
}

function MG_batchDeleteCategory( ) {
    global $_MG_CONF, $_CONF, $_TABLES;

    $numItems = count($_POST['sel']);

    for ($i=0; $i < $numItems; $i++) {
        $sql = "DELETE FROM {$_TABLES['mg_category']} WHERE cat_id='" . COM_applyFilter($_POST['sel'][$i],true) . "'";
        $result = DB_query($sql);
        if ( DB_error() ) {
            COM_errorLog("Media Gallery: Error removing category");
        }
        // now remove it from all the media items...
        $sql = "UPDATE {$_TABLES['mg_media']} SET media_category=0 WHERE media_category=" . COM_applyFilter($_POST['sel'][$i],true);
        $result = DB_query($sql);
        if ( DB_error() ) {
            COM_errorLog("Media Gallery: Error removing category from media table");
        }
    }

    echo COM_refresh($_MG_CONF['admin_url'] . 'category.php');
    exit;
}


function MG_displayCategories( ) {
    global $_CONF, $_MG_CONF, $_TABLES, $_USER, $LANG_MG00, $LANG_MG01;

    $retval = '';

    $T = new Template($_MG_CONF['template_path'].'/admin');

    $T->set_file (array(
        'category'  =>  'category.thtml',
        'empty'     =>  'cat_noitems.thtml',
        'catitems'  =>  'catitems.thtml'
    ));
    $T->set_var('site_url', $_CONF['site_url']);
    $T->set_var(array(
        'lang_checkall'     => $LANG_MG01['check_all'],
        'lang_uncheckall'   => $LANG_MG01['uncheck_all'],
    ));

    $sql = "SELECT * FROM {$_TABLES['mg_category']} ORDER BY cat_id ASC";
    $result = DB_query($sql);
    $numRows = DB_numRows($result);
    $rowclass = 0;

    if ( $numRows == 0 ) {
        // we have no categories
        $T->set_var(array(
            'lang_no_cat'  =>  $LANG_MG01['no_cat']
        ));
        $T->parse('noitems','empty');
    } else {
        $totalCat = $numRows;
        $mediaObject = array();

        $T->set_block('catitems', 'catRow','cRow');

        for ($x = 0; $x < $numRows; $x++ ) {
            $row = DB_fetchArray( $result );
            $T->set_var(array(
                'row_class'     =>      ($rowclass % 2) ? '1' : '2',
                'cat_id'        =>      $row['cat_id'],
                'edit_cat_id'   =>      '<a href="' . $_MG_CONF['admin_url'] . 'category.php?mode=edit&amp;id=' . $row['cat_id'] . '">' . $row['cat_id'] . '</a>',
                'cat_name'      =>      $row['cat_name'],
                'cat_description' =>    $row['cat_description'],
                'cat_order'     =>      $row['cat_order'],
            ));
            $T->parse('cRow','catRow',true);
            $rowclass++;
        }
        $T->parse('catitems','catitems');
    }
    $T->set_var(array(
        's_form_action'     => $_MG_CONF['admin_url'] . '/category.php',
        'mode'              => 'category',
        'lang_category_manage_help' => $LANG_MG01['category_manage_help'],
        'lang_catid'        => $LANG_MG01['cat_id'],
        'lang_cat_name'     => $LANG_MG01['cat_name'],
        'lang_cat_description' => $LANG_MG01['cat_description'],
        'lang_order'        => $LANG_MG01['order'],
        'lang_save'         => $LANG_MG01['save'],
        'lang_cancel'       => $LANG_MG01['cancel'],
        'lang_delete'       => $LANG_MG01['delete'],
        'lang_create'       => $LANG_MG01['create'],
        'lang_select'       => $LANG_MG01['select'],
        'lang_checkall'     => $LANG_MG01['check_all'],
        'lang_uncheckall'   => $LANG_MG01['uncheck_all'],
        'lang_batch'        => $LANG_MG01['batch_process'],
        'lang_delete_confirm'   => $LANG_MG01['delete_item_confirm'],
    ));

    $T->parse('output','category');
    $retval .= $T->finish($T->get_var('output'));
    return $retval;
}


/**
* Main
*/

$mode = '';
if ( isset($_POST['save']) ) {
    $mode = 'save';
}
if ( isset($_POST['cancel']) ) {
    $mode = 'cancel';
}
if ( isset($_REQUEST['mode']) ) {
    $mode = COM_applyFilter($_REQUEST['mode']);
}

$display = '';
$retval = '';

$T = new Template($_MG_CONF['template_path'].'/admin');
$T->set_file (array ('admin' => 'administration.thtml'));
$T->set_var(array(
    'site_admin_url'    => $_CONF['site_admin_url'],
    'site_url'          => $_MG_CONF['site_url'],
    'mg_navigation'     => MG_navigation(),
    'lang_admin'        => $LANG_MG00['admin'],
    'version'           => $_MG_CONF['pi_version'],
));

if ($mode == 'save' && SEC_checkToken() ) {
    $cat_id = COM_applyFilter($_POST['cat_id'],true);
    $T->set_var(array(
        'admin_body'    => MG_saveCategory($cat_id),
    ));
} elseif ($mode == 'cancel' ) {
    echo COM_refresh ($_MG_CONF['admin_url'] . 'index.php');
    exit;
} elseif ($mode == $LANG_MG01['create'] && !empty ($LANG_MG01['create'])) {
    $T->set_var(array(
        'admin_body'    => MG_editCategory(0,'create'),
        'title'         => $LANG_MG01['create_category'],
    ));
} elseif ($mode == 'edit') {
    $cat_id = COM_applyFilter($_GET['id'],true);
    $T->set_var(array(
        'admin_body'    => MG_editCategory($cat_id,'edit'),
        'title'         => $LANG_MG01['edit_category'],
    ));
} elseif ($mode == $LANG_MG01['delete'] && !empty ($LANG_MG01['delete'])) {
    if ( isset($_POST['cat_id'] ) ) {
        $cat_id = $_POST['cat_id'];
        MG_batchDeleteCategory($cat_id);
    } else {
        MG_batchDeleteCategory(0);
    }
} else {
    $T->set_var(array(
        'admin_body'    => MG_displayCategories(),
        'title'         => $LANG_MG01['category_manage_help'],
        'lang_help'     => '<img src="' . MG_getImageFile('button_help.png') . '" style="border:none;" alt="?"/>',
        'help_url'      => $_MG_CONF['site_url'] . '/docs/usage.html#Category_Maintenance',

    ));
}

$T->parse('output', 'admin');
$display = COM_siteHeader();
$display .= $T->finish($T->get_var('output'));
$display .= COM_siteFooter();
echo $display;
exit;
?>