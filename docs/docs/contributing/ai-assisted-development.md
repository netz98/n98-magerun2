---
title: AI-Assisted Development
sidebar_label: AI-Assisted Development
---

The repository ships shared configuration for AI coding assistants — currently [Claude Code](https://docs.claude.com/en/docs/claude-code) and [OpenCode](https://opencode.ai/) — so that contributors get the same guardrails and shortcuts regardless of which tool they use.

:::info
None of this is required to contribute. It's tooling for contributors who use an AI assistant and want it to follow the same conventions as the rest of the project.
:::

## AGENTS.md

[`AGENTS.md`](https://github.com/netz98/n98-magerun2/blob/develop/AGENTS.md) at the repository root is the tool-agnostic source of truth read by both Claude Code and OpenCode (and any other assistant that supports the `AGENTS.md` convention). It documents:

- Build, test, and static analysis commands
- Project structure (`src/N98/...`, `tests/...`, `docs/...`)
- How to add a new command, including the docs entry required for it
- Branch naming and [Conventional Commits](https://www.conventionalcommits.org/) rules

When project conventions change, update `AGENTS.md` first — it's what keeps assistant-generated contributions consistent with the rest of the codebase.

## Custom slash commands

Two commands are defined for both tools, with identical instructions in each:

| Command | Purpose |
| --- | --- |
| `/issue [bug\|feature\|enhancement\|question\|support] <description>` | Files a new GitHub issue on `netz98/n98-magerun2` using the matching template from `.github/ISSUE_TEMPLATE/`, after checking for duplicates. |
| `/pr [issue-number] [base-branch] [title override...]` | Opens a pull request against `netz98/n98-magerun2` (base `develop` by default), filled in from `.github/PULL_REQUEST_TEMPLATE.md` and linked to a related issue if one is detected. |

Both commands:

- Work regardless of local remote naming or fork setup — they detect the right remote/owner at runtime instead of assuming `origin`.
- Use the `gh` CLI when it's installed and authenticated, and fall back to the GitHub MCP server otherwise (see [MCP server integration](#mcp-server-integration) below). If neither is available, they tell you what to set up.
- Show you the computed title/body/base/head and ask for confirmation before actually creating anything — filing an issue or opening a PR is a visible action on a shared repo.

Command definitions live in:

- `.claude/commands/issue.md`, `.claude/commands/pr.md`
- `.opencode/commands/issue.md`, `.opencode/commands/pr.md`

## Skills

Reusable, longer-form instructions live under `.agents/skills/` and are symlinked into each tool's own skills directory (`.claude/skills/...`, `.agents/.opencode/skills/...`) so there's a single source of truth per skill.

- **`magerun-release`** — describes the technical release process (inspecting the git log since the last tag, bumping `N98\Magento\Application::APP_VERSION`, `version.txt` and `CHANGELOG.md`, and running `release-it` from `master`).

Add new skills under `.agents/skills/<name>/SKILL.md` and symlink them from `.claude/skills/` if they should also be available in Claude Code.

## MCP server integration

`opencode.json` at the repository root configures the [GitHub MCP server](https://github.com/github/github-mcp-server) for OpenCode, so `/issue` and `/pr` can use it as a fallback when the `gh` CLI isn't available. Claude Code users get equivalent GitHub MCP tools by [connecting the GitHub MCP server](https://docs.claude.com/en/docs/claude-code/mcp) themselves.

n98-magerun2 also ships its own MCP server ([`mcp:server:start`](../command-docs/mcp/mcp-server-start.md)), which lets your assistant run n98-magerun2 commands directly against a Magento installation instead of shelling out. It's a convenient way to exercise commands against the test environments in this repo's ddev setup while contributing — see [Example Configuration (ddev)](../command-docs/mcp/mcp-server-start.md#example-configuration-ddev) for Claude Code and OpenCode setup.

## What's checked into git

Only the shared, tool-agnostic parts of this configuration are committed — everything else (personal settings, local caches, session state) is ignored:

```
AGENTS.md
opencode.json
.claude/commands/
.claude/skills/
.opencode/commands/
.agents/skills/
```

`.claude/settings.local.json`, `.claude/scheduled_tasks.lock`, and the `.opencode/node_modules`/lockfiles used by its MCP plugin dependency stay local to each contributor's machine.
