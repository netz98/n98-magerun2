---
title: cache:clean
---

# cache:clean

:::info
Cleans expired cache entries in Magento. Use this command to keep your cache storage optimized and up-to-date.
:::

If you would like to clean only one cache type:

```sh
n98-magerun2.phar cache:clean [type...]
```

If you would like to clean multiple cache types at once:

```sh
n98-magerun2.phar cache:clean [type] [type] ...
```

:::tip
To remove all cache entries, use the `cache:flush` command instead. Run `cache:list` to see all available cache codes.
:::
