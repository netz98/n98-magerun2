---
title: dev:module:sequence
---

# dev:module:sequence

Regenerate the dependency-ordered `modules` section of `app/etc/config.php` from
the modules in the current checkout, without running `setup:upgrade` or database
migrations.

```bash
n98-magerun2 dev:module:sequence
```

Use this after resolving a merge or rebase conflict in `config.php`. First resolve
the file to valid PHP and retain the intended enabled/disabled states, then run
the command to apply Magento's `module.xml` sequence ordering.

- Existing module states, including disabled modules, are preserved.
- Newly discovered modules are added with state `1` (enabled), as with Magento's
  setup module-list regeneration.
- Modules no longer present in the code are removed from the list.
- Unrelated configuration sections and `app/etc/env.php` are left unchanged.
- If the list is already correct, no file is written. Repeated runs are idempotent.

:::warning

Back up or commit your configuration before running this command. It updates the
configuration file, not the database. Enabling a newly discovered module here does
not install its schema or data; perform any required installation or upgrades
separately as part of your normal deployment process.

:::
