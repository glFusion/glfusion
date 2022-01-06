<?php
/**
 * glFusion Rating Interface.
 *
 * @license Creative Commons Attribution 3.0 License.
 *     http://creativecommons.org/licenses/by/3.0/
 *
 *  Copyright (C) 2008-2022 by the following authors:
 *   Mark R. Evans   mark AT glfusion DOT org
 *
 *  Based on original work Copyright (C) 2006,2007,2008 by the following authors:
 *   Ryan Masuga, masugadesign.com  - ryan@masugadesign.com
 *   Masuga Design
 *      http://masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar
 *   Komodo Media (http://komodomedia.com)
 *   Climax Designs (http://slim.climaxdesigns.com/)
 *   Ben Nolan (http://bennolan.com/behaviour/) for Behavio(u)r!
 *
 *  Homepage for this script:
 *  http://www.masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/
 *
 *  This (Unobtusive) AJAX Rating Bar script is licensed under the
 *  Creative Commons Attribution 3.0 License
 *    http://creativecommons.org/licenses/by/3.0/
 *
 *  What that means is: Use these files however you want, but don't
 *  redistribute without the proper credits, please. I'd appreciate hearing
 *  from you if you're using this script.
 *
 *  To mimic RATING_ratingBar(), call as
 *      return glFusion\Rater\Rater::create($type, $id)
 *          ->withTotalVotes($total_votes)
 *          ->withRating($total_value)
 *          ->withUnits($units)
 *          ->withStatic($static)
 *          ->withSize($size)
 *          ->withWrapper($wrapper)
 *          ->Render();
 *  Only create() and render() are required unless variations to the default parameters
 *  are needed.
 */
namespace glFusion\Rater;
use glFusion\Database\Database;
use Template;


/**
 * Class to manage ratings and display the ratingbar.
 * @package glfusion
 */
class Rater
{
    /** Record ID of the item rating information.
     * @var integer */
    private $rating_id = 0;

    /** Value of the current rating (total_value / total_votes).
     * @var float */
    private $rating = 0;

    /** Type of item being rated, e.g. plugin name.
     * @var string */
    private $item_type = '';

    /** Item ID.
     * @var string */
    private $item_id = '';

    /** Voter's user ID stored in Votes table.
     * @var integer */
    private $uid = 0;

    /** IP Address of the voter.
     * @var string */
    private $ip = '';

    /** Total number of votes given.
     * @var integer */
    private $total_votes = 0;

    /** Total value of all votes.
     * @var integer */
    private $total_value = 0;

    /** Flag to indicate that the current user has already voted.
     * @var boolean */
    private $voted = 0;

    /** Total number of rating stars to show.
     * @var integer */
    private $units = 5;

    /** Flag to indicate that the rating bar is static (no voting).
     * @var boolean */
    private $static = 0;

    /** Size of the stars shown.
     * 'sm' indicates small icons, anything else indicates normal.
     * Normal size uses the uk-icon-small class.
     * @var string */
    private $size = 'med';

    /** Flag to wrap the rating bar in a wrapper.
     * @var boolean */
    private $wrapper = 1;

    /** Flag to indicate that the javascript has been included.
     * @var boolean */
    private static $have_js = 0;

    /** Field by which votes will be sorted.
     * @var string */
    private $vote_sortby = 'ratingdate';

    /** Sort order for votes, asc or desc.
     * @var string */
    private $vote_sortdir = 'desc';


    /**
     * Create a Rater object and assign the current user values.
     */
    public function __construct(?string $item_type=NULL, ?string $item_id = NULL)
    {
        global $_USER;

        $this->ip = $_SERVER['REAL_ADDR'];
        $this->uid = (int)$_USER['uid'];
        if ($item_type !== NULL && $item_id !== NULL) {
            $this->item_type = $item_type;
            $this->item_id = $item_id;
            $this->getItemRating();
        }
    }


    /**
     * Set the rating record ID.
     *
     * @param   integer $id     Rating record ID
     * @return  object  $this
     */
    public function withRatingID(int $id) : self
    {
        $this->rating_id = (int)$id;
        return $this;
    }


    /**
     * Get the rating record ID.
     *
     * @return  integer     Record ID of the rating
     */
    public function getRatingID() : int
    {
        return (int)$this->rating_id;
    }


    /**
     * Set the item ID.
     *
     * @param   string  $item_id    Item ID
     * @return  object  $this
     */
    public function withItemID(string $item_id) : self
    {
        $this->item_id = $item_id;
        return $this;
    }


