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
