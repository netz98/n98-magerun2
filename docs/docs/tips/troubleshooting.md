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
