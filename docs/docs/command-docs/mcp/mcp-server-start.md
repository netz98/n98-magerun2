---
title: mcp:server:start
---

# mcp:server:start

:::info
Start an MCP server exposing selected n98-magerun2 commands as tools.
:::

## Description

The `mcp:server:start` command starts a [Model Context Protocol (MCP)](https://modelcontextprotocol.io/) server.
This server exposes n98-magerun2 commands as executable tools to MCP clients (like Claude Desktop, or other AI agents).

By default, Symfony internal commands (`help`, `list`, `completion`) and Magento core proxy commands are not exposed.
You can control the exposed command set with `--include` and `--exclude`.

This allows AI assistants to directly interact with your Magento 2 installation through n98-magerun2 commands.

## Usage

```bash
n98-magerun2.phar mcp:server:start
```

```bash
n98-magerun2.phar mcp:server:start --include="sys:cron:* cache:*" --exclude="sys:cron:history"
```

```bash
n98-magerun2.phar mcp:server:start --include="@cron"
```

The server runs using stdio transport, meaning it communicates via standard input/output. This is the standard way to integrate with local MCP clients.

## Features

- **Tool Exposure**: Commands are filtered before registration. Aliases and internal Symfony commands are excluded.
- **Argument Handling**: Arguments for commands are passed as a single string.
- **Output**: The output of the command is returned to the MCP client.

## Include / Exclude Filters

- `--include`: Registers only commands matching one or more patterns.
- `--exclude`: Removes matching commands from the result set.
- Wildcards are supported (`*`, `?`), for example: `sys:cron:*`.
- Group references are supported with `@group` syntax.

Command groups are configured via command config in `config.yaml`:

```yaml
commands:
  N98\Magento\Command\Mcp\Server\StartCommand:
    command-groups:
      - id: cron
        description: Cron related commands
        commands: "sys:cron:*"
```

Then you can use `@cron` in `--include` and `--exclude`.

Run the command help to see all configured groups and their patterns:

```bash
n98-magerun2.phar mcp:server:start --help
```

## Predefined Command Groups

The project ships with these predefined groups in `config.yaml`:

- `@admin` - Admin users, tokens and notifications (`admin:*`)
- `@cron` - Cron related commands (`sys:cron:*`)
- `@cache` - Cache related commands (`cache:*`)
- `@config` - Environment and store config commands (`config:*`, `magerun:config:*`)
- `@database` - Database commands (`db:*`)
- `@development` - Developer and generation commands (`dev:*`, `generation:*`)
- `@index` - Indexer commands (`index:*`)
- `@integration` - Integration commands (`integration:*`)
- `@maintenance` - Core maintenance and setup commands (`sys:maintenance`, `sys:setup:*`)
- `@read-only` - Mostly read-only inspection command set
- `@repo` - Script repository commands (`script`, `script:repo:*`)
- `@unsafe` - Commands that can mutate or delete data
- `@system` - System commands (`sys:*`)

Example:

```bash
n98-magerun2.phar mcp:server:start --include="@read-only" --exclude="@unsafe"
```

## Example Configuration (Claude Desktop)

To use n98-magerun2 as an MCP server in Claude Desktop, add the following to your `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "n98-magerun2": {
      "command": "/path/to/php",
      "args": [
        "/path/to/n98-magerun2.phar",
        "mcp:server:start"
      ]
    }
  }
}
```

Make sure to replace `/path/to/php` and `/path/to/n98-magerun2.phar` with your actual paths.

## Example Configuration (ddev)

[ddev](https://ddev.com/) ships `magerun2` out of the box for Magento 2 projects, so you don't have to install anything to run the MCP server. Note that the bundled version may not be the latest — see [ddev Integration](../../extending/development/install-in-ddev.md) if you need to update it.

Since the command must run inside the web container, add it as an MCP server via `ddev exec`.

:::info
`--root-dir` is resolved **inside the web container**, not on the host. Use the container path to your Magento installation (usually `/var/www/html` unless your project uses a different docroot).
:::

### Claude Code

```bash
claude mcp add n98-magerun -- ddev exec magerun2 --root-dir=/var/www/html mcp:server:start
```

Once added, restart Claude Code (or run `claude mcp list`) to confirm the server is available, then verify the exposed tools with `mcp:server:start --help`.

### OpenCode

Add an entry to `opencode.jsonc` (or `opencode.json`) in your project root:

```jsonc
{
  "$schema": "https://opencode.ai/config.json",
  "mcp": {
    "n98-magerun": {
      "type": "local",
      "command": ["ddev", "exec", "magerun2", "--root-dir=/var/www/html", "mcp:server:start"],
      "enabled": true
    }
  }
}
```

Restart OpenCode to pick up the new server.
