---
title: Activate and Deactivate Admin User
sidebar_label: admin:user:activate & admin:user:deactivate
---

# admin:user:activate & admin:user:deactivate

These commands allow you to activate or deactivate a Magento 2 admin user account from the command line.

## Usage

### Activate an Admin User

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

### Deactivate an Admin User

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

- You must have sufficient permissions to run these commands.
- Useful for quickly enabling or disabling admin access without using the Magento backend.
- For a list of all admin users, use [`admin:user:list`](./admin-user-list.md).

:::tip
Use the `--help` flag to see all available options for each command.
:::

