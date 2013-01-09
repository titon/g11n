<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Utility;

use Titon\G11n\G11n;
use Titon\G11n\Locale;
use Titon\G11n\Utility\Validate;
use \Exception;

/**
 * Test class for Titon\G11n\Utility\Validate.
 */
class ValidateTest extends \PHPUnit_Framework_TestCase {

	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		G11n::addLocale(new Locale('en'));
		G11n::useLocale('en');
	}

	/**
	 * Test that get() returns a validation rule.
	 */
	public function testGet() {
		$this->assertEquals('/^\$[0-9,]+(?:\.[0-9]{2})?$/', Validate::get('currency'));
		$this->assertEquals('/^(?:\+?1)?\s?(?:\([0-9]{3}\))?\s?[0-9]{3}-[0-9]{4}$/', Validate::get('phone'));
		$this->assertEquals('/^[0-9]{5}(?:-[0-9]{4})?$/', Validate::get('postalCode'));
		$this->assertEquals('/^[0-9]{3}-[0-9]{2}-[0-9]{4}$/', Validate::get('ssn'));

		try {
			Validate::get('fake');
			$this->assertTrue(false);

		} catch (Exception $e) {
			$this->assertTrue(true);
		}
	}

	/**
	 * Test that currency() validates against the locales currency rule.
	 */
	public function testCurrency() {
		$this->assertTrue(Validate::currency('$1,000.00'));
		$this->assertTrue(Validate::currency('$343'));
		$this->assertTrue(Validate::currency('$193,482.33'));

		$this->assertFalse(Validate::currency('2,392.23'));
		$this->assertFalse(Validate::currency('2325'));
		$this->assertFalse(Validate::currency('$ten'));
		$this->assertFalse(Validate::currency('$1923.2'));
	}

	/**
	 * Test that phone() validates against the locales phone rule.
	 */
	public function testPhone() {
		$this->assertTrue(Validate::phone('666-1337'));
		$this->assertTrue(Validate::phone('(888)666-1337'));
		$this->assertTrue(Validate::phone('(888) 666-1337'));
		$this->assertTrue(Validate::phone('1 (888) 666-1337'));
		$this->assertTrue(Validate::phone('+1 (888) 666-1337'));

		$this->assertFalse(Validate::phone('666.1337'));
		$this->assertFalse(Validate::phone('888-666.1337'));
		$this->assertFalse(Validate::phone('1 888.666.1337'));
		$this->assertFalse(Validate::phone('666-ABMS'));
	}

	/**
	 * Test that postalCode() validates against the locales postal code rule.
	 */
	public function testPostalCode() {
		$this->assertTrue(Validate::postalCode('38842'));
		$this->assertTrue(Validate::postalCode('38842-0384'));

		$this->assertFalse(Validate::postalCode('3842'));
		$this->assertFalse(Validate::postalCode('38842.0384'));
		$this->assertFalse(Validate::postalCode('AksiS-0384'));
	}

	/**
	 * Test that ssn() validates against the locales ssn rule.
	 */
	public function testSsn() {
		$this->assertTrue(Validate::ssn('666-10-1337'));
		$this->assertTrue(Validate::ssn('384-29-3481'));

		$this->assertFalse(Validate::ssn('66-10-1337'));
		$this->assertFalse(Validate::ssn('384-29-341'));
		$this->assertFalse(Validate::ssn('666.10.1337'));
		$this->assertFalse(Validate::ssn('AHE-29-34P1'));
	}

}