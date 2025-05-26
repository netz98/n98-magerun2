---
sidebar_position: 24
title: Setup and Store URL Commands
---
## Compare Setup Versions

Compares module version with saved setup version in
`setup_module` table and displays version mismatchs if
found.

```sh
n98-magerun2.phar sys:setup:compare-versions [--ignore-data] [--log-junit="..."] [--format[="..."]]
```
**Options:**
| Option                 | Description                                          |
|------------------------|------------------------------------------------------|
| `--ignore-data`        | Ignore data updates                                  |
| `--log-junit=LOG-JUNIT`| Log output to a JUnit xml file.                      |
| `--format[=FORMAT]`    | Output Format. One of [csv,json,json_array,yaml,xml] |


- If a filename with `--log-junit` option is set the tool generates an XML file and no output to *stdout*.

## Change Setup Version

Changes the version of a module. This command is useful if you want to
re-run an upgrade script again possibly for debugging. Alternatively you
would have to alter the row in the database manually.

```sh
n98-magerun2.phar sys:setup:change-version <module> <version>
```
**Arguments:**
| Argument  | Description        |
|-----------|--------------------|
| `module`  | Module name        |
| `version` | New version value  |

---

## Downgrade Setup Versions

Downgrade the versions in the database to the module version from its
xml file if necessary. Useful while developing and switching branches
between module version changes.

```sh
n98-magerun2.phar sys:setup:downgrade-versions [--dry-run]
```
**Options:**
| Option     | Description                                       |
|------------|---------------------------------------------------|
| `--dry-run`| Write what to change but do not do any changes    |


## List all configured store URLs

The default behavior is to show the base URL of all stores except the admin store.
If you want to show the base URL of the admin store as well, use the `--with-admin-store` option.
If you want to show the admin login URL as well, use the `--with-admin-login-url` option.
The options `--with-admin-store` and `--with-admin-login-url` cannot be combined, because both print a url for the same store.

```sh
n98-magerun2.phar sys:store:config:base-url:list [options]
```
**Options:**
| Option                      | Description                                          |
|-----------------------------|------------------------------------------------------|
| `--with-admin-store`        | Include admin store                                  |
| `--with-admin-login-url`    | Include admin login url                              |
| `--format[=FORMAT]`         | Output Format. One of [csv,json,json_array,yaml,xml] |
