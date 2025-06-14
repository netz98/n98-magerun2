---
title: cache:remove:id
---

# cache:remove:id

Remove entry by ID

The command is not checking if the cache id exists. If you want to check if the cache id exists use the `cache:remove:id` command with the `--strict` option.

```sh
n98-magerun2.phar cache:remove:id [--strict] <id>
```

**Options:**

| Option     | Description                                      |
|------------|--------------------------------------------------|
| `--strict` | Use strict mode (remove only if cache id exists) |
