---
title: Activate Admin User
sidebar_label: admin:user:activate
---

# admin:user:activate

This command allows you to activate a Magento 2 admin user account from the command line.

## Usage

```
n98-magerun2 admin:user:activate <username|email>
```

- **Description:** Activates (enables) the specified admin user account.
- **Aliases:** `admin:user:enable`
- **Arguments:**
  - `<username|email>`: The username or email address of the admin user to activate.

#### Example

```
n98-magerun2 admin:user:activate admin
```

---

## Notes

- You must have sufficient permissions to run this command.
- Useful for quickly enabling admin access without using the Magento backend.
- For a list of all admin users, use [`admin:user:list`](./admin-user-list.md).

:::tip
Use the `--help` flag to see all available options for this command.
:::

