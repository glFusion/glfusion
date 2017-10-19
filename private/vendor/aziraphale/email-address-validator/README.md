Email-Address-Validator
=======================

This is a fork of [AddedBytes' EmailAddressValidator class](https://code.google.com/p/php-email-address-validation/).

## Changes ##
Changes include:

- [Composer](https://getcomposer.org/) support
- Refactored the class to be purely static
- Opened up methods for checking the "local part" (the bit before the `@`) and the "domain part" (after the `@`) 
to be public methods
- Additional code style and docblock fixing to properly follow the [PHP-FIG PSR-1](http://www.php-fig.org/psr/psr-1/) 
and [PSR-2](http://www.php-fig.org/psr/psr-2/) documents

Note that this class is still **un-namespaced** - i.e. it's still declared in the global namespace. The `composer.json` 
file is still set up to correctly load it when required, so this shouldn't be a problem in practice - it's just perhaps
not best-practice.

## Usage ##
Due to the aforementioned changes, the way of using this class has completely changed. However it has such a small and simple interface that these changes shouldn't be problematic.

As a recap, the **old usage** was like this:
```php
$validator = new EmailAddressValidator;
if ($validator->check_email_address('test@example.org')) {
    // Email address is technically valid
}
```

The **new syntax** is as follows (ensure you have already included Composer's `autoload.php` file!):
```php
if (EmailAddressValidator::checkEmailAddress("test@example.org")) {
    // Email address is technically valid
}
```

with a couple of additional methods in case they're helpful:
```php
if (EmailAddressValidator::checkLocalPortion("test")) {
    // "test" is technically a valid string to have before the "@" in an email address
}
if (EmailAddressValidator::checkDomainPotion("example.org")) {
    // "example.org" is technically a valid email address host
}
```
