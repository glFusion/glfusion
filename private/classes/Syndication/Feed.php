<?php
/**
 * Class to handle glFusion syndication feed definitions.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Syndication;
use \glFusion\Database\Database;


/**
 * Syndication Feed class.
 * @package glfusion
 */
class Feed
{
    /** Record ID.
     * @var integer */
    protected $fid = 0;

    /** Type of feed, e.g. plugin name.
     * @var string */
    protected $type = '';

    /** Feed topic. For plugins may be a category or other limiter.
     * @var string */
    protected $topic = '';

    /** Header Topic ID.
     * @var string */
    protected $header_tid = '';

    /** Feed format spec.
     * @var string */
    protected $format = '';

    /** Feed format version.
     * @var string */
    protected $format_version = '';

    /** Limit the number of items or time span for feed content.
     * @var string */
    protected $limits = '10';

    /** Content Length. 0 = none, 1 = all, anything else limits the content.
     * @var integer */
    protected $content_length = 1;

    /** Feed title.
     * @var string */
    protected $title = '';

    /** Feed description.
     * @var string */
    protected $description = '';

    /** Logo image URL.
     * @var string */
    protected $feedlogo = '';

    /** Feed filename to write in the rss_path.
     * @var string */
    protected $filename = '';

    /** Character Set.
     * @var string */
    protected $charset = 'utf-8';

    /** Language.
     * @var string */
    protected $language = 'en-gb';

    /** Flag to enable or disable a feed.
     * @var integer */
    protected $is_enabled = 1;

    /** Last update date/time.
     * @var object */
    protected $updated = NULL;

    /** Comma-separated lists of item IDs in the last update.
     * Used to determine if the feed needs to be rewritten.
     * @var string */
    protected $update_info = '';


    /**
     * Constructor.
     *
     * @param  mixed   $A  Array of properties or group ID
     */
    public function __construct(array $A=array())
    {
        global $_TABLES;

        if (is_array($A)) {
            $this->setVars($A);
        }
    }


    /**
     * Get a feed object based on the feed type.
     *
     * @param   integer $fid    Feed record ID
     * @return  object      Child object according to feed type
     */
    public static function getById(int $fid) : object
    {
        $sql = "fid = " . (int)$fid;
        $feeds = self::_execQuery($sql);
        // $feeds will be an array with one element, so get it by index.
        if (isset($feeds[$fid])) {
            return $feeds[$fid];
        } else {
            return new self;
        }
    }


    /**
     * Get all the enabled feeds, optionally filtering by type.
     *
     * @param   string  $type   Optional type of feed, aka plugin name
     * @return  array       Array of feed objects
     */
    public static function getEnabled(string $type='') : array
    {
        $type = "is_enabled = 1";
        if (!empty($type)) {
            $type = "AND type = '" . DB_escapeString($type) . "'";
        }
        return self::_execQuery($type);
    }


    /**
     * Get all the feeds, optionally filtering by type.
     *
     * @param   string  $type   Optional type of feed, aka plugin name
     * @return  array       Array of feed objects
     */
    public static function getAll(string $type='') : array
    {
        if (!empty($type)) {
            $type = "type = '" . DB_escapeString($type) . "'";
        }
        return self::_execQuery($type);
    }


    /**
     * Execute the SQL query with the provided filter.
     *
     * @param   string  $where  Optional SQL filter string
     * @return  array       Array of instantiated child objects
     */
    private static function _execQuery(string $where='') : array
    {
        global $_TABLES;

        $retval = array();
        $db = Database::getInstance();
        $sql = "SELECT * FROM `{$_TABLES['syndication']}`";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        try {
            $stmt = $db->conn->executeQuery($sql);
        } catch(Throwable $e) {
            return $retval;
        }
        $data = $stmt->fetchAll(Database::ASSOCIATIVE);
        if (is_array($data)) {
            foreach ($data as $feed) {
                $format = explode('-', $feed['format']);
                switch ($format[0]) {
                case 'ICS':
                case 'Atom':
                case 'RSS':
                case 'XML':
                    $cls = __NAMESPACE__ . '\\Formats\\' . $format[0];
                    $retval[$feed['fid']] = new $cls($feed);
                    break;
                default:
                    $F = PLG_callFunctionForOnePlugin(
                        'plugin_getfeedprovider_' . $feed['type'],
                        array(1 => $format[0], 2 => $feed)
                    );
                    if ($F) {
                        $retval[$feed['fid']] = new $F($feed);
                    } else {
                        $retval[$feed['fid']] = new Formats\XML($feed);
                    }
                    break;
                }
            }
        }
        return $retval;
    }


