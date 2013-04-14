<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Utility;

use Titon\Common\Registry;
use Titon\G11n\G11n;
use Titon\G11n\Exception;

/**
 * Enhance the parent Validate class by providing localized validation rule support.
 */
class Validate extends \Titon\Utility\Validate {

	/**
	 * Validate input matches a currency format.
	 *
	 * @param string $input
	 * @param string $format
	 * @return boolean
	 * @static
	 */
	public static function currency($input, $format = null) {
		return parent::currency($input, self::get('currency', $format));
	}

	/**
	 * Get a validation rule from G11n, else use the fallback.
	 *
	 * @param string $key
	 * @param string $fallback
	 * @return string
	 * @throws \Titon\G11n\Exception
	 * @static
	 */
	public static function get($key, $fallback = null) {
		$pattern = Registry::factory('Titon\G11n\G11n')->current()->getValidationRules($key) ?: $fallback;

		if (!$pattern) {
			throw new Exception(sprintf('Validation rule %s does not exist', $key));
		}

		return $pattern;
	}

	/**
	 * Validate input matches a phone number format.
	 *
	 * @param string $input
	 * @param string $format
	 * @return boolean
	 * @static
	 */
	public static function phone($input, $format = null) {
		return parent::phone($input, self::get('phone', $format));
	}

	/**
	 * Validate input matches a postal/zip code format.
	 *
	 * @param string $input
	 * @param string $format
	 * @return boolean
	 * @static
	 */
	public static function postalCode($input, $format = null) {
		return parent::postalCode($input, self::get('postalCode', $format));
	}

	/**
	 * Validate input matches a social security number (SSN) format.
	 *
	 * @param string $input
	 * @param string $format
	 * @return boolean
	 * @static
	 */
	public static function ssn($input, $format = null) {
		return parent::ssn($input, self::get('ssn', $format));
	}

}