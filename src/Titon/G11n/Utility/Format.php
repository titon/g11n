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
 * Enhance the parent Format class by providing localized formatting rule support.
 */
class Format extends \Titon\Utility\Format {

	/**
	 * Format a date string.
	 *
	 * @param string|int $time
	 * @param string $format
	 * @return string
	 * @static
	 */
	public static function date($time, $format = '%Y-%m-%d') {
		return parent::date($time, self::get('date', $format));
	}

	/**
	 * Format a datetime string.
	 *
	 * @param string|int $time
	 * @param string $format
	 * @return string
	 * @static
	 */
	public static function datetime($time, $format = '%Y-%m-%d %H:%M:%S') {
		return parent::datetime($time, self::get('datetime', $format));
	}

	/**
	 * Get a formatting rule from G11n, else use the fallback.
	 *
	 * @param string $key
	 * @param string $fallback
	 * @return string
	 * @throws \Titon\G11n\Exception
	 * @static
	 */
	public static function get($key, $fallback = null) {
		$pattern = Registry::factory('Titon\G11n\G11n')->current()->getFormatPatterns($key) ?: $fallback;

		if (!$pattern) {
			throw new Exception(sprintf('Format pattern %s does not exist', $key));
		}

		return $pattern;
	}

	/**
	 * Format a phone number. A phone number can support multiple variations,
	 * depending on how many numbers are present.
	 *
	 * @param int $value
	 * @param string $format
	 * @return string
	 * @static
	 */
	public static function phone($value, $format = null) {
		return parent::phone($value, self::get('phone', $format));
	}

	/**
	 * Format a social security number.
	 *
	 * @param string|int $value
	 * @param string $format
	 * @return string
	 * @static
	 */
	public static function ssn($value, $format = null) {
		return parent::ssn($value, self::get('ssn', $format));
	}

	/**
	 * Format a time string.
	 *
	 * @param string|int $time
	 * @param string $format
	 * @return string
	 * @static
	 */
	public static function time($time, $format = '%H:%M:%S') {
		return parent::time($time, self::get('time', $format));
	}

}