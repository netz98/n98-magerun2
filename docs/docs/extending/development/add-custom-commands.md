---
title: Add Custom Commands
---

You can assign your own Symfony2 commands to `n98-magerun2`.

1. Add the folder which contains your custom commands to the autoloader.
2. Register Commands

The config file must be placed in your *home directory* with the name **~/.n98-magerun2.yaml**.

It's also possible (since version 1.36.0) to place a config in your magento installation to add custom project based command. Create config and save it as **app/etc/n98-magerun2.yaml**. The project config can use the variable **%root%** to get magento root folder dynamically.

You can organize custom commands into modules to better structure your code (available since version 1.72.0).
For more details, see the [Modules documentation](../modules/index.md).

## Config

```yaml
autoloaders_psr4:
  MyCommandNamespace\: %module%/src

  # or in project based config i.e.:
  # MyCommandNamespace\: %root%/lib

# old deprecated psr-0
autoloaders:
  # Namespace => path to your libs
  MyCommandNamespace: src

commands:  
  customCommands:
    - MyCommandNamespace\FooCommand

  # optional command config
  MyCommandNamespace\FooCommand:
    bar: zoz
```

## Example Command

### Injecting Magento Dependencies

If your custom command needs to use Magento classes, you can use the `inject` method for dependency injection. This method is called by `AbstractMagentoCommand::injectObjects` after `detectMagento` and `initMagento` have been successfully executed.

The `injectObjects` method, in turn, is called at the beginning of the `run` method of the `AbstractMagentoCommand`.

Here's an example of how to use the `inject` method:

```php
<?php

namespace MyCommandNamespace;

use Magento\Catalog\Api\ProductRepositoryInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MyExampleCommand extends AbstractMagentoCommand
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    protected function configure()
    {
        $this
            ->setName('my:example')
            ->setDescription('An example command that uses Magento dependencies');
            ->addArgument(
                'sku',
                InputArgument::REQUIRED,
                'The sku of the product to retrieve'
            );
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @return void
     */
    public function inject(ProductRepositoryInterface $productRepository): void
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $product = $this->productRepository->get($input->getArgument('sku'));
        
        // dumper output of product data
        $output->writeln('Product ID: ' . $product->getId());
        $output->writeln('Product Name: ' . $product->getName());
        $output->writeln('Product SKU: ' . $product->getSku());
        
        return self::SUCCESS;
    }
}

```

In this example:
1. We type-hint the `ProductRepositoryInterface` in the `inject` method's signature.
2. The internal `InjectionHelper` (used by `AbstractMagentoCommand::injectObjects`) will use the Magento `ObjectManager` to create an instance of `ProductRepositoryInterface` and pass it to the `inject` method.
3. We then store the injected instance in a class property (`$this->productRepository`) for use in the `execute` method.

The `detectMagento()` and `initMagento()` methods are automatically called by `AbstractMagentoCommand::injectObjects()` before your `inject()` method is executed, so you don't need to call them explicitly if you are using the `inject()` method.

Make sure that any classes you are trying to inject are available through Magento's ObjectManager.

:::note
We do not use the constructor injection pattern. This is because with the constructor injection all objects would be created for all commands, just to list them. This would lead to performance issues, especially if the command is not executed.
:::

### Command Configuration

You can also define configuration for your command in the `n98-magerun2.yaml` file. This allows you to set options or parameters that your command can access.

```yaml
commands:
  MyCommandNamespace\MyInjectedCommand:
    bar: zoz
```

In the code example above, you can access the `bar` configuration in your command by using `$this->getCommandConfig()['bar']` after the `detectMagento()` and `initMagento()` calls.

```php
public function execute(InputInterface $input, OutputInterface $output)
{
    $barValue = $this->getCommandConfig()['bar'] ?? 'default_value';
    $output->writeln('Bar value: ' . $barValue);
}
```

### Basic Command Structure

Here is a basic example of a command:

```php
<?php

namespace MyCommandNamespace;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FooCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('foo')            
            ->setDescription('Foo test command')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {
            var_dump($this->getCommandConfig());
            $output->writeln(__DIR__ . ' -> ' . __CLASS__);
        }
    }
}
```

## Run an existing command in a command

```php
<?php

$input = new StringInput('cache:flush');

// ensure that n98-magerun2 doesn't stop after first command
$this->getApplication()->setAutoExit(false);

// without output
$this->getApplication()->run($input, new NullOutput());

// with output
$this->getApplication()->run($input, $output);

// reactivate auto-exit
$this->getApplication()->setAutoExit(true);
```

## Important Methods in `AbstractMagentoCommand`

When extending `AbstractMagentoCommand`, you have access to several helpful methods:

*   **`getObjectManager(): ObjectManagerInterface`**
    *   Returns an instance of Magento's `ObjectManagerInterface`.
    *   This is your gateway to instantiating Magento classes if you are not using the `inject()` method.
    *   Requires Magento to be initialized (`initMagento()`).

*   **`getCommandConfig(string $commandClass = null): array`**
    *   Retrieves command-specific configuration from the `n98-magerun2.yaml` files.
    *   If `$commandClass` is null, it fetches the configuration for the current command.

*   **`writeSection(OutputInterface $output, string $text, string $style = 'bg=blue;fg=white'): void`**
    *   A helper method to output a formatted block of text to the console.
    *   Useful for displaying titles or important messages.

*   **`initMagento(): bool`**
    *   Bootstraps the Magento application. This is necessary to use most Magento functionalities.
    *   It's automatically called by `injectObjects()` if your command has an `inject()` method. If not, and you need Magento services, you'll typically call this after `detectMagento()`.

*   **`detectMagento(OutputInterface $output, bool $silent = true): bool`**
    *   Locates the Magento root directory.
    *   Sets internal properties like `_magentoRootFolder`, `_magentoEnterprise`, and `_magentoMajorVersion`.
    *   Throws a `RuntimeException` if Magento cannot be found.
    *   It's automatically called by `injectObjects()` if your command has an `inject()` method.

*   **`createSubCommandFactory(InputInterface $input, OutputInterface $output, string $baseNamespace = ''): SubCommandFactory`**
    *   Useful for creating and managing sub-commands within your command.

*   **`runsInProductionMode(InputInterface $input, OutputInterface $output): bool`**
    *   Checks the current Magento application mode.
    *   Returns `true` if Magento is in `production` mode, `false` otherwise.
    *   Requires Magento to be initialized.

## Get Magento Root-Folder

```php
$rootFolder = $this->getApplication()->getMagentoRootFolder();
```

## Get Magento Version

```php
$version = $this->getApplication()->getMagentoVersion();
```

## Dynamically Enable/Disable Commands

The ìsEnabled` method allows you to control whether your command should be available based on certain conditions, such as the Magento version or edition.

### Example with a Adobe Commerce (Magento Enterprise) only command

```php
<?php

/**
 * @return bool
 */
public function isEnabled()
{
    return $this->getApplication()->isMagentoEnterprise();
}
```

### Example with a Magento 2.4.8+ only command

```php
<?php

/**
 * @return bool
 */
public function isEnabled()
{
    return version_compare($this->getApplication()->getMagentoVersion(), '2.4.8', '>=');
}
```
