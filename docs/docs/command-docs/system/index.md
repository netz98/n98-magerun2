---
sidebar_position: 1
title: System Commands
---

# System Commands

:::info
Commands for system-level information, checks, and maintenance tasks in Magento. Use these to inspect, maintain, and troubleshoot your Magento installation.
:::

## Commands

### System Information
- sys:info - Provide system information like edition, version, cache backends
- [sys:store:list](./sys-store-list.md) - List all store views
- sys:website:list - List all websites

### Cron Jobs
- [sys:cron:list](./sys-cron-list.md) - List all cronjobs defined in crontab.xml files
- [sys:cron:run](./sys-cron-run.md) - Run a cronjob by code
- [sys:cron:kill](./sys-cron-kill.md) - Kill a running job
- [sys:cron:history](./sys-cron-history.md) - Show last executed cronjobs with status
- [sys:cron:schedule](./sys-cron-schedule.md) - Schedule a cronjob for execution right now

### Setup and Versions
- [sys:setup:compare-versions](./sys-setup-compare-versions.md) - Compare module version with saved setup version
- [sys:setup:change-version](./sys-setup-change-version.md) - Change the version of a module
- [sys:setup:downgrade-versions](./sys-setup-downgrade-versions.md) - Downgrade the versions in the database

### Store URLs and Configuration
- [sys:store:config:base-url:list](./sys-store-config-base-url-list.md) - List all configured store URLs
- [sys:url:list](./sys-url-list.md) - Get all URLs (products, categories, CMS pages)

### System Maintenance
- [sys:check](./sys-check.md) - Check Magento system for issues
- [sys:maintenance](./sys-maintenance.md) - Toggle maintenance mode
- [design:demo-notice](./design-demo-notice.md) - Toggle demo store notice for a store view
