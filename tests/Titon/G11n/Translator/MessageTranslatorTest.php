<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n\Translator;

use Titon\Common\Registry;
use Titon\G11n\G11n;
use Titon\G11n\Locale;
use Titon\G11n\Translator\MessageTranslator;
use Titon\Cache\Storage\MemoryStorage;
use Titon\Io\Reader\PhpReader;
use Titon\Io\Reader\IniReader;
use Titon\Io\Reader\XmlReader;
use Titon\Io\Reader\JsonReader;
use Titon\Io\Reader\PoReader;
use Titon\Test\TestCase;

/**
 * Test class for Titon\G11n\Translator\MessageTranslator.
 *
 * @property \Titon\G11n\G11n $object
 */
class MessageTranslatorTest extends TestCase {

	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();

		$this->object = Registry::factory('Titon\G11n\G11n');

		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ex-no,ex;q=0.5';

		foreach (['ex', 'en'] as $code) {
			$this->object->addLocale(new Locale($code));
		}
	}

	/**
	 * Test reading keys from php message bundles.
	 */
	public function testPhpMessages() {
		$object = new MessageTranslator();
		$object->setReader(new PhpReader());
		$object->setStorage(new MemoryStorage());

		$this->object->setTranslator($object);
		$this->object->useLocale('ex');

		$this->assertEquals('Titon', $object->getMessage('default.titon'));
		$this->assertEquals('Test', $object->getMessage('default.test'));
		$this->assertEquals('php', $object->getMessage('default.type'));

		$this->object->useLocale('en');

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

		$this->object->setTranslator($object);
		$this->object->useLocale('ex');

		$this->assertEquals('Titon', $object->getMessage('default.titon'));
		$this->assertEquals('Test', $object->getMessage('default.test'));
		$this->assertEquals('ini', $object->getMessage('default.type'));

		$this->object->useLocale('en');

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

		$this->object->setTranslator($object);
		$this->object->useLocale('ex');

		$this->assertEquals('Titon', $object->getMessage('default.titon'));
		$this->assertEquals('Test', $object->getMessage('default.test'));
		$this->assertEquals('xml', $object->getMessage('default.type'));

		$this->object->useLocale('en');

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

		$this->object->setTranslator($object);
		$this->object->useLocale('ex');

		$this->assertEquals('Titon', $object->getMessage('default.titon'));
		$this->assertEquals('Test', $object->getMessage('default.test'));
		$this->assertEquals('json', $object->getMessage('default.type'));

		$this->object->useLocale('en');

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

		$this->object->setTranslator($object);
		$this->object->useLocale('ex');

		$this->assertEquals('Basic message', $object->getMessage('default.basic'));
		$this->assertEquals('Context message', $object->getMessage('default.context'));
		$this->assertEquals("Multiline message\nMore message here\nAnd more message again", $object->getMessage('default.multiline'));

		$this->object->useLocale('en');

		$this->assertEquals('1,337 health, 666 energy, 255 damage', $object->translate('default.format', [1337, 666, 255]));
	}

}