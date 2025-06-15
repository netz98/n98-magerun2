---
title: db:dump
---

# db:dump

Dump database

:::info
Requires MySQL CLI tools (`mysqldump` or `mydumper`). Use the `--force` option with caution, as it will skip confirmation prompts. The `--strip` option can remove important data from the dump; review your table groups before using it.
:::

Dumps configured Magento database with `mysqldump` or `mydumper`.

- Requires MySQL CLI tools (either `mysqldump` or `mydumper`)

```sh
n98-magerun2.phar db:dump [options] [--] [<filename>]
```

**Arguments:**

| Argument   | Description    |
|------------|----------------|
| `filename` | Dump filename. |

**Options (selected):**

| Option                              | Description                                                                                                                          |
|-------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------|
| `--connection=CONNECTION`           | Select DB connection type for Magento configurations with several databases (default: `default`)                                       |
| `--add-routines`                    | Include stored routines in dump (procedures & functions).                                                                            |
| `--add-time[=ADD-TIME]`             | Append or prepend a timestamp to filename. Values: `suffix`, `prefix`, `no`. (default: `no`)                                          |
| `-c, --compression=COMPRESSION`     | Compress the dump file using one of the supported algorithms (e.g., `gzip`, `lz4`, `zstd`).                                            |
| `--dry-run`                         | Do everything but the actual dump. Useful to test.                                                                                   |
| `-e, --exclude=EXCLUDE`             | Tables to exclude entirely from the dump (including structure). Multiple values allowed.                                               |
| `-f, --force`                       | Do not prompt if all options are defined.                                                                                            |
| `--git-friendly`                    | Use one insert statement, but with line breaks instead of separate insert statements.                                                |
| `--human-readable`                  | Use a single insert with column names per row. Use `db:import --optimize` for faster import.                                         |
| `-i, --include=INCLUDE`             | Tables to include entirely in the dump (default: all tables are included). Multiple values allowed.                                  |
| `--keep-definer`                    | Do not replace DEFINER in dump with CURRENT_USER.                                                                                    |
| `--keep-column-statistics`          | Retains `column statistics` table in `mysqldump`.                                                                                    |
| `--mydumper`                        | Use mydumper instead of mysqldump for potentially faster dumps.                                                                      |
| `--no-single-transaction`           | Do not use single-transaction (not recommended, this is blocking).                                                                   |
| `--no-tablespaces`                  | Use this option if you want to create a dump without having the PROCESS privilege.                                                   |
| `--only-command`                    | Print only mysqldump/mydumper command. Does not execute.                                                                             |
| `--print-only-filename`             | Execute and prints no output except the dump filename.                                                                               |
| `--set-gtid-purged-off`             | Adds --set-gtid-purged=OFF to mysqldump.                                                                                             |
| `--stdout`                          | Dump to stdout.                                                                                                                      |
| `-s, --strip=STRIP`                 | Tables to strip (dump only structure of those tables). Multiple values and table groups (e.g. `@log`) allowed.                        |
| `--views`                           | Explicitly include views in the dump. Views are included by default if not otherwise excluded by name or by `--no-views`.              |
| `--no-views`                        | Exclude all views from the dump. This overrides any other view inclusion.                                                            |
| `--zstd-level[=ZSTD-LEVEL]`         | ZSTD compression level. (default: `10`)                                                                                              |
| `--zstd-extra-args[=ZSTD-EXTRA-ARGS]` | Custom extra options for zstd.                                                                                                       |

(For a full list of strip table groups and other options, use `n98-magerun2.phar help db:dump`)

**View Handling in Dumps:**

By default, `db:dump` includes views if their underlying tables are dumped or if the entire database is dumped. The following options provide more control:

