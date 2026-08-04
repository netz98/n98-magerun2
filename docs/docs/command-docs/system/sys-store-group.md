---
title: Store Group Commands
---

# Store Group Commands

## sys:store-group:create

Creates a store group for an existing website. The website can be selected by ID or code. A root category ID is required.

```bash
bin/n98-magerun2 sys:store-group:create [<name>] --website-id=<id> \
  --root-category-id=<id> [--default-store-id=<id>]

bin/n98-magerun2 sys:store-group:create [<name>] --website-code=<code> \
  --root-category-id=<id> [--default-store-id=<id>]
```

## sys:store-group:delete

Deletes a store group by ID. The website's default store group cannot be deleted. The command asks for confirmation unless `--force` (or `-f`) is supplied.

```bash
bin/n98-magerun2 sys:store-group:delete <id> [--force]
```
