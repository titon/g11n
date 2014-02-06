<?php
/**
 * @copyright   2010-2013, The Titon Project
 * @license     http://opensource.org/licenses/bsd-license.php
 * @link        http://titon.io
 */

namespace Titon\G11n\Route;

use Titon\Common\Registry;
use Titon\G11n\G11n;
use Titon\Route\Route;

/**
 * Applies locale aware routes through the G11n package.
 *
 * @package Titon\Route
 */
class LocaleRoute extends Route {

    /**
     * Store the routing configuration and prepend the locale pattern.
     *
     * @uses Titon\G11n\G11n
     * @uses Titon\Common\Registry
     *
     * @param string $key
     * @param string $path
     * @param string|array $route
     * @param array $config
     */
    public function __construct($key, $path, $route = [], array $config = []) {
        $g11n = G11n::registry();

        if ($g11n->isEnabled()) {
            if (mb_substr($path, 0, 9) !== '/<locale>') {
                $path = '/<locale>/' . ltrim($path, '/');
            }

            $config['patterns']['locale'] = self::LOCALE;
            $config['locale'] = G11n::canonicalize($g11n->getFallback()->getCode());
        }

        parent::__construct($key, $path, $route, $config);
    }

}