<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n;

use Titon\G11n\G11n;
use Titon\G11n\Locale;
use Titon\Test\TestCase;
use \Exception;

/**
 * Test class for Titon\G11n\Locale.
 */
class LocaleTest extends TestCase {

	/**
	 * Parent instance.
	 *
	 * @var \Titon\G11n\Locale
	 */
	public $parent;

	/**
	 * Formats instance.
	 *
	 * @var \Titon\G11n\Locale
	 */
	public $formats;

	/**
	 * Inflections instance.
	 *
	 * @var \Titon\G11n\Locale
	 */
	public $inflections;

	/**
	 * Validations instance.
	 *
	 * @var \Titon\G11n\Locale
	 */
	public $validations;

	/**
	 * Setup bundles for specific conditions.
	 */
	protected function setUp() {
		parent::setUp();

		$this->parent = new Locale('ex');
		$this->parent->initialize();

		$this->formats = new Locale('ex_FM');
		$this->formats->initialize();

		$this->inflections = new Locale('ex_IN');
		$this->inflections->initialize();

		$this->validations = new Locale('ex_VA');
		$this->validations->initialize();
	}

	/**
	 * Test that the locale meta data is parsed correctly.
	 * If the bundle has a parent, also test that the values between the two are merged correctly.
	 */
	public function testGetLocale() {
		$parent = $this->parent->config->all();
		$formats = $this->formats->config->all();
		$inflections = $this->inflections->config->all();
		$validations = $this->validations->config->all();

		// Parent
		$this->assertTrue(is_array($parent));
		$this->assertArraysEqual([
			'code' => 'ex',
			'language' => 'ex',
			'iso2' => 'ex',
			'iso3' => 'exp',
			'timezone' => '',
			'title' => 'Example Parent',
			'initialize' => false
		], $parent);

		// Formats
		$this->assertTrue(is_array($formats));
		$this->assertArraysEqual([
			'code' => 'ex_FM',
			'language' => 'ex',
			'region' => 'FM',
			'iso2' => 'ex',
			'iso3' => ['exf', 'frm'],
			'timezone' => '',
			'title' => 'Example for Formats',
			'parent' => 'ex',
			'initialize' => false
		], $formats);

		// Inflections
		$this->assertTrue(is_array($inflections));
		$this->assertArraysEqual([
			'code' => 'ex_IN',
			'language' => 'ex',
			'region' => 'IN',
			'iso2' => 'ex',
			'iso3' => 'inf',
			'timezone' => '',
			'title' => 'Example for Inflections',
			'parent' => 'ex',
			'initialize' => false
		], $inflections);

		// Validations
		$this->assertTrue(is_array($validations));
		$this->assertArraysEqual([
			'code' => 'ex_VA',
			'language' => 'ex',
			'region' => 'VA',
			'iso2' => 'ex',
			'iso3' => 'val',
			'timezone' => '',
			'title' => 'Example for Validations',
			'parent' => 'ex',
			'initialize' => false
		], $validations);

		// By key
		$this->assertEquals('ex', $this->parent->config->code);
		$this->assertEquals('ex', $this->parent->config->iso2);
		$this->assertEquals('exp', $this->parent->config->iso3);
		$this->assertEquals('', $this->parent->config->timezone);

		try {
			$this->parent->config->fakeKey;
			$this->assertTrue(false);

		} catch (Exception $e) {
			$this->assertTrue(true);
		}
	}

	/**
	 * Test that the formatting rules are parsed correctly.
	 * If the bundle has a parent, also test that the values between the two are merged correctly.
	 */
	public function testGetFormatPatterns() {
		$parent = $this->parent->getFormatPatterns();
		$formats = $this->formats->getFormatPatterns();
		$inflections = $this->inflections->getFormatPatterns();
		$validations = $this->validations->getFormatPatterns();

		$parentFormat = [
			'date' => 'ex',
			'time' => 'ex',
			'datetime' => 'ex',
			'pluralForms' => 2,
			'pluralRule' => function() { }
		];

		// Parent
		$this->assertTrue(is_array($parent));
		$this->assertEquals($parentFormat, $parent);

		// Formats
		$this->assertTrue(is_array($formats));
		$this->assertEquals([
			'date' => 'ex_FM',
			'time' => 'ex',
			'datetime' => 'ex',
			'pluralForms' => 3,
			'pluralRule' => function() { }
		], $formats);

		// Inflections
		$this->assertTrue(is_array($inflections));
		$this->assertEquals($parentFormat, $inflections);

		// Validations
		$this->assertTrue(is_array($validations));
		$this->assertEquals($parentFormat, $validations);

		// By key
		$this->assertEquals('ex_FM', $this->formats->getFormatPatterns('date'));
		$this->assertEquals('ex', $this->formats->getFormatPatterns('time'));
		$this->assertEquals('ex', $this->formats->getFormatPatterns('datetime'));
		$this->assertEquals(3, $this->formats->getFormatPatterns('pluralForms'));
		$this->assertEquals(null, $this->formats->getFormatPatterns('fakeKey'));
	}

