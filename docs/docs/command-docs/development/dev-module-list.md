---
title: dev:module:list
---

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

:::info
Lists all installed modules. You can filter by vendor, enabled/disabled state, and output format. Useful for auditing and debugging module status.
:::

Lists all installed modules. If `--vendor` option is set, only modules of the given vendor are listed.
If `--only-enabled` option is set, only enabled modules are listed.
If `--only-disabled` option is set, only disabled modules are listed.
Format can be `csv`, `json`, `xml` or `yaml`.
