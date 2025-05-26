---
sidebar_position: 10
title: Admin Commands
---
## List admin users

```sh
n98-magerun2.phar admin:user:list [--format[="..."]]
```
**Options:**

| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


## Change admin user password

```sh
n98-magerun2.phar admin:user:change-password [username] [password]
```

## Delete admin user

```sh
n98-magerun2.phar admin:user:delete [-f|--force] [<id>]
```
**Arguments:**
| Argument | Description        |
|----------|--------------------|
| `id`     | Username or Email  |

**Options:**
| Option     | Description |
|------------|-------------|
| `-f, --force` | Force       |


ID can be e-mail or username. The command will attempt to find the user
by username first and if it cannot be found it will attempt to find the
user by e-mail. If ID is omitted you will be prompted for it. If the
force parameter `-f` is omitted you will be prompted for confirmation.

## Create Admin Token for Webapi

```sh
n98-magerun2.phar admin:token:create <username> [--no-newline]
```
**Options:**
| Option         | Description                        |
|----------------|------------------------------------|
| `--no-newline` | Do not output the trailing newline |

## admin:notifications
Toggles admin notifications.
```sh
n98-magerun2.phar admin:notifications [options]
```
**Options:**
| Option | Description |
|--------|-------------|
| `--on` | Switch on   |
| `--off`| Switch off  |
