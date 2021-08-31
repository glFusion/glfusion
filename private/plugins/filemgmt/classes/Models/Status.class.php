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
    const SUBMISSION= 0;    // file is awaiting submission
    const APPROVED = 1;     // file is available for downloads

    const OK = 0;           // generic OK status
    const UPL_OK = 0;       // upload OK
    const UPL_DUPFILE = 1;  // duplicate filename
    const UPL_DUPSNAP = 2;  // duplicate snapshot image
    const UPL_ERROR = 3;    // general upload error
    const UPL_NODEMO = 4;   // uploads disabled in demo mode
    const UPL_PENDING = 5;  // success, pending approval
    const UPL_MISSING = 6;  // missing fields
    const UPL_UPDATED = 7;  // file updated, no new file uploaded
}
