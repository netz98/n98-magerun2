---
title: Command Aliases
---

The command alias feature was added with n98-magerun version 1.46.0

## Add a new alias

Register new alias in your configuration file (see [Configuration](../extending/configuration.md)).

Example:

```yaml
commands:
  aliases:
    - "db:dump:time": "db:dump --add-time"
    - "ccc": "cache:clean config"
    - "cf": "cache:flush"
    - "fe-cache-off": "cache:disable view_files_preprocessing view_files_fallback full_page layout block_html"
    - "fe-cache-on": "cache:enable view_files_preprocessing view_files_fallback full_page layout block_html"
```
