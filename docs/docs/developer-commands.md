---
title: Developer Commands
---
### Clear static view files

```sh
n98-magerun2.phar dev:asset:clear [--theme="..."]
```

Options:

| Option                  | Description                                                        |
|-------------------------|--------------------------------------------------------------------|
| `-t, --theme=THEME`     | Clear assets for specific theme(s) only (multiple values allowed). |


To clear assets for all themes:

```sh
n98-magerun2.phar dev:asset:clear
```

To clear assets for specific theme(s) only:

```sh
n98-magerun2.phar dev:asset:clear --theme=Magento/luma
```

### List Themes

```sh
n98-magerun2.phar dev:theme:list [--format[=FORMAT]]
```
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


### Build Hyva Theme CSS

```sh
n98-magerun2.phar dev:theme:build-hyva [--production] [<theme-name>]
```
**Arguments:**
| Argument     | Description                                |
|--------------|--------------------------------------------|
| `theme-name` | Hyvä Theme to build (e.g. Hyva/default)    |
**Options:**
| Option           | Description                                           |
|------------------|-------------------------------------------------------|
| `-p, --production`| Build for production (minified) instead of watch mode |


Example: `n98-magerun2.phar dev:theme:build-hyva "Hyva/default"`

The command starts in watch mode by default, as it is primarily designed for developers.

If no theme is specified, an interactive mode allows you to select a theme from a list.

If the `--production` flag is set, the command does not run in watch mode and will stop after the theme is built.

---

### Create Module Skeleton

Creates an empty module and registers it in current Magento shop.

```sh
n98-magerun2.phar dev:module:create [options] [--] <vendorNamespace> <moduleName>
```
**Arguments:**
| Argument          | Description                     |
|-------------------|---------------------------------|
| `vendorNamespace` | Namespace (your company prefix) |
| `moduleName`      | Name of your module.            |

**Options (selected):**
| Option                          | Description                                         |
|---------------------------------|-----------------------------------------------------|
| `-m, --minimal`                 | Create only module file                             |
| `--add-blocks`                  | Adds blocks                                         |
| `--add-helpers`                 | Adds helpers                                        |
| `--add-models`                  | Adds models                                         |
| `--add-setup`                   | Adds SQL setup                                      |
| `--add-all`                     | Adds blocks, helpers and models                     |
| `-e, --enable`                  | Enable module after creation                        |
| `--modman`                      | Create all files in folder with a modman file.      |
| `--add-readme`                  | Adds a readme.md file to generated module           |
| `--add-composer`                | Adds a composer.json file to generated module       |
| `--add-strict-types`            | Add strict_types declaration to generated PHP files |
| `--author-name[=AUTHOR-NAME]`   | Author for readme.md or composer.json               |
| `--author-email[=AUTHOR-EMAIL]` | Author for readme.md or composer.json               |
| `--description[=DESCRIPTION]`   | Description for readme.md or composer.json          |


---

### Detect Composer Dependencies in Module

The source code of one or more modules can be scanned for dependencies.

```sh
n98-magerun2.phar dev:module:detect-composer-dependencies [--only-missing] <path>...
```
**Arguments:**
| Argument | Description      |
|----------|------------------|
| `path`   | Path to modules  |

**Options:**
| Option           | Description                       |
|------------------|-----------------------------------|
| `--only-missing` | Print only missing dependencies.  |


---

### Translations

Enable/disable inline translation feature for Magento Admin:

```sh
n98-magerun2.phar dev:translate:admin [--on] [--off]
```

Enable/disable inline translation feature for shop frontend:

```sh
n98-magerun2.phar dev:translate:shop [--on] [--off] [<store>]
```

Set a translation (saved in translation table)

```sh
n98-magerun2.phar dev:translate:set <string> <translate> [<store>]
```

Export inline translations

```sh
n98-magerun2.phar dev:translate:export [--store=<storecode>] <locale> [<filename>]
```

---

### DI (Dependency Injection)

List Preferences:

```sh
n98-magerun2.phar dev:di:preferences:list [--format [FORMAT]] [<area>]
```

`area` is one of [global, adminhtml, frontend, crontab, webapi_rest, webapi_soap, graphql, doc, admin] 

Format can be `csv`, `json`, `xml` or `yaml`.

---

### List modules

```sh
n98-magerun2.phar dev:module:list [options]
```
**Options:**
| Option             | Description                                            |
|--------------------|--------------------------------------------------------|
| `--vendor[=VENDOR]` | Show modules of a specific vendor (case insensitive)   |
| `-e, --only-enabled`| Show only enabled modules                              |
| `-d, --only-disabled`| Show only disabled modules                             |
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml]   |


Lists all installed modules. If `--vendor` option is set, only modules of the given vendor are listed.
If `--only-enabled` option is set, only enabled modules are listed.
If `--only-disabled` option is set, only disabled modules are listed.
Format can be `csv`, `json`, `xml` or `yaml`.

### Encryption

Encrypt the given string using Magentos crypt key

```sh
n98-magerun2.phar dev:encrypt <value>
```

Decrypt the given string using Magentos crypt key

```sh
n98-magerun2.phar dev:decrypt <value>
```

---

### List Observers

