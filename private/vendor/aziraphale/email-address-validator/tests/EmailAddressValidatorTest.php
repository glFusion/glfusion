<?php

    // Include PHPUnit
    require_once('PHPUnit/Framework.php');

    // Include the email address validator class
    require_once('../EmailAddressValidator.php');
     
    class EmailAddressValidatorTest extends PHPUnit_Framework_TestCase {

        protected $objValidator = null;

        public function setUp() {
            $this->objValidator = new EmailAddressValidator;
        }

        public function tearDown() {
            unset($this->objValidator);
        }

        /* Valid Addresses
        -------------------- */

        public function testValidAddress_Standard() {
            $this->assertEquals(true, $this->objValidator->check_email_address('test@example.com'));
        }

        public function testValidAddress_UpperCaseLocalPart() {
            $this->assertEquals(true, $this->objValidator->check_email_address('TEST@example.com'));
        }

        public function testValidAddress_NumericLocalPart() {
            $this->assertEquals(true, $this->objValidator->check_email_address('1234567890@example.com'));
        }

        public function testValidAddress_TaggedLocalPart() {
            $this->assertEquals(true, $this->objValidator->check_email_address('test+test@example.com'));
        }

        public function testValidAddress_QmailLocalPart() {
            $this->assertEquals(true, $this->objValidator->check_email_address('test-test@example.com'));
        }

        public function testValidAddress_UnusualCharactersInLocalPart() {
            $this->assertEquals(true, $this->objValidator->check_email_address('t*est@example.com'));
            $this->assertEquals(true, $this->objValidator->check_email_address('+1~1+@example.com'));
            $this->assertEquals(true, $this->objValidator->check_email_address('{_test_}@example.com'));
            $this->assertEquals(true, $this->objValidator->check_email_address('test\"test@example.com'));
            $this->assertEquals(true, $this->objValidator->check_email_address('test\@test@example.com'));
            $this->assertEquals(true, $this->objValidator->check_email_address('test\test@example.com'));
            $this->assertEquals(true, $this->objValidator->check_email_address('"test\test"@example.com'));
            $this->assertEquals(true, $this->objValidator->check_email_address('"test.test"@example.com'));
        }

        public function testValidAddress_QuotedLocalPart() {
            $this->assertEquals(true, $this->objValidator->check_email_address('"[[ test ]]"@example.com'));
        }

        public function testValidAddress_AtomisedLocalPart() {
            $this->assertEquals(true, $this->objValidator->check_email_address('test.test@example.com'));
        }

        public function testValidAddress_ObsoleteLocalPart() {
            $this->assertEquals(true, $this->objValidator->check_email_address('test."test"@example.com'));
        }

        public function testValidAddress_QuotedAtLocalPart() {
            $this->assertEquals(true, $this->objValidator->check_email_address('"test@test"@example.com'));
        }

        public function testValidAddress_IpDomain() {
            $this->assertEquals(true, $this->objValidator->check_email_address('test@123.123.123.123'));
        }

        public function testValidAddress_BracketIpDomain() {
            $this->assertEquals(true, $this->objValidator->check_email_address('test@[123.123.123.123]'));
        }

        public function testValidAddress_MultipleLabelDomain() {
            $this->assertEquals(true, $this->objValidator->check_email_address('test@example.example.com'));
            $this->assertEquals(true, $this->objValidator->check_email_address('test@example.example.example.com'));
        }

        /* Invalid Addresses
        -------------------- */

        public function testInvalidAddress_TooLong() {
            $this->assertEquals(false, $this->objValidator->check_email_address('12345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345@example.com'));
        }

        public function testInvalidAddress_TooShort() {
            $this->assertEquals(false, $this->objValidator->check_email_address('@a'));
        }

        public function testInvalidAddress_NoAtSymbol() {
            $this->assertEquals(false, $this->objValidator->check_email_address('test.example.com'));
        }

        public function testInvalidAddress_BlankAtomInLocalPart() {
            $this->assertEquals(false, $this->objValidator->check_email_address('test.@example.com'));
            $this->assertEquals(false, $this->objValidator->check_email_address('test..test@example.com'));
            $this->assertEquals(false, $this->objValidator->check_email_address('.test@example.com'));
        }

        public function testInvalidAddress_MultipleAtSymbols() {
            $this->assertEquals(false, $this->objValidator->check_email_address('test@test@example.com'));
            $this->assertEquals(false, $this->objValidator->check_email_address('test@@example.com'));
        }

        public function testInvalidAddress_InvalidCharactersInLocalPart() {
            $this->assertEquals(false, $this->objValidator->check_email_address('-- test --@example.com')); // No spaces allowed in local part
            $this->assertEquals(false, $this->objValidator->check_email_address('[test]@example.com')); // Square brackets only allowed within quotes
            $this->assertEquals(false, $this->objValidator->check_email_address('"test"test"@example.com')); // Quotes cannot be nested
            $this->assertEquals(false, $this->objValidator->check_email_address('()[]\;:,<>@example.com')); // Disallowed Characters
        }

        public function testInvalidAddress_DomainLabelTooShort() {
            $this->assertEquals(false, $this->objValidator->check_email_address('test@.'));
            $this->assertEquals(false, $this->objValidator->check_email_address('test@example.'));
            $this->assertEquals(false, $this->objValidator->check_email_address('test@.org'));
        }

        public function testInvalidAddress_LocalPartTooLong() {
            $this->assertEquals(false, $this->objValidator->check_email_address('12345678901234567890123456789012345678901234567890123456789012345@example.com')); // 64 characters is maximum length for local part. This is 65.
        }

        public function testInvalidAddress_DomainLabelTooLong() {
            $this->assertEquals(false, $this->objValidator->check_email_address('test@123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012.com')); // 255 characters is maximum length for domain. This is 256.
        }

        public function testInvalidAddress_TooFewLabelsInDomain() {
            $this->assertEquals(false, $this->objValidator->check_email_address('test@example'));
        }

        public function testInvalidAddress_UnpartneredSquareBracketIp() {
            $this->assertEquals(false, $this->objValidator->check_email_address('test@[123.123.123.123'));
            $this->assertEquals(false, $this->objValidator->check_email_address('test@123.123.123.123]'));
        }

    }

?>