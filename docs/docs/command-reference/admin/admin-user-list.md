---
title: admin:user:list
sidebar_label: admin:user:list
---

# `admin:user:list`

Lists admin users.

## Description

The `admin:user:list` command displays a list of all admin users in the Magento installation.
It provides options to format the output and sort the list by various user attributes.

## Options

| Option      | Description                                                                  |
|-------------|------------------------------------------------------------------------------|
| `format`    | Output Format. One of `csv`, `json`, `json_array`, `yaml`, `xml`.              |
| `sort`      | Sort by field (e.g. `user_id`, `username`, `email`, `logdate`). Default: `user_id`. |
| `sort-order`| Sort order direction (`asc` or `desc`). Default: `asc`.                        |
| `columns`    | Comma-separated list of columns to display. See below for available columns. |

## Output Columns

The following columns are available and can be selected with the `--columns` option:

- `user_id` or `id`: The user ID. Both can be used as column names, but the output header will always be `id`.
- `firstname`: The user's first name.
- `lastname`: The user's last name.
- `email`: The email address of the admin user.
- `username`: The username of the admin user.
- `password`: The password hash (for security, use with caution).
- `created`: The date and time the user was created.
- `modified`: The date and time the user was last modified.
- `logdate`: The date and time of the user's last login.
- `lognum`: The number of logins.
- `reload_acl_flag`: Whether ACL needs to be reloaded.
- `is_active`: The status of the user account (e.g., `active`, `inactive`). The output header will be `status`.
- `extra`: Extra data.
- `rp_token`: Reset password token.
- `rp_token_created_at`: When the reset password token was created.
- `interface_locale`: The user's interface locale.
- `failures_num`: Number of failed login attempts.
- `first_failure`: Timestamp of the first failed login attempt.
- `lock_expires`: When the account lock expires.

## Examples

### List all admin users (default sorting by user_id)

```bash
n98-magerun2.phar admin:user:list
```

Output:

```
+----+----------+----------------------+--------+---------------------+
| id | username | email                | status | logdate             |
+----+----------+----------------------+--------+---------------------+
| 1  | admin    | admin@example.com    | active | 2023-10-27 10:00:00 |
| 2  | editor   | editor@example.com   | active | 2023-10-26 15:30:00 |
| 3  | newuser  | newuser@example.com  | active |                     |
+----+----------+----------------------+--------+---------------------+
```

### List admin users and sort by username

```bash
n98-magerun2.phar admin:user:list --sort=username
```

Output:

```
+----+----------+----------------------+--------+---------------------+
| id | username | email                | status | logdate             |
+----+----------+----------------------+--------+---------------------+
| 1  | admin    | admin@example.com    | active | 2023-10-27 10:00:00 |
| 2  | editor   | editor@example.com   | active | 2023-10-26 15:30:00 |
| 3  | newuser  | newuser@example.com  | active |                     |
+----+----------+----------------------+--------+---------------------+
```
(Note: The example data shows `admin`, `editor`, `newuser`. If sorted by username alphabetically, this order is correct.)

### List admin users sorted by email in descending order

```bash
n98-magerun2.phar admin:user:list --sort=email --sort-order=desc
```

Output:

```
+----+----------+----------------------+--------+---------------------+
| id | username | email                | status | logdate             |
+----+----------+----------------------+--------+---------------------+
| 3  | newuser  | newuser@example.com  | active |                     |
| 2  | editor   | editor@example.com   | active | 2023-10-26 15:30:00 |
| 1  | admin    | admin@example.com    | active | 2023-10-27 10:00:00 |
+----+----------+----------------------+--------+---------------------+
```

### List admin users in JSON format

```bash
n98-magerun2.phar admin:user:list --format=json
```

Output:

```json
[
    {
        "id": "1",
        "username": "admin",
        "email": "admin@example.com",
        "status": "active",
        "logdate": "2023-10-27 10:00:00"
    },
    {
        "id": "2",
        "username": "editor",
        "email": "editor@example.com",
        "status": "active",
        "logdate": "2023-10-26 15:30:00"
    },
    {
        "id": "3",
        "username": "newuser",
        "email": "newuser@example.com",
        "status": "active",
        "logdate": null
    }
]
```

### List admin users with additional columns

```bash
n98-magerun2.phar admin:user:list --columns="user_id,firstname,lastname,email,logdate"
```

Output:

```
+----+-----------+----------+----------------------+---------------------+
| id | firstname | lastname | email                | logdate             |
+----+-----------+----------+----------------------+---------------------+
| 1  | John      | Doe      | admin@example.com    | 2023-10-27 10:00:00 |
| 2  | Jane      | Smith    | editor@example.com   | 2023-10-26 15:30:00 |
| 3  | Alice     | Brown    | newuser@example.com  |                     |
+----+-----------+----------+----------------------+---------------------+
```
