---
title: db:add-default-authorization-entries
---

# db:add-default-authorization-entries

Fix empty authorization tables

If you run `db:dump` with stripped option and `@admin` group, the `authorization_rule` and `authorization_role` tables are empty. This blocks the creation of admin users.

You can re-create the default entries by running the command:

```sh
n98-magerun2.phar db:add-default-authorization-entries [--connection=CONNECTION]
```

If you are using the `db:import` command to import the stripped SQL dump, then this command will be implicitly called unless `--skip-authorization-entry-creation` is used.
