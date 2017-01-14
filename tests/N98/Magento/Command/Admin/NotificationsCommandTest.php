<?php

namespace N98\Magento\Command\Admin;

use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class NotificationsCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new NotificationsCommand());
        $application->setAutoExit(false);
        $command = $this->getApplication()->find('admin:notifications');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--on'    => true,
            )
        );
        $this->assertRegExp('/Admin Notifications hidden/', $commandTester->getDisplay());

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                '--off'   => true,
            )
        );

        $this->assertRegExp('/Admin Notifications visible/', $commandTester->getDisplay());
    }
}
