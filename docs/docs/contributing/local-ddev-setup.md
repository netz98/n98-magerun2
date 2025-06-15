---
title: Local DDEV Setup
---

## What is DDEV?

[DDEV](https://ddev.readthedocs.io/en/stable/) is an open-source tool that provides a consistent, containerized local development environment. It is especially useful for PHP projects and supports tools like Composer, PHPUnit, and more.

The project contains a `ddev` configuration for easy local development.
All configuration files are located in the `.ddev` directory.

## DDEV Setup in This Project

- All DDEV configuration files are located in the `.ddev` directory at the root of the repository.
- The main configuration file is `.ddev/config.yaml`, which defines the web server, database, PHP version, and other services.
- Additional configuration and service overrides are provided in the `.ddev` directory (e.g., custom Docker Compose files, service-specific configs, and helper scripts).

### Getting Started

1. **Install DDEV**  
   Follow the [official DDEV installation guide](https://ddev.readthedocs.io/en/stable/) for your operating system.

2. **Start the DDEV Environment**  
   In the project root, run:
   ```bash
   ddev start
   ```
   This will build and start the containers as defined in `.ddev/config.yaml`.

3. **Access the Container**  
   To run commands in the containerized environment, use:
   ```bash
   ddev ssh
   ```
   This gives you a shell inside the web container, where you can run Composer, PHPUnit, and other tools.

4. **Common Commands**
   - Composer:
     ```bash
     ddev composer
     ```
   - Run unit tests:  
     ```bash
     ddev unit-test-24
     ```
   - Build the PHAR file:  
     ```bash
     ddev exec ./build.sh
     ```

### Customization

You can customize the environment by editing or adding files in the `.ddev` directory. For example:
- Add PHP configuration in `.ddev/php/`
- Add web server configuration in `.ddev/apache/` or `.ddev/nginx_full/`
- Add custom commands in `.ddev/commands/`

### Automated Magento Installation and Test Environments

When you start the DDEV environment for this project, a Magento installation is automatically set up for testing purposes. This process is handled by DDEV post-start hooks and supporting scripts.

- **Persistent Test Environments:**
  All test Magento installations are created inside the container at `/opt/magento-test-environments`. This directory is mounted as a persistent Docker volume, ensuring that your test environments and data are preserved across container restarts and rebuilds.

- **Default Magento Version:**
  The version of Magento used for the default test installation is controlled by the `MAGERUN_SETUP_TEST_DEFAULT_MAGENTO_VERSION` environment variable, which is defined in the `.ddev/config.yaml` file:
  
  ```yaml
  web_environment:
    - MAGERUN_SETUP_TEST_DEFAULT_MAGENTO_VERSION=2.4.7-p4
  ```

(check your `.ddev/config.yaml` for the exact version).
  
  You can change this value to install a different Magento version for testing. The setup scripts will automatically use the specified version when creating the default test environment.

- **Test Installation Location:**
  All test installations are located in `/opt/magento-test-environments` inside the container. This folder is a persistent volume mount, so your test environments remain available even after restarting or rebuilding the containers.

- **Automatic Setup:**
  Magento is installed automatically on `ddev start`.

### Locally Installed DDEV Commands

The project provides several custom DDEV commands to simplify development and testing. These commands are located in the `.ddev/commands` directory and are available inside the DDEV environment. You can run them using `ddev <command>`.

#### Web Container Commands (`.ddev/commands/web/`)

- **get-magento-source**  
  Generates a PHAR file with the Magento source code for IDE code completion.  
  Usage: `ddev get-magento-source`

- **install-magento-ce**  
  Installs a specific version of Magento Community Edition in a persistent test environment.  
  Usage: `ddev install-magento-ce <version> <use_opensearch yes|no>`
  
  The instance is installed in `/opt/magento-test-environments/<version>`.

- **install-magento-ce-git**  
  Installs Magento CE from a Git repository (for advanced use cases).

- **install-mage-os**  
  Installs Mage-OS, an open-source fork of Magento.
  Usage: `ddev install-mage-os <version> <use_opensearch yes|no>`

  The instance is installed in `/opt/magento-test-environments/<version>`.

- **mr2-dev-23** / **mr2-dev-24**  
  Set up development environments for specific Magento 2.x versions.

- **qa**  
  Runs all important quality assurance checks.
  Usage: `ddev qa`

- **unit-test-23** / **unit-test-24**  
  Run the test suite against Magento 2.3 or 2.4 test environments, respectively.
  We will drop support for Magento 2.3 in the future, so please use `unit-test-24` for new tests.

#### Host, DB, and Solr Commands

- **host/**  
  Contains example scripts for integrating with host tools like MySQL Workbench and PhpStorm.

- **db/**  
  Example scripts for database operations, such as `mysqldump`.

> For more details or to add your own commands, see the `README.txt` files in each subdirectory.

### Additional Notes

- The DDEV environment is designed to closely match production and CI environments.
- Database, web server, and other services are pre-configured for Magento 2 development.
- See the `.ddev/README.txt` files for more details on specific service configurations.

For more information, refer to the [DDEV documentation](https://ddev.readthedocs.io/en/stable/).
