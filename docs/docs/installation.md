---
id: installation
slug: /installation
title: "Installation"
sidebar_label: Installation
sidebar_position: 2
---

# Installation

There are three ways to install the tools:

## Download and Install Phar File

Download the latest stable n98-magerun phar-file from the [file-server](https://files.magerun.net/):

```sh
curl -O https://files.magerun.net/n98-magerun2.phar
```

All historic versions including their GPG signatures are also stored on this server. You can
browse [all available versions](https://files.magerun.net/old_versions.php).
To download a specific release replace `<version>` in the file name. Example for version `7.0.3`:

```sh
curl -O https://files.magerun.net/n98-magerun2-7.0.3.phar
curl -O https://files.magerun.net/n98-magerun2-7.0.3.phar.asc
```

Verify the download by comparing the SHA256 checksum with the one on the
website:

```sh
shasum -a256 n98-magerun2.phar
```

It is also possible to verify automatically:

```sh
curl -sS -O https://files.magerun.net/n98-magerun2.phar
curl -sS -o n98-magerun2.phar.sha256 https://files.magerun.net/sha256.php?file=n98-magerun2.phar
shasum -a 256 -c n98-magerun2.phar.sha256
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

## Install the phar via Composer

We offer a special dist package to install the phar file via Composer.
See (https://packagist.org/packages/n98/magerun2-dist) for more details.
The main advantage of the dist package is that there are **no package dependencies**.

### Installation in a project

```bash
composer require n98/magerun2-dist
```

Run the command with `./vendor/bin/n98-magerun2.phar`

### Installation globally

```bash
composer global require n98/magerun2-dist
````

:::info
requires ~/.composer/vendor/bin in your PATH
:::


## Install with Composer (Source Package)

:::danger
The installation via Composer is **not recommended** to run the tool in daily use,
because it's impossible to be compatible with all project and Magento core dependencies.
Please use the phar file instead of the Composer version. We are not able to provide
compatibility to all Magento versions anymore.
:::

```bash
composer require n98/n98-magerun2
```

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
