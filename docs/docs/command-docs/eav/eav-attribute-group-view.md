---
title: eav:attribute-group:view
sidebar_position: 16
---

# eav:attribute-group:view

View group metadata and the attributes assigned to that group in one attribute set.

```sh
n98-magerun2.phar eav:attribute-group:view <entityType> <attributeSetId> <attributeGroupId> [--format=FORMAT]
n98-magerun2.phar eav:attribute-group:view catalog_product 4 27
```

Supply an entity type **code** and positive numeric set/group **IDs** from your installation. The set must belong to the entity type, and the group must belong to the set. A group from another set is never silently substituted or resolved to a default group.

The property/value output includes group ID, name, set ID, sort order, default marker and group code. Each assigned attribute is listed as `attribute.<code>` with its ID, admin label and assignment sort order. Standard `--format` output formats are supported; all metadata and assignments are rendered as one result.

This command is read-only. Success, including a valid empty group, returns `0`. Invalid context or a missing group returns a non-zero status.
