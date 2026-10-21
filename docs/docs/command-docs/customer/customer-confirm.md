---
title: customer:confirm
---

# customer:confirm

Confirm a pending customer account, or resend its confirmation email without confirming it.

```bash
n98-magerun2.phar customer:confirm [email] [website] [--resend]
```

- `email`: Customer email address; prompted for interactively if omitted.
- `website`: Website code or ID. With one website it can be omitted; with multiple websites select one interactively or supply it explicitly. Non-interactive use requires an email and an unambiguous website.
- `--resend`: Send another confirmation email. The account remains pending and its confirmation token is not cleared.

```bash
n98-magerun2.phar customer:confirm alice@example.com base
n98-magerun2.phar customer:confirm alice@example.com base --resend
```

The customer must belong to the selected website. Missing customers, invalid websites, already-confirmed accounts, and email failures produce a nonzero exit status. Already-confirmed accounts are never unconfirmed, including when `--resend` is supplied.

The default operation uses Magento account activation, including its normal account-confirmed notification. Email rendering uses the selected website's default store. A notification failure during activation can occur after Magento has persisted confirmation; inspect the account before retrying.
