---
title: dev:di:preferences:list
---

## List DI Preferences

:::info
This command helps you inspect Magento's dependency injection (DI) preferences for a given area. Useful for debugging and understanding class rewrites and dependency mappings.
:::

List Preferences:

```sh
n98-magerun2.phar dev:di:preferences:list [--format [FORMAT]] [<area>]
```

`area` is one of [global, adminhtml, frontend, crontab, webapi_rest, webapi_soap, graphql, doc, admin]

Format can be `csv`, `json`, `xml` or `yaml`.
