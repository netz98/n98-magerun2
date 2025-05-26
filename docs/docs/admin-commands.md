---
title: Admin Commands
---
### List admin users

```sh
n98-magerun2.phar admin:user:list [--format[="..."]]
```
**Options:**

| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


### Change admin user password

```sh
n98-magerun2.phar admin:user:change-password [username] [password]
```

### Delete admin user

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

### Create Admin Token for Webapi

```sh
n98-magerun2.phar admin:token:create <username> [--no-newline]
```
**Options:**
| Option         | Description                        |
|----------------|------------------------------------|
| `--no-newline` | Do not output the trailing newline |

### admin:notifications
Toggles admin notifications.
```sh
n98-magerun2.phar admin:notifications [options]
```
**Options:**
| Option | Description |
|--------|-------------|
| `--on` | Switch on   |
| `--off`| Switch off  |
---

### Change Admin user status

Changes the admin user based on the options, the command will toggle
the status if no options are supplied.

```sh
n98-magerun2.phar admin:user:change-status [options] [--] <user>
```
**Arguments:**
| Argument | Description                             |
|----------|-----------------------------------------|
| `user`   | Username or email for the admin user    |
**Options:**
| Option        | Description        |
|---------------|--------------------|
| `--activate`  | Activate the user  |
| `--deactivate`| Deactivate the user|


*Note: It is possible for a user to exist with a username that matches
the email of a different user. In this case the first matched user will be changed.*
