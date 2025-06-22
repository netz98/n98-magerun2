---
title: sys:cron:schedule
sidebar_label: sys:cron:schedule
---

# sys:cron:schedule

Schedule a cronjob for execution right now.

## Usage

```bash
n98-magerun2.phar sys:cron:schedule <job_code>
```

- `<job_code>`: The code of the cronjob to schedule for immediate execution.

## Description

This command schedules a Magento cronjob (defined in crontab.xml) to be executed as soon as possible. It is useful for testing or triggering specific jobs without waiting for the next scheduled run.

## Example

Schedule the `indexer_reindex_all_invalid` cronjob:

```bash
n98-magerun2.phar sys:cron:schedule indexer_reindex_all_invalid
```

## Options

- `--help`  Show help for the command

## Related Commands

- [sys:cron:list](./sys-cron-list.md)
- [sys:cron:run](./sys-cron-run.md)
- [sys:cron:kill](./sys-cron-kill.md)
- [sys:cron:history](./sys-cron-history.md)

