<?php
/**
 * @copyright   2010-2013, The Titon Project
 * @license     http://opensource.org/licenses/bsd-license.php
 * @link        http://titon.io
 */

namespace Titon\G11n\Utility;

use Titon\Common\Registry;
use Titon\G11n\G11n;
use Titon\G11n\Locale;
use Titon\G11n\Utility\Inflector;
use Titon\Test\TestCase;
use \Exception;

/**
 * Test class for Titon\G11n\Utility\Inflector.
 *
 * @property \Titon\G11n\G11n $object
 */
class InflectorTest extends TestCase {

    /**
     * This method is called before a test is executed.
     */
    protected function setUp() {
        parent::setUp();

        $this->object = Registry::factory('Titon\G11n\G11n');
        $this->object->addLocale(new Locale('en'));
        $this->object->useLocale('en');
    }

    /**
     * Test that ordinal() returns the number with the proper suffix.
     */
    public function testOrdinal() {
        $this->assertEquals('1st', Inflector::ordinal(1));
        $this->assertEquals('2nd', Inflector::ordinal(2));
        $this->assertEquals('3rd', Inflector::ordinal(3));
        $this->assertEquals('4th', Inflector::ordinal(4));
        $this->assertEquals('5th', Inflector::ordinal(5));

        // teens
        $this->assertEquals('12th', Inflector::ordinal(12));
        $this->assertEquals('15th', Inflector::ordinal(15));
        $this->assertEquals('18th', Inflector::ordinal(18));
        $this->assertEquals('20th', Inflector::ordinal(20));

        // high numbers
        $this->assertEquals('91st', Inflector::ordinal(91));
        $this->assertEquals('342nd', Inflector::ordinal(342));
        $this->assertEquals('8534th', Inflector::ordinal(8534));
        $this->assertEquals('92343rd', Inflector::ordinal(92343));
        $this->assertEquals('678420th', Inflector::ordinal(678420));

        // casting
        $this->assertEquals('11th', Inflector::ordinal('11th'));
        $this->assertEquals('98th', Inflector::ordinal('98s'));
        $this->assertEquals('438th', Inflector::ordinal('438lbs'));
        $this->assertEquals('-12th', Inflector::ordinal('-12$'));
    }

    /**
     * Test that pluralize() returns a plural form, respecting irregularities and other locale specific rules.
     */
    public function testPluralize() {
        // irregular
        $this->assertEquals('opuses', Inflector::pluralize('opus'));
        $this->assertEquals('penises', Inflector::pluralize('penis'));
        $this->assertEquals('loaves', Inflector::pluralize('loaf'));
        $this->assertEquals('mythoi', Inflector::pluralize('mythos'));
        $this->assertEquals('men', Inflector::pluralize('man'));

        // uninflected
        $this->assertEquals('information', Inflector::pluralize('information'));
        $this->assertEquals('corps', Inflector::pluralize('corps'));
        $this->assertEquals('gallows', Inflector::pluralize('gallows'));
        $this->assertEquals('maltese', Inflector::pluralize('maltese'));
        $this->assertEquals('rice', Inflector::pluralize('rice'));

        // plural
        $this->assertEquals('matrices', Inflector::pluralize('matrix'));
        $this->assertEquals('buses', Inflector::pluralize('bus'));
        $this->assertEquals('perches', Inflector::pluralize('perch'));
        $this->assertEquals('people', Inflector::pluralize('person'));
        $this->assertEquals('bananas', Inflector::pluralize('banana'));

        // already plural
        $this->assertEquals('opuses', Inflector::pluralize('opuses'));
        $this->assertEquals('penises', Inflector::pluralize('penises'));
        $this->assertEquals('loaves', Inflector::pluralize('loaves'));
        $this->assertEquals('mythoi', Inflector::pluralize('mythoi'));
        $this->assertEquals('men', Inflector::pluralize('men'));
    }

