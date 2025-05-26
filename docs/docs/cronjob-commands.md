---
sidebar_position: 6
title: Cronjob Commands
---
## List Cronjobs

Lists all cronjobs defined in crontab.xml files.

```sh
n98-magerun2.phar sys:cron:list [--format[="..."]]
```

## Run Cronjobs

Runs a cronjob by code.

```sh
n98-magerun2.phar sys:cron:run [job]
```

If no `job` argument is passed you can select a job from a list.
See it in action: <http://www.youtube.com/watch?v=QkzkLgrfNaM>
If option schedule is present, cron is not launched, but just scheduled immediately in magento crontab.

## Kill a running job

```sh
n98-magerun2.phar sys:cron:kill [--timeout <seconds>] [job_code]
```

If no job is specified a interactive selection of all running jobs is shown.
Jobs can only be killed if the process runs on the same machine as n98-magerun2.

Default timeout of a process kill is 5 seconds.

## Cronjob History

Last executed cronjobs with status.

```sh
n98-magerun2.phar sys:cron:history [--format[="..."]] [--timezone[="..."]]
```
**Options:**

| Option                | Description                                          |
|-----------------------|------------------------------------------------------|
| `--timezone[=TIMEZONE]`| Timezone to show finished at in                      |
| `--format[=FORMAT]`   | Output Format. One of [csv,json,json_array,yaml,xml] |

## sys:cron:schedule
Schedule a cronjob for execution right now, by job code.
```sh
n98-magerun2.phar sys:cron:schedule [<job>]
```
**Arguments:**
| Argument | Description |
|----------|-------------|
| `job`    | Job code    |
**Help:**
If no `job` argument is passed you can select a job from a list.
