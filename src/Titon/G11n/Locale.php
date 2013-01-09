<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n;

use Titon\Common\Base;
use Titon\Common\Config;
use Titon\Common\Traits\Cacheable;
use Titon\G11n\Bundle\LocaleBundle;
use Titon\G11n\Bundle\MessageBundle;
use Titon\Utility\Hash;

/**
 * The Locale class manages all aspects of a locale code, it's region specific rules
 * and even translated messages.
 */
class Locale extends Base {
	use Cacheable;

	/**
	 * Locale country code.
	 *
	 * @access protected
	 * @var string
	 */
	protected $_code;

	/**
	 * Locale resource bundle.
	 *
	 * @access protected
	 * @var \Titon\G11n\Bundle\LocaleBundle
	 */
	protected $_localeBundle;

	/**
	 * Message resource bundle.
	 *
	 * @access protected
	 * @var \Titon\G11n\Bundle\MessageBundle
	 */
	protected $_messageBundle;

	/**
	 * Parent locale.
	 *
	 * @access protected
	 * @var \Titon\G11n\Locale
	 */
	protected $_parent;

	/**
	 * Set code and config.
	 *
	 * @access public
	 * @param string $code
	 * @param array $config
	 */
	public function __construct($code, array $config = []) {
		$config['initialize'] = false;

		parent::__construct($config);

		$this->_code = $code;
	}

	/**
	 * Instantiate the locale and message bundles using the resource locations.
	 *
	 * @access public
	 * @return \Titon\G11n\Locale
	 */
	public function initialize() {
		$locale = new LocaleBundle();
		$message = new MessageBundle();
		$code = $this->getCode();

		if ($locations = Config::get('Resource.paths')) {
			foreach ((array) $locations as $location) {
				$locale->addLocation(sprintf('%s/locales/%s', $location, $code));
				
				$message->addLocation(sprintf('%s/messages/%s', $location, $code));
				$message->addLocation(sprintf('%s/messages/%s/LC_MESSAGES', $location, $code));
			}
		}

		$this->_localeBundle = $locale;
		$this->_messageBundle = $message;

		// Gather locale configuration
		if ($data = $locale->loadResource('locale')) {
			$data = \Locale::parseLocale($data['code']) + $data;

			$config = $this->config->get();
			unset($config['code'], $config['initialize']);

			$this->config->set($config + $data);
		}

		// Force parent config to merge
		$this->getParentLocale();

		return $this;
	}

	/**
	 * Return the locale code.
	 *
	 * @access public
	 * @return string
	 */
	public function getCode() {
		return $this->_code;
	}

	/**
	 * Return the format patterns from the locale bundle.
	 *
	 * @access public
	 * @param string $key
	 * @return string|array
	 */
	public function getFormatPatterns($key = null) {
		return Hash::get($this->_loadResource('formats'), $key);
	}

	/**
	 * Return the inflection rules from the locale bundle.
	 *
	 * @access public
	 * @param string $key
	 * @return string|array
	 */
	public function getInflectionRules($key = null) {
		return Hash::get($this->_loadResource('inflections'), $key);
	}

	/**
	 * Return the validation rules from the locale bundle.
	 *
	 * @access public
	 * @param string $key
	 * @return string|array
	 */
	public function getValidationRules($key = null) {
		return Hash::get($this->_loadResource('validations'), $key);
	}

	/**
	 * Return the parent locale if it exists.
	 *
	 * @access public
	 * @return \Titon\G11n\Locale
	 */
	public function getParentLocale() {
		if ($this->_parent) {
			return $this->_parent;
		}

		if (!$this->config->has('parent')) {
			return null;
		}

		$parent = new Locale($this->config->parent);
		$parent->initialize();

		// Merge parent config
		$this->config->set($this->config->get() + $parent->config->get());

		$this->_parent = $parent;

		return $parent;
	}

	/**
	 * Return the locale bundle.
	 *
	 * @access public
	 * @return \Titon\G11n\Bundle\LocaleBundle
	 */
	public function getLocaleBundle() {
		return $this->_localeBundle;
	}

	/**
	 * Return the message bundle.
	 *
	 * @access public
	 * @return \Titon\G11n\Bundle\MessageBundle
	 */
	public function getMessageBundle() {
		return $this->_messageBundle;
	}

	/**
	 * Load a resource from the locale bundle and merge with the parent if possible.
	 *
	 * @access protected
	 * @param string $resource
	 * @return array
	 */
	protected function _loadResource($resource) {
		return $this->cache([__METHOD__, $resource], function() use ($resource) {
			$data = $this->getLocaleBundle()->loadResource($resource);

			if ($parent = $this->getParentLocale()) {
				$data = array_merge(
					$parent->getLocaleBundle()->loadResource($resource),
					$data
				);
			}

			return $data;
		});
	}

}