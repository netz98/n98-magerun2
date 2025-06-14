---
title: db:variables
---

# db:variables

Shows important variables or custom selected.

:::tip
Use this command to check MySQL server configuration or troubleshoot issues by viewing important server variables. You can filter results using the `search` argument.
:::

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
