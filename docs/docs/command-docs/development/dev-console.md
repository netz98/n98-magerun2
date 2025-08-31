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
| `-s, --single-process`| Run without forking (single process) |

Optional an area code can be defined. If provided, the configuration (di.xml, translations) of the area are loaded.

:::warning
The `--single-process` flag disables process forking and should only be used for database transaction edge cases. It is not recommended for daily work.
:::

:::tip
Use area codes like `adminhtml` or `crontab` to load specific Magento configurations for your session.
:::

Possible area codes are:

- `adminhtml`
- `crontab`

:::info
The idea behind the way the `make` commands works was invented by [Jacques Bodin-Hullin](https://monsieurbiz.com). Special thanks for the inspiration!
:::

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
$productRepository = $di->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$product = $productRepository->get('24-MB01');
$product->dump();
```
### Development Helper (`$dh`)

When `dev:console` starts, both `$di` and `$dh` variables are predefined. `$dh` contains an instance of `\N98\Magento\Command\Developer\DevelopmentHelper` and provides shortcut methods for common debugging tasks.

| Method | Description |
| ------ | ----------- |
| `debugProductBySku($sku, $storeId = 0)` | Load and dump a product by SKU. |
| `getProductRepository()` | Returns the product repository. |
| `debugProductById($id, $storeId = 0)` | Load and dump a product by ID. |
| `debugCategoryById($id, $storeId = 0)` | Dump a category by ID. |
| `getCategoryRepository()` | Returns the category repository. |
| `debugOrderById($id)` | Dump an order by ID. |
| `getOrderRepository()` | Returns the order repository. |
| `debugCustomerById($id)` | Dump a customer by ID. |
| `debugCustomerByEmail($email, $websiteId = 0)` | Dump a customer by e-mail. |
| `getCustomerRepository()` | Returns the customer repository. |
| `getCustomerModel()` | Returns the customer model. |
| `debugCartById($cartId)` | Dump a cart by ID. |
| `getCartRepository()` | Returns the cart repository. |
| `getStoreManager()` | Returns the store manager. |
| `createProductModel()` | Creates a product model instance. |
| `createCustomerModel()` | Creates a customer model instance. |
| `getScopeConfig()` | Returns the scope configuration. |
| `getEavAttributeRepository()` | Returns the EAV attribute repository. |
| `getCmsBlockRepository()` | Returns the CMS block repository. |
| `getCmsPageRepository()` | Returns the CMS page repository. |
| `getDatabaseConnection()` | Returns the database connection. |

### Example Usage of `$dh`

#### Loading and Dumping Product Data

Dump product data by SKU:

```php
$dh->debugProductBySku('24-MB01');
```

Load product by ID and dump its data:

```php
$dh->debugProductById(123);
```

Load the product repository and fetch a product:

```php
$productRepository = $dh->getProductRepository();
$product = $productRepository->get('24-MB01');
$product->dump();
```

#### Dump Cart (Quote) Data

Dump cart data by ID:

```php
$dh->debugCartById('1234');
```

#### Dump Order Data

Dump order data by ID:

```php
$dh->debugOrderById(4711);
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

| Command | Description |
| ------- | ----------- |
| `make:config:di` | Creates a new `di.xml` file |
| `make:config:crontab` | Creates a new `crontab.xml` file |
| `make:config:events` | Creates a new `events.xml` file |
| `make:config:fieldset` | Creates a new `fieldset.xml` file |
| `make:config:menu` | Creates a new `menu.xml` file |
| `make:config:routes` | Creates a new `routes.xml` file |
| `make:config:system` | Creates a new `system.xml` file |
| `make:config:widget` | Creates a new `widget.xml` file |
| `make:config:webapi` | Creates a new `webapi.xml` file |
| `module` | Set current module context |
| `make:block` | Creates a generic block class |
| `make:helper` | Creates a helper class |
| `make:module` | Creates a new module (prompts for module name if omitted) |
| `modules` | List all modules |
| `make:class` | Creates a generic class |
| `make:command` | Creates a CLI command |
| `make:controller` | Creates a controller action class |
| `make:model` | Creates a model class |
| `make:interface` | Creates a generic interface |
| `make:theme` | Creates a new theme |
The idea is that you can create a new module with `make:module` command or switch in the context of an existing module with the `module` command.
Inside this context it's possible to generate config files, classes, etc

## Video Demonstration

For a practical demonstration of the `dev:console` and its capabilities with `n98-magerun2`, check out the following video:

- **Debug Magento 2 with this free, open source dev console:** [Watch on YouTube](https://youtu.be/teqHKYpz8dE?si=6_Vj-UBM2P6eYqtf)

This video showcases many of the features discussed, including live examples of using the console for debugging and development tasks.

**Video by Mark Shust.**


## dev:console Deep Dive

The dev:console command provides an interactive PHP shell with full Magento integration, making it an invaluable tool for rapid debugging, one-off code execution, and complex data manipulation directly from the command line.
Key Capabilities:
Interactive PHP Shell: Drop directly into a PHP environment with Magento's bootstrap loaded, allowing immediate execution of PHP code within the Magento context.
Full Magento Integration: Commands are carried out extremely fast due to local integration with Magento.
Direct Object Manager Access: The special $di (or $dh for Development Helper) variable provides direct access to Magento's Object Manager, enabling easy creation and manipulation of objects with dependency injection.
Example:

```php
$product = $di->create(\Magento\Catalog\Model\Product::class)->load(123);
```

Debugging Functions with `$dh`: The $dh (Development Helper) variable offers convenient debugging functions for common tasks, useful even in production environments.
One-off Commands & Code Testing: Ideal for running quick, one-time commands or testing code snippets without needing to create controllers or temporary files.
Data Inspection & Manipulation: Quickly retrieve specific records, inspect full product details, attributes, media gallery images, quantities, and more.
Area Initialization: The dev:console can be initialized in different Magento application areas using the --area option.

Example: Inspecting Product Data

```php
# In dev:console
$product = $dh->loadProductBySku('24-MB01'); # Using a helper function
```
## Known Issues

We also work on support for switching the app area. Currently only the "global" area is loaded.
In some cases (errors) we lose the context of the module.
