---
title: sys:cron:list
sidebar_label: sys:cron:list
---

Lists all cronjobs defined in crontab.xml files.

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

This is useful for integrating the output with other tools or for easier data processing
