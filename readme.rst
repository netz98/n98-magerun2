======================================
netz98 magerun CLI tools for Magento 2
======================================

The n98 magerun cli tools provides some handy tools to work with Magento from command line.

Build Status
------------

+------------------------+------------------------------------------------------------------------------------------------+
| **Latest Release**     | .. image:: https://travis-ci.org/netz98/n98-magerun2.png?branch=master                         |
|                        |    :target: https://travis-ci.org/netz98/n98-magerun2                                          |
|                        | .. image:: https://www.versioneye.com/user/projects/51236c8b294edc00020064c5/badge.png         |
|                        |    :target: https://www.versioneye.com/user/projects/51236c8b294edc00020064c5                  |
|                        | .. image:: https://scrutinizer-ci.com/g/netz98/n98-magerun2/badges/quality-score.png?b=master  |
|                        |    :target: https://scrutinizer-ci.com/g/netz98/n98-magerun2/                                  |
|                        | .. image:: https://poser.pugx.org/n98/magerun2/v/stable.png                                    |
|                        |    :target: https://packagist.org/packages/n98/magerun2                                        |
+------------------------+------------------------------------------------------------------------------------------------+
| **Development Branch** | .. image:: https://travis-ci.org/netz98/n98-magerun2.png?branch=develop                        |
|                        |    :target: https://travis-ci.org/netz98/n98-magerun2                                          |
|                        | .. image:: https://circleci.com/gh/netz98/n98-magerun2/tree/develop.svg?style=shield           |
|                        |    :target: https://circleci.com/gh/netz98/n98-magerun2/tree/develop                           |
|                        | .. image:: https://scrutinizer-ci.com/g/netz98/n98-magerun2/badges/quality-score.png?b=develop |
|                        |    :target: https://scrutinizer-ci.com/g/netz98/n98-magerun2/?branch=develop                   |
|                        | .. image:: https://codecov.io/github/netz98/n98-magerun2/coverage.svg?branch=develop           |
|                        |    :target: https://codecov.io/github/netz98/n98-magerun2?branch=develop                       |
+------------------------+------------------------------------------------------------------------------------------------+

DEVELOPMENT IN GIT BRANCH **develop**.

