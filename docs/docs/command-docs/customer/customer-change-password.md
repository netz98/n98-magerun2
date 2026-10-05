---
title: customer:change-password
sidebar_label: customer:change-password
---

Change a customer's password.

```sh
n98-magerun2.phar customer:change-password [email] [password] [website]
```

- Website parameter must only be given if more than one websites are available.
- A non-empty password is required. If omitted, the command prompts for it only in an interactive terminal.
- With `--no-interaction`, redirected stdin, or MCP, pass the password explicitly. Missing or empty passwords
  cause the command to fail without changing the stored password.

```sh
n98-magerun2.phar customer:change-password customer@example.com 'New-Passw0rd!' main --no-interaction
```

---

:::note
This command was introduced with version 2.1.1.
:::
