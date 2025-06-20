<?php
namespace MyCompany\TestModule\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MyCompany\TestModule\Dummy\AutoloadTestClass;

class MyTestHelloCommand extends Command
{
    protected static $defaultName = 'mytest:hello';

    protected function configure()
    {
        $this->setDescription('A simple test command from an added module directory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $autoloaderTest = new AutoloadTestClass();
        $output->writeln('Hello from MyTestHelloCommand! Autoloaded: ' . $autoloaderTest->greet());
        return Command::SUCCESS;
    }
}
