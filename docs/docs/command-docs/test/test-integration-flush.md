---
title: test:integration:flush
sidebar_label: test:integration:flush
---

# test:integration:flush

Cleanup integration test temp folders for developer mode. This command is useful for developers who run integration tests frequently and need to clean up accumulated temporary files and directories.

## Usage

```bash
n98-magerun2 test:integration:flush [options]
```

## Options

- `--force, -f`: Completely remove all sandbox directories instead of just cleaning var folders

## Description

This command traverses all integration test sandbox directories (typically located in `dev/tests/integration/tmp/sandbox-*`) and performs cleanup operations:

**Default behavior** (without `--force`):
- Finds all sandbox directories matching the pattern `sandbox-*`
- Cleans the `var` folder contents within each sandbox
- Preserves the sandbox directory structure and other files

**Force mode** (with `--force` option):
- Completely removes all sandbox directories
- More aggressive cleanup that frees up maximum disk space

## Examples

### Clean var folders only (default)
```bash
n98-magerun2 test:integration:flush
```

This will clean the contents of var folders in all sandbox directories but preserve the sandbox structure.

### Force removal of all sandboxes
```bash
n98-magerun2 test:integration:flush --force
```

This will completely remove all integration test sandbox directories.

## Output Examples

### No sandboxes found
```
No integration tests directory found at: /path/to/magento/dev/tests/integration
```

### Successful cleanup
```
Cleaned var folder in sandbox: sandbox-20240101_123456
Cleaned var folder in sandbox: sandbox-20240102_654321
Successfully cleaned 2 var folders
```

### Force removal
```
Removed sandbox: sandbox-20240101_123456
Removed sandbox: sandbox-20240102_654321
Successfully removed 2 sandboxes
```

## Use Cases

- **Development workflow**: Clean up accumulated cache and temporary files between test runs
- **Disk space management**: Free up space used by integration test artifacts
- **Test environment reset**: Ensure clean state before running new integration tests
- **CI/CD cleanup**: Reset integration test environment in automated pipelines

:::tip

Use the default mode (without `--force`) for routine cleanup during development. Use `--force` only when you need to completely reset the integration test environment.

:::

:::warning

The `--force` option will permanently delete all integration test sandbox directories. Make sure you don't have any important test data in these directories before using this option.

:::