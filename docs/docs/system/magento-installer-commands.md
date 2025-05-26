---
sidebar_position: 4
title: Magento Installer Commands
---
## Magento Installer

- Downloads Composer (if not already installed)
- Downloads Magento 2.
- Tries to create database if it does not exist.
- Installs Magento sample data.
- Starts Magento installer
- Sets rewrite base in .htaccess file

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


Example of an unattended Magento CE 2.0.0.0 dev beta 1 installation:

```sh
n98-magerun2.phar install --dbHost="localhost" --dbUser="mydbuser" --dbPass="mysecret" --dbName="magentodb" --installSampleData=yes --useDefaultConfigParams=yes --magentoVersionByName="magento-ce-2.0.0.0-dev-beta1" --installationFolder="magento2" --baseUrl="http://magento2.localdomain/"
```

Additionally, with `--noDownload` option you can install Magento working
copy already stored in `--installationFolder` on the given database.
