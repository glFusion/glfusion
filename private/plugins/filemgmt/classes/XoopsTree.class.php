<?php
// +--------------------------------------------------------------------------+
// | FileMgmt Plugin - glFusion CMS                                           |
// +--------------------------------------------------------------------------+
// | xoopstree.php                                                            |
// |                                                                          |
// | Displays elements in tree format                                         |
// +--------------------------------------------------------------------------+
// | Copyright (C) 2002-2015 by the following authors:                        |
// |                                                                          |
// | Mark R. Evans          mark AT glfusion DOT org                          |
// |                                                                          |
// | Copyright (C) 2004 by Consult4Hire Inc.                                  |
// | Author:                                                                  |
// | Blaine Lang            blaine@portalparts.com                            |
// |                                                                          |
// | Based on:                                                                |
// | myPHPNUKE Web Portal System - http://myphpnuke.com/                      |
// | PHP-NUKE Web Portal System - http://phpnuke.org/                         |
// | Thatware - http://thatware.org/                                          |
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
namespace Filemgmt;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

class XoopsTree
{
    /** Name of table with parent-child structure.
     * @var string */
    private $table;

    /** Name of unique id field for records in table $table.
     * @var integer */
    public  $id;

    /** Name of parent id field used in table $table.
     * @var integer */
    private $pid;

    /** Specifies the order of query results.
     * @var string */
    private $order;

    /** Name of a field which will be used for selection box and paths.
     * @var string */
    private $title;

    /** Name of the database.
     * @var string */
    private $db;

    /** Selected list of groups allowed access.
     * @var string */
    public $filtersql = '';


    /**
     * Constructor of class XoopsTree.
     * Sets the names of table, unique id, and parend id.
     *
     * @param   string  $db_name    Database name
     * @param   string  $table_name Table name
     * @param   string  $id_name    Record ID field name
     * @param   string  $pid_name   Parent ID field name
     */
    public function __construct($db_name,$table_name, $id_name, $pid_name)
    {
        $this->db = $db_name;
        $this->table = $table_name;
        $this->id = $id_name;
        $this->pid = $pid_name;
    }

    public function setGroupAccessFilter($groups) {
        if (count($groups) == 1) {
            $this->filtersql = " AND grp_access = '" . current($groups) ."'";
        } else {
            $this->filtersql = " AND grp_access IN (" . implode(',',array_values($groups)) .")";
        }
    }

    public function setGroupUploadAccessFilter($groups) {
        if (count($groups) == 1) {
            $this->filtersql = " AND grp_writeaccess = '" . current($groups) ."'";
        } else {
            $this->filtersql = " AND grp_writeaccess IN (" . implode(',',array_values($groups)) .")";
        }
    }

    // returns an array of first child objects for a given id($sel_id)
    public function getFirstChild($sel_id, $order=""){
        $arr = array();

        if ( $order != "" ) {
            $result = DB_query("SELECT * FROM {$this->table} WHERE {$this->pid} = {$sel_id} $this->filtersql ORDER BY $order");
        } else {
            $result = DB_query("SELECT * FROM {$this->table} WHERE {$this->pid} = {$sel_id} $this->filtersql");
        }
        $count  = DB_numRows($result);
        if ( $count==0 ) {
            return $arr;
        }
        while ( $myrow=DB_fetchArray($result,false) ) {
            array_push($arr, $myrow);
        }
        return $arr;
    }

    // returns an array of all FIRST child ids of a given id($sel_id)
    public function getFirstChildId($sel_id){
        $idarray =array();
        $result = DB_query("SELECT $this->id FROM $this->table WHERE $this->pid = '$sel_id'");
        $count  = DB_numRows($result);
        if ( $count == 0 ) {
            return $idarray;
        }
        while ( list($id) = DB_fetchArray($result) ) {
            array_push($idarray, $id);
        }
        return $idarray;
    }

    //returns an array of ALL child ids for a given id($sel_id)
    public function getAllChildId($sel_id,$order="",$idarray = array()){
        //$sql = "SELECT ".$this->id." FROM ".$this->table." WHERE ".$this->pid."=".$sel_id."";
        if ( $order != "" ) {
            $result = DB_query("SELECT $this->id FROM $this->table WHERE $this->pid = '$sel_id' ORDER BY $order");
        } else {
            $result = DB_query("SELECT $this->id FROM $this->table WHERE $this->pid = '$sel_id'");
        }

        if ( DB_numRows($result) == 0 ) {
            return $idarray;
        }
        while ( list($r_id) = DB_fetchArray($result) ) {
            array_push($idarray, $r_id);
            $idarray = $this->getAllChildId($r_id,$order,$idarray);
        }
        return $idarray;
    }

    //returns an array of ALL parent ids for a given id($sel_id)
    public function getAllParentId($sel_id,$order="",$idarray = array())
    {
        $sql = "SELECT $this->pid FROM $this->table WHERE $this->id = '$sel_id'";
        if ( $order != "" ) {
            $sql .= " ORDER BY $order";
        }
        $result=$this->db->query($sql);
        list($r_id) = $this->db->fetchRow($result);
        if ( $r_id == 0 ) {
            return $idarray;
        }
        array_push($idarray, $r_id);
        $idarray = $this->getAllParentId($r_id,$order,$idarray);
        return $idarray;
    }

