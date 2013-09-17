# Resources #

Every application and module should contain a resources folder.
This folder should contain a locales and messages folder.
Within each of these folders should be locale specific folders, like en_US.

```
resources/
    configs/
    environments/
    locales/
        en/
            locale.php
        en_US/
    messages/
        en/
            common.php
        en_US/
```

If a module contains resources, the module name should prepend the resources folder.
For the most part, module resources should only contain configuration and message strings.

```
ForumModule/
    resources/
        configs/
        messages/
```

### Messages ###

Message bundles are localized strings that are mapped to specific keys.
Messages are mapped to modules and catalogs. A catalog is represnted by a file in the resources folder.
The filename and catalog key name should be exactly the same. They are written in the following format:

```
module.catalog.key // <module>/resources/messages/<locale>/catalog.php
catalog.key // resources/messages/<locale>/catalog.php (outside of module)
```

When the translate method is called, the system will lookup a message by filtering down locales and catalogs till a message is found.

Messages can be written in any format (defaults to PHP), just be sure to load the correct Reader class.

### Locales ###

Locale bundles are a set of rules that are specific to that locale. These rules dictate formatting,
inflections and grammer, validation, and more.

Child locales (en_US) inherit rules from their parent (en). This allows for easy to use hierarchical inheritance.

Rules are defined in separate PHP files that can be found in the resources/locales folder. If no rule file is found, they will simply be ignored.
The following rule files can exist (use the en locale as an example): `formats.php`, `inflections.php` and `validations.php`.

* Formats - Defines rules for formatting dates, times, currency, phone numbers, social numbers, etc
* Inflections - Defines rules for grammar inflection
* Validations - Defines regex patterns for validating phone numbers, postal codes and more