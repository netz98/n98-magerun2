---
sidebar_position: 16
title: Dependency Injection Commands
---
## DI (Dependency Injection)

List Preferences:

```sh
n98-magerun2.phar dev:di:preferences:list [--format [FORMAT]] [<area>]
```

`area` is one of [global, adminhtml, frontend, crontab, webapi_rest, webapi_soap, graphql, doc, admin] 

Format can be `csv`, `json`, `xml` oder `yaml` sein.
