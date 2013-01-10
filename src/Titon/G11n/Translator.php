<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n;

use Titon\Io\Reader;
use Titon\Cache\Storage;

/**
 * Interface for G11N string translators.
 */
interface Translator {

	/**
	 * Locate the key within the catalog. If the catalog has not been loaded,
	 * load it and cache the collection of strings.
	 *
	 * @param string $key
	 * @return string
	 */
	public function getMessage($key);

	/**
	 * Parse out the module, catalog and key for string lookup.
	 *
	 * @param string $key
	 * @return string
	 */
	public function parseKey($key);

	/**
	 * Set the file reader to use for resource parsing.
	 *
	 * @param \Titon\Io\Reader $reader
	 * @return \Titon\G11n\Translator
	 */
	public function setReader(Reader $reader);

	/**
	 * Set the storage engine to use for catalog caching.
	 *
	 * @param \Titon\Cache\Storage $storage
	 * @return \Titon\G11n\Translator
	 */
	public function setStorage(Storage $storage);

	/**
	 * Process the located string with dynamic parameters if necessary.
	 *
	 * @param string $key
	 * @param array $params
	 * @return string
	 */
	public function translate($key, array $params = []);

}