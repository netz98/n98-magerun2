---
sidebar_position: 19
title: Dev Utilities - Report, Symlinks, Template Hints
---
## dev:report:count
Get count of report files.
```sh
n98-magerun2.phar dev:report:count
```

## dev:symlinks
Toggle allow symlinks setting.
```sh
n98-magerun2.phar dev:symlinks [options] [--] [<store>]
```
**Arguments:**
| Argument | Description    |
|----------|----------------|
| `store`  | Store code or ID |
**Options:**
| Option   | Description                 |
|----------|-----------------------------|
| `--on`   | Switch on                   |
| `--off`  | Switch off                  |
| `--global`| Set value on default scope  |

## dev:template-hints
Toggles template hints.
```sh
n98-magerun2.phar dev:template-hints [options] [--] [<store>]
```
**Arguments:**
| Argument | Description    |
|----------|----------------|
| `store`  | Store code or ID |
**Options:**
| Option | Description |
|--------|-------------|
| `--on` | Switch on   |
| `--off`| Switch off  |

## dev:template-hints-blocks
Toggles template hints block names.
```sh
n98-magerun2.phar dev:template-hints-blocks [options] [--] [<store>]
```
**Arguments:**
| Argument | Description    |
|----------|----------------|
| `store`  | Store code or ID |
**Options:**
| Option | Description |
|--------|-------------|
| `--on` | Switch on   |
| `--off`| Switch off  |
