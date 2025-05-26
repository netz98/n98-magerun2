---
sidebar_position: 15
title: Translation Commands
---
## Translations

Enable/disable inline translation feature for Magento Admin:

```sh
n98-magerun2.phar dev:translate:admin [--on] [--off]
```

Enable/disable inline translation feature for shop frontend:

```sh
n98-magerun2.phar dev:translate:shop [--on] [--off] [<store>]
```

Set a translation (saved in translation table)

```sh
n98-magerun2.phar dev:translate:set <string> <translate> [<store>]
```

Export inline translations

```sh
n98-magerun2.phar dev:translate:export [--store=<storecode>] <locale> [<filename>]
```
