---
sidebar_position: 2
title: Usage and General Commands
---
## Usage / Commands

> **NOTE** There are more commands available as documented here. Please use the list command (`n98-magerun2.phar list`) and `n98-magerun2.phar help <command>` to see all options for a specific command.

All commands try to detect the current Magento root directory. If you
have multiple Magento installations you must change your working
directory to the preferred installation.

You can list all available commands by:

```sh
n98-magerun2.phar list
```

If you don't have the .phar file installed system wide you can call it
with the PHP CLI interpreter:

```sh
php n98-magerun2.phar list
```

Global config parameters:

| Parameter                            | Description                                                                 |
|--------------------------------------|-----------------------------------------------------------------------------|
| `-h, --help`                         | Display help for the given command.                                         |
| `-q, --quiet`                        | Do not output any message.                                                  |
| `-V, --version`                      | Display this application version.                                           |
| `--ansi` / `--no-ansi`               | Force (or disable) ANSI output.                                             |
| `-n, --no-interaction`               | Do not ask any interactive question.                                        |
| `--root-dir[=ROOT-DIR]`              | Force Magento root dir. No auto detection.                                  |
| `--skip-config`                      | Do not load any custom config.                                              |
| `--skip-root-check`                  | Do not check if n98-magerun2 runs as root.                                  |
| `--skip-core-commands`               | Do not include proxied Magento core commands.                               |
| `--skip-magento-compatibility-check` | Do not check Magento version compatibility for n98-magerun2 commands.       |
| `-v|vv|vvv, --verbose`               | Increase the verbosity of messages (1 for normal, 2 for verbose, 3 for debug).|

### Call Core Magento Commands

The tool can be used to run core Magento commands. We provide a internal *Proxy Command* which calls
the original Magento command via `bin/magento`.
All options and arguments are passed to the original command.

If you do not want to use the proxy command you can disable it with the `--skip-core-commands` option.

One of the big advantages of the proxy command is that you can run any command without having to change the working
directory to the Magento root directory
or to specify the path to `bin/magento` if your current working directory is inside the Magento installation.

If you are outside the Magento root directory you can run any command by specifying the Magento root directory with
the `--root-dir` option.
That is very useful if you have multiple Magento installations or if it is used in some kind of automation.

For core commands we filter environment variables to avoid problems with enabled xdebug extension.

### Open Shop in Browser

```sh
n98-magerun2.phar open-browser [store]
```
