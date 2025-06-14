---
title: magerun:config:info
---

# magerun:config:info

Show a list of applied configuration files and types for n98-magerun2.

## Usage

```bash
n98-magerun2 magerun:config:info
```

## Example Output

```
+--------+-----------------------------------------+----------------------------------------------------+
| type   | path                                    | note                                               |
+--------+--------...------------------------------+----------------------------------------------------+
| dist   |                                         | Shipped in phar file                               |
| user   | /home/foo/.n98-magerun2.yaml            | Configuration in home directory of current user    |
| project| /var/www/html/app/etc/n98-magerun2.yaml | The config is stored in the currently used project |
+--------+-----------------------------------------+----------------------------------------------------+
```
