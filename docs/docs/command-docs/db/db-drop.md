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
| `--drop-views`           | Drop all views in the database                                              |
| `-f, --force`            | Force execution without confirmation                                        |

**Help and Usage Scenarios:**

The command prompts before performing any drop operation unless the `--force` option is specified. The configured user in `app/etc/env.php` must have "DROP" privileges.

The `db:drop` command can be used to drop the entire database, only tables, or only views:

*   **Drop entire database:**
    If run without `--tables` or `--drop-views`, the command will target the entire database.
    ```sh
    n98-magerun2.phar db:drop
    ```
    With force:
    ```sh
    n98-magerun2.phar db:drop --force
    ```
    This action drops all tables, views, and other database objects.

*   **Drop only tables:**
    Use the `--tables` option.
    ```sh
    n98-magerun2.phar db:drop --tables
    ```
    With force:
    ```sh
    n98-magerun2.phar db:drop --tables --force
    ```

*   **Drop only views:**
    Use the `--drop-views` option.
    ```sh
    n98-magerun2.phar db:drop --drop-views
    ```
    With force:
    ```sh
    n98-magerun2.phar db:drop --drop-views --force
    ```

*   **Drop both tables and views (but not other DB objects like procedures, etc.):**
    Specify both `--tables` and `--drop-views`.
    ```sh
    n98-magerun2.phar db:drop --tables --drop-views
    ```
    With force:
    ```sh
    n98-magerun2.phar db:drop --tables --drop-views --force
    ```
