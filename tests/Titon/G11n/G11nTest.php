<?php
/**
 * @copyright   2010-2013, The Titon Project
 * @license     http://opensource.org/licenses/bsd-license.php
 * @link        http://titon.io
 */

namespace Titon\G11n;

use Titon\G11n\G11n;
use Titon\G11n\Locale;
use Titon\G11n\Translator\MessageTranslator;
use Titon\Test\TestCase;
use \Exception;

/**
 * Test class for Titon\G11n\G11n.
 *
 * @property \Titon\G11n\G11n $object
 */
class G11nTest extends TestCase {

    /**
     * This method is called before a test is executed.
     */
    protected function setUp() {
        parent::setUp();

        $this->object = new G11n();

        foreach (['ex_VA', 'ex_IN', 'ex_FM', 'no'] as $code) {
            $this->object->addLocale(new Locale($code));
        }

        $this->object->setFallback('ex');
        $this->object->setTranslator(new MessageTranslator());
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
            $this->object->initialize();

            $this->assertEquals($localeId, $this->object->cascade());

            // Delete the cache since we are doing repeat checks
            // This wouldn't happen in production
            $this->object->removeCache('Titon\G11n\G11n::cascade');
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
            $this->object->initialize();

            $current = $this->object->current();

            $this->assertInstanceOf('Titon\G11n\Locale', $current);
            $this->assertEquals($localeId, $current->getCode());
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
        $this->object->setFallback('ex-va');
        $this->assertEquals('ex_VA', $this->object->getFallback()->getCode());

        $this->object->setFallback('ex-IN');
        $this->assertEquals('ex_IN', $this->object->getFallback()->getCode());

        $this->object->setFallback('ex_FM');
        $this->assertEquals('ex_FM', $this->object->getFallback()->getCode());

        try {
            $this->object->setFallback('fakeKey');
            $this->assertTrue(false);

        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Test that all locales are setup correctly and reference the correct bundle class.
     */
    public function testGetLocales() {
        $locales = $this->object->getLocales();

        $this->assertEquals(5, count($locales));
        $this->assertEquals(['ex-va', 'ex', 'ex-in', 'ex-fm', 'no'], array_keys($locales));

        foreach ($locales as $locale) {
            $this->assertInstanceOf('Titon\G11n\Locale', $locale);
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
            $this->object->initialize();

            $this->assertTrue($this->object->is($localeId[0]));
            $this->assertTrue($this->object->is($localeId[1]));
        }
    }

    /**
     * Test that setting a locale key/ID applies the correct bundle.
     */
    public function testUseLocale() {
        $this->object->useLocale('ex');
        $this->assertEquals('ex', $this->object->current()->getCode());

        $this->object->useLocale('ex_VA');
        $this->assertEquals('ex_VA', $this->object->current()->getCode());

        $this->object->useLocale('ex-IN');
        $this->assertEquals('ex_IN', $this->object->current()->getCode());

        $this->object->useLocale('ex_fm');
        $this->assertEquals('ex_FM', $this->object->current()->getCode());

        try {
            $this->object->useLocale('fakeKey');
            $this->assertTrue(false);

        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }

}
