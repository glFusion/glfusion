<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | lib-admin.php                                                            |
// |                                                                          |
// | Admin-related functions needed in more than one place.                   |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2010 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// | Mark Howard            mark AT usable-web DOT com                        |
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2000-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Tony Bibbs         - tony AT tonybibbs DOT com                  |
// |          Mark Limburg       - mlimburg AT users DOT sourceforge DOT net  |
// |          Jason Whittenburg  - jwhitten AT securitygeeks DOT com          |
// |          Dirk Haun          - dirk AT haun-online DOT de                 |
// |          Oliver Spiesshofer - oliver AT spiesshofer DOT com              |
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

/**
* Stub function for generation of checkbox
*
*/
function ADMIN_chkDefault($A = array())
{
    return true;
}

/**
* Create a list of admin icons, for use in admin/list functions
*
* These images should be available in public_html/layout/theme/images/admin
*
*/
function ADMIN_getIcons()
{
    global $_CONF, $_IMAGE_TYPE, $LANG_ADMIN;

    $icons_type_arr = array('add', 'edit', 'copy', 'delete', 'list', 'mail', 'group', 'user', 'check', 'greycheck', 'cross', 'disk', 'accept', 'addchild', 'blank');
    $icon_arr = array();
    foreach ($icons_type_arr as $icon_type) {
        $icon_url = "{$_CONF['layout_url']}/images/admin/$icon_type.$_IMAGE_TYPE";
        $alt = (isset($LANG_ADMIN[$icon_type])) ? $LANG_ADMIN[$icon_type] : '';
        $attr['title'] = $alt;
        $icon_arr[$icon_type] = COM_createImage($icon_url, $alt, $attr);
    }
    return $icon_arr;
}


