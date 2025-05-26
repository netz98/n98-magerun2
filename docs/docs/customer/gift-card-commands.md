---
sidebar_position: 23
title: Gift Card Commands
---
## Generate Gift Card Pool

Generates a new gift card pool.

```sh
n98-magerun2.phar giftcard:pool:generate
```

## Create a Gift Card

```sh
n98-magerun2.phar giftcard:create [--website[="..."]] [--expires[="..."]] [amount]
```

You may specify a website ID or use the default. You may also optionally
add an expiration date to the gift card using the
`--expires` option. Dates should be in `YYYY-MM-DD` format.

## View Gift Card Information

```sh
n98-magerun2.phar giftcard:info [--format[="..."]] [code]
```

## Remove a Gift Card

```sh
n98-magerun2.phar giftcard:remove [code]
```
