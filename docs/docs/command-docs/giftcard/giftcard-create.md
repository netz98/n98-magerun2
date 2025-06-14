---
title: giftcard:create
---

# giftcard:create

Creates a new gift card.

:::info
This command allows you to quickly generate a new gift card for your Magento store.
:::

```sh
n98-magerun2.phar giftcard:create [--website[="..."]] [--expires[="..."]] [amount]
```

You may specify a website ID or use the default. You may also optionally add an expiration date to the gift card using the `--expires` option. Dates should be in `YYYY-MM-DD` format.

:::tip
Use the `--expires` option to set an expiration date for the gift card, ensuring it cannot be used after a certain date.
:::
