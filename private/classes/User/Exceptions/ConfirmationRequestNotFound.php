<?php

/*
 * PHP-Auth (https://github.com/delight-im/PHP-Auth)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace glFusion\User\Exceptions;

if (!defined ('GVERSION')) {
    die ('This file can not be used on its own.');
}

class ConfirmationRequestNotFound extends AuthException {}
