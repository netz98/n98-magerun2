---
title: Scripting Commands
---
### n98-magerun Script

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

### Script Repository

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
