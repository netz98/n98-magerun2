---
title: dev:flush
---

# dev:flush

Clean integration test sandboxes beneath the detected Magento root.

```bash
n98-magerun2 dev:flush
n98-magerun2 dev:flush --force
```

By default, this removes the `var` directory (including hidden files and nested
directories) from each immediate `dev/tests/integration/tmp/sandbox-*` directory.
The sandbox itself and files outside its `var` directory are retained. Missing
`var` directories, a missing integration test tree, and no matching sandboxes
are successful no-ops. Other entries in `tmp` and Magento's own `var` are untouched.

| Option | Description |
| --- | --- |
| `--force` | Remove each entire matching sandbox instead of only its `var` directory. |

:::warning

Stop integration tests before cleanup. `--force` permanently deletes sandbox
contents without a confirmation prompt, including with `--no-interaction`.
This command does not reset or drop integration test databases.

:::

Symbolic links in the path from the Magento root to integration `tmp`, matching
sandbox links, and a linked `var` deletion target are refused with a nonzero exit
status. Links inside a directory being deleted are removed without following
their targets. In force mode, a nested `var` link is likewise only unlinked.
Invalid directory boundaries and filesystem errors return a nonzero status;
independent sandboxes are still processed where possible. Successful removals
are reported. Do not run while another process changes the sandbox tree.
