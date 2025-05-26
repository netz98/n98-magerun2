---
sidebar_position: 21
title: Code Generation and Indexing Commands
---
## generation:flush
Flushs generated code like factories and proxies.
```sh
n98-magerun2.phar generation:flush [<vendorName>]
```
**Arguments:**
| Argument     | Description                     |
|--------------|---------------------------------|
| `vendorName` | Vendor to remove like "Magento" |

## index:list
Lists all magento indexes.
```sh
n98-magerun2.phar index:list [--format[=FORMAT]]
```
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |

## index:trigger:recreate
ReCreate all triggers.
```sh
n98-magerun2.phar index:trigger:recreate
```
