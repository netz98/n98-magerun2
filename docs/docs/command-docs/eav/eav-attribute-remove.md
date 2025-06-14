---
title: eav:attribute:remove
sidebar_position: 12
---

# eav:attribute:remove

Remove attribute for a given attribute code.

:::warning
Use this command with caution. Removing attributes is irreversible and may affect data integrity or break features relying on the attribute.
:::

```sh
n98-magerun2.phar eav:attribute:remove <entityType> <attributeCode>...
```

**Arguments:**
| Argument        | Description                             |
|-----------------|-----------------------------------------|
| `entityType`    | Entity Type Code, e.g. catalog_product  |
| `attributeCode` | Attribute Code (one or more)            |
