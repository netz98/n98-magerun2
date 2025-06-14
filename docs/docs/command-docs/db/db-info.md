---
title: db:info
---

# db:info

Dumps database information.

:::info
Use this command to quickly print all information about the current configured database in `app/etc/env.php`, including connection strings for JDBC and PDO. Helpful for debugging and environment checks.
:::

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

This command is useful to print all information about the current configured database in `app/etc/env.php`. It can print connection string for JDBC, PDO connections.
