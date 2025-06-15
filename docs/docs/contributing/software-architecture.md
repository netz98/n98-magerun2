---
title: Software Architecture
---

This document provides an overview of the software architecture of the n98-magerun2 project.

:::note
   This document is a work in progress.
:::

## Project Structure

- `src/N98/Magento/Command/`: Contains all the commands
- `src/N98/Util/`: More general utility classes
- `src/N98/Magento/Application.php`: Main application class
- `config.yaml`: Configuration for commands and other settings
- `tests/N98`: Unit Test classes to cover src/N98 classes
- `tests/bats`: Functional tests using BATS (Bash Automated Testing System)
- `res/`: Resources like autocompletion files
- `bin/`: Executable scripts
