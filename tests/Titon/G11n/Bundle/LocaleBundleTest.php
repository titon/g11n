<?php
/**
 * @copyright   2010-2013, The Titon Project
 * @license     http://opensource.org/licenses/bsd-license.php
 * @link        http://titon.io
 */

namespace Titon\G11n\Bundle;

use Titon\G11n\Bundle\LocaleBundle;
use Titon\Test\TestCase;

/**
 * Test class for Titon\G11n\Bundle\LocaleBundle.
 *
 * @property \Titon\G11n\Bundle\LocaleBundle $object
 */
class LocaleBundleTest extends TestCase {

    /**
     * Setup bundles for specific conditions.
     */
    protected function setUp() {
        parent::setUp();

        $this->object = new LocaleBundle(['locale' => 'ex']);
        $this->object->addPath('common', TEMP_DIR . '/locales/{locale}/');
    }

    /**
     * Test that the locale meta data is parsed correctly.
     */
    public function testGetLocale() {
        $locale = $this->object->loadResource('common', 'locale');

        $this->assertTrue(is_array($locale));
        $this->assertArraysEqual([
            'code' => 'ex',
            'iso2' => 'ex',
            'iso3' => 'exp',
            'timezone' => '',
            'title' => 'Example Parent',
        ], $locale);
    }

    /**
     * Test that the formatting rules are parsed correctly.
     */
    public function testGetFormats() {
        $formats = $this->object->loadResource('common', 'formats');

        $this->assertTrue(is_array($formats));
        $this->assertEquals([
            'date' => 'ex',
            'time' => 'ex',
            'datetime' => 'ex',
            'pluralForms' => 2,
            'pluralRule' => function() { }
        ], $formats);
    }

    /**
     * Test that the inflection rules are parsed correctly.
     */
    public function testGetInflections() {
        $inflections = $this->object->loadResource('common', 'inflections');

        $this->assertTrue(is_array($inflections));
        $this->assertEquals([
            'irregular' => ['ex' => 'irregular'],
            'uninflected' => ['ex'],
            'plural' => ['ex' => 'plural'],
            'singular' => ['ex' => 'singular']
        ], $inflections);
    }

    /**
     * Test that the validation rules are parsed correctly.
     */
    public function testGetValidations() {
        $validations = $this->object->loadResource('common', 'validations');

        $this->assertTrue(is_array($validations));
        $this->assertEquals([
            'phone' => 'ex',
            'postalCode' => 'ex',
            'ssn' => 'ex'
        ], $validations);
    }

}
