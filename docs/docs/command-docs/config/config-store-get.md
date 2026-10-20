---
title: config:store:get
---

# config:store:get

Get effective store configuration values, including module XML defaults, database
configuration, and `system` configuration in `app/etc/config.php` and `app/etc/env.php`.
Magento's native configuration pipeline determines precedence and scope inheritance.
Backend values remain raw (encrypted values are not automatically decrypted);
use `--decrypt` explicitly when needed.

```sh
n98-magerun2.phar config:store:get [--scope="..."] [--scope-id="..."] [--decrypt] [--update-script] [--magerun-script] [--format[="..."]] [path]
```

**Arguments:**

| Argument | Description        |
|----------|--------------------|
| `path`   | The config path. Wildcards (`*`, `%`, and single-character `_`) are supported. A trailing `/` lists child paths. If not set, all items are listed. |

**Options:**

| Option             | Description                                                                  |
|--------------------|------------------------------------------------------------------------------|
| `--scope=SCOPE`    | Restrict output to `default`, `websites`, or `stores`, including inherited values. Without this option, use the compact listing described below. |
| `--scope-id=SCOPE-ID`| The config value's scope ID or scope code.                                   |
| `--decrypt`        | Decrypt the config value using crypt key defined in `env.php`.               |
| `--update-script`  | Output as update script lines.                                               |
| `--magerun-script` | Output for usage with `config:store:set`.                                    |
| `--format[=FORMAT]`| Output Format. One of [csv,tsv,json,json_array,jsonl,yaml,markdown,xml]. Aliases: yml,md,ndjson                        |

**Help:**

If path is not set, all available config items will be listed. Quote wildcard
paths to prevent shell expansion.

Without `--scope`, the listing is compact: it includes all merged default paths
and only explicitly configured paths for websites and stores. Each row contains
the effective value at that scope, not necessarily the database value. Inherited
paths are not duplicated into every website and store.

With `--scope`, matching effective paths are listed for that scope, including
fallback from default to website to store. Use `--scope-id` to restrict this to
one scope ID or code; without a scope ID, all contexts of the selected scope are
listed. With only a numeric `--scope-id`, matching IDs across scopes are selected.

The `Updated At` column is retained for compatibility but is blank for every
row: a merged effective value has no available modification timestamp.

:::warning

`--update-script` and `--magerun-script` export the effective listing, including
XML/file values and, with `--scope`, inherited values. They are not a backup of
database overrides. Importing such a script creates explicit database values;
it does not modify deployment files or override values locked in those files.

:::

**Example:**

```sh
n98-magerun2.phar config:store:get 'web/*' --magerun-script
n98-magerun2.phar config:store:get admin/security/session_lifetime --scope=default
n98-magerun2.phar config:store:get 'web/%' --scope=stores --scope-id=default
```

Combine `--decrypt` with `--magerun-script` to export decrypted values as
`config:store:set --encrypt` commands, so they are encrypted again on import.
SQL `NULL` values remain unencrypted NULL values. The exported script contains
plaintext secrets; store and handle it securely.
