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
	 * @access public
	 * @param string $key
	 * @return string
	 */
	public function getMessage($key);

	/**
	 * Load the correct resource bundle for the associated file type.
	 *
	 * @access public
	 * @param string $module
	 * @param string $locale
	 * @return \Titon\Io\Bundle
	 */
	public function loadBundle($module, $locale);

	/**
	 * Parse out the module, catalog and key for string lookup.
	 *
	 * @access public
	 * @param string $key
	 * @return string
	 */
	public function parseKey($key);

	/**
	 * Set the file reader to use for resource parsing.
	 *
	 * @access public
	 * @param \Titon\Io\Reader $reader
	 * @return \Titon\G11n\Translator
	 */
	public function setReader(Reader $reader);

	/**
	 * Set the storage engine to use for catalog caching.
	 *
	 * @access public
	 * @param \Titon\Cache\Storage $storage
	 * @return \Titon\G11n\Translator
	 */
	public function setStorage(Storage $storage);

	/**
	 * Process the located string with dynamic parameters if necessary.
	 *
	 * @access public
	 * @param string $key
	 * @param array $params
	 * @return string
	 */
	public function translate($key, array $params = []);

}