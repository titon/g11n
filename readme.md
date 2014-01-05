# G11n v0.10.9 [![Build Status](https://travis-ci.org/titon/g11n.png)](https://travis-ci.org/titon/g11n) #

The Titon g11n package handles the localization and internationalization of your application.

### Features ###

* `G11n` - L10n and I18n management
* `Locale` - Locale configuration
* `Translator` - Message bundle parsing
* `Utility` - Extends Inflector, Format, Number and Validator
* `Route` - Locale aware routing

### Dependencies ###

* `Common`
* `Utility`
* `Event`
* `IO`
* `Cache` (optional for Translator)
* `Route` (optional)

### Requirements ###

* PHP 5.4.0
    * Intl
    * Multibyte
    * Gettext (for GettextTranslator)

### To Do ###

* Test gettext functionality
* Implement TranslatableBehavior