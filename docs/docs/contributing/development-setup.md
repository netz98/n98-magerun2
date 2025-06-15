---
title: Development Setup
---

## Prerequisites

:::info
**Prerequisites:**
- PHP 8.1 or higher
- Composer
- Git
- Curl
- For testing: A Magento 2 installation
:::

## Setting Up the Development Environment

:::tip
The repository comes with a `ddev` configuration for easy local development. If you don't have `ddev` installed, follow the [DDEV installation guide](https://ddev.readthedocs.io/en/stable/).
Run all commands inside the ddev container. Use `ddev ssh` to enter the container.
:::

1. **Fork the Repository on GitHub**:
   - Go to [https://github.com/netz98/n98-magerun2](https://github.com/netz98/n98-magerun2) and click the "Fork" button to create your own copy of the repository under your GitHub account.

2. **Clone Your Forked Repository**:
   ```bash
   git clone https://github.com/<your-username>/n98-magerun2.git
   cd n98-magerun2
   ```

3. **Install Dependencies**:
   ```bash
   composer install
   ```

4. **Building the PHAR File**:
    See the [Build the PHAR file](./build-the-phar-file.md) section for details on how to build the `n98-magerun2.phar` file.

:::tip
It is a good practice to setup the upstream repository as a remote in your local clone. This allows you to easily pull updates from the main repository.
You can do this with: `git remote add upstream https://github.com/netz98/n98-magerun2.git`

Run `git fetch upstream` to fetch the latest changes from the main repository.
:::

### Testing

See the [Test Setup](./testing/) section for details on how to set up and run tests.


---

### Deployment
Documentation is deployed automatically via CI/CD on changes to the `main` branch. For manual deployment or troubleshooting, refer to the Docusaurus documentation or project-specific CI scripts.

### Build a phar file

You can build the phar file `/var/www/html/n98-magerun2.phar` in the ddev Docker web container with:

```bash
ddev exec ./build.sh
```
