---
sidebar_position: 28
title: Interactive Console and Scripting
---
## Interactive Development Console

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

## n98-magerun Script

Run multiple commands from a script file.

```sh
n98-magerun2.phar script [options] [--] [<filename>]
```
**Arguments:**
| Argument   | Description |
|------------|-------------|
| `filename` | Script file |
**Options:**
| Option               | Description                                  |
|----------------------|----------------------------------------------|
| `-d, --define[=DEFINE]` | Defines a variable (multiple values allowed) |
| `--stop-on-error`    | Stops execution of script on error           |


**Example:**

```sh
# Set multiple config
config:store:set "web/cookie/cookie_domain" example.com

# Set with multiline values with `\n`
config:store:set "general/store_information/address" "First line\nSecond line\nThird line"

# This is a comment
cache:flush
```

Optionally you can work with unix pipes.

```sh
echo "cache:flush" | n98-magerun2.phar script
```

```sh
n98-magerun2.phar script < filename
```

It is even possible to create executable scripts:

Create file `test.magerun` and make it executable `chmod +x test.magerun`:

```sh
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
```

**Pre-defined variables:**

| Variable             | Description                                |
|----------------------|--------------------------------------------|
| `${magento.root}`    | Magento Root-Folder                        |
| `${magento.version}` | Magento Version i.e. 2.0.0.0               |
| `${magento.edition}` | Magento Edition -> Community or Enterprise |
| `${magerun.version}` | Magerun version i.e. 2.1.0                 |
| `${php.version}`     | PHP Version                                |
| `${script.file}`     | Current script file path                   |
| `${script.dir}`      | Current script file dir                    |

Variables can be passed to a script with "--define (-d)" option.

Example:

```sh
n98-magerun2.phar script -d foo=bar filename

# This will register the variable ${foo} with value bar.
```

It's possible to define multiple values by passing more than one
option.

Environment variables can be used in a script by using the `env.` prefix.

Example:

```bash
!echo "My current working directory is: ${env.PWD}"
!echo "Path: ${env.PATH}"
```

## Toggle CMS Block status

Toggles the status for a CMS block based on the given Block identifier.

```sh
n98-magerun2.phar cms:block:toggle <blockId>
```
**Arguments:**
| Argument  | Description      |
|-----------|------------------|
| `blockId` | Block identifier |


## Change Admin user status

Changes the admin user based on the options, the command will toggle
the status if no options are supplied.

```sh
n98-magerun2.phar admin:user:change-status [options] [--] <user>
```
**Arguments:**
| Argument | Description                             |
|----------|-----------------------------------------|
| `user`   | Username or email for the admin user    |
**Options:**
| Option        | Description        |
|---------------|--------------------|
| `--activate`  | Activate the user  |
| `--deactivate`| Deactivate the user|


*Note: It is possible for a user to exist with a username that matches
the email of a different user. In this case the first matched user will be changed.*

## Add Sales Sequences for a given store

Create sales sequences in the database if they are missing, this will recreate profiles to.

```sh
n98-magerun2.phar sales:sequence:add [<store>] 
```
**Arguments:**
| Argument | Description        |
|----------|--------------------|
| `store`  | The store code or ID |


If store is omitted, it'll run for all stores.

*Note: It is possible a sequence already exists, in this case nothing will happen, only missing tables are created.*

## Remove Sales Sequences for a given store

Remove sales sequences from the database, warning, you cannot undo this, make sure you have database backups.

```sh
n98-magerun2.phar sales:sequence:remove [<store>] 
```
**Arguments:**
| Argument | Description        |
|----------|--------------------|
| `store`  | The store code or ID |


If store is omitted, it'll run for all stores. When the option `no-interaction` is given, it will run immediately
without any interaction.
Otherwise it will remind you and ask if you know what you're doing and ask for each store you are running it on.

*Note: .*

## Script Repository

You can organize your scripts in a repository.
Simply place a script in folder `/usr/local/share/n98-magerun2/scripts` or in your home dir
in folder `<HOME>/.n98-magerun2/scripts`.

Scripts must have the file extension *.magerun*.

After that you can list all scripts with the *script:repo:list* command.
The first line of the script can contain a comment (line prefixed with #) which will be displayed as description.

```sh
n98-magerun2.phar script:repo:list [--format[="..."]]
```
**Options:**
| Option             | Description                                          |
|--------------------|------------------------------------------------------|
| `--format[=FORMAT]` | Output Format. One of [csv,json,json_array,yaml,xml] |


If you want to execute a script from the repository this can be done by *script:repo:run* command.

```sh
n98-magerun2.phar script:repo:run [-d|--define[="..."]] [--stop-on-error] [<script>]
```
**Arguments:**
| Argument | Description                |
|----------|----------------------------|
| `script` | Name of script in repository |
**Options:**
| Option                   | Description                                  |
|--------------------------|----------------------------------------------|
| `-d, --define[=DEFINE]`  | Defines a variable (multiple values allowed) |
| `--stop-on-error`        | Stops execution of script on error           |


Script argument is optional. If you don't specify any you can select one from a list.

## Composer Redeploy Base Packages

If files are missing after a Magento updates it could be that new files were added to the files map in the base packages
of Magento. The `composer:redeploy-base-packages` command can fix this issue.

```sh
n98-magerun2.phar composer:redeploy-base-packages
```

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
