# G11n v0.11.2 [![Build Status](https://travis-ci.org/titon/g11n.png)](https://travis-ci.org/titon/g11n) #

Handles the localization (l10n) and internationalization (i18n) of your application, also known as
globalization (g11n). The g11n package provides a robust and extensible way to localize messages,
provide locale aware patterns and rules, translate strings, and much more.

The `G11n` class manages locales, translators, and the initialization via HTTP accept headers.

```php
use Titon\G11n\Locale;

$g11n = Titon\G11n\G11n:registry();
$g11n->addLocale(new Locale('en'));
$g11n->addLocale(new Locale('fr'));
$g11n->setFallback('en');
$g11n->setTranslator(new Titon\G11n\Translator\MessageTranslator())
    ->setReader(new Titon\Io\Reader\PhpReader())
    ->setStorage(new Titon\Cache\Storage\MemcacheStorage());
$g11n->initialize();
```

If the route package is used in combination with the g11n package,
automatic route resolving and URL locale prefixing can be achieved.
The g11n `LocaleRoute` should also be used in place of the default `Route`.

```php
$router->on('g11n', $g11n);
```

Fetching and translating strings is as easy as calling a single function.
Of course the messages will have to exist in the resource lookup path.

```php
$message = $g11n->translate('domain.catalog.id');
// or
$message = msg('domain.catalog.id');
// or
$message = __('id', 'catalog', 'domain');
```

### Features ###

* `G11n` - L10n and I18n management
* `Locale` - Locale configuration
* `Translator` - Message bundle parsing
* `Utility` - Extends Inflector, Format, Number and Validator
* `Route` - Locale aware routing

### Dependencies ###

* `Common`
* `Event`
* `IO`
* `Cache` (optional for Translator)

### Requirements ###

* PHP 5.4.0
    * Intl
    * Multibyte
    * Gettext (for GettextTranslator)

### To Do ###

* Test gettext functionality
* Implement TranslatableBehavior