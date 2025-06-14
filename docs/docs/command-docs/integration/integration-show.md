---
title: integration:show
---

# integration:show

Show information about an existing integration.

:::info
You can use this command to retrieve all details about an integration, or specify a key to get a single value (e.g., just the Access Token).
:::

## Usage
```sh
n98-magerun2.phar integration:show [--format[=FORMAT]] <name_or_id> [<key>]
```

**Arguments:**
| Argument     | Description                                                                 |
|--------------|-----------------------------------------------------------------------------|
| `name_or_id` | Name or ID of the integration                                               |
| `key`        | Only output value of named param like "Access Token". Key is case insensitive.|

**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |

## Example (print only Access Key)
```sh
n98-magerun2.phar integration:show 1 "Access Key"
```
