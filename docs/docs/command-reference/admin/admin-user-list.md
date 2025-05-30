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

| Option   | Description                                                                  |
|----------|------------------------------------------------------------------------------|
| `format` | Output Format. One of `csv`, `json`, `json_array`, `yaml`, `xml`.              |
| `sort`   | Sort by field (e.g. `user_id`, `username`, `email`, `logdate`). Default: `user_id`. |

## Output Columns

The command output includes the following columns:

- `id`: The user ID.
- `username`: The username of the admin user.
- `email`: The email address of the admin user.
- `status`: The status of the user account (e.g., `active`, `inactive`).
- `logdate`: The date and time of the user's last login. This column might be empty if the user has never logged in.

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
