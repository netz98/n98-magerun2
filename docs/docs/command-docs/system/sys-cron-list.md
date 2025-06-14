---
title: sys:cron:list
sidebar_label: sys:cron:list
---

# sys:cron:list

:::info
Lists all cronjobs defined in crontab.xml files. This is useful for auditing scheduled tasks and integrating with external tools.
:::

```sh
n98-magerun2.phar sys:cron:list [--format[="..."]]
```

## Options
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
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
