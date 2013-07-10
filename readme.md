# G11n v0.8.2 [![Build Status](https://travis-ci.org/titon/G11n.png)](https://travis-ci.org/titon/G11n) #

The Titon g11n package handles the localization and internationalization of your application.

### Features ###

* `G11n` - L10n and I18n management
* `Locale` - Locale configuration
* `Translator` - Message bundle parsing
* `Utility` - Extends Inflector, Format, Number and Validator

### Dependencies ###

* `Common`
* `Utility`
* `IO`
* `Cache` (Optional, for Translator)

### Requirements ###

* PHP 5.4.0
	* Intl
	* Multibyte
	* Gettext (for GettextTranslator)