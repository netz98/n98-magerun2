---
id: intro
sidebar_position: 1
title: Introduction
slug: /
---

# Welcome to n98-magerun2!

Welcome! **n98-magerun2** is the essential command-line companion for anyone working with **Adobe Commerce**, **Magento**, or **Mage-OS**. It simplifies complex tasks, automates repetitive work, and gives you powerful control over your e-commerce platform directly from the terminal.

> It's famously known as **the Swiss Army knife** for Magento developers, system administrators, and DevOps professionals.

This version of the tool is specifically for modern Magento-based platforms. If you are working with the classic Magento 1 or OpenMage, please use the original [n98-magerun](httpss://github.com/netz98/n98-magerun).

---

## ‍👥 For Everyone: What does n98-magerun2 do?

Think of n98-magerun2 as a powerful assistant. Many routine maintenance and development tasks for an e-commerce store require navigating through complex admin panels or writing custom scripts. This tool bundles hundreds of these operations into simple, repeatable commands.

For a store owner or project manager, this means your development team can work faster and more efficiently, saving time and reducing errors on critical tasks.

---

## 📖 User Guide

See the [Command Documentation](command-docs/) for a full list of available commands and detailed usage instructions.

---

## ⚙️ For System Administrators & DevOps

Automate, deploy, and manage your infrastructure with confidence. Magerun is built for scripting and provides powerful tools for system-level tasks.

* **Easy Installation:** Install the tool system-wide or per-project.
* **Scripting & Automation:** Integrate magerun commands seamlessly into your deployment and CI/CD pipelines.
* **System Inspection:** Quickly check the status of caches, indexers, and other system requirements.
* **Hosting-Specific Commands:** Benefit from commands tailored for various hosting environments.

Get your infrastructure set up:
* **Check out our [Hosting & Infrastructure Guide](./hosting/)**
* **See the list of [supportive hosting companies](https://magerun.net/testimonials/providers/)**

---

## 👩‍💻 For Developers

Stop clicking through the admin and speed up your daily workflow. Magerun is your go-to tool for common development tasks.

* **Cache & Index Management:** Instantly clean caches or reindex your entire store with a single command.
* **Database Access:** Run direct SQL queries or manage database dumps without needing a separate client.
* **Code Scaffolding:** Quickly create new modules, plugins, or controllers from templates.
* **Admin & Customer Management:** Create admin users or inspect customer data directly from the command line.
* **Configuration:** Easily change system configuration values without searching through the admin panel.

Ready to supercharge your development?
* **Explore the [GitHub Repository](https://github.com/netz98/n98-magerun2)**
* **Learn how to [build your own commands](./extending/)**

---

## Ready to Get Started?

Before you begin, please review our **[Compatibility Page](./compatibility.md)** to ensure the tool supports your versions of PHP and Magento.

When you're ready, head over to the installation instructions to add n98-magerun2 to your project!

---

## 🚀 Quick Start

Ready to get n98-magerun2 up and running?

1.  **Review Compatibility**: Check the [Compatibility Page](./compatibility.md) to ensure it works with your Magento and PHP versions.
2.  **Install**: Follow our [Installation Guide](./installation.md) to add n98-magerun2 to your system or project.
3.  **Explore Commands**: Once installed, run `n98-magerun2 list` to see all available commands, or browse the [Command Documentation](./command-docs/).

This will get you started quickly with the most important first steps.

---

# n98-magerun2 Development Guidelines

This document provides essential information for developers working on the n98-magerun2 project.

## Build/Configuration Instructions

### Prerequisites

:::info
**Prerequisites:**
- PHP 8.1 or higher
- Composer
- Git
- Curl
- For testing: A Magento 2 installation
:::

### Setting Up the Development Environment

:::tip
Run all commands inside the ddev container. Use `ddev ssh` to enter the container.
:::

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

:::note
The tests require a Magento 2 installation. Set the `N98_MAGERUN2_TEST_MAGENTO_ROOT` environment variable to point to your Magento installation.
:::

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

### Adding New Commands

1. Create a new command class in the appropriate namespace under `src/N98/Magento/Command/`
2. Extend the `AbstractMagentoCommand` class
3. Implement the `configure()` and `execute()` methods
4. Add appropriate tests (Unit-Test and bats) for your command
5. In the `docs/docs/command-reference/` directory, add an entry for your command category (e.g., `cache/index.md`) that lists the command names and links to their dedicated documentation files. Each command should have its own detailed documentation file (see below for example).

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

:::note
**Debugging Tips:**
- Use the `--debug` flag for detailed output.
- For development, run commands directly: `php bin/n98-magerun2 command`
- Add logging with `$this->getOutput()->writeln()`
:::

### Build a phar file

:::tip
You can build the phar file `/var/www/html/n98-magerun2.phar` in the ddev Docker web container with:
```bash
ddev exec ./build.sh
```
:::
