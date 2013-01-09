<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon;

use Titon\Common\Config;
use Titon\G11n\G11n;

/**
 * Convenience function for fetching a localized string.
 *
 * @param string $key
 * @param array $params
 * @return string
 */
function msg($key, array $params = []) {
	return G11n::translate($key, $params);
}

/**
 * Add g11n resources if VENDOR_DIR constant exists.
 */
if (defined('VENDOR_DIR')) {
	Config::add('Resource.paths', VENDOR_DIR . '/titon/g11n/resources/');
}