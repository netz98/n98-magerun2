---
sidebar_position: 13
title: Module Development Commands
---
## Create Module Skeleton

Creates an empty module and registers it in current Magento shop.

```sh
n98-magerun2.phar dev:module:create [options] [--] <vendorNamespace> <moduleName>
```
**Arguments:**
| Argument          | Description                     |
|-------------------|---------------------------------|
| `vendorNamespace` | Namespace (your company prefix) |
| `moduleName`      | Name of your module.            |

**Options (selected):**
| Option                          | Description                                         |
|---------------------------------|-----------------------------------------------------|
| `-m, --minimal`                 | Create only module file                             |
| `--add-blocks`                  | Adds blocks                                         |
| `--add-helpers`                 | Adds helpers                                        |
| `--add-models`                  | Adds models                                         |
| `--add-setup`                   | Adds SQL setup                                      |
| `--add-all`                     | Adds blocks, helpers and models                     |
| `-e, --enable`                  | Enable module after creation                        |
| `--modman`                      | Create all files in folder with a modman file.      |
| `--add-readme`                  | Adds a readme.md file to generated module           |
| `--add-composer`                | Adds a composer.json file to generated module       |
| `--add-strict-types`            | Add strict_types declaration to generated PHP files |
| `--author-name[=AUTHOR-NAME]`   | Author for readme.md or composer.json               |
| `--author-email[=AUTHOR-EMAIL]` | Author for readme.md or composer.json               |
| `--description[=DESCRIPTION]`   | Description for readme.md or composer.json          |
