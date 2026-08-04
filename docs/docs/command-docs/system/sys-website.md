---
title: Website Commands
---

# Website Commands

## sys:website:create

Creates a website from the supplied code and name. If either value is omitted, the command prompts for it.

```bash
bin/n98-magerun2 sys:website:create [<code>] [<name>] [--default-group-id=<id>]
```

## sys:website:delete

Deletes a website by code or ID. If no identifier is supplied, the command displays a selector. The default website cannot be deleted. The command asks for confirmation unless `--force` (or `-f`) is supplied.

```bash
bin/n98-magerun2 sys:website:delete [<code-or-id>] [--force]
```
