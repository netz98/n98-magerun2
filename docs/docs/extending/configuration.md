---
title: Configuration
---

n98-magerun2 can be extended and changed by configurations.
It's possible to overwrite the build-in config delivered by magerun. See config.yml file https://github.com/netz98/n98-magerun2/blob/master/config.yaml

Configs can be loaded on different levels:

- build-in config by magerun
- on system level
- on user level
- on module level
- on project level

All configs will be merged in the following order: `buildin -> system -> user -> module -> project`

To verify which config is loaded, you can use the command [`magerun:config:dump`](../command-docs/magerun/magerun-config-dump.md).
The commands [`magerun:config:info`](../command-docs/magerun/magerun-config-info.md) shows the merged config and the source of each config value.

## Example Config 

```yaml
autoloaders:
  # Namespace => path to your libs
  VendorPrefix: /path/to/VendorPrefix/src
  AnotherPrefix: /path/to/another-prefix

commands:
  customCommands:
    - VendorPrefix\Magento\Command\MyCommand
    - AnotherPrefix\FooCommand
    - AnotherPrefix\BarCommand
  aliases:
    - "ccc": "cache:clean config"
    - "customer:create:cmuench": "customer:create c.muench@netz98.de test123456 Christian Münch"
```

## Config Types

### System Wide Config

A system wide configuration can be placed in **/etc/n98-magerun2.yaml**

`%windir%\\n98-magerun2.yaml` (only Microsoft Windows)

### User Config

Place your config in your home directory **~/.n98-magerun2.yaml**

`%userprofile%\\n98-magerun2.yaml` (only Microsoft Windows)

### Project Config

You can load a config in your Magento project.
Create your config here: **app/etc/n98-magerun2.yaml**

### Alternative Project Config

You can now place an alternative project config file in the project root folder. This was an often requested feature for n98-magerun. We will have this features also in the next n98-magerun1 version.

It’s now possible to place a new config file .n98-magerun2.yaml in the project root. Please note that project root can be different to your Magento root.
The .n98-magerun2.yaml file will only be loaded if “stop file” .n98-magerun2 (the file with relative path to the Magento root folder) was found.

Example:

```
.                        -- Project root folder
├── .n98-magerun2        -- "Stop file"
├── .n98-magerun2.yaml   -- Alternative project config
└── www                  -- Magento root folder
```
