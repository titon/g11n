<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Utility;

use Titon\G11n\G11n;

/**
 * Enhance the parent Number class by providing localized currency and number rule support.
 */
class Number extends \Titon\Utility\Number {

	/**
	 * Convert a number to it's currency equivalent, respecting locale.
	 * Allow for overrides through an options array.
	 *
	 * @access public
	 * @param int $number
	 * @param array $options
	 * @return string
	 * @static
	 */
	public static function currency($number, array $options = []) {
		if (G11n::isEnabled()) {
			$options = array_merge(
				G11n::current()->getFormats('number'),
				G11n::current()->getFormats('currency'),
				$options
			);
		}

		return parent::currency($number, $options);
	}

	/**
	 * Convert a number to a percentage string with decimal and comma separations.
	 *
	 * @access public
	 * @param int $number
	 * @param int|array $options
	 * @return string
	 * @static
	 */
	public static function percentage($number, $options = []) {
		if (is_numeric($options)) {
			$options = ['places' => $options];
		}

		if (G11n::isEnabled()) {
			$options = array_merge(
				G11n::current()->getFormats('number'),
				$options
			);
		}

		return parent::percentage($number, $options);
	}

}