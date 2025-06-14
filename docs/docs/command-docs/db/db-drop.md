---
title: db:drop
---

# db:drop

Drop current database.

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
