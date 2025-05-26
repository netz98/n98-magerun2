---
sidebar_position: 26
title: Integration (WebAPI Access Token) Commands
---
## Integrations (Webapi Access Tokens)

There are four commands to create, show, list, delete integrations (access tokens).
This commands are very useful for developers.

### List all existing integrations

```sh
n98-magerun2.phar integration:list [--format[=FORMAT]]
```
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


### Create a new integration

```sh
n98-magerun2.phar integration:create [options] [--] <name> [<email> [<endpoint>]]
```
**Arguments:**
| Argument   | Description               |
|------------|---------------------------|
| `name`     | Name of the integration   |
| `email`    | Email                     |
| `endpoint` | Endpoint URL              |

**Options:**

| Option                                      | Description                                              |
|---------------------------------------------|----------------------------------------------------------|
| `--consumer-key=CONSUMER-KEY`               | Consumer Key (length 32 chars)                           |
| `--consumer-secret=CONSUMER-SECRET`         | Consumer Secret (length 32 chars)                        |
| `--access-token=ACCESS-TOKEN`               | Access-Token (length 32 chars)                           |
| `--access-token-secret=ACCESS-TOKEN-SECRET` | Access-Token Secret (length 32 chars)                    |
| `--resource=RESOURCE` `-r`                  | Defines a granted ACL resource (multiple values allowed) |
| `--format[=FORMAT]`                         | Output Format. One of [csv,json,json_array,yaml,xml]     |


If no ACL resource is defined the new integration token will be created with FULL ACCESS.

If you do not want that, please provide a list of ACL resources by using the `--resource` option.

Example:

```sh
n98-magerun2.phar integration:create "My new integration 10" foo@example.com https://example.com -r Magento_Catalog::catalog_inventory -r Magento_Backend::system_other_settings
```

To see all available ACL resources, please run the command `config:data:acl`.

### Show infos about existing integration

```sh
n98-magerun2.phar integration:show [--format[=FORMAT]] <name_or_id> [<key>]
```
**Arguments:**
| Argument     | Description                                                                 |
|--------------|-----------------------------------------------------------------------------|
| `name_or_id` | Name or ID of the integration                                               |
| `key`        | Only output value of named param like "Access Token". Key is case insensitive.|
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


Example (print only Access Key):

```sh
n98-magerun2.phar integration:show 1 "Access Key"
```

### Delete integration

```sh
n98-magerun2.phar integration:delete <name_or_id>
```
**Arguments:**
| Argument     | Description                   |
|--------------|-------------------------------|
| `name_or_id` | Name or ID of the integration |
