---
title: db:query
---

# db:query

Run a raw DB query

:::warning
Running raw SQL queries can affect your database and should be done with caution, especially in production environments. Always review your queries before execution.
:::

```sh
n98-magerun2.phar db:query [--connection=CONNECTION] [--only-command] [<query>]
```

**Arguments:**

| Argument | Description |
|----------|-------------|
| `query`  | SQL query   |

**Options:**

| Option                   | Description                                                                 |
|--------------------------|-----------------------------------------------------------------------------|
| `--connection=CONNECTION`| Select DB connection type for Magento configurations with several databases |
| `--only-command`         | Print only mysql command. Do not execute                                    |

**Example:**

```sh
n98-magerun2.phar db:query "select * from store"
```
