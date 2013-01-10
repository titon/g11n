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