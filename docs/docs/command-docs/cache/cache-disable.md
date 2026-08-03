---
title: cache:disable
---

# cache:disable

Disable Magento cache

```sh
n98-magerun2.phar cache:disable [--format[=FORMAT]] [type...]
```

**Options:**

| Option              | Description                                         |
|---------------------|-----------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,tsv,json,json_array,jsonl,yaml,markdown,xml]. Aliases: yml,md,ndjson |

If no code is specified, all cache types will be disabled. Run `cache:list` command to see all codes.
