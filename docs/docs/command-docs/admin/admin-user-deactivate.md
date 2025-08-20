---
title: Deactivate Admin User
sidebar_label: admin:user:deactivate
---

# admin:user:deactivate

This command allows you to deactivate a Magento 2 admin user account from the command line.

## Usage

```
n98-magerun2 admin:user:deactivate <username|email>
```

- **Description:** Deactivates (disables) the specified admin user account.
- **Aliases:** `admin:user:disable`
- **Arguments:**
  - `<username|email>`: The username or email address of the admin user to deactivate.

#### Example

```
n98-magerun2 admin:user:deactivate admin
```

---

## Notes

- You must have sufficient permissions to run this command.
- Useful for quickly disabling admin access without using the Magento backend.
- For a list of all admin users, use [`admin:user:list`](./admin-user-list.md).

:::tip
Use the `--help` flag to see all available options for this command.
:::

