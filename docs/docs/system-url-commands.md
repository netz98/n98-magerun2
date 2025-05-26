---
sidebar_position: 22
title: System Check, Maintenance, and URL Commands
---
## search:engine:list
Lists all registered search engines.
```sh
n98-magerun2.phar search:engine:list [--format[=FORMAT]]
```
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |

## sys:check
Checks Magento System.
```sh
n98-magerun2.phar sys:check [options]
```
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |
**Help:**
  - Checks missing files and folders
  - Security
  - PHP Extensions (Required and Bytecode Cache)
  - MySQL InnoDB Engine

## sys:maintenance
Toggles maintenance mode if --on or --off preferences are not set.
```sh
n98-magerun2.phar sys:maintenance [options]
```
**Options:**
| Option | Description                                                                                                                      |
|--------|----------------------------------------------------------------------------------------------------------------------------------|
| `--on` | Set to [1] to enable maintenance mode. Optionally supply a comma separated list of IP addresses to exclude from being affected |
| `--off`| Set to [1] to disable maintenance mode. Set to [d] to also delete the list with excluded IP addresses.                             |

## sys:url:list
Get all urls.
```sh
n98-magerun2.phar sys:url:list [options] [--] [<stores> [<linetemplate>]]
```
**Arguments:**
| Argument       | Description                                        |
|----------------|----------------------------------------------------|
| `stores`       | Stores (comma-separated list of store ids)         |
| `linetemplate` | Line template [default: "{url}"]                   |
**Options:**
| Option             | Description                             |
|--------------------|-----------------------------------------|
| `--add-categories` | Adds categories                         |
| `--add-products`   | Adds products                           |
| `--add-cmspages`   | Adds cms pages                          |
| `--add-all`        | Adds categories, products and cms pages |
**Help:**
  Examples:
  
  - Create a list of product urls only:
  
     `$ n98-magerun2.phar sys:url:list --add-products 4`
  
  - Create a list of all products, categories and cms pages of store 4 
    and 5 separating host and path (e.g. to feed a jmeter csv sampler):
  
     `$ n98-magerun2.phar sys:url:list --add-all 4,5 '{host},{path}' > urls.csv`
  
  - The "linetemplate" can contain all parts "parse_url" return wrapped 
    in '{}'. '{url}' always maps the complete url and is set by default

## design:demo-notice
Toggles demo store notice for a store view.
```sh
n98-magerun2.phar design:demo-notice [options] [--] [<store>]
```
**Arguments:**
| Argument | Description    |
|----------|----------------|
| `store`  | Store code or ID |
**Options:**
| Option   | Description                 |
|----------|-----------------------------|
| `--on`   | Switch on                   |
| `--off`  | Switch off                  |
| `--global`| Set value on default scope  |
