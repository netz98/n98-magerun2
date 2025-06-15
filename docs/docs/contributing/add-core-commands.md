---
title: Add Core Commands
---


1. Create a new command class in the appropriate namespace under `src/N98/Magento/Command/`
2. Extend the `AbstractMagentoCommand` class
3. Implement the `configure()` and `execute()` methods
4. Add appropriate tests (Unit-Test and bats) for your command
5. In the `docs/docs/command-reference/` directory, add an entry for your command category (e.g., `cache/index.md`) that lists the command names and links to their dedicated documentation files. Each command should have its own detailed documentation file (see below, for example).
