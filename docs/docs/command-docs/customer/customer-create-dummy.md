---
title: customer:create:dummy
sidebar_label: customer:create:dummy
---

Generate dummy customers. You can specify a count and a locale. If required arguments are omitted, the command will prompt for them interactively.

:::info
By default, this command suppresses Magento welcome email notifications when generating dummy customers to prevent unwanted emails during local development or testing.
:::

```sh
n98-magerun2.phar customer:create:dummy [count] [locale] [website] [--with-addresses] [--print-password] [--email-domain="..."] [--format[="..."]]
```

Example:

```sh
# Generate 10 dummy customers in the en_US locale using default example.com domain
n98-magerun2.phar customer:create:dummy 10 en_US
```

Generate dummy customers with address information:

```sh
# Generate 5 dummy customers in the de_DE locale, including billing and shipping addresses
n98-magerun2.phar customer:create:dummy 5 de_DE --with-addresses
```

Generate dummy customers using a custom email domain:

```sh
# Generate 5 dummy customers with emails ending in @test.local
n98-magerun2.phar customer:create:dummy 5 en_US --email-domain="test.local"
```

Generate dummy customers and print their secure, randomly-generated passwords:

```sh
# Generate 3 dummy customers in the en_US locale and print their generated passwords
n98-magerun2.phar customer:create:dummy 3 en_US --print-password
```

**Arguments:**

| Argument  | Description                                                                                          |
|-----------|------------------------------------------------------------------------------------------------------|
| `count`   | **(Optional)** Amount of dummy customers to generate. Prompted interactively if omitted.              |
| `locale`  | **(Optional)** Locale for generation (e.g. `de_DE`, `en_US`, `fr_FR`). Prompted interactively if omitted. |
| `website` | **(Optional)** Website code or ID. Prompted interactively if omitted.                                |

**Options:**

| Option             | Description                                                                                     |
|--------------------|-------------------------------------------------------------------------------------------------|
| `--with-addresses` | Create dummy billing/shipping addresses for each customer.                                      |
| `--print-password` | Print the generated password in the command line (generates a secure, random password per user). |
| `--email-domain`   | Email domain to generate dummy customer emails with. Default is `example.com` (officially reserved by IETF for documentation and testing). |
| `--format[=FORMAT]`| Output Format. One of [csv,tsv,json,json_array,jsonl,yaml,markdown,xml]. Aliases: yml,md,ndjson |

---

:::note
This command was introduced with version 10.0.0.
:::
