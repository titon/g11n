<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Translator;

use Titon\Common\Base;
use Titon\Common\Traits\Cacheable;
use Titon\Cache\Storage;
use Titon\Io\Reader;
use Titon\G11n\G11n;
use Titon\G11n\Translator;
use Titon\G11n\Exception;
use \MessageFormatter;
use \Locale;

/**
 * Abstract class that implements the string translation functionality for Translators.
 */
abstract class AbstractTranslator extends Base implements Translator {
	use Cacheable;

	/**
	 * List of MessageBundle's.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_bundles = [];

	/**
	 * File reader used for parsing.
	 *
	 * @access protected
	 * @var \Titon\Io\Reader
	 */
	protected $_reader;

	/**
	 * Storage engine for caching.
	 *
	 * @access protected
	 * @var \Titon\Cache\Storage
	 */
	protected $_storage;

	/**
	 * Locate the key within the catalog. If the catalog has not been loaded,
	 * load it and cache the collection of strings.
	 *
	 * @access public
	 * @param string $key
	 * @return string
	 * @throws \Titon\G11n\Exception
	 */
	public function getMessage($key) {
		if ($cache = $this->getCache($key)) {
			return $cache;
		}

		list($module, $catalog, $id) = $this->parseKey($key);

		// Cycle through each locale till a message is found
		$locales = G11n::cascade();

		foreach ($locales as $locale) {
			$cacheKey = 'g11n.' . ($module === null ? 'root' : $module) . '.' . $catalog . '.' . $locale;
			$messages = [];

			// Check within the cache first
			if ($this->_storage) {
				$messages = $this->_storage->get($cacheKey);
			}

			// Else check within the bundle
			if (!$messages) {
				$bundleKey = $module . '.' . $locale;

				if (!isset($this->_bundles[$bundleKey])) {
					$this->_bundles[$bundleKey] = $this->loadBundle($module, $locale);
				}

				$bundle = $this->_bundles[$bundleKey];

				// If the catalog doesn't exist, try the next locale
				if ($data = $bundle->loadResource($catalog)) {
					$messages = $data;
				}

				if ($this->_storage) {
					$this->_storage->set($cacheKey, $messages);
				}
			}

			// Return message if it exists, else continue cycle
			if (isset($messages[$id])) {
				return $this->setCache($key, $messages[$id]);
			}
		}

		throw new Exception(sprintf('Message key %s does not exist in %s', $key, implode(', ', $locales)));
	}

	/**
	 * Parse out the module, catalog and key for string lookup.
	 *
	 * @access public
	 * @param string $key
	 * @return array
	 * @throws \Titon\G11n\Exception
	 * @final
	 */
	final public function parseKey($key) {
		return $this->cache([__METHOD__, $key], function() use ($key) {
			$parts = explode('.', preg_replace('/[^-a-z0-9\.]+/i', '', $key));
			$count = count($parts);
			$module = null;
			$catalog = null;

			if ($count < 2) {
				throw new Exception(sprintf('No module or catalog present for %s key', $key));

			} else if ($count === 2) {
				$catalog = $parts[0];
				$key = $parts[1];

			} else {
				$module = array_shift($parts);
				$catalog = array_shift($parts);
				$key = implode('.', $parts);
			}

			return [$module, $catalog, $key];
		});
	}

	/**
	 * Set the file reader to use for resource parsing.
	 *
	 * @access public
	 * @param \Titon\Io\Reader $reader
	 * @return \Titon\G11n\Translator
	 */
	public function setReader(Reader $reader) {
		$this->_reader = $reader;

		return $this;
	}

	/**
	 * Set the storage engine to use for catalog caching.
	 *
	 * @access public
	 * @param \Titon\Cache\Storage $storage
	 * @return \Titon\G11n\Translator
	 */
	public function setStorage(Storage $storage) {
		$this->_storage = $storage;
		$this->_storage->config->storage = 'g11n';

		return $this;
	}

	/**
	 * Process the located string with dynamic parameters if necessary.
	 *
	 * @access public
	 * @param string $key
	 * @param array $params
	 * @return string
	 */
	public function translate($key, array $params = []) {
		return MessageFormatter::formatMessage(Locale::DEFAULT_LOCALE, $this->getMessage($key), $params);
	}

}
