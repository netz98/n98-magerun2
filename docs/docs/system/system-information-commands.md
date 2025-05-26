---
sidebar_position: 5
title: System Information Commands
---
## Magento System Info

Provides infos like the edition, version or the configured cache
backends, amount of data or installed packages.

```sh
n98-magerun2.phar sys:info [options] [<key>]
```
**Arguments:**
| Argument | Description                                                            |
|----------|------------------------------------------------------------------------|
| `key`    | Only output value of named param like "version". Key is case insensitive. |

**Options:**

| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--sort`           | Sort table by name                                   |
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


---

## Magento Stores

Lists all store views.

```sh
n98-magerun2.phar sys:store:list [--format[="..."]]
```

## Magento Websites

Lists all websites.

```sh
n98-magerun2.phar sys:website:list [--format[="..."]]
```
