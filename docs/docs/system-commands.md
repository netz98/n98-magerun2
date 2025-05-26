---
title: System Commands
---
### Magento System Info

Provides infos like the edition, version or the configured cache
backends, amount of data or installed packages.

```sh
n98-magerun2.phar sys:info [options] [<key>]
```
**Arguments:**
| Argument | Description                                                            |
|----------|------------------------------------------------------------------------|
| `key`    | Only output value of named param like "version". Key is case insensitive. |

**Options:**

| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--sort`           | Sort table by name                                   |
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


---

### Magento Stores

Lists all store views.

```sh
n98-magerun2.phar sys:store:list [--format[="..."]]
```

### Magento Websites

Lists all websites.

```sh
n98-magerun2.phar sys:website:list [--format[="..."]]
```

---

### List Cronjobs

Lists all cronjobs defined in crontab.xml files.

```sh
n98-magerun2.phar sys:cron:list [--format[="..."]]
```

### Run Cronjobs

Runs a cronjob by code.

```sh
n98-magerun2.phar sys:cron:run [job]
```

If no `job` argument is passed you can select a job from a list.
See it in action: <http://www.youtube.com/watch?v=QkzkLgrfNaM>
If option schedule is present, cron is not launched, but just scheduled immediately in magento crontab.

### Kill a running job

```sh
n98-magerun2.phar sys:cron:kill [--timeout <seconds>] [job_code]
```

If no job is specified a interactive selection of all running jobs is shown.
Jobs can only be killed if the process runs on the same machine as n98-magerun2.

Default timeout of a process kill is 5 seconds.

### Cronjob History

Last executed cronjobs with status.

```sh
n98-magerun2.phar sys:cron:history [--format[="..."]] [--timezone[="..."]]
```
**Options:**

| Option                | Description                                          |
|-----------------------|------------------------------------------------------|
| `--timezone[=TIMEZONE]`| Timezone to show finished at in                      |
| `--format[=FORMAT]`   | Output Format. One of [csv,json,json_array,yaml,xml] |

### sys:cron:schedule
Schedule a cronjob for execution right now, by job code.
```sh
n98-magerun2.phar sys:cron:schedule [<job>]
```
**Arguments:**
| Argument | Description |
|----------|-------------|
| `job`    | Job code    |
**Help:**
If no `job` argument is passed you can select a job from a list.
---

### sys:check
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
---
### sys:maintenance
Toggles maintenance mode if --on or --off preferences are not set.
```sh
n98-magerun2.phar sys:maintenance [options]
```
**Options:**
| Option | Description                                                                                                                      |
|--------|----------------------------------------------------------------------------------------------------------------------------------|
| `--on` | Set to [1] to enable maintenance mode. Optionally supply a comma separated list of IP addresses to exclude from being affected |
| `--off`| Set to [1] to disable maintenance mode. Set to [d] to also delete the list with excluded IP addresses.                             |
---
### sys:url:list
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
---

### Compare Setup Versions

Compares module version with saved setup version in
`setup_module` table and displays version mismatchs if
found.

```sh
n98-magerun2.phar sys:setup:compare-versions [--ignore-data] [--log-junit="..."] [--format[="..."]]
```
**Options:**
| Option                 | Description                                          |
|------------------------|------------------------------------------------------|
| `--ignore-data`        | Ignore data updates                                  |
| `--log-junit=LOG-JUNIT`| Log output to a JUnit xml file.                      |
| `--format[=FORMAT]`    | Output Format. One of [csv,json,json_array,yaml,xml] |


- If a filename with `--log-junit` option is set the tool generates an XML file and no output to *stdout*.

### Change Setup Version

Changes the version of a module. This command is useful if you want to
re-run an upgrade script again possibly for debugging. Alternatively you
would have to alter the row in the database manually.

```sh
n98-magerun2.phar sys:setup:change-version <module> <version>
```
**Arguments:**
| Argument  | Description        |
|-----------|--------------------|
| `module`  | Module name        |
| `version` | New version value  |

---

### Downgrade Setup Versions

Downgrade the versions in the database to the module version from its
xml file if necessary. Useful while developing and switching branches
between module version changes.

```sh
n98-magerun2.phar sys:setup:downgrade-versions [--dry-run]
```
**Options:**
| Option     | Description                                       |
|------------|---------------------------------------------------|
| `--dry-run`| Write what to change but do not do any changes    |


### List all configured store URLs

The default behavior is to show the base URL of all stores except the admin store.
If you want to show the base URL of the admin store as well, use the `--with-admin-store` option.
If you want to show the admin login URL as well, use the `--with-admin-login-url` option.
The options `--with-admin-store` and `--with-admin-login-url` cannot be combined, because both print a url for the same store.

```sh
n98-magerun2.phar sys:store:config:base-url:list [options]
```
**Options:**
| Option                      | Description                                          |
|-----------------------------|------------------------------------------------------|
| `--with-admin-store`        | Include admin store                                  |
| `--with-admin-login-url`    | Include admin login url                              |
| `--format[=FORMAT]`         | Output Format. One of [csv,json,json_array,yaml,xml] |

---
