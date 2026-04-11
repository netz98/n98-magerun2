---
title: setup:prepare-upgrade
---

# setup:prepare-upgrade

Run `setup:upgrade` on your current `env.php` database and generate SQL differences versus an original database.

:::warning
This command does **not** clone/import databases for you. Prepare your upgrade target DB first and point `env.php` to it before running this command.
:::

- Requires `mysqldbcompare` (MySQL Utilities)
- Requires the original and upgraded databases to be reachable with `env.php` credentials
- Requires `--original-db`

```sh
n98-magerun2.phar setup:prepare-upgrade --original-db=<name> [options]
```

## Workflow

1. You manually clone production DB (for example with `db:dump` + `db:import`) to a working target DB.
2. You point `app/etc/env.php` to that cloned target DB.
3. `setup:prepare-upgrade` runs `bin/magento setup:upgrade` on the current `env.php` DB.
4. It compares `--original-db` against the upgraded `env.php` DB with `mysqldbcompare`.
5. It writes the resulting SQL diff to a file.

**Options:**

| Option                                    | Description                                                                 |
|-------------------------------------------|-----------------------------------------------------------------------------|
| `--original-db=NAME`                      | Original database name before upgrade (same MySQL server)                  |
| `-o`, `--output-file=FILE`                | Output SQL filename (default: `upgrade-<timestamp>.sql`)                   |
| `--no-data-diff`                          | Only diff schema (DDL), skip data comparison                               |
| `--compare-extra-arg=COMPARE-EXTRA-ARG`  | Forward raw extra argument(s) to `mysqldbcompare` (repeatable)             |
| `--connection=CONNECTION`                 | Select DB connection type for Magento configurations with several databases |

**Examples:**

```sh
# Compare original snapshot vs upgraded env.php database
n98-magerun2.phar setup:prepare-upgrade --original-db=production_snapshot

# Schema-only output with custom filename
n98-magerun2.phar setup:prepare-upgrade --original-db=production_snapshot --no-data-diff -o upgrade-schema.sql

# Pass additional compare flags
n98-magerun2.phar setup:prepare-upgrade --original-db=production_snapshot --compare-extra-arg=--run-all-tests
```

## Applying on production

```sh
n98-magerun2.phar db:import upgrade-20260412_071725.sql
# or
mysql -u user -p database_name < upgrade-20260412_071725.sql
```

## Notes

- The generated SQL file contains a small metadata header plus SQL output from `mysqldbcompare`.
- Use verbose mode (`-v` / `-vv`) to inspect compare output while running.
- Review the SQL before applying in production.
