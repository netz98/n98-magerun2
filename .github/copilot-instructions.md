# n98-magerun2 Development Guidelines

This document provides essential information for developers working on the n98-magerun2 project.

## Build/Configuration Instructions

### Prerequisites

- PHP 8.1 or higher
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
5. In the `docs/docs/command-reference/` directory, add an entry for your command category (e.g., `cache/index.md`) that lists the command names and links to their dedicated documentation files. Each command should have its own detailed documentation file (see below for example).

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

---

## Docusaurus Documentation

The project uses [Docusaurus](https://docusaurus.io/) for documentation. All documentation sources are located in the `docs/` directory.

### Structure
- `docs/docs/` – Main documentation content (Markdown files)
- `docs/docs/command-reference/` – Command reference documentation
- `docs/docusaurus.config.js` – Docusaurus configuration
- `docs/sidebars.js` – Sidebar navigation structure
- `docs/static/` – Static assets (images, etc.)

### Editing Documentation
- Edit or add Markdown files in the appropriate subdirectory under `docs/docs/`.
- Follow the existing structure and naming conventions.
- For new commands, add documentation in `docs/docs/command-reference/`.

### Local Preview & Build
To preview documentation locally:
```bash
devd ssh
cd docs
npm install
npm run start
```
This will start a local server (usually at http://localhost:3000) for live preview.

To build the static site:
```bash
npm run build
```
The output will be in `docs/build/`.

### Deployment
Documentation is deployed automatically via CI/CD on changes to the `main` branch. For manual deployment or troubleshooting, refer to the Docusaurus documentation or project-specific CI scripts.

### Debugging Tips

- Use the `--debug` flag when running commands to see detailed debug information
- For development, you can run the commands directly with PHP: `php bin/n98-magerun2 command`
- Add logging to your commands with `$this->getOutput()->writeln()`

### Build a phar file

You can build the phar file "/var/www/html/n98-magerun2.phar" in the ddev Docker web container" with the following command:

```bash
ddev exec ./build.sh
```
