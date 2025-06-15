---
title: dev:console
---
The `dev:console` command is a powerful feature of the `n98-magerun2` command-line tool, designed to simplify common development and debugging tasks within Magento 2. It provides an interactive PHP shell, offering direct access to your Magento application's internals.

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

:::tip
Use area codes like `adminhtml` or `crontab` to load specific Magento configurations for your session.
:::

Possible area codes are:

- `adminhtml`
- `crontab`

## Executing Code

:::note
You can enter PHP code directly in the console. This is useful for quick calculations or inspecting objects. This allows for immediate execution of any valid PHP code, making it easy to test simple logic or functions.
:::

Example code:

```php
1+1;

time();
```

### Object Manager

We also bootstrap your current Magento store and register the Magento "Object Manager" inside the console.
The "Object Manager" can be accessed by a registered variable `$di`.

:::info
The `$di` variable gives you direct access to Magento's dependency injection container, allowing you to fetch any service or model.
:::

```php
$page = $di->get('Magento\Cms\Api\PageRepositoryInterface');
```

With this feature you are able to run short tests in your development or production environment.

```php
// Example: Load product information (inspired by the n98-magerun2 video)
$productRepository = $di->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
try {
    $product = $productRepository->get('joust-duffel-bag');
    echo "Product Name: " . $product->getName() . "\n";
    echo "Price: " . $product->getPrice() . "\n";
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    echo "Product not found: joust-duffel-bag\n";
}
```

## Benefits and Use Cases

The `dev:console`, as part of `n98-magerun2`, offers several advantages for Magento developers:

- **Rapid Data Retrieval:** Quickly fetch and inspect specific records or data from the database.
- **Code Snippet Testing:** Easily test small pieces of Magento or PHP code in isolation before integrating them into larger modules.
- **Scratchpad for Development:** Use it as a scratchpad to experiment with code ideas or debug logic step-by-step.
- **Effective in Ephemeral Environments:** Particularly useful in environments like Magento Cloud or other containerized/ephemeral hosting solutions where direct file system access or a full IDE might be limited.
- **Fast and Accessible:** The `n98-magerun2` tool itself is known for being fast, free, and open-source.
- **Broad Compatibility:** It has a long history of support and maintains compatibility with a wide range of Magento and PHP versions.

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

## Video Demonstration

For a practical demonstration of the `dev:console` and its capabilities with `n98-magerun2`, check out the following video:

- **Debug Magento 2 with this free, open source dev console:** [Watch on YouTube](https://youtu.be/teqHKYpz8dE?si=6_Vj-UBM2P6eYqtf)

This video showcases many of the features discussed, including live examples of using the console for debugging and development tasks.

**Video by Mark Shust.**

## Known Issues

We also work on support for switching the app area. Currently only the "global" area is loaded.
In some cases (errors) we loose the context of the module.
