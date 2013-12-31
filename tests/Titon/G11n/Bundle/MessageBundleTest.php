<?php
/**
 * @copyright   2010-2013, The Titon Project
 * @license     http://opensource.org/licenses/bsd-license.php
 * @link        http://titon.io
 */

namespace Titon\G11n\Bundle;

use Titon\G11n\Bundle\MessageBundle;
use Titon\Io\Reader\PhpReader;
use Titon\Io\Reader\IniReader;
use Titon\Io\Reader\JsonReader;
use Titon\Io\Reader\XmlReader;
use Titon\Io\Reader\PoReader;
use Titon\Test\TestCase;

/**
 * Test class for Titon\G11n\Bundle\MessageBundle.
 */
class MessageBundleTest extends TestCase {

    /**
     * Test that loading messages from php files work.
     */
    public function testPhpBundles() {
        $bundle = new MessageBundle(['bundle' => 'ex']);
        $bundle->addReader(new PhpReader())->addPath('common', TEMP_DIR . '/messages/{bundle}/');

        $messages = $bundle->loadResource('common', 'default');

        $this->assertTrue(is_array($messages));
        $this->assertEquals(['titon', 'test', 'type', 'format'], array_keys($messages));
        $this->assertEquals([
            'titon' => 'Titon',
            'test' => 'Test',
            'type' => 'php',
            'format' => '{0,number,integer} health, {1,number,integer} energy, {2,number} damage'
        ], $messages);

        $messages = $bundle->loadResource('common', 'doesntExist');

        $this->assertTrue(is_array($messages));
        $this->assertEmpty($messages);
    }

    /**
     * Test that loading messages from ini files work.
     */
    public function testIniBundles() {
        $bundle = new MessageBundle(['bundle' => 'ex']);
        $bundle->addReader(new IniReader())->addPath('common', TEMP_DIR . '/messages/{bundle}/');

        $messages = $bundle->loadResource('common', 'default');

        $this->assertTrue(is_array($messages));
        $this->assertEquals(['titon', 'test', 'type', 'format'], array_keys($messages));
        $this->assertEquals([
            'titon' => 'Titon',
            'test' => 'Test',
            'type' => 'ini',
            'format' => '{0,number,integer} health, {1,number,integer} energy, {2,number} damage'
        ], $messages);

        $messages = $bundle->loadResource('common', 'doesntExist');

        $this->assertTrue(is_array($messages));
        $this->assertEmpty($messages);
    }

    /**
     * Test that loading messages from json files work.
     */
    public function testJsonBundles() {
        $bundle = new MessageBundle(['bundle' => 'ex']);
        $bundle->addReader(new JsonReader())->addPath('common', TEMP_DIR . '/messages/{bundle}/');

        $messages = $bundle->loadResource('common', 'default');

        $this->assertTrue(is_array($messages));
        $this->assertEquals(['titon', 'test', 'type', 'format'], array_keys($messages));
        $this->assertEquals([
            'titon' => 'Titon',
            'test' => 'Test',
            'type' => 'json',
            'format' => '{0,number,integer} health, {1,number,integer} energy, {2,number} damage'
        ], $messages);

        $messages = $bundle->loadResource('common', 'doesntExist');

        $this->assertTrue(is_array($messages));
        $this->assertEmpty($messages);
    }

    /**
     * Test that loading messages from xml files work.
     */
    public function testXmlBundles() {
        $bundle = new MessageBundle(['bundle' => 'ex']);
        $bundle->addReader(new XmlReader())->addPath('common', TEMP_DIR . '/messages/{bundle}/');

        $messages = $bundle->loadResource('common', 'default');

        $this->assertTrue(is_array($messages));
        $this->assertEquals(['titon', 'test', 'type', 'format'], array_keys($messages));
        $this->assertEquals([
            'titon' => 'Titon',
            'test' => 'Test',
            'type' => 'xml',
            'format' => '{0,number,integer} health, {1,number,integer} energy, {2,number} damage'
        ], $messages);

        $messages = $bundle->loadResource('common', 'doesntExist');

        $this->assertTrue(is_array($messages));
        $this->assertEmpty($messages);
    }

    /**
     * Test that loading messages from xml files work.
     */
    public function testPoBundles() {
        $bundle = new MessageBundle(['bundle' => 'ex']);
        $bundle->addReader(new PoReader())->addPath('common', TEMP_DIR . '/messages/{bundle}/LC_MESSAGES/');

        $messages = $bundle->loadResource('common', 'default');

        $this->assertTrue(is_array($messages));
        $this->assertEquals(['basic', 'multiline', 'plurals', 'context', 'format'], array_keys($messages));
        $this->assertEquals([
            'basic' => 'Basic message',
            'multiline' => "Multiline message\nMore message here\nAnd more message again",
            'plurals' => ['plural', 'plurals'],
            'context' => 'Context message',
            'format' => '{0,number,integer} health, {1,number,integer} energy, {2,number} damage'
        ], $messages);

        $messages = $bundle->loadResource('common', 'doesntExist');

        $this->assertTrue(is_array($messages));
        $this->assertEmpty($messages);
    }

}
