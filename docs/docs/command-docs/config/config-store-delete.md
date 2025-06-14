---
title: config:store:delete
---

# config:store:delete

Delete a store config value.

```sh
n98-magerun2.phar config:store:delete [--scope[="..."]] [--scope-id[="..."]] [--all] <path>
```

**Arguments:**

| Argument | Description        |
|----------|--------------------|
| `path`   | The config path    |

**Options:**

| Option             | Description                                                                            |
|--------------------|----------------------------------------------------------------------------------------|
| `--scope`          | The config value's scope (default: `default`). Can be `default`, `websites`, `stores`) |
| `--scope-id`       | The config value's scope ID                                                            |
| `--all`            | Delete all entries by path                                                             |

