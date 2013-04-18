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
	 * {@inheritdoc}
	 */
	public static function date($time, $format = '%Y-%m-%d') {
		return parent::date($time, self::get('date', $format));
	}

	/**
	 * {@inheritdoc}
	 */
	public static function datetime($time, $format = '%Y-%m-%d %H:%M:%S') {
		return parent::datetime($time, self::get('datetime', $format));
	}

	/**
	 * {@inheritdoc}
	 * @throws \Titon\G11n\Exception
	 */
	public static function get($key, $fallback = null) {
		$pattern = Registry::factory('Titon\G11n\G11n')->current()->getFormatPatterns($key) ?: $fallback;

		if (!$pattern) {
			throw new Exception(sprintf('Format pattern %s does not exist', $key));
		}

		return $pattern;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function phone($value, $format = null) {
		return parent::phone($value, self::get('phone', $format));
	}

	/**
	 * {@inheritdoc}
	 */
	public static function relativeTime($time, array $options = array()) {
		$g11n = Registry::factory('Titon\G11n\G11n');
		$msg = function($key) use ($g11n) {
			return $g11n->translate('common.format.relativeTime.' . $key);
		};

		// TODO Find a more optimized way to do this.
		$options = $options + array(
			'seconds' => array($msg('sec'), $msg('second'), $msg('seconds')),
			'minutes' => array($msg('min'), $msg('minute'), $msg('minutes')),
			'hours' => array($msg('hr'), $msg('hour'), $msg('hours')),
			'days' => array($msg('dy'), $msg('day'), $msg('days')),
			'weeks' => array($msg('wk'), $msg('week'), $msg('weeks')),
			'months' => array($msg('mon'), $msg('month'), $msg('months')),
			'years' => array($msg('yr'), $msg('year'), $msg('years')),
			'now' => $msg('now'),
			'in' => $msg('in'),
			'ago' => $msg('ago')
		);

		return parent::relativeTime($time, $options);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function ssn($value, $format = null) {
		return parent::ssn($value, self::get('ssn', $format));
	}

	/**
	 * {@inheritdoc}
	 */
	public static function time($time, $format = '%H:%M:%S') {
		return parent::time($time, self::get('time', $format));
	}

}