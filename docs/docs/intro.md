---
sidebar_position: 1
slug: /
---
# netz98 magerun CLI tools for Magento 2

![n98-magerun Logo](/img/magerun-logo.png)

The n98 magerun CLI Tools provides some handy tools to work with Magento / Mage-OS / Adobe Commerce
from command line.

> The swiss army knife for Magento developers, sysadmins and devops

This software is only running with Magento 2.

If you use Magento 1 (EOL) or OpenMage please use another software in a different repository: https://github.com/netz98/n98-magerun.

## Compatibility

The tools will automatically be tested for multiple PHP versions. It's
currently running in various Linux distributions and Mac OS X. Microsoft
Windows is not fully supported (some Commands like `db:dump`
or `install` are excluded).

We support the following Magento Versions:

- Mage-OS 1.0.x
- 2.4.7 Open Source/Commerce
- 2.4.6 Open Source/Commerce
- 2.4.5 Open Source/Commerce
- 2.4.4 Open Source/Commerce (last compatible n98-magerun2 version is v7.5.0)
- 2.3.x Open Source/Commerce (last compatible n98-magerun2 version is v5.2.0)
- 2.2.x Open Source/Commerce (last compatible n98-magerun2 version is v3.2.0)

We support the following PHP Versions:

- PHP 8.3
- PHP 8.2
- PHP 8.1
- PHP 7.4 (last compatible version is v7.5.0)
- PHP 7.3 (last compatible version is v6.1.1)
- PHP 7.2 (last compatible version is v4.7.0)

## Installation

There are three ways to install the tools:

### Download and Install Phar File

Download the latest stable n98-magerun phar-file from the [file-server](https://files.magerun.net/):

```sh
wget https://files.magerun.net/n98-magerun2.phar
```

or if you prefer to use Curl:

```sh
curl -O https://files.magerun.net/n98-magerun2.phar
```

Verify the download by comparing the SHA256 checksum with the one on the
website:

```sh
shasum -a256 n98-magerun2.phar
```

It is also possible to verify automatically:

```sh
curl -sS -O https://files.magerun.net/n98-magerun2-latest.phar
curl -sS -o n98-magerun2-latest.phar.sha256 https://files.magerun.net/sha256.php?file=n98-magerun2-latest.phar
shasum -a 256 -c n98-magerun2-latest.phar.sha256
```

If it shows the same checksum as on the website, you downloaded the file
successfully.

Now you can make the phar-file executable:

```sh
chmod +x ./n98-magerun2.phar
```

The base-installation is now complete and you can verify it:

```sh
./n98-magerun2.phar --version
```

The command should execute successfully and show you the version number
of N98-Magerun like:

`n98-magerun2 version 4.8.0 by valantic CEC`

You now have successfully installed Magerun! You can tailor the
installation further like installing it system-wide and enable
autocomplete - read on for more information about these and other
features.

If you want to use the command system wide you can copy it to
`/usr/local/bin`.

```sh
sudo cp ./n98-magerun2.phar /usr/local/bin/
```

### Install the phar via Composer

We offer a special dist package to install the phar file via Composer.
See (https://packagist.org/packages/n98/magerun2-dist) for more details.
The main advantage of the dist package is that there are no package dependencies.

### Install with Composer (Source Package) - not recommended

The installation via Composer is **not recommended**,
because it's impossible to be compatible with all project and Magento core dependencies.
Please use the phar file instead of the Composer version. We are not able to provide
compatibility to all Magento versions anymore.

## Update

There is a `self-update` command available. This works only
for phar-distribution.

```sh
./n98-magerun2.phar self-update [--dry-run] <version>
```

With `--dry-run` option it is possible to download and test
the phar file without replacing the old one.

The version argument is optional and can be used to rollback to a specific
version of n98-magerun2. The version was introduced with v8.0.0. Older versions do not have the version argument.

## Autocompletion

Files for autocompletion with Magerun can be found inside the folder
`res/autocompletion`, In the following some more information
about a specific one (Bash), there are more (e.g. Fish, Zsh).

### Bash

Bash completion is available pre-generated, all commands and their
respective options are availble on tab. To get completion for an option
type two dashes (`--`) and then tab.

To install the completion files, copy `n98-magerun2.phar.bash` to your
bash compatdir folder for autocompletion.

On my Ubuntu system this can be done with the following command:

```sh
sudo cp res/autocompletion/bash/n98-magerun2.phar.bash /etc/bash_completion.d/
```

The concrete folder can be obtained via pkg-config:

```sh
pkg-config --variable=compatdir bash-completion
```

Detailed information is available in the bash-completions FAQ:
https://github.com/scop/bash-completion#faq
