---
title: Unit Tests / Integration Tests
---

## Test Environment Setup

:::note
the tests require a Magento 2 installation. Set the `N98_MAGERUN2_TEST_MAGENTO_ROOT` environment variable to point to your Magento installation. This is necessary to run the tests against a real Magento instance. Such cases act as integration tests, ensuring that the commands work correctly with Magento's core functionality.

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

## Running Unit/Integration Tests

The project uses PHPUnit for unit and integration tests.

To run all PHPUnit tests:
```bash
vendor/bin/phpunit
```

Example (run a specific test):
```bash
vendor/bin/phpunit tests/N98/Util/Console/Helper/DatabaseHelperTest.php
```

:::tip
ddev users can run the `ddev unit-test-24` command.
`ddev unit-test-24 tests/N98/Util/Console/Helper/DatabaseHelperTest.php`
:::

## Adding New Unit/Integration Tests

- Create a new test class in the `tests/N98/Magento/Command/` directory
- Extend the appropriate TestCase class
- Follow the existing test patterns (most command tests use `testExecute()` method)

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

## Testing `dev:console` Commands

Commands executed inside the interactive `dev:console` use their own base test
case. Instead of extending `N98\\Magento\\Command\\TestCase`, these tests must
extend `N98\\Magento\\Command\\Developer\\Console\\TestCase` which prepares
the REPL context.

```php
<?php
namespace N98\Magento\Command\Developer\Console;

class MakeCommandCommandTest extends TestCase
{
    public function testExecute()
    {
        $command = new MakeCommandCommand();
        $tester = $this->createCommandTester($command);

        $tester->execute(['classpath' => 'foo.bar.baz']);
        // add assertions here
    }
}
```

See the files in `tests/N98/Magento/Command/Developer/Console/` for more
examples of testing commands that run within the `dev:console` environment.
