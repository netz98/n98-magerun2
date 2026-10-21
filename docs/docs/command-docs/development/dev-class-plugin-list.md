---
title: dev:class:plugin:list
---

# dev:class:plugin:list

Lists plugins declared for a class or interface, optionally restricted to one public method.

```bash
n98-magerun2.phar dev:class:plugin:list 'Magento\Catalog\Model\Product'
n98-magerun2.phar dev:class:plugin:list 'Magento\Catalog\Model\Product::getName()'
n98-magerun2.phar dev:class:plugin:list 'Magento\Framework\App\FrontController' --area=frontend
```

Quote the argument to preserve namespace separators and parentheses in your shell.

## Options

`--area=AREA` selects the configuration area, defaulting to `global`. Accepted
values are `global`, `adminhtml`, `frontend`, `crontab`, `webapi_rest`, `webapi_soap`,
`graphql`, `doc`, and the legacy `admin` area. Invalid values return a nonzero exit code.
Primary and global XML are always merged first, followed by the selected area's
XML. Thus area declarations can add plugins or override global configuration.

The table shows plugin name, configured plugin class, observed method, interception
type (`before`, `around`, `after`), configured sort order (default `0`), and status
(`active` or `disabled`). A plugin implementing multiple interception methods has
one row per method and type. Parent-class and interface declarations are inherited;
more specific declarations override the same plugin name, including disabled state.
Undeclared references (without an instance after inheritance within that relation)
are discarded before merging the next relation, following Magento's processing.
Public static plugin interception methods are included; static subject methods
remain non-interceptable.

Rows are sorted by ascending configured sort order, then plugin name, observed
method, type, and class for reproducible ties. This is a configuration listing,
not an execution trace: around/after nesting may differ from this display order.

:::note

Reads merged DI XML from enabled modules, retaining declared disabled entries.
Area-specific declarations are included only for the selected area.
Plugins are reflected, not instantiated. A declaration without an available plugin
class is shown with `-` for method and type in class-wide output; its methods cannot
be matched in method-specific output. `active` means not disabled in configuration,
not a guarantee that a particular runtime call can be intercepted.

:::

Unknown classes, malformed targets, and missing or non-interceptable methods return
a nonzero exit code. A valid target with no matching plugins prints `No plugins found.`
and succeeds. No active-status, interception-type, or method-filter flags are provided.
