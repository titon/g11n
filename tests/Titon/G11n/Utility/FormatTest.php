<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Utility;

use Titon\G11n\G11n;
use Titon\G11n\Locale;
use Titon\G11n\Utility\Format;
use \Exception;

/**
 * Test class for Titon\G11n\Utility\Format.
 */
class FormatTest extends \PHPUnit_Framework_TestCase {

	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		G11n::addLocale(new Locale('en'));
		G11n::useLocale('en');
	}

	/**
	 * Test that get() returns a formatting rule.
	 */
	public function testGet() {
		$this->assertEquals('%m/%d/%Y', Format::get('date'));
		$this->assertEquals('%m/%d/%Y %I:%M%p', Format::get('datetime'));
		$this->assertEquals('%I:%M%p', Format::get('time'));
		$this->assertEquals('###-##-####', Format::get('ssn'));
		$this->assertEquals([
			7 => '###-####',
			10 => '(###) ###-####',
			11 => '# (###) ###-####'
		], Format::get('phone'));

		try {
			Format::get('fake');
			$this->assertTrue(false);

		} catch (Exception $e) {
			$this->assertTrue(true);
		}
	}

	/**
	 * Test that date() formats a timestamp to a date.
	 */
	public function testDate() {
		$time = mktime(16, 35, 0, 2, 26, 1988);

		$this->assertEquals('02/26/1988', Format::date($time));
	}

	/**
	 * Test that datetime() formats a timestamp to a date and time.
	 */
	public function testDatetime() {
		$time = mktime(16, 35, 0, 2, 26, 1988);

		$this->assertEquals('02/26/1988 04:35PM', Format::datetime($time));
	}

	/**
	 * Test that phone() formats a number to a phone number.
	 */
	public function testPhone() {
		$this->assertEquals('666-1337', Format::phone(6661337));
		$this->assertEquals('(888) 666-1337', Format::phone('8886661337'));
		$this->assertEquals('1 (888) 666-1337', Format::phone('+1 8886661337'));
	}

	/**
	 * Test that ssn() formats a number to a social security number.
	 */
	public function testSsn() {
		$this->assertEquals('998-29-3841', Format::ssn('998293841'));
	}

	/**
	 * Test that time() formats a timestamp to time.
	 */
	public function testTime() {
		$time = mktime(16, 35, 0, 2, 26, 1988);

		$this->assertEquals('04:35PM', Format::time($time));
	}

}