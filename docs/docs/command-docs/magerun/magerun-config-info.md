---
title: magerun:config:info
---

# magerun:config:info

:::info
This command displays a list of all configuration files currently applied to n98-magerun2, including their type and location. This is helpful for troubleshooting configuration issues or understanding which files are in effect.
:::

## Usage

```bash
n98-magerun2 magerun:config:info
```

## Example Output

```
+--------+-----------------------------------------+----------------------------------------------------+
| type   | path                                    | note                                               |
+--------+-----------------------------------------+----------------------------------------------------+
| dist   |                                         | Shipped in phar file                               |
| user   | /home/foo/.n98-magerun2.yaml            | Configuration in home directory of current user    |
| project| /var/www/html/app/etc/n98-magerun2.yaml | The config is stored in the currently used project |
+--------+-----------------------------------------+----------------------------------------------------+
```
