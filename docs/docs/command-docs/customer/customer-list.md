---
title: customer:list
sidebar_label: customer:list
---

List customers. The output is limited to 1000 (can be changed by overriding config). If search parameter is given the customers are filtered (searches in firstname, lastname and email).

```sh
n98-magerun2.phar customer:list [--format[="..."]] [search]
```
