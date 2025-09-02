# n98-magerun2: The Swiss Army Knife for Magento 2

![n98-magerun Logo](.github/doc/magerun-logo.png)

n98-magerun2 provides powerful CLI tools for Magento 2, Mage-OS, and Adobe Commerce.

- **Official Documentation:** [https://netz98.github.io/n98-magerun2/](https://netz98.github.io/n98-magerun2/)
- **Development Branch:** `develop`
- **Supported Magento:** Magento 2 only ([Magento 1/OpenMage tool here](https://github.com/netz98/n98-magerun))

## Quick Start

### Download the PHAR file

```
curl -sS -O https://files.magerun.net/n98-magerun2.phar
curl -sS -o n98-magerun2-latest.phar.sha256 https://files.magerun.net/sha256.php?file=n98-magerun2.phar
shasum -a 256 -c n98-magerun2.phar.sha256
```

### Install dist package

The dist package installs the n98-magerun2 PHAR file directly in your project.

```sh
composer require netz98/magerun2-dist
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


| Category/Namespace                                                           | Description                                                                 | Example Commands                                                                            |
|:-----------------------------------------------------------------------------| :-------------------------------------------------------------------------- |:--------------------------------------------------------------------------------------------|
| [admin](https://netz98.github.io/n98-magerun2/command-docs/admin/)           | Commands for managing Magento admin user accounts and related settings.     | `admin:user:list`, `admin:user:create`, `admin:user:change-password`, `admin:notifications` |
| [cache](https://netz98.github.io/n98-magerun2/command-docs/cache/)           | Commands for interacting with and managing Magento's various cache systems. | `cache:clean`, `cache:disable`, `cache:enable`, `cache:flush`, `cache:list`                 |
| [config](https://netz98.github.io/n98-magerun2/command-docs/config/)         | Commands for managing Magento store configurations and environment settings.  | `config:store:get`, `config:store:set`, `config:env:set`, `config:search`                   |
| [composer](https://netz98.github.io/n98-magerun2/command-docs/composer/)     | Commands for managing Composer-related tasks and package deployment.      | `composer:redeploy-base-packages`                                                           |
| [customer](https://netz98.github.io/n98-magerun2/command-docs/customer/)     | Commands for managing Magento customer accounts.                            | `customer:create`, `customer:list`, `customer:info`, `customer:change-password`             |
| [db](https://netz98.github.io/n98-magerun2/command-docs/db/)                 | Commands for database operations such as dumps, imports, and queries.       | `db:dump`, `db:import`, `db:query`, `db:create`, `db:info`                                  |
| [dev](https://netz98.github.io/n98-magerun2/command-docs/development/)       | Commands tailored for Magento developers, including code generation and debugging tools. | `dev:module:create`, `dev:console`, `dev:translate:admin`, `dev:theme:list`                 |
| [eav](https://netz98.github.io/n98-magerun2/command-docs/eav/)               | Commands for managing EAV (Entity-Attribute-Value) attributes.            | `eav:attribute:list`, `eav:attribute:view`, `eav:attribute:remove`                          |
| [giftcard](https://netz98.github.io/n98-magerun2/command-docs/giftcard/)     | Commands for managing Magento gift cards.                                    | `giftcard:pool:generate`, `giftcard:create`, `giftcard:info`, `giftcard:remove`             |
| [generation](https://netz98.github.io/n98-magerun2/command-docs/generation/) | Commands related to Magento's code generation processes.                    | `generation:flush`                                                                          |
| [index](https://netz98.github.io/n98-magerun2/command-docs/index/)           | Commands for managing Magento's indexers.                                   | `index:list`, `index:trigger:recreate`                                                      |
| [install](https://netz98.github.io/n98-magerun2/command-docs/installer/)     | Command for installing Magento.                                             | `installer`                                                                                 |
| [integration](https://netz98.github.io/n98-magerun2/command-docs/integration/) | Command for integrations to Magento.                                                     | `integration:list`, `integration:show`, `integration:delete`                                |
| [magerun](https://netz98.github.io/n98-magerun2/command-docs/magerun/)       | Commands for working with n98-magerun2 config and internal tools.         | `magerun:config:info`, `magerun:config:dump`                                                 |
| [routes](https://netz98.github.io/n98-magerun2/command-docs/routes/)         | Commands for managing and viewing Magento routes.                                  | `routes:list`                                                                               |
| [script](https://netz98.github.io/n98-magerun2/command-docs/scripting/)      | Command for running sequences of n98-magerun2 commands from a file.       | `script`                                                                                    |
| [sys](https://netz98.github.io/n98-magerun2/command-docs/system/)            | Commands for system-level information, checks, and maintenance tasks.     | `sys:info`, `sys:check`, `sys:maintenance`, `sys:cron:list`, `sys:store:list`               |



## License

MIT License. See [MIT-LICENSE.txt](./MIT-LICENSE.txt).
