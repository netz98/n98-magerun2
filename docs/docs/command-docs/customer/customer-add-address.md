---
title: customer:add-address
sidebar_label: customer:add-address
---

Add a new address to an existing customer.

```sh
n98-magerun2.phar customer:add-address <email> <website> [--firstname="..."] [--lastname="..."] [--street="..."] [--city="..."] [--country="..."] [--postcode="..."] [--telephone="..."] [--default-billing] [--default-shipping]
```

**Options:**

| Option              | Description                                        |
|---------------------|----------------------------------------------------|
| `--firstname`       | Customer first name                                |
| `--lastname`        | Customer last name                                 |
| `--street`          | Street address                                     |
| `--city`            | City                                               |
| `--country`         | Country ID (e.g., `US`)                             |
| `--postcode`        | Postcode                                           |
| `--telephone`       | Telephone number                                   |
| `--default-billing` | Set address as default billing address             |
| `--default-shipping`| Set address as default shipping address            |

Example:

```sh
n98-magerun2.phar customer:add-address foo@example.com base --firstname="John" --lastname="Doe" --street="Main Street 1" --city="Berlin" --country="DE" --postcode="10117" --telephone="1234567890" --default-billing --default-shipping
```

:::note
This command was introduced with version 7.4.0.
:::
