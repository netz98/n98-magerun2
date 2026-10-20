---
title: eav:attribute-group:list
sidebar_position: 15
---

# eav:attribute-group:list

List groups in one explicit EAV entity type and attribute set.

```sh
n98-magerun2.phar eav:attribute-group:list <entityType> <attributeSetId> [--format=FORMAT]
n98-magerun2.phar eav:attribute-group:list catalog_product 4 --format=csv
```

`entityType` is a code, such as `catalog_product`, `catalog_category`, `customer` or `customer_address`. `attributeSetId` is a positive numeric ID that must belong to this entity type. IDs are installation-specific.

Output columns are `id`, `name`, `attribute_set_id`, `sort_order`, and `default_id`, ordered by sort order then group ID. The standard table helper formats are supported, including CSV and JSON. An existing empty set produces an empty result with status `0`; an unknown entity type, missing set or mismatched entity/set context fails with a non-zero status.

This is a read-only command. Use the listed IDs with [view](./eav-attribute-group-view.md) and [delete](./eav-attribute-group-delete.md).
