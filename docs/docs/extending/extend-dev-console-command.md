---
title: How to Extend the dev:console Command
sidebar_label: Extend dev:console Command
---

# How to Extend the `dev:console` Command

The `dev:console` command in n98-magerun2 provides a powerful developer console for generating Magento 2 code and configuration files. You can easily extend this console by registering your own custom Dev Console commands.

This guide explains how to add your own commands to the Dev Console.

## Registering a New Dev Console Command

To register a new command for the Dev Console, you need to add your command class to the configuration under the `N98\Magento\Command\Developer\ConsoleCommand` section in your [configuration](../extending/configuration.md).

### Example Configuration

Open your config and add the following section:

```yaml
N98\Magento\Command\Developer\ConsoleCommand:
  commands:
    # ... add your own class names here
```

To add your own command, simply append your fully qualified class name to the `commands` list. For example:

```yaml
N98\Magento\Command\Developer\ConsoleCommand:
  commands:
    # ...
    - Vendor\Module\Console\MyCustomDevConsoleCommand
```

Your custom command class (e.g., `Vendor\Module\Console\MyCustomDevConsoleCommand`) must extend the appropriate base class, typically `N98\Magento\Command\Developer\Console\AbstractConsoleCommand`.

### Example: Custom Dev Console Command

```php
<?php
namespace Vendor\Module\Console;

use N98\Magento\Command\Developer\Console\AbstractConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MyCustomDevConsoleCommand extends AbstractConsoleCommand
{
    protected function configure()
    {
        $this
            ->setName('dev:console:my-custom')
            ->setDescription('My custom Dev Console command.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Hello from my custom Dev Console command!</info>');
        return 0;
    }
}
```

After registering your command, the new command should be listed in the dev:console REPL.

```bash
dev:console

> dev:console:my-custom
```

---

## Example: Simple Code Generator Make Command

You can also create your own code generator ("make") command for the Dev Console. This is useful for scaffolding files or code structures in your Magento 2 modules.

Below is a minimal example of a custom make command that generates a simple PHP class file using the code generator approach (see `N98\Magento\Command\Developer\Console\MakeClassCommand` for reference):

```php
<?php
namespace Vendor\Module\Console;

use Laminas\Code\Generator\FileGenerator;
use Magento\Framework\Code\Generator\ClassGenerator;
use N98\Magento\Command\Developer\Console\AbstractGeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeSimpleClassCommand extends AbstractGeneratorCommand
{
    const CLASSPATH = 'classpath';

    protected function configure()
    {
        $this
            ->setName('dev:console:make:simple-class')
            ->setDescription('Generates a simple PHP class file using the code generator')
            ->addArgument(self::CLASSPATH, InputArgument::REQUIRED, 'Class path (e.g. Foo/Bar)');
    }

    protected function catchedExecute(InputInterface $input, OutputInterface $output)
    {
        $classFileName = $this->getNormalizedPathByArgument($input->getArgument(self::CLASSPATH));
        $classNameToGenerate = $this->getCurrentModuleNamespace()
            . '\\'
            . $this->getNormalizedClassnameByArgument($input->getArgument(self::CLASSPATH));
        $filePathToGenerate = $classFileName . '.php';

        /** @var $classGenerator ClassGenerator */
        $classGenerator = $this->create(ClassGenerator::class);
        $classGenerator->setName($classNameToGenerate);

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);

        $directoryWriter = $this->getCurrentModuleDirectoryWriter();
        $directoryWriter->writeFile($filePathToGenerate, $fileGenerator->generate());

        $output->writeln('<info>Generated </info><comment>' . $filePathToGenerate . '</comment>');
    }
}
```

Register this command in your configuration as shown above. You can then use it in the Dev Console:

```bash
dev:console make:simple-class Foo/Bar
```

This will generate a file `Foo/Bar.php` in the current module directory with a minimal class definition using the code generator.

---

:::info
You can register multiple custom Dev Console commands by adding them to the `commands` array in the configuration.
:::
