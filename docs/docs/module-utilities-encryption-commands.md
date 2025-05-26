---
sidebar_position: 17
title: Module Utilities and Encryption
---
## List modules

```sh
n98-magerun2.phar dev:module:list [options]
```
**Options:**
| Option             | Description                                            |
|--------------------|--------------------------------------------------------|
| `--vendor[=VENDOR]` | Show modules of a specific vendor (case insensitive)   |
| `-e, --only-enabled`| Show only enabled modules                              |
| `-d, --only-disabled`| Show only disabled modules                             |
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml]   |


Lists all installed modules. If `--vendor` option is set, only modules of the given vendor are listed.
If `--only-enabled` option is set, only enabled modules are listed.
If `--only-disabled` option is set, only disabled modules are listed.
Format can be `csv`, `json`, `xml` or `yaml`.

## Encryption

Encrypt the given string using Magentos crypt key

```sh
n98-magerun2.phar dev:encrypt <value>
```

Decrypt the given string using Magentos crypt key

```sh
n98-magerun2.phar dev:decrypt <value>
```
