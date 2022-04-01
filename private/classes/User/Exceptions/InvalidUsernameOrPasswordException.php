<?php

/*
 * Extension tot he PHP-Auth library for glFusion
 *
 * PHP-Auth (https://github.com/delight-im/PHP-Auth)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace glFusion\User\Exceptions;

use \glFusion\User\UserInterface;

class InvalidUsernameOrPasswordException extends AuthException {

    public function __contruct() {
        global $MESSAGE;

        COM_setMsg($MESSAGE[81],'error');
        // call UI login form and be done.
        UserInterface::loginPage();
    }
}