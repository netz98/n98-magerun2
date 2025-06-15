---
title: Documentation
---

:::warning
For each new command, create a dedicated Markdown documentation file in the appropriate directory. This ensures detailed and organized documentation.
:::

#### Example: Documenting Command References in Docusaurus

Suppose you are adding cache-related commands. In `docs/docs/command-reference/cache/index.md`, provide a list of commands and link each to its dedicated documentation file:

```markdown
---
title: Cache Commands
sidebar_label: Cache
---

# Cache Commands

Commands for interacting with and managing Magento's various cache systems.

## Commands

- [cache:clean](../../system/cache-commands.md)
- [cache:disable](../../system/cache-commands.md)
- [cache:enable](../../system/cache-commands.md)
- [cache:flush](../../system/cache-commands.md)
- [cache:list](../../system/cache-commands.md)
```

For each command, create a separate Markdown file (e.g., `docs/docs/system/cache-commands.md`) with the full documentation for that command.

This ensures the command reference remains concise, while detailed documentation is available in dedicated files for each command.

## Generate the documentation

To generate the documentation, run the following command in the `docs` directory:

```bash
cd docs
npm install
npm run start
```

This will build the documentation site and make it available for local preview.
You can then view it at `http://localhost:3000/n98-magerun2/` or the port specified in your terminal.


## Structure of the Documentation

The project uses [Docusaurus](https://docusaurus.io/) for documentation. All documentation sources are located in the `docs/` directory.

- `docs/docs/` – Main documentation content (Markdown files)
- `docs/docs/command-reference/` – Command reference documentation
- `docs/docusaurus.config.js` – Docusaurus configuration
- `docs/sidebars.js` – Sidebar navigation structure
- `docs/static/` – Static assets (images, etc.)