*   `--views`: This option can be used to explicitly state that views should be included. Since views are generally included by default if not otherwise excluded (e.g., by a specific table exclusion pattern that happens to match a view name, or by `--no-views`), this option is mainly for clarity or to ensure views are included if a very broad exclusion pattern might accidentally exclude them.
*   `--no-views`: This option ensures that **no views** are included in the dump. Their definitions will not be present in the SQL file. This option takes precedence over any other rules that might otherwise include a view (e.g., if a view name matches an `--include` pattern or is part of a table list provided for the dump).

**Examples:**

Dump database without any views:
```sh
n98-magerun2.phar db:dump --no-views dump_without_views.sql
```

Explicitly include views (usually default behavior):
```sh
n98-magerun2.phar db:dump --views dump_with_views.sql
```

If `my_view_name` is a view, and you want to ensure its definition is not dumped, even if it was part of a `--strip` pattern that would normally dump structure:
```sh
n98-magerun2.phar db:dump --strip="my_view_name" --no-views dump_stripped_no_view_def.sql
```

Only the dump command:

```sh
n98-magerun2.phar db:dump --only-command [filename]
```

Or directly to stdout:

```sh
n98-magerun2.phar db:dump --stdout
```

Use compression (gzip cli tool has to be installed):

```sh
n98-magerun2.phar db:dump --compression="gzip"
```

Use mydumper for faster dumps:

```sh
n98-magerun2.phar db:dump --mydumper
```

## Stripped Database Dump

Dumps your database and excludes some tables. This is useful for development or staging environments where you may want to provision a restricted database.

Separate each table to strip by a space. You can use wildcards like `*` and `?` in the table names to strip multiple tables. In addition, you can specify pre-defined table groups, that start with an @ sign.

Example: `dataflow_batch_export unimportant_module_* @log`

```sh
n98-magerun2.phar db:dump --strip="@stripped"
```

Available Table Groups:

| Table Group           | Description                                                                                                                          |
|-----------------------|--------------------------------------------------------------------------------------------------------------------------------------|
| `@2fa`                | 2FA tables. These tables are used for storing 2FA information for admin users.                                                       |
| `@admin`              | Admin users, roles, sessions, etc.                                                                                                   |
| `@aggregated`         | Aggregated tables used for generating reports, etc.                                                                                  |
| `@dotmailer`          | Dotmailer data(`email_abandoned_cart` `email_automation` `email_campaign` `email_contact`).                                          |
| `@customers`          | Customer data (and company data from the B2B extension).                                                                             |
| `@development`        | Removes logs, sessions, trade data and admin users so developers do not have to work with real customer data or admin user accounts. |
| `@dotmailer`          | Dotmailer module tables                                                                                                              |
| `@ee_changelog`       | Changelog tables of new indexer since EE 1.13                                                                                        |
| `@idx`                | Tables with `_idx` suffix and index event tables.                                                                                    |
| `@klarna`             | Klarna tables containing information about klarna payments and their quotes/orders.                                                  |
| `@log`                | Log tables.                                                                                                                          |
| `@mailchimp`          | Mailchimp tables.                                                                                                                    |
| `@newrelic_reporting` | New Relic reporting tables. These tables provide production metric data for New Relic.                                               |
| `@oauth`              | OAuth sessions, tokens, etc.                                                                                                         |
| `@quotes`             | Cart (quote) data and B2B quotes.                                                                                                    |
| `@replica`            | Replica tables, these are generated from Magento Staging functionality.                                                              |
| `@sales`              | Sales data (orders, invoices, creditmemos etc).                                                                                      |
| `@search`             | Search related tables (catalogsearch\_).                                                                                             |
| `@sessions`           | Database session tables.                                                                                                             |
| `@stripped`           | Standard definition for a stripped dump (logs and sessions).                                                                         |
| `@trade`              | Current trade data (customers, orders and quotes). You usually do not want those in developer systems.                               |
| `@temp`               | Indexer __temp tables.                                                                                                               |

---

You can extend the db:dump with own table groups. Have a look here: [Extend db:dump Command](../../extending/extend-db-dump-command.md#add-your-own-groups)
