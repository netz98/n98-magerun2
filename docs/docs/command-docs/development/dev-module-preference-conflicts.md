---
title: dev:module:preference:conflicts
---

# dev:module:preference:conflicts

Find potential competing DI preferences in enabled, registered Magento modules.

```bash
n98-magerun2 dev:module:preference:conflicts
n98-magerun2 dev:module:preference:conflicts --area=frontend
n98-magerun2 dev:module:preference:conflicts --format=json
```

## Detection rules

The command reads original module `etc/di.xml` (global) and
`etc/<area>/di.xml` files, including custom area directories. It reports a target
(`for`) when more than one distinct implementation (`type`) is declared in the
**same scope**, even when both declarations are in one file.

- Repeated declarations of the same implementation alone are not conflicts.
- A global preference and an area-specific override are not compared.
- Different areas are independent: frontend and adminhtml declarations do not conflict.
- Disabled and unregistered modules, primary `app/etc/di.xml`, generated DI
  configuration, and plugins are not inspected.

:::note

These are **potential conflicts**, not proof that an installation is broken.
An intentional same-scope replacement may be reported. The command does not
resolve preference chains or infer whether an override is intentional.

:::

## Options and output

| Option | Description |
| --- | --- |
| `--area=AREA` | Inspect one declaration scope, such as `global`, `frontend`, or a custom area. By default, inspect all discovered scopes. |
| `--format=FORMAT` | Use a supported table output format, such as `json` or `csv`. |

Each reported declaration includes scope, target, implementation, module, source
file and XML line number. Results are sorted by scope, target, implementation,
module, source file and line. All contributing declarations are retained when a
group conflicts, including repeated implementations.

Plain output displays `No preference conflicts found.` when there are no findings.
Structured formats render an empty result instead (for example, JSON `{}`, following the existing table renderer).
A scope with no declarations also produces an empty result.

Both findings and no findings exit with status **0**. Invalid area names,
unreadable selected files or directories, malformed XML, DTDs, and preferences
missing `for` or `type` produce an error and a nonzero exit status. XML external
entities are not expanded.

This is an informational, read-only command. It does not repair preferences,
write configuration, clean caches or update the database. Normal Magento
bootstrap may incidentally populate framework caches or logs.
