---
title: dev:theme:build-hyva
sidebar_position: 12
---

## Build Hyva Theme CSS

:::info
This command builds the CSS for a Hyvä theme. Use the `--production` option for minified output suitable for live environments.
:::

```sh
n98-magerun2.phar dev:theme:build-hyva [--production] [<theme-name>]
```

**Arguments:**

| Argument     | Description                             |
|--------------|-----------------------------------------|
| theme-name   | Hyva Theme to build (e.g. Hyva/default) |

**Options:**

| Option                          | Description                                                |
|---------------------------------|------------------------------------------------------------|
| --production                    | Build CSS for production (minified output)                 |
| --all                           | Build CSS for all Hyva themes                              |
| --suppress-no-theme-found-error | Suppress error if no Hyva theme was found when using --all |
| --force-npm-install             | Always run `npm install` before building, even if `node_modules` exists |

:::tip
**--all**

Use this option to build CSS for all Hyva themes in your Magento installation. No theme argument is required when using this option.
:::

:::tip
**--suppress-no-theme-found-error**

Use this option together with `--all` to suppress the error if no Hyva theme is found. The command will exit successfully instead of returning an error.
:::

:::tip
**--force-npm-install**

Use this option to force a fresh `npm install` before building the theme CSS. This is useful if you want to ensure all dependencies are up to date or if you encounter build issues related to missing or outdated node modules.
:::

:::info
**Timeouts**

- The `npm install` process has a timeout of 1 hour (3600 seconds) to prevent hanging on long installs.
- The build process (`npm run watch` or `npm run build-prod`) has no timeout in watch mode, so it will run until you stop it (Ctrl+C). In production mode, it will run until the build completes.
:::