    /**
     * Get the item ID.
     *
     * @return  string  $item_id    Item ID
     */
    public function getItemID() : string
    {
        return $this->item_id;
    }


    /**
     * Set the voter's user ID.
     *
     * @param   integer $uid        User ID
     * @return  object  $this
     */
    public function withUid(int $uid) : self
    {
        $this->uid = (int)$uid;
        return $this;
    }


    /**
     * Get the voter's user ID.
     *
     * @return  integer     User ID
     */
    public function getUid() : int
    {
        return (int)$this->uid;
    }


    /**
     * Set the item type, e.g. plugin name.
     *
     * @param   string  $type   Item type/Plugin name
     * @return  object  $this
     */
    public function withItemType(string $type) : self
    {
        $this->item_type = $type;
        return $this;
    }


    /**
     * Get the item type/plugin name.
     *
     * @return  string      Item type
     */
    public function getItemType() : string
    {
        return $this->item_type;
    }


    /**
     * Set the number of units (stars) to be used.
     *
     * @param   integer $units      Number of units
     * @return  object  $this
     */
    public function withUnits(int $units) : self
    {
        $this->units = (int)$units;
        return $this;
    }


    /**
     * Set the voter's IP address.
     *
     * @param   string  $ip     IP address, obtained from `$_SERVER` if empty
     * @return  object  $this
     */
    public function withIpAddress(?string $ip=NULL) : self
    {
        if ($ip === NULL) {
            $ip = $_SERVER['REAL_ADDR'];
        }
        $this->ip = $ip;
        return $this;
    }


    /**
     * Get the voter's IP address.
     *
     * @return  string      IP address
     */
    public function getIpAddress() : string
    {
        return $this->ip;
    }


    /**
     * Set the total votes received for the item.
     *
     * @param   integer $count  Total vote count for the current item
     * @return  object  $this
     */
    public function withTotalVotes(int $count) : self
    {
        $this->total_votes = (int)$count;
        return $this;
    }


    /**
     * Get the total vote count for the current item.
     *
     * @return  integer     Number of votes received
     */
    public function getTotalVotes() : int
    {
        return (int)$this->total_votes;
    }


    /**
     * Set the current rating for the item.
     *
     * @param   float   $rating     Current rating
     * @return  object  $this
     */
    public function withRating(float $rating) : self
    {
        $this->rating = (float)$rating;
        return $this;
    }


    /**
     * Get the current rating for the item.
     *
     * @param   boolean $sql    True to force decimal format for MySQL
     * @return  float   Current rating
     */
    public function getRating(?bool $sql=NULL) : float
    {
        $rating = sprintf('%2.02f', $this->rating);
        if ($sql) {
            $rating = str_replace(",", ".", $rating);
            $rating = preg_replace('/\.(?=.*\.)/', '', $rating);
        }
        return $rating;
    }


    /**
     * Set the flag to wrap the output in `<div>` sections or not.
     * Wrapper is normally used for the initial display, but not when
     * called via AJAX.
     *
     * @param   boolean $flag   True to wrap, False to not
     * @return  object  $this
     */
    public function withWrapper(bool $flag) : self
    {
        $this->wrapper = $flag ? 1 : 0;
        return $this;
    }


    /**
     * Set the static display flag.
     *
     * @param   boolean $flag   True to show a static display, False for normal
     * @return  object  $this
     */
    public function withStatic(bool $flag) : self
    {
        $this->static = $flag ? 1 : 0;
        return $this;
    }


    /**
     * Set the size of the icons to use.
     *
     * @param   string  $size   Size indicator (sm, med, lg)
     * @return  object  $this
     */
    public function withSize(string $size) : self
    {
        switch ($size) {
        case 'sm':
        case 'small':
            $this->size = 'sm';
            break;
        case 'med':
        case 'medium':
            $this->size = 'med';
            break;
        case 'lg':
        case 'large':
            $this->size = 'lg';
            break;
        }
        return $this;
    }


    /**
     * Set the field to sort votes by.
     *
     * @param   string  $fld    Sorting field name
     * @return  object  $this
     */
    public function voteSortBy(string $fld) : self
    {
        $validFields = array(
            'id','type','item_id','uid','vote','ip_address','ratingdate',
        );
        if (in_array($fld, $validFields)) {
            $this->vote_sort_by = $fld;
        }
        return $this;
    }


    /**
     * Set the order to sort votes (ascending or descending).
     *
     * @param   string  $dir    Either ASC or DESC
     * @return  object  $this
     */
    public function voteSortDir(string $dir) : self
    {
        $dir = strtoupper($dir);
        if ($dir != 'ASC') {
            $dir = 'DESC';
        }
        $this->vote_sortdir = $dir;
        return $this;
    }


