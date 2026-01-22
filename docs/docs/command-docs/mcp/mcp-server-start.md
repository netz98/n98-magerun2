---
title: mcp:server:start
---

# mcp:server:start

:::info
Start an MCP server exposing all n98-magerun2 commands as tools.
:::

## Description

The `mcp:server:start` command starts a [Model Context Protocol (MCP)](https://modelcontextprotocol.io/) server.
This server exposes all available n98-magerun2 commands as executable tools to MCP clients (like Claude Desktop, or other AI agents).

This allows AI assistants to directly interact with your Magento 2 installation through n98-magerun2 commands.

## Usage

```bash
n98-magerun2.phar mcp:server:start
```

The server runs using stdio transport, meaning it communicates via standard input/output. This is the standard way to integrate with local MCP clients.

## Features

- **Tool Exposure**: All standard n98-magerun2 commands (and custom ones) are automatically registered as MCP tools.
- **Argument Handling**: Arguments for commands are passed as a single string.
- **Output**: The output of the command is returned to the MCP client.

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
