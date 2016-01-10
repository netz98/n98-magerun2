<?php

namespace N98\Magento\Command\Developer\Console;

use N98\Magento\Command\PHPUnit\TestCase as BaseTestCase;
use Psy\Context;
use Symfony\Component\Console\Tester\CommandTester;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param AbstractConsoleCommand $command
     * @return CommandTester
     */
    public function createCommandTester(AbstractConsoleCommand $command)
    {
        $di = $this->getApplication()->getObjectManager();

        $command->setContext(new Context());
        $command->setScopeVariable('di', $di);

        $commandTester = new CommandTester($command);

        return $commandTester;
    }
}