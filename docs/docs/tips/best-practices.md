---
name: Best Practices
---

# Best Practices

## Disable Cache for Frontend Work

```bash
n98-magerun2.phar cache:disable view_files_preprocessing view_files_fallback full_page layout block_html
```

## Check the Environment from the Ground Up

When troubleshooting, start with the basics:

- Verify the shell can execute the PHAR. If the command is not found, run it with a relative path (`./n98-magerun2.phar`) or install it system-wide.
- Ensure the file is executable (`chmod +x ./n98-magerun2.phar`).
- Confirm PHP has the `phar` extension enabled and matches your Magento version.

## Use Verbosity Flags for Debugging

n98-magerun2 supports the Symfony verbosity flags. Re-run failing commands with
`-v`, `-vv`, or `-vvv` to get detailed output and stack traces.

## Stay Up-to-Date

Regularly update the tool with:

```bash
n98-magerun2.phar self-update
```

If installed system-wide you may need `sudo`. Use `--dry-run` to test the
download.

## Manage Configuration Files

n98-magerun2 loads configuration from multiple locations in the following order:

1. `/etc/n98-magerun.yaml`
2. `~/.n98-magerun.yaml`
3. `app/etc/n98-magerun2.yaml`

Use `magerun:config:info` to list loaded files and `magerun:config:dump` to see
the merged configuration.

## Handle Custom Project Layouts

If your Magento root is not detected automatically, pass it explicitly with
`--root-dir=/path/to/magento` or create a `.n98-magerun2` stop file that
contains the relative path to the root directory.

## Workaround for Read-Only Filesystems

On platforms where the filesystem is read-only (e.g. Magento Cloud), some
commands like `dev:console` need a writable config directory. Use

```bash
XDG_CONFIG_HOME=~/var/ ./n98-magerun2.phar dev:console
```

This sets a temporary directory for PsySH to store its files.
