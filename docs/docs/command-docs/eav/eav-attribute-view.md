---
title: eav:attribute:view
sidebar_position: 11
---

# eav:attribute:view

View the data for a particular attribute.

:::tip
Use this command to quickly inspect the configuration and details of a specific EAV attribute. This is helpful for troubleshooting and development.
:::

```sh
n98-magerun2.phar eav:attribute:view [--format[="..."]] <entityType> <attributeCode>
```

**Arguments:**
| Argument        | Description                             |
|-----------------|-----------------------------------------|
| `entityType`    | Entity Type Code like catalog_product   |
| `attributeCode` | Attribute Code                          |

**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |
