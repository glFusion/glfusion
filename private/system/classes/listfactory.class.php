<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | listfactory.class.php                                                    |
// |                                                                          |
// | This class allows personalised lists or tables to be easily generated    |
// | from arrays or SQL statements. It will also supports the sorting and     |
// | paging of results.                                                       |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2008-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
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

/* Example Use

    // Initiate an instance of the class with the URL of the current page
    $url = $_SERVER['PHP_SELF'];
    $obj = new ListFactory($url);

    // Set up some hidden fields that will be used to help format the data later on
    $obj->setField('ID', 'id', false);

    // Set up the fields that will be seen by the user
    $obj->setField(
        '#',            // Title of the field
        ROW_NUMBER,     // The field identifier can be either:
                        //   ROW_NUMBER - The number of each row will be displayed
                        //   SQL_TITLE  - The title given the the SQL query will be displayed
                        //   <string>   - SQL column name
        true,           // Enables the field
        true,           // The field can be sorted
        '<b>%d.</b>'    // Formats the data
    );
    $obj->setField('Type', SQL_TITLE, true, true, '<b>%s</b>');
    $obj->setField('Title', 'title');
    $obj->setField('Text', 'text');
    $obj->setField('Date', 'date');

    // Set the default field to sort by
    $obj->setDefaultSort('date');

    // Set the style of output
    $obj->setStyle('table');

    // Sets the call back function to add any extra formatting to the fields
    $obj->setRowFunction('test_list_func');

    // Set up some queries to execute
    $sql = 'SELECT sid AS id, title, introtext AS text, date FROM stories';
    $obj->setQuery(
        'Story', // The name given to the query which will be displayed in the SQL_TITLE field (optional)
        'story',
        $sql,    // The SQL string without the LIMIT or ORDER BY clauses. Notice the column names match the field identifiers
        5        // The rank of the query, 5 highest = more results, 1 lowest = least results
    );
    $sql = 'SELECT cid AS id, title, comment AS text, date FROM comments';
    $obj->setQuery('Comment', 'comment', $sql, 2);

    // Append some extra rows to the output
    // Note: the array must match the field identifier names stated previously
    $extra_row = array(
        'id' => -1,
        SQL_TITLE => 'Extra Row',
        'title' => 'An extra row example',
        'text' => 'With some really really really long text.....<b>and HTML</b>',
        'date' => '2008-07-08 03:00:00'
    );
    // Add the extra row, notice it is not automatically passed to the row function
    $obj->addResult($extra_row);

    // Prints out the list
    $results = $obj->ExecuteQueries();
    $title = 'Test ListFactory';
    $text = 'Showing %d - %d of %d results.';
    $retval = $obj->getFormattedOutput($results, $title, $text);
    echo $retval;

    // This function is called by the ListFactory to provide furthur formatting of the results.
    function test_list_func($preSort, $row)
    {
        if ($preSort)
        {
            // extract any further information from the results.
            // such as converting user ID's to user names
        }
        else
        {
            // Create a link from the title and id
            $row['title'] = '<a href="http://www.glfusion.org/list_test.php?id='.$row['id'].'">'.$row['title'].'</a>';

            // Shorten the text and strip any HTML tags
            $row['text'] = substr(strip_tags($row['text']), 0, 20);
        }

        // Return the reformatted row
        return $row;
    }

*/

/**
 * glFusion List Factory Class
 *
 * @author Sami Barakat <s.m.barakat AT gmail DOT com>
 *
 */
class ListFactory {

    // PRIVATE VARIABLES
    var $_fields = array();
    var $_query_arr = array();
    var $_total_rank = 0;
    var $_sort_arr = array();
    var $_def_sort_arr = array();
    var $_page = 1;
    var $_per_page = 0;
    var $_page_limits = array();
    var $_function = '';
    var $_preset_rows = array();
    var $_page_url = '';
    var $_style = 'table';
    var $_limits = array();
    var $_total_found = 0;

