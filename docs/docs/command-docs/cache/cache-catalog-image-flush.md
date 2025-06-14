---
title: cache:catalog:image:flush
---

# cache:catalog:image:flush

Removes pre-generated catalog images and triggers `clean_catalog_images_cache_after` event which should invalidate the full page cache.

```sh
n98-magerun2.phar cache:catalog:image:flush [--suppress-event]
```

**Options:**

| Option             | Description                                                |
|--------------------|------------------------------------------------------------|
| `--suppress-event` | Suppress clean_catalog_images_cache_after event dispatching |
