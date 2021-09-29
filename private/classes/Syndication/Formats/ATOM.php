<?php
/**
 * Class to handle creating Atom feeds.
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     glfusion
 * @version     0.0.1
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace glFusion\Syndication\Formats;

/**
 * Atom Feed class.
 * Uses all the same functions and formats as XML.
 * @package glfusion
 */
class ATOM extends XML
{
    public static $versions = array('0.3', '1.0');
}
