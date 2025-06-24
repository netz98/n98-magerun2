---
title: Add Custom Commands
---

You can assign your own Symfony2 commands to `n98-magerun2`.

1. Add the folder which contains your custom commands to the autoloader.
2. Register Commands

The config file must be placed in your *home directory* with the name **~/.n98-magerun2.yaml**.

It's also possible (since version 1.36.0) to place a config in your magento installation to add custom project based command. Create config and save it as **app/etc/n98-magerun2.yaml**. The project config can use the variable **%root%** to get magento root folder dynamically.

Since version 1.72.0 it's possible to structure commands in modules.
See [[Modules]].

Config
------
```yaml
autoloaders_psr4:
  MyCommandNamespace\: /home/myuser/lib

  # or in project based config i.e.: 
  # MyCommandNamespace\: %root%/lib

# old deprecated psr-0
autoloaders:
  # Namespace => path to your libs
  MyCommandNamespace: /home/myuser/lib

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

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ProductMetadataInterface; // Example Magento class

class MyInjectedCommand extends AbstractMagentoCommand
{
    /** @var ProductMetadataInterface */
    protected $productMetadata;

    protected function configure()
    {
        $this
            ->setName('my:injected:command')
            ->setDescription('A command that uses injected Magento dependencies');
    }

    /**
     * Inject Magento dependencies.
     *
     * @param ProductMetadataInterface $productMetadata
     */
    public function inject(ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $this->detectMagento($output); // Called by injectObjects before inject()
        // if ($this->initMagento()) {   // Called by injectObjects before inject()
            if ($this->productMetadata) {
                $output->writeln('Successfully injected ProductMetadataInterface.');
                $output->writeln('Magento Version: ' . $this->productMetadata->getVersion());
            } else {
                $output->writeln('<error>Failed to inject ProductMetadataInterface.</error>');
            }
        // }
    }
}
```

In this example:
1. We type-hint the `ProductMetadataInterface` in the `inject` method's signature.
2. The `InjectionHelper` (used by `AbstractMagentoCommand::injectObjects`) will use the Magento `ObjectManager` to create an instance of `ProductMetadataInterface` and pass it to the `inject` method.
3. We then store the injected instance in a class property (`$this->productMetadata`) for use in the `execute` method.

The `detectMagento()` and `initMagento()` methods are automatically called by `AbstractMagentoCommand::injectObjects()` before your `inject()` method is executed, so you don't need to call them explicitly if you are using the `inject()` method.

Make sure that any classes you are trying to inject are available through Magento's ObjectManager.

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

*   **`initialize(InputInterface $input, OutputInterface $output): void`**
    *   This method is called by Symfony Console after the input has been validated but before the `execute` method.
    *   It's useful for setting up properties based on input arguments and options.
    *   By default, it calls `checkDeprecatedAliases`.

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

*   **`run(InputInterface $input, OutputInterface $output): int`**
    *   This is the main method called by Symfony Console to execute your command.
    *   It first calls `injectCommandToHelpers()` and then `injectObjects(OutputInterface $output)`.
    *   `injectObjects()` is responsible for calling your command's `inject()` method (if it exists) after initializing Magento.
    *   Finally, it calls `parent::run()`, which in turn calls your command's `execute()` method.

*   **`injectObjects(OutputInterface $output): void`**
    *   This method orchestrates the dependency injection process.
    *   It first calls `detectMagento()` and `initMagento()`.
    *   Then, if your command class has a public method named `inject`, it uses `InjectionHelper` to resolve and pass dependencies to it using Magento's `ObjectManager`.

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

## Enterprise Edition Only Commands

Add isEnable method to your command.

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
