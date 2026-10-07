---
name: db-compatibility-refresh
description: Refresh the n98-magerun2 database compatibility dataset at res/db-compatibility.json, the data behind the `db:compatibility` command, using the deterministic magento.watch importer plus targeted manual research for what it doesn't cover. Use this whenever the user asks to refresh, update, verify, re-check, or add entries to res/db-compatibility.json or the db:compatibility dataset; mentions a new Magento Open Source / Adobe Commerce / Mage-OS release, a new MySQL or MariaDB version, or asks things like "is MariaDB 11.8 supported yet", "check if our compatibility data is still current", or "add support info for Adobe Commerce Cloud". Always use this skill instead of hand-editing res/db-compatibility.json directly - most of the file is machine-generated from a live API and should stay that way.
---

# db:compatibility dataset refresh

## Why this exists

`res/db-compatibility.json` is what the `db:compatibility` command uses to tell a developer whether
their Magento/Adobe Commerce/Mage-OS + MySQL/MariaDB combination is officially supported. People will
make real upgrade decisions based on it, so every fact in it has to be real.

The bulk of the file (the `mw-`-prefixed `rules[]`/`applications[]`/`sources[]` entries) is generated
by `scripts/import-db-compatibility-dataset.php` from **magento.watch** (https://magento.watch/api),
an independent, open, no-auth community API that tracks exact per-release MySQL/MariaDB requirements
for `magento-community`, `magento-commerce`, and `mage-os`. That's a deterministic transformation, not
something an LLM should retype by hand - the dataset was originally hand-authored from training-data
recollection instead of a live source, and that produced real errors (confirmed by fetching the actual
data: e.g. 2.4.9 was recorded as supporting MariaDB 10.6/10.11/11.4 when it actually requires 11.8/12.3).
**So step 1, almost always, is just running the importer** - see below.

A small remainder of the file is still hand-authored, because magento.watch doesn't cover it: explicit
"this old version is unsupported" rules, the MySQL→MariaDB `migrations[]` guidance, and the
Adobe Commerce Cloud context (modeled as "unknown" since Cloud's managed database setup isn't something
this API describes). The importer never touches these - anything without an `mw-` id prefix is yours to
maintain, and the same rule applies to it: **every fact must trace back to something you actually
fetched, with a `verifiedAt` date that is genuinely today** - never inferred, never recalled from memory.

## The files involved

| File | Role |
|---|---|
| `res/db-compatibility.json` | The dataset. Most of it is machine-generated (see below); a small hand-authored remainder covers what the importer can't. |
| `res/db-compatibility.schema.json` | The JSON Schema documenting its structure. |
| `scripts/import-db-compatibility-dataset.php` | **Primary tool.** Fetches magento.watch, regenerates every `mw-`-prefixed entry, validates, writes the file. See Step 1. |
| `scripts/validate-db-compatibility-dataset.php` | Schema validator CLI, used standalone for hand-authored edits (the importer already runs it internally). |
| `src/N98/Util/Compatibility/Import/MagentoWatchDatasetImporter.php` | The transformation logic, if you need to understand exactly how it decides rule ids, merges community/enterprise, etc. |
| `src/N98/Util/Compatibility/RuleEvaluator.php` | How `applications[]` + `rules[]` become a supported/unsupported/unknown verdict - worth a skim if a change isn't producing the result you expect. |

## Workflow

### 1. Run the importer

```bash
php scripts/import-db-compatibility-dataset.php
```

This fetches all three distributions from magento.watch, regenerates every entry it owns (ids prefixed
`mw-`), validates the result against the schema, and writes `res/db-compatibility.json` - printing a
summary of what was added/updated/removed. It's always safe to run: it never touches hand-authored
entries (different id shape), and it's idempotent (running it again with nothing new upstream changes
nothing). Use `--dry-run` first if you want to see the summary without writing, and `--distributions=`
to scope it to just one product if that's all the user asked about.

This alone covers the overwhelming majority of "is version X's DB support current" requests. If that's
all the user needs, you're basically done - go straight to Step 3.

### 2. Handle what the importer doesn't cover

Only needed when the user's request touches something magento.watch doesn't model:

- **Explicit "known unsupported" versions** (e.g. "MySQL 5.7 doesn't work with 2.4.9") - magento.watch
  only publishes positive support lists, so absence of a version there means *unknown*, not
  *unsupported*, per `RuleEvaluator`'s design. If you have an authoritative source stating a version is
  explicitly rejected, add a hand-authored `rules[]` entry for it (id without the `mw-` prefix, so the
  importer leaves it alone) citing that source.
- **Migration guidance** (`migrations[]`) - not something magento.watch provides. If asked to add/update
  it, cite a real fetched source (e.g. MariaDB's own migration docs).
- **Cloud/managed-database contexts** - the existing `magento-enterprise-cloud-2.4` application scope
  deliberately reports "unknown" rather than reusing on-premise rules. Only change this with real
  Adobe Commerce Cloud-specific sourcing.
- **magento.watch itself looks wrong or is down** - fetch the primary source directly (Adobe's system
  requirements docs, or the relevant project's release notes) and say so explicitly; don't silently fall
  back to guessing.

For any of these, the same rules as before apply: fetch the real source, cite it with a real `url` and
today's `verifiedAt`, and leave something out (or leave it "unknown") rather than infer it. See
`res/db-compatibility.schema.json` for the exact shape, and skim a few existing hand-authored entries
(anything without an `mw-` id) in `res/db-compatibility.json` for the established conventions - patch
version ranges via `Version::normalize()` (`2.4.8-p1` → `2.4.8.1`), `contexts: ["on-premise"]` unless
Cloud-specific, stable kebab-ish rule ids.

### 3. Validate and test

```bash
php scripts/validate-db-compatibility-dataset.php
vendor/bin/phpunit -c phpunit.xml.dist tests/N98/Util/Compatibility
```

The importer already validates before writing, but re-run the validator if you hand-edited anything
afterward. The test suite exercises `RuleEvaluator`/importer logic, not your specific data, but catches
structural regressions. A real smoke test, if a Magento/ddev fixture is available (see `AGENTS.md`):

```bash
ddev exec "cd /var/www/html && php bin/n98-magerun2 --root-dir=<path> db:compatibility --target-version=<version> --target-db=<family> --target-db-version=<version>"
```

### 4. Summarize for the user

The importer already prints added/updated/removed rule ids - lead with that. Add anything you did by
hand in Step 2, with its source and citation. Note anything you looked for but genuinely couldn't
confirm. This is a normal file change - the user reviews it via `git diff` like anything else. Don't
create a CHANGELOG.md entry unless asked.

## Hard constraints (Step 2 / hand-authored entries only)

- Never write a `verifiedAt` date without having fetched that exact URL in this session.
- Never write a `rules[]` entry for a specific database version you didn't find explicitly addressed in
  a fetched source.
- Never flip `applications[].exhaustive` to `true` for a scope unless the source actually presents a
  closed, complete list of supported database families for it.
- Never touch `res/db-compatibility.schema.json` or `src/N98/Util/Compatibility/Import/` as part of a
  routine refresh - if magento.watch's response shape has changed enough that the importer breaks, or
  the data genuinely doesn't fit the current schema, stop and discuss it with the user rather than
  improvising a fix.
