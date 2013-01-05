<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Translator;

use Titon\Common\Config;
use Titon\G11n\G11n;
use Titon\G11n\Exception;
use Titon\G11n\Translator\AbstractTranslator;
use Titon\Utility\String;
use \Locale;

/**
 * Translator used for hooking into the GNU gettext library and fetching messages from locale domain files.
 */
class GettextTranslator extends AbstractTranslator {

	/**
	 * Bind domain locations if they have not been setup.
	 *
	 * @access public
	 * @param string $module
	 * @param string $catalog
	 * @return boolean
	 */
	public function bindDomains($module, $catalog) {
		bind_textdomain_codeset($catalog, Config::encoding());

		return $this->cache([__METHOD__, $module, $catalog], function() use ($module, $catalog) {
			foreach (G11n::current()->getMessageBundle()->getLocations() as $location) {
				bindtextdomain($catalog, String::insert($location, ['module' => $module]));
			}

			return true;
		});
	}

	/**
	 * Get the message from the bound domain.
	 *
	 * @access public
	 * @param string $key
	 * @return string
	 * @throws \Titon\G11n\Exception
	 */
	public function getMessage($key) {
		return $this->cache([__METHOD__, $key], function() use ($key) {
			list($module, $catalog, $id) = $this->parseKey($key);

			$this->bindDomains($module, $catalog);

			textdomain($catalog);

			$message = gettext($id);

			if ($message !== $id) {
				return $message;
			}

			throw new Exception(sprintf('Message key %s does not exist in %s', $key, Locale::DEFAULT_LOCALE));
		});
	}

}