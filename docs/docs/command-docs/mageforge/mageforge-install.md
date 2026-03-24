---
title: mageforge:install
---

# mageforge:install

:::info
Install the MageForge tool into your Magento 2 project.
:::

## Description

The `mageforge:install` command installs the [MageForge](https://github.com/openforgeproject/mageforge) module
into your Magento 2 installation by running the standard Composer and Magento setup steps:

1. `composer require openforgeproject/mageforge`
2. `bin/magento module:enable OpenForgeProject_MageForge`
3. `bin/magento setup:upgrade`

After a successful installation, the command automatically runs `mageforge:hyva:compatibility:check`
to verify that the setup is working correctly. This post-install check can be suppressed with the `--no-check` option.

## Usage

```bash
n98-magerun2.phar mageforge:install
```

```bash
n98-magerun2.phar mageforge:install --no-check
```

## Options

| Option | Description |
|:-------|:------------|
| `--no-check` | Skip the post-install `mageforge:hyva:compatibility:check` run |

## Examples

**Standard installation with post-install check:**

```bash
n98-magerun2.phar mageforge:install
```

**Installation without running the compatibility check:**

```bash
n98-magerun2.phar mageforge:install --no-check
```
