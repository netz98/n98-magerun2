---
title: sys:setup:compare-versions
sidebar_label: sys:setup:compare-versions
---

Compares module version with saved setup version in `setup_module` table and displays version mismatches if found.

```sh
n98-magerun2.phar sys:setup:compare-versions [--ignore-data] [--log-junit="..."] [--format[="..."]]
```

**Options:**
| Option                 | Description                                          |
|------------------------|------------------------------------------------------|
| `--ignore-data`        | Ignore data updates                                  |
| `--log-junit=LOG-JUNIT`| Log output to a JUnit xml file.                      |
| `--format[=FORMAT]`    | Output Format. One of [csv,json,json_array,yaml,xml] |

- If a filename with `--log-junit` option is set the tool generates an XML file and no output to *stdout*.

