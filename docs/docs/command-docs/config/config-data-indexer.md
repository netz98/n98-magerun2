---
title: config:data:indexer
---

# config:data:indexer

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

