---
title: Store Commands
---

# Store Commands

## sys:store:create

Creates a store view. In interactive mode, the command asks for the store code, name, and store group. For automation, provide all required values using arguments and options.

```bash
bin/n98-magerun2 sys:store:create [<code>] [<name>] \
  --group-id=<id> [--is-active=0|1]

bin/n98-magerun2 sys:store:create [<code>] [<name>] \
  --group-code=<code> [--is-active=0|1]

bin/n98-magerun2 sys:store:create [<code>] [<name>] \
  --website-id=<id> [--is-active=0|1]
```

## sys:store:delete

Deletes a store view by code or ID. If no identifier is supplied, the command displays a selector. The default store of a store group cannot be deleted. The command asks for confirmation unless `--force` (or `-f`) is supplied.

```bash
bin/n98-magerun2 sys:store:delete [<code-or-id>] [--force]
```
