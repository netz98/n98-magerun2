---
title: Cache Commands
---
### List Magento cache status

```sh
n98-magerun2.phar cache:list [--enabled[=ENABLED]] [--format[=FORMAT]]
```
**Options:**

| Option                | Description                                                                 |
|-----------------------|-----------------------------------------------------------------------------|
| `--enabled[=ENABLED]` | Filter the list to display only enabled [1] or disabled [0] cache types     |
| `--format[=FORMAT]`   | Output Format. One of [csv,json,json_array,yaml,xml]                        |


### Clean Magento cache

Cleans expired cache entries.

If you would like to clean only one cache type:

```sh
n98-magerun2.phar cache:clean [type...]
```

If you would like to clean multiple cache types at once:

```sh
n98-magerun2.phar cache:clean [type] [type] ...
```

If you would like to remove all cache entries use `cache:flush`

Run `cache:list` command to see all codes.

### Remove all cache entries

```sh
n98-magerun2.phar cache:flush [type...]
```

Keep in mind that `cache:flush` cleares the cache backend,
so other cache types in the same backend will be cleared as well.

### Remove entry by ID

The command is not checking if the cache id exists. If you want to check if the cache id exists
use the `cache:remove:id` command with the `--strict` option.

```sh
n98-magerun2.phar cache:remove:id [--strict] <id>
```
**Options:**
| Option     | Description                                        |
|------------|----------------------------------------------------|
| `--strict` | Use strict mode (remove only if cache id exists) |


### Disable Magento cache

```sh
n98-magerun2.phar cache:disable [--format[=FORMAT]] [type...]
```
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |

If no code is specified, all cache types will be disabled. Run
`cache:list` command to see all codes.

### Enable Magento cache

```sh
n98-magerun2.phar cache:enable [--format[=FORMAT]] [type...]
```
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |

### Cache Report

This command let you investigate what's stored inside your cache. It prints out a table with cache IDs.

```sh
n98-magerun2.phar cache:report [options]
```
**Options:**
| Option                   | Description                                                     |
|--------------------------|-----------------------------------------------------------------|
| `--fpc`                  | Use full page cache instead of core cache                       |
| `-t, --tags`             | Output tags                                                     |
| `-m, --mtime`            | Output last modification time                                   |
| `--filter-id[=FILTER-ID]`| Filter output by ID (substring)                                 |
| `--filter-tag[=FILTER-TAG]`| Filter output by TAG (separate multiple tags by comma)          |
| `--format[=FORMAT]`      | Output Format. One of [csv, json, json_array, yaml, xml]        |


### Cache View

Prints stored cache entry by ID.

```sh
n98-magerun2.phar cache:view [options] <id>
```
**Options:**
| Option         | Description                              |
|----------------|------------------------------------------|
| `--fpc`        | Use full page cache instead of core cache|
| `--unserialize`| Unserialize output                       |
| `--decrypt`    | Decrypt output with encryption key       |


### Flush Catalog Images Cache

Removes pre-generated catalog images and triggers `clean_catalog_images_cache_after` event which
should invalidate the full page cache.

```sh
n98-magerun2.phar cache:catalog:image:flush [--suppress-event]
```
**Options:**
| Option             | Description                                               |
|--------------------|-----------------------------------------------------------|
| `--suppress-event` | Suppress clean_catalog_images_cache_after event dispatching |

---
