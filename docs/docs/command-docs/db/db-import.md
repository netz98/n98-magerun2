---
title: db:import
---

# db:import

Import database

:::warning
Using the `--drop` or `--drop-tables` options will remove existing data before import. Make sure you have backups and understand the consequences before using these options.
:::

- Requires MySQL CLI tools

```sh
n98-magerun2.phar db:import [options] [<filename>]
```

**Arguments:**

- filename - Dump filename

**Options:**

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
