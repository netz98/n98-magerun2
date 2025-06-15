---
title: Test Setup
---

### Test Environment Setup

:::note
The tests require a Magento 2 installation. Set the `N98_MAGERUN2_TEST_MAGENTO_ROOT` environment variable to point to your Magento installation. This is necessary to run the tests against a real Magento instance. Such cases act as integration tests, ensuring that the commands work correctly with Magento's core functionality.

If the environment variable is not set, all tests that call a Magento initialization **will be skipped** during runtime.
:::

1. **Set Up Magento**:
    - Install a compatible version of Magento 2 (see README.md for supported versions)
    - Configure the database and other required services

    :::tip
    In the ddev environment, a Magento 2 installation is already set up. You can use the provided `N98_MAGERUN2_TEST_MAGENTO_ROOT` path to point to the Magento installation.
    :::

2. **Configure Test Environment**:
   ```bash
   export N98_MAGERUN2_TEST_MAGENTO_ROOT=/path/to/your/magento/installation
   ```

### Running Tests

The project uses PHPUnit for unit tests and BATS for functional tests.

1. **Running PHPUnit Tests**:
   ```bash
   vendor/bin/phpunit
   ```

2. **Running Functional Tests with BATS**:
   ```bash
   export N98_MAGERUN2_BIN=/path/to/n98-magerun2.phar
   bats tests/bats/functional_magerun_commands.bats
   bats tests/bats/functional_core_commands.bats
   ```

Example: 

`vendor/bin/phpunit tests/N98/Util/Console/Helper/DatabaseHelperTest.php`

:::tip
ddev users can run the `ddev unit-test-24` command.
`ddev unit-test-24 tests/N98/Util/Console/Helper/DatabaseHelperTest.php`
:::


### Adding New Tests

1. **Unit Tests**:
    - Create a new test class in the `tests/N98/Magento/Command/` directory
    - Extend the appropriate TestCase class
    - Follow the existing test patterns (most command tests use `testExecute()` method)

2. **Functional Tests**:
    - Add new test cases to the BATS files in `tests/bats/` directory
    - Follow the existing patterns for command testing

### Example Test

Here's a simple example of a command test:

```php
<?php
namespace N98\Magento\Command\YourNamespace;

use N98\Magento\Command\TestCase;

class YourCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('your:command', 'Expected output text');
    }
}
```

## Additional Development Information

### Code Style

:::note
The project uses PHP-CS-Fixer for code style (`.php-cs-fixer.php`) and PHPStan for static analysis (`phpstan.neon.dist`).
:::

To check code style:
```bash
vendor/bin/php-cs-fixer fix --dry-run
```

To fix code style issues:
```bash
vendor/bin/php-cs-fixer fix
```

### Static Analysis

To run static analysis:
```bash
vendor/bin/phpstan analyse
```
