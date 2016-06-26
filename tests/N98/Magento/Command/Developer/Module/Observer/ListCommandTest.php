<?php
namespace N98\Magento\Command\Developer\Module\Observer;

use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

class ListCommandTest extends TestCase
{
    const GLOBAL_EVENT_NAME = 'customer_address_save_before';
    const CRONTAB_OBSERVER_NAME = 'cron_observer';

    /**
     * @var $command ListCommand
     */
    protected $command = null;

    public function setUp()
    {
        $application = $this->getApplication();
        $application->add(new ListCommand);

        $this->command = $this->getApplication()->find('dev:module:observer:list');
    }

    public function testGlobalList()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array('command' => $this->command->getName(), 'area' => 'global'));

        $this->assertContains(self::GLOBAL_EVENT_NAME, $commandTester->getDisplay());
    }

    public function testCrontabList()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array('command' => $this->command->getName(), 'area' => 'crontab'));

        $this->assertContains(self::CRONTAB_OBSERVER_NAME, $commandTester->getDisplay());
    }
}
