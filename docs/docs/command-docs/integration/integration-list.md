---
title: integration:list
sidebar_label: integration:list
---

List all existing integrations (WebAPI access tokens).

:::note
This command is useful for auditing which integrations currently have access to your Magento store.
:::

```sh
n98-magerun2.phar integration:list [--format[=FORMAT]]
```

**Options:**
| Option              | Description                                         |
|---------------------|-----------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |
