<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Utility;

use Titon\G11n\G11n;

/**
 * Enhance the parent Inflector class by providing localized inflection rule support.
 */
class Inflector extends \Titon\Utility\Inflector {

	/**
	 * Inflect a number by appending its ordinal suffix: st, nd, rd, th, etc.
	 *
	 * @param int $number
	 * @return string
	 * @static
	 */
	public static function ordinal($number) {
		if (!G11n::isEnabled()) {
			return $number;
		}

		return self::_cache([__METHOD__, $number], function() use ($number) {
			$inflections = G11n::current()->getInflectionRules();
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
	 * Inflect a form to its pluralized form. Applies special rules to determine uninflected, irregular or regular forms.
	 *
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function pluralize($string) {
		if (!G11n::isEnabled()) {
			return $string;
		}

		return self::_cache([__METHOD__, $string], function() use ($string) {
			$string = mb_strtolower($string);
			$result = null;
			$inflections = G11n::current()->getInflectionRules();

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
	 * Inflect a form to its singular form. Applies special rules to determine uninflected, irregular or regular forms.
	 *
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function singularize($string) {
		if (!G11n::isEnabled()) {
			return $string;
		}

		return self::_cache([__METHOD__, $string], function() use ($string) {
			$string = mb_strtolower($string);
			$result = null;
			$inflections = G11n::current()->getInflectionRules();

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
	 * Inflect a word by replacing all non-ASCII characters with there equivalents.
	 *
	 * @param string $string
	 * @return string
	 * @static
	 */
	public static function transliterate($string) {
		if (!G11n::isEnabled()) {
			return $string;
		}

		return self::_cache([__METHOD__, $string], function() use ($string) {
			$inflections = G11n::current()->getInflectionRules();

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