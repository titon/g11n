# Usage #

The G11n class is used to manage Locales and translation resource bundles.
Install a Locale for each locale that you want the application to support.
Each locale must have a respective folder in the resources/locales location.

```php
use Titon\G11n\G11n;
use Titon\G11n\Locale;
use Titon\G11n\Translator\MessageTranslator;
use Titon\Io\Reader\PhpReader;
use Titon\Cache\Storage\MemcacheStorage;
use Titon\Common\Config;

$g11n = G11n::registry();

// Define resource locations
Config::set('Titon.path.resources', ['/resources/']);

// English (loads parent en)
$g11n->addLocale(new Locale('en_US'));

// German, Luxembourg (loads parent de)
$g11n->addLocale(new Locale('de_LU', [
    'timezone' => 'Europe/Berlin'
]));

// Use PHP files for messages
$g11n->setTranslator(new MessageTranslator())
    ->setReader(new PhpReader())
    ->setStorage(new MemcacheStorage());

// Set fallback
$g11n->setFallback('en');
```

After all configuration and bootstrapping has occurred, initialize the application.
The locale that gets loaded depends on the HTTP_ACCEPT_LANGUAGE header.

```php
$g11n->initialize();
```

Once a locale has been chosen, you can access it at anytime.

```php
$g11n->current()->getCode(); // en_US
$g11n->current()->getParentLocale()->getCode(); // en
```

You can also pull translated messages from the current locale.
Translated messages will cycle through all locales, starting with the current, its parent and finally the fallback.

```php
// Pull the string with the key locked, from the topics catalog, in the forum domain
// Example path: /forum/resources/messages/en_US/topics.php
$g11n->translate('forum.topics.locked'); // {0} Locked!

// Use convenience function
msg('forum.topics.locked'); // {0} Locked!

// And also pass arguments
msg('forum.topics.locked', [$topicTitle]); // Title Locked!
```