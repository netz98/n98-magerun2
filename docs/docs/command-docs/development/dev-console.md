---
title: dev:console
---

Opens PHP interactive shell with initialized Magento Admin-Store.

```sh
n98-magerun2.phar dev:console [options] [--] [<cmd>]
```

**Arguments:**

| Argument | Description                |
|----------|----------------------------|
| `cmd`    | Direct code to run [default: ""] |

**Options:**

| Option           | Description                |
|------------------|----------------------------|
| `-a, --area=AREA`| Area to initialize         |
| `-e, --auto-exit`| Automatic exit after cmd   |

Optional an area code can be defined. If provided, the configuration (di.xml, translations) of the area are loaded.

Possible area codes are:

- `adminhtml`
- `crontab`

