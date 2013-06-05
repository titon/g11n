<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Utility;

use Titon\Common\Registry;
use Titon\G11n\G11n;

/**
 * Enhance the parent Inflector class by providing localized inflection rule support.
 *
 * @package Titon\G11n\Utility
 */
class Inflector extends \Titon\Utility\Inflector {

	/**
	 * {@inheritdoc}
	 */
	public static function className($string) {
		return self::_cache(array(__METHOD__, $string), function() use ($string) {
			return Inflector::camelCase(Inflector::singularize($string));
		});
	}

	/**
	 * {@inheritdoc}
	 *
	 * @uses Titon\Common\Registry
	 */
	public static function ordinal($number) {
		$g11n = Registry::factory('Titon\G11n\G11n');

		if (!$g11n->isEnabled()) {
			return $number;
		}

		return self::_cache([__METHOD__, $number], function() use ($number, $g11n) {
			$inflections = $g11n->current()->getInflectionRules();
			$number = (int) $number;

			if (!$inflections || empty($inflections['ordinal'])) {
				return $number;
			}

			$ordinal = $inflections['ordinal'];

			// Teens 11-13
			if (in_array(($number % 100), range(11, 13)) && isset($ordinal['default'])) {
				return str_replace('#', $number, $ordinal['default']);
			}

			// First, second, third
			$modNumber = $number % 10;

			foreach ($ordinal as $i => $format) {
				if (is_numeric($i) && $modNumber === $i) {
					return str_replace('#', $number, $ordinal[$i]);
				}
			}

			// Fallback
			if (isset($ordinal['default'])) {
				return str_replace('#', $number, $ordinal['default']);
			}

			return $number;
		});
	}

	/**
	 * {@inheritdoc}
	 *
	 * @uses Titon\Common\Registry
	 */
	public static function pluralize($string) {
		$g11n = Registry::factory('Titon\G11n\G11n');

		if (!$g11n->isEnabled()) {
			return $string;
		}

		return self::_cache([__METHOD__, $string], function() use ($string, $g11n) {
			$string = mb_strtolower($string);
			$result = null;
			$inflections = $g11n->current()->getInflectionRules();

			if (!$inflections) {
				return $string;

			} else if (!empty($inflections['uninflected']) && in_array($string, $inflections['uninflected'])) {
				$result = $string;

			} else if (!empty($inflections['irregular']) && isset($inflections['irregular'][$string])) {
				$result = $inflections['irregular'][$string];

			} else if (!empty($inflections['irregular']) && in_array($string, $inflections['irregular'])) {
				$result = $string;

			} else if (!empty($inflections['plural'])) {
				foreach ($inflections['plural'] as $pattern => $replacement) {
					if (preg_match($pattern, $string)) {
						$result = preg_replace($pattern, $replacement, $string);
						break;
					}
				}
			}

			if (empty($result)) {
				$result = $string;
			}

			return $result;
		});
	}

	/**
	 * {@inheritdoc}
	 *
	 * @uses Titon\Common\Registry
	 */
	public static function singularize($string) {
		$g11n = Registry::factory('Titon\G11n\G11n');

		if (!$g11n->isEnabled()) {
			return $string;
		}

		return self::_cache([__METHOD__, $string], function() use ($string, $g11n) {
			$string = mb_strtolower($string);
			$result = null;
			$inflections = $g11n->current()->getInflectionRules();

			if (!$inflections) {
				return $string;

			} else if (!empty($inflections['uninflected']) && in_array($string, $inflections['uninflected'])) {
				$result = $string;

			} else if (!empty($inflections['irregular']) && in_array($string, $inflections['irregular'])) {
				$result = array_search($string, $inflections['irregular']);

			} else if (!empty($inflections['irregular']) && isset($inflections['irregular'][$string])) {
				$result = $string;

			} else if (!empty($inflections['singular'])) {
				foreach ($inflections['singular'] as $pattern => $replacement) {
					if (preg_match($pattern, $string)) {
						$result = preg_replace($pattern, $replacement, $string);
						break;
					}
				}
			}

			if (empty($result)) {
				$result = $string;
			}

			return $result;
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public static function tableName($string) {
		return self::_cache(array(__METHOD__, $string), function() use ($string) {
			return lcfirst(Inflector::camelCase(Inflector::pluralize($string)));
		});
	}

	/**
	 * {@inheritdoc}
	 *
	 * @uses Titon\Common\Registry
	 */
	public static function transliterate($string) {
		$g11n = Registry::factory('Titon\G11n\G11n');

		if (!$g11n->isEnabled()) {
			return $string;
		}

		return self::_cache([__METHOD__, $string], function() use ($string, $g11n) {
			$inflections = $g11n->current()->getInflectionRules();

			if (!$inflections || empty($inflections['transliteration'])) {
				return $string;
			}

			// Replace with ASCII characters
			$transliterations = $inflections['transliteration'];
			$string = preg_replace(array_keys($transliterations), array_values($transliterations), $string);

			// Remove any left over non 7bit ASCII
			return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $string);
		});
	}

}