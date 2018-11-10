<?php
/**
* glFusion CMS
*
* Admin-related functions needed in more than one place.
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2009-2018 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*   Mark Howard     mark AT usable-web DOT com
*
*  Based on prior work Copyright (C) 2000-2008 by the following authors:
*   Authors: Tony Bibbs       - tony AT tonybibbs DOT com
*            Mark Limburg       - mlimburg AT users DOT sourceforge DOT net
*            Jason Whittenburg  - jwhitten AT securitygeeks DOT com
*            Dirk Haun          - dirk AT haun-online DOT de
*            Oliver Spiesshofer - oliver AT spiesshofer DOT com
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
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
* Sorts a multi-dimensional associative array $data by column $field
*
* See: http://www.the-art-of-web.com/php/sortarray/
*
*/
function ADMIN_sortArray(&$data, $field, $dir='')
{
    $dir = strtolower($dir);
    $dir = (($dir == 'asc') || ($dir == 'desc')) ? $dir : 'asc';
    usort($data,build_sorter($dir,$field));
}

function build_sorter($dir, $key) {
    if ( $dir == 'asc' ) {
        return function ($a, $b) use ($key) {
            return strnatcmp($a[$key], $b[$key]);
        };
    } else {
        return function ($a, $b) use ($key) {
            return -strnatcmp($a[$key], $b[$key]);
        };
    }
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

    $icons_type_arr = array('add', 'edit', 'copy', 'delete',
                            'list', 'mail', 'group', 'user',
                            'greyuser','check', 'greycheck',
                            'cross', 'disk', 'accept', 'addchild',
                            'update', 'wrench', 'info', 'greyinfo',
                            'blank'
                            );
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
* Common function used in Admin scripts to display a list of items (old version)
*
* @param    string  $fieldfunction  Name of a function used to display the list item row details
* @param    array   $header_arr     array of header fields with sortables and table fields
* @param    array   $text_arr       array with different text strings
* @param    array   $data_arr       array with sql query data - array of list records
* @param    array   $options_arr    array of options - used for check-all feature
* @param    array   $form_arr       optional extra forms at top or bottom
* @param    string  $extra          additional values passed to fieldfunction
* @return   string                  HTML output of function
*
*/
function ADMIN_simpleList($fieldfunction, $header_arr, $text_arr,
                           $data_arr, $options_arr = '', $form_arr='', $extra='')
{

    // these are not used by simpleList - initialize to prevent E_ALL issues
    $defsort_arr = array('field' => 0, 'direction' => 'ASC');
    $filter      = '';

    $retval = ADMIN_listArray('simpleList', $fieldfunction, $header_arr, $text_arr,
                                $data_arr, $defsort_arr, $filter, $extra,
                                $options_arr, $form_arr);
    return $retval;
}

/**
* Common function used in Admin scripts to display a list of items in an array
*
* @param    string  $component      name of the list (stub for now)
* @param    string  $fieldfunction  Name of a function used to display the list item row details
* @param    array   $header_arr     array of header fields with sortables and table fields
* @param    array   $text_arr       array with different text strings
* @param    array   $data_arr       array with sql query data - array of list records
* @param    array   $defsort_arr    default sorting values
* @param    string  $filter         additional drop-down filters (stub for now)
* @param    string  $extra          additional values passed to fieldfunction
* @param    array   $options_arr    array of options - used for check-all feature
* @param    array   $form_arr       optional extra forms at top or bottom
* @return   string                  HTML output of function
*
*/
function ADMIN_listArray($component, $fieldfunction, $header_arr, $text_arr,
                           $data_arr, $defsort_arr, $filter='', $extra='',
                           $options_arr = '', $form_arr='')
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

    // number of rows/records to display
    $nrows = count($data_arr);

    if ($nrows > $chkminimum AND $chkselect) {
        if ($chkall) {
            $admin_templates->set_var('header_text', '<input type="checkbox" name="chk_selectall" title="'.$LANG01[126].'" onclick="caItems(this.form, \'' . $chkname . '\');"/>');
        } else {
            $admin_templates->set_var('header_text', '<input type="checkbox" name="disabled" value="x" style="visibility:hidden" disabled="disabled"/>');
        }
        $admin_templates->set_var('class', 'admin-list-field');
        $admin_templates->set_var('header_column_style', 'style="text-align:center;width:25px;"'); // always center checkbox
        $admin_templates->parse('header_row', 'header', true);
    }
    // setup list sort options
    if (!isset($_GET['orderby'])) {
        $orderby = $defsort_arr['field']; // not set - use default (this could be null)
    } else {
        $orderbyidx = COM_applyFilter($_GET['orderby'], true); // set - retrieve and clean
        if ( isset($header_arr[$orderbyidx]['field']) && $header_arr[$orderbyidx]['sort'] != false ) {
            $orderidx_link = "&amp;orderby=$orderbyidx"; // preserve the value for paging
            $orderby = $header_arr[$orderbyidx]['field']; // get the field name to sort by
        } else {
            $orderby = $defsort_arr['field']; // not set - use default (this could be null)
        }
    }
    // set sort direction.  defaults to ASC
    $direction = (isset($_GET['direction'])) ? COM_applyFilter($_GET['direction']) : $defsort_arr['direction'];
    $direction = strtoupper($direction) == 'DESC' ? 'DESC' : 'ASC';

    // retrieve previous sort order field
    $prevorder = (isset($_GET['prevorder'])) ? COM_applyFilter ($_GET['prevorder']) : '';
    // reverse direction if previous order field was the same (this is a toggle)
    if ($orderby == $prevorder) { // reverse direction if prev. order was the same
        $direction = ($direction == 'DESC') ? 'ASC' : 'DESC';
    }

    // assign proper arrow img based upon order
    $arrow_img = ($direction == 'ASC') ? 'ascending' : 'descending';
    $img_arrow_url = "{$_CONF['layout_url']}/images/admin/$arrow_img.$_IMAGE_TYPE";
    $attr['style'] = "vertical-align:text-top;";
    $img_arrow = '&nbsp;' . COM_createImage($img_arrow_url, $arrow_img, $attr);

    # HEADER FIELDS array(text, field, sort, align, class) =====================

    // number of columns in each row
    $ncols = count( $header_arr );

    for ($i=0; $i < $ncols; $i++) {
        $header_text = (isset($header_arr[$i]['text']) && !empty($header_arr[$i]['text'])) ? $header_arr[$i]['text'] : '';
        // check to see if field is sortable
        if (isset($header_arr[$i]['sort']) && $header_arr[$i]['sort'] != false) {
            // add the sort indicator
            $header_text .= ($orderby == $header_arr[$i]['field']) ? $img_arrow : '';
            // change the mouse to a pointer
            $th_subtags = " onmouseover=\"this.style.cursor='pointer';\"";
            // create an index so we know what to sort
            $separator = (strpos($form_url, '?') > 0) ? '&amp;' : '?';
            // ok now setup the parameters to preserve:
            // sort field and direction
            $th_subtags .= " onclick=\"window.location.href='$form_url$separator" // onclick action
                    ."orderby=$i&amp;prevorder=$orderby&amp;direction=$direction";
            $th_subtags .= "';\"";
        } else {
            $th_subtags = '';
        }
        // apply field styling if specified
        if (!empty($header_arr[$i]['header_class'])) {
            $admin_templates->set_var('class', $header_arr[$i]['header_class']);
        } else {
            $admin_templates->set_var('class', 'admin-list-headerfield');
        }
        // apply field alignment options if specified
        $header_column_style = '';
        if (!empty($header_arr[$i]['align'])) {
            if ($header_arr[$i]['align'] == 'center') {
                $header_column_style = 'text-align:center;';
            } elseif ($header_arr[$i]['align'] == 'right') {
                $header_column_style = 'text-align:right;';
            }
        }
        // apply field wrap option if specified
        $header_column_style .= (isset($header_arr[$i]['nowrap'])) ? ' white-space:nowrap;' : '';
        // allow specification of field width
        $header_column_style .= (isset($header_arr[$i]['width'])) ? ' width:' . $header_arr[$i]['width'] . ';' : '';
        if(!empty($header_column_style)) {
            $admin_templates->set_var('header_column_style', 'style="' . $header_column_style . '"');
        } else {
            $admin_templates->clear_var('header_column_style');
        }
        // output the header field
        $admin_templates->set_var('header_text', $header_text);
        $admin_templates->set_var('th_subtags', $th_subtags);
        $admin_templates->parse('header_row', 'header', true);
        // clear all for next header field (if any)
        $admin_templates->clear_var('th_subtags');
        $admin_templates->clear_var('class');
        $admin_templates->clear_var('header_text');
    }

    if ($nrows == 0) {
        $message = (isset($no_data)) ? $no_data : $LANG_ADMIN['no_results'];
        $admin_templates->set_var('message', $message);
    } else if ($data_arr === false) {
        $admin_templates->set_var('message', $LANG_ADMIN['data_error']);
    } else {
        $admin_templates->set_var('show_message', 'display:none;');

        // prior to displaying the data, sort the array if a column is specified
        if (!empty($orderby)) ADMIN_sortArray($data_arr, $orderby, $direction);

        # ARRAY DATA FIELDS ====================================================

        $row = 1;
        for ($i = 0; $i < $nrows; $i++) {
            $A = $data_arr[$i];
            $row_output = false;
            if ($nrows > $chkminimum AND $chkselect) {
                $admin_templates->set_var('class', 'admin-list-field');
                $admin_templates->set_var('column_style', 'style="text-align:center;"'); // always center checkbox
                if ($chkfunction($A)) {
                    $admin_templates->set_var('itemtext', '<input type="checkbox" name="' . $chkname . '[]" value="' . $A[$chkfield] . '" title="' . $LANG_ADMIN['select'] . '"/>');
                } else {
                    $admin_templates->set_var('itemtext', '<input type="checkbox" name="disabled" value="x" style="visibility:hidden" disabled="disabled" />');
                }
                $admin_templates->parse('item_field', 'field', true);
            }
            for ($j = 0; $j < $ncols; $j++) {
                $fieldname = $header_arr[$j]['field'];
                $fieldvalue = '';
                if (!empty($A[$fieldname])) {
                    $fieldvalue = $A[$fieldname];
                }
                if (!empty($fieldfunction) && !empty($extra)) {
                    $fieldvalue = $fieldfunction($fieldname, $fieldvalue, $A, $icon_arr, $extra);
                } elseif(!empty($fieldfunction)) {
                    $fieldvalue = $fieldfunction($fieldname, $fieldvalue, $A, $icon_arr);
                } else {
                    $fieldvalue = $fieldvalue;
                }
                if ($fieldvalue !== false) { # return was there, so write line
                    $row_output = true;
                } else {
                    $fieldvalue = ''; // dont give empty fields
                }
                // apply field style option if specified
                if (!empty($header_arr[$j]['field_class'])) {
                    $admin_templates->set_var('class', $header_arr[$j]['field_class']);
                } else {
                    $admin_templates->set_var('class', 'admin-list-field');
                }
                // process field alignment option if specified
                $column_style = '';
                if (!empty($header_arr[$j]['align'])) {
                    if ($header_arr[$j]['align'] == 'center') {
                        $column_style = 'text-align:center;';
                    } elseif ($header_arr[$j]['align'] == 'right') {
                        $column_style = 'text-align:right;';
                    }
                }
                // process field nowrap option if specified
                $column_style .= (isset($header_arr[$j]['nowrap'])) ? ' white-space:nowrap;' : '';
                if(!empty($column_style)) {
                    $admin_templates->set_var('column_style', 'style="' . $column_style . '"');
                } else {
                    $admin_templates->clear_var('column_style');
                }

                $admin_templates->set_var('itemtext', $fieldvalue);
                $admin_templates->parse('item_field', 'field', true);
            }
            // if we had any field data, then parse row
            if ($row_output) {
                $row++;
                $admin_templates->set_var('cssid', ($row%2)+1);
                $admin_templates->parse('item_row', 'row', true);
            }
            $admin_templates->clear_var('item_field');
        }
//    $footer_cols = ($chkselect) ? $ncols + 1 : $ncols;
//    $admin_templates->set_var('footer_row', '<tr><td colspan="' . $footer_cols . '"><div style="margin:2px 0 2px 0;border-top:1px solid #cccccc"></div></td></tr>');
    }

    // if we displayed data, and chkselect option is available, display the
    // actions row for all selected items. provide a delete action as a minimum
    if ($nrows > $chkminimum AND $chkselect) {
        $actions = '<td style="text-align:center;">'
            . '<img src="' . $_CONF['layout_url'] . '/images/admin/action.' . $_IMAGE_TYPE . '" alt="" /></td>';
        $actions .= '<td colspan="' . $ncols . '">' . $LANG_ADMIN['action'] . '&nbsp;&nbsp;&nbsp;';
        if (empty($chkactions)) {
            $actions .= '<input name="delbutton" type="image" src="'
            . $_CONF['layout_url'] . '/images/admin/delete.' . $_IMAGE_TYPE
            . '" style="vertical-align:text-bottom;" title="' . $LANG01[124]
            . '" onclick="return confirm(\'' . $LANG01[125] . '\');"'
            . '/>&nbsp;' . $LANG_ADMIN['delete'];
        } else {
            $actions .= $chkactions;
        }
        $actions .= '</td>';
        $admin_templates->set_var('actions', $actions);
        $admin_templates->parse('action_row', 'arow', true);
    }

    // paging will go here in the future

    // return the html output
    $admin_templates->parse('output', 'list');
    $retval = (!empty($title)) ? COM_startBlock($title, $help_url,
                            COM_getBlockTemplate('_admin_block', 'header')) : '';
    $retval .= $admin_templates->finish($admin_templates->get_var('output'));
    $retval .= (!empty($title)) ? COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')) : '';

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

    $db = glFusion\Database::getInstance();

    // retrieve the query
    if (isset($_GET['q'])) {
        $query = strip_tags($_GET['q']);
    } else if (isset($_POST['q'])) {
        $query = strip_tags($_POST['q']);
    } else if (SESS_isSet($component . '_q') ) {
        $query = strip_tags(SESS_getVar($component . '_q'));
    } else {
        $query = '';
    }

    // retrieve the query_limit
    if ( isset($_GET['query_limit']) ) {
        $query_limit = COM_applyFilter ($_GET['query_limit'], true);
    } else if ( isset($_POST['query_limit']) ) {
        $query_limit = COM_applyFilter ($_POST['query_limit'], true);
    } else if ( SESS_isSet($component.'_query_limit') ) {
        $query_limit = COM_applyFilter(SESS_getVar($component.'_query_limit'),true);
    } else {
        $query_limit = 50;
    }

    // get the current page from the interface. The variable is linked to the
    // component, i.e. the plugin/function calling this here to avoid overlap
    // the default page number is 1
    if (isset($_GET[$component . 'listpage'])) {
        $page = COM_applyFilter ($_GET[$component . 'listpage'], true);
        $curpage = $page;
    } else if ( isset($_POST[$component . 'listpage'])) {
        $page = COM_applyFilter ($_POST[$component . 'listpage'], true);
        $curpage = $page;
    } else if ( SESS_isSet($component.'listpage') ) {
        $page = COM_applyFilter(SESS_getVar($component.'listpage'),true);
        $curpage = $page;
    } else {
        $page ='';
        $curpage = 1;
    }
    $curpage = ($curpage <= 0) ? 1 : $curpage; // curpagee has to be > 0

    // process text_arr for title, help url and form url
    $title = (is_array($text_arr) AND !empty($text_arr['title'])) ? $text_arr['title'] : '';
    $help_url = (is_array($text_arr) AND !empty($text_arr['help_url'])) ? $text_arr['help_url'] : '';
    $form_url = (is_array($text_arr) AND !empty($text_arr['form_url'])) ? $text_arr['form_url'] : '';
    $no_data = (is_array($text_arr) AND !empty($text_arr['no_data'])) ? $text_arr['no_data'] : '';

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
            $admin_templates->set_var('header_text', '<input type="checkbox" name="chk_selectall" title="'.$LANG01[126].'" onclick="caItems(this.form,\'' . $chkname . '\');"/>');
        } else {
            $admin_templates->set_var('header_text', '<input type="checkbox" name="disabled" value="x" style="visibility:hidden" disabled="disabled" />');
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

    $sql = $query_arr['sql']; // get sql from array that builds data

    if ( isset($_GET['orderby']) || SESS_isSet($component.'_orderby') ) {
        if ( isset($_GET['orderby'] ) ) {
            $orderbyidx = COM_applyFilter($_GET['orderby'], true);
        } else {
            $orderbyidx = COM_applyFilter(SESS_getVar($component.'_orderby'), true);
        }
        if ( isset($header_arr[$orderbyidx]['field']) && $header_arr[$orderbyidx]['sort'] != false ) {
            $orderidx_link = "&amp;orderby=$orderbyidx"; // preserve the value for paging
            $orderby = $header_arr[$orderbyidx]['field']; // get the field name to sort by
        } else {
            $orderby = $defsort_arr['field']; // not set - use default (this could be null)
            $orderidx_link = '';
            $orderbyidx = -1;
        }
    } else {
        $orderby = $defsort_arr['field']; // not set - use default (this could be null)
        $orderidx_link = '';
        $orderbyidx = -1;
    }

    // set sort direction.  defaults to ASC
    if (isset($_GET['direction']) ) {
        $direction = COM_applyFilter($_GET['direction']);
    } else if (SESS_isSet($component . '_direction') ) {
        $direction = SESS_getVar($component.'_direction');
    } else {
        $direction = $defsort_arr['direction'];
    }
    $direction = strtoupper($direction) == 'DESC' ? 'DESC' : 'ASC';
    // retrieve previous sort order field
    if ( isset($_GET['prevorder']) ) {
        $prevorder = COM_applyFilter($_GET['prevorder']);
    } else {
        $prevorder = '';
    }

    // reverse direction if previous order field was the same (this is a toggle)
    if ($orderby == $prevorder) { // reverse direction if prev. order was the same
        $direction = ($direction == 'DESC') ? 'ASC' : 'DESC';
    }

    SESS_setVar($component.'listpage',$page);
    SESS_setVar($component.'_q',$query);
    SESS_setVar($component.'_query_limit',$query_limit);
    SESS_setVar($component.'_direction',$direction);
    SESS_setVar($component.'_orderby',$orderbyidx);

//@SQL
    // ok now let's build the order sql
    $orderbysql = (!empty($orderby)) ? "ORDER BY $orderby $direction" : '';

    // assign proper arrow img based upon order
    $arrow_img = ($direction == 'ASC') ? 'ascending' : 'descending';
    $img_arrow_url = "{$_CONF['layout_url']}/images/admin/$arrow_img.$_IMAGE_TYPE";
    $attr['style'] = "vertical-align:text-top;";
    $img_arrow = '&nbsp;' . COM_createImage($img_arrow_url, $arrow_img, $attr);

    # HEADER FIELDS array(text, field, sort, align, class) =====================

    // number of columns in each row
    $ncols = count( $header_arr );

    for ($i=0; $i < $ncols; $i++) {
        $header_text = (isset($header_arr[$i]['text']) && !empty($header_arr[$i]['text'])) ? $header_arr[$i]['text'] : '';
        // check to see if field is sortable
        if (isset($header_arr[$i]['sort']) && $header_arr[$i]['sort'] != false) {
            // add the sort indicator
            $header_text .= ($orderby == $header_arr[$i]['field']) ? $img_arrow : '';
            // change the mouse to a pointer
            $th_subtags = " onmouseover=\"this.style.cursor='pointer';\"";
            // create an index so we know what to sort
            $separator = (strpos($form_url, '?') > 0) ? '&amp;' : '?';
            // ok now setup the parameters to preserve:
            // sort field and direction
            $th_subtags .= " onclick=\"window.location.href='$form_url$separator" // onclick action
                    ."orderby=$i&amp;prevorder=$orderby&amp;direction=$direction";
            // page number
            $th_subtags .= (!empty($page)) ? '&amp;' . $component . 'listpage=' . $page : '';
            // query
            $th_subtags .= (!empty($query)) ? '&amp;q=' . urlencode($query): '';
            // query limit
            $th_subtags .= (!empty($query_limit)) ? '&amp;query_limit=' . $query_limit : '';
            $th_subtags .= "';\"";
        } else {
            $th_subtags = '';
        }
        // apply field styling if specified
        if (!empty($header_arr[$i]['header_class'])) {
            $admin_templates->set_var('class', $header_arr[$i]['header_class']);
        } else {
            $admin_templates->set_var('class', 'admin-list-headerfield');
        }
        // apply field alignment options if specified
        $header_column_style = '';
        if (!empty($header_arr[$i]['align'])) {
            if ($header_arr[$i]['align'] == 'center') {
                $header_column_style = 'text-align:center;';
            } elseif ($header_arr[$i]['align'] == 'right') {
                $header_column_style = 'text-align:right;';
            }
        }
        // apply field wrap option if specified
        $header_column_style .= (isset($header_arr[$i]['nowrap'])) ? ' white-space:nowrap;' : '';
        // apply field width option if specified
        $header_column_style .= (isset($header_arr[$i]['width'])) ? ' width:' . $header_arr[$i]['width'] . ';' : '';
        // apply field style option if specified
        if(!empty($header_column_style)) {
            $admin_templates->set_var('header_column_style', 'style="' . $header_column_style . '"');
        } else {
            $admin_templates->clear_var('header_column_style');
        }
        // output the header field
        $admin_templates->set_var('header_text', $header_text);
        $admin_templates->set_var('th_subtags', $th_subtags);
        $admin_templates->parse('header_row', 'header', true);
        // clear all for next header
        $admin_templates->clear_var('th_subtags');
        $admin_templates->clear_var('class');
        $admin_templates->clear_var('header_text');
    }

    if ($has_limit) {
        $admin_templates->set_var('lang_limit_results', $LANG_ADMIN['limit_results']);

        $limit = (!empty($query_limit)) ? $query_limit : 50; // query limit (default=50)

        if ($query != '') { # set query into form after search
            $admin_templates->set_var ('query', urlencode($query) );
        } else {
            $admin_templates->set_var ('query', '');
        }
        $admin_templates->set_var ('query_limit', $query_limit);
        # choose proper dropdown field for query limit
        $admin_templates->set_var($limit . '_selected', 'selected="selected"');

        // set the default sql filter (if any)
        $filtersql = (isset($query_arr['default_filter']) && !empty($query_arr['default_filter'])) ? " {$query_arr['default_filter']}" : '';
        $groupbysql = (isset($query_arr['group_by']) && !empty($query_arr['group_by'])) ? " GROUP BY {$query_arr['group_by']} " : '';

        // now add the query fields
        if (!empty($query)) { # add query fields with search term
            $filtersql .= " AND (";
            for ($f = 0; $f < count($query_arr['query_fields']); $f++) {
                $filtersql .= $query_arr['query_fields'][$f]
                            . " LIKE " . $db->conn->quote('%'.$query.'%') . "";
                if ($f < (count($query_arr['query_fields']) - 1)) {
                    $filtersql .= " OR ";
                }
            }
            $filtersql .= ")";
        }
        $num_pagessql = $sql . $filtersql . $groupbysql;
        try {
            $stmt = $db->conn->executeQuery($num_pagessql,
                array(),
                array(),
                new \Doctrine\DBAL\Cache\QueryCacheProfile(600, $component.'_num'));
        } catch(\Doctrine\DBAL\DBALException $e) {
            $db->dbError($e->getMessage(),$sql);
        }
        $num_rows = $stmt->rowCount();
        $stmt->closeCursor();

        $num_pages = ceil ($num_rows / $limit);
        $curpage = ($num_pages < $curpage) ? 1 : $curpage; // don't go beyond possible results
        $offset = (($curpage - 1) * $limit);
        $limitsql = "LIMIT $offset,$limit"; // get only current page data
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
    # form the sql query to retrieve the data
    if ( !isset($filtersql) ) {
        $filtersql = '';
    }
    if ( !isset($groupbysql) ) {
        $groupbysql = '';
    }
    if ( !isset($orderbysql) ) {
        $orderbysql = '';
    }
    if ( !isset($limitsql) ) {
        $limitsql = '';
    }
    $sql .= "$filtersql $groupbysql $orderbysql $limitsql;";

    try {
        $stmt = $db->conn->executeQuery($sql,
            array(),
            array(),
            new \Doctrine\DBAL\Cache\QueryCacheProfile(600, $component));
    } catch(\Doctrine\DBAL\DBALException $e) {
        $db->dbError($e->getMessage(),$sql);
    }
    $resultSet = $stmt->fetchAll(\glFusion\Database::ASSOCIATIVE);
    $stmt->closeCursor();
    $nrows = 0;

    $r = 1; # r is the counter for the actual displayed rows for correct coloring
    foreach($resultSet AS $A) {
        $nrows++;
        $row_output = false; # as long as no fields are returned, dont print row
        if ($chkselect) {
            $admin_templates->set_var('class', 'admin-list-field');
            $admin_templates->set_var('column_style', 'style="text-align:center;"'); // always center checkbox
            if ($chkfunction($A)) {
                $admin_templates->set_var('itemtext', '<input type="checkbox" name="' . $chkname . '[]" value="' . $A[$chkfield] . '" title="' . $LANG_ADMIN['select'] . '"/>');
            } else {
                $admin_templates->set_var('itemtext', '<input type="checkbox" name="disabled" value="x" style="visibility:hidden" disabled="disabled" />');
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
                $row_output = true;
            } else {
                $fieldvalue = ''; // dont give empty fields
            }
            if (!empty($header_arr[$j]['field_class'])) {
                $admin_templates->set_var('class', $header_arr[$j]['field_class']);
            } else {
                $admin_templates->set_var('class', 'admin-list-field');
            }
            // process field alignment option if specified
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
        if ($row_output) { # there was data in at least one field, so print line
            $r++; # switch to next color
            $admin_templates->set_var('cssid', ($r%2)+1); # make alternating table color
            $admin_templates->parse('item_row', 'row', true); # process the complete row
        }
        $admin_templates->clear_var('item_field'); # clear field
    }

    if ($nrows==0) { # there is no data. return notification message.
        $message = (isset($no_data)) ? $no_data : $LANG_ADMIN['no_results'];
        $admin_templates->set_var('message', $message);
    }

    // if we displayed data, and chkselect option is available, display the
    // actions row for all selected items. provide a delete action as a minimum
    if ($nrows > 0 AND $chkselect ) {
        $actions = '<td style="text-align:center;">'
            . '<img src="' . $_CONF['layout_url'] . '/images/admin/action.' . $_IMAGE_TYPE . '" alt="" /></td>';
        $actions .= '<td colspan="' . $ncols . '">' . $LANG_ADMIN['action'] . '&nbsp;&nbsp;&nbsp;';
        if (empty($chkactions)) {
            $actions .= '<input name="delbutton" type="image" src="'
            . $_CONF['layout_url'] . '/images/admin/delete.' . $_IMAGE_TYPE
            . '" style="vertical-align:text-bottom;" title="' . $LANG01[124]
            . '" onclick="return confirm(\'' . $LANG01[125] . '\');"'
            . '/>&nbsp;' . $LANG_ADMIN['delete'];
        } else {
            $actions .= $chkactions;
        }
        $actions .= '</td>';
        $admin_templates->set_var('actions', $actions);
        $admin_templates->parse('action_row', 'arow', true);
    }

    // perform the paging
    if ($has_paging) {
        $hasargs = strstr( $form_url, '?' );
        if( $hasargs ) {
            $sep = '&amp;';
        } else {
            $sep = '?';
        }
        if (!empty($query)) { # port query to next page
            $base_url = $form_url . $sep . 'q=' . urlencode($query) . "&amp;query_limit=$query_limit$orderidx_link&amp;direction=$direction";
        } else {
            $base_url = $form_url . $sep ."query_limit=$query_limit$orderidx_link&amp;direction=$direction";
        }

        if ($num_pages > 1) { # print actual google-paging
            $admin_templates->set_var('google_paging',COM_printPageNavigation($base_url,$curpage,$num_pages, $component . 'listpage='));
        } else {
            $admin_templates->set_var('google_paging', '');
        }
    }

    // return the html output
    $admin_templates->parse('output', 'list');
    $retval = (!empty($title)) ? COM_startBlock($title, $help_url,
                            COM_getBlockTemplate('_admin_block', 'header')) : '';
    $retval .= $admin_templates->finish($admin_templates->get_var('output'));
    $retval .= (!empty($title)) ? COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer')) : '';

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

    $admin_templates->set_block('top_menu', 'menu_items', 'menuvar');
    $admin_templates->set_block('top_menu', 'alt_menu_items', 'alt_menuvar');

    $attr = array('class' => 'admin-menu-item');
    for ($i = 0; $i < count($menu_arr); $i++) { # iterate through menu
        $menu_fields .= COM_createLink($menu_arr[$i]['text'], $menu_arr[$i]['url'], $attr);
        if ( isset($menu_arr[$i]['active'] ) && $menu_arr[$i]['active'] == true ) {
            $admin_templates->set_var('menu_item_url',"#");
        } else {
            $admin_templates->set_var('menu_item_url',$menu_arr[$i]['url']);
        }
        $admin_templates->set_var('menu_item_text',$menu_arr[$i]['text']);
        if ( isset($menu_arr[$i]['active'] ) && $menu_arr[$i]['active'] == true) {
            $admin_templates->set_var('menu_item_active',true);
        } else {
            $admin_templates->unset_var('menu_item_active');
        }
        $admin_templates->parse('menuvar', 'menu_items',true);
        $admin_templates->parse('alt_menuvar', 'alt_menu_items',true);

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


function ADMIN_createMenuHeader($menu_arr, $text, $title = '', $icon = '')
{
    global $_CONF;

    $admin_templates = new Template($_CONF['path_layout'] . 'admin/lists');
    $admin_templates->set_file (
        array ('top_menu' => 'topmenu_nav.thtml')
    );

    $menu_fields = '';
    $attr = array('class' => 'admin-menu-item');

    $admin_templates->set_block('top_menu', 'menu_items', 'menuvar');
    $admin_templates->set_block('top_menu', 'alt_menu_items', 'alt_menuvar');
    for ($i = 0; $i < count($menu_arr); $i++) { # iterate through menu
        $admin_templates->set_var('menu_item_url',$menu_arr[$i]['url']);
        $admin_templates->set_var('menu_item_text',$menu_arr[$i]['text']);
        $admin_templates->parse('menuvar', 'menu_items',true);
        $admin_templates->parse('alt_menuvar', 'alt_menu_items',true);
    }
    if (!empty ($icon)) {
        $attr = array('class' => 'admin-menu-icon');
        $icon = COM_createImage($icon, '', $attr);
        $admin_templates->set_var('icon', $icon);
    }
    $admin_templates->set_var('lang_title',$title);
    $admin_templates->set_var('lang_instructions', $text);

    $admin_templates->parse('top_menu', 'top_menu');
    $retval = $admin_templates->finish($admin_templates->get_var('top_menu'));
    return $retval;
}
?>
