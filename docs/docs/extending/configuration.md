---
title: Configuration
---

n98-magerun2 can be extended and changed by configurations.
It's possible to overwrite the build-in config delivered by magerun. See config.yml file https://github.com/netz98/n98-magerun2/blob/master/config.yaml

Configs can be loaded on different levels:

- build-in config by magerun
- on system level
- on user level
- on module level
- on project level

All configs will be merged in the following order: `buildin -> system -> user -> module -> project`

To verify which config is loaded, you can use the command [`magerun:config:dump`](../command-docs/magerun/magerun-config-dump.md).
The commands [`magerun:config:info`](../command-docs/magerun/magerun-config-info.md) shows the merged config and the source of each config value.

For details on how configuration files are merged and the order of precedence, see the [Config Merging Concept](../concepts/config-merging.md).

## Example Config 

```yaml
autoloaders:
  # Namespace => path to your libs
  VendorPrefix: /path/to/VendorPrefix/src
  AnotherPrefix: /path/to/another-prefix

commands:
  customCommands:
    - VendorPrefix\Magento\Command\MyCommand
    - AnotherPrefix\FooCommand
    - AnotherPrefix\BarCommand
  aliases:
    - "ccc": "cache:clean config"
    - "customer:create:cmuench": "customer:create c.muench@netz98.de test123456 Christian Münch"
```

## Disabling Commands

Use `commands.disabled` to disable commands globally, including their aliases. Disabled commands are unavailable in the CLI and cannot be exposed as MCP tools. Entries can be exact command names or wildcard patterns:

```yaml
commands:
  disabled:
    - mcp:server:start
    - db:dump
    - db:import
```

To disable entire command namespaces, use quoted wildcard patterns:

```yaml
commands:
  disabled:
    - 'mcp:*'
    - 'db:*'
```

This disables all MCP server commands and all database commands globally, including Magento core and custom module commands whose names match a pattern. If a command's canonical name matches, all its aliases are unavailable too. A pattern matching only an alias disables that alias while leaving the canonical command and other aliases available.

All commands are enabled by default. To enable a command again, remove every matching name or pattern from the disabled list in the configuration that added it. Lists from different configuration levels are merged; an empty list in a later config does not clear earlier exclusions.

To keep commands available in the CLI but disable them as MCP tools, configure `disabled` under `N98\Magento\Command\Mcp\Server\StartCommand`:

```yaml
commands:
  N98\Magento\Command\Mcp\Server\StartCommand:
    disabled:
      - db:dump
      - db:import
```

For example, exclude all database commands and the predefined unsafe command group from MCP:

```yaml
commands:
  N98\Magento\Command\Mcp\Server\StartCommand:
    disabled:
      - 'db:*'
      - '@unsafe'
```

The MCP-specific list matches canonical CLI command names, such as `db:dump`, rather than MCP tool names, such as `db_dump`. Aliases are never exposed as separate MCP tools. Disabled tools stay excluded even when selected by `--include`. The `--exclude` option adds further exclusions. Globally disabled commands are unavailable to MCP regardless of the MCP-specific settings.

### Wildcard Syntax

Both disabled lists use case-sensitive shell-style wildcard matching:

| Pattern | Meaning | Example |
| --- | --- | --- |
| `*` | Zero or more characters, including `:` | `'db:*'` disables every database command. |
| `?` | Exactly one character | `'db:d?mp'` matches `db:dump`. |
| `[abc]` | One character from the set | `'db:[di]*'` matches `db:dump`, `db:import`, and `db:info`. |

Quote wildcard patterns in YAML, particularly patterns beginning with `*`, to avoid YAML alias syntax. Use one name or pattern per list entry. `@group` references are supported only by the MCP-specific list; the global list supports names and wildcard patterns.

Configuration changes take effect on the next CLI invocation. Restart an already-running MCP server to apply changes to its tool list. For more examples, see [`mcp:server:start`](../command-docs/mcp/mcp-server-start.md#disabling-mcp-tools-in-configuration).

## Config Types

### System Wide Config

A system wide configuration can be placed in **/etc/n98-magerun2.yaml**

`%windir%\\n98-magerun2.yaml` (only Microsoft Windows)

### User Config

Place your config in your home directory **~/.n98-magerun2.yaml**

`%userprofile%\\n98-magerun2.yaml` (only Microsoft Windows)

### Project Config

You can load a config in your Magento project.
Create your config here: **app/etc/n98-magerun2.yaml**

### Alternative Project Config

You can now place an alternative project config file in the project root folder. This was an often requested feature for n98-magerun. We will have this features also in the next n98-magerun1 version.

It’s now possible to place a new config file .n98-magerun2.yaml in the project root. Please note that project root can be different to your Magento root.
The .n98-magerun2.yaml file will only be loaded if “stop file” .n98-magerun2 (the file with relative path to the Magento root folder) was found.

Example:

```
.                        -- Project root folder
├── .n98-magerun2        -- "Stop file"
├── .n98-magerun2.yaml   -- Alternative project config
└── www                  -- Magento root folder
```
