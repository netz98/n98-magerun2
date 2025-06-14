---
title: config:store:set
---

# config:store:set

Set a store config value.

```sh
n98-magerun2.phar config:store:set [--scope[="..."]] [--scope-id[="..."]] [--encrypt] [--no-null] <path> <value>
```

**Arguments:**

| Argument | Description                                      |
|----------|--------------------------------------------------|
| `path`   | The store config path like "general/local/code"  |
| `value`  | The config value                                 |

**Options:**

| Option             | Description                                                                            |
|--------------------|----------------------------------------------------------------------------------------|
| `--scope`          | The config value's scope (default: `default`). Can be `default`, `websites`, `stores`) |
| `--scope-id`       | The config value's scope ID (default: `0`)                                             |
| `--encrypt`        | Encrypt the config value using crypt key                                               |
| `--no-null`        | Do not treat value NULL as NULL (NULL/"unknown" value) value                           |

