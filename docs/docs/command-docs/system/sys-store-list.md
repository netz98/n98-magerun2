---
title: sys:store:list
---

# sys:store:list

List all store views.

## Usage
```sh
n98-magerun2.phar sys:store:list
```

## Options
- `--format` Output format. One of: `csv`, `json`, `json_array`, `yaml`, `xml`

## Examples
```sh
# JSON array output (recommended for scripting)
n98-magerun2.phar sys:store:list --format json

# CSV output
n98-magerun2.phar sys:store:list --format csv
```
