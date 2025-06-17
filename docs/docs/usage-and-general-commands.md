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
- `--help`: Show help for a command
- `--version`: Show version information

## Example

```sh
php n98-magerun2.phar cache:clean
```

See the command reference for more details on each command.

## Loading External Modules via Command Line

n98-magerun2 allows you to load custom modules from directories specified directly on the command line. This is useful for testing modules, or when you want to temporarily load a module without modifying global or project configuration files.

### `--add-module-dir=<path>`

You can use the `--add-module-dir` option to specify a path to a directory containing a n98-magerun2 module. The specified directory should contain a valid `n98-magerun2.yaml` file at its root, which defines the commands and autoloaders for that module.

**Usage:**

```bash
n98-magerun2 --add-module-dir=/path/to/your/module some:command
```

Or, using a relative path:

```bash
n98-magerun2 --add-module-dir=../relative/path/to/your/module some:command
```

**Multiple Directories:**

This option can be used multiple times to load modules from several different directories:

```bash
n98-magerun2 --add-module-dir=/path/to/module1 --add-module-dir=/path/to/module2 admin:user:list
```

**Details:**

- The path provided can be absolute or relative to the current working directory from where n98-magerun2 is executed.
- n98-magerun2 will look for a `n98-magerun2.yaml` file in the root of the specified directory.
- The configurations (commands, autoloaders, etc.) from this YAML file will be merged with the existing configurations.
- If the path is invalid, does not exist, or does not contain a readable `n98-magerun2.yaml`, it will be skipped. Verbose mode (`-v`) may show warnings for such cases.
