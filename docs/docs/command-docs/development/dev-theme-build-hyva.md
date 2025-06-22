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
| theme-name   | Hyvä Theme to build (e.g. Hyva/default) |

**Options:**

| Option                          | Description                                                |
|---------------------------------|------------------------------------------------------------|
| --production                    | Build CSS for production (minified output)                 |
| --all                           | Build CSS for all Hyvä themes                              |
| --suppress-no-theme-found-error | Suppress error if no Hyvä theme was found when using --all |

:::tip
**--all**

Use this option to build CSS for all Hyvä themes in your Magento installation. No theme argument is required when using this option.
:::

:::tip
**--suppress-no-theme-found-error**

Use this option together with `--all` to suppress the error if no Hyvä theme is found. The command will exit successfully instead of returning an error.
:::
