<?php
/**
 * @copyright   2010-2013, The Titon Project
 * @license     http://opensource.org/licenses/bsd-license.php
 * @link        http://titon.io
 */

use Titon\Common\Config;
use Titon\G11n\G11n;

/**
 * Add G11n resources if VENDOR_DIR constant exists.
 */
if (defined('VENDOR_DIR')) {
    Config::add('titon.path.resources', VENDOR_DIR . '/titon/g11n/src/resources/');
}

/**
 * Convenience function for fetching a localized string.
 * Uses a single combination key.
 *
 * @uses Titon\Common\Registry
 *
 * @param string $key
 * @param array $params
 * @return string
 */
if (!function_exists('msg')) {
    function msg($key, array $params = []) {
        return G11n::registry()->translate($key, $params);
    }
}

/**
 * Convenience function for fetching a localized string.
 * Uses separate values for key.
 *
 * @uses Titon\Common\Registry
 *
 * @param string $id
 * @param string $catalog
 * @param string $domain
 * @param array $params
 * @return string
 */
if (!function_exists('__')) {
    function __($id, $catalog = 'default', $domain = 'core', array $params = []) {
        return G11n::registry()->translate(sprintf('%s.%s.%s', $domain, $catalog, $id), $params);
    }
}