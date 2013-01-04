<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon;

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