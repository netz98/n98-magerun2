---
title: cache:clean
---

# cache:clean

Clean Magento cache

Cleans expired cache entries.

If you would like to clean only one cache type:

```sh
n98-magerun2.phar cache:clean [type...]
```

If you would like to clean multiple cache types at once:

```sh
n98-magerun2.phar cache:clean [type] [type] ...
```

If you would like to remove all cache entries use `cache:flush`.

Run `cache:list` command to see all codes.
