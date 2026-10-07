---
title: fast-di-compile:install
---

# fast-di-compile:install

:::info
Install [Magento 2 Fast DI Compile](https://github.com/speedupmate/magento2-spmt-fast-di-compile), a replacement for Magento's PHP DI compiler.
:::

## Description

The command installs and configures the compiler replacement in the current Magento project:

1. `composer require spmt/magento2-spmt-fast-di-compile`
2. `bin/magento module:enable Spmt_FastDiCompile`
3. `bin/magento setup:upgrade`
4. `vendor/bin/install-fast-di-compile.php`

The final step downloads the release binary for the current platform and verifies its checksums before installing it into the Composer package. The command stops at the first failed step and retains the output from Composer or Magento to help resolve it.

Use `--no-setup-upgrade` to defer `bin/magento setup:upgrade` when it must be run as part of a separate deployment step.

After installation, continue using Magento's usual command:

```bash
bin/magento setup:di:compile
```

Use `bin/magento setup:di:compile --standard` when you need Magento's standard PHP compiler instead.

## Usage

```bash
n98-magerun2.phar fast-di-compile:install
```

```bash
n98-magerun2.phar fast-di-compile:install --no-setup-upgrade
```
