---
sidebar_position: 1
title: EAV Commands
---

# EAV Commands

Commands for managing EAV (Entity-Attribute-Value) attributes.

:::note
EAV (Entity-Attribute-Value) is a flexible data model used in Magento for entities like products and customers. These commands help you inspect, view, and manage EAV attributes efficiently.
:::

## Commands

- [eav:attribute:list](./eav-attribute-list.md) - List EAV attributes
- [eav:attribute:view](./eav-attribute-view.md) - View the data for a particular attribute
- [eav:attribute:remove](./eav-attribute-remove.md) - Remove attribute for a given attribute code
- [eav:attribute:create](./eav-attribute-create.md) - Create a user-defined attribute with validated core metadata
- [eav:attribute-group:create](./eav-attribute-group-create.md) - Create an empty group in an explicit attribute set
- [eav:attribute-group:list](./eav-attribute-group-list.md) - List groups in an attribute set
- [eav:attribute-group:view](./eav-attribute-group-view.md) - View group metadata and assigned attributes
- [eav:attribute-group:delete](./eav-attribute-group-delete.md) - Delete an empty, non-default group with confirmation

## Interactive required inputs

The five creation/group-management commands prompt for **missing required inputs only** when run interactively. Existing arguments and options are preserved; optional metadata is never presented as a wizard. Entity choices use entity type codes, and set/group choices use the displayed record IDs, not numbered row positions. Sets are scoped to the selected entity and groups to the selected set.

Press Enter or choose `cancel` at a context selection to abort without changes; Ctrl+C also cancels. There is no default entity, set or group. Invalid answers, empty choice lists or end-of-input abort safely. Required free text must be non-empty and pass the command's validation before writes.

With `--no-interaction`, supply all required positional arguments and, for attribute creation, `--label`. Missing inputs fail with a non-zero status instead of selecting defaults. Group deletion still requires its existing confirmation, or `--force`; force never bypasses default/populated-group protections.
