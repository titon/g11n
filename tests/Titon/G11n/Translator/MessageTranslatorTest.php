<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Translator;

use Titon\G11n\G11n;
use Titon\G11n\Locale;
use Titon\G11n\Translator\MessageTranslator;
use Titon\Cache\Storage\MemoryStorage;
use Titon\Io\Reader\PhpReader;
use Titon\Io\Reader\IniReader;
use Titon\Io\Reader\XmlReader;
use Titon\Io\Reader\JsonReader;
use Titon\Io\Reader\PoReader;

/**
 * Test class for Titon\G11n\Translator\MessageTranslator.
 */
class MessageTranslatorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ex-no,ex;q=0.5';

		foreach (['ex', 'en'] as $code) {
			$locale = new Locale($code);
			$locale->addLocation(TEMP_DIR)->addLocation(dirname(TEST_DIR) . '/resources/');

			G11n::addLocale($locale);
		}
	}

	/**
	 * Test reading keys from php message bundles.
	 */
	public function testPhpMessages() {
		$object = new MessageTranslator();
		$object->setReader(new PhpReader());
		$object->setStorage(new MemoryStorage());

		G11n::setTranslator($object);
		G11n::useLocale('ex');

		$this->assertEquals('Titon', $object->getMessage('default.titon'));
		$this->assertEquals('Test', $object->getMessage('default.test'));
		$this->assertEquals('php', $object->getMessage('default.type'));

		G11n::useLocale('en');

		$this->assertEquals('Titon', $object->translate('default.titon'));
		$this->assertEquals('Test', $object->translate('default.test'));
		$this->assertEquals('php', $object->translate('default.type'));
		$this->assertEquals('1,337 health, 666 energy, 255 damage', $object->translate('default.format', [1337, 666, 255]));
	}

	/**
	 * Test reading keys from ini message bundles.
	 */
	public function testIniMessages() {
		$object = new MessageTranslator();
		$object->setReader(new IniReader());
		$object->setStorage(new MemoryStorage());

		G11n::setTranslator($object);
		G11n::useLocale('ex');

		$this->assertEquals('Titon', $object->getMessage('default.titon'));
		$this->assertEquals('Test', $object->getMessage('default.test'));
		$this->assertEquals('ini', $object->getMessage('default.type'));

		G11n::useLocale('en');

		$this->assertEquals('Titon', $object->translate('default.titon'));
		$this->assertEquals('Test', $object->translate('default.test'));
		$this->assertEquals('ini', $object->translate('default.type'));
		$this->assertEquals('1,337 health, 666 energy, 255 damage', $object->translate('default.format', [1337, 666, 255]));
	}

	/**
	 * Test reading keys from xml message bundles.
	 */
	public function testXmlMessages() {
		$object = new MessageTranslator();
		$object->setReader(new XmlReader());
		$object->setStorage(new MemoryStorage());

		G11n::setTranslator($object);
		G11n::useLocale('ex');

		$this->assertEquals('Titon', $object->getMessage('default.titon'));
		$this->assertEquals('Test', $object->getMessage('default.test'));
		$this->assertEquals('xml', $object->getMessage('default.type'));

		G11n::useLocale('en');

		$this->assertEquals('Titon', $object->translate('default.titon'));
		$this->assertEquals('Test', $object->translate('default.test'));
		$this->assertEquals('xml', $object->translate('default.type'));
		$this->assertEquals('1,337 health, 666 energy, 255 damage', $object->translate('default.format', [1337, 666, 255]));
	}

	/**
	 * Test reading keys from json message bundles.
	 */
	public function testJsonMessages() {
		$object = new MessageTranslator();
		$object->setReader(new JsonReader());
		$object->setStorage(new MemoryStorage());

		G11n::setTranslator($object);
		G11n::useLocale('ex');

		$this->assertEquals('Titon', $object->getMessage('default.titon'));
		$this->assertEquals('Test', $object->getMessage('default.test'));
		$this->assertEquals('json', $object->getMessage('default.type'));

		G11n::useLocale('en');

		$this->assertEquals('Titon', $object->translate('default.titon'));
		$this->assertEquals('Test', $object->translate('default.test'));
		$this->assertEquals('json', $object->translate('default.type'));
		$this->assertEquals('1,337 health, 666 energy, 255 damage', $object->translate('default.format', [1337, 666, 255]));
	}

	/**
	 * Test reading keys from po message bundles.
	 */
	public function testPoMessages() {
		$object = new MessageTranslator();
		$object->setReader(new PoReader());
		$object->setStorage(new MemoryStorage());

		G11n::setTranslator($object);
		G11n::useLocale('ex');

		$this->assertEquals('Basic message', $object->getMessage('default.basic'));
		$this->assertEquals('Context message', $object->getMessage('default.context'));
		$this->assertEquals("Multiline message\nMore message here\nAnd more message again", $object->getMessage('default.multiline'));

		G11n::useLocale('en');

		$this->assertEquals('1,337 health, 666 energy, 255 damage', $object->translate('default.format', [1337, 666, 255]));
	}

}