---
title: admin:token:create
---

# admin:token:create

:::info
Creates an Admin Token for Web API authentication. This token can be used for programmatic access to the Magento backend via REST or GraphQL.
:::

```sh
n98-magerun2.phar admin:token:create <username> [--no-newline]
```

**Options:**

| Option           | Description             |
|------------------|-------------------------|
| `--no-newline`   | Do not print newline    |

:::warning
Keep your admin tokens secure. Do not share them or expose them in version control or public scripts.
:::
