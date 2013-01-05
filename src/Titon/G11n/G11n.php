<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n;

use Titon\Common\Registry;
use Titon\Common\Traits\StaticCacheable;
use Titon\G11n\Locale;
use Titon\G11n\Translator;
use Titon\G11n\Exception;

/**
 * The Globalization class handles all the necessary functionality for internationalization and
 * localization. This includes defining which locales to support, loading translators,
 * parsing resource bundles and initializing environments.
 *
 * @link http://en.wikipedia.org/wiki/IETF_language_tag
 * @link http://en.wikipedia.org/wiki/ISO_639
 * @link http://en.wikipedia.org/wiki/ISO_3166-1
 * @link http://loc.gov/standards/iso639-2/php/code_list.php
 */
class G11n {
	use StaticCacheable;

	/**
	 * Possible formats for locale keys.
	 *
	 *	FORMAT_1 - en-us (URL format)
	 *	FORMAT_2 - en-US
	 *	FORMAT_3 - en_US (Preferred)
	 *	FORMAT_4 - enUS
	 */
	const FORMAT_1 = 1;
	const FORMAT_2 = 2;
	const FORMAT_3 = 3;
	const FORMAT_4 = 3;

	/**
	 * Currently active locale bundle based on the client.
	 *
	 * @access protected
	 * @var \Titon\G11n\Locale
	 * @static
	 */
	protected static $_current;

	/**
	 * Fallback locale key if none can be found.
	 *
	 * @access protected
	 * @var \Titon\G11n\Locale
	 * @static
	 */
	protected static $_fallback;

	/**
	 * Loaded locale bundles.
	 *
	 * @access protected
	 * @var \Titon\G11n\Locale[]
	 * @static
	 */
	protected static $_locales = [];

	/**
	 * Translator used for string fetching and parsing.
	 *
	 * @access protected
	 * @var \Titon\G11n\Translator
	 * @static
	 */
	protected static $_translator;

	/**
	 * Sets up the application with the defined locale key; the key will be formatted to a lowercase dashed URL friendly format.
	 * The system will then attempt to load the locale resource bundle and finalize configuration settings.
	 *
	 * @access public
	 * @param \Titon\G11n\Locale $locale
	 * @return \Titon\G11n\Locale
	 * @static
	 */
	public static function addLocale(Locale $locale) {
		$key = self::canonicalize($locale->getCode());

		if (isset(self::$_locales[$key])) {
			return self::$_locales[$key];
		}

		// Configure and initialize
		$locale->initialize();

		// Cache the bundle
		self::$_locales[$key] = $locale;

		// Set the parent as well
		if ($parent = $locale->getParentLocale()) {
			self::addLocale($parent);
		}

		// Set fallback if none defined
		if (!self::$_fallback) {
			self::fallbackAs($key);
		}

		return $locale;
	}

	/**
	 * Convert a locale key to 3 possible formats.
	 *
	 * @access public
	 * @param string $key
	 * @param int $format
	 * @return string
	 * @static
	 */
	public static function canonicalize($key, $format = self::FORMAT_1) {
		return self::cache([__METHOD__, $key, $format], function() use ($key, $format) {
			$parts = explode('-', str_replace('_', '-', mb_strtolower($key)));
			$return = $parts[0];

			if (isset($parts[1])) {
				switch ($format) {
					case self::FORMAT_1:
						$return .= '-' . $parts[1];
					break;
					case self::FORMAT_2:
						$return .= '-' . mb_strtoupper($parts[1]);
					break;
					case self::FORMAT_3:
						$return .= '_' . mb_strtoupper($parts[1]);
					break;
					case self::FORMAT_4:
						$return .= mb_strtoupper($parts[1]);
					break;
				}
			}

			return $return;
		});
	}

	/**
	 * Get a list of locales and fallback locales in descending order starting from the current locale.
	 *
	 * @access public
	 * @return array
	 * @static
	 */
	public static function cascade() {
		return self::cache(__METHOD__, function() {
			$cycle = [];

			foreach ([self::current(), self::getFallback()] as $locale) {
				while ($locale instanceof Locale) {
					$cycle[] = $locale->getCode();

					$locale = $locale->getParentLocale();
				}
			}

			return array_unique($cycle);
		});
	}

	/**
	 * Takes an array of key-values and returns a correctly ordered and delimited locale ID.
	 *
	 * @access public
	 * @param array $tags
	 * @return string
	 * @static
	 */
	public static function compose(array $tags) {
		return \Locale::composeLocale($tags);
	}

	/**
	 * Return the current locale config, or a certain value.
	 *
	 * @access public
	 * @return \Titon\G11n\Locale
	 * @static
	 */
	public static function current() {
		return self::$_current;
	}

	/**
	 * Parses a locale string and returns an array of key-value locale tags.
	 *
	 * @access public
	 * @param string $locale
	 * @return string
	 * @static
	 */
	public static function decompose($locale) {
		return \Locale::parseLocale($locale);
	}

	/**
	 * Define the fallback language if none can be found or is not supported.
	 *
	 * @access public
	 * @param string $key
	 * @return void
	 * @throws \Titon\G11n\Exception
	 * @static
	 */
	public static function fallbackAs($key) {
		$key = self::canonicalize($key);

		if (!isset(self::$_locales[$key])) {
			throw new Exception(sprintf('Locale %s has not been setup', $key));
		}

		self::$_fallback = self::$_locales[$key];

		ini_set('intl.default_locale', self::$_fallback->getCode());
	}

	/**
	 * Return the fallback locale bundle.
	 *
	 * @access public
	 * @return \Titon\G11n\Locale
	 * @static
	 */
	public static function getFallback() {
		return self::$_fallback;
	}

	/**
	 * Returns the setup locales bundles.
	 *
	 * @access public
	 * @return \Titon\G11n\Locale[]
	 * @static
	 */
	public static function getLocales() {
		return self::$_locales;
	}

	/**
	 * Detect which locale to use based on the clients Accept-Language header.
	 *
	 * @access public
	 * @return void
	 * @throws \Titon\G11n\Exception
	 * @static
	 */
	public static function initialize() {
		if (!self::isEnabled()) {
			return;
		}

		$header = mb_strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);

		if (mb_strpos($header, ';') !== false) {
			$header = mb_strstr($header, ';', true);
		}

		$header = explode(',', $header);
		$current = null;

		if (count($header) > 0) {
			foreach ($header as $key) {
				if (isset(self::$_locales[$key])) {
					$current = $key;
					break;
				}
			}
		}

		// Set current to the fallback if none found
		if ($current === null) {
			$current = self::$_fallback->getCode();
		}

		// Apply the locale
		self::useLocale($current);

		// Check for a translator
		if (!self::$_translator) {
			throw new Exception('A translator is required for G11n message parsing');
		}
	}

	/**
	 * Does the current locale matched the passed key?
	 *
	 * @access public
	 * @param string $key
	 * @return boolean
	 * @static
	 */
	public static function is($key) {
		$code = self::current()->getCode();

		return ($code === $key || self::canonicalize($code) === $key);
	}

	/**
	 * G11n will be enabled if more than 1 locale has been setup.
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isEnabled() {
		return (count(self::$_locales) > 0);
	}

	/**
	 * Return an array of setup locale keys.
	 *
	 * @access public
	 * @return array
	 * @static
	 */
	public static function listing() {
		return array_keys(self::$_locales);
	}

	/**
	 * Sets the translator to use in the string locating and translating process.
	 *
	 * @access public
	 * @param \Titon\G11n\Translator $translator
	 * @return \Titon\G11n\Translator
	 * @static
	 */
	public static function setTranslator(Translator $translator) {
		self::$_translator = $translator;

		return $translator;
	}

	/**
	 * Return a translated string using the translator.
	 * If a storage engine is present, read and write from the cache.
	 *
	 * @access public
	 * @param string $key
	 * @param array $params
	 * @return string
	 */
	public static function translate($key, array $params = []) {
		return self::$_translator->translate($key, $params);
	}

	/**
	 * Set the locale using PHPs built in setlocale().
	 *
	 * @link http://php.net/setlocale
	 * @link http://php.net/manual/locale.setdefault.php
	 *
	 * @access public
	 * @param string $key
	 * @return \Titon\G11n\Locale
	 * @throws \Titon\G11n\Exception
	 * @static
	 */
	public static function useLocale($key) {
		$key = self::canonicalize($key);

		if (!isset(self::$_locales[$key])) {
			throw new Exception(sprintf('Locale %s does not exist', $key));
		}

		$locale = self::$_locales[$key];
		$locales = [$locale];
		$options = [];

		if (self::getFallback()->getCode() != $locale->getCode()) {
			$locales[] = self::getFallback();
		}

		foreach ($locales as $loc) {
			$config = $loc->config->get();

			$options[] = $config['code'] . '.UTF8';
			$options[] = $config['code'] . '.UTF-8';
			$options[] = $config['code'];

			if (!empty($config['iso3'])) {
				foreach ((array) $config['iso3'] as $iso3) {
					$options[] = $iso3 . '.UTF8';
					$options[] = $iso3 . '.UTF-8';
					$options[] = $iso3;
				}
			}

			if (!empty($config['iso2'])) {
				$options[] = $config['iso2'] . '.UTF8';
				$options[] = $config['iso2'] . '.UTF-8';
				$options[] = $config['iso2'];
			}
		}

		// Set environment
		putenv('LC_ALL=' . $locale->getCode());
		setlocale(LC_ALL, $options);
		\Locale::setDefault($locale->getCode());

		self::$_current = $locale;

		return $locale;
	}

}

