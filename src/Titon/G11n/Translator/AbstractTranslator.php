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
	 * File reader used for parsing.
	 *
	 * @var \Titon\Io\Reader
	 */
	protected $_reader;

	/**
	 * Storage engine for caching.
	 *
	 * @var \Titon\Cache\Storage
	 */
	protected $_storage;

	/**
	 * Parse out the module, catalog and key for string lookup.
	 *
	 * @param string $key
	 * @return array
	 * @throws \Titon\G11n\Exception
	 * @final
	 */
	final public function parseKey($key) {
		return $this->cache([__METHOD__, $key], function() use ($key) {
			$parts = explode('.', preg_replace('/[^-a-z0-9\.]+/i', '', $key));
			$count = count($parts);
			$module = 'common';
			$catalog = 'default';

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
	 * @param \Titon\Cache\Storage $storage
	 * @return \Titon\G11n\Translator
	 */
	public function setStorage(Storage $storage) {
		$this->_storage = $storage;

		return $this;
	}

	/**
	 * Process the located string with dynamic parameters if necessary.
	 *
	 * @param string $key
	 * @param array $params
	 * @return string
	 */
	public function translate($key, array $params = []) {
		return MessageFormatter::formatMessage(Locale::DEFAULT_LOCALE, $this->getMessage($key), $params);
	}

}
