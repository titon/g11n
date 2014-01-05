<?php
/**
 * @copyright   2010-2013, The Titon Project
 * @license     http://opensource.org/licenses/bsd-license.php
 * @link        http://titon.io
 */

namespace Titon\G11n;

use Titon\Test\TestCase;
use Titon\Test\Stub\TranslatorStub;
use \Exception;

/**
 * Test class for Titon\G11n\Translator.
 */
class TranslatorTest extends TestCase {

    /**
     * Test that parsing a translation key returns the correct module, catalog and id.
     */
    public function testParseKey() {
        $object = new TranslatorStub();

        $this->assertEquals(['module', 'catalog', 'id'], $object->parseKey('module.catalog.id'));
        $this->assertEquals(['module', 'catalog', 'id.multi.part'], $object->parseKey('module.catalog.id.multi.part'));
        $this->assertEquals(['module', 'catalog', 'id-dashed'], $object->parseKey('module.catalog.id-dashed'));
        $this->assertEquals(['module', 'catalog', 'idspecial27304characters'], $object->parseKey('module.catalog.id * special )*&2)*7304 characters'));
        $this->assertEquals(['Module', 'Catalog', 'id.CamelCase'], $object->parseKey('Module.Catalog.id.CamelCase'));
        $this->assertEquals(['m', 'c', 'i'], $object->parseKey('m.c.i'));
        $this->assertEquals([1, 2, 3], $object->parseKey('1.2.3'));

        $this->assertEquals(['core', 'catalog', 'id'], $object->parseKey('catalog.id'));
        $this->assertEquals(['core', 'root', 'id'], $object->parseKey('root.id'));
        $this->assertEquals(['core', 'test', 'key'], $object->parseKey('test.key'));
        $this->assertEquals(['core', 1, 2], $object->parseKey('1.2'));

        try {
            $object->parseKey('noModuleOrCatalog');
            $object->parseKey('not-using-correct-notation');

            $this->assertTrue(false);

        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

}