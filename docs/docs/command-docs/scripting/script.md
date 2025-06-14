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

## More about scripting

For general scripting features, see [Scripting with n98-magerun2](../../extending/scripting.md).
