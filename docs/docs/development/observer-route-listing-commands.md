---
sidebar_position: 18
title: Observer and Route Listing Commands
---
## List Observers

```sh
n98-magerun2.phar dev:module:observer:list [--sort] [--format=FORMAT] [<event> [<area>]]
```
**Arguments:**
| Argument | Description                                                                                               |
|----------|-----------------------------------------------------------------------------------------------------------|
| `event`  | Filter observers for specific event.                                                                      |
| `area`   | Filter observers in specific area. One of [global,adminhtml,frontend,crontab,webapi_rest,webapi_soap,graphql,doc,admin] |
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--sort`           | Sort output ascending by event name                  |
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


## List Routes

```sh
n98-magerun2.phar route:list [-a|--area=AREA] [-m|--module=MODULE] [--format=FORMAT]
```
**Options:**
| Option                   | Description                                          |
|--------------------------|------------------------------------------------------|
| `-a, --area[=AREA]`      | Route area code. One of [frontend,adminhtml]         |
| `-m, --module[=MODULE]`  | Show registered routes of a module                   |
| `--format[=FORMAT]`      | Output Format. One of [csv,json,json_array,yaml,xml] |
