---
title: composer:redeploy-base-packages
sidebar_label: composer:redeploy-base-packages
---

# composer:redeploy-base-packages

Redeploys all base packages as defined in the command configuration. This command is useful for reapplying the deployment of Magento 2 modules and related files, especially after changes to deployment strategies or mappings.

## Background

If there are changes in the base packages (e.g. `magento2-base`), the Composer installer does not copy them again because all the main project files are only copied once. This command allows you to redeploy those files without requiring a full Composer reinstall.

The `config.yaml` shipped in the `n98-magerun2.phar` defines a list of packages. The package list can be extended by a custom config file.

::note
This command is a backport of a netz98 internal Composer Plugin and is now available in n98-magerun2 for public use.
::

## Usage

```bash
n98-magerun2 composer:redeploy-base-packages
```

## Description

The `composer:redeploy-base-packages` command scans the configured list of base packages and redeploys their files using the Magento Composer Installer. This is particularly helpful if you have changed deployment mappings, updated ignored files, or need to reapply the deployment logic without reinstalling packages via Composer.

- Detects the Magento root directory.
- Loads the list of base packages from the command configuration.
- For each package:
  - Finds the installed Composer package.
  - Applies the deployment strategy and mappings.
  - Respects the `magento-deploy-ignore` configuration for ignored files.
- Triggers the deployment process for all listed packages.
- Fires the `post-package-install` Composer event to ensure all post-deployment hooks are executed.

:::info
This command does not reinstall or update Composer packages. It only redeploys files according to the current deployment configuration.
:::

:::note
This command was introduced with version 7.1.0.
:::
