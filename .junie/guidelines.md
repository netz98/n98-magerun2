# n98-magerun2 Development Guidelines

This document provides essential information for developers working on the n98-magerun2 project.

## Build/Configuration Instructions

### Prerequisites

- PHP 8.0 or higher (PHP 8.1+ recommended)
- Composer
- Git
- Curl
- For testing: A Magento 2 installation

### Setting Up the Development Environment

Run every of the command in the ddev container. You can use `ddev ssh` to enter the container.

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/netz98/n98-magerun2.git
   cd n98-magerun2
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   ```

3. **Building the PHAR File**:
   The project includes a build script that creates the n98-magerun2.phar file:
   ```bash
   ./build.sh
   ```
   
   This script:
   - Checks for required dependencies
   - Downloads the box.phar tool if needed
   - Configures Composer for reproducible builds
   - Compiles the PHAR file
   - Sets the timestamp to the last commit time for reproducible builds
   - Verifies the PHAR signature
   - Makes the PHAR executable

## Testing Information

### Test Environment Setup

The tests require a Magento 2 installation to run properly. The test suite is configured to use the Magento installation specified by the `N98_MAGERUN2_TEST_MAGENTO_ROOT` environment variable.

1. **Set Up Magento**:
   - Install a compatible version of Magento 2 (see README.md for supported versions)
   - Configure the database and other required services

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
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

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

### File Header

All PHP files should start with a file header that includes the copyright and license information. The header should look like this:

```php
<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */
 
 namespace N98\Magento\Command\YourNamespace;
 
```

### Code Style

The project uses PHP-CS-Fixer for code style. The configuration is in `.php-cs-fixer.php`.

To check code style:
```bash
vendor/bin/php-cs-fixer fix --dry-run
```

To fix code style issues:
```bash
vendor/bin/php-cs-fixer fix
```

### Static Analysis

The project uses PHPStan for static analysis. The configuration is in `phpstan.neon.dist`.

To run static analysis:
```bash
vendor/bin/phpstan analyse
```

### Adding New Commands

1. Create a new command class in the appropriate namespace under `src/N98/Magento/Command/`
2. Extend the `AbstractMagentoCommand` class
3. Implement the `configure()` and `execute()` methods
4. Add appropriate tests (Unit-Test and bats) for your command

### Project Structure

- `src/N98/Magento/Command/`: Contains all the commands
- `src/N98/Util/`: More general utility classes
- `src/N98/Magento/Application.php`: Main application class
- `config.yaml`: Configuration for commands and other settings
- `tests/N98`: Unit Test classes to cover src/N98 classes
- `tests/bats`: Functional tests using BATS (Bash Automated Testing System)
- `res/`: Resources like autocompletion files
- `bin/`: Executable scripts

### Testing

Run unit tests with `ddev unit-test-24` command.
Example: `ddev unit-test-24 tests/N98/Util/Console/Helper/DatabaseHelperTest.php`

### Versioning

The project follows semantic versioning. Version information is stored in `version.txt`.

### Release Process

1. Update the CHANGELOG.md file
2. Update version.txt with the new version number
3. Tag the release in Git
4. Build the PHAR file
5. Upload the PHAR file to the file server

### Debugging Tips

- Use the `--debug` flag when running commands to see detailed debug information
- For development, you can run the commands directly with PHP: `php bin/n98-magerun2 command`
- Add logging to your commands with `$this->getOutput()->writeln()`

### Build a phar file

You can build the phar file "/var/www/html/n98-magerun2.phar" in the ddev Docker web container" with the following command:

```bash
ddev exec ./build.sh
```

