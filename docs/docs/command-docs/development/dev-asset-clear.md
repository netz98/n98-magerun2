---
title: dev:asset:clear
---

Clear static view files

```sh
n98-magerun2.phar dev:asset:clear [--theme="..."]
```

Options:

| Option                  | Description                                                        |
|-------------------------|--------------------------------------------------------------------|
| `-t, --theme=THEME`     | Clear assets for specific theme(s) only (multiple values allowed). |

To clear assets for all themes:

```sh
n98-magerun2.phar dev:asset:clear
```

To clear assets for specific theme(s) only:

```sh
n98-magerun2.phar dev:asset:clear --theme=Magento/luma
```

