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

## Quick start: guided supervised task

From an OpenCode session inside a Herdr pane at this repository, invoke:

```text
/supervised-task
```

The command selects the `orchestrator` profile in the current session, not an
OpenCode subagent. It asks for a GitHub issue number/URL or a feature description,
confirms the base ref, environment mode, and local-commit permission, then shows
a plan for approval. It does not create worktrees or start workers/containers
until you explicitly approve that plan.

You can prefill the task:

```text
/supervised-task 123
/supervised-task https://github.com/netz98/n98-magerun2/issues/123
/supervised-task Add a cache report with JSON output
```

The issue identifiers above are examples, not recommended tickets. Bare numbers
are resolved against the actual repository remotes; ambiguous fork/upstream
identities trigger a question. Already supplied answers are not asked again.
The command reads an existing issue; unlike `/issue`, it does not create one.

Choose isolated DDEV runtimes for a full implementation-and-test run. Code-only
mode skips provisioning and cannot yield PASS when required runtime checks are
missing. Local task commits enable immutable QA handoff; permission to create
them is separate from permission to push or open a PR.

Commit the agent profiles before launching worktrees from the chosen base.
The command is a guided prompt workflow, not a sandbox or an automatic grant of
tool permissions. Your ordinary permission prompts still apply. Invoking it
outside Herdr stops with instructions rather than controlling another session.

## Start the Orchestrator manually

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

The steps below describe the current agent profiles, which use a separate QA
branch and cherry-picked commits. See [Branch and worktree strategy](#branch-and-worktree-strategy)
for the recommended simpler handoff and its implementation status.

1. Checks Herdr, repository state, acceptance criteria, and commit permissions.
2. Creates two worktrees using `herdr worktree create --no-focus` with explicit
   branches, paths, and a recorded base SHA. Herdr opens linked workspaces with
   shell panes; each worker runs in its worktree's pane.
3. Parses returned JSON/live state for the actual pane IDs and starts workers:

   ```sh
    herdr agent start dev-<run> --kind opencode --pane <dev-pane-id> -- mini --agent developer --model '<resolved-developer-model>'
    herdr agent start qa-<run> --kind opencode --pane <qa-pane-id> -- mini --agent qa --model '<resolved-qa-model>'
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

## Subscription account and model reuse

By default, both workers reuse your existing OpenCode subscription sign-in and
the Orchestrator's selected provider/model/variant. No API key is required by
this workflow, and no credentials are copied into worktrees or DDEV containers.

Account reuse and model selection are separate:

- OpenCode connections belong to its server/authentication context, not the Git
  branch. Workers run on the same host and OS user with the same OpenCode context.
- Each Herdr worker is a new primary session, so it does not automatically inherit
  the parent's model. The Orchestrator resolves the active selector and explicitly
  passes `--model` at worker startup. No model is pinned in the agent profiles.
- If session metadata cannot establish that selector, the Orchestrator asks for
  the exact ID from `/models`; it does not guess. Explicit worker overrides are
  supported but must use the existing connection unless you approve otherwise.
- The provider ID matters: the same underlying model through a gateway or another
  provider may have different authentication and billing. Project configuration
  may also affect availability and routing. Startup must verify the intended
  provider/model and subscription connection, or stop for your confirmation.
- Workers must not silently switch to API-key billing or another account when
  subscription access fails. Concurrent sessions share subscription usage limits.

The simplest way to preserve the model you are already using is to run
`/supervised-task` in that existing session. Starting
`opencode mini --agent orchestrator` creates a separate session and does not copy
the current conversation's model; use `--model '<provider/model>'` when launching
manually if you want an explicit choice.

This is an agent instruction and launch policy, not a billing enforcement layer.
No live worker/account verification has occurred just by installing these files.

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

An illustrative task inventory for the current separate-branch workflow looks
like this; actual IDs come from Herdr:

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

## Branch and worktree strategy

**Separate worktrees protect the QA snapshot; a separate QA branch is optional.**
A branch is a movable Git reference, while a worktree is a directory containing
checked-out files. Neither provides database or service isolation by itself.

### Why not use the same branch and checkout?

| Setup | Benefit | Trade-off |
| --- | --- | --- |
| Same branch and same worktree | Simple; QA immediately sees Developer's changes | Files can change during review or a test run |
| Same branch in separate worktrees | Appears to offer independent directories | Git normally prevents the same branch from being checked out in multiple worktrees; do not bypass that guard |
| Separate worktrees at independent refs | QA tests a stable snapshot while Developer continues | Requires an explicit candidate handoff |

For example, Developer finishes candidate A and QA starts testing it. If
Developer begins B in the same directory, QA may test a mixture of A and B.
With separate worktrees, QA's files stay at A even as the feature branch advances.
Separate worktrees also keep QA's generated files and test experiments out of
Developer's working directory.

### Recommended: one feature branch, QA at the exact candidate commit

For review and testing without committed QA changes, prefer this model:

```text
Developer worktree: feature/issue-123
                       |
                       +-- candidate commit abc123
                                      |
                                      v
QA worktree: detached HEAD at abc123
```

The handoff should work as follows:

1. Developer commits a candidate on the issue's feature/bugfix branch and reports
   its exact SHA. Do not amend or rewrite a candidate already handed to QA.
2. The Orchestrator records that SHA and prepares a separate QA worktree at
   **that same commit**, rather than copying changes or cherry-picking them.
3. QA verifies that its HEAD equals the candidate SHA, runs checks in its own
   environment, and ties its verdict to that SHA. Detached HEAD is intentional:
   QA does not need a writable branch just to inspect and test the candidate.
4. Developer can continue independently. A new candidate requires another QA
   round; an earlier PASS does not cover a later branch tip.
5. Before moving QA to a new candidate, wait for its active checks to finish and
   inspect its status. Preserve or explicitly resolve QA-local changes; never
   reset or discard them merely to advance the snapshot.

This avoids duplicate implementation commits, differing cherry-picked SHAs,
and cherry-pick conflicts. A dedicated QA branch pointing directly at the
candidate commit is also valid when the tooling requires a named branch; it
does not need to recreate the candidate's commits.

If QA is explicitly assigned to commit regression tests, create a dedicated QA
branch from the tested candidate before committing. Report the original
candidate verdict and test-only commits separately. The Orchestrator coordinates
their integration into the feature branch, followed by verification of the new
combined candidate. QA must not silently fix production code.

**Implementation status:** this is the recommended strategy, not yet the behavior
encoded in the agent profiles. The current Orchestrator/QA instructions still
require a separate QA branch and cherry-pick handoff. Adopting detached QA needs
a coordinated update to those profiles and verification of the installed Herdr
worktree capabilities. Do not assume Herdr supports an unverified detached-HEAD
creation flag, bypass Herdr to create worktrees, or silently mix both workflows.

### Simpler alternative: sequential QA in one worktree

For a small supervised task, sharing one worktree is reasonable if the plan
explicitly trades parallel verification for lower setup cost:

1. Developer finishes, commits, and stops editing.
2. QA checks the candidate SHA and tests in that same worktree.
3. Developer resumes only after QA has finished and handed back ownership.

This avoids a second runtime environment, but requires exclusive ownership
during QA. Do not run concurrent edits or processes that change the tested state.
It is an alternative requiring an explicit workflow/profile change, not the
current default. Regardless of strategy, never treat branches as isolation for
Magento databases, DDEV services, ports, or other mutable external resources.

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
