---
title: customer:create
sidebar_label: customer:create
---

Create a new customer/user for shop frontend.

```sh
n98-magerun2.phar customer:create [email] [password] [firstname] [lastname] [website] [--format[="..."]] [additionalFields...]
```

Example:

```sh
n98-magerun2.phar customer:create foo@example.com password123 John Doe base
```

You can add any number of custom fields, example:

```sh
n98-magerun2.phar customer:create foo@example.com passworD123 John Doe base taxvat DE12345678 prefix Mrs.
```

**Options:**

| Option              | Description                                          |
|---------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |

---

:::note
This command was introduced with version 1.2.0.
:::
