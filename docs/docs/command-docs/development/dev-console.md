---
title: dev:console
---

We offer an interactive console based on [Psy Shell](http://psysh.org/).
The interactive console is a REPL (Read-Eval-Print-Loop).


```sh
n98-magerun2.phar dev:console [options] [--] [<cmd>]
```

**Arguments:**

| Argument | Description                |
|----------|----------------------------|
| `cmd`    | Direct code to run [default: ""] |

**Options:**

| Option           | Description                |
|------------------|----------------------------|
| `-a, --area=AREA`| Area to initialize         |
| `-e, --auto-exit`| Automatic exit after cmd   |

Optional an area code can be defined. If provided, the configuration (di.xml, translations) of the area are loaded.

Possible area codes are:

- `adminhtml`
- `crontab`

## Executing Code


It's possible to enter PHP code inside the console which should be parsed and executed.

Example code:

```php
1+1;

time();
```

### Object Manager

We also bootstrap your current Magento store and register the Magento "Object Manager" inside the console.
The "Object Manager" can be accessed by a registered variable `$di`.

```php
$page = $di->get('Magento\Cms\Api\PageRepositoryInterface');
```

With this feature you are able to run short tests in your development or production environment.

## Code Generator

We offer some commands which can be used to create boilerplate code or even a complete new module.

```
  make:config:di                    Creates a new di.xml file
  make:config:crontab               Creates a new crontab.xml file
  make:config:events                Creates a new events.xml file
  make:config:fieldset              Creates a new fieldset.xml file
  make:config:menu                  Creates a new menu.xml file
  make:config:routes                Creates a new routes.xml file
  make:config:system                Creates a new system.xml file
  make:config:widget                Creates a new widget.xml file
  make:config:webapi                Creates a new webapi.xml file
  module                            Set current module context
  make:block                        Creates a generic block class
  make:helper                       Creates a helper class
  make:module                       Creates a new module
  modules                           List all modules
  make:class                        Creates a generic class
  make:command                      Creates a cli command
  make:controller                   Creates a controller action class
  make:model                        Creates a model class
  make:interface                    Creates a generic interface
  make:theme                        Creates a new theme
```

The idea is that you can create a new module with `make:module` command or switch in the context of an existing module with the `module` command.
Inside this context it's possible to generate config files, classes, etc


## Know Issues

We also work on support for switching the app area. Currently only the "global" area is loaded.
In some cases (errors) we loose the context of the module.
