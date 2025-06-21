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

Runs a sequence of common Magento 2 maintenance commands in a single step:

- `setup:upgrade` (also clears cache)
- `indexer:reindex`
- `setup:di:compile`
- `setup:static-content:deploy`

This command is experimental and intended to simplify the process of keeping your Magento 2 environment up to date during development or after code changes.

## Usage

```bash
n98-magerun2 dev:keep-calm [--force-static-content-deploy]
```

## Options

| Option                        | Description                                                                 |
|-------------------------------|-----------------------------------------------------------------------------|
| `--force-static-content-deploy` | Force static content deploy with `--force` option, even in developer/default mode. |

## Behavior

- In Magento's `default` and `developer` modes, static content deployment is usually skipped for performance. Use `--force-static-content-deploy` to always run `setup:static-content:deploy --force` regardless of the mode.
- If any command in the sequence fails, the process stops and the error is displayed.

:::tip
This command is your all-in-one rescue rope for those days when Magento just won't cooperate. Hit it, cross your fingers, and hope for the best!
:::

## Example

```bash
n98-magerun2 dev:keep-calm --force-static-content-deploy
```

Runs all steps, forcing static content deployment even in developer mode.

## See Also
- [Magento Deploy Modes](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/set-mode)
- https://x.com/sandermangel/status/573506345275686912