/**
* Common function used in Admin scripts to display a list of items
*
* @param    string  $fieldfunction  Name of a function used to display the list item row details
* @param    array   $header_arr     array of header fields with sortables and table fields
* @param    array   $text_arr       array with different text strings
* @param    array   $data_arr       array with sql query data - array of list records
* @param    array   $options_arr    array of options - used for check-all feature
* @param    array   $form_arr       optional extra forms at top or bottom
* @return   string                  HTML output of function
*
*/
function ADMIN_simpleList($fieldfunction, $header_arr, $text_arr,
                           $data_arr, $options_arr = '', $form_arr='')
{
    global $_CONF, $_TABLES, $LANG01, $LANG_ADMIN, $LANG_ACCESS, $MESSAGE,
           $_IMAGE_TYPE;

    $retval = '';

    // process text_arr for title, help url and form url
    $title = (is_array($text_arr) AND !empty($text_arr['title'])) ? $text_arr['title'] : '';
    $help_url = (is_array($text_arr) AND !empty($text_arr['help_url'])) ? $text_arr['help_url'] : '';
    $form_url = (is_array($text_arr) AND !empty($text_arr['form_url'])) ? $text_arr['form_url'] : '';
    $no_data = (is_array($text_arr) AND !empty($text_arr['no_data'])) ? $text_arr['no_data'] : '';

    // process options_arr for chkdelete/chkselect options if any
    $chkselect = (is_array($options_arr) AND
                   ((isset($options_arr['chkselect']) AND $options_arr['chkselect']) OR
                   (isset($options_arr['chkdelete']) AND $options_arr['chkdelete']))) ? true : false;
    $chkall = (is_array($options_arr) AND isset($options_arr['chkall'])) ? $options_arr['chkall'] : true;
    $chkname = (is_array($options_arr) AND isset($options_arr['chkname'])) ? $options_arr['chkname'] : 'delitem';
    $chkfield = (is_array($options_arr) AND isset($options_arr['chkfield'])) ? $options_arr['chkfield'] : '';
    $chkactions = (is_array($options_arr) AND isset($options_arr['chkactions'])) ? $options_arr['chkactions'] : '';
    $chkfunction = (is_array($options_arr) AND isset($options_arr['chkfunction'])) ? $options_arr['chkfunction'] : 'ADMIN_chkDefault';
    $chkminimum = (is_array($options_arr) AND isset($options_arr['chkminimum'])) ? $options_arr['chkminimum'] : 1;

    $admin_templates = new Template($_CONF['path_layout'] . 'admin/lists');
    $admin_templates->set_file (
        array (
            'list' => 'list.thtml',
            'header' => 'header.thtml',
            'row' => 'listitem.thtml',
            'field' => 'field.thtml',
            'arow'  => 'actionrow.thtml'
        )
    );
    $admin_templates->set_var('form_url', $form_url);
    $admin_templates->set_var('lang_edit', $LANG_ADMIN['edit']);
    $admin_templates->set_var('lang_delconfirm', $LANG01[125]);
    if (isset($form_arr['top'])) {
        $admin_templates->set_var('formfields_top', $form_arr['top']);
    }
    if (isset($form_arr['bottom'])) {
        $admin_templates->set_var('formfields_bottom', $form_arr['bottom']);
    }

    // retrieve the array of admin icons
    $icon_arr = ADMIN_getIcons();

    // number of columns in each row
    $ncols = count( $header_arr );

    // number of rows/records to display
    $nrows = count($data_arr);

    if ($nrows > $chkminimum AND $chkselect) {
        if ($chkall) {
            $admin_templates->set_var('header_text', '<input type="checkbox" name="chk_selectall" title="'.$LANG01[126].'" onclick="caItems(this.form, \'' . $chkname . '\');"' . XHTML . '>');
        } else {
            $admin_templates->set_var('header_text', '<input type="checkbox" name="disabled" value="x" style="visibility:hidden" DISABLED'.XHTML.'>');
        }
        $admin_templates->set_var('class', 'admin-list-field');
        $admin_templates->set_var('header_column_style', 'style="text-align:center;width:25px;"'); // always center checkbox
        $admin_templates->parse('header_row', 'header', true);
    }

    # HEADER FIELDS array(text, field, sort)
    for ($i=0; $i < $ncols; $i++) {
        $admin_templates->set_var('header_text', $header_arr[$i]['text']);
        if (!empty($header_arr[$i]['header_class'])) {
            $admin_templates->set_var('class', $header_arr[$i]['header_class']);
        } else {
            $admin_templates->set_var('class', 'admin-list-headerfield');
        }
        // header column formatting
        $header_column_style = '';
        if (!empty($header_arr[$i]['align'])) {
            if ($header_arr[$i]['align'] == 'center') {
                $header_column_style = 'text-align:center;';
            } elseif ($header_arr[$i]['align'] == 'right') {
                $header_column_style = 'text-align:right;';
            }
        }
        $header_column_style .= (isset($header_arr[$i]['nowrap'])) ? ' white-space:nowrap;' : '';
        $header_column_style .= (isset($header_arr[$i]['width'])) ? ' width:' . $header_arr[$i]['width'] . ';' : '';
        if(!empty($header_column_style)) {
            $admin_templates->set_var('header_column_style', 'style="' . $header_column_style . '"');
        } else {
            $admin_templates->clear_var('header_column_style');
        }
        $admin_templates->parse('header_row', 'header', true);
    }

    if ($nrows == 0) {
        $message = (isset($no_data)) ? $no_data : $LANG_ADMIN['no_results'];
        $admin_templates->set_var('message', $message);
    } else if ($data_arr === false) {
        $admin_templates->set_var('message', $LANG_ADMIN['data_error']);
    } else {
        $admin_templates->set_var('show_message', 'display:none;');
        for ($i = 0; $i < $nrows; $i++) {
            if ($nrows > $chkminimum AND $chkselect) {
                $admin_templates->set_var('class', 'admin-list-field');
                $admin_templates->set_var('column_style', 'style="text-align:center;"'); // always center checkbox
                if ($chkfunction($data_arr[$i])) {
                    $admin_templates->set_var('itemtext', '<input type="checkbox" name="' . $chkname . '[]" value="' . $data_arr[$i][$chkfield] . '" title="' . $LANG_ADMIN['select'] . '"' . XHTML . '>');
                } else {
                    $admin_templates->set_var('itemtext', '<input type="checkbox" name="disabled" value="x" style="visibility:hidden" DISABLED'.XHTML.'>');
                }
                $admin_templates->parse('item_field', 'field', true);
            }
            for ($j = 0; $j < $ncols; $j++) {
                $fieldname = $header_arr[$j]['field'];
                $fieldvalue = '';
                if (!empty($data_arr[$i][$fieldname])) {
                    $fieldvalue = $data_arr[$i][$fieldname];
                }
                if (!empty($fieldfunction)) {
                    $fieldvalue = $fieldfunction($fieldname, $fieldvalue, $data_arr[$i], $icon_arr);
                } else {
                    $fieldvalue = $fieldvalue;
                }
                if (!empty($header_arr[$j]['field_class'])) {
                    $admin_templates->set_var('class', $header_arr[$j]['field_class']);
                } else {
                    $admin_templates->set_var('class', 'admin-list-field');
                }
                $column_style = '';
                if (!empty($header_arr[$j]['align'])) {
                    if ($header_arr[$j]['align'] == 'center') {
                        $column_style = 'text-align:center;';
                    } elseif ($header_arr[$j]['align'] == 'right') {
                        $column_style = 'text-align:right;';
                    }
                }
                $column_style .= (isset($header_arr[$j]['nowrap'])) ? ' white-space:nowrap;' : '';
                if(!empty($column_style)) {
                    $admin_templates->set_var('column_style', 'style="' . $column_style . '"');
                } else {
                    $admin_templates->clear_var('column_style');
                }
                if ($fieldvalue !== false) {
                    $admin_templates->set_var('itemtext', $fieldvalue);
                    $admin_templates->parse('item_field', 'field', true);
                }
            }
            $admin_templates->set_var('cssid', ($i%2)+1);
            $admin_templates->parse('item_row', 'row', true);

            $admin_templates->clear_var('item_field');
        }
    $footer_cols = ($chkselect) ? $ncols + 1 : $ncols;
    $admin_templates->set_var('footer_row', '<tr><td colspan="' . $footer_cols . '"><div style="margin:2px 0 2px 0;border-top:1px solid #cccccc"></div></td></tr>');
    }

    // if we displayed data, and chkselect option is available, display the
    // actions row for all selected items. provide a delete action as a minimum
    if ($nrows > $chkminimum AND $chkselect) {
        $actions = '<td style="text-align:center;">'
            . '<img src="' . $_CONF['layout_url'] . '/images/admin/action.' . $_IMAGE_TYPE . '"></td>';
        $delete_action = '<input name="delbutton" type="image" src="'
            . $_CONF['layout_url'] . '/images/admin/delete.' . $_IMAGE_TYPE
            . '" style="vertical-align:text-bottom;" title="' . $LANG01[124]
            . '" onclick="return confirm(\'' . $LANG01[125] . '\');"'
            . XHTML . '>&nbsp;' . $LANG_ADMIN['delete'];
        $actions .= '<td colspan="' . $ncols . '">' . $LANG_ADMIN['action'] . '&nbsp;&nbsp;&nbsp;' . $delete_action;
        $actions .= (!empty($chkactions)) ? $chkactions . '</td>' : '</td>';
        $admin_templates->set_var('actions', $actions);
        $admin_templates->parse('action_row', 'arow', true);
    }

    $admin_templates->parse('output', 'list');

    if (!empty($title)) {
        $retval .= COM_startBlock($title, $help_url,
                            COM_getBlockTemplate('_admin_block', 'header'));
    }
    $retval .= $admin_templates->finish($admin_templates->get_var('output'));
    if (!empty($title)) {
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    }

    return $retval;
}

