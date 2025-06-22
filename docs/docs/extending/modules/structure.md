---
title: Module Structure
---

## Module Structure

A module is a folder with at least a config file with the name `n98-magerun2.yaml`.
Inside your config you can define a command by using the same structure as defining a single custom command.
See [[Add-custom-commands]].

:::tip

You can find a complete example module at [https://github.com/netz98/n98-magerun2-example-module](https://github.com/netz98/n98-magerun2-example-module).

:::

Example n98-magerun2.yaml:

```yaml
autoloaders_psr4:
  MyNamespace\: %module%/src

commands:
  customCommands:
    - MyNamespace\FooCommand
    - MyNamespace\BarCommand
```

%module% will be replaced with your current module folder path. It's not possible to place modules inside of modules.

```text
.
└── test-module
    ├── n98-magerun2.yaml
    └── src
          ├── BarCommand.php
          └── FooCommand.php

```

Example Command:

```php
<?php

namespace MyNamespace;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FooCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
      $this
          ->setName('mynamespace:foo')
          ->setDescription('Test command registered in a module')
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
           // .. do something 
        }
    }
}
```
