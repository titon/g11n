<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Bundle;

use Titon\Io\Bundle\AbstractBundle;
use Titon\Io\Reader\PhpReader;

/**
 * The LocaleBundle manages the loading of locale resources which contain locale specific configuration,
 * validation rules (phone numbers, zip codes, etc), inflection rules (plurals, singulars, irregulars, etc)
 * and formatting rules (dates, times, etc).
 */
class LocaleBundle extends AbstractBundle {

	/**
	 * Add the PhpReader for locale bundle reading.
	 */
	public function initialize() {
		$this->addReader(new PhpReader());
	}

}
