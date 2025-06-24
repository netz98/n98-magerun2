---
title: sys:url:regenerate
---

# sys:url:regenerate

Regenerate product and category URL rewrites.

## Usage
```sh
n98-magerun2.phar sys:url:regenerate --products 1,2 --categories 3 --store 1
```

- `--products`   Comma separated product IDs. Leave empty to process all products.
- `--categories` Comma separated category IDs. Leave empty to process all categories.
- `--store`      Store ID. Default processes all stores.