    /**
     * Test that singularize() returns a single form, respecting irregularities and other locale specific rules.
     */
    public function testSingularize() {
        // irregular
        $this->assertEquals('atlas', Inflector::singularize('atlases'));
        $this->assertEquals('corpus', Inflector::singularize('corpuses'));
        $this->assertEquals('octopus', Inflector::singularize('octopuses'));
        $this->assertEquals('ox', Inflector::singularize('oxen'));
        $this->assertEquals('goose', Inflector::singularize('geese'));

        // uninflected
        $this->assertEquals('money', Inflector::singularize('money'));
        $this->assertEquals('flounder', Inflector::singularize('flounder'));
        $this->assertEquals('moose', Inflector::singularize('moose'));
        $this->assertEquals('species', Inflector::singularize('species'));
        $this->assertEquals('wildebeest', Inflector::singularize('wildebeest'));

        // singular
        $this->assertEquals('quiz', Inflector::singularize('quizzes'));
        $this->assertEquals('alias', Inflector::singularize('aliases'));
        $this->assertEquals('shoe', Inflector::singularize('shoes'));
        $this->assertEquals('person', Inflector::singularize('people'));
        $this->assertEquals('apple', Inflector::singularize('apples'));

        // already singular
        $this->assertEquals('atlas', Inflector::singularize('atlas'));
        $this->assertEquals('corpus', Inflector::singularize('corpus'));
        $this->assertEquals('octopus', Inflector::singularize('octopus'));
        $this->assertEquals('ox', Inflector::singularize('ox'));
        $this->assertEquals('goose', Inflector::singularize('goose'));
    }

    /**
     * Test that transliterate() replaces non-ASCII chars.
     */
    public function testTransliterate() {
        $this->assertEquals('Ingles', Inflector::transliterate('Inglés'));
        $this->assertEquals('Uber', Inflector::transliterate('Über'));
    }

    /**
     * Test that className() returns a singular camel cased form.
     */
    public function testClassName() {
        $this->assertEquals('CamelCase', Inflector::className('camel Cases'));
        $this->assertEquals('StudlyCase', Inflector::className('StuDly CaSes'));
        $this->assertEquals('TitleCase', Inflector::className('Title Cases'));
        $this->assertEquals('NormalCase', Inflector::className('Normal cases'));
        $this->assertEquals('Lowercase', Inflector::className('lowercases'));
        $this->assertEquals('Uppercase', Inflector::className('UPPERCASEs'));
        $this->assertEquals('UnderScore', Inflector::className('under_scores'));
        $this->assertEquals('DashE', Inflector::className('dash-es'));
        $this->assertEquals('123Number', Inflector::className('123 numbers'));
        $this->assertEquals('WithExtxml', Inflector::className('with EXT.xml'));
        $this->assertEquals('LotsOfWhiteSpace', Inflector::className('lots  of     white space'));
    }

    /**
     * Test that tableName() returns a plural lower-camel-cased form.
     */
    public function testTableName() {
        $this->assertEquals('camelCases', Inflector::tableName('camel Case'));
        $this->assertEquals('studlyCases', Inflector::tableName('StuDly CaSe'));
        $this->assertEquals('titleCases', Inflector::tableName('Title Case'));
        $this->assertEquals('normalCases', Inflector::tableName('Normal case'));
        $this->assertEquals('lowercases', Inflector::tableName('lowercase'));
        $this->assertEquals('uppercases', Inflector::tableName('UPPERCASE'));
        $this->assertEquals('underScores', Inflector::tableName('under_score'));
        $this->assertEquals('dashEs', Inflector::tableName('dash-es'));
        $this->assertEquals('123Numbers', Inflector::tableName('123 numbers'));
        $this->assertEquals('withExtxmls', Inflector::tableName('with EXT.xml'));
        $this->assertEquals('lotsOfWhiteSpaces', Inflector::tableName('lots  of     white space'));
    }

}
