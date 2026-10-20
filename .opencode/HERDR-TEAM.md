# Three-agent development with Herdr

This repository provides three English-language OpenCode V2 primary profiles:

| Profile | Responsibility | Location during a task |
| --- | --- | --- |
| `orchestrator` | Plan, delegate through Herdr, manage handoffs and QA gates | Original checkout |
| `developer` | Implement and run focused checks | Dedicated Developer worktree |
| `qa` | Independently review and test a frozen candidate | Dedicated QA worktree |

These are **not OpenCode subagents**. Each runs in an independent interactive
OpenCode session hosted by a Herdr pane. The profiles deny the `subagent` tool;
the Orchestrator controls workers with the Herdr CLI and `herdr` skill.

## Prerequisites

- Start inside Herdr (`HERDR_ENV=1`). Install OpenCode V2 and make the `herdr`
  skill available to each session. The skill is not bundled into this repository.
- Keep normal OpenCode permission prompts enabled. These profiles do not grant
  blanket shell access or bypass user approvals.
- Commit these profile files before creating worktrees from a base that needs
  them. Git worktrees do not inherit uncommitted or ignored files, dependencies,
  credentials, local environment variables, or a ready Magento test installation.
- Supply a concrete task. Setup alone does not authorize changes, pushes, or
  merges. Authorize local handoff commits when you want commit-based QA.

## Start the Orchestrator

In a Herdr shell pane at this repository's root:

```sh
opencode mini --agent orchestrator
```

Mini is used because the installed V2 CLI supports explicit `--agent` there.
Do not assume that the full-screen `opencode` command accepts the same flag.
You can also select the `orchestrator` profile in the full TUI's agent selector.
Existing sessions do not switch profiles merely because files were added.

Example first prompt:

```text
Use the herdr skill to coordinate the Developer and QA profiles for this task:
[describe the change and acceptance criteria].

Create isolated Developer and QA Git worktrees from the current committed HEAD.
Keep my existing checkout and unrelated changes untouched. You may create local
task commits in the worker branches for immutable QA handoff. Do not push, merge
into my original branch, or clean up panes/worktrees without asking me.
Run independent implementation and QA preparation in parallel. Require QA to
verify the exact candidate commit before reporting completion.
```

## What the Orchestrator does

1. Checks Herdr, repository state, acceptance criteria, and commit permissions.
2. Creates two worktrees using `herdr worktree create --no-focus` with explicit
   branches, paths, and a recorded base SHA. Herdr opens linked workspaces with
   shell panes; each worker runs in its worktree's pane.
3. Parses returned JSON/live state for the actual pane IDs and starts workers:

   ```sh
   herdr agent start dev-<run> --kind opencode --pane <dev-pane-id> -- mini --agent developer
   herdr agent start qa-<run> --kind opencode --pane <qa-pane-id> -- mini --agent qa
   ```

   These commands are templates, not copy-and-run commands. Profile IDs
   (`developer`, `qa`) are distinct from unique Herdr live names (`dev-<run>`,
   `qa-<run>`) and opaque pane IDs returned by Herdr.

4. Dispatches independent assignments through `herdr agent prompt` before
   waiting. Developer implements while QA prepares coverage and checks readiness.
5. Collects Developer's committed candidate and explicitly directs QA to apply
   its ordered commits in the QA worktree. QA reports the original candidate and
   actual tested HEAD, checks, findings, and PASS/FAIL/BLOCKED.
6. Routes fixes back to Developer, requires revalidation of new candidates, and
   reports the result. Integration and cleanup require separate user approval.

Herdr's `agent start` needs an existing available shell pane; it does not create
the pane. To add an extra pane, the Orchestrator first inspects layout, then uses
`herdr pane split` with explicit target/cwd and `--no-focus`.

## Develop a GitHub issue or feature in its own environment

The unit of work is an issue/feature workspace: an identifiable Git branch and
worktree, a linked Herdr workspace with an OpenCode pane, and an isolated runtime.
The original checkout and its running services remain untouched.

For example, give the Orchestrator this prompt (replace the issue URL):

```text
Develop https://github.com/netz98/n98-magerun2/issues/<number>.
Read the issue and relevant comments, extract acceptance criteria, and confirm
the base branch before starting.

Create a dedicated issue worktree/branch and Herdr workspace for Developer,
plus a separate QA worktree and environment. Use the herdr and ddev-expert skills.
Give each runtime a unique DDEV project name, database, and Magento test volume.
Show me the provisioning plan and request approval before starting containers,
downloading dependencies, or installing test environments.

You may create local task commits for immutable QA handoff. Do not change my
original checkout, push, post to GitHub, open a PR, or clean up without asking.
Report the candidate SHA, QA evidence, environment identities, and next steps.
```

For a feature without an issue, replace the first paragraph with the feature
description and acceptance criteria. The same isolation and QA process applies.

An illustrative task inventory looks like this; actual IDs come from Herdr:

| Role | Git branch | Worktree | Runtime identity |
| --- | --- | --- | --- |
| Orchestrator | Original branch, unchanged | Original checkout | Existing runtime, unchanged |
| Developer | `bugfix/issue-123-cache-output` | Sibling `issue-123-dev` directory | `n98-issue-123-dev-<run>` |
| QA | `chore/issue-123-qa` | Sibling `issue-123-qa` directory | `n98-issue-123-qa-<run>` |

### Runtime provisioning is a separate readiness gate

Creating a worktree alone does **not** create a usable test environment. This
repository has a fixed DDEV project name in `.ddev/config.yaml`. Each new
runtime needs an ignored worktree-local name override, plus verification of
container/volume names, service URLs, and ports. The Magento test volume in
`.ddev/docker-compose.magento-volumes.yaml` is keyed by `DDEV_SITENAME`, so a
unique project name is important for data isolation.

Also, `ddev start` triggers Composer installation and `.ddev/hook_01_setup.sh`,
which configures Magento repository authentication and sets up a Mage-OS test
installation. The Orchestrator must inspect and get approval for those effects,
not blindly start the copied project. No secrets or databases are automatically
copied from the original checkout. New dependencies/data belong to the assigned
environment; local runtime overrides must not enter feature commits.

Provisioning commands run in Herdr-owned shell panes. The Orchestrator records
the actual runtime identity, URLs, test root, and readiness checks. If credentials
or provisioning approval are missing, it reports **BLOCKED**, not "ready".
Only explicitly approved shared-service operation may relax this isolation.

After QA passes, the result is a verified local candidate. Publishing a PR,
posting an issue comment, merging, and removing containers/volumes/worktrees
are separate actions requiring approval.

## Parallelism and limits

With exactly three sessions, one implementation task and independent QA work
can progress together. QA can verify candidate A while Developer starts an
independent task B in another assigned worktree. Multiple simultaneous coding
tasks would require additional Developer sessions and are outside this setup.

Do not give both workers the same writable checkout. Do not review uncommitted
files in the Developer tree. Branch names are not immutable handoff identifiers.
Shared Magento databases, DDEV services, and ports require explicit scheduling
or separate environments even when code lives in different worktrees.

The profiles' role boundaries are instructions, not an OS sandbox: shell tools
and user-approved commands can modify files. Review permission requests normally.
Neither lifecycle status `done` nor an agent's claim replaces test evidence.

## Setup verification

```sh
opencode debug agents
git diff --check
```

The agent list should include `orchestrator`, `developer`, and `qa` as primary
profiles. This checks discovery, not a live multi-agent coding/test run. Inspect
`herdr status` and installed command help before relying on version-specific
behavior. Do not restart an active Herdr server just to refresh its version.
