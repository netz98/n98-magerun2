---
title: dev:module:detect-composer-dependencies
---

## Detect Composer Dependencies in Module

:::info
Scan your module's source code to identify required Composer dependencies. This helps ensure your module's composer.json lists all necessary packages.
:::

The source code of one or more modules can be scanned for dependencies.

```sh
n98-magerun2.phar dev:module:detect-composer-dependencies [--only-missing] [--check] <path>...
```

**Arguments:**

| Argument | Description     |
|----------|-----------------|
| path     | Path to modules |

**Options:**

| Option         | Description                                              |
|----------------|----------------------------------------------------------|
| --only-missing | Print only missing dependencies.                         |
| --check        | Exit with status code 1 if dependencies are missing.     |