    //generates path from the root id to a given id($sel_id)
    // the path is delimetered with "/"
    public function getPathFromId($sel_id, $title, $path="")
    {
        $result = DB_query("SELECT $this->pid, $title FROM $this->table WHERE $this->id = '$sel_id'");
           if ( DB_numRows($result) == 0 ) {
            return $path;
        }
        list($parentid,$name) = DB_fetchArray($result);
        $path = "/".$name.$path."";
        if ( $parentid == 0 ) {
            return $path;
        }
        $path = $this->getPathFromId($parentid, $title, $path);
        return $path;
    }


    /**
     * Create only the option elements for a selection field.
     */
    public function makeMySelBoxOptions($title,$order="",$preset_id=0, $none=0, $sel_name="", $onchange="", $exclude='')
    {
        $retval = '';

        if ( $sel_name == "" ) {
            $sel_name = $this->id;
        }
        $myts = MyTextSanitizer::getInstance();

        $sql = "SELECT $this->id, $title FROM $this->table WHERE $this->pid = 0 $this->filtersql ";
        if ( $order != "" ) {
            $sql .= " ORDER BY $order";
        }
        $result = DB_query($sql);
        if ( $none ) {
            $retval .= "<option value='0'>----</option>\n";
        }
        while ( list($catid, $name) = DB_fetchARRAY($result) ) {
            if ( $catid == $preset_id ) {
                $sel = " selected='selected'";
            } else {
                $sel = '';
            }
            $retval .= "<option value='$catid'$sel>$name</option>\n";
            $sel = "";
            $arr = $this->getChildTreeArray($catid);
            foreach ( $arr as $option ) {
                $option['prefix'] = str_replace(".","--",$option['prefix']);
                $catpath = $option['prefix']."&nbsp;".$myts->makeTboxData4Show($option[$title]);
                if ( $option[$this->id] == $preset_id ) {
                    $sel = " selected='selected'";
                }
                $retval .= "<option value='".$option[$this->id]."'$sel>$catpath</option>\n";
                $sel = "";
            }
        }
        return $retval;
    }


    /**
     * Makes a nicely ordered selection box, including the "select" tags.
     * $preset_id is used to specify a preselected item.
     * set $none to 1 to add a option with value 0
     */
    public function makeMySelBox($title,$order="",$preset_id=0, $none=0, $sel_name="", $onchange="",$exclude='')
    {
        if ( $sel_name == "" ) {
            $sel_name = $this->id;
        }
        $myts = MyTextSanitizer::getInstance();
        $retval = "<select name='".$sel_name."'";
        if ( $onchange != "" ) {
            $retval .= ' onchange="' . $onchange . ' "';
        }
        $retval .= ">\n";
        $retval .= $this->makeMySelBoxOptions($title,$order,$preset_id, $none, $sel_name, $onchange,$exclude);
        $retval .= "</select>\n";
        return $retval;
    }


    //generates nicely formatted linked path from the root id to a given id
    public function getNicePathFromId($sel_id, $title, $funcURL, $path="")
    {
        $sql = "SELECT $this->pid, $title FROM $this->table WHERE $this->id = '$sel_id'";
        $result = DB_query($sql);
        if ( DB_numROWS($result) == 0 ) {
            return $path;
        }
        list($parentid,$name) = DB_fetchARRAY($result);
        $myts = MyTextSanitizer::getInstance();
        $name = $myts->makeTboxData4Show($name);
        if (strpos($funcURL,'?',0) === FALSE) {
            $path = "<li><a href=\"{$funcURL}?{$this->id}={$sel_id}\">{$name}</a>{$path}</li>";
        } else {
            $path = "<li><a href=\"{$funcURL}&{$this->id}={$sel_id}\">{$name}</a>{$path}</li>";
        }
        if ( $parentid == 0 ) {
            return $path;
        }
        $path = $this->getNicePathFromId($parentid, $title, $funcURL, $path);
        return $path;
    }

    //generates id path from the root id to a given id
    // the path is delimetered with "/"
    public function getIdPathFromId($sel_id, $path="")
    {
        $result = $this->db->query("SELECT $this->pid FROM $this->table WHERE $this->id = '$sel_id'");
        if ( $this->db->getRowsNum($result) == 0 ) {
            return $path;
        }
        list($parentid) = $this->db->fetchRow($result);
        $path = "/".$sel_id.$path."";
        if ( $parentid == 0 ) {
            return $path;
        }
        $path = $this->getIdPathFromId($parentid, $path);
        return $path;
    }


    public function getAllChild($sel_id=0,$order="",$parray = array())
    {
        $sql = "SELECT * FROM $this->table WHERE $this->pid = '$sel_id'";
        if ( $order != "" ) {
            $sql .= " ORDER BY $order";
        }
        $result = $this->db->query($sql);
        $count = $this->db->getRowsNum($result);
        if ( $count == 0 ) {
            return $parray;
        }
        while ( $row = DB_fetchARRAY($result) ) {
            array_push($parray, $row);
            $parray=$this->getAllChild($row[$this->id],$order,$parray);
        }
        return $parray;
    }

    public function getChildTreeArray($sel_id=0,$order="",$parray = array(),$r_prefix="")
    {
        $sql = "SELECT * FROM $this->table WHERE $this->pid = '$sel_id' $this->filtersql ";
        if ( $order != "" ) {
            $sql .= " ORDER BY $order";
        }
        $result = DB_query($sql);
        $count = DB_numROWS($result);
        if ( $count == 0 ) {
            return $parray;
        }
        while ( $row = DB_fetchARRAY($result) ) {
            $row['prefix'] = $r_prefix.".";
            array_push($parray, $row);
            $parray = $this->getChildTreeArray($row[$this->id],$order,$parray,$row['prefix']);
        }
        return $parray;
    }

}
