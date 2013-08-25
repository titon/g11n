<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n;

use Titon\Common\Config;
use Titon\Common\Registry;
use Titon\Common\Traits\Cacheable;
use Titon\Event\Traits\Emittable;
use Titon\G11n\Exception\MissingTranslatorException;
use Titon\G11n\Exception\MissingLocaleException;
use Titon\G11n\Locale;
use Titon\G11n\Translator;

/**
 * The Globalization class handles all the necessary functionality for internationalization and
 * localization. This includes defining which locales to support, loading translators,
 * parsing resource bundles and initializing environments.
 *
 * @link http://en.wikipedia.org/wiki/IETF_language_tag
 * @link http://en.wikipedia.org/wiki/ISO_639
 * @link http://en.wikipedia.org/wiki/ISO_3166-1
 * @link http://loc.gov/standards/iso639-2/php/code_list.php
 *
 * @package Titon\G11n
 */
class G11n {
	use Cacheable, Emittable;

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
	 * Currently active locale based on the client.
	 *
	 * @type \Titon\G11n\Locale
	 */
	protected $_current;

	/**
	 * Fallback locale if none can be found.
	 *
	 * @type \Titon\G11n\Locale
	 */
	protected $_fallback;

	/**
	 * Supported list of locales.
	 *
	 * @type \Titon\G11n\Locale[]
	 */
	protected $_locales = [];

	/**
	 * Translator used for string fetching and parsing.
	 *
	 * @type \Titon\G11n\Translator
	 */
	protected $_translator;

	/**
	 * Sets up the application with the defined locale key; the key will be formatted to a lowercase dashed URL friendly format.
	 * The system will then attempt to load the locale resource bundle and finalize configuration settings.
	 *
	 * @param \Titon\G11n\Locale $locale
	 * @return \Titon\G11n\Locale
	 */
	public function addLocale(Locale $locale) {
		$key = self::canonicalize($locale->getCode());

		if (isset($this->_locales[$key])) {
			return $this->_locales[$key];
		}

		// Configure and initialize
		$locale->initialize();

		// Set the locale
		$this->_locales[$key] = $locale;

		// Set the parent as well
		if ($parent = $locale->getParentLocale()) {
			$this->addLocale($parent);
		}

		// Set fallback if none defined
		if (!$this->_fallback) {
			$this->setFallback($key);
		}

		return $locale;
	}

	/**
	 * Convert a locale key to 3 possible formats.
	 *
	 * @param string $key
	 * @param int $format
	 * @return string
	 */
	public static function canonicalize($key, $format = self::FORMAT_1) {
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
	}

	/**
	 * Get a list of locales and fallback locales in descending order starting from the current locale.
	 *
	 * @return array
	 */
	public function cascade() {
		return $this->cache(__METHOD__, function() {
			$cycle = [];

			foreach ([$this->current(), $this->getFallback()] as $locale) {
				while ($locale instanceof Locale) {
					$cycle[] = $locale->getCode();

					$locale = $locale->getParentLocale();
				}
			}

			$cycle = array_unique($cycle);

			$this->emit('g11n.cascade', [&$cycle]);

			return $cycle;
		});
	}

	/**
	 * Takes an array of key-values and returns a correctly ordered and delimited locale ID.
	 *
	 * @uses Locale
	 *
	 * @param array $tags
	 * @return string
	 */
	public static function compose(array $tags) {
		return \Locale::composeLocale($tags);
	}

	/**
	 * Return the current locale.
	 *
	 * @return \Titon\G11n\Locale
	 */
	public function current() {
		return $this->_current;
	}

	/**
	 * Parses a locale string and returns an array of key-value locale tags.
	 *
	 * @uses Locale
	 *
	 * @param string $locale
	 * @return string
	 */
	public static function decompose($locale) {
		return \Locale::parseLocale($locale);
	}

	/**
	 * Return the fallback locale.
	 *
	 * @return \Titon\G11n\Locale
	 */
	public function getFallback() {
		return $this->_fallback;
	}

