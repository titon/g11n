<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Utility;

use Titon\G11n\G11n;
use Titon\G11n\Locale;
use Titon\G11n\Utility\Number;

/**
 * Test class for Titon\G11n\Utility\Number.
 */
class NumberTest extends \PHPUnit_Framework_TestCase {

	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		G11n::addLocale(new Locale('en'));
		G11n::useLocale('en');
	}

	/**
	 * Test that currency() renders the money formats correctly.
	 */
	public function testCurrency() {
		$this->assertEquals('$12,345.34', Number::currency(12345.34));
		$this->assertEquals('$734.00', Number::currency(734));
		$this->assertEquals('$84,283.38', Number::currency(84283.384));
		$this->assertEquals('($3,483.23)', Number::currency(-3483.23));

		// cents
		$this->assertEquals('0.33&cent;', Number::currency(.33));
		$this->assertEquals('0.75&cent;', Number::currency(0.75));
		$this->assertEquals('(0.75&cent;)', Number::currency(-0.75));

		// options
		$this->assertEquals('USD 85 839,34', Number::currency(85839.34, [
			'use' => 'code',
			'thousands' => ' ',
			'decimals' => ','
		]));

		// formats
		$this->assertEquals('-$0.34', Number::currency(-0.34, [
			'negative' => '-#',
			'cents' => false
		]));
	}

	/**
	 * Test that percentage() returns a number formatted string with a % sign.
	 */
	public function testPercentage() {
		$this->assertEquals('123%', Number::percentage(123, ['places' => 0]));
		$this->assertEquals('4,546%', Number::percentage(4546, ['places' => 0]));
		$this->assertEquals('92,378,453%', Number::percentage(92378453, ['places' => 0]));
		$this->assertEquals('287,349,238,432%', Number::percentage('287349238432', ['places' => 0]));
		$this->assertEquals('3,843.45%', Number::percentage(3843.4450));
		$this->assertEquals('93,789.34%', Number::percentage(93789.34));

		// options
		$this->assertEquals('92 378 453,94%', Number::percentage(92378453.9438, [
			'thousands' => ' ',
			'decimals' => ',',
			'places' => 2
		]));
	}

}