---
title: Command Reference
sidebar_label: Overview
---

# Command Reference

Welcome to the n98-magerun2 Command Reference. This section provides a detailed overview of all available commands, categorized by namespace.

## Command Category/Namespace Overview

This table will help you navigate to the relevant group of commands based on their functionality.

| Category/Namespace | Description                                                                 | Example Commands                                                                   |
| :----------------- | :-------------------------------------------------------------------------- | :--------------------------------------------------------------------------------- |
| admin              | Commands for managing Magento admin user accounts and related settings.     | `admin:user:list`, `admin:user:create`, `admin:user:change-password`, `admin:notifications` |
| cache              | Commands for interacting with and managing Magento's various cache systems. | `cache:clean`, `cache:disable`, `cache:enable`, `cache:flush`, `cache:list`            |
| config             | Commands for managing Magento store configurations and environment settings.  | `config:store:get`, `config:store:set`, `config:env:set`, `config:search`              |
| customer           | Commands for managing Magento customer accounts.                            | `customer:create`, `customer:list`, `customer:info`, `customer:change-password`      |
| db                 | Commands for database operations such as dumps, imports, and queries.       | `db:dump`, `db:import`, `db:query`, `db:create`, `db:info`                         |
| dev                | Commands tailored for Magento developers, including code generation and debugging tools. | `dev:module:create`, `dev:console`, `dev:translate:admin`, `dev:theme:list`            |
| eav                | Commands for managing EAV (Entity-Attribute-Value) attributes.            | `eav:attribute:list`, `eav:attribute:view`, `eav:attribute:remove`                 |
| generation         | Commands related to Magento's code generation processes.                    | `generation:flush`                                                                 |
| index              | Commands for managing Magento's indexers.                                   | `index:list`, `index:trigger:recreate`                                             |
| install            | Command for installing Magento.                                             | `install`                                                                          |
| script             | Command for running sequences of n98-magerun2 commands from a file.       | `script`                                                                           |
| sys                | Commands for system-level information, checks, and maintenance tasks.     | `sys:info`, `sys:check`, `sys:maintenance`, `sys:cron:list`, `sys:store:list`        |