This software is only running with Magento 2.
If you use Magento 1 please use another stable version (https://github.com/netz98/n98-magerun).

Compatibility
-------------
The tools will automatically be tested for multiple PHP versions (5.4, 5.5). It's currently running in various Linux distributions and Mac OS X.
Microsoft Windows is not fully supported (some Commands like `db:dump` or `install` are excluded).

Installation
------------

There are two ways to install the tools:

Download phar file
""""""""""""""""""

.. code-block:: sh

    wget https://files.magerun.net/n98-magerun2.phar

or if you have problems with SSL certificate:

.. code-block:: sh

   curl -O https://files.magerun.net/n98-magerun2.phar

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

Set Config
""""""""""

.. code-block:: sh

   $ n98-magerun2.phar config:set [--scope[="..."]] [--scope-id[="..."]] [--encrypt] path value

Arguments:
    path        The config path
    value       The config value

Options:
    --scope     The config value's scope (default: "default" | Can be "default", "websites", "stores")
    --scope-id  The config value's scope ID (default: "0")
    --encrypt   Encrypt the config value using crypt key

Get Config
""""""""""

.. code-block:: sh

   $ n98-magerun2.phar config:get [--scope="..."] [--scope-id="..."] [--decrypt] [--format[="..."]] [path]

Arguments:
    path        The config path

Options:
    --scope             The config value's scope (default, websites, stores)
    --scope-id          The config value's scope ID
    --decrypt           Decrypt the config value using local.xml's crypt key
    --update-script     Output as update script lines
    --magerun-script    Output for usage with config:set
    --format            Output as json, xml or csv

Help:
    If path is not set, all available config items will be listed. path may contain wildcards (*)

Example:

.. code-block:: sh

   $ n98-magerun2.phar config:get web/* --magerun-script

Delete Config
"""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar config:delete [--scope[="..."]] [--scope-id[="..."]] [--all] path

Arguments:
    path        The config path

Options:
    --scope     The config scope (default, websites, stores)
    --scope-id  The config value's scope ID
    --all       Deletes all entries of a path (ignores --scope and --scope-id)

List Magento cache status
"""""""""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar cache:list

Clean Magento cache
"""""""""""""""""""

Cleans expired cache entries.

If you would like to clean only one cache type:

.. code-block:: sh

   $ n98-magerun2.phar cache:clean [code]

If you would like to clean multiple cache types at once:

.. code-block:: sh

   $ n98-magerun2.phar cache:clean [code] [code] ...

If you would like to remove all cache entries use `cache:flush`

Run `cache:list` command to see all codes.

Remove all cache entries
""""""""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar cache:flush

List Magento caches
"""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar cache:list [--format[="..."]]

Disable Magento cache
"""""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar cache:disable [code]

If no code is specified, all cache types will be disabled.
Run `cache:list` command to see all codes.

Enable Magento cache
""""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar cache:enable [code]

If no code is specified, all cache types will be enabled.
Run `cache:list` command to see all codes.

EAV Attributes
"""""""""""""""""

View the data for a particular attribute:

.. code-block:: sh

   $ n98-magerun2.phar eav:attribute:view [--format[="..."]] entityType attributeCode

Generate Gift Card Pool
"""""""""""""""""

Generates a new gift card pool.

.. code-block:: sh

   $ n98-magerun2.phar giftcard:pool:generate

Create a Gift Card
"""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar giftcard:create [--website[="..."]] [--expires[="..."]] [amount]

You may specify a website ID or use the default. You may also optionally add an expiration date to the gift card
using the `--expires` option. Dates should be in `YYYY-MM-DD` format.

View Gift Card Information
"""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar giftcard:info [--format[="..."]] [code]

Remove a Gift Card
"""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar giftcard:remove [code]


Compare Setup Versions
""""""""""""""""""""""

Compares module version with saved setup version in `setup_module` table and displays version mismatchs if found.

.. code-block:: sh

   $ n98-magerun2.phar sys:setup:compare-versions [--ignore-data] [--log-junit="..."] [--format[="..."]]

* If a filename with `--log-junit` option is set the tool generates an XML file and no output to *stdout*.

Change Setup Version
""""""""""""""""""""

Changes the version of a module. This command is useful if you want to re-run an upgrade script again possibly for 
debugging. Alternatively you would have to alter the row in the database manually.

.. code-block:: sh

   $ n98-magerun2.phar sys:setup:change-version module version

Interactive Development Console
"""""""""""""""""""""""""""""""

Opens PHP interactive shell with initialized Magento Admin-Store.

.. code-block:: sh

   $ n98-magerun2.phar dev:console

Variable ``$di`` is made available with a ``Magento\Framework\ObjectManagerInterface`` instance to allow creation of object instances.

n98-magerun Shell
"""""""""""""""""

If you need autocompletion for all n98-magerun commands you can start with "shell command".

.. code-block:: sh

   $ n98-magerun2.phar shell

n98-magerun Script
""""""""""""""""""

Run multiple commands from a script file.

.. code-block:: sh

   $ n98-magerun2.phar [-d|--define[="..."]] [--stop-on-error] [filename]

Example:

.. code-block::

   # Set multiple config
   config:set "web/cookie/cookie_domain" example.com

   # Set with multiline values with "\n"
   config:set "general/store_information/address" "First line\nSecond line\nThird line"

   # This is a comment
   cache:flush


Optionally you can work with unix pipes.

.. code-block:: sh

   $ echo "cache:flush" | n98-magerun2.phar script

.. code-block:: sh

   $ n98-magerun2.phar script < filename

It is even possible to create executable scripts:

Create file `test.magerun` and make it executable (`chmod +x test.magerun`):

.. code-block:: sh

   #!/usr/bin/env n98-magerun2.phar script

   config:set "web/cookie/cookie_domain" example.com
   cache:flush

   # Run a shell script with "!" as first char
   ! ls -l

   # Register your own variable (only key = value currently supported)
   ${my.var}=bar

   # Let magerun ask for variable value - add a question mark
   ${my.var}=?

   ! echo ${my.var}

   # Use resolved variables from n98-magerun in shell commands
   ! ls -l ${magento.root}/code/local

Pre-defined variables:

* ${magento.root}    -> Magento Root-Folder
* ${magento.version} -> Magento Version i.e. 2.0.0.0
* ${magento.edition} -> Magento Edition -> Community or Enterprise
* ${magerun.version} -> Magerun version i.e. 2.1.0
* ${php.version}     -> PHP Version
* ${script.file}     -> Current script file path
* ${script.dir}      -> Current script file dir

Variables can be passed to a script with "--define (-d)" option.

Example:

.. code-block:: sh

   $ n98-magerun2.phar script -d foo=bar filename

   # This will register the variable ${foo} with value bar.

It's possible to define multiple values by passing more than one option.
