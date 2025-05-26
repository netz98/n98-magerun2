---
sidebar_position: 8
title: Store Configuration Commands
---
## Config Search

Search in the store config meta data (labels).
The output is a table with id, type and name of the config item.

Type can be one of:
- section
- group
- field

```sh
n98-magerun2.phar config:search [--format[="..."]] <search>
```


## Set Store Config

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


## Get Store Config

```sh
n98-magerun2.phar config:store:get [--scope="..."] [--scope-id="..."] [--decrypt] [--update-script] [--magerun-script] [--format[="..."]] [path]
```

**Arguments:**

| Argument | Description        |
|----------|--------------------|
| `path`   | The config path. Wildcards (`*`) are supported. If not set, all items are listed. |

**Options:**

| Option             | Description                                                                  |
|--------------------|------------------------------------------------------------------------------|
| `--scope=SCOPE`    | The config value's scope (`default`, `websites`, `stores`). Default: `default`. |
| `--scope-id=SCOPE-ID`| The config value's scope ID or scope code.                                   |
| `--decrypt`        | Decrypt the config value using crypt key defined in `env.php`.               |
| `--update-script`  | Output as update script lines.                                               |
| `--magerun-script` | Output for usage with `config:store:set`.                                    |
| `--format[=FORMAT]`| Output Format. One of [csv,json,json_array,yaml,xml].                        |


**Help:**

If path is not set, all available config items will be listed. path may contain wildcards (`*`)

**Example:**

```sh
n98-magerun2.phar config:store:get web/* --magerun-script
```

## Delete Store Config

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


## Display ACL Tree

```sh
n98-magerun2.phar config:data:acl
```

**Help:**

Prints acl.xml data as table

## Print Dependency Injection Config Data

```sh
n98-magerun2.phar config:data:di [--scope=SCOPE] [<type>]
```

**Arguments:**

| Argument | Description  |
|----------|--------------|
| `type`   | Type (class) |


**Options:**

| Option         | Description                                                                                             |
|----------------|---------------------------------------------------------------------------------------------------------|
| `--scope` `-s` | Config scope (`global`, `adminhtml`, `frontend`, `webapi_rest`, `webapi_soap`, ...) (default: `global`) |

## Print MView Config

Print the data of all merged mview.xml files.

```sh
n98-magerun2.phar config:data:mview [options]
```

**Options:**

| Option              | Description                                                                                             |
|---------------------|---------------------------------------------------------------------------------------------------------|
| `--scope` `-s`      | Config scope (`global`, `adminhtml`, `frontend`, `webapi_rest`, `webapi_soap`, ...) (default: `global`) |
| `--tree` `-t`       | Print data as tree                                                                                      |
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml]                                                    |


## Print Indexer Config

Print the data of all merged indexer.xml files.

```sh
n98-magerun2.phar config:data:indexer [options]
```

**Options:**

| Option              | Description                                                                                             |
|---------------------|---------------------------------------------------------------------------------------------------------|
| `--scope` `-s`      | Config scope (`global`, `adminhtml`, `frontend`, `webapi_rest`, `webapi_soap`, ...) (default: `global`) |
| `--tree` `-t`       | Print data as tree                                                                                      |
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml]                                                    |
