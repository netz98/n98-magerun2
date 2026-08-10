---
title: sys:email:test
sidebar_label: sys:email:test
---

# sys:email:test

:::info
Sends a minimal test email through Magento's own mail transport, without creating any customer accounts or other test data. This is useful for checking email deliverability with tools like mail-tester.com, and for verifying that SMTP settings are correctly configured for a given store view. The email re-uses the "Contact Form" template shipped with Magento, since it does not require any additional data to be set up.
:::

```sh
n98-magerun2.phar sys:email:test [--to=<email>] [--from=<email>] [--cc=<email>]... [--store=<code-or-id>]
```

If `--to` is omitted, you will be prompted for it interactively.

## Options

- `--to` (required, prompted for interactively if omitted) - Recipient email address
- `--from` (optional) - Sender email address, defaults to the store's configured general contact email
- `--cc` (optional, repeatable) - Additional cc email address, can be used multiple times
- `--store` (optional) - Store code or id, defaults to the current store

## Examples

```sh
n98-magerun2.phar sys:email:test
n98-magerun2.phar sys:email:test --to=you@example.com
n98-magerun2.phar sys:email:test --to=you@example.com --store=2
n98-magerun2.phar sys:email:test --to=you@example.com --from=sender@example.com --cc=cc1@example.com --cc=cc2@example.com
```
