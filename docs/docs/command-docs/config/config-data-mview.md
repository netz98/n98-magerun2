---
title: config:data:mview
---

# config:data:mview

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


---

:::note
This command was introduced with version 6.0.0.
:::
