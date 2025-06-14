---
title: admin:user:delete
---

# admin:user:delete

Delete admin user.

```sh
n98-magerun2.phar admin:user:delete [-f|--force] [<id>]
```

**Arguments:**

| Argument | Description        |
|----------|--------------------|
| `id`     | Username or Email  |

**Options:**

| Option        | Description |
|---------------|-------------|
| `-f, --force` | Force       |

ID can be e-mail or username. The command will attempt to find the user by username first and if it cannot be found it will attempt to find the user by e-mail. If ID is omitted you will be prompted for it. If the force parameter `-f` is omitted you will be prompted for confirmation.

