---
title: install
sidebar_label: install
---

:::warning
**Deprecated:** This command is deprecated and will be removed in v10.0.0. A new install command will be available in that version.
:::

Magento installer command. Install Magento 2 with various options.

:::info
This command supports both interactive and unattended installation modes.
:::

Interactive installer:

```sh
n98-magerun2.phar install
```

Unattended installation:

```sh
n98-magerun2.phar install [options]
```

**Options (selected):**
| Option                                   | Description                                                                 |
|------------------------------------------|-----------------------------------------------------------------------------|
| `--magentoVersion[=MAGENTOVERSION]`      | Magento version                                                             |
| `--magentoVersionByName[=MAGENTOVERSIONBYNAME]` | Magento version name instead of order number                            |
| `--installationFolder[=INSTALLATIONFOLDER]`| Installation folder                                                         |
| `--dbHost[=DBHOST]`                      | Database host                                                               |
| `--dbUser[=DBUSER]`                      | Database user                                                               |
| `--dbPass[=DBPASS]`                      | Database password                                                           |
| `--dbName[=DBNAME]`                      | Database name                                                               |
| `--dbPort[=DBPORT]`                      | Database port [default: 3306]                                               |
| `--installSampleData[=INSTALLSAMPLEDATA]`| Install sample data                                                         |
| `--useDefaultConfigParams[=USEDEFAULTCONFIGPARAMS]` | Use default installation parameters defined in the yaml file          |
| `--baseUrl[=BASEURL]`                    | Installation base url                                                       |
| `--replaceHtaccessFile[=REPLACEHTACCESSFILE]` | Generate htaccess file (for non vhost environment)                        |
| `--noDownload`                           | If set skips download step.                                                 |
| `--only-download`                        | Downloads (and extracts) source code                                        |
| `--forceUseDb`                           | If passed, force to use given database if it already exists.                |
| `--composer-use-same-php-binary`         | If passed, will invoke composer with the same PHP binary                    |

:::tip
Use the `--useDefaultConfigParams` option to quickly install Magento with default parameters defined in your YAML configuration file.
:::

Example of an unattended Magento CE 2.0.0.0 dev beta 1 installation:

:::note
You can perform a fully unattended installation by specifying all required options:
:::

```sh
n98-magerun2.phar install --dbHost="localhost" --dbUser="mydbuser" --dbPass="mysecret" --dbName="magentodb" --installSampleData=yes --useDefaultConfigParams=yes --magentoVersionByName="magento-ce-2.0.0.0-dev-beta1" --installationFolder="magento2" --baseUrl="http://magento2.localdomain/"
```

:::info
With the `--noDownload` option, you can install Magento from a working copy already stored in `--installationFolder` on the given database.
:::
