---
title: eav:attribute:create
sidebar_position: 13
---

# eav:attribute:create

Create a **new user-defined** attribute for an explicit EAV entity type code. Existing attributes are never updated.

## Interactive use

Run `eav:attribute:create` without positional arguments to select the entity type and enter the attribute code and required admin label. For example, `eav:attribute:create catalog_product care_note` asks only for the missing label. Supplied values are preserved. No optional metadata wizard is shown: continue to use flags for options such as input type, scope and assignment.

With `--no-interaction`, both positional arguments and `--label` are required; missing values fail without mutation. See [interactive input safety](./index.md#interactive-required-inputs) for cancellation and selection behavior.

```sh
n98-magerun2.phar eav:attribute:create <entityType> <attributeCode> --label="Admin label" [options]
```

Use entity type codes such as `catalog_product`, `catalog_category`, `customer` or `customer_address`, not entity IDs. Creation uses Magento's EAV setup API and its entity-specific property mappers. Availability in customer forms, storefront rendering, indexing and other entity-specific integrations may require module configuration; this command does not install that configuration.

## Metadata options

| Option | Default | Meaning |
| --- | --- | --- |
| `--label` | Required | Non-empty admin label, at most 255 bytes |
| `--input` | `text` | `text`, `textarea`, `date`, `boolean`, `select`, `multiselect` |
| `--required` | `0` | Require a value (`0` or `1`) |
| `--unique` | `0` | Require unique values (`0` or `1`) |
| `--visible` | `1` | Admin visibility (`0` or `1`) |
| `--scope` | `global` | `global`, `website`, `store`; non-global scopes are catalog-only |
| `--default` | None | Default value for text, textarea or boolean; boolean accepts only `0` or `1` |
| `--option` | None | Repeatable admin label; required for select/multiselect, forbidden for other inputs |
| `--sort-order` | `0` | Assignment order, integer from 0 to 32767 (signed SMALLINT storage) |
| `--attribute-set` | None | Existing attribute set **ID**, requires `--attribute-group` |
| `--attribute-group` | None | Existing group **ID** in that set, requires `--attribute-set` |

Attribute codes must start with a lowercase letter and contain at most 30 lowercase letters, digits or underscores. Labels must not contain control characters. Select option labels must be unique, non-empty, trimmed, at most 255 bytes and contain no control characters. Defaults are limited to 255 bytes for text and 65535 bytes for textarea. Select/multiselect and date defaults are intentionally not supported.

Storage types are derived from input: text/multiselect → `varchar`, textarea → `text`, boolean/select → `int`, date → `datetime`. Boolean uses Magento's core boolean source; multiselect uses its core array backend. There are **no arbitrary PHP-class, backend-table, static-column or free-form setup-data options**. This is a bounded metadata creation tool, not a replacement for module setup/data patches.

## Examples

```sh
# Replace 4 and 27 with actual IDs from your installation.
n98-magerun2.phar eav:attribute:create catalog_product care_note \
  --label="Care note" --input=textarea --required=0 \
  --attribute-set=4 --attribute-group=27

n98-magerun2.phar eav:attribute:create catalog_product finish \
  --label="Finish" --input=select --option="Matte" --option="Gloss"
```

Without assignment options, the attribute is created without assigning it to all attribute sets. With assignment options, only the specified set/group receives it. The entity type, metadata, duplicates and assignment targets are checked before insertion. Attribute creation, option insertion and assignment are wrapped in one database transaction.

:::warning
Creation changes the Magento database and is disabled in production mode, like `eav:attribute:remove`. Test in a disposable development installation first. Reindex or refresh relevant caches as required by your entity's integration. Success returns status `0`; invalid input, duplicates and missing targets return a non-zero status.
:::
