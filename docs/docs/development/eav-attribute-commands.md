---
sidebar_position: 20
title: EAV Attribute Commands
---
## EAV Attributes

### eav:attribute:list
List EAV attributes.
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

---

View the data for a particular attribute:

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

### eav:attribute:remove
Remove attribute for a given attribute code.
```sh
n98-magerun2.phar eav:attribute:remove <entityType> <attributeCode>...
```
**Arguments:**
| Argument        | Description                             |
|-----------------|-----------------------------------------|
| `entityType`    | Entity Type Code, e.g. catalog_product  |
| `attributeCode` | Attribute Code (one or more)            |
