---
title: sys:cron:run
sidebar_label: sys:cron:run
---

Runs a cronjob by code.

```sh
n98-magerun2.phar sys:cron:run [job]
```

If no `job` argument is passed you can select a job from a list.
If option schedule is present, cron is not launched, but just scheduled immediately in Magento crontab.