    /**
     * Set all property values from DB or form.
     *
     * @param   array   $A          Array of property name=>value
     * @return  object  $this
     */
    public function setVars(array $A) : object
    {
        global $_CONF;

        // All property names
        $fields = array(
            'fid', 'type', 'topic', 'header_tid', 'limits', 'format',
            'content_length', 'title', 'description', 'feedlogo', 'filename',
            'charset', 'language', 'is_enabled', 'update_info', 'updated',
        );
        foreach ($fields as $fld_name) {
            if (isset($A[$fld_name])) {
                switch ($fld_name) {
                case 'is_enabled':
                case 'content_length':
                    $this->$fld_name = (int)$A[$fld_name];
                    break;
                case 'format':
                    $format = explode('-', $A['format']);
                    $this->format = $format[0];
                    if (isset($format[1])) {
                        $this->format_version = $format[1];
                    }
                    break;
                case 'updated':
                    $this->updated = new \Date($A['updated'], $_CONF['timezone']);
                    break;
                default:
                    $this->$fld_name = $A[$fld_name];
                    break;
                }
            }
        }
        return $this;
    }


    public function getDescription()
    {
        return $this->description;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getContentLength()
    {
        return $this->content_length;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getLogo()
    {
        return $this->feedlogo;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getFid()
    {
        return $this->fid;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function getLimits()
    {
        return $this->limits;
    }


    /**
     * Get the path of the feed directory or a specific feed file
     *
     * @param   string  $feedfile   (option) feed file name
     * @return  string              path of feed directory or file
     */
    public static function getFeedPath( $feedfile = '' ) : string
    {
        global $_CONF;

        $feed = $_CONF['path_rss'] . $feedfile;
        return $feed;
    }


    /**
     * Get the URL of the feed directory or a specific feed file
     *
     * @param    string  $feedfile   (option) feed file name
     * @return   string              URL of feed directory or file
     */
    public static function getFeedUrl( string $feedfile = '' ) : string
    {
        global $_CONF;

        $feedpath = self::getFeedPath();
        $url = substr_replace($feedpath, $_CONF['site_url'], 0, strlen ($_CONF['path_html']) - 1);
        $url .= $feedfile;
        return $url;
    }


    /**
     * Wrapper function for each feed type's _generate() function.
     * Handles updating the syndication table with new information.
     *
     * @return  void
     */
    public function Generate() : void
    {
        global $_CONF, $_TABLES, $_SYND_DEBUG;

        // Actually generate the feed.
        $this->_generate();

        // Now perform housekeeping.
        if (empty($this->update_info)) {
            $data = 'NULL';
        } else {
            $data = DB_escapeString($this->update_info);
        }
        if ($_SYND_DEBUG) {
            Log::write('system',Log::DEBUG,"update_info for feed {$this->fid} is {$this->update_info}");
        }

        $db = Database::getInstance();
        $db->conn->executeUpdate(
            "UPDATE {$_TABLES['syndication']} SET updated = ?, update_info = ? WHERE fid = ?",
            array(
                $_CONF['_now']->toMySQL(true),
                $data,
                $this->fid
            ),
            array(Database::STRING,Database::STRING,Database::STRING)
        );
    }


    /**
     * Accessor to allow plugin feed providers to send the list of updated IDs.
     *
     * @param   string  $data_str   Comma-separated list of item IDs
     * @return  object  $this
     */
    public function setUpdateData(?string $data_str) : object
    {
        $this->update_info= $data_str;
        return $this;
    }


    /**
     * Get all the available feed formats.
     *
     * @param   string  $type   Type of feed, e.g. plugin name
     * @return  array   Array of feed (name, version) elements
     */
    public static function findFormats(string $type = '') : array
    {
        global $_CONF;

        if (!empty($type)) {
            $func = 'plugin_getfeedformats_' . $type;
            $formats= PLG_callFunctionForOnePlugin($func);
            if (!empty($formats)) {
                return $formats;
            }
        }

        $formats = array();
        $files = glob(__DIR__ . '/Formats/*.php');
        if (is_array($files)) {
            foreach ($files as $fullpath) {
                $parts = pathinfo($fullpath);
                $class = $parts['filename'];
                $classfile = __NAMESPACE__ . '\\Formats\\' . $class;
                foreach ($classfile::$versions as $version) {
                    $formats[] = array(
                        'name' => $class,
                        'version' => $version,
                    );
                }
            }
            asort($formats);
        }
        return $formats;
    }


    /**
     * Write the feed data to the specified file.
     *
     * @param   string  $filename   Filename to write, path will be added
     * @param   string  $data       Data to be written
     * @return  bool        True on success, False on error
     */
    protected function _writeFile(string $fileName, string $data) : bool
    {
        $filepath = self::getFeedPath($fileName);
        if (($fp = @fopen($filepath, 'w')) !== false) {
            fputs($fp, $data);
            fclose($fp);
            return true;
        } else {
            Log:;write('system', Log::ERROR, "Error: Unable to open $filepath for writing");
            return false;
        }
    }

}
