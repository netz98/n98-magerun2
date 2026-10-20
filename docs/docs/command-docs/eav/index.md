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

The five creation/group-management commands prompt for **missing required inputs only** when run interactively. Existing arguments and options are preserved; optional metadata is never presented as a wizard. Like the DI/observer command menus, choices are numbered starting at **1**. Enter the displayed choice number, not the entity code or record ID shown in its label. The command maps that number to the actual record, including when IDs are noncontiguous or names are repeated. Sets are scoped to the selected entity and groups to the selected set. Supplied CLI arguments remain entity codes and actual set/group IDs, never menu numbers.

Press Enter or choose **0 (Cancel)** at a context selection to abort without changes; Ctrl+C also cancels. There is no default entity, set or group. Invalid answers, empty choice lists or end-of-input abort safely. Required free text must be non-empty and pass the command's validation before writes.

With `--no-interaction`, supply all required positional arguments and, for attribute creation, `--label`. Missing inputs fail with a non-zero status instead of selecting defaults. Group deletion still requires its existing confirmation, or `--force`; force never bypasses default/populated-group protections.

## Output formats

Group **list** and **view** use the shared table helper's `--format[=FORMAT]` option, with a default of `null` (human-readable table). A bare `--format` also renders a table. Supported formats are `csv`, `tsv`, `json`, `json_array`, `jsonl`, `yaml`, `markdown`, and `xml`; aliases are `yml`, `md`, and `ndjson`. Explicit empty (`--format=`) and unknown values fail. The three mutating commands print status messages rather than tables and do not provide `--format`.

Formatted data goes to **stdout**; interactive menus/questions and application errors go to **stderr**. For automation, supply the real context arguments and `--no-interaction`, for example:

```sh
n98-magerun2.phar --no-interaction eav:attribute-group:list catalog_product 4 --format=json > groups.json
```

Serialization follows the shared helper, not a separate EAV schema: `json` is object-shaped (an empty collection is `{}`), `json_array` is an array (empty `[]`), and CSV/TSV include column headers only when rows exist (empty collections produce no bytes). JSONL and Markdown likewise emit no bytes for empty lists; XML and YAML retain their shared renderer output. View emits one property/value result containing group metadata and, when present, assigned attributes—not multiple concatenated documents.
