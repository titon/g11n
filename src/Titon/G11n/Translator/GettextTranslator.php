<?php
/**
 * @copyright   2010-2013, The Titon Project
 * @license     http://opensource.org/licenses/bsd-license.php
 * @link        http://titon.io
 */

namespace Titon\G11n\Translator;

use Titon\Common\Config;
use Titon\Common\Registry;
use Titon\G11n\G11n;
use Titon\G11n\Exception\MissingMessageException;
use Titon\G11n\Translator\AbstractTranslator;
use Titon\Utility\String;
use \Locale;

/**
 * Translator used for hooking into the GNU gettext library and fetching messages from locale domain files.
 *
 * @package Titon\G11n\Translator
 */
class GettextTranslator extends AbstractTranslator {

    /**
     * {@inheritdoc}
     *
     * @uses Titon\Common\Registry
     * @uses Titon\Utility\String
     */
    public function bindDomains($module, $catalog) {
        bind_textdomain_codeset($catalog, Config::encoding());

        return $this->cache([__METHOD__, $module, $catalog], function() use ($module, $catalog) {
            $locations = Registry::factory('Titon\G11n\G11n')->current()->getMessageBundle()->getLocations();

            foreach ($locations as $location) {
                bindtextdomain($catalog, String::insert($location, ['module' => $module]));
            }

            return true;
        });
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Titon\G11n\Exception\MissingMessageException
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

            throw new MissingMessageException(sprintf('Message key %s does not exist in %s', $key, Locale::DEFAULT_LOCALE));
        });
    }

}