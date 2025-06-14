---
title: dev:module:observer:list
---

## List Observers

```sh
n98-magerun2.phar dev:module:observer:list [--sort] [--format=FORMAT] [<event> [<area>]]
```

**Arguments:**

| Argument | Description |
|----------|-------------|
| event    | Filter observers for specific event. |
| area     | Filter observers in specific area. One of [global,adminhtml,frontend,crontab,webapi_rest,webapi_soap,graphql,doc,admin] |

**Options:**

| Option            | Description                                         |
|-------------------|-----------------------------------------------------|
| --sort            | Sort output ascending by event name                 |
| --format=FORMAT   | Output Format. One of [csv,json,json_array,yaml,xml]|
