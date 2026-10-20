---
title: eav:attribute-group:create
sidebar_position: 14
---

# eav:attribute-group:create

Create an empty attribute group in an existing attribute set using Magento's EAV setup API.

## Interactive use

Omit required inputs to select an entity type, select an existing set scoped to it, and enter a group name. Choose by the displayed menu number (starting at 1), not the code/ID in the label; 0/Enter cancels. `eav:attribute-group:create catalog_product` asks only for the missing set and name. Optional sort order remains a flag, not a wizard question. Supplied arguments/options are never replaced.

With `--no-interaction`, provide entity type, set ID and name explicitly. Missing inputs fail without mutation. Context selection has no record default; Enter cancels. See [interactive input safety](./index.md#interactive-required-inputs).

```sh
n98-magerun2.phar eav:attribute-group:create <entityType> <attributeSetId> <name> [--sort-order=0]
n98-magerun2.phar eav:attribute-group:create catalog_product 4 "Care information" --sort-order=90
```

The entity type must be a code, such as `catalog_product`. The set must be a positive numeric **ID belonging to that entity type**; IDs in examples are installation-specific. Group names are trimmed and must contain 1–255 bytes without control characters. Names must produce a non-empty, non-numeric Magento group code, so Magento cannot mistake it for an existing group ID. Sort order must be an integer from 0 to 32767, matching Magento's signed SMALLINT storage.

Duplicate names (case-insensitive), generated group-code collisions and aliases of existing default groups are rejected instead of updating an existing group. The command prints the new group ID and set ID. Success returns `0`; invalid context, metadata or duplicates return non-zero.

:::warning
This mutates the database and is disabled in production mode. It does not create an attribute set or assign attributes. Use [eav:attribute:create](./eav-attribute-create.md) with both assignment options to create an attribute in this group.
:::
