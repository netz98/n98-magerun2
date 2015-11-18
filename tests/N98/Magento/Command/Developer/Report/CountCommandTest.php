<?php

namespace N98\Magento\Command\Developer\Report;

use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

class CountCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new CountCommand());
        $command = $this->getApplication()->find('dev:report:count');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $this->assertTrue(is_numeric(trim($commandTester->getDisplay())));
    }
}
