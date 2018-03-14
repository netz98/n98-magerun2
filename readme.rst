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

Development is done in **develop** branch.

This software is only running with Magento 2.

If you use Magento 1 please use another stable version (https://github.com/netz98/n98-magerun).

Compatibility
-------------
The tools will automatically be tested for multiple PHP versions. It's currently running in various Linux distributions and Mac OS X.
Microsoft Windows is not fully supported (some Commands like `db:dump` or `install` are excluded).

Installation
------------

There are three ways to install the tools:

Download and Install Phar File
""""""""""""""""""""""""""""""

Download the latest stable N98-Magerun phar-file from the file-server_:

.. code-block:: sh

    wget https://files.magerun.net/n98-magerun2.phar

or if you prefer to use Curl:

.. code-block:: sh

   curl -O https://files.magerun.net/n98-magerun2.phar

Verify the download by comparing the SHA256 checksum with the one on the website:

.. code-block:: sh

    shasum -a256 n98-magerun2.phar

If it shows the same checksum as on the website, you downloaded the file successfully.

Now you can make the phar-file executable:

.. code-block:: sh

    chmod +x ./n98-magerun2.phar

The base-installation is now complete and you can verify it:

.. code-block:: sh

    ./n98-magerun2.phar --version

The command should execute successfully and show you the version number of N98-Magerun like:

.. code-block:: sh

    n98-magerun2 version 1.3.2 by netz98 GmbH

You now have successfully installed Magerun! You can tailor the installation further like installing it system-wide and
enable autocomplete - read on for more information about these and other features.

If you want to use the command system wide you can copy it to `/usr/local/bin`.

.. code-block:: sh

    sudo cp ./n98-magerun2.phar /usr/local/bin/

Install with Composer
"""""""""""""""""""""
Require Magerun within the Magento (or any other) project and you can then
execute it from the vendor’s bin folder:

.. code-block:: sh

    composer require n98/magerun2
    # ...
    ./vendor/bin/n98-magerun2 --version
    n98-magerun2 version 1.3.2 by netz98 GmbH

Install with Homebrew
"""""""""""""""""""""

First you need to have homebrew installed: http://brew.sh/

Install homebrew-php tap: https://github.com/Homebrew/homebrew-php#installation

Once homebrew and the tap are installed, you can install the tools with it:

.. code-block:: sh

    brew install n98-magerun2

You can now use the tools:

.. code-block:: sh

    $ n98-magerun2 {command}

Update
------

There is a `self-update` command available.
This works only for phar-distribution.

.. code-block:: sh

   $ n98-magerun2.phar self-update [--dry-run]

With `--dry-run` option it is possible to download and test the phar file without replacing the old one.

Autocompletion
--------------

Files for autocompletion with Magerun can be found inside the folder `res/autocompletion`, In
the following some more information about a specific one (Bash), there are
more (e.g. Fish, Zsh).

Bash
""""

Bash completion is available pre-generated, all commands and their respective
options are availble on tab. To get completion for an option type two dashes
("--") and then tab.

To install the completion files, copy **n98-magerun2.phar.bash** to your bash
compatdir folder for autocompletion.

On my Ubuntu system this can be done with the following command:

.. code-block:: sh

   # cp res/autocompletion/bash/n98-magerun2.phar.bash /etc/bash_completion.d

The concrete folder can be obtained via pkg-config::

   # pkg-config --variable=compatdir bash-completion

Detailed information is available in the bash-completions FAQ: https://github.com/scop/bash-completion#faq

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

Open Shop in Browser
""""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar open-browser [store]

Customer Info
"""""""""""""

Loads basic customer info by email address.

.. code-block:: sh

   $ n98-magerun2.phar  customer:info [email] [website]


Create customer
"""""""""""""""

Creates a new customer/user for shop frontend.

.. code-block:: sh

   $ n98-magerun2.phar  customer:create [email] [password] [firstname] [lastname] [website]

Example:

.. code-block:: sh

  $ n98-magerun2.phar customer:create foo@example.com password123 John Doe base

List Customers
""""""""""""""

List customers. The output is limited to 1000 (can be changed by overriding config).
If search parameter is given the customers are filtered (searchs in firstname, lastname and email).

.. code-block:: sh

   $ n98-magerun2.phar  customer:list [--format[="..."]] [search]

Change customer password
""""""""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar customer:change-password [email] [password] [website]

- Website parameter must only be given if more than one websites are available.

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

Set Config
""""""""""

.. code-block:: sh

   $ n98-magerun2.phar config:store:set [--scope[="..."]] [--scope-id[="..."]] [--encrypt] path value

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

   $ n98-magerun2.phar config:store:get [--scope="..."] [--scope-id="..."] [--decrypt] [--format[="..."]] [path]

Arguments:
    path        The config path

Options:
    --scope             The config value's scope (default, websites, stores)
    --scope-id          The config value's scope ID
    --decrypt           Decrypt the config value using crypt key defined in env.php
    --update-script     Output as update script lines
    --magerun-script    Output for usage with config:store:set
    --format            Output as json, xml or csv

Help:
    If path is not set, all available config items will be listed. path may contain wildcards (*)

Example:

.. code-block:: sh

   $ n98-magerun2.phar config:store:get web/* --magerun-script

Delete Config
"""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar config:store:delete [--scope[="..."]] [--scope-id[="..."]] [--all] path

Arguments:
    path        The config path

Options:
    --scope     The config scope (default, websites, stores)
    --scope-id  The config value's scope ID
    --all       Deletes all entries of a path (ignores --scope and --scope-id)

Display ACL Tree
""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar config:data:acl

Help:
    Prints acl.xml data as table

Print Dependency Injection Config Data
""""""""""""""""""""""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar config:data:di <type>


Arguments:
    type           Type (class)


Options:
    --scope (-s)   Config scope (global, adminhtml, frontend, webapi_rest, webapi_soap, ...) (default: "global")

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

   $ n98-magerun2.phar cache:flush [code]

Keep in mind that `cache:flush` cleares the cache backend, so other cache types in the same backend will be cleared as well.

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

Dump database
"""""""""""""

Dumps configured Magento database with `mysqldump`.

* Requires MySQL CLI tools

**Arguments**

    filename        Dump filename

**Options**

  --add-time
        Adds time to filename (only if filename was not provided)

  --compression (-c)
        Compress the dump file using one of the supported algorithms

  --only-command
        Print only mysqldump command. Do not execute

  --print-only-filename
        Execute and prints not output except the dump filename

  --dry-run
        Do everything but the actual dump

  --no-single-transaction
        Do not use single-transaction (not recommended, this is blocking)

  --human-readable
        Use a single insert with column names per row.

  --add-routines
        Include stored routines in dump (procedures & functions).

  --stdout
        Dump to stdout

  --strip
        Tables to strip (dump only structure of those tables)

  --exclude
        Tables to exclude entirely from the dump (including structure)

  --force (-f)
        Do not prompt if all options are defined


.. code-block:: sh

   $ n98-magerun2.phar db:dump

Only the mysqldump command:

.. code-block:: sh

   $ n98-magerun2.phar db:dump --only-command [filename]

Or directly to stdout:

.. code-block:: sh

   $ n98-magerun2.phar db:dump --stdout

Use compression (gzip cli tool has to be installed):

.. code-block:: sh

   $ n98-magerun2.phar db:dump --compression="gzip"

Stripped Database Dump
^^^^^^^^^^^^^^^^^^^^^^

Dumps your database and excludes some tables. This is useful for development or staging environments
where you may want to provision a restricted database.

Separate each table to strip by a space.
You can use wildcards like * and ? in the table names to strip multiple tables.
In addition you can specify pre-defined table groups, that start with an @
Example: "dataflow_batch_export unimportant_module_* @log

.. code-block:: sh

   $ n98-magerun2.phar db:dump --strip="@stripped"

Available Table Groups:

* @customers Customer data
* @development Removes logs, sessions, trade data and admin users so developers do not have to work with real customer data or admin user accounts
* @ee_changelog Changelog tables of new indexer since EE 1.13
* @idx Tables with _idx suffix and index event tables
* @log Log tables
* @quotes Cart (quote) data
* @sales Sales data (orders, invoices, creditmemos etc)
* @search Search related tables (catalogsearch_)
* @sessions Database session tables
* @stripped Standard definition for a stripped dump (logs and sessions)
* @trade Current trade data (customers, orders and quotes). You usually do not want those in developer systems.

Clear static view files
"""""""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar dev:asset:clear [--theme="..."]

Options:
    --theme     The specific theme(s) to clear

To clear assets for all themes:

.. code-block:: sh

   $ n98-magerun2.phar dev:asset:clear

To clear assets for specific theme(s) only:

.. code-block:: sh

   $ n98-magerun2.phar dev:asset:clear --theme=Magento/luma

EAV Attributes
""""""""""""""

View the data for a particular attribute:

.. code-block:: sh

   $ n98-magerun2.phar eav:attribute:view [--format[="..."]] entityType attributeCode

Generate Gift Card Pool
"""""""""""""""""""""""

Generates a new gift card pool.

.. code-block:: sh

   $ n98-magerun2.phar giftcard:pool:generate

Create a Gift Card
""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar giftcard:create [--website[="..."]] [--expires[="..."]] [amount]

You may specify a website ID or use the default. You may also optionally add an expiration date to the gift card
using the `--expires` option. Dates should be in `YYYY-MM-DD` format.

View Gift Card Information
""""""""""""""""""""""""""

.. code-block:: sh

   $ n98-magerun2.phar giftcard:info [--format[="..."]] [code]

Remove a Gift Card
""""""""""""""""""

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

Downgrade Setup Versions
""""""""""""""""""""""""

Downgrade the versions in the database to the module version from its xml file if necessary. Useful while developing
and switching branches between module version changes.

.. code-block:: sh

   $ n98-magerun2.phar sys:setup:downgrade-versions

Dump Media folder
"""""""""""""""""

Creates a ZIP archive with media folder content.

.. code-block:: sh

   $ n98-magerun.phar media:dump [--strip] [filename]

Interactive Development Console
"""""""""""""""""""""""""""""""

Opens PHP interactive shell with initialized Magento Admin-Store.

.. code-block:: sh

   $ n98-magerun2.phar dev:console <arg>

Variable ``$di`` is made available with a ``Magento\Framework\ObjectManagerInterface`` instance to allow creation of object instances.

The interactive console works as `REPL <https://en.wikipedia.org/wiki/Read%E2%80%93eval%E2%80%93print_loop>`_ .
It's possible to enter any PHP code. The code will be executed immediately.
The interactive console also comes with a lot of embedded commands.

It's possible to add initial commands to the interactive console. Commands should be delimited by a semicolon.
You can mix PHP-Code with embedded interactive console commands.

Example:

.. code-block:: sh

   $ n98-magerun2.phar dev:console "$a = 1; call cache:flush; ls;"


The interactive console comes with a extendable code generator tool to create i.e. modules, cli commands,
controllers, blocks, helpers etc.

The console can be in a module context which allows you to generate code for a selected module.

The basic idea of the stateful console was developed by `Jacques Bodin-Hullin <https://github.com/jacquesbh>`_ in this
great tool `Installer <https://github.com/jacquesbh/installer>`_.


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
   config:store:set "web/cookie/cookie_domain" example.com

   # Set with multiline values with "\n"
   config:store:set "general/store_information/address" "First line\nSecond line\nThird line"

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

   config:store:set "web/cookie/cookie_domain" example.com
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
