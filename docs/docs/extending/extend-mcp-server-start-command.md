---
title: Extend mcp:server:start Command
sidebar_label: Extend mcp:server:start Command
---

Extend the [mcp:server:start](../command-docs/mcp/mcp-server-start.md) command to define your own command groups for `--include` and `--exclude`.

## Add your own groups

You can extend or override the command groups by creating your own config in **~/.n98-magerun2.yaml** or a project-specific config in **app/etc/n98-magerun2.yaml**.

Example:

```yaml
commands:
  N98\Magento\Command\Mcp\Server\StartCommand:
    command-groups:
      - id: "order-read"
        description: "Read-only order inspection commands"
        commands: "sales:order:info sales:orders:list"
      - id: "ops"
        description: "Operational checks and cache commands"
        commands: "sys:check sys:cron:run cache:*"
      - id: "safe-default"
        description: "Combine existing groups with custom groups"
        commands: "@read-only @order-read"
```

Use your custom groups with `mcp:server:start`:

```bash
n98-magerun2.phar mcp:server:start --include="@safe-default" --exclude="@unsafe"
```

Verify with `mcp:server:start --help` if your new groups are registered.
