---
title: Scripting with n98-magerun2
sidebar_label: Scripting
---

# Scripting with n98-magerun2

n98-magerun2 supports scripting to automate complex workflows or batch operations in Magento 2. You can write multiple commands in a single file and execute them together.

## General Scripting Features

- **Multiline values**: Use `\n` to set multiline config values.
- **Comments**: Use `#` at the beginning of a line.
- **Shell commands**: Prefix with `!` to run shell commands.
- **Variables**: Register variables with `${var}=value` or prompt for them with `${var}=?`.
- **Pre-defined variables**: Use `${magento.root}`, `${magento.version}`, `${magento.edition}`.

## Using Unix Pipes

You can pipe commands directly into the script command for quick, one-off executions:

```sh
echo "cache:flush" | n98-magerun2.phar script
```

Or execute a script from a file:

```sh
n98-magerun2.phar script < filename
```

## Executable Scripts

Make your script file executable and use the shebang line to run it directly:

```sh
#!/usr/bin/env n98-magerun2.phar script

config:store:set "web/cookie/cookie_domain" example.com
cache:flush
```

Then make it executable:

```sh
chmod +x test.magerun
```

---

For command-specific options and arguments, see the [script command documentation](../command-docs/scripting/script.md).