    /**
     * Constructor
     *
     * Sets up private url variable and defines the
     * SQL_TITLE, SQL_NAME and ROW_NUMBER constants.
     *
     * @access public
     * @param string $url The URL of the page the table appears on
     * @param array $limits The avaliable page limits
     * @param integer $per_page The default number or rows per page
     *
     */
    function __construct($url, $limits = '10,15,20,25,30,35', $per_page = 20)
    {
        $url .= (strpos($url,'?') === false ? '?' : '&amp;');
        $this->_page_url = $url;
        $this->_style = 'table';
        $this->_per_page = $per_page;

        if (is_string($limits))
            $this->_page_limits = explode(',', $limits);
        else if (is_array($limits))
            $this->_page_limits = $limits;
        else
            $this->_page_limits = array(10, 15, 20, 25, 30, 35);

        define('SQL_TITLE', 0);
        define('SQL_NAME', 1);
        define('ROW_NUMBER', 2);
    }

    /**
     * Determins which set of templates to load when formatting the output
     *
     * @access public
     * @param string $style Either 'table' or 'inline'
     *
     */
    function setStyle($style)
    {
        $this->_style = $style;
    }

    /**
     * Sets a field in the list.
     *
     * Note: ROW_NUMBER cannot be sorted
     *
     * @access public
     * @param string $title The title of the field which is displayed to the user
     * @param string $name The local name given to the field
     * @param boolean $display True if the field is to be displayed to the user otherwise false
     * @param boolean $sort True if the field can be sorted otherwise false
     * @param string $format The format string with one type specifier
     *
     */
    function setField($title, $name, $display = true, $sort = true, $format = '%s')
    {
        if ($name === ROW_NUMBER)
            $sort = false;
        $this->_fields[] = array('title' => $title, 'name' => $name, 'display' => $display, 'sort' => $sort, 'format' => $format);
    }
    function addTotalRank( $rank )
    {
        $this->_total_rank+= $rank;
    }
    function getTotalRank( )
    {
        return $this->_total_rank;
    }
    function getPerPage()
    {
        return $this->_per_page;
    }
    function addToTotalFound($total)
    {
        $this->_total_found += $total;
    }

    /**
     * Sets the SQL query that will generate rows
     *
     * @access public
     * @param string $title The text that's displayed to the user
     * @param string $name The local name given to the query
     * @param string $sql The SQL string without the ORDER BY or LIMIT clauses
     * @param integer $rank The rating that determins how many results will be returned
     *
     */
    function setQuery($title, $name, $sql, $rank)
    {
        $total_results = $this->_numRows($sql);
        if ( $total_results > 0 ) {
            $this->_query_arr[] = array('title' => $title, 'name' => $name, 'sql' => $sql, 'rank' => $rank, 'found' => $total_results, 'type'=>'sql');
            $this->_total_rank += $rank;
        }
    }
    /**
     * Set rank and totals for non-SQL based search
     */

    function setQueryText($title,$name,$query,$numResults,$rank)
    {
        if ( $numResults > 0 ) {
            $this->_query_arr[] = array('title' => $title, 'name' => $name, 'sql' => $query, 'rank' => $rank, 'found' => $numResults,'type'=>'text');
            $this->_total_rank += $rank;
        }
    }


    /**
     * Sets the callback function that gets called when formatting a row
     *
     * @access public
     * @param string $function The name given to a call back function that can format the results
     * @param object $inst The instance of the class that contains the function
     *
     */
    function setRowFunction($function)
    {
        $this->_function = $function;
    }

    /**
     * Sets the default sort field
     *
     * @access public
     * @param string $field The field name to sort
     * @param string $direction 'asc' for ascending order and 'desc' for descending order
     *
     */
    function setDefaultSort($field, $direction = 'desc')
    {
        $this->_def_sort_arr = array('field' => $field, 'direction' => $direction);
    }

    /**
     * Appends a result to the list
     *
     * @access public
     * @param array $result An single result that will be appended to the rest
     *
     */
    function addResult($result)
    {
        $this->_preset_rows[] = $result;
    }

    /**
     * Gets the total number of results from a query
     *
     * @access private
     * @param string $sql The query
     * @return integer Total number of rows
     *
     */
    function _numRows($sql)
    {
        if (is_array($sql))
        {
            $sql['mysql'] = preg_replace('/SELECT.*?FROM/is', 'SELECT COUNT(*) FROM', $sql['mysql']);
        } else {
            $sql = preg_replace('/SELECT.*?FROM/is', 'SELECT COUNT(*) FROM', $sql);
        }

        $result = DB_query($sql);
        $num_rows = DB_numRows($result);
        if ($num_rows <= 1)
        {
            $B = DB_fetchArray($result, true);
            $num_rows = $B[0];
        }
        return $num_rows ? $num_rows : 0;
    }

