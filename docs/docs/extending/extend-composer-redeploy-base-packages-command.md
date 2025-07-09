---
title: Extending composer:redeploy-base-packages Command
sidebar_label: Extending composer:redeploy-base-packages
---

# Extending the composer:redeploy-base-packages Command

The `composer:redeploy-base-packages` command redeploys all base packages as defined in the command configuration. You can extend the list of packages that are redeployed by providing your own configuration file.

## How to Add New Packages

By default, the list of base packages is defined in the `config.yaml` file shipped with `n98-magerun2.phar`:

```yaml
commands:
  N98\Magento\Command\Composer\RedeployBasePackagesCommand:
    packages:
      - magento/magento2-base
      # ... other default packages
```

To add your own packages, create a custom configuration file (e.g., `~/.n98-magerun2.yaml` for global use or `app/etc/n98-magerun2.yaml` for project-specific use) and extend the `packages` list:

```yaml
commands:
  N98\Magento\Command\Composer\RedeployBasePackagesCommand:
    packages:
      - my-vendor/my-package
```

When you run:

```bash
n98-magerun2.phar composer:redeploy-base-packages
```

The command will read the merged configuration (default + your custom config) and redeploy all packages listed under `packages`.


:::important
Base packages must contain a static file mapping in the `composer.json` file.

This typically looks like this:

```json
"extra": {        
  "map": [
    [
      ".editorconfig",
      ".editorconfig"
    ]
}
```
:::
