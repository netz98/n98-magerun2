---
id: usage-and-general-commands
title: Usage and General Commands
sidebar_label: Usage and General Commands
sidebar_position: 4
---

# Usage and General Commands

This section covers general usage and commands for n98-magerun2.

## Running Commands

You can run n98-magerun2 commands in two ways:

1. **Directly execute the PHAR file** (if it has the executable flag set):

```sh
./n98-magerun2.phar list
```

   If you get a permission error, set the executable flag:

```sh
chmod +x n98-magerun2.phar
```

2. **Run the PHAR file via PHP**:

```sh
php n98-magerun2.phar list
```

To get help for a specific command:

```sh
php n98-magerun2.phar <command> --help
```

## Common Options

- `--root-dir`: Specify Magento root directory
- `--skip-config`: Skip loading of custom config files
- `--skip-root-check`: Suppress warning if n98-magerun2 is run as root user
- `--help`: Show help for a command
- `--version`: Show version information

## Example

```sh
php n98-magerun2.phar cache:clean
```

See the command reference for more details on each command.
