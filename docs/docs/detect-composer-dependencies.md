---
sidebar_position: 14
title: Detect Composer Dependencies
---
## Detect Composer Dependencies in Module

The source code of one or more modules can be scanned for dependencies.

```sh
n98-magerun2.phar dev:module:detect-composer-dependencies [--only-missing] <path>...
```
**Arguments:**

| Argument | Description      |
|----------|------------------|
| `path`   | Path to modules  |

**Options:**

| Option           | Description                      |
|------------------|----------------------------------|
| `--only-missing` | Print only missing dependencies.  |
