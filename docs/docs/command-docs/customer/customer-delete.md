---
title: customer:delete
sidebar_label: customer:delete
---

Delete customers by various filters or in bulk.

```sh
n98-magerun2.phar customer:delete [options]
```

**Options:**

| Option                      | Description                                 |
|-----------------------------|---------------------------------------------|
| `--id[=ID]`                 | Customer Id                                 |
| `--email[=EMAIL]`           | Email                                       |
| `--firstname[=FIRSTNAME]`   | Firstname                                   |
| `--lastname[=LASTNAME]`     | Lastname                                    |
| `--website[=WEBSITE]`       | Website                                     |
| `-f, --force`               | Force delete                                |
| `-a, --all`                 | Delete all customers. Ignore all filters.   |
| `-r, --range`               | Delete a range of customers by Id           |
| `--fuzzy`                   | Fuzziness                                   |

**Examples:**

```sh
n98-magerun2.phar customer:delete --id 1                     # Will delete customer with Id 1
n98-magerun2.phar customer:delete --fuzzy --email=test       # Will delete all customers with email like "%test%"
n98-magerun2.phar customer:delete --all                      # Will delete all customers
n98-magerun2.phar customer:delete --range                    # Will prompt for start and end Ids for batch deletion
```
