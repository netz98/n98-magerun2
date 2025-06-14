---
title: integration:delete
---

# integration:delete

Delete an integration.

:::danger
Deleting an integration will immediately revoke its access to the Magento WebAPI. This action cannot be undone.
:::

## Usage
```sh
n98-magerun2.phar integration:delete <name_or_id>
```

**Arguments:**
| Argument     | Description                   |
|--------------|-------------------------------|
| `name_or_id` | Name or ID of the integration |