    /**
     * Executes pre set queries
     *
     * @access public
     * @return array The results found
     *
     */
    function ExecuteQueries()
    {
        global $_CONF;

        if ( isset($_POST['order']) ) {
            $this->_sort_arr['field'] = COM_applyFilter($_POST['order']);
        } elseif (isset($_GET['order']) ) {
            $this->_sort_arr['field'] = COM_applyFilter($_GET['order']);
        } else {
            $this->_sort_arr['field'] = $this->_def_sort_arr['field'];
        }
        if ( isset($_POST['direction']) ) {
            $this->_sort_arr['direction'] = ($_POST['direction'] == 'asc' ? 'asc' : 'desc');
        } elseif (isset($_GET['direction']) ) {
            $this->_sort_arr['direction'] = ($_GET['direction'] == 'asc' ? 'asc' : 'desc');
        } else {
            $this->_sort_arr['direction'] = $this->_def_sort_arr['direction'];
        }
        if (is_numeric($this->_sort_arr['field'])) {
            $ord = $this->_def_sort_arr['field'];
            $this->_sort_arr['field'] = SQL_TITLE;
        } else {
            $ord = $this->_sort_arr['field'];
        }
        if ( !$this->array_search_recursive($ord,$this->_fields) ) {
            $order_sql = ' ORDER BY date DESC';
        } else {
            $order_sql = ' ORDER BY ' . DB_escapeString($ord) . ' ' . DB_escapeString(strtoupper($this->_sort_arr['direction']));
        }
        if ( isset($_POST['results']) ) {
            $this->_per_page = COM_applyFilter($_POST['results'], true);
        } elseif (isset($_GET['results']) ) {
            $this->_per_page = COM_applyFilter($_GET['results'], true);
        }
        $keyType = 'any';
        if ( isset($_POST['keyType']) ) {
            $keyType = COM_applyFilter($_POST['keyType']);
        } elseif (isset($_GET['keyType']) ) {
            $keyType = COM_applyFilter($_GET['keyType']);
        }
        // Calculate the limits for each query

        $num_query_results = $this->_per_page - count($this->_preset_rows);
        $pp_total = count($this->_preset_rows);

        if ( isset($_POST['page']) ) {
            $this->_page = COM_applyFilter($_POST['page'], true);
        } elseif (isset($_GET['page']) ) {
            $this->_page = COM_applyFilter($_GET['page'], true);
        } else {
            $this->_page = 1;
        }
        if ( (isset($_POST['np']) && $_POST['np'] == 1 ) || (isset($_GET['np']) && $_GET['np'] == 1 ) ) {
            $this->_page++;
        }
        $prevPage = 0;
        if ( (isset($_POST['pp']) && $_POST['pp'] == 1 ) || (isset($_GET['pp']) && $_GET['pp'] == 1 ) ) {
            $this->_page--;
            $prevPage = 1;
        }
        if ( $this->_page < 1 ) {
            $this->_page = 1;
        }
        if ( isset($_POST['i']) ) {
            $encode = urldecode($_POST['i']);
        } elseif (isset($_GET['i']) ) {
            $encode = urldecode($_GET['i']);
        } else {
            $encode = '';
        }
        if ( $encode != '' ) {
            $decode = base64_decode($encode);
            $vars = explode(',',$decode);
            for ($i=0;$i<count($vars);$i++) {
                list($name,$value) = explode('=',$vars[$i]);
                $_post_offset[$name] = intval($value);
            }
        }

        if ( isset($_POST['j']) ) {
            $encode = urldecode($_POST['j']);
        } elseif (isset($_GET['j']) ) {
            $encode = urldecode($_GET['j']);
        } else {
            $encode = '';
        }
        if ( $encode != '' ) {
            $decode = base64_decode($encode);
            $vars = explode(',',$decode);
            for ($i=0;$i<count($vars);$i++) {
                list($name,$value) = explode('=',$vars[$i]);
                $_post_pp[$name] = intval($value);
            }
        }
        /*
         * preprocess so we can find those that are empty on the subsequest pages
         * so we can adjust the total ranking number.
         *
         * can we handle those that are too short (more pp than items found)?
         */

        $limits = array();
        $bucket = 0;
        $bucket_limit = $num_query_results;

        for ($i = 0; $i < count($this->_query_arr); $i++) {
            $limits[$i]['name'] = $this->_query_arr[$i]['name'];
            $limits[$i]['total'] = $this->_query_arr[$i]['found'];

            $limits[$i]['pp'] = round(($this->_query_arr[$i]['rank'] / $this->_total_rank) * $num_query_results);
            $this->_total_found += $this->_query_arr[$i]['found'];
            /*
             * Check to see if the total found is less that the per page limit
             */
            if ( $limits[$i]['total'] < $limits[$i]['pp'] ) {
                $real_pp = $limits[$i]['total'];
                $limits[$i]['pp'] = $real_pp;
            } else {
                $real_pp = $limits[$i]['pp'];
                $limits[$i]['limit'] = $real_pp;
            }
            /*
             * Calculate the offset based on how many we can fit on a page
             */
            $name = $limits[$i]['name'];
            if ( isset($_post_offset[$name]) && isset($_REQUEST['np']) && $_REQUEST['np'] == 1 ) {
                $limits[$i]['offset'] = COM_applyFilter($_post_offset[$name],true) + COM_applyFilter($_post_pp[$name],true);
            } else {
                $limits[$i]['offset'] = ($this->_page - 1) * $limits[$i]['pp'];
                if ( $limits[$i]['offset'] < 0 ) {
                    $limits[$i]['offset'] = 0;
                }
            }
            /*
             * Check to see if offset+limit is greater
             */

            if ( ($limits[$i]['offset'] + $limits[$i]['pp']) > $limits[$i]['total']) {
                $limits[$i]['pp'] = ($limits[$i]['total'] - $limits[$i]['offset']);
                $real_pp = $limits[$i]['pp'];
            }

            /*
             * Check to see if the offset is greater than the total
             */
            if ( $limits[$i]['offset'] >= $limits[$i]['total'] ) {
                // set limit to 0 so we will skip later
                $limits[$i]['limit'] = 0;
            } else {
                $limits[$i]['limit']  = $real_pp;
                $bucket += $real_pp;
            }
        }
        /*
         * Check to see if the bucket limit is too big (all searches returned less)
         */
        if ( ( $this->_page  * $bucket_limit ) > $this->_total_found ) {
            $shortby = ( $this->_page  * $bucket_limit ) - $this->_total_found;
            $bucket_limit = $bucket_limit - $shortby;
        }
        /*
         * now loop through the bunch to fill the gaps, we'll loop several times
         */
        $done = 1;
        while ( $bucket < $bucket_limit ) {
            $done = 1;
            for ($i = 0; $i < count($this->_query_arr); $i++) {
                if ( ($limits[$i]['offset'] + $limits[$i]['limit']) < $limits[$i]['total'] ) {
                    $limits[$i]['pp']++;
                    $limits[$i]['limit']++;
                    $bucket++;
                    $done = 0;
                    if ( $bucket >= $bucket_limit ) {
                        break;
                    }
                }
            }
            if ( $done ) {
                break;
            }
            if ( $bucket >= $bucket_limit) {
                break;
            }
        }

        for ($i = 0; $i < count($this->_query_arr); $i++) {
            if ($limits[$i]['limit'] != 0 ) {
                $pp_total+=$limits[$i]['pp'];
            }
        }

        // Execute each query in turn
        $rows_arr = $this->_preset_rows;
        for ($i = 0; $i < count($this->_query_arr); $i++) {
            if ($limits[$i]['limit'] == 0 || $limits[$i]['limit'] < 0) {
                continue;
            }
            if ($this->_query_arr[$i]['type'] == 'sql' ) {
                $limit_sql = " LIMIT ".(int) $limits[$i]['offset'].",".(int) $limits[$i]['limit'];
                if (is_array($this->_query_arr[$i]['sql'])) {
                    $this->_query_arr[$i]['sql']['mysql'] .= $order_sql . $limit_sql;
                    $this->_query_arr[$i]['sql']['mssql'] .= $order_sql . $limit_sql;
                } else {
                    $this->_query_arr[$i]['sql'] .= $order_sql . $limit_sql;
                }
                $result = DB_query($this->_query_arr[$i]['sql']);

                while ($A = DB_fetchArray($result)) {
                    $col = array();
                    $col[SQL_TITLE] = $this->_query_arr[$i]['title'];
                    $col[SQL_NAME] = $this->_query_arr[$i]['name'];

                    foreach ($this->_fields as $field) {
                        if (!is_numeric($field['name'])) {
                            if ($field['name'] == '_html') {
                                $col[$field['name']] = $field['format'];
                            } else {
                                $col[ $field['name'] ] = isset($A[$field['name']]) ? $A[ $field['name'] ] : '';
                            }
                        }
                    }
                    // Need to call the format function before and after
                    // sorting the results.
                    if (is_callable($this->_function)) {
                        $col = call_user_func($this->_function, true, $col);
                    }
                    $rows_arr[] = $col;
                }
            } else if ( $this->_query_arr[$i]['type'] == 'text' ) {
                $sqlFunction = 'plugin_executepluginsearch_'.$this->_query_arr[$i]['name'];
                $sqlResults  = $sqlFunction($this->_query_arr[$i]['sql'],$limits[$i]['offset'],$limits[$i]['limit'],$keyType);
                if ( is_array($sqlResults) ) {
                    foreach ($sqlResults as $A) {
                        $col = array();
                        $col[SQL_TITLE] = $this->_query_arr[$i]['title'];
                        $col[SQL_NAME] = $this->_query_arr[$i]['name'];

                        foreach ($this->_fields as $field) {
                            if (!is_numeric($field['name'])) {
                                if ($field['name'] == '_html') {
                                    $col[$field['name']] = $field['format'];
                                } else {
                                    if ( isset($A[$field['name']])) {
                                        $col[ $field['name'] ] = $A[ $field['name'] ];
                                    }
                                }
                            }
                        }

                        // Need to call the format function before and after
                        // sorting the results.
                        if (is_callable($this->_function)) {
                            $col = call_user_func($this->_function, true, $col);
                        }
                        $rows_arr[] = $col;
                    }
                }
            }
        }
        // Sort the final array
        $direction = $this->_sort_arr['direction'] == 'asc' ? SORT_ASC : SORT_DESC;
        $column = array();
        foreach ($rows_arr as $sortarray) {
            $column[] = strip_tags($sortarray[ $this->_sort_arr['field'] ]);
        }
        array_multisort($column, $direction, $rows_arr);

        $this->_limits = $limits;

        return $rows_arr;
    }

