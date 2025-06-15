---
title: Functional Tests
---

Functional tests in this project are implemented using [BATS](https://github.com/bats-core/bats-core) (Bash Automated Testing System). These tests ensure that the CLI commands work as expected in a real shell environment, often interacting with a Magento 2 instance.

:::tip
- Use the ddev environment for a pre-configured setup.
- Ensure your Magento test environment is in a clean state before running destructive tests.
- Review existing tests for best practices and patterns.
  :::

## Prerequisites

- A working Magento 2 installation (see [Test Environment Setup](./unit-tests.md))
- BATS installed (pre-installed in the ddev environment)
- The `n98-magerun2.phar` file built and available

## Test Environment Setup

If you are using the ddev environment, most requirements are already set up. Otherwise, ensure the following environment variables are set:

```bash
export N98_MAGERUN2_TEST_MAGENTO_ROOT=/path/to/your/magento/installation
export N98_MAGERUN2_BIN=/path/to/n98-magerun2.phar
```

## Running Functional Tests

To run all functional tests:

```bash
ddev ssh
bats tests/bats/functional_magerun_commands.bats
bats tests/bats/functional_core_commands.bats
```

You can also run only specific tests by using the `--filter` option. For example, to run only tests related to the `admin:user:list` command:

```bash
bats --filter "admin:user:list" tests/bats/functional_magerun_commands.bats
```

## Structure of Functional Tests

- All BATS test files are located in the `tests/bats/` directory.
- There are separate files for magerun commands and Magento core proxy commands:
  - `functional_magerun_commands.bats`
  - `functional_core_commands.bats`

## The Two Main BATS Test Suites

The n98-magerun2 project provides two major BATS-based functional test suites, each targeting a different aspect of the CLI tool.

:::info
Both test suites are essential: the first ensures the reliability of n98-magerun2's custom features, while the second guarantees compatibility and correct integration with Magento's native CLI commands.
:::

### 1. n98-magerun2 Commands

- **Purpose:**
  - Tests the custom commands provided by n98-magerun2 (those implemented in the project itself).
  - Ensures that these commands work as expected in a real shell environment and interact correctly with Magento.
- **Location:**
  - `tests/bats/functional_magerun_commands.bats`
- **Typical Coverage:**
  - User management, cache operations, database commands, and other features unique to n98-magerun2.
- **Example:**
  - Verifying that `admin:user:list` returns the expected user list.

### 2. Magento Core Commands (called by n98-magerun2)

- **Purpose:**
  - Tests the proxy commands that wrap or delegate to Magento core CLI commands.
  - Ensures that n98-magerun2 correctly passes through and handles Magento's built-in commands.
- **Location:**
  - `tests/bats/functional_core_commands.bats`
- **Typical Coverage:**
  - Magento's own CLI commands, such as `setup:upgrade`, `cache:flush`, etc., as exposed through n98-magerun2.
- **Example:**
  - Verifying that running a core command via n98-magerun2 produces the same result as running it directly with Magento's CLI.

## Adding New Functional Tests

1. Add new test cases to the appropriate BATS file in `tests/bats/`.
2. Follow the existing test patterns for command testing.
3. Use descriptive test names and assertions to ensure clarity and maintainability.

## Example BATS Test

```bash
@test "admin:user:list command lists users" {
  run $N98_MAGERUN2_BIN admin:user:list
  [ "$status" -eq 0 ]
  [[ "$output" == *"admin"* ]]
}
```

---

For more information on unit and integration tests, see [Unit Tests](./unit-tests.md).
For code style and static analysis, see [Static Tests](./static-tests.md).
