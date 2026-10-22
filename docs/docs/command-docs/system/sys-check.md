---
title: sys:check
---

# sys:check

:::info
Checks your Magento system for common issues and misconfigurations. Use this command to quickly identify potential problems in your setup.
:::

## Usage
```sh
n98-magerun2.phar sys:check
```

## Frontend full-page cache declarations

The `CACHE` group reports an **error** for each `<block cacheable="false">`
declaration in a frontend `default.xml` layout. The XML Schema equivalent
`cacheable="0"` (including surrounding whitespace) is also detected. Each result
identifies the file, line and block name (or marks it unnamed). XML comments,
cacheable blocks, page-specific handles and adminhtml layouts are not checked.
An uncacheable block in the default handle can disable full-page caching across
the storefront; restrict intentionally uncacheable blocks to appropriate handles.

Discovery uses Magento's registered component paths, including Composer-installed
components outside `app/code` and `app/design`. It scans enabled modules'
`view/frontend/layout/default.xml` and `view/base/layout/default.xml` (base layouts
also apply to the frontend), plus enabled modules' layout directories in **all
registered frontend themes**, including base and parent-theme layout overrides.
Disabled modules and registered adminhtml themes are excluded.

:::note
This is a read-only static declaration scan, not a merged-layout or request-time
cacheability test. Registered themes need not be assigned to a store. Overrides,
removed blocks, conditional configuration, theme inheritance and runtime changes
are not resolved, so a reported declaration may not affect a particular page.
Conversely, page-specific handles, included handles and PHP code can affect cacheability
without being reported here. Invalid XML or documents with a DOCTYPE produce a
warning rather than a clean result. No files, configuration or caches are changed.
:::

As with other `sys:check` findings, error results do **not** change the command's
exit status after a successful Magento bootstrap. Use `--format=json` or another
supported table format to inspect individual result statuses in automation.
