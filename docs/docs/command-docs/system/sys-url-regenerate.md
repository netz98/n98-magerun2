---
title: sys:url:regenerate
---

# sys:url:regenerate

Regenerate product, category, and CMS page URL rewrites.

:::note
This command was introduced with version 9.1.0.
:::

## Usage

```sh
n98-magerun2.phar sys:url:regenerate [options]
```

**Options:**

| Option                | Description                                      |
|----------------------|--------------------------------------------------|
| `--products`         | Comma separated product IDs. Leave empty to process all products. |
| `--categories`       | Comma separated category IDs. Leave empty to process all categories. |
| `--cms-pages`        | Comma separated CMS page IDs. Leave empty to process all CMS pages. |
| `--store`            | Store ID. Default processes all stores.          |
| `--all-products`     | Regenerate all products.                         |
| `--all-categories`   | Regenerate all categories.                       |
| `--all-cms-pages`    | Regenerate all CMS pages.                        |
| `--batch-size`       | Batch size for pagination (default: 100).        |

## Examples

Regenerate URL rewrites for specific products and categories in store 1:

```sh
n98-magerun2.phar sys:url:regenerate --products 1,2 --categories 3 --store 1
```

Regenerate all products and categories for all stores:

```sh
n98-magerun2.phar sys:url:regenerate --all-products --all-categories
```

Regenerate all CMS pages for store 2 with a custom batch size:

```sh
n98-magerun2.phar sys:url:regenerate --all-cms-pages --store 2 --batch-size 50
```
