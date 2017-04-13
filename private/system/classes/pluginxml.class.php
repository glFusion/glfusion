<?php
/**
* glFusion CMS
*
* Plugin XML Parser
*
* @license GNU General Public License version 2 or later
*     http://www.opensource.org/licenses/gpl-license.php
*
*  Copyright (C) 2008-2017 by the following authors:
*   Mark R. Evans   mark AT glfusion DOT org
*/

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own!');
}

class pluginXML
{
    private $pluginData = array();
    private $state;

    public function __construct()
    {
        $this->state = '';
    }

    public function getPluginData()
    {
        return $this->pluginData;
    }

    public function parseXMLFile($filename)
    {

        if ( $filename == '' || !@file_exists( $filename ) ) {
            return -1;
        }

        if ( !($fp = @fopen( $filename, "r" ) ) ) {
            return -1;
        }

        $this->pluginData = array();

        if (!($xml_parser = xml_parser_create()))
            return false;

        xml_set_element_handler($xml_parser,
                                array($this,"startElementHandler"),
                                array($this,"endElementHandler"));

        xml_set_character_data_handler( $xml_parser,
                                        array($this,"characterDataHandler"));

        while( $data = fread($fp, 4096)){
            if(!xml_parse($xml_parser, $data, feof($fp))) {
                break;
            }
        }
        xml_parser_free($xml_parser);
    }

    /**
    * XML startElement callback
    *
    * used for plugin.xml parsing
    *
    * @param    object $parser  Handle to the parser object
    * @param    string $name    Name of element
    * @param    array  $attrib  array of attributes for element
    * @return   none
    *
    */
    private function startElementHandler ($parser,$name,$attrib) {

        switch ($name) {
            case 'ID' :
                $this->state = 'id';
                break;
            case 'NAME' :
                $this->state = 'pluginname';
                break;
            case 'VERSION' :
                $this->state = 'pluginversion';
                break;
            case 'GLFUSIONVERSION' :
                $this->state = 'glfusionversion';
                break;
            case 'PHPVERSION' :
                $this->state = 'phpversion';
                break;
            case 'DESCRIPTION' :
                $this->state = 'description';
                break;
            case 'URL' :
                $this->state = 'url';
                break;
            case 'MAINTAINER' :
                $this->state = 'maintainer';
                break;
            case 'DATABASE' :
                $this->state = 'database';
                break;
            case 'REQUIRES' :
                $this->state = 'requires';
                break;
            case 'DATAPROXYDRIVER' :
                $this->state = 'dataproxydriver';
                break;
            case 'LAYOUT' :
                $this->state = 'layout';
                break;
            case 'RENAMEDIST' :
                $this->state = 'renamedist';
                break;
        }
    }

    private function endElementHandler ($parser,$name)
    {
        $this->state='';
    }

    private function characterDataHandler ($parser, $data)
    {
        if (!$this->state) {
            return;
        }

        switch ($this->state) {
            case 'id' :
                $this->pluginData['id'] = $data;
                break;
            case 'pluginname' :
                $this->pluginData['name'] = $data;
                break;
            case 'pluginversion' :
                $this->pluginData['version'] = $data;
                break;
            case 'glfusionversion' :
                $this->pluginData['glfusionversion'] = $data;
                break;
            case 'phpversion' :
                $this->pluginData['phpversion'] = $data;
                break;
            case 'description' :
                $this->pluginData['description'] = $data;
                break;
            case 'url' :
                $this->pluginData['url'] = $data;
                break;
            case 'maintainer' :
                $this->pluginData['author'] = $data;
                break;
            case 'database' :
                $this->pluginData['database'] = $data;
                break;
            case 'requires' :
                $this->pluginData['requires'][] = $data;
                break;
            case 'dataproxydriver' :
                $this->pluginData['dataproxydriver'] = $data;
                break;
            case 'layout' :
                $this->pluginData['layout'] = $data;
                break;
            case 'renamedist' :
                $this->pluginData['renamedist'][] = $data;
                break;
        }
    }
}
?>