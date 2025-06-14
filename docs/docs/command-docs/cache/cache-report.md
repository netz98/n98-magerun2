---
title: cache:report
---

# cache:report

Cache Report

This command lets you investigate what's stored inside your cache. It prints out a table with cache IDs.

```sh
n98-magerun2.phar cache:report [options]
```

**Options:**

| Option                      | Description                                                    |
|-----------------------------|----------------------------------------------------------------|
| `--fpc`                     | Use full page cache instead of core cache                      |
| `-t, --tags`                | Output tags                                                    |
| `-m, --mtime`               | Output last modification time                                  |
| `--filter-id[=FILTER-ID]`   | Filter output by ID (substring)                                |
| `--filter-tag[=FILTER-TAG]` | Filter output by TAG (separate multiple tags by comma)         |
| `--format[=FORMAT]`         | Output Format. One of [csv, json, json_array, yaml, xml]       |
