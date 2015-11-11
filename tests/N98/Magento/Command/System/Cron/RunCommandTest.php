<?php

namespace N98\Magento\Command\System\Cron;

use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

class RunCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ListCommand());
        $command = $this->getApplication()->find('sys:cron:run');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'job'     => 'backend_clean_cache'
        ]);
    
        $this->assertContains('Run Magento\Backend\Cron\CleanCache::execute done', $commandTester->getDisplay());
    }
}
