---
title: Extend db:dump Command
---

Extend the [db:dump](../command-docs/db/db-dump.md) command to add your own table groups.

## Add your own groups

You can extend or modify the groups by creating your own config **~/.n98-magerun2.yaml** or project-specific config in **app/etc/n98-magerun2.yaml** file.

Example:

```yaml
commands:
  N98\Magento\Command\Database\DumpCommand:
    table-groups:
      - id: "n98"
        description: "Tables starting with n98"
        tables: "n98*"
      - id: "foo"
        description: "Mix groups and single table names"
        tables: "foo bar @log"
      - id: "development_custom"
        description: "Removes logs and trade data so developers do not have to work with real customer data"
        tables: "@development @n98"
```

Verify with `db:dump --help` if your new groups are registered.
