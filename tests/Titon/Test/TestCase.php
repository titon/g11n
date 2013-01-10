<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Test;

/**
 * TestCase with more assert methods.
 */
class TestCase extends \PHPUnit_Framework_TestCase {

	/**
	 * Assert that two array values are equal, disregarding the order.
	 *
	 * @param array $expected
	 * @param array $actual
	 * @return void
	 */
	public function assertArraysEqual(array $expected, array $actual) {
		ksort($actual);
		ksort($expected);

		$this->assertEquals($expected, $actual);
	}

}