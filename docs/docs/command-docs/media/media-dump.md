---
title: media:dump
sidebar_label: media:dump
---

:::tip
Use this command to quickly create a portable backup of your Magento media folder, which is useful for migrations or local development.
:::

Create a ZIP archive with media folder content.

```sh
n98-magerun2.phar media:dump [--strip] [<filename>]
```

**Arguments:**
| Argument   | Description   |
|------------|---------------|
| `filename` | Dump filename |

**Options:**
| Option   | Description          |
|----------|----------------------|
| `--strip`| Excludes image cache |
