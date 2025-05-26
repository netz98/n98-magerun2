---
title: Customer Commands
---
### Customer Info

Loads basic customer info by email address.

```sh
n98-magerun2.phar customer:info [email] [website]
```

### Create customer

Creates a new customer/user for shop frontend.

```sh
n98-magerun2.phar customer:create [email] [password] [firstname] [lastname] [website] [--format[="..."]] [additionalFields...]
```

Example:

```sh
n98-magerun2.phar customer:create foo@example.com password123 John Doe base
```

You can add additional any number of custom fields, example:

```sh
n98-magerun2.phar customer:create foo@example.com passworD123 John Doe base taxvat DE12345678 prefix Mrs.
```
**Options:**

| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


### List Customers

List customers. The output is limited to 1000 (can be changed by
overriding config). If search parameter is given the customers are
filtered (searchs in firstname, lastname and email).

```sh
n98-magerun2.phar customer:list [--format[="..."]] [search]
```

### Change customer password

```sh
n98-magerun2.phar customer:change-password [email] [password] [website]
```

- Website parameter must only be given if more than one websites are available.

### Create Customer Token for Webapi

```sh
n98-magerun2.phar customer:token:create <email> [--no-newline] [<website>]
```
**Options:**

| Option             | Description                        |
|--------------------|------------------------------------|
| `--no-newline`     | Do not output the trailing newline |

### Delete customer

```sh
n98-magerun2.phar customer:delete [options]
```
**Options:**

| Option                | Description                             |
|-----------------------|-----------------------------------------|
| `--id[=ID]`           | Customer Id or email                    |
| `--email[=EMAIL]`     | Email                                   |
| `--firstname[=FIRSTNAME]` | Firstname                             |
| `--lastname[=LASTNAME]` | Lastname                              |
| `--website[=WEBSITE]` | Website                                 |
| `-f, --force`         | Force delete                            |
| `-a, --all`           | Delete all customers. Ignore all filters. |
| `-r, --range`         | Delete a range of customers by Id       |
| `--fuzzy`             | Fuzziness                               |


Examples:

```sh
n98-magerun2.phar customer:delete --id 1                     # Will delete customer with Id 1
n98-magerun2.phar customer:delete --fuzzy --email=test       # Will delete all customers with email like "%test%"
n98-magerun2.phar customer:delete --all                      # Will delete all customers
n98-magerun2.phar customer:delete --range                    # Will prompt for start and end Ids for batch deletion
```

Deletes customer(s) by given id or a combination of the website id and email or website id and firstname and lastname.
In addition, you can delete a range of customer ids or delete all customers.


### Add customer address

```sh
n98-magerun2.phar customer:add-address <email> <website> [options]
```
**Arguments:**

| Argument   | Description     |
|------------|-----------------|
| `email`    | Customer email  |
| `website`  | Customer website|

**Options:**

| Option                | Description                     |
|-----------------------|---------------------------------|
| `--firstname=FIRSTNAME` | First name                      |
| `--lastname=LASTNAME`   | Last name                       |
| `--street=STREET`       | Street address                  |
| `--city=CITY`           | City                            |
| `--country=COUNTRY`     | Country ID, e.g., US            |
| `--postcode=POSTCODE`   | Postcode                        |
| `--telephone=TELEPHONE` | Telephone number                |
| `--default-billing`   | Use as default billing address  |
| `--default-shipping`  | Use as default shipping address |


Examples:

```sh
n98-magerun2.phar customer:add-address foo@example.com base --firstname="John" --lastname="Doe" --street="Pariser Platz" --city="Berlin" --country="DE" --postcode="10117" --telephone="1234567890"  # add address of brandenburger tor to customer with email "foo@example.com" in website "base"
n98-magerun2.phar customer:add-address foo@example.com base --firstname="John" --lastname="Doe" --street="Pariser Platz" --city="Berlin" --country="DE" --postcode="10117" --telephone="1234567890" --default-billing --default-shipping # add address of brandenburger tor to customer with email "foo@example.com" in website "base" as default billing and shipping
```

Adds a customer address to given customer defined by email and website
---
