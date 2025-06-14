---
title: sys:cron:history
sidebar_label: sys:cron:history
---

# sys:cron:history

:::info
Shows the last executed cronjobs with their status. Useful for debugging and monitoring scheduled tasks in Magento.
:::

```sh
n98-magerun2.phar sys:cron:history [--format[="..."]] [--timezone[="..."]]
```

**Options:**

| Option                | Description                                          |
|-----------------------|------------------------------------------------------|
| `--timezone[=TIMEZONE]`| Timezone to show finished at in                      |
| `--format[=FORMAT]`   | Output Format. One of [csv,json,json_array,yaml,xml] |