/**
* Creates a list of data with a search, filter, clickable headers etc.
*
* @param    string  $component      name of the list
* @param    string  $fieldfunction  name of the function that handles special entries
* @param    array   $header_arr     array of header fields with sortables and table fields
* @param    array   $text_arr       array with different text strings
* @param    array   $query_arr      array with sql-options
* @param    array   $defsort_arr    default sorting values
* @param    string  $filter         additional drop-down filters
* @param    string  $extra          additional values passed to fieldfunction
* @param    array   $options_arr    array of options - used for check-all feature
* @param    array   $form_arr       optional extra forms at top or bottom
* @return   string                  HTML output of function
*
*/
function ADMIN_list($component, $fieldfunction, $header_arr, $text_arr,
            $query_arr, $defsort_arr, $filter = '', $extra = '',
            $options_arr = '', $form_arr='')
{
    global $_CONF, $_TABLES, $LANG_ADMIN, $LANG_ACCESS, $LANG01, $_IMAGE_TYPE, $MESSAGE;

    // set all variables to avoid warnings
    $retval = '';
    $filter_str = '';
    $order_sql = '';
    $limit = '';
    $prevorder = '';
    if (isset ($_GET['prevorder'])) { # what was the last sorting?
        $prevorder = COM_applyFilter ($_GET['prevorder']);
    }

    $query = '';
    if ( isset($_GET['q']) ) {
        $query = strip_tags(COM_stripslashes($_GET['q']));
    } else if (isset ($_POST['q'])) {
        $query = strip_tags(COM_stripslashes($_POST['q']));
    } else {
        $query = '';
    }

    $query_limit = 0;
    if ( isset($_GET['query_limit']) ) {
        $query_limit = intval(COM_applyFilter ($_GET['query_limit'], true));
    } else if ( isset($_POST['query_limit']) ) {
        $query_limit = intval(COM_applyFilter ($_POST['query_limit'], true));
    }
    if($query_limit == 0) {
        $query_limit = 50;
    }

    // we assume that the current page is 1 to set it.
    $curpage = 1;
    $page = '';
    // get the current page from the interface. The variable is linked to the
    // component, i.e. the plugin/function calling this here to avoid overlap
    if ( isset($_GET[$component . 'listpage'])) {
        $page = intval(COM_applyFilter ($_GET[$component . 'listpage'], true));
        $curpage = $page;
    } else if ( isset($_POST[$component . 'listpage'])) {
        $page = intval(COM_applyFilter ($_POST[$component . 'listpage'], true));
        $curpage = $page;
    }
    if ($curpage <= 0) {
        $curpage = 1; #current page has to be larger 0
    }

    // process text_arr for title, help url and form url
    $title = (is_array($text_arr) AND !empty($text_arr['title'])) ? $text_arr['title'] : '';
    $help_url = (is_array($text_arr) AND !empty($text_arr['help_url'])) ? $text_arr['help_url'] : '';
    $form_url = (is_array($text_arr) AND !empty($text_arr['form_url'])) ? $text_arr['form_url'] : '';

    // determine what extra options we should use (search, limit, paging)
    if (isset($text_arr['has_extras']) && $text_arr['has_extras']) { # old option, denotes all
        $has_search = true;
        $has_limit = true;
        $has_paging = true;
    } else {
        $has_search = (isset($text_arr['has_search']) && $text_arr['has_search']) ? true : false;
        $has_limit = (isset($text_arr['has_limit']) && $text_arr['has_limit']) ? true : false;
        $has_paging = (isset($text_arr['has_paging']) && $text_arr['has_paging']) ? true : false;
    }

    // process options_arr for chkdelete/chkselect options if any
    $chkselect = (is_array($options_arr) AND
                   ((isset($options_arr['chkselect']) AND $options_arr['chkselect']) OR
                   (isset($options_arr['chkdelete']) AND $options_arr['chkdelete']))) ? true : false;
    $chkall = (is_array($options_arr) AND isset($options_arr['chkall'])) ? $options_arr['chkall'] : true;
    $chkname = (is_array($options_arr) AND isset($options_arr['chkname'])) ? $options_arr['chkname'] : 'delitem';
    $chkfield = (is_array($options_arr) AND isset($options_arr['chkfield'])) ? $options_arr['chkfield'] : '';
    $chkactions = (is_array($options_arr) AND isset($options_arr['chkactions'])) ? $options_arr['chkactions'] : '';
    $chkfunction = (is_array($options_arr) AND isset($options_arr['chkfunction'])) ? $options_arr['chkfunction'] : 'ADMIN_chkDefault';
    $chkminimum = (is_array($options_arr) AND isset($options_arr['chkminimum'])) ? $options_arr['chkminimum'] : 1;

    # get all template fields.
    $admin_templates = new Template($_CONF['path_layout'] . 'admin/lists');
    $admin_templates->set_file (array (
        'search' => 'searchmenu.thtml',
        'list' => 'list.thtml',
        'header' => 'header.thtml',
        'row' => 'listitem.thtml',
        'field' => 'field.thtml',
        'arow' => 'actionrow.thtml'
    ));

    # insert std. values into the template
    $admin_templates->set_var('form_url', $form_url);
    $admin_templates->set_var('lang_edit', $LANG_ADMIN['edit']);
    $admin_templates->set_var('lang_delconfirm', $LANG01[125]);
    if (isset($form_arr['top'])) {
        $admin_templates->set_var('formfields_top', $form_arr['top']);
    }
    if (isset($form_arr['bottom'])) {
        $admin_templates->set_var('formfields_bottom', $form_arr['bottom']);
    }

    // Check if the delete checkbox and support for the delete all feature should be displayed
    if ($chkselect) {
        if ($chkall) {
            $admin_templates->set_var('header_text', '<input type="checkbox" name="chk_selectall" title="'.$LANG01[126].'" onclick="caItems(this.form,\'' . $chkname . '\');"' . XHTML . '>');
        } else {
            $admin_templates->set_var('header_text', '<input type="checkbox" name="disabled" value="x" style="visibility:hidden" DISABLED'.XHTML.'>');
        }
        $admin_templates->set_var('class', 'admin-list-field');
        $admin_templates->set_var('header_column_style', 'style="text-align:center;width:25px;"'); // always center checkbox
        $admin_templates->parse('header_row', 'header', true);
    }

    $icon_arr = ADMIN_getIcons();

    if ($has_search) { // show search
        $admin_templates->set_var('lang_search', $LANG_ADMIN['search']);
        $admin_templates->set_var('lang_submit', $LANG_ADMIN['submit']);
        $admin_templates->set_var('last_query', htmlspecialchars($query));
        $admin_templates->set_var('filter', $filter);
    }

    $sql_query = DB_escapeString($query); // replace quotes etc for security
    $sql = $query_arr['sql']; // get sql from array that builds data

    $order_var = ''; # number that is displayed in URL
    $order = '';     # field that is used in SQL
    $order_var_link = ''; # Variable for google paging.

    // is the order set in the link (when sorting the list)
    if (!isset ($_GET['order'])) {
        $order = $defsort_arr['field']; // no, get the default
    } else {
        $order_var = COM_applyFilter ($_GET['order'], true);
        $order_var_link = "&amp;order=$order_var"; # keep the variable for the google paging
        $order = $header_arr[$order_var]['field'];  # current order field name
    }
    $order_for_query = $order;

    $direction = '';
    if (!isset ($_GET['direction'])) { # get direction to sort after
        $direction = $defsort_arr['direction'];
    } else {
        $direction = COM_applyFilter ($_GET['direction']);
    }
    $direction = strtoupper($direction) == 'DESC' ? 'DESC' : 'ASC';

    if ($order == $prevorder) { #reverse direction if prev. order was the same
        $direction = ($direction == 'DESC') ? 'ASC' : 'DESC';
    } else {
        $direction = ($direction == 'DESC') ? 'DESC' : 'ASC';
    }

    if ($direction == 'ASC') { # assign proper arrow img name dep. on sort order
        $arrow = 'bararrowdown';
    } else {
        $arrow = 'bararrowup';
    }
    # make actual order arrow image
    $img_arrow_url = "{$_CONF['layout_url']}/images/$arrow.$_IMAGE_TYPE";
    $img_arrow = '&nbsp;' . COM_createImage($img_arrow_url, $arrow);

    if (!empty ($order_for_query)) { # concat order string
        $order_sql = "ORDER BY $order_for_query $direction";
    }

    $th_subtags = ''; // other tags in the th, such as onclick and mouseover
    $header_text = ''; // title as displayed to the user

    // number of columns in each row
    $ncols = count( $header_arr );

    // HEADER FIELDS array(text, field, sort, class)
    // this part defines the contents & format of the header fields
    for ($i=0; $i < $ncols; $i++) { #iterate through all headers
        $header_text = $header_arr[$i]['text'];
        $th_subtags = '';
        if (isset($header_arr[$i]['sort']) && $header_arr[$i]['sort'] != false) { # is this sortable?
            if ($order==$header_arr[$i]['field']) { # is this currently sorted?
                $header_text .= $img_arrow;
            }
            # make the mouseover effect is sortable
            $th_subtags = " onmouseover=\"this.style.cursor='pointer';\"";
            $order_var = $i; # assign number to field so we know what to sort
            if (strpos ($form_url, '?') > 0) {
                $separator = '&amp;';
            } else {
                $separator = '?';
            }
            $th_subtags .= " onclick=\"window.location.href='$form_url$separator" // onclick action
                    ."order=$order_var&amp;prevorder=$order&amp;direction=$direction";
            if (!empty ($page)) {
                $th_subtags .= '&amp;' . $component . 'listpage=' . $page;
            }
            if (!empty ($query)) {
                $th_subtags .= '&amp;q=' . urlencode($query);
            }
            if (!empty ($query_limit)) {
                $th_subtags .= '&amp;query_limit=' . $query_limit;
            }
            $th_subtags .= "';\"";
        } else {
            $th_subtags = '';
        }

        if (!empty($header_arr[$i]['header_class'])) {
            $admin_templates->set_var('class', $header_arr[$i]['header_class']);
        } else {
            $admin_templates->set_var('class', 'admin-list-headerfield');
        }
        $header_column_style = '';
        if (!empty($header_arr[$i]['align'])) {
            if ($header_arr[$i]['align'] == 'center') {
                $header_column_style = 'text-align:center;';
            } elseif ($header_arr[$i]['align'] == 'right') {
                $header_column_style = 'text-align:right;';
            }
        }
        $header_column_style .= (isset($header_arr[$i]['nowrap'])) ? ' white-space:nowrap;' : '';
        $header_column_style .= (isset($header_arr[$i]['width'])) ? ' width:' . $header_arr[$i]['width'] . ';' : '';
        if(!empty($header_column_style)) {
            $admin_templates->set_var('header_column_style', 'style="' . $header_column_style . '"');
        } else {
            $admin_templates->clear_var('header_column_style');
        }
        $admin_templates->set_var('header_text', $header_text);
        $admin_templates->set_var('th_subtags', $th_subtags);
        $admin_templates->parse('header_row', 'header', true);
        $admin_templates->clear_var('th_subtags'); // clear all for next header
        $admin_templates->clear_var('class');
        $admin_templates->clear_var('header_text');
    }

    if ($has_limit) {
        $admin_templates->set_var('lang_limit_results', $LANG_ADMIN['limit_results']);
        $limit = 50; # default query limit if not other chosen.
                     # maybe this could be a setting from the list?
        if (!empty($query_limit)) {
            $limit = $query_limit;
        }
        if ($query != '') { # set query into form after search
            $admin_templates->set_var ('query', urlencode($query) );
        } else {
            $admin_templates->set_var ('query', '');
        }
        $admin_templates->set_var ('query_limit', $query_limit);
        # choose proper dropdown field for query limit
        $admin_templates->set_var($limit . '_selected', 'selected="selected"');

        if (!empty($query_arr['default_filter'])){ # add default filter to sql
            $filter_str = " {$query_arr['default_filter']}";
        }
        if (!empty ($query)) { # add query fields with search term
            $filter_str .= " AND (";
            for ($f = 0; $f < count($query_arr['query_fields']); $f++) {
                $filter_str .= $query_arr['query_fields'][$f]
                            . " LIKE '%$sql_query%'";
                if ($f < (count($query_arr['query_fields']) - 1)) {
                    $filter_str .= " OR ";
                }
            }
            $filter_str .= ")";
        }
        $num_pages_sql = $sql . $filter_str;
        $num_pages_result = DB_query($num_pages_sql);
        $num_rows = DB_numRows($num_pages_result);
        $num_pages = ceil ($num_rows / $limit);
        if ($num_pages < $curpage) { # make sure we dont go beyond possible results
               $curpage = 1;
        }
        $offset = (($curpage - 1) * $limit);
        $limit = "LIMIT $offset,$limit"; # get only current page data
        $admin_templates->set_var ('lang_records_found',
                                   $LANG_ADMIN['records_found']);
        $admin_templates->set_var ('records_found',
                                   COM_numberFormat ($num_rows));
    }
    if ( $has_search || $has_limit || $has_paging ) {
        $admin_templates->parse('search_menu', 'search', true);
    } else {
        $admin_templates->set_var('search_menu','');
    }

    # SQL
    $sql .= "$filter_str $order_sql $limit;";
    // echo $sql;

    $result = DB_query($sql);

    // number of rows/records to display
    $nrows = DB_numRows($result);

    $r = 1; # r is the counter for the actual displayed rows for correct coloring
    for ($i = 0; $i < $nrows; $i++) { # now go through actual data
        $A = DB_fetchArray($result);
        $this_row = false; # as long as no fields are returned, dont print row
        if ($chkselect) {
            $admin_templates->set_var('class', 'admin-list-field');
            $admin_templates->set_var('column_style', 'style="text-align:center;"'); // always center checkbox
            if ($chkfunction($A)) {
                $admin_templates->set_var('itemtext', '<input type="checkbox" name="' . $chkname . '[]" value="' . $A[$chkfield] . '" title="' . $LANG_ADMIN['select'] . '"' . XHTML . '>');
            } else {
                $admin_templates->set_var('itemtext', '<input type="checkbox" name="disabled" value="x" style="visibility:hidden" DISABLED'.XHTML.'>');
            }
            $admin_templates->parse('item_field', 'field', true);
        }
        for ($j = 0; $j < $ncols; $j++) {
            $fieldname = $header_arr[$j]['field']; # get field name from headers
            $fieldvalue = '';
            if (!empty($A[$fieldname])) { # is there a field in data like that?
                $fieldvalue = $A[$fieldname]; # yes, get its data
            }
            if (!empty ($fieldfunction) && !empty ($extra)) {
                $fieldvalue = $fieldfunction ($fieldname, $fieldvalue, $A, $icon_arr, $extra);
            } else if (!empty ($fieldfunction)) { # do we have a fieldfunction?
                $fieldvalue = $fieldfunction ($fieldname, $fieldvalue, $A, $icon_arr);
            } else { # if not just take the value
                $fieldvalue = $fieldvalue;
            }
            if ($fieldvalue !== false) { # return was there, so write line
                $this_row = true;
            } else {
                $fieldvalue = ''; // dont give empty fields
            }
            if (!empty($header_arr[$j]['field_class'])) {
                $admin_templates->set_var('class', $header_arr[$j]['field_class']);
            } else {
                $admin_templates->set_var('class', 'admin-list-field');
            }
            $column_style = '';
            if (!empty($header_arr[$j]['align'])) {
                if ($header_arr[$j]['align'] == 'center') {
                    $column_style = 'text-align:center;';
                } elseif ($header_arr[$j]['align'] == 'right') {
                    $column_style = 'text-align:right;';
                }
            }
            $column_style .= (isset($header_arr[$j]['nowrap'])) ? ' white-space:nowrap;' : '';
            if(!empty($column_style)) {
                $admin_templates->set_var('column_style', 'style="' . $column_style . '"');
            } else {
                $admin_templates->clear_var('column_style');
            }
            $admin_templates->set_var('itemtext', $fieldvalue); # write field
            $admin_templates->parse('item_field', 'field', true);
        }
        if ($this_row) { # there was data in at least one field, so print line
            $r++; # switch to next color
            $admin_templates->set_var('cssid', ($r%2)+1); # make alternating table color
            $admin_templates->parse('item_row', 'row', true); # process the complete row
        }
        $admin_templates->clear_var('item_field'); # clear field
    }

    if ($nrows==0) { # there is no data. return notification message.
        $message = (isset($no_data)) ? $text_arr['no_data'] : $LANG_ADMIN['no_results'];
        $admin_templates->set_var('message', $message);
    } else {
        $footer_cols = ($chkselect) ? $ncols + 1 : $ncols;
        $admin_templates->set_var('footer_row', '<tr><td colspan="' . $footer_cols . '"><div style="margin:2px 0 2px 0;border-top:1px solid #cccccc"></div></td></tr>');
    }

    // if we displayed data, and chkselect option is available, display the
    // actions row for all selected items. provide a delete action as a minimum
    if ($nrows > 0 AND $chkselect ) {
        $actions = '<td style="text-align:center;">'
            . '<img src="' . $_CONF['layout_url'] . '/images/admin/action.' . $_IMAGE_TYPE . '"></td>';
        $delete_action = '<input name="delbutton" type="image" src="'
            . $_CONF['layout_url'] . '/images/admin/delete.' . $_IMAGE_TYPE
            . '" style="vertical-align:text-bottom;" title="' . $LANG01[124]
            . '" onclick="return confirm(\'' . $LANG01[125] . '\');"'
            . XHTML . '>&nbsp;' . $LANG_ADMIN['delete'];
        $actions .= '<td colspan="' . $ncols . '">' . $LANG_ADMIN['action'] . '&nbsp;&nbsp;&nbsp;' . $delete_action;
        $actions .= (!empty($chkactions)) ? $chkactions . '</td>' : '</td>';
        $admin_templates->set_var('actions', $actions);
        $admin_templates->parse('action_row', 'arow', true);
    }

    if ($has_paging) { # now make google-paging
        $hasargs = strstr( $form_url, '?' );
        if( $hasargs ) {
            $sep = '&amp;';
        } else {
            $sep = '?';
        }
        if (!empty($query)) { # port query to next page
            $base_url = $form_url . $sep . 'q=' . urlencode($query) . "&amp;query_limit=$query_limit$order_var_link&amp;direction=$direction";
        } else {
            $base_url = $form_url . $sep ."query_limit=$query_limit$order_var_link&amp;direction=$direction";
        }

        if ($num_pages > 1) { # print actual google-paging
            $admin_templates->set_var('google_paging',COM_printPageNavigation($base_url,$curpage,$num_pages, $component . 'listpage='));
        } else {
            $admin_templates->set_var('google_paging', '');
        }
    }

    $admin_templates->parse('output', 'list');

    // Do the actual output
    if (!empty($title)) {
        $retval .= COM_startBlock($title, $help_url,
                            COM_getBlockTemplate('_admin_block', 'header'));
    }
    $retval .= $admin_templates->finish($admin_templates->get_var('output'));
    if (!empty($title)) {
        $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));
    }

    return $retval;
}

/**
* Creates a menu with an optional icon and optional text below
* this is used in the admin screens but may be used elsewhere also.
*
* @param    array   $menu_arr       array of text & URL of the menu entries
* @param    string  $text           instructions to be displayed
* @param    string  icon            url of an icon that will be displayed
* @return   string                  HTML output of function
*
*/
function ADMIN_createMenu($menu_arr, $text, $icon = '')
{
    global $_CONF;

    $admin_templates = new Template($_CONF['path_layout'] . 'admin/lists');
    $admin_templates->set_file (
        array ('top_menu' => 'topmenu.thtml')
    );

    $menu_fields = '';
    $attr = array('class' => 'admin-menu-item');
    for ($i = 0; $i < count($menu_arr); $i++) { # iterate through menu
        $menu_fields .= COM_createLink($menu_arr[$i]['text'], $menu_arr[$i]['url'], $attr);
        if ($i < (count($menu_arr) -1)) {
            $menu_fields .= ' | '; # add separator
        }
    }
    if (!empty ($icon)) {
        $attr = array('class' => 'admin-menu-icon');
        $icon = COM_createImage($icon, '', $attr);
        $admin_templates->set_var('icon', $icon);
    }
    $admin_templates->set_var('menu_fields', $menu_fields);
    $admin_templates->set_var('lang_instructions', $text);
    $admin_templates->parse('top_menu', 'top_menu');
    $retval = $admin_templates->finish($admin_templates->get_var('top_menu'));
    return $retval;
}

?>
