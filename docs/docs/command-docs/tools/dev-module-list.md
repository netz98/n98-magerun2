---
title: dev:module:list
sidebar_label: dev:module:list
---

:::info
Lists all installed Magento modules. You can filter by vendor, enabled/disabled state, and output format.
:::

Lists all installed modules. If `--vendor` option is set, only modules of the given vendor are listed.
If `--only-enabled` option is set, only enabled modules are listed.
If `--only-disabled` option is set, only disabled modules are listed.
Format can be `csv`, `json`, `xml` or `yaml`.

```sh
n98-magerun2.phar dev:module:list [options]
```

**Options:**
| Option               | Description                                            |
|----------------------|--------------------------------------------------------|
| `--vendor[=VENDOR]`  | Show modules of a specific vendor (case insensitive)   |
| `-e, --only-enabled` | Show only enabled modules                              |
| `-d, --only-disabled`| Show only disabled modules                             |
| `--format[=FORMAT]`  | Output Format. One of [csv,json,json_array,yaml,xml]   |
