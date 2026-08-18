---
title: Store Group Commands
---

# Store Group Commands

## sys:store-group:create

Creates a store group for an existing website. The name, code, website, and root category ID are requested interactively when omitted. A default store ID is optional.

```bash
bin/n98-magerun2 sys:store-group:create [<code>] [<name>]

bin/n98-magerun2 sys:store-group:create [<code>] [<name>] --website-id=<id> \
  --root-category-id=<id> [--default-store-id=<id>]

bin/n98-magerun2 sys:store-group:create [<code>] [<name>] --website-code=<code> \
  --root-category-id=<id> [--default-store-id=<id>]
```

## sys:store-group:delete

Deletes a store group by ID. If no ID is supplied, the command displays a selector. The website's default store group cannot be deleted. The command asks for confirmation unless `--force` (or `-f`) is supplied.

```bash
bin/n98-magerun2 sys:store-group:delete [<id>] [--force]
```
