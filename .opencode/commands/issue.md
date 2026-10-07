---
description: File a new GitHub issue on netz98/n98-magerun2, using the right issue template and checking for duplicates first
---

Create a new issue on the **netz98/n98-magerun2** GitHub repository.

This command must work on any contributor's machine, regardless of what they named their git
remotes or which fork setup they use, and regardless of whether they use the `gh` CLI or the
GitHub MCP server. Do not assume a specific remote name or GitHub account — detect everything
below at runtime.

Arguments passed by the user (may be empty): $ARGUMENTS
Parse them loosely:
- A leading word matching one of the issue types (`bug`, `feature`/`feature request`,
  `enhancement`, `question`, `support`, `other`) selects the template to use.
- The rest (or all of it, if no type keyword is present) is the description of the issue — use it
  together with the surrounding conversation to fill in the template.
- If the type can't be inferred from the arguments or the conversation, ask the user which
  template fits, or default to "bug" if the content is clearly describing broken behaviour, or
  "feature" if it's clearly a request for new behaviour.

## Detect available tooling

- Check if the `gh` CLI is installed and authenticated (`gh auth status`). If it's installed but
  not logged in, tell the user to run `gh auth login` (or rely on the MCP fallback below).
- Check if a GitHub MCP server is connected in this OpenCode config (see `opencode.json`,
  `mcp.github`). Either `gh` or that MCP server is enough; if neither is available, stop and tell
  the user to install/authenticate the `gh` CLI or configure the GitHub MCP server (needs
  `GITHUB_TOKEN` in the environment for the local `@modelcontextprotocol/server-github`).

## Target repo

- Always `netz98/n98-magerun2`, regardless of what the local `origin`/`upstream`/etc. remotes are
  named or who owns them. (Filing an issue doesn't require a fork or a branch — no git push is
  needed for this command.)

## Pick a template and labels

Read the templates in `.github/ISSUE_TEMPLATE/` (`01_bugs.md`, `02_feature_request.md`,
`03_enhancement.md`, `04_questions.md`, `05_support.md`, `06_other.md`). Each has frontmatter with
`name`, `about`, and `labels`. Pick the one matching the detected type and:
- Use its `labels:` value as the label(s) to apply to the new issue.
- Use its section headings as the structure for the body, filling each section from the user's
  description / conversation context. Leave a section out if there's nothing meaningful to put
  there rather than inventing content.
- Strip the template's own instructional HTML comments (the `<!-- ... -->` blocks) from the final
  body — those are guidance for humans filling out the web form, not part of the issue itself.

## Check for duplicates first

The templates explicitly ask contributors to check for existing duplicates before filing. Do the
same: search open (and recently closed) issues on `netz98/n98-magerun2` for similar titles/keywords
using `gh issue list --search "..."` / `gh search issues` or the configured GitHub MCP server's
search tool. If you find a strong candidate duplicate, show it to the user and ask whether to
proceed, comment on the existing issue instead, or cancel — don't create a duplicate silently.

## Build the title

Generate a short, specific title from the description (not a generic restatement of the type,
e.g. not just "Bug report"). If the user supplied an explicit title in the arguments, use that.

## Create the issue

Prefer the `gh` CLI when available:

```
gh issue create --repo netz98/n98-magerun2 --title "<title>" --body "<body>" --label "<label>"
```

If `gh` is not available or not authenticated, use the configured GitHub MCP server's
issue-creation tool with the equivalent parameters (owner `netz98`, repo `n98-magerun2`, title,
body, labels).

Before actually calling `gh issue create` / the MCP tool, show the user the computed title, body,
and label(s), and confirm — filing an issue is a visible action on a shared repo.

After creation, print the resulting issue URL.