	/**
	 * Returns a list of supported locales.
	 *
	 * @return \Titon\G11n\Locale[]
	 */
	public function getLocales() {
		return $this->_locales;
	}

	/**
	 * Return the translator.
	 *
	 * @return \Titon\G11n\Translator
	 */
	public function getTranslator() {
		return $this->_translator;
	}

	/**
	 * Detect which locale to use based on the clients Accept-Language header.
	 *
	 * @throws \Titon\G11n\Exception\MissingTranslatorException
	 */
	public function initialize() {
		if (!$this->isEnabled()) {
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
				if (isset($this->_locales[$key])) {
					$current = $key;
					break;
				}
			}
		}

		// Set current to the fallback if none found
		if ($current === null) {
			$current = $this->_fallback->getCode();
		}

		// Apply the locale
		$this->useLocale($current);

		// Check for a translator
		if (!$this->_translator) {
			throw new MissingTranslatorException('A translator is required for G11n message parsing');
		}
	}

	/**
	 * Does the current locale matched the passed key?
	 *
	 * @param string $key
	 * @return bool
	 */
	public function is($key) {
		$code = $this->current()->getCode();

		return ($code === $key || $this->canonicalize($code) === $key);
	}

	/**
	 * G11n will be enabled if more than 1 locale has been setup.
	 *
	 * @return bool
	 */
	public function isEnabled() {
		return (count($this->_locales) > 0);
	}

	/**
	 * Define the fallback locale to use if none can be found or is not supported.
	 *
	 * @uses Titon\Common\Config
	 *
	 * @param string $key
	 * @return \Titon\G11n\G11n
	 * @throws \Titon\G11n\Exception\MissingLocaleException
	 */
	public function setFallback($key) {
		$key = $this->canonicalize($key);

		if (!isset($this->_locales[$key])) {
			throw new MissingLocaleException(sprintf('Locale %s has not been setup', $key));
		}

		$this->_fallback = $this->_locales[$key];

		Config::set('titon.locale.fallback', $key);

		return $this;
	}

	/**
	 * Sets the translator to use in the string locating and translating process.
	 *
	 * @param \Titon\G11n\Translator $translator
	 * @return \Titon\G11n\Translator
	 */
	public function setTranslator(Translator $translator) {
		$this->_translator = $translator;

		return $translator;
	}

	/**
	 * Return a translated string using the translator.
	 * If a storage engine is present, read and write from the cache.
	 *
	 * @param string $key
	 * @param array $params
	 * @return string
	 */
	public function translate($key, array $params = []) {
		$message = $this->getTranslator()->translate($key, $params);

		$this->emit('g11n.translate', [$key, &$message, $params]);

		return $message;
	}

	/**
	 * Set the locale using PHPs built in setlocale().
	 *
	 * @link http://php.net/setlocale
	 * @link http://php.net/manual/locale.setdefault.php
	 *
	 * @uses Titon\Common\Config
	 *
	 * @param string $key
	 * @return \Titon\G11n\Locale
	 * @throws \Titon\G11n\Exception\MissingLocaleException
	 */
	public function useLocale($key) {
		$key = self::canonicalize($key);

		if (!isset($this->_locales[$key])) {
			throw new MissingLocaleException(sprintf('Locale %s does not exist', $key));
		}

		$locale = $this->_locales[$key];
		$locales = [$locale];
		$options = [];

		if ($this->getFallback()->getCode() != $locale->getCode()) {
			$locales[] = $this->getFallback();
		}

		foreach ($locales as $loc) {
			$config = $loc->config->all();

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
		$code = $locale->getCode();

		putenv('LC_ALL=' . $code);
		setlocale(LC_ALL, $options);

		\Locale::setDefault($code);
		Config::set('titon.locale.current', $code);

		$this->_current = $locale;

		$this->emit('g11n.useLocale', [$locale]);

		return $locale;
	}

}

