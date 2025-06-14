---
title: sys:cron:kill
sidebar_label: sys:cron:kill
---

Kill a running cronjob.

```sh
n98-magerun2.phar sys:cron:kill [--timeout <seconds>] [job_code]
```

If no job is specified, an interactive selection of all running jobs is shown. Jobs can only be killed if the process runs on the same machine as n98-magerun2. Default timeout of a process kill is 5 seconds.

