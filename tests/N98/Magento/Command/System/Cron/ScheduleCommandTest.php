<?php

namespace N98\Magento\Command\System\Cron;

use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ScheduleCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ListCommand());
        $command = $this->getApplication()->find('sys:cron:schedule');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'job'     => 'backend_clean_cache',
        ]);

        $this->assertContains('Scheduling Magento\Backend\Cron\CleanCache::execute done', $commandTester->getDisplay());
    }
}
