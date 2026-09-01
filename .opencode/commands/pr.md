---
description: File a PR against netz98/n98-magerun2 (defaults to base branch develop), referencing the related issue if one exists
---

Create a pull request for the **netz98/n98-magerun2** repository on GitHub.

This command must work on any contributor's machine, regardless of what they named their
remotes or which fork setup they use. Do not assume specific remote names (`origin`,
`upstream`, ...) or a specific GitHub account — detect everything below at runtime.

Arguments passed by the user (may be empty): $ARGUMENTS
Parse them loosely:
- A bare number or `#123` → the issue this PR relates to/fixes.
- A token that looks like a branch name (e.g. `develop`, `2.x`, `main`) → overrides the base branch.
- Anything else remaining → use as the PR title instead of a generated one.

## Detect available tooling

- Check if the `gh` CLI is installed and authenticated (`gh auth status`). If it's installed but not
  logged in, tell the user to run `gh auth login` (or rely on the MCP fallback below).
- Check if a GitHub MCP server is connected in this OpenCode config (see `opencode.json`, `mcp.github`).
  Either `gh` or that MCP server is enough; if neither is available, stop and tell the user to
  install/authenticate the `gh` CLI or configure the GitHub MCP server (needs `GITHUB_TOKEN` in the
  environment for the local `@modelcontextprotocol/server-github`).

## Detect the current GitHub identity

- Via `gh`: `gh api user --jq .login`.
- Via the GitHub MCP server: use whichever "who am I" / get-authenticated-user tool it exposes.

## Target repo & branches

- Upstream repo is always `netz98/n98-magerun2` — the PR is always opened there, never against
  whatever remote happens to be called `origin` on this machine.
- Base branch is `develop` unless the arguments specify a different one.
- Head is the **current local branch**. Never create the PR from `develop`/`main`/`master` itself —
  if the current branch is one of those, stop and ask the user which branch to use.
- Run `git remote -v` and parse the owner/repo out of each remote URL (works for both
  `git@github.com:OWNER/REPO.git` and `https://github.com/OWNER/REPO.git` forms). Do not rely on the
  remote's local name — a contributor may have named things differently.
- From that list, determine where to push the branch for the head ref:
  - If one of the remotes' owner matches the detected GitHub identity → that's the contributor's own
    fork; push the branch there.
  - Otherwise, if one of the remotes points directly at `netz98/n98-magerun2` and there is no
    identity-matching fork remote → the contributor likely has direct push access (e.g. a
    maintainer); push the branch there.
  - If neither case applies unambiguously, ask the user which remote to push the branch to.
- Compute `head`:
  - `<branch>` if the push target *is* `netz98/n98-magerun2`.
  - `<push-remote-owner>:<branch>` if the push target is a fork.
- Check `git status` and `git log <base>..HEAD --oneline` to confirm there are actual commits to
  submit. If the branch is behind or has no diff vs the base, tell the user instead of proceeding.
  Note: `<base>` here means the base branch as it exists on the `netz98/n98-magerun2` remote (e.g.
  `<netz98-remote>/develop`), not any local branch of the same name, since a contributor's local
  `develop` may be stale.

## Push the branch

- Check whether the current branch exists on the push-target remote and is up to date.
- If it needs pushing, **ask the user to confirm** before running
  `git push -u <push-remote> <branch>` — pushing is a visible, hard-to-reverse action.

## Find a related issue

If no issue number was given in the arguments, try to detect one automatically:
- Look at the current branch name for patterns like `123-...`, `issue-123`, `fix/123`, `feature/123-...`.
- Look at the commit messages in `git log <base>..HEAD` for `#123`, `fixes #123`, `closes #123`, `relates to #123`.
- If nothing is found, proceed without an issue reference (don't block on it).

## Build the PR body

Read `.github/PULL_REQUEST_TEMPLATE.md` and use it as the basis for the PR body:
- Fill the **Related Issue(s)** section with `Fixes #<n>` (if the issue looks like it's fully resolved by this PR) or `Relates to #<n>` otherwise. Leave the template's own instructional HTML comments out of the final body.
- Fill **Summary** with a concise description generated from the commit messages / diff (`git log <base>..HEAD` and `git diff <base>..HEAD --stat`).
- Leave **Additional Notes** empty unless there's something genuinely useful to add (e.g. testing notes).
- Keep the contributor checklist at the top as-is.

## Create the PR

Prefer the `gh` CLI when available:

```
gh pr create --repo netz98/n98-magerun2 --base <base-branch> --head <head> --title "<title>" --body "<body>"
```

If `gh` is not available or not authenticated, use the configured GitHub MCP server's PR-creation tool
with the equivalent parameters (owner `netz98`, repo `n98-magerun2`, base, head, title, body).

Before actually calling `gh pr create` / the MCP tool, show the user the computed base, head, title,
and body, and confirm — opening a PR is a visible action on a shared repo.

After creation, print the resulting PR URL.
