---
title: cache:enable
---

# cache:enable

Enable Magento cache

```sh
n98-magerun2.phar cache:enable [--format[=FORMAT]] [type...]
```

**Options:**

| Option              | Description                                         |
|---------------------|-----------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,tsv,json,json_array,jsonl,yaml,markdown,xml]. Aliases: yml,md,ndjson |

If no code is specified, all cache types will be enabled. Run `cache:list` command to see all codes.
