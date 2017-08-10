<?php

/**
 * Class EmailAddressValidator
 *
 * @link https://github.com/aziraphale/email-address-validator
 * @link http://code.google.com/p/php-email-address-validation/
 * @license New BSD license http://www.opensource.org/licenses/bsd-license.php
 * @example if (EmailAddressValidator::checkEmailAddress('test@example.org')) {
 * @example     // Email address is technically valid
 * @example }
 */
class EmailAddressValidator
{
    /**
     * Check email address validity
     * @param string $emailAddress Email address to be checked
     * @return bool Whether email is valid
     */
    public static function checkEmailAddress($emailAddress)
    {
        // If magic quotes is "on", email addresses with quote marks will
        // fail validation because of added escape characters. Uncommenting
        // the next three lines will allow for this issue.
        //if (get_magic_quotes_gpc()) {
        //    $emailAddress = stripslashes($emailAddress);
        //}

        // Control characters are not allowed
        if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $emailAddress)) {
            return false;
        }

        // Check email length - min 3 (a@a), max 256
        if (!self::checkTextLength($emailAddress, 3, 256)) {
            return false;
        }

        // Split it into sections using last instance of "@"
        $atSymbol = strrpos($emailAddress, '@');
        if ($atSymbol === false) {
            // No "@" symbol in email.
            return false;
        }
        $emailAddressParts[0] = substr($emailAddress, 0, $atSymbol);
        $emailAddressParts[1] = substr($emailAddress, $atSymbol + 1);

        // Count the "@" symbols. Only one is allowed, except where
        // contained in quote marks in the local part. Quickest way to
        // check this is to remove anything in quotes. We also remove
        // characters escaped with backslash, and the backslash
        // character.
        $tempAddressParts[0] = preg_replace('/\./', '', $emailAddressParts[0]);
        $tempAddressParts[0] = preg_replace('/"[^"]+"/', '', $tempAddressParts[0]);
        $tempAddressParts[1] = $emailAddressParts[1];
        $tempAddress = $tempAddressParts[0] . $tempAddressParts[1];
        // Then check - should be no "@" symbols.
        if (strrpos($tempAddress, '@') !== false) {
            // "@" symbol found
            return false;
        }

        // Check local portion
        if (!self::checkLocalPortion($emailAddressParts[0])) {
            return false;
        }

        // Check domain portion
        if (!self::checkDomainPortion($emailAddressParts[1])) {
            return false;
        }

        // If we're still here, all checks above passed. Email is valid.
        return true;
    }

    /**
     * Checks email section before "@" symbol for validity
     * @param string $localPortion Text to be checked
     * @return bool Whether local portion is valid
     */
    public static function checkLocalPortion($localPortion)
    {
        // Local portion can only be from 1 to 64 characters, inclusive.
        // Please note that servers are encouraged to accept longer local
        // parts than 64 characters.
        if (!self::checkTextLength($localPortion, 1, 64)) {
            return false;
        }
        // Local portion must be:
        // 1) a dot-atom (strings separated by periods)
        // 2) a quoted string
        // 3) an obsolete format string (combination of the above)
        $localPortionParts = explode('.', $localPortion);
        for ($i = 0, $max = sizeof($localPortionParts); $i < $max; $i++) {
             if (!preg_match('.^('
                            .    '([A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]'
                            .    '[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]{0,63})'
                            .'|'
                            .    '("[^\\\"]{0,62}")'
                            .')$.'
                            ,$localPortionParts[$i])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks email section after "@" symbol for validity
     * @param string $domainPortion Text to be checked
     * @return bool Whether domain portion is valid
     */
    public static function checkDomainPortion($domainPortion)
    {
        // Total domain can only be from 1 to 255 characters, inclusive
        if (!self::checkTextLength($domainPortion, 1, 255)) {
            return false;
        }
        // Check if domain is IP, possibly enclosed in square brackets.
        if (preg_match('/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
           .'(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}$/',
                $domainPortion) ||
            preg_match('/^\[(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
           .'(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}\]$/',
                $domainPortion)) {
            return true;
        } else {
            $domainPortionParts = explode('.', $domainPortion);
            if (sizeof($domainPortionParts) < 2) {
                return false; // Not enough parts to domain
            }
            for ($i = 0, $max = sizeof($domainPortionParts); $i < $max; $i++) {
                // Each portion must be between 1 and 63 characters, inclusive
                if (!self::checkTextLength($domainPortionParts[$i], 1, 63)) {
                    return false;
                }
                if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|'
                   .'([A-Za-z0-9]+))$/', $domainPortionParts[$i])) {
                    return false;
                }
                if ($i == $max - 1) { // TLD cannot be only numbers
                    if (strlen(preg_replace('/[0-9]/', '', $domainPortionParts[$i])) <= 0) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Check given text length is between defined bounds
     * @param string $text Text to be checked
     * @param int $minimum Minimum acceptable length
     * @param int $maximum Maximum acceptable length
     * @return bool Whether string is within bounds (inclusive)
     */
    protected static function checkTextLength($text, $minimum, $maximum)
    {
        // Minimum and maximum are both inclusive
        $textLength = strlen($text);
        return ($textLength >= $minimum && $textLength <= $maximum);
    }
}
