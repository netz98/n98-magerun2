---
title: config:store:get
---

# config:store:get

Get a store config value.

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

