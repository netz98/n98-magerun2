---
title: ddev Integration
---

[ddev](https://ddev.com/) ships a version of n98-magerun2, so you can use it right away.
This version is not in any case the latest, so you may want to update it.

If you want to use the latest version of n98-magerun2, you can install it in your ddev project.

## Variant 1: Setup post install hook

Add a hook to your .ddev/config.yaml

```yaml
hooks:                                                                                                                  
  post-start:                                                                                                           
  - exec: bash .ddev/install_magerun.sh    
```

Add **.ddev/install_magerun.sh** file with this content:


```bash
#!/bin/bash

if [ ! -f "/usr/local/bin/n98-magerun2.phar" ]; then
    curl -sS -o n98-magerun2.phar https://files.magerun.net/n98-magerun2.phar
    curl -sS -o n98-magerun2.phar.sha256 https://files.magerun.net/sha256.php?file=n98-magerun2.phar
    shasum -a 256 -c n98-magerun2.phar.sha256
    rm n98-magerun2.phar.sha256
    chmod +x n98-magerun2.phar
    sudo cp -f n98-magerun2.phar /usr/local/bin/n98-magerun2.phar
    rm n98-magerun2.phar

    # remove pre-installed version
    sudo rm /usr/local/bin/magerun2

    sudo ln -s /usr/local/bin/n98-magerun2.phar /usr/local/bin/n98-magerun2
    sudo ln -s /usr/local/bin/n98-magerun2.phar /usr/local/bin/mr2
    sudo ln -s /usr/local/bin/n98-magerun2.phar /usr/local/bin/magerun2    
fi
```

Run `ddev start`.

### Usage

```
ddev exec mr2
```

## Variant 2: ddev magerun command

Add a file **.ddev/commands/web/magerun** with this content:

```bash
#!/bin/bash

## Description: Download and executes n98-magerun2
## Usage: magerun [flags] [args]
## Example: "ddev magerun"
## ProjectTypes: magento2

if [ ! -f "/usr/local/bin/n98-magerun2.phar" ]; then
    curl -sS -o n98-magerun2.phar https://files.magerun.net/n98-magerun2.phar
    curl -sS -o n98-magerun2.phar.sha256 https://files.magerun.net/sha256.php?file=n98-magerun2.phar
    shasum -a 256 -c n98-magerun2.phar.sha256
    rm n98-magerun2.phar.sha256
    chmod +x n98-magerun2.phar
    sudo cp -f n98-magerun2.phar /usr/local/bin/n98-magerun2.phar
    rm n98-magerun2.phar

    # remove pre-installed version
    sudo rm /usr/local/bin/magerun2

    sudo ln -s /usr/local/bin/n98-magerun2.phar /usr/local/bin/n98-magerun2
    sudo ln -s /usr/local/bin/n98-magerun2.phar /usr/local/bin/mr2
    sudo ln -s /usr/local/bin/n98-magerun2.phar /usr/local/bin/magerun2
fi

n98-magerun2.phar --root-dir=/var/www/html $@
```

### Usage

```
ddev magerun
```
