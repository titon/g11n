<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Translator;

use Titon\G11n\Exception;
use Titon\G11n\Translator\AbstractTranslator;
use Titon\Io\Bundle\MessageBundle;

/**
 * Translator used for parsing resource files into an array of translated messages.
 */
class MessageTranslator extends AbstractTranslator {

	/**
	 * Initialize the MessageBundle and inject the Reader dependency.
	 *
	 * @access public
	 * @param string $module
	 * @param string $locale
	 * @return \Titon\Io\Bundle
	 * @throws \Titon\G11n\Exception
	 */
	public function loadBundle($module, $locale) {
		if (!$this->_reader) {
			throw new Exception('No Reader has been loaded for message translating');
		}

		$bundle = new MessageBundle([
			'module' => $module,
			'bundle' => $locale
		]);

		$bundle->addReader($this->_reader);

		return $bundle;
	}

}