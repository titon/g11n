<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Utility;

use Titon\Common\Registry;
use Titon\G11n\G11n;
use Titon\G11n\Locale;
use Titon\G11n\Translator\MessageTranslator;
use Titon\G11n\Utility\Format;
use Titon\Io\Reader\PhpReader;
use Titon\Test\TestCase;
use \Exception;

/**
 * Test class for Titon\G11n\Utility\Format.
 *
 * @property \Titon\G11n\G11n $object
 */
class FormatTest extends TestCase {

	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();

		$translator = new MessageTranslator();
		$translator->setReader(new PhpReader());

		$this->object = Registry::factory('Titon\G11n\G11n');
		$this->object->addLocale(new Locale('en'));
		$this->object->useLocale('en');
		$this->object->setTranslator($translator);
	}

	/**
	 * Reset cache.
	 */
	protected function tearDown() {
		parent::tearDown();

		Registry::flush();
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
	 * Test that relativeTime() returns a timestamp in seconds ago/in.
	 */
	public function testRelativeTime() {
		$time = mktime(16, 35, 0, 2, 26, 2012);

		// Current
		$this->assertEquals('just now', Format::relativeTime(strtotime('+0 seconds', $time), array('time' => $time)));
		$this->assertEquals('just now', Format::relativeTime($time, array('time' => $time)));

		// Past
		$this->assertEquals('45 seconds ago', Format::relativeTime(strtotime('-45 seconds', $time), array('time' => $time)));
		$this->assertEquals('2 weeks, 2 days ago', Format::relativeTime(strtotime('-16 days', $time), array('time' => $time)));
		$this->assertEquals('8 months ago', Format::relativeTime(strtotime('-33 weeks', $time), array('time' => $time)));
		$this->assertEquals('6 months, 4 days ago', Format::relativeTime(strtotime('-6 months', $time), array('time' => $time)));
		$this->assertEquals('2 years, 2 months ago', Format::relativeTime(strtotime('-799 days', $time), array('time' => $time)));

		// Future
		$this->assertEquals('in 45 seconds', Format::relativeTime(strtotime('+45 seconds', $time), array('time' => $time)));
		$this->assertEquals('in 2 weeks, 2 days', Format::relativeTime(strtotime('+16 days', $time), array('time' => $time)));
		$this->assertEquals('in 8 months', Format::relativeTime(strtotime('+33 weeks', $time), array('time' => $time)));
		$this->assertEquals('in 6 months, 2 days', Format::relativeTime(strtotime('+6 months', $time), array('time' => $time)));
		$this->assertEquals('in 2 years, 2 months', Format::relativeTime(strtotime('+799 days', $time), array('time' => $time)));

		// Large depth
		$this->assertEquals('1 year, 1 month, 4 days ago', Format::relativeTime(strtotime('-399 days', $time), array('time' => $time, 'depth' => 5)));
		$this->assertEquals('1 year, 3 months ago', Format::relativeTime(strtotime('-444 days', $time), array('time' => $time, 'depth' => 5)));
		$this->assertEquals('3 years ago', Format::relativeTime(strtotime('-999 days', $time), array('time' => $time, 'depth' => 5)));
		$this->assertEquals('3 years, 5 months ago', Format::relativeTime(strtotime('-1235 days', $time), array('time' => $time, 'depth' => 5)));
		$this->assertEquals('2 years, 2 months, 1 week, 2 days ago', Format::relativeTime(strtotime('-799 days', $time), array('time' => $time, 'depth' => 5)));

		// Non-verbose
		$this->assertEquals('2y, 2m, 1w, 2d ago', Format::relativeTime(strtotime('-799 days', $time), array('time' => $time, 'depth' => 5, 'verbose' => false)));
		$this->assertEquals('in 2y, 2m, 1w, 2d', Format::relativeTime(strtotime('+799 days', $time), array('time' => $time, 'depth' => 5, 'verbose' => false)));
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