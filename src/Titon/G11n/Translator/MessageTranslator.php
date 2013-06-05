<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Translator;

use Titon\Common\Registry;
use Titon\G11n\G11n;
use Titon\G11n\Exception;
use Titon\G11n\Translator\AbstractTranslator;

/**
 * Translator used for parsing resource files into an array of translated messages.
 *
 * @package Titon\G11n\Translator
 */
class MessageTranslator extends AbstractTranslator {

	/**
	 * {@inheritdoc}
	 *
	 * @uses Titon\G11n\G11n
	 * @uses Titon\Common\Registry
	 *
	 * @throws \Titon\G11n\Exception
	 */
	public function getMessage($key) {
		if ($cache = $this->getCache($key)) {
			return $cache;
		}

		list($module, $catalog, $id) = $this->parseKey($key);

		// Cycle through each locale till a message is found
		$g11n = Registry::factory('Titon\G11n\G11n');
		$locales = $g11n->getLocales();

		foreach ($g11n->cascade() as $locale) {
			$cacheKey = sprintf('g11n.%s.%s.%s', $module, $catalog, $locale);
			$messages = [];

			// Check within the cache first
			if ($storage = $this->getStorage()) {
				$messages = $storage->get($cacheKey);
			}

			// Else check within the bundle
			if (!$messages) {
				$bundle = clone $locales[G11n::canonicalize($locale)]->getMessageBundle();
				$bundle->addReader($this->getReader());
				$bundle->config->set('module', $module);

				if ($data = $bundle->loadResource($catalog)) {
					$messages = $data;

					if ($storage = $this->getStorage()) {
						$storage->set($cacheKey, $messages);
					}

				// If the catalog doesn't exist, try the next locale
				} else {
					continue;
				}
			}

			// Return message if it exists, else continue cycle
			if (isset($messages[$id])) {
				return $this->setCache($key, $messages[$id]);
			}
		}

		throw new Exception(sprintf('Message key %s does not exist in %s', $key, implode(', ', array_keys($locales))));
	}

}