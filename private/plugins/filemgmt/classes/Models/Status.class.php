<?php
/**
 * Class to describe file statuses
 *
 * @author      Lee Garner <lee@leegarner.com>
 * @copyright   Copyright (c) 2021 Lee Garner <lee@leegarner.com>
 * @package     filemgmt
 * @version     v1.9.0
 * @since       v1.9.0
 * @license     http://opensource.org/licenses/gpl-2.0.php
 *              GNU Public License v2 or later
 * @filesource
 */
namespace Filemgmt\Models;

/**
 * Class to describe event statuses.
 * @package evlist
 */
class Status
{
    const SUBMISSION= 0;
    const APPROVED = 1;

    const OK = 0;
    const UPL_OK = 0;
    const UPL_DUPFILE = 1;
    const UPL_DUPSNAP = 2;
    const UPL_ERROR = 3;
    const UPL_NODEMO = 4;
}
