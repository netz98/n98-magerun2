---
title: sys:cron:kill
sidebar_label: sys:cron:kill
---

# sys:cron:kill

:::warning
Jobs can only be killed if the process runs on the same machine as n98-magerun2. Default timeout of a process kill is 5 seconds. If no job is specified, an interactive selection of all running jobs is shown.
:::

```sh
n98-magerun2.phar sys:cron:kill [--timeout <seconds>] [job_code]
```
