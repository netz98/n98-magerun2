---
title: config:env:show
---

# config:env:show

Show env.php settings.

```sh
n98-magerun2.phar config:env:show [options] [<key>]
```

If no key is passed, the whole content of the file is displayed as table.

Examples:

```sh
n98-magerun2.phar config:env:show  # whole content
n98-magerun2.phar config:env:show backend.frontName
n98-magerun2.phar config:env:show --format=json
n98-magerun2.phar config:env:show --format=csv
n98-magerun2.phar config:env:show --format=xml
```
---
title: config:env:create
---

# config:env:create

Create env file interactively. Can also update existing files. To update a single value use `config:env:set`.

```sh
n98-magerun2.phar config:env:create
```

