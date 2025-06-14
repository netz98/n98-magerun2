---
title: magerun:config:dump
---

# magerun:config:dump

:::info
This command dumps the full, current (merged) configuration for n98-magerun2. Useful for debugging and understanding the active configuration state.
:::

## Usage

```bash
n98-magerun2 magerun:config:dump
```

:::tip
You can redirect the output to a file for further inspection or sharing with your team.
:::

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
  composer: N98\\Util\\Console\\Helper\\ComposerHelper
  database: N98\\Util\\Console\\Helper\\DatabaseHelper
  dialog: N98\\Util\\Console\\Helper\\DialogHelper
  parameter: N98\\Util\\Console\\Helper\\ParameterHelper
  table: N98\\Util\\Console\\Helper\\TableHelper
  injection: N98\\Util\\Console\\Helper\\InjectionHelper
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
