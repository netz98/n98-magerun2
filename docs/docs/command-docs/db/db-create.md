---
title: db:create
---

# db:create

Create currently configured database.

```sh
n98-magerun2.phar db:create [--connection=CONNECTION]
```

**Options:**

| Option                   | Description                                                                 |
|--------------------------|-----------------------------------------------------------------------------|
| `--connection=CONNECTION`| Select DB connection type for Magento configurations with several databases |

**Help:**

The command tries to create the configured database according to your settings in `app/etc/env.php`. The configured user must have "CREATE DATABASE" privileges on MySQL Server.
