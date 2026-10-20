---
title: eav:attribute-group:delete
sidebar_position: 17
---

# eav:attribute-group:delete

Delete an **empty, non-default** attribute group in an explicit entity type and attribute set.

## Interactive use

Missing entity/set/group inputs are selected in dependency order, with sets scoped to the entity and groups scoped to the set. `eav:attribute-group:delete catalog_product 4` asks only for a group in set 4, then retains the existing deletion confirmation. Enter at a context selection cancels instead of selecting a record. Supplied values are never replaced.

`--force` skips only the existing destructive confirmation, not context selection or safety checks. With `--no-interaction`, supply all three arguments **and** `--force` to delete an eligible group. See [interactive input safety](./index.md#interactive-required-inputs).

```sh
n98-magerun2.phar eav:attribute-group:delete <entityType> <attributeSetId> <attributeGroupId> [--force|-f]
n98-magerun2.phar eav:attribute-group:delete catalog_product 4 27 --force
```

Use an entity type **code** and positive numeric set/group **IDs** from your installation. The command verifies that the set belongs to the entity and the group belongs to that set before doing anything destructive.

Without `--force`, an interactive confirmation is required and defaults to **No**. With `--no-interaction`, deletion fails unless `--force` is supplied. Cancellation returns a non-zero status and leaves the group unchanged.

:::danger
`--force` bypasses only confirmation. A group marked as default, the framework's effective default group, or a group containing assigned attributes **cannot be deleted, even with force**. No attributes are removed or moved implicitly. This command is disabled in production mode.
:::

Inspect the group with [eav:attribute-group:view](./eav-attribute-group-view.md) first. Successful deletion prints its ID and set ID and returns status `0`. Missing records, mismatched context, protected groups and other failures return non-zero.