    /**
     * Factory function to create a rating bar.
     * If tye type and ID are supplied then the database is read.
     *
     * @param   string  $item_type  Type of item (plugin name)
     * @param   string  $item_id    Item ID
     * @return  object      New Rater object
     */
    public static function create(?string $item_type=NULL, ?string $item_id=NULL) : self
    {
        $retval = new self($item_type, $item_id);
        return $retval;
    }


    /**
     * Render the rating bar.
     *
     * @return  string      HTML for rating bar
     */
    public function render() : string
    {
        global $_USER, $_CONF, $LANG13;

        // determine whether the user has voted, so we know how to draw the ul/li
        // now draw the rating bar
        $has_voted = $this->userHasVoted();
        $text = '';
        $rating1 = @number_format($this->rating, 2);
        $tense = ($this->total_votes == 1) ? $LANG13['vote'] : $LANG13['votes'];
        if ($this->static || $has_voted) {
            $rater_cls = '';
            $voting = 0;
        } else {
            $rater_cls = 'ratingstar enabled';
            $voting = 1;
        }

        $retval = '';
        $T = new Template($_CONF['path_layout']);
        $T->set_file('rater', 'ratingbar.thtml');
        $T->set_var(array(
            'wrapper'   => $this->wrapper,
            'item_id'   => $this->item_id,
            'item_type' => $this->item_type,
            'ip_address' => $this->ip,
            'units'     => $this->units,
            'voting'    => $voting,
            'tense'     => $tense,
            'rating'    => $rating1,
            'total_votes' => $this->total_votes,
            'bar_size'  => $this->size,
            'need_js'   => self::$have_js ? 0 : 1,
        ) );
        self::$have_js = 1;

        // Place the rating icons.
        // RTL is set in the CSS, so start with the right (unchecked)
        // and work back to the left.
        $T->set_block('rater', 'ratingIcons', 'Icons');
        for ($i = $this->units; $i > ceil($this->rating); $i--) {
            $T->set_var(array(
                'checked' => 'unchecked',
                'points' => $i,
                'rater_cls' => $rater_cls,
                'size'      => $this->size,
            ) );
            $T->parse('Icons', 'ratingIcons', true);
        }
        if ($this->rating != (int)$this->rating) {
            $T->set_var(array(
                'checked' => 'half',
                'points' => $i,
                'rater_cls' => $rater_cls,
                'size'      => $this->size,
            ) );
            $T->parse('Icons', 'ratingIcons', true);
            $i--;
        }
        for (; $i >= 1; $i--) {
            $T->set_var(array(
                'checked' => 'checked',
                'points' => $i,
                'rater_cls' => $rater_cls,
                'size'      => $this->size,
            ) );
            $T->parse('Icons', 'ratingIcons', true);
        }

        $T->parse('output', 'rater');
        $retval .= $T->finish($T->get_var('output'));
        return $retval;
    }


    /**
     * Returns an array of all voting records for either a $type or an $item_id.
     *
     * @param   string  $type       Plugin name
     * @param   string  $item_id    Item id (optional)
     * @param   string  $sort       Column to sort data by
     * @param   string  $sortdir    asc or desc
     * @param   array   $filterArray Array of operator=>clause for where clause
     * @return  array       Array of all matching voting records
     */
    public function getVoteData(?array $filterArray=NULL) : array
    {
        global $_TABLES;

        $retval = array();

        $type = DB_escapeString($this->item_type);
        $item_id = DB_escapeString($this->item_id);
        if ($item_id != '') {
            $whereClause = " AND item_id ='$item_id' ";
        }
        if (is_array($filterArray)) {
            foreach ($filterArray AS $bType=>$filter) {
                $whereClause .= ' ' . $bType . ' ' . $filter;
            }
        }

        $db = Database::getInstance();
        $sql = "SELECT * FROM {$_TABLES['rating_votes']} AS r
            LEFT JOIN {$_TABLES['users']} AS u
            ON r.uid = u.uid
            WHERE type = \"{$type}\" $whereClause
            ORDER BY {$this->vote_sortby} {$this->vote_sortdir}";
        $retval = $db->conn->fetchAssoc($sql);

        if ($retval === false) {
            return array();
        }

        return $retval;
    }