```sh
n98-magerun2.phar dev:module:observer:list [--sort] [--format=FORMAT] [<event> [<area>]]
```
**Arguments:**
| Argument | Description                                                                                               |
|----------|-----------------------------------------------------------------------------------------------------------|
| `event`  | Filter observers for specific event.                                                                      |
| `area`   | Filter observers in specific area. One of [global,adminhtml,frontend,crontab,webapi_rest,webapi_soap,graphql,doc,admin] |
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--sort`           | Sort output ascending by event name                  |
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


### List Routes

```sh
n98-magerun2.phar route:list [-a|--area=AREA] [-m|--module=MODULE] [--format=FORMAT]
```
**Options:**
| Option                   | Description                                          |
|--------------------------|------------------------------------------------------|
| `-a, --area[=AREA]`      | Route area code. One of [frontend,adminhtml]         |
| `-m, --module[=MODULE]`  | Show registered routes of a module                   |
| `--format[=FORMAT]`      | Output Format. One of [csv,json,json_array,yaml,xml] |

---
### dev:report:count
Get count of report files.
```sh
n98-magerun2.phar dev:report:count
```
---
### dev:symlinks
Toggle allow symlinks setting.
```sh
n98-magerun2.phar dev:symlinks [options] [--] [<store>]
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
---
### dev:template-hints
Toggles template hints.
```sh
n98-magerun2.phar dev:template-hints [options] [--] [<store>]
```
**Arguments:**
| Argument | Description    |
|----------|----------------|
| `store`  | Store code or ID |
**Options:**
| Option | Description |
|--------|-------------|
| `--on` | Switch on   |
| `--off`| Switch off  |
---
### dev:template-hints-blocks
Toggles template hints block names.
```sh
n98-magerun2.phar dev:template-hints-blocks [options] [--] [<store>]
```
**Arguments:**
| Argument | Description    |
|----------|----------------|
| `store`  | Store code or ID |
**Options:**
| Option | Description |
|--------|-------------|
| `--on` | Switch on   |
| `--off`| Switch off  |
---

### Interactive Development Console

Opens PHP interactive shell with initialized Magento Admin-Store.

```sh
n98-magerun2.phar dev:console [options] [--] [<cmd>]
```
**Arguments:**
| Argument | Description              |
|----------|--------------------------|
| `cmd`    | Direct code to run [default: ""] |
**Options:**
| Option         | Description                |
|----------------|----------------------------|
| `-a, --area=AREA` | Area to initialize       |
| `-e, --auto-exit` | Automatic exit after cmd |


Optional an area code can be defined. If provided, the configuration
(di.xml, translations) of the area are loaded.

Possible area codes are:

- `adminhtml`
- `crontab`
- `frontend`
- `graphql`
- `webapi_xml`
- `webapi_rest`

Variable `$di` is made available with a
`Magento\Framework\ObjectManagerInterface` instance to allow creation of
object instances.

Variable `$dh` provides convenient debugging functions.
Type `$dh->` and press Tab for a list.

Example:

```bash
n98-magerun2 dev:console --area=adminhtml
    // show name of category 123 in default store
    $dh->debugCategoryById(123)['name']; 
    // show name of product id 123
    $dh->debugProductById(123)['name']; 
```

The interactive console works as
[REPL](https://en.wikipedia.org/wiki/Read%E2%80%93eval%E2%80%93print_loop).
It's possible to enter any PHP code. The code will be executed immediately.
The interactive console also comes with a lot of embedded scommands.

It's possible to add initial commands to the interactive console.
Commands should be delimited by a semicolon. You can mix PHP-Code with
embedded interactive console commands.

**Example:**

```sh
n98-magerun2.phar dev:console "$a = 1; call cache:flush; ls;"
```

The interactive console comes with a extendable code generator tool to
create i.e. modules, cli commands, controllers, blocks, helpers etc.

The console can be in a module context which allows you to generate code
for a selected module.

The basic idea of the stateful console was developed by [Jacques
Bodin-Hullin](https://github.com/jacquesbh) in this great tool
[Installer](https://github.com/jacquesbh/installer).

## Development

We have more information on the wiki page:

<https://github.com/netz98/n98-magerun2/wiki>

### Included Commands for Plugin Developers

We offer two command to debug the configuration.

The `magerun:config:info` can display all loaded config files.

```sh
$> n98-magerun2.phar magerun:config:info [--format[=FORMAT]]
```
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |

```
+------+----------------------------------+-------------------------------------------------+
| type | path                             | note                                            |
+------+----------------------------------+-------------------------------------------------+
| dist |                                  | Shipped in phar file                            |
| user | /home/cmuench/.n98-magerun2.yaml | Configuration in home directory of current user |
+------+----------------------------------+-------------------------------------------------+
```
The `magerun:config:dump` command can dump the merged configuration as highlighted yaml.

```sh
$> n98-magerun2.phar magerun:config:dump [options]
```
**Options:**
| Option             | Description                               |
|--------------------|-------------------------------------------|
| `--only-dist`      | Only dump the dist config                 |
| `-r, --raw`        | Dump the raw config without formatting    |
| `-i, --indent[=INDENT]`| Indentation level [default: 4]          |
