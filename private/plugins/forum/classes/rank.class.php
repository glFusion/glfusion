<?php
/**
*   Class to handle Forum ranks
*
*   @author     Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2017 Lee Garner <lee@leegarner.com>
*   @package    glfusion
*   @version    0.0.1
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
namespace Forum;

class Rank
{
    /**
    *   Properties of the instantiated object
    *   @var array() */
    private $properties = array();

    /**
    *   Constructor.
    *   Sets the field values from the supplied array, or reads the record
    *   if $A is a rank ID.
    *
    *   @param  mixed   $A  Array of properties or group ID
    */
    public function __construct($A='')
    {
        if (is_array($A)) {
            $this->setVars($A, true);
        } else {
            $A = (int)$A;
            $this->posts = $A;
            if ($this->posts > 0) {
                $this->Read($this->posts);
            } else {
                // Set reasonable defaults
                $this->posts = 0;
                $this->dscp = '';
            }
        }
    }


    /**
    *   Read a single rank record into an instantiated object.
    *
    *   @param  integer $posts  Rank record ID
    *   @return boolean     True on success, False on error or not found
    */
    public function Read($posts)
    {
        global $_TABLES;

        $sql = "SELECT * FROM {$_TABLES['ff_ranks']}
                WHERE posts = " . (int)$posts;
        $res = DB_query($sql);
        if ($res && DB_numRows($res) == 1) {
            $A = DB_fetchArray($res, false);
            $this->setVars($A);
            return true;
        } else {
            return false;
        }
    }


    /**
    *   Setter function. Sanitizes values and sets into $properties
    *
    *   @param  string  $key    Name of property
    *   @param  mixed   $value  Property value
    */
    public function __set($key, $value)
    {
        switch ($key) {
        case 'posts':
            $this->properties[$key] = (int)$value;
            break;
        case 'dscp':
            $this->properties[$key] = trim($value);
            break;
        }
    }


    /**
    *   Getter function. Returns property value or NULL if not set
    *
    *   @param  string  $key    Name of property
    *   @return mixed           Value of property
    */
    public function __get($key)
    {
        return isset($this->properties[$key]) ? $this->properties[$key] : NULL;
    }


    /**
    *   Set all property values from DB or form
    *
    *   @param  array   $A          Array of property name=>value
    *   @param  boolean $from_db    True of coming from the DB, False for form
    */
    public function setVars($A, $from_db = true)
    {
        foreach ($A as $key=>$value) {
            $this->$key = $value;
        }
    }


    /**
    *   Gets an array of all the rank groups.
    *
    *   @param  boolean $enabled    True to get only enabled ranks
    *   @return array           Array of grp_name=>grp_id
    */
    public static function getAll()
    {
        global $_TABLES;

        static $cache = NULL;

        if ($cache === NULL) {
            $cache = array();
            $sql = "SELECT * FROM {$_TABLES['ff_ranks']}
                    ORDER BY posts ASC";
            $res = DB_query($sql);
            if ($res) {
                while ($A = DB_fetchArray($res, false)) {
                    $cache[$A['posts']] = new self($A);
                }
            }
        }
        return $cache;
    }


    /**
    *   Creates the edit form.
    *
    *   @return string      HTML for edit form
    */
    public function Edit()
    {
        global $_TABLES;

        $T = new \Template(__DIR__ . '/../templates/admin/');
        $T->set_file('editform', 'editrank.thtml');
        $T->set_var(array(
            'posts'     => $this->posts,
            'dscp'      => $this->dscp,
        ) );
        $T->parse('output','editform');
        return $T->finish($T->get_var('output'));
    }


    /**
    *   Save a rank from the edit form
    *
    *   @param  array   $A      Array of fields, e.g. $_POST
    *   @return string      Error messages, empty string on success
    */
    public function Save($A = array())
    {
        global $_TABLES, $LANG_GF01;

        if (!empty($A)) {
            $this->setVars($A, false);
        }
        if ($A['orig_posts'] > 0) {
            // updating an existing record
            $sql1 = "UPDATE {$_TABLES['ff_ranks']} SET ";
            $sql3 = " WHERE posts = " . (int)$A['orig_posts'];
        } else {
            // inserting a new record
            $sql1 = "INSERT INTO {$_TABLES['ff_ranks']} SET ";
            $sql3 = '';
        }
        $sql2 = "posts = {$this->posts},
                dscp = '" . DB_escapeString($this->dscp) . "'";
        DB_query($sql1 . $sql2 . $sql3);
        if (DB_error())  {
            return $LANG_GF01['badge_save_error'];
        } else {
            return '';
        }
    }


    /**
    *   Delete a single rank record. Does not delete the image.
    *
    *   @param  integer $posts  Rank ID to delete
    */
    public static function Delete($posts)
    {
        global $_TABLES;
        DB_delete($_TABLES['ff_ranks'], 'posts', (int)$posts);
    }


    /**
    *   Get the user ranking stars and display text.
    *   Caches the output for repeated display. $admin_lvl overrides the
    *   post count to display a specially-formatted rank for admins and mods.
    *
    *   @param  integer $posts  Number of posts
    *   @param  integer $admin_lvl  0=None, 1=Moderator, 2=Forum Admin
    *   @rturn  array   Array of (image display, level description)
    */
    public static function getRank($posts, $admin_lvl = 0)
    {
        global $_CONF, $LANG_GF01;
        static $cache = array();

        $ranks = self::getAll();
        $total_ranks = count($ranks);
        $rank = 0;
        $txt = '';
        $isAdmin = false;
        $isMod = false;

        switch ($admin_lvl) {
        case 2:
            $rank = $total_ranks;
            $txt = $LANG_GF01['admin'];
            $isAdmin = true;
            break;
        case 1:
            $rank = $total_ranks;
            $txt = $LANG_GF01['moderator'];
            $isMod = true;
            break;
        default:
            $i = 0;
            $have_rank = false;
            foreach ($ranks as $R) {
                if ($posts < $R->posts) break;;
                $txt = $R->dscp;
                $i++;
                $have_rank = true;
            }
            $rank = $i;
            break;
        }

        // Initialize the cache if needed.
        if (!isset($cache[$admin_lvl])) {
            $cache[$admin_lvl] = array();
        }
        // Return cached userlevel if set.
        if (array_key_exists($rank, $cache[$admin_lvl])) {
            return array($cache[$admin_lvl][$rank], $txt);
        }

        if ($have_rank) {
        // Create a new userlevel display from template if needed.
        $T = new \Template($_CONF['path'] . 'plugins/forum/templates');
        $T->set_file('stars', 'rank.thtml');
        $T->set_var(array(
            'loopfilled'  => $rank,
            'loopopen'  => max(($total_ranks - $rank),0),
            'txt'   => $txt,
            'isAdmin' => $isAdmin,
            'isMod' => $isMod,
        ) );
        $T->parse('output', 'stars');
        $cache[$admin_lvl][$rank] = $T->finish($T->get_var('output'));
        } else {
        $cache[$admin_lvl][$rank] = '';
        }
            
        return array($cache[$admin_lvl][$rank], $txt);
    }

}

?>
