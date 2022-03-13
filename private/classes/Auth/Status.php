<?php

/*
 * PHP-Auth (https://github.com/delight-im/PHP-Auth)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace glFusion\Auth;

/*
define('USER_ACCOUNT_DISABLED', 0); // Account is banned/disabled
define('USER_ACCOUNT_AWAITING_ACTIVATION', 1); // Account awaiting user to login.
define('USER_ACCOUNT_AWAITING_APPROVAL', 2); // Account awaiting moderator approval
define('USER_ACCOUNT_ACTIVE', 3); // active account
define('USER_ACCOUNT_AWAITING_VERIFICATION', 4); // Account waiting for user to complete verification
*/

final class Status {

	const NORMAL 	= 3;	// USER_ACCOUNT_ACTIVE (3) or USER_ACCOUNT_AWAITING_ACTIVATION (1)
	const ARCHIVED 	= 5;	// not mapped
	const BANNED 	= 0;	// USER_ACCOUNT_DISABLED (0)
	const LOCKED 	= 6;	// not mapped - new
	const PENDING_REVIEW = 2;	// USER_ACCOUNT_AWAITING_APPROVAL (2)
	const SUSPENDED = 7;	// not mapped

}
