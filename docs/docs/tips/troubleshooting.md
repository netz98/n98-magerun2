---
title: Troubleshooting
sidebar_position: 10
---

## Error Messages

### TTY mode requires /dev/tty to be read/writable

This can happen e.g. in CI/CD environments. Try to run with `--no-interactive` flag.

### n98-magerun2 update failed: the "/usr/local/bin/n98-magerun2.phar" file could not be written

Most the time the write permissions are not available to overwrite the phar file. Try to add write permissions with `chmod` command. Example:

```bash
chmod +w /usr/local/bin/n98-magerun2.phar
```

### Magento folder could not be detected

If you see this message then a n98-magerun2 command needs a proper installed Magento environment. Try to run `bin/magento` in your environment to see if Magento works.

### Magento Core Commands cannot be loaded. Please verify if "bin/magento" is running.

This message indicates that command list of the Magento environment could not be loaded. That's an indicator that your Magento installation is maybe broken. In that case you can only use the build-in command of n98-magerun2.

### It's not recommended to run n98-magerun as root user

You try to run n98-magerun with root permissions. That's not a good idea because you could produce permission issues in your file system if e.g. cache files are generated with root permissions. Additionally it's not recommended to work as root for security reasons.

If you really decide to run the tool with root permissions then it's possible to disable the warning by adding a custom `/etc/n98-magerun2.yaml` with the following content:

```yaml
application:
  check-root-user: false
```
### Command not found

If the shell cannot locate `n98-magerun2.phar`, either call it with a path (`./n98-magerun2.phar`) or move the file into a directory that is part of your `$PATH` such as `/usr/local/bin`.

### Permission denied

When the file is not executable, run `chmod +x n98-magerun2.phar` to add execute permission.

### PHP Fatal error: Class 'Phar' not found

Ensure the PHP `phar` extension is installed and enabled for the CLI. On Debian based systems this can be installed with `sudo apt-get install php-phar`.

### Check tool, PHP and Magento versions

Incompatibilities can lead to `TypeError` or fatal errors. Verify the versions via `n98-magerun2 --version`, `php -v` and the Magento `composer.json`. Updating n98-magerun2 with `self-update` often resolves these issues.

### Increasing verbosity

Re-run failing commands with `-v`, `-vv` or `-vvv` to receive more detailed output and stack traces. This helps identifying the origin of an error, especially for proxied Magento commands.

### Troubles with self-update

If `self-update` fails due to missing permissions or a broken Magento installation, run the command with `sudo` (if installed system-wide) or execute it from outside the Magento root.

### Manually specify the Magento root

When automatic detection fails use the `--root-dir=/path/to/magento` option or create a `.n98-magerun2` file in a parent directory containing the relative path to the Magento root.

