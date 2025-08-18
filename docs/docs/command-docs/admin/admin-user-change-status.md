---
title: admin:user:change-status
---

# admin:user:change-status

Changes the status of an admin user.

The command can be used to activate or deactivate a user. If no option is provided, the status will be toggled.

## Usage

```bash
n98-magerun2.phar admin:user:change-status [options] <username>
```

## Arguments

| Argument | Description |
|---|---|
| `username` | The username or email of the admin user to modify. |

## Options

| Option | Description |
|---|---|
| `--activate` | Activates the specified user. |
| `--deactivate` | Deactivates the specified user. |

## Examples

### Activate a user

```bash
n98-magerun2.phar admin:user:change-status --activate john.doe
```

### Deactivate a user

```bash
n98-magerun2.phar admin:user:change-status --deactivate john.doe
```

### Toggle a user's status

If `john.doe` is currently active, this command will deactivate them. If they are inactive, it will activate them.

```bash
n98-magerun2.phar admin:user:change-status john.doe
```
