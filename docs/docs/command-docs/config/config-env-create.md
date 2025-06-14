---
title: config:env:create
sidebar_label: config:env:create
---

# config:env:create

Create `app/etc/env.php` interactively.

This command helps you create the `env.php` file required by Magento 2. It guides you through the process interactively, allowing you to set up all necessary configuration values. If the file already exists, it can update existing values as needed.

> **Tip:** To update a single value, use the [`config:env:set`](./config-env-set.md) command instead.

## Usage

```bash
n98-magerun2.phar config:env:create
```

## Features
- Interactive creation of `app/etc/env.php`
- Can update existing `env.php` files
- Supports all required Magento 2 environment settings

## Example

```bash
n98-magerun2.phar config:env:create
```

You will be prompted for the necessary configuration values (database, crypt key, etc.).

## See also
- [`config:env:set`](./config-env-set.md): Update a single value in `env.php`
- [`config:env:delete`](./config-env-delete.md): Remove a value from `env.php`

