---
title: Updates
sidebar_position: 3
---

There is a `self-update` command available (This works only
with phar files).

```sh
./n98-magerun2.phar self-update [--dry-run] <version>
```

With `--dry-run` option it is possible to download and test
the phar file without replacing the old one.

The `version` argument is optional and can be used to roll back to a specific
version of n98-magerun2.

:::info
The version was introduced with v8.0.0. Older versions do not have the version argument.
:::

After the update completes, you should see the changelog of the new version.

## Examples

```bash
# Update to the latest version
./n98-magerun2.phar self-update

# Update to a specific version
./n98-magerun2.phar self-update 8.1.1

# Update to an old version (only update to latest stable version is then possible)
./n98-magerun2.phar self-update 7.5.0
```

