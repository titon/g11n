<?php
/**
 * @copyright   2010-2013, The Titon Project
 * @license     http://opensource.org/licenses/bsd-license.php
 * @link        http://titon.io
 */

namespace Titon\Route;

use Titon\Common\Registry;
use Titon\G11n\G11n;
use Titon\G11n\Locale;
use Titon\G11n\Route\LocaleRoute;
use Titon\Test\TestCase;

/**
 * Test class for Titon\G11n\Route\LocaleRoute.
 */
class LocaleRouteTest extends TestCase {

    /**
     * Reset g11n.
     */
    protected function tearDown() {
        parent::tearDown();

        Registry::flush();
    }

    /**
     * Test that compile() appends the correct locale pattern.
     */
    public function testCompile() {
        $g11n = Registry::factory('Titon\G11n\G11n');
        $g11n->addLocale(new Locale('en_US'));
        $g11n->useLocale('en_US');

        $moduleControllerActionExt = new LocaleRoute('mcae', '/{module}/{controller}/{action}.{ext}');
        $moduleControllerAction = new LocaleRoute('mca', '/{module}/{controller}/{action}');
        $moduleController = new LocaleRoute('mc', '/{module}/{controller}');
        $module = new LocaleRoute('m', '/{module}');
        $rootStatic = new LocaleRoute('r', '/', [], ['static' => true]);
        $root = new LocaleRoute('r', '/');

        $this->assertEquals('/^\/([a-z]{2}(?:-[a-z]{2})?)\/([a-z\_\-\+]+)\/([a-z\_\-\+]+)\/([a-z\_\-\+]+)\.([a-z\_\-\+]+)(.*)?/i', $moduleControllerActionExt->compile());
        $this->assertEquals('/^\/([a-z]{2}(?:-[a-z]{2})?)\/([a-z\_\-\+]+)\/([a-z\_\-\+]+)\/([a-z\_\-\+]+)(.*)?/i', $moduleControllerAction->compile());
        $this->assertEquals('/^\/([a-z]{2}(?:-[a-z]{2})?)\/([a-z\_\-\+]+)\/([a-z\_\-\+]+)(.*)?/i', $moduleController->compile());
        $this->assertEquals('/^\/([a-z]{2}(?:-[a-z]{2})?)\/([a-z\_\-\+]+)(.*)?/i', $module->compile());
        $this->assertEquals('/^\/<locale>(.*)?/i', $rootStatic->compile());
        $this->assertEquals('/^\/([a-z]{2}(?:-[a-z]{2})?)(.*)?/i', $root->compile());

        $multi = new LocaleRoute('anw', '{alpha}/[numeric]/(wildcard)/');

        $patterns = new LocaleRoute('al', '/<alnum>/<locale>', [], [
            'patterns' => [
                'alnum' => LocaleRoute::ALNUM,
                'locale' => '([a-z]{2}(?:-[a-z]{2})?)'
            ]
        ]);

        $withPattern = new LocaleRoute('lawna', '/<locale>/{alpha}/(wildcard)/[numeric]/{alnum}', [], [
            'patterns' => [
                'alnum' => LocaleRoute::ALNUM,
                'locale' => '([a-z]{2}(?:-[a-z]{2})?)'
            ]
        ]);

        $withoutPattern = new LocaleRoute('lawna', '/<locale>/{alpha}/(wildcard)/[numeric]/{alnum}', [], [
            'patterns' => [
                'alnum' => LocaleRoute::ALNUM
            ]
        ]);

        $this->assertEquals('/^\/([a-z]{2}(?:-[a-z]{2})?)\/([a-z\_\-\+]+)\/([0-9]+)\/(.*)(.*)?/i', $multi->compile());
        $this->assertEquals('/^\/([a-z]{2}(?:-[a-z]{2})?)\/([a-z0-9\_\-\+]+)\/([a-z]{2}(?:-[a-z]{2})?)(.*)?/i', $patterns->compile());
        $this->assertEquals('/^\/([a-z]{2}(?:-[a-z]{2})?)\/([a-z\_\-\+]+)\/(.*)\/([0-9]+)\/([a-z\_\-\+]+)(.*)?/i', $withPattern->compile());
        $this->assertEquals('/^\/([a-z]{2}(?:-[a-z]{2})?)\/([a-z\_\-\+]+)\/(.*)\/([0-9]+)\/([a-z\_\-\+]+)(.*)?/i', $withoutPattern->compile());
    }

    /**
     * Test that isMatch() returns a valid response.
     * Test that getParam() returns a single value, or all values.
     * Test that url() returns the current URL.
     */
    public function testIsMatchAndParamAndUrl() {
        $g11n = Registry::factory('Titon\G11n\G11n');
        $g11n->addLocale(new Locale('en_US'));
        $g11n->useLocale('en_US');

        $url = '/en-us/blog/2012/02/26';
        $route = new LocaleRoute('bymd', '/blog/[year]/[month]/[day]', [
            'module' => 'blog',
            'controller' => 'api',
            'action' => 'archives',
            'custom' => 'value'
        ]);

        $this->assertTrue($route->isMatch($url));
        $this->assertEquals([
            'ext' => '',
            'module' => 'blog',
            'controller' => 'api',
            'action' => 'archives',
            'query' => [],
            'args' => [],
            'year' => 2012,
            'month' => 2,
            'day' => 26,
            'custom' => 'value',
            'locale' => 'en-us'
        ], $route->getParams());

        $this->assertEquals('blog', $route->getParam('module'));
        $this->assertEquals('archives', $route->getParam('action'));
        $this->assertEquals(2012, $route->getParam('year'));
        $this->assertEquals(2, $route->getParam('month'));
        $this->assertEquals(26, $route->getParam('day'));
        $this->assertEquals('value', $route->getParam('custom'));
        $this->assertEquals(null, $route->getParam('foobar'));
        $this->assertEquals('en-us', $route->getParam('locale'));
        $this->assertEquals($url, $route->url());

        // module, controller, action
        $url = '/en/forum/topic/view/123';
        $route = new LocaleRoute('mca', '/{module}/{controller}/{action}');

        $this->assertTrue($route->isMatch($url));
        $this->assertEquals([
            'ext' => '',
            'module' => 'forum',
            'controller' => 'topic',
            'action' => 'view',
            'query' => [],
            'args' => [123],
            'locale' => 'en'
        ], $route->getParams());

        // invalid locale
        $url = '/foo-bar/forum/topic/view/123';
        $route = new LocaleRoute('mca', '/{module}/{controller}/{action}');

        $this->assertFalse($route->isMatch($url));

        // no locale
        $url = '/forum/topic/view/123';
        $route = new LocaleRoute('mca', '/{module}/{controller}/{action}');

        $this->assertFalse($route->isMatch($url));
    }

}