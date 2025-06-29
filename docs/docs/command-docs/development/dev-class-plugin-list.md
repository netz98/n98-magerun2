---
title: dev:class:plugin:list
---

## List Class Plugins

:::info
This command lists all plugins registered for a given class. It can help you debug the interception configuration of your Magento installation.
:::

```sh
n98-magerun2.phar dev:class:plugin:list <class> [<area>] [--format=FORMAT]
```

**Arguments:**

| Argument | Description |
|----------|-------------|
| class    | Fully qualified class name |
| area     | Area code (e.g. global, adminhtml, frontend) |

**Options:**

| Option          | Description                                         |
|-----------------|-----------------------------------------------------|
| --format=FORMAT | Output Format. One of [csv,json,json_array,yaml,xml] |

