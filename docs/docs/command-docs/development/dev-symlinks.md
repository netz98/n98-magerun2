---
title: dev:symlinks
sidebar_position: 19
---

## Toggle allow symlinks setting

:::info
This command toggles the "allow symlinks" setting in Magento, which can be useful for development environments where symlinks are needed for static content or modules.
:::

```sh
n98-magerun2.phar dev:symlinks [options] [--] [<store>]
```

**Arguments:**

| Argument | Description      |
|----------|-----------------|
| store    | Store code or ID |

**Options:**

| Option     | Description                  |
|------------|------------------------------|
| --on       | Switch on                    |
| --off      | Switch off                   |
| --global   | Set value on default scope   |