	/**
	 * Test that the inflection rules are parsed correctly.
	 * If the bundle has a parent, also test that the values between the two are merged correctly.
	 */
	public function testGetInflectionRules() {
		$parent = $this->parent->getInflectionRules();
		$formats = $this->formats->getInflectionRules();
		$inflections = $this->inflections->getInflectionRules();
		$validations = $this->validations->getInflectionRules();

		$parentInflections = [
			'irregular' => ['ex' => 'irregular'],
			'uninflected' => ['ex'],
			'plural' => ['ex' => 'plural'],
			'singular' => ['ex' => 'singular']
		];

		// Parent
		$this->assertTrue(is_array($parent));
		$this->assertEquals($parentInflections, $parent);

		// Formats
		$this->assertTrue(is_array($formats));
		$this->assertEquals($parentInflections, $formats);

		// Inflections
		$this->assertTrue(is_array($inflections));
		$this->assertEquals([
			'irregular' => ['ex_IN' => 'irregular'],
			'uninflected' => ['ex'],
			'plural' => ['ex_IN' => 'plural'],
			'singular' => ['ex_IN' => 'singular']
		], $inflections);

		// Validations
		$this->assertTrue(is_array($validations));
		$this->assertEquals($parentInflections, $validations);

		// By key
		$this->assertEquals(['ex_IN' => 'irregular'], $this->inflections->getInflectionRules('irregular'));
		$this->assertEquals(['ex_IN' => 'plural'], $this->inflections->getInflectionRules('plural'));
		$this->assertEquals(['ex_IN' => 'singular'], $this->inflections->getInflectionRules('singular'));
		$this->assertEquals(['ex'], $this->inflections->getInflectionRules('uninflected'));
		$this->assertEquals(null, $this->inflections->getInflectionRules('fakeKey'));
	}

	/**
	 * Test that the validation rules are parsed correctly.
	 * If the bundle has a parent, also test that the values between the two are merged correctly.
	 */
	public function testGetValidationRules() {
		$parent = $this->parent->getValidationRules();
		$formats = $this->formats->getValidationRules();
		$inflections = $this->inflections->getValidationRules();
		$validations = $this->validations->getValidationRules();

		$parentValidations = [
			'phone' => 'ex',
			'postalCode' => 'ex',
			'ssn' => 'ex'
		];

		// Parent
		$this->assertTrue(is_array($parent));
		$this->assertEquals($parentValidations, $parent);

		// Formats
		$this->assertTrue(is_array($formats));
		$this->assertEquals($parentValidations, $formats);

		// Inflections
		$this->assertTrue(is_array($inflections));
		$this->assertEquals($parentValidations, $inflections);

		// Validations
		$this->assertTrue(is_array($validations));
		$this->assertEquals([
			'phone' => 'ex_VA',
			'postalCode' => 'ex',
			'ssn' => 'ex_VA'
		], $validations);

		// By key
		$this->assertEquals('ex_VA', $this->validations->getValidationRules('phone'));
		$this->assertEquals('ex_VA', $this->validations->getValidationRules('ssn'));
		$this->assertEquals('ex', $this->validations->getValidationRules('postalCode'));
		$this->assertEquals(null, $this->validations->getValidationRules('fakeKey'));
	}

	/**
	 * Test that parent bundles are loaded.
	 */
	public function testGetParentLocale() {
		$this->assertEquals(null, $this->parent->getParentLocale());
		$this->assertInstanceOf('Titon\G11n\Locale', $this->formats->getParentLocale());
		$this->assertInstanceOf('Titon\G11n\Locale', $this->inflections->getParentLocale());
		$this->assertInstanceOf('Titon\G11n\Locale', $this->validations->getParentLocale());
	}

}
