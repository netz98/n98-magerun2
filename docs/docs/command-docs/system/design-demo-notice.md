---
title: design:demo-notice
sidebar_label: design:demo-notice
---

# design:demo-notice

:::info
This command enables or disables the demo store notice in Magento for a specific store view, globally, or for the default scope. The demo store notice is a banner displayed at the top of the storefront, typically used to indicate that the store is running in demo mode.
:::

## Usage

```bash
n98-magerun2 design:demo-notice [options] [--] [<store>]
```

## Arguments

| Argument | Description         |
|----------|---------------------|
| store    | Store code or ID    |

## Options

| Option    | Description                  |
|-----------|------------------------------|
| --on      | Switch on                    |
| --off     | Switch off                   |
| --global  | Set value on default scope   |
| --help    | Display help for the command |

## Examples

:::tip
Enable the demo notice for all store views:
:::

```bash
n98-magerun2 design:demo-notice --on --global
```

:::tip
Disable the demo notice for a specific store view:
:::

```bash
n98-magerun2 design:demo-notice --off default
```

:::tip
Enable the demo notice for store view with code "de":
:::
