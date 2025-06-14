---
title: config:env:delete
---

# config:env:delete

Remove a configuration from the env.php file by providing a key.

Sub-arrays in config.php can be specified by adding a "." character to every array.

```sh
n98-magerun2.phar config:env:delete <key>
```

Examples:

```sh
n98-magerun2.phar config:env:delete system
n98-magerun2.phar config:env:delete cache.frontend.default.backend
n98-magerun2.phar config:env:delete cache.frontend.default.backend_options
```