    /**
     * Gets the item rating information for the current item/id.
     *
     * @param   string  $type     plugin name
     * @param   string  $item_id  item id
     * @return  object  $this
     */
    public function getItemRating() : self
    {
        global $_TABLES;

        $db = Database::getInstance();
        $sql = "SELECT * FROM {$_TABLES['rating']}
            WHERE type = ? AND item_id = ?";
        $row = $db->conn->fetchAssoc(
            $sql,
            array($this->item_type, $this->item_id),
            array(Database::STRING, Database::STRING)
        );

        if (is_array($row)) {
            $this->withRatingID($row['id'])
                ->withTotalVotes($row['votes'])
                ->withRating($row['rating']);
        }
        return $this;
    }


    /**
     * Check if user has already rated for an item.
     * Determines if the current user or IP has already rated the item.
     *
     * @return   boolean     True if user or ip has already rated, False if not
     */
    public function userHasVoted() : bool
    {
        global $_TABLES, $_USER;

        $voted = false;
        $db = Database::getInstance();

        $uid = (int)$_USER['uid'];
        $ip = $_SERVER['REAL_ADDR'];
        if ($uid < 2) {
            $sql = "SELECT id FROM {$_TABLES['rating_votes']}
                WHERE ip_address = ?
                AND item_id = ?";
            $row = $db->conn->fetchAssoc(
                $sql,
                array($ip , $this->item_id),
                array(Database::STRING, Database::STRING)
            );
        } else {
            $sql = "SELECT id FROM {$_TABLES['rating_votes']}
                WHERE (uid=? OR (uid=1 AND ip_address=?))
                AND item_id=?";
            $row = $db->conn->fetchAssoc(
                $sql,
                array($this->uid, $ip, $this->item_id),
                array(Database::INTEGER, Database::STRING, Database::STRING)
            );
        }
        if (is_array($row) && count($row) > 0) {
            $voted = true;
        }
        return $voted;
    }


    /**
     * Removes all rating data for an item.
     *
     * @param   string  $type       Plugin name (item type)
     * @param   string  $item_id    Item ID
     * @return  void
     */
    public static function reset(string $type, string $item_id) : void
    {
        global $_TABLES;

        $db->conn->delete(
            $_TABLES['rating'],
            array('type' => $type, 'item_id' => $item_id),
            array(Database::STRING, Database::STRING)
        );
        $db->conn->delete(
            $_TABLES['rating_votes'],
            array('type' => $type, 'item_id' => $item_id),
            array(Database::STRING, Database::STRING)
        );
        PLG_itemRated($type, $item_id, 0, 0);
    }


    /**
     * Deletes a specific vote and recalculates the new rating.
     *
     * @param   integer $voteID     The ID of the rating_votes record
     * @return  boolean     True if successful otherwise False
     */
    public static function deleteVote(int $voteID) : bool
    {
        global $_TABLES;

        $voteID = (int)$voteID;
        $db = Database::getInstance();

        // First delete the vote record.a
        $row = $db->conn->fetchAssoc(
            "SELECT * FROM {$_TABLES['rating_votes']} WHERE id = ?",
            array($voteID),
            array(Database::INTEGER)
        );
        if (is_array($row) && count($row) == 1) {
            $item_id = $row['item_id'];
            $type = $row['type'];
        } else {
            return false;
        }
        $db->conn->delete(
            $_TABLES['rating_votes'],
            array('id' => $voteID),
            array(DATABASE::INTEGER)
        );

        // Then recalculate the total votes and total rating without the
        // deleted vote.
        $Rating = self::create($type, $item_id);
        $sql = "SELECT SUM(rating) as total_rating, COUNT(item_id) as total_votes
            FROM  {$_TABLES['rating_votes']}
            WHERE item_id = ? AND type = ?";
        list($total_rating,$total_votes) = $db->conn->fetchAssoc(
            $sql,
            array($item_id, $type),
            array(Database::STRING, Database::STRING)
        );
        $total_votes = (int)$votes['total_votes'];
        $total_rating = (int)$votes['total_votes'];
        if ($total_votes > 0) {
            // If this was not the only vote, recalculate
            $new_rating = $total_rating / $total_votes;
        } else {
            // The only vote was deleted, set the rating to zero.
            $new_rating = 0;
        }
        $new_rating = number_format($new_rating, 2);

        // Update the rating table with the new values.
        $sql = "UPDATE {$_TABLES['rating']} SET
            votes = ?
            rating = {$new_rating}
            WHERE type = ? AND item_id = ?";
        $db->conn->executeQuery(
            $sql,
            array($total_votes, $type, $item_id),
            array(Database::INTEGER, Database::STRING, Database::STRING)
        );
        PLG_itemRated($type, $item_id, $new_rating, $total_votes);
        return true;
    }


