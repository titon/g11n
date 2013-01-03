<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\G11n;

use Titon\G11n\G11n;
use Titon\G11n\Translator\MessageTranslator;
use \Exception;

/**
 * Test class for Titon\G11n\G11n.
 */
class G11nTest extends \PHPUnit_Framework_TestCase {

	/**
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		G11n::setup('ex-va');
		G11n::setup('ex-fm');
		G11n::setup('ex-in');
		G11n::setup('no'); // Needs 2 types of locales
		G11n::fallbackAs('ex');
		G11n::setTranslator(new MessageTranslator());
	}

	/**
	 * Test that formatting locale keys return the correct formats.
	 */
	public function testCanonicalize() {
		$this->assertEquals('en-us', G11n::canonicalize('en-us', G11n::FORMAT_1));
		$this->assertEquals('en-US', G11n::canonicalize('en-us', G11n::FORMAT_2));
		$this->assertEquals('en_US', G11n::canonicalize('en-us', G11n::FORMAT_3));

		$this->assertEquals('en-us', G11n::canonicalize('en-US', G11n::FORMAT_1));
		$this->assertEquals('en-US', G11n::canonicalize('en-US', G11n::FORMAT_2));
		$this->assertEquals('en_US', G11n::canonicalize('en-US', G11n::FORMAT_3));

		$this->assertEquals('en-us', G11n::canonicalize('en_US', G11n::FORMAT_1));
		$this->assertEquals('en-US', G11n::canonicalize('en_US', G11n::FORMAT_2));
		$this->assertEquals('en_US', G11n::canonicalize('en_US', G11n::FORMAT_3));
	}

	/**
	 * Test that cascade returns a descending list of locale IDs.
	 */
	public function testCascade() {
		$httpAccepts = [
			'ex-no,ex;q=0.5' => ['ex'],
			'ex-in,ex;q=0.5' => ['ex_IN', 'ex'],
			'ex-va,ex;q=0.5' => ['ex_VA', 'ex'],
			'ex-fm,ex;q=0.5' => ['ex_FM', 'ex'],
			'foobar' => ['ex'] // Wont match and will use the fallback
		];

		foreach ($httpAccepts as $httpAccept => $localeId) {
			$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $httpAccept;
			G11n::initialize();

			$this->assertEquals($localeId, G11n::cascade());

			// Delete the cache since we are doing repeat checks
			// This wouldn't happen in production
			G11n::removeCache('Titon\G11n\G11n::cascade');
		}
	}

	/**
	 * Test that composing locale tags return the correctly formatted key.
	 */
	public function testCompose() {
		$this->assertEquals('en', G11n::compose([
			'language' => 'en'
		]));

		$this->assertEquals('en_US', G11n::compose([
			'language' => 'en',
			'region' => 'US'
		]));

		$this->assertEquals('en_Hans_US', G11n::compose([
			'language' => 'en',
			'region' => 'US',
			'script' => 'Hans'
		]));

		$this->assertEquals('en_Hans_US_NEDIS_x_prv1', G11n::compose([
			'language' => 'en',
			'region' => 'US',
			'script' => 'Hans',
			'variant0' => 'NEDIS',
			'private0' => 'prv1'
		]));
	}

	/**
	 * Test that the correct locale bundle is set while parsing the HTTP accept language header.
	 */
	public function testCurrent() {
		$httpAccepts = [
			'ex-no,ex;q=0.5' => 'ex',
			'ex-in,ex;q=0.5' => 'ex_IN',
			'ex-va,ex;q=0.5' => 'ex_VA',
			'ex-fm,ex;q=0.5' => 'ex_FM',
			'foobar' => 'ex' // Wont match and will use the fallback
		];

		foreach ($httpAccepts as $httpAccept => $localeId) {
			$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $httpAccept;
			G11n::initialize();

			$current = G11n::current();

			$this->assertInstanceOf('Titon\Io\Bundle\LocaleBundle', $current);
			$this->assertEquals($localeId, $current->getLocale('id'));
		}
	}

	/**
	 * Test that decomposing a locale returns the correct array of tags.
	 */
	public function testDecompose() {
		$this->assertEquals([
			'language' => 'en'
		], G11n::decompose('en'));

		$this->assertEquals([
			'language' => 'en',
			'region' => 'US'
		], G11n::decompose('en_US'));

		$this->assertEquals([
			'language' => 'en',
			'region' => 'US',
			'script' => 'Hans'
		], G11n::decompose('en_Hans_US'));

		$this->assertEquals([
			'language' => 'en',
			'script' => 'Hans',
			'region' => 'US',
			'variant0' => 'NEDIS',
			'private0' => 'prv1'
		], G11n::decompose('en_Hans_US_nedis_x_prv1'));
	}

	/**
	 * Test that setting fallbacks work.
	 */
	public function testFallback() {
		G11n::fallbackAs('ex-va');
		$this->assertEquals('ex_VA', G11n::getFallback()->getLocale('id'));

		G11n::fallbackAs('ex-IN');
		$this->assertEquals('ex_IN', G11n::getFallback()->getLocale('id'));

		G11n::fallbackAs('ex_FM');
		$this->assertEquals('ex_FM', G11n::getFallback()->getLocale('id'));

		try {
			G11n::fallbackAs('fakeKey');
			$this->assertTrue(false);

		} catch (Exception $e) {
			$this->assertTrue(true);
		}
	}

	/**
	 * Test that all locales are setup correctly and reference the correct bundle class.
	 */
	public function testGetLocales() {
		$bundles = G11n::getLocales();

		$this->assertEquals(5, count($bundles));
		$this->assertEquals(['ex-va', 'ex', 'ex-fm', 'ex-in', 'no'], array_keys($bundles));

		foreach ($bundles as $bundle) {
			$this->assertInstanceOf('Titon\Io\Bundle\LocaleBundle', $bundle);
		}
	}

	/**
	 * Test that is matches a given locale key or locale id to the current bundle.
	 */
	public function testIs() {
		$httpAccepts = [
			'ex-no,ex;q=0.5' => ['ex', 'ex'],
			'ex-in,ex;q=0.5' => ['ex_IN', 'ex-in'],
			'ex-va,ex;q=0.5' => ['ex_VA', 'ex-va'],
			'ex-fm,ex;q=0.5' => ['ex_FM', 'ex-fm'],
			'foobar' => ['ex', 'ex'] // Wont match and will use the fallback
		];

		foreach ($httpAccepts as $httpAccept => $localeId) {
			$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $httpAccept;
			G11n::initialize();

			$this->assertTrue(G11n::is($localeId[0]));
			$this->assertTrue(G11n::is($localeId[1]));
		}
	}

	/**
	 * Test that setting a locale key/ID applies the correct bundle.
	 */
	public function testSet() {
		G11n::set('ex');
		$this->assertEquals('ex', G11n::current()->getLocale('id'));

		G11n::set('ex_VA');
		$this->assertEquals('ex_VA', G11n::current()->getLocale('id'));

		G11n::set('ex-IN');
		$this->assertEquals('ex_IN', G11n::current()->getLocale('id'));

		G11n::set('ex_fm');
		$this->assertEquals('ex_FM', G11n::current()->getLocale('id'));

		try {
			G11n::set('fakeKey');
			$this->assertTrue(false);

		} catch (Exception $e) {
			$this->assertTrue(true);
		}
	}

}
