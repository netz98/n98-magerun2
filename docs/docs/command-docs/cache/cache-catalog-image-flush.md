---
title: cache:catalog:image:flush
---

# cache:catalog:image:flush

:::info
Removes pre-generated catalog images and triggers the `clean_catalog_images_cache_after` event, which should invalidate the full page cache.
:::

```sh
n98-magerun2.phar cache:catalog:image:flush [--suppress-event]
```

:::note
**Options:**

| Option             | Description                                                |
|--------------------|------------------------------------------------------------|
| `--suppress-event` | Suppress clean_catalog_images_cache_after event dispatching |
:::
