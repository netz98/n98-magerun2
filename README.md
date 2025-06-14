# n98-magerun2: The Swiss Army Knife for Magento 2

![n98-magerun Logo](.github/doc/magerun-logo.png)

n98-magerun2 provides powerful CLI tools for Magento 2, Mage-OS, and Adobe Commerce.

- **Official Documentation:** [https://netz98.github.io/n98-magerun2/](https://netz98.github.io/n98-magerun2/)
- **Development Branch:** `develop`
- **Supported Magento:** Magento 2 only ([Magento 1/OpenMage tool here](https://github.com/netz98/n98-magerun))

## Quick Start

### Download the PHAR file

```
curl -sS -O https://files.magerun.net/n98-magerun2-latest.phar
curl -sS -o n98-magerun2-latest.phar.sha256 https://files.magerun.net/sha256.php?file=n98-magerun2-latest.phar
shasum -a 256 -c n98-magerun2-latest.phar.sha256
```

### Install dist package

The dist package installs the n98-magerun2 PHAR file directly in your project.

```sh
composer require netz98/n98-magerun2-dist
```
## Run the PHAR file

You can run the PHAR file directly from the command line:

```bash
./n98-magerun2.phar
```

## Build from source

1. **Clone the repository:**
   ```bash
   git clone https://github.com/netz98/n98-magerun2.git
   cd n98-magerun2
   ```
2. **Install dependencies:**
   ```bash
   composer install
   ```
3. **Build the PHAR:**
   ```bash
   ./build.sh
   ```

## Full Documentation

For full installation, usage, development, and contribution guidelines, please visit the [official documentation](https://netz98.github.io/n98-magerun2/).

# n98-magerun2

n98-magerun2 is a powerful CLI tool for Magento 2 developers and administrators. It provides a wide range of commands to simplify and automate common Magento 2 tasks.

## Features
- Manage cache, indexes, and configuration
- Run scripts and commands
- Manage customers, products, and more
- Integration with Magento 2 core and extensions

## Getting Started

1. Download the latest PHAR release or clone the repository
2. Run `php n98-magerun2.phar list` to see available commands
3. See the [Development Guidelines](./DEVELOPMENT.md) for contributing

## License

MIT License. See [MIT-LICENSE.txt](../MIT-LICENSE.txt).
