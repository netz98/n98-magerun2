---
title: cache:flush
---

# cache:flush

Remove all cache entries

```sh
n98-magerun2.phar cache:flush [type...]
```

Keep in mind that `cache:flush` clears the cache backend, so other cache types in the same backend will be cleared as well.
