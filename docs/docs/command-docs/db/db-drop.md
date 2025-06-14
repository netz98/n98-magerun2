---
title: db:drop
---

# db:drop

Drop current database.

:::warning
This command will permanently delete the current database. Use with extreme caution, especially with the `--force` option, as it will drop the database without confirmation. Ensure you have backups before proceeding.
:::

```sh
n98-magerun2.phar db:drop [options]
```

**Options:**

| Option                   | Description                                                                 |
|--------------------------|-----------------------------------------------------------------------------|
| `--connection=CONNECTION`| Select DB connection type for Magento configurations with several databases |
| `-t, --tables`           | Drop all tables instead of dropping the database                            |
| `-f, --force`            | Force                                                                       |

**Help:**

The command prompts before dropping the database. If `--force` option is specified it directly drops the database. The configured user in `app/etc/env.php` must have "DROP" privileges.
