======================================
netz98 magerun CLI tools for Magento 2
======================================

The n98 magerun cli tools provides some handy tools to work with Magento from command line.

DEVELOPMENT IN GIT BRANCH **develop**.

This software is only running with Magento 2.
If you use Magento 1 please use another stable version (https://github.com/netz98/n98-magerun).

Installation
------------

There are two ways to install the tools:

Download phar file
""""""""""""""""""

.. code-block:: sh

    wget https://raw.githubusercontent.com/netz98/n98-magerun2/master/n98-magerun2.phar

or if you have problems with SSL certificate:

.. code-block:: sh

   curl -o n98-magerun2.phar https://raw.githubusercontent.com/netz98/n98-magerun2/master/n98-magerun2.phar

You can make the .phar file executable.

.. code-block:: sh

    chmod +x ./n98-magerun2.phar

If you want to use the command system wide you can copy it to `/usr/local/bin`.

.. code-block:: sh

    sudo cp ./n98-magerun2.phar /usr/local/bin/


Usage / Commands
----------------

All commands try to detect the current Magento root directory.
If you have multiple Magento installations you must change your working directory to
the preferred installation.

You can list all available commands by::

   $ n98-magerun2.phar list


If you don't have the .phar file installed system wide you can call it with the PHP CLI interpreter::

   php n98-magerun2.phar list


Global config parameters:

  --root-dir
      Force Magento root dir. No auto detection.
  --skip-config
      Do not load any custom config.
  --skip-root-check
      Do not check if n98-magerun2 runs as root.

Magento Installer
"""""""""""""""""

* Downloads Composer (if not already installed)
* Downloads Magento 2.
* Tries to create database if it does not exist.
* Installs Magento sample data.
* Starts Magento installer
* Sets rewrite base in .htaccess file

Interactive installer:

.. code-block:: sh

   $ n98-magerun2.phar install

Unattended installation:

.. code-block:: sh

   $ n98-magerun2.phar install [--magentoVersion[="..."]] [--magentoVersionByName[="..."]] [--installationFolder[="..."]] [--dbHost[="..."]] [--dbUser[="..."]] [--dbPass[="..."]] [--dbName[="..."]] [--installSampleData[="..."]] [--useDefaultConfigParams[="..."]] [--baseUrl[="..."]] [--replaceHtaccessFile[="..."]]

Example of an unattended Magento CE 2.0.0.0 dev beta 1 installation:

.. code-block:: sh

   $ n98-magerun2.phar install --dbHost="localhost" --dbUser="mydbuser" --dbPass="mysecret" --dbName="magentodb" --installSampleData=yes --useDefaultConfigParams=yes --magentoVersionByName="magento-ce-2.0.0.0-dev-beta1" --installationFolder="magento2" --baseUrl="http://magento2.localdomain/"

Additionally, with --noDownload option you can install Magento working copy already stored in --installationFolder on
the given database.

Magento system info
"""""""""""""""""""

Provides info like the edition and version or the configured cache backends.

.. code-block:: sh

   $ n98-magerun2.phar sys:info

Magento Stores
""""""""""""""

Lists all store views.

.. code-block:: sh

   $ n98-magerun2.phar sys:store:list [--format[="..."]]

Magento Websites
""""""""""""""""

Lists all websites.

.. code-block:: sh

   $ n98-magerun2.phar sys:website:list [--format[="..."]]

List Magento cache status
"""""""""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar cache:list