---
title: Database Commands
---
### Run a raw DB query

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

### Open MySQL Console

```sh
n98-magerun2.phar db:console [options]
```

**Options:**

| Option                         | Description                                                                                          |
|--------------------------------|------------------------------------------------------------------------------------------------------|
| `--use-mycli-instead-of-mysql` | Use `mycli` as the MySQL client instead of `mysql`                                                   |
| `--no-auto-rehash`             | Same as `-A` option to MySQL client to turn off auto-complete (avoids long initial connection time). |
| `--connection=CONNECTION`      | Select DB connection type for Magento configurations with several databases (default: `default`)     |

### Dump database

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
| `--zstd-level[=ZSTD-LEVEL]`         | ZSTD compression level. (default: `10`)                                                                                              |
| `--zstd-extra-args[=ZSTD-EXTRA-ARGS]` | Custom extra options for zstd.                                                                                                       |

(For a full list of strip table groups and other options, use `n98-magerun2.phar help db:dump`)


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

#### Stripped Database Dump

Dumps your database and excludes some tables. This is useful for
development or staging environments where you may want to provision a
restricted database.

Separate each table to strip by a space. You can use wildcards like `*` and `?` in the table names to strip multiple
tables.
In addition, you can specify pre-defined table groups, that start with an @ sign.

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

### Import database

- Requires MySQL CLI tools

Arguments:

- filename - Dump filename

Options:

| Option                                | Description                                                                        |
|---------------------------------------|------------------------------------------------------------------------------------|
| `--connection=CONNECTION`             | Select DB connection type for Magento configurations with several databases        |
| `-c`, `--compression=COMPRESSION`     | The compression of the specified file (e.g. `gzip`, `lz4`, `zstd`)                 |
| `--zstd-level[=ZSTD-LEVEL]`           | ZSTD compression level [default: 10]                                               |
| `--zstd-extra-args[=ZSTD-EXTRA-ARGS]` | Custom extra options for zstd                                                      |
| `--drop`                              | Drop and recreate database before import                                           |
| `--drop-tables`                       | Drop tables before import                                                          |
| `--force`                             | Continue even if an SQL error occurs                                               |
| `--only-command`                      | Print only mysql command. Do not execute                                           |
| `--only-if-empty`                     | Imports only if database is empty                                                  |
| `--optimize`                          | Convert verbose INSERTs to short ones before import (not working with compression) |
| `--skip-authorization-entry-creation` | Do not create authorization rule/role entries if they are missing.                 |


```sh
n98-magerun2.phar db:import [options] [<filename>]
```

### Fix empty authorization tables

If you run `db:dump` with stripped option and `@admin` group, the authorization_rule and authorization_role tables are
empty.
This blocks the creation of admin users.

You can re-create the default entries by running the command:

```sh
n98-magerun2.phar db:add-default-authorization-entries [--connection=CONNECTION]
```

If you are using the `db:import` command to import the stripped SQL dump, then this command will be implicitly called unless `--skip-authorization-entry-creation` is used.

### db:create
Create currently configured database.
```sh
n98-magerun2.phar db:create [--connection=CONNECTION]
```
**Options:**
| Option                   | Description                                                                 |
|--------------------------|-----------------------------------------------------------------------------|
| `--connection=CONNECTION`| Select DB connection type for Magento configurations with several databases |
**Help:**
The command tries to create the configured database according to your
settings in app/etc/env.php.
The configured user must have "CREATE DATABASE" privileges on MySQL Server.
---
### db:drop
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
The command prompts before dropping the database. If --force option is specified it
directly drops the database.
The configured user in app/etc/env.php must have "DROP" privileges.
---
### db:info
Dumps database informations.
```sh
n98-magerun2.phar db:info [options] [--] [<setting>]
```
**Arguments:**
| Argument  | Description                        |
|-----------|------------------------------------|
| `setting` | Only output value of named setting |
**Options:**
| Option                   | Description                                                                 |
|--------------------------|-----------------------------------------------------------------------------|
| `--connection=CONNECTION`| Select DB connection type for Magento configurations with several databases |
| `--format[=FORMAT]`      | Output Format. One of [csv,json,json_array,yaml,xml]                        |
**Help:**
This command is useful to print all informations about the current configured database in app/etc/env.php.
It can print connection string for JDBC, PDO connections.
---
### db:maintain:check-tables
Check database tables.
```sh
n98-magerun2.phar db:maintain:check-tables [options]
```
**Options:**
| Option             | Description                                                                 |
|--------------------|-----------------------------------------------------------------------------|
| `--type[=TYPE]`    | Check type (one of QUICK, FAST, MEDIUM, EXTENDED, CHANGED) [default: "MEDIUM"] |
| `--repair`         | Repair tables (only MyISAM)                                                 |
| `--table[=TABLE]`  | Process only given table (wildcards are supported)                          |
| `--format[=FORMAT]`| Output Format. One of [csv,json,json_array,yaml,xml]                        |
---
### db:status
Shows important server status information or custom selected status values.
```sh
n98-magerun2.phar db:status [options] [--] [<search>]
```
**Arguments:**
| Argument | Description                                                              |
|----------|--------------------------------------------------------------------------|
| `search` | Only output variables of specified name. The wildcard % is supported!    |
**Options:**
| Option                   | Description                                                                 |
|--------------------------|-----------------------------------------------------------------------------|
| `--connection=CONNECTION`| Select DB connection type for Magento configurations with several databases |
| `--format[=FORMAT]`      | Output Format. One of [csv,json,json_array,yaml,xml]                        |
| `--rounding[=ROUNDING]`  | Amount of decimals to display. If -1 then disabled [default: 0]             |
| `--no-description`       | Disable description                                                         |
---
### db:variables
Shows important variables or custom selected.
```sh
n98-magerun2.phar db:variables [options] [--] [<search>]
```
**Arguments:**
| Argument | Description                                                              |
|----------|--------------------------------------------------------------------------|
| `search` | Only output variables of specified name. The wildcard % is supported!    |
**Options:**
| Option                   | Description                                                                 |
|--------------------------|-----------------------------------------------------------------------------|
| `--connection=CONNECTION`| Select DB connection type for Magento configurations with several databases |
| `--format[=FORMAT]`      | Output Format. One of [csv,json,json_array,yaml,xml]                        |
| `--rounding[=ROUNDING]`  | Amount of decimals to display. If -1 then disabled [default: 0]             |
| `--no-description`       | Disable description                                                         |
---
