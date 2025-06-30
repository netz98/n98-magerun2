---
title: dev:keep-calm
sidebar_label: dev:keep-calm
---

## Keep Calm and Run All the Things 🛠️😎

:::info
Don't panic! Run all the magic commands to (maybe) fix your broken shop and restore your sanity!
:::

:::warning
Command is experimental and not intended for production use. Use with caution!
:::

The `dev:keep-calm` command runs a sequence of common Magento 2 maintenance and development commands in a single step. This helps you quickly restore your development environment after code changes or when things go wrong.

### What It Does

Run the following commands in order (unless skipped or not needed):

- `hyva:config:generate` (only if `app/etc/hyva-themes.json` does not exist)
- `setup:upgrade` (also clears cache)
- [`db:add-default-authorization-entries`](../db/db-add-default-authorization-entries.md)
- [`generation:flush`](../generation/generation-flush-command.md) (clears generated code)
- `setup:di:compile` (compiles dependency injection)
- `dev:asset:clear` (clears static assets)
- `dev:theme:build-hyva` (builds the CSS for all installed Hyvä themes)
- `setup:static-content:deploy` (skipped in non-production mode unless `--force-static-content-deploy` is used)
- `indexer:reset`
- `indexer:reindex`
- `maintenance:disable`

Each command can be skipped with a `--skip-<command>` option, e.g. `--skip-setup-upgrade`.

At the end, a summary checklist is printed showing which commands were executed, skipped, or failed.

### Usage

```bash
n98-magerun2.phar dev:keep-calm [--force-static-content-deploy] [--skip-setup-upgrade] [--skip-indexer-reindex] ...
```

#### Options

- `--force-static-content-deploy` – Forces static content deploy even in non-production mode.
- `--skip-<command>` – Skips the specified command (replace `<command>` with the command name, e.g. `setup-upgrade`).

Example if you want to skip the `indexer:reset` and `indexer:reindex` commands:

```bash
n98-magerun2.phar dev:keep-calm --skip-indexer-reset --skip-indexer-reindex
```

### Example Output

```text
1. 🚀  Running command: hyva:config:generate
😊  Skipping command: hyva:config:generate (app/etc/hyva-themes.json exists)
2. 🚀  Running command: setup:upgrade
... (output of each command)

Command Execution Summary:
  1. ⏭️ hyva:config:generate - Generate Hyvä theme configuration files if they are missing. (app/etc/hyva-themes.json exists)
  2. ✅ setup:upgrade - Run setup upgrade and database schema/data updates. Clears also the cache.
  ...
```

### Notes

- The command is intended for development environments only.
- The summary at the end helps you quickly see what was done and what was skipped.

### See Also

- [Magento 2 Maintenance Commands](https://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-maint.html)

---

:::note
This command was introduced with version 9.0.0.
:::
