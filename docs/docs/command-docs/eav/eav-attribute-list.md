---
title: eav:attribute:list
sidebar_position: 10
---

# eav:attribute:list

List EAV attributes.

:::info
This command helps you inspect all EAV attributes in your Magento installation. Useful for debugging and development.
:::

```sh
n98-magerun2.phar eav:attribute:list [options]
```

**Options:**

| Option                     | Description                                          |
|----------------------------|------------------------------------------------------|
| `--add-source`             | Add source models to list.                           |
| `--add-backend`            | Add backend type to list.                            |
| `--filter-type[=FILTER-TYPE]` | Filter attributes by entity type.                    |
| `--format[=FORMAT]`        | Output Format. One of [csv,json,json_array,yaml,xml] |
