---
title: sys:maintenance
---

# sys:maintenance

Toggles maintenance mode for the Magento shop.

## Usage
```sh
n98-magerun2.phar sys:maintenance [options]
```

## Options
| Option | Description                                                                                                                      |
|--------|----------------------------------------------------------------------------------------------------------------------------------|
| `--on` | Set to [1] to enable maintenance mode. Optionally supply a comma separated list of IP addresses to exclude from being affected |
| `--off`| Set to [1] to disable maintenance mode. Set to [d] to also delete the list with excluded IP addresses.                             |

Maintenance mode is useful for upgrades or maintenance tasks. You can exclude specific IP addresses from being affected by maintenance mode.
