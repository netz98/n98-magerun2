---
title: Store Group Commands
---

# Store Group Commands

## sys:store-group:create

Creates a store group for an existing website. When `--website-id` or `--website-code` is omitted, the command displays a website selector. The name and root category ID are also requested interactively when omitted. A default store ID is optional.

```bash
bin/n98-magerun2 sys:store-group:create [<name>]

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
