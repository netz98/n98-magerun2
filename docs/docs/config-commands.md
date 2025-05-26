---
title: Config Commands
---
### Create app/etc/env.php

Create env file interactively.
If can also update existing files.
To update a single value you can use the command `config:env:set`.

```sh
n98-magerun2.phar config:env:create
```

### Set single value in env.php file

Set a single value in env.php by providing a key and an optional value.
The command will save an empty string as default value if no value is set.

Sub-arrays in config.php can be specified by adding a "." character to every array.

```sh
n98-magerun2.phar config:env:set <key> [<value>] [--input-format=INPUT-FORMAT]
```
**Options:**
| Option                          | Description                                   |
|---------------------------------|-----------------------------------------------|
| `--input-format=INPUT-FORMAT`   | Input Format. One of [plain,json] [default: "plain"] |


You can also choose to provide a json text argument as value, by using the optional `--input-format=json` flag.
This will allow you to add values that aren't a string but also other scalar types.

Examples:

```sh
n98-magerun2.phar config:env:set backend.frontName mybackend
n98-magerun2.phar config:env:set crypt.key bb5b0075303a9bb8e3d210a971674367
n98-magerun2.phar config:env:set session.redis.host 192.168.1.1
n98-magerun2.phar config:env:set 'x-frame-options' '*'

n98-magerun2.phar config:env:set --input-format=json queue.consumers_wait_for_messages 0
n98-magerun2.phar config:env:set --input-format=json directories.document_root_is_pub true
n98-magerun2.phar config:env:set --input-format=json cron_consumers_runner.consumers '["some.consumer", "some.other.consumer"]'
```

### Delete key from env.php file

Remove a configuration from the env.php file by providing a key.

Sub-arrays in config.php can be specified by adding a "." character to every array.

```sh
n98-magerun2.phar config:env:delete <key>
```

Examples:

```sh
n98-magerun2.phar config:env:delete system
n98-magerun2.phar config:env:delete cache.frontend.default.backend
n98-magerun2.phar config:env:delete cache.frontend.default.backend_options
```

### Show env.php settings

```sh
n98-magerun2.phar config:env:show [options] [<key>]
```

If no key is passed, the whole content of the file is displayed as table.

Examples:

```sh
n98-magerun2.phar config:env:show  # whole content
n98-magerun2.phar config:env:show backend.frontName
n98-magerun2.phar config:env:show --format=json
n98-magerun2.phar config:env:show --format=csv
n98-magerun2.phar config:env:show --format=xml
```

---

### Config Search

Search in the store config meta data (labels).
The output is a table with id, type and name of the config item.

Type can be one of:
- section
- group
- field

```sh
n98-magerun2.phar config:search [--format[="..."]] <search>
```


### Set Store Config

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


### Get Store Config

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

### Delete Store Config

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


### Display ACL Tree

```sh
n98-magerun2.phar config:data:acl
```

**Help:**

Prints acl.xml data as table

### Print Dependency Injection Config Data

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

### Print MView Config

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


### Print Indexer Config

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

---
