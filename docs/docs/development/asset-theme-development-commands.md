---
sidebar_position: 12
title: Asset and Theme Development Commands
---
## Clear static view files

```sh
n98-magerun2.phar dev:asset:clear [--theme="..."]
```

Options:

| Option                  | Description                                                        |
|-------------------------|--------------------------------------------------------------------|
| `-t, --theme=THEME`     | Clear assets for specific theme(s) only (multiple values allowed). |


To clear assets for all themes:

```sh
n98-magerun2.phar dev:asset:clear
```

To clear assets for specific theme(s) only:

```sh
n98-magerun2.phar dev:asset:clear --theme=Magento/luma
```

## List Themes

```sh
n98-magerun2.phar dev:theme:list [--format[=FORMAT]]
```
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


## Build Hyva Theme CSS

```sh
n98-magerun2.phar dev:theme:build-hyva [--production] [<theme-name>]
```
**Arguments:**
| Argument     | Description                                |
|--------------|--------------------------------------------|
| `theme-name` | Hyvä Theme to build (e.g. Hyva/default)    |
**Options:**
| Option           | Description                                           |
|------------------|-------------------------------------------------------|
| `-p, --production`| Build for production (minified) instead of watch mode |


Example: `n98-magerun2.phar dev:theme:build-hyva "Hyva/default"`

The command starts in watch mode by default, as it is primarily designed for developers.

If no theme is specified, an interactive mode allows you to select a theme from a list.

If the `--production` flag is set, the command does not run in watch mode and will stop after the theme is built.
