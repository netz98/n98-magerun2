---
title: magerun:config:dump
---

# magerun:config:dump

Dump full current (merged) configuration for n98-magerun2.

## Usage

```bash
n98-magerun2 magerun:config:dump
```

Example output:

```yaml
application:
  check-root-user: true
twig:
  baseDirs: null
plugin:
  folders:
    - /usr/share/n98-magerun2/modules
    - /usr/local/share/n98-magerun2/modules
    - /home/foo/.n98-magerun2/modules
    - /var/www/html/lib/n98-magerun2/modules
helpers:
  composer: N98\Util\Console\Helper\ComposerHelper
  database: N98\Util\Console\Helper\DatabaseHelper
  dialog: N98\Util\Console\Helper\DialogHelper
  parameter: N98\Util\Console\Helper\ParameterHelper
  table: N98\Util\Console\Helper\TableHelper
  injection: N98\Util\Console\Helper\InjectionHelper
script:
  folders:
    - /usr/share/n98-magerun2/scripts
    - /usr/local/share/n98-magerun2/scripts
  excluded-folders:
    - .github
    - dev
    - etc
    - generated
```
