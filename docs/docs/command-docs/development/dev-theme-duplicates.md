---
title: dev:theme:duplicates
---

```sh
n98-magerun2.phar dev:theme:duplicates <theme> [<originalTheme>] [--log-junit=<path>]
```

**Arguments:**
| Argument | Description |
|----------|-------------|
| `theme` | Your theme |
| `originalTheme` | Original theme to compare. Default is "base/default" |

**Options:**
| Option | Description |
|--------|-------------|
| `--log-junit` | Log duplicates in JUnit XML format to defined file. |

:::info
Find duplicate files (templates, layout, locale, etc.) between two themes.
:::