    /**
     * Add a new rating vote for an item.
     * This will calculate the new overall rating, update the vote table
     * with the user / ip info and ask the plugin to update its records.
     *
     * @param   integer $rating     Rating sent by user
     * @return  void
     */
    public function addVote(int $rating) : void
    {
        global $_TABLES;

        if ($rating < 1) {
            return;
        }

        $db = Database::getInstance();
        $ratingdate = time();
        $total_rating = 0;
        $total_votes = 0;

        $sql = "SELECT SUM(rating) AS total_rating, COUNT(item_id) AS total_votes
            FROM {$_TABLES['rating_votes']}
            WHERE item_id = ?
            AND type = ?";

        $vals = $db->conn->fetchAssoc(
            $sql,
            array($this->item_id, $this->item_type),
            array(Database::STRING, Database::STRING)
        );
        if (is_array($vals)) {
            $total_rating = isset($vals['total_rating']) ? (int)$vals['total_rating'] : 0;
            $total_votes = isset($vals['total_votes']) ? (int)$vals['total_votes'] : 0;
        }
        $total_rating += (int)$rating;
        $total_votes++;
        if ($total_rating > 0 && $total_votes > 0) {
            $new_rating = $total_rating / $total_votes;
        } else {
            $new_rating = 0;
        }

        $new_rating = number_format($new_rating, 2);
        $this->withRating($new_rating);
        $sql_rating = $this->getRating(true);   // get format for sql update
        $this->withTotalVotes($total_votes);

        if ($this->getRatingID() != 0) {
            // Item has already been rated, just update.
            $sql = "UPDATE {$_TABLES['rating']} SET
                votes = ?,
                rating = {$sql_rating}
                WHERE id = ?";
            $db->conn->executeQuery(
                $sql,
                array($this->getTotalVotes(), $this->getRatingID()),
                array(Database::INTEGER, Database::INTEGER)
            );
        } else {
            // First rating, create a new record.
            $sql = "INSERT INTO {$_TABLES['rating']} SET
                type = ?,
                item_id = ?,
                votes = ?,
                rating = {$sql_rating}";
            $db->conn->executeQuery(
                $sql,
                array(
                    $this->getItemType(),
                    $this->getItemID(),
                    $this->getTotalVotes(),
                ),
                array(
                    Database::STRING,
                    Database::STRING,
                    Database::INTEGER,
                )
            );
        }

        // Now save the user's vote.
        $sql = "INSERT INTO {$_TABLES['rating_votes']} SET
            type = ?,
            item_id = ?,
            rating = {$rating},
            uid = ?,
            ip_address = ?,
            ratingdate = ?";
        $db->conn->executeQuery(
            $sql,
            array(
                $this->getItemType(),
                $this->getItemID(),
                $this->getUid(),
                $this->getIPAddress(),
                $ratingdate,
            ),
            array(
                Database::STRING,
                Database::STRING,
                Database::INTEGER,
                Database::STRING,
                Database::STRING,
            )
        );

        // Notify plugins of the new rating.
        PLG_itemRated(
            $this->getItemType(),
            $this->getItemID(),
            $this->getRating(),
            $this->getTotalVotes()
        );
    }


    /**
     * Retrieve an array of item_id's the current user has rated.
     * This function will return an array of all the items the user
     * has rated for the specific type.
     *
     * @param   string  $type     Plugin name
     * @return  array       Array of item ids
     */
    public static function getRatedIds(string $type) : array
    {
        global $_TABLES, $_USER;

        $ip     = $_SERVER['REAL_ADDR'];
        $uid    = isset($_USER['uid']) ? (int)$_USER['uid'] : 1;
        $db = Database::getInstance();
        if ($uid == 1) {
            $sql = "SELECT item_id FROM {$_TABLES['rating_votes']}
                WHERE type = ? AND ip_address = ?";
            $ratedIds = $db->conn->fetchAssoc(
                $sql,
                array($type, $ip),
                array(Database::STRING, Database::STRING)
            );
        } else {
            $sql = "SELECT item_id FROM {$_TABLES['rating_votes']}
                WHERE type = ?
                AND (uid = ? OR ip_address = ?)";
            $ratedIds = $db->conn->fetchAssoc(
                $sql,
                array($type, $uid, $ip),
                array(Database::STRING, Database::INTEGER, Database::STRING)
            );
        }
        if (!is_array($ratedIds)) {
            $ratedIds = array();
        }
        return $ratedIds;
    }

}
