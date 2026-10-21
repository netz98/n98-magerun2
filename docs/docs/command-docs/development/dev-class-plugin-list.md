---
title: dev:class:plugin:list
---

# dev:class:plugin:list

Lists plugins declared for a class or interface, optionally restricted to one public method.

```bash
n98-magerun2.phar dev:class:plugin:list 'Magento\Catalog\Model\Product'
n98-magerun2.phar dev:class:plugin:list 'Magento\Catalog\Model\Product::getName()'
```

Quote the argument to preserve namespace separators and parentheses in your shell.

The table shows plugin name, configured plugin class, observed method, interception
type (`before`, `around`, `after`), configured sort order (default `0`), and status
(`active` or `disabled`). A plugin implementing multiple interception methods has
one row per method and type. Parent-class and interface declarations are inherited;
more specific declarations override the same plugin name, including disabled state.

Rows are sorted by ascending configured sort order, then plugin name, observed
method, type, and class for reproducible ties. This is a configuration listing,
not an execution trace: around/after nesting may differ from this display order.

:::note

Reads merged primary and global DI XML from enabled modules, retaining disabled
entries. Area-specific declarations (such as frontend or adminhtml) are not included.
Plugins are reflected, not instantiated. A declaration without an available plugin
class is shown with `-` for method and type in class-wide output; its methods cannot
be matched in method-specific output. `active` means not disabled in configuration,
not a guarantee that a particular runtime call can be intercepted.

:::

Unknown classes, malformed targets, and missing or non-interceptable methods return
a nonzero exit code. A valid target with no matching plugins prints `No plugins found.`
and succeeds. No optional filtering flags are provided.