    /**
     * Generates the HTML code based on the preset style
     *
     * @access public
     * @param array $rows_arr The rows to display in the list
     * @param string $title The title of the list
     * @param string $list_top HTML that will appear before the list is printed
     * @param string $list_bottom HTML that will appear after the list is printed
     * @param boolean $show_sort True to enable column sorting, false to disable
     * @param boolean $show_limit True to show page limits, false to hide
     * @return string HTML output
     *
     */
    function getFormattedOutput($rows_arr, $title, $list_top = '', $list_bottom = '', $show_sort = true, $show_limit = true)
    {
        global $_CONF, $_IMAGE_TYPE, $LANG_ADMIN, $LANG09,$LANG05;

        // get all template fields.
        $list_templates = new Template($_CONF['path_layout'] . 'lists/' . $this->_style);
        $list_templates->set_file (array (
            'list'  => 'list.thtml',
            'limit' => 'page_limit.thtml',
            'sort'  => 'page_sort.thtml',
            'row'   => 'item_row.thtml',
            'field' => 'item_field.thtml'
        ));

        $search_helper = '';

        $string_offsets = '';
        $string_pp = '';
        for ($i=0;$i<count($this->_limits);$i++) {
            if ( $i != 0 ) {
                $string_offsets.=',';
                $string_pp.=',';
            }
            $name  = $this->_limits[$i]['name'];
            $value = $this->_limits[$i]['offset'];
            $pp    = $this->_limits[$i]['limit'];
            $string_offsets .= $name . '='.$value;
            $string_pp .= $name . '=' . $pp;
        }
        $offset_encode = urlencode(base64_encode($string_offsets));
        $pp_encode = urlencode(base64_encode($string_pp));

        if (count($rows_arr) == 0) {
            $list_templates->set_var('message', $LANG_ADMIN['no_results']);
            $list_templates->set_var('list_top', $list_top);
            $list_templates->set_var('list_bottom', $list_bottom);
            $list_templates->parse('output', 'list');

            // No results to show so quickly print a message and exit
            $retval = '';
            if (!empty($title))
                $retval .= COM_startBlock($title, '', COM_getBlockTemplate('_admin_block', 'header'));
            $retval .= $list_templates->finish($list_templates->get_var('output'));
            if (!empty($title))
                $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            return $retval;
        }

        if ( $this->_style == 'table' ) {
            foreach ($this->_fields as $field) {
                if ($field['display'] == true && $field['title'] != '') {
                    $text = $sort_text . $field['title'];
                    $href = '';
                    $selected = '';
                    $list_templates->set_var('sort_text', $text);
                    $list_templates->set_var('sort_href', $href);
                    $list_templates->set_var('sort_selected', $selected);
                    $list_templates->parse('page_sort', 'sort', true);
                }
            }
        }

        $offset = ($this->_page-1) * $this->_per_page;

        $list_templates->set_var('show_message', 'display:none;');

        // Run through all the results
        $r = 1;
        foreach ($rows_arr as $row) {
            if (is_callable($this->_function)) {
                $row = call_user_func($this->_function, false, $row);
            }

            foreach ($this->_fields as $field) {
                if ($field['display'] == true) {
                    $fieldvalue = '';
                    if ($field['name'] == ROW_NUMBER)
                        $fieldvalue = $r + $offset;
                    else if (!empty($row[ $field['name'] ]))
                        $fieldvalue = $row[ $field['name'] ];
                    if ( $fieldvalue != '' ) {
                        $fieldvalue = sprintf($field['format'], $fieldvalue, $field['title']);

                        // Write field
                        $list_templates->set_var('field_text', $fieldvalue);
                        $list_templates->parse('item_field', 'field', true);
                    }
                }
            }

            // Write row
            $r++;
            $list_templates->set_var('cssid', ($r % 2) + 1);
            $list_templates->parse('item_row', 'row', true);
            $list_templates->clear_var('item_field');
        }

        // Print page numbers
        $page_url = $this->_page_url . 'order=' . $this->_sort_arr['field'] . '&amp;direction=' . $this->_sort_arr['direction'] . '&amp;results=' . $this->_per_page . '&amp;page='.$this->_page.'&amp;i='.$offset_encode.'&amp;j='.$pp_encode;
        $num_pages = ceil($this->_total_found / $this->_per_page);
        $gp = '';
        if ( $num_pages > 1 ) {
            if ( $this->_page == 1 ) {
                $gp .= '[&nbsp;' . '<a href="'.$page_url.'&amp;np=1">'.$LANG05[5].'</a>&nbsp;]';
            } else {
                if ( $this->_page < $num_pages ) {
                    $gp .= '[&nbsp;' . '<a href="'.$page_url.'&amp;pp=1">'.$LANG05[6].'</a>&nbsp;]&nbsp;&nbsp;&nbsp;';
                    $gp .= '[&nbsp;' . '<a href="'.$page_url.'&amp;np=1">'.$LANG05[5].'</a>&nbsp;]';
                } else {
                    $gp .= '[&nbsp;' . '<a href="'.$page_url.'&amp;pp=1">'.$LANG05[6].'</a>&nbsp;]';
                }
            }
        }
        $list_templates->set_var('google_paging',$gp);
        $list_templates->set_var('page_url',$page_url);

        $search_numbers = @sprintf($LANG09[64],$offset+1, $r+$offset-1, $this->_total_found);
        $list_top = $list_top . '<p>'.$search_numbers.'<br /></p>';
        $list_templates->set_var('list_top', $list_top);
        $list_templates->set_var('list_bottom', $list_bottom);

        $list_templates->parse('output', 'list');

        // Do the actual output
        $retval = '<div style="margin-top:5px;margin-bottom:5px;border-bottom:1px solid #ccc;"></div>';

        $retval .= $list_templates->finish($list_templates->get_var('output'));

        return $retval;
    }



    /**
     * Recursively searches an array
     *
     * @access public
     * @return bool true / false
     *
     */
    function array_search_recursive($needle, $haystack)
    {
        $found = 0;
        foreach($haystack as $id => $val) {
            if($val === $needle) {
                $found++;
                break;
            } else if(is_array($val)){
                $found=$this->array_search_recursive($needle, $val);
                if($found>0){
                    break;
                }
            }
        }
        return $found;
    }
}

?>