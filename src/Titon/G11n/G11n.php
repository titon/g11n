<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n;

use Titon\Common\Registry;
use Titon\Common\Traits\StaticCacheable;
use Titon\G11n\Exception;
use Titon\G11n\Translator;
use Titon\Io\Bundle\LocaleBundle;
use \Locale;

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
	 * @var string
	 * @static
	 */
	protected static $_current;

	/**
	 * Fallback locale key if none can be found.
	 *
	 * @access protected
	 * @var string
	 * @static
	 */
	protected static $_fallback;

	/**
	 * Loaded locale bundles.
	 *
	 * @access protected
	 * @var array
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
	 * @param string $key
	 * @param \Titon\Io\Bundle\LocaleBundle $bundle
	 * @return void
	 * @static
	 */
	public static function addLocale($key, LocaleBundle $bundle) {
		$urlKey = self::canonicalize($key);

		if (isset(self::$_locales[$urlKey])) {
			return;
		}

		// Configure and initialize
		$bundle->config->bundle = self::canonicalize($key, self::FORMAT_3);
		$bundle->loadDefaults();

		// Cache the bundle
		self::$_locales[$urlKey] = Registry::set($bundle, 'g11n.bundle.' . $bundle->getLocale('id'));

		// Set the parent as well
		if ($parent = $bundle->getParent()) {
			self::addLocale($parent->getLocale('id'), $parent);
		}

		// Set fallback if none defined
		if (!self::$_fallback) {
			self::$_fallback = $urlKey;
		}
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

			foreach ([self::current(), self::getFallback()] as $bundle) {
				while ($bundle instanceof LocaleBundle) {
					$cycle[] = $bundle->getLocale('id');

					$bundle = $bundle->getParent();
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
		return Locale::composeLocale($tags);
	}

	/**
	 * Return the current locale config, or a certain value.
	 *
	 * @access public
	 * @return \Titon\Io\Bundle\LocaleBundle
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
		return Locale::parseLocale($locale);
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

		self::$_fallback = $key;

		ini_set('intl.default_locale', self::$_locales[$key]->getLocale('id'));
	}

	/**
	 * Return the fallback locale bundle.
	 *
	 * @access public
	 * @return \Titon\Io\Bundle\LocaleBundle
	 * @throws \Titon\G11n\Exception
	 * @static
	 */
	public static function getFallback() {
		if (!self::$_fallback || !isset(self::$_locales[self::$_fallback])) {
			throw new Exception('Fallback locale has not been setup');
		}

		return self::$_locales[self::$_fallback];
	}

	/**
	 * Returns the setup locales bundles.
	 *
	 * @access public
	 * @return array
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
			foreach ($header as $locale) {
				if (isset(self::$_locales[$locale])) {
					$current = $locale;
					break;
				}
			}
		}

		// Set current to the fallback if none found
		if ($current === null) {
			$current = self::$_fallback;
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
		$locale = self::current()->getLocale();

		return ($locale['key'] === $key || $locale['id'] === $key);
	}

	/**
	 * G11n will be enabled if more than 1 locale has been setup, excluding family chains.
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isEnabled() {
		return self::cache(__METHOD__, function() {
			$locales = self::getLocales();

			if (!$locales) {
				return false;
			}

			$loaded = [];

			foreach ($locales as $bundle) {
				$locale = $bundle->getLocale();
				$loaded[] = $locale['id'];

				if (isset($locale['parent'])) {
					$loaded[] = $locale['parent'];
				}
			}

			return (count(array_unique($loaded)) > 1);
		});
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
	 * @return void
	 * @static
	 */
	public static function setTranslator(Translator $translator) {
		self::$_translator = $translator;
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
	 * @return void
	 * @throws \Titon\G11n\Exception
	 * @static
	 */
	public static function useLocale($key) {
		$key = self::canonicalize($key);

		if (!isset(self::$_locales[$key])) {
			throw new Exception(sprintf('Locale %s does not exist', $key));
		}

		$bundle = self::$_locales[$key];
		$bundles = [$bundle, self::getFallback()];
		$options = [];

		foreach ($bundles as $tempBundle) {
			$locale = $tempBundle->getLocale();

			$options[] = $locale['id'] . '.UTF8';
			$options[] = $locale['id'] . '.UTF-8';
			$options[] = $locale['id'];

			if (!empty($locale['iso3'])) {
				foreach ((array) $locale['iso3'] as $iso3) {
					$options[] = $iso3 . '.UTF8';
					$options[] = $iso3 . '.UTF-8';
					$options[] = $iso3;
				}
			}

			if (!empty($locale['iso2'])) {
				$options[] = $locale['iso2'] . '.UTF8';
				$options[] = $locale['iso2'] . '.UTF-8';
				$options[] = $locale['iso2'];
			}
		}

		// Set environment
		$locale = $bundle->getLocale();

		putenv('LC_ALL=' . $locale['id']);
		setlocale(LC_ALL, $options);
		Locale::setDefault($locale['id']);

		self::$_current = $bundle;
	}

}

