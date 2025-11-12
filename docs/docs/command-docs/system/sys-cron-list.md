---
title: sys:cron:list
sidebar_label: sys:cron:list
---

# sys:cron:list

:::info
Lists all cronjobs defined in crontab.xml files. This is useful for auditing scheduled tasks and integrating with external tools.
:::

```sh
n98-magerun2.phar sys:cron:list [<job_code>] [--format[="..."]]
```

## Arguments
| Argument     | Description                                                                                                                                        |
|--------------|----------------------------------------------------------------------------------------------------------------------------------------------------|
| `<job_code>` | *(Optional)* Filter the output to cronjobs matching the given code. Supports wildcards (`*`) so you can target groups of jobs (e.g., `catalog_*`). |

## Options
| Option              | Description                                          |
|---------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |

The `--format` option allows you to specify the output format for the list of cronjobs. Supported formats are:
- csv
- json
- json_array
- yaml
- xml

:::tip
This is useful for integrating the output with other tools or for easier data processing.
:::
