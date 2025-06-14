---
title: script
sidebar_label: script
---

# script

Run multiple commands from a script file.

:::tip
Use the script command to automate complex workflows or batch operations in Magento 2 by writing them in a single file.
:::

## Usage

```sh
n98-magerun2.phar script [options] [--] [<filename>]
```

### Arguments
| Argument   | Description   |
|------------|---------------|
| `filename` | Script file   |

### Options
| Option                   | Description                                    |
|--------------------------|------------------------------------------------|
| `-d, --define[=DEFINE]`  | Defines a variable (multiple values allowed)   |
| `--stop-on-error`        | Stops execution of script on error             |

## Examples

:::note
You can include comments in your script files using the `#` character at the beginning of a line.
:::

```sh
# Set multiple config
config:store:set "web/cookie/cookie_domain" example.com

# Set with multiline values with `\n`
config:store:set "general/store_information/address" "First line\nSecond line\nThird line"

# This is a comment
cache:flush
```

Optionally you can work with unix pipes:

:::tip
You can pipe commands directly into the script command for quick, one-off executions.
:::

```sh
echo "cache:flush" | n98-magerun2.phar script
```

```sh
n98-magerun2.phar script < filename
```

It is even possible to create executable scripts:

:::info
Make your script file executable and use the shebang line to run it directly with n98-magerun2.
:::

Create file `test.magerun` and make it executable `chmod +x test.magerun`:

```sh
#!/usr/bin/env n98-magerun2.phar script

config:store:set "web/cookie/cookie_domain" example.com
cache:flush

# Run a shell script with "!" as first char
! ls -l

# Register your own variable (only key = value currently supported)
${my.var}=bar

# Let magerun ask for variable value - add a question mark
${my.var}=?

! echo ${my.var}

# Use resolved variables from n98-magerun in shell commands
! ls -l ${magento.root}/code/local
```

## Pre-defined variables

:::info
Several variables are available for use in your scripts to make them more dynamic and environment-aware.
:::

| Variable             | Description                                |
|----------------------|--------------------------------------------|
| `${magento.root}`    | Magento Root-Folder                        |
| `${magento.version}` | Magento Version i.e. 2.0.0.0               |
| `${magento.edition}` | Magento Edition -> Community or Enterprise |
| `${magerun.version}` | Magerun version i.e. 2.1.0                 |
| `${php.version}`     | PHP Version                                |
| `${script.file}`     | Current script file path                   |
| `${script.dir}`      | Current script file dir                    |

Variables can be passed to a script with `--define (-d)` option.

Example:

```sh
n98-magerun2.phar script -d foo=bar filename

# This will register the variable ${foo} with value bar.
```

It's possible to define multiple values by passing more than one option.

:::tip
You can use environment variables in your script by using the `env.` prefix.
:::
