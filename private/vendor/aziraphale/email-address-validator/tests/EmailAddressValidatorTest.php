<?php

/**
 * Class EmailAddressValidatorTest
 */
class EmailAddressValidatorTest extends PHPUnit_Framework_TestCase
{

    /**
     * DataProvider for the dfault tests
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array('test@example.com', true),
            array('TEST@example.com', true),
            array('1234567890@example.com', true),
            array('test+test@example.com', true),
            array('test-test@example.com', true),
            array('t*est@example.com', true),
            array('+1~1+@example.com', true),
            array('{_test_}@example.com', true),
            array('"[[ test ]]"@example.com', true),
            array('test.test@example.com', true),
            array('test."test"@example.com', true),
            array('"test@test"@example.com', true),
            array('test@123.123.123.123', true),
            array('test@[123.123.123.123]', true),
            array('test@example.example.com', true),
            array('test@example.example.example.com', true),

            array('test.example.com', false),
            array('test.@example.com', false),
            array('test..test@example.com', false),
            array('.test@example.com', false),
            array('test@test@example.com', false),
            array('test@@example.com', false),
            array('-- test --@example.com', false), // No spaces allowed in local part
            array('[test]@example.com', false), // Square brackets only allowed within quotes
            array('"test\test"@example.com', false), // Quotes cannot contain backslash
            array('"test"test"@example.com', false), // Quotes cannot be nested
            array('()[]\;:,<>@example.com', false), // Disallowed Characters
            array('test@.', false),
            array('test@example.', false),
            array('test@.org', false),
            array('12345678901234567890123456789012345678901234567890123456789012345@example.com', false), // 64 characters is maximum length for local part. This is 65.
            array('test@123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012.com', false), // 255 characters is maximum length for domain. This is 256.
            array('test@[123.123.123.123', false),
            array('test@123.123.123.123]', false),

            array('bugs@php.net', true),
            array('~someone@somewhere.com', true),
            array('no+body.here@somewhere.com.au', true),
            array('username+tag@domain.com', true), // FS#1447
            array("rfc2822+allthesechars_#*!'`/-={}are.legal@somewhere.com.au", true),
            array('_foo@test.com', true), // FS#1049
            array('bugs@php.net1', true), // new ICAN rules seem to allow this
            array('.bugs@php.net1', false),
            array('bu..gs@php.net', false),
            array('bugs@php..net', false),
            array('bugs@.php.net', false),
            array('bugs@php.net.', false),
            array('bu(g)s@php.net1', false),
            array('bu[g]s@php.net1', false),
            array('somebody@somewhere.museum', true),
            array('somebody@somewhere.travel', true),

            // https://code.google.com/archive/p/php-email-address-validation/issues/12
            array('root@[2010:fb:fdac::311:2101]', true),
        );
    }

    /**
     * @dataProvider provider
     * @param string $email
     * @param bool $isvalid
     */
    public function testMails($email, $isvalid)
    {
        $result = EmailAddressValidator::checkEmailAddress($email);
        $this->assertSame($isvalid, $result);
    }

    /**
     * Local domain emails should be allowed when the appropriate parameter is set
     *
     * @link https://code.google.com/archive/p/php-email-address-validation/issues/15
     */
    public function testLocal() {
        $this->assertFalse(EmailAddressValidator::checkEmailAddress('test@example'));
        $this->assertTrue(EmailAddressValidator::checkEmailAddress('test@example', true));
    }
}
