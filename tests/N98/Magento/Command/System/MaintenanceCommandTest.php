<?php
namespace N98\Magento\Command\System;

use Magento\Framework\App\MaintenanceMode;
use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

class MaintenanceCommandTest extends TestCase
{
    /**
     * @var $command MaintenanceCommand
     */
    protected $command = null;

    protected $maintenanceFile;

    public function setUp()
    {
        $application = $this->getApplication();
        $application->add(new MaintenanceCommand);

        $this->command = $this->getApplication()->find('sys:maintenance');
        $this->maintenanceFile = $this->getApplication()->getMagentoRootFolder() . DIRECTORY_SEPARATOR . MaintenanceMode::FLAG_DIR . DIRECTORY_SEPARATOR . MaintenanceMode::FLAG_FILENAME;
    }

    public function testSimpleFlag()
    {
        if (file_exists($this->maintenanceFile)) {
            $this->simpleFlagDisable();
            $this->simpleFlagEnable();
        } else {
            $this->simpleFlagEnable();
            $this->simpleFlagDisable();
        }
    }

    public function testIpFlag()
    {
        if (file_exists($this->maintenanceFile)) {
            $this->ipFlagDisable();
            $this->ipFlagEnable();
        } else {
            $this->ipFlagEnable();
            $this->ipFlagDisable();
        }
    }

    protected function simpleFlagDisable()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['command' => $this->command->getName(), '--off']);

        $this->assertEquals(strip_tags(MaintenanceCommand::DISABLED_MESSAGE) . PHP_EOL, $commandTester->getDisplay());
    }

    protected function simpleFlagEnable()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['command' => $this->command->getName(), '--on']);

        $this->assertEquals(strip_tags(MaintenanceCommand::ENABLED_MESSAGE) . PHP_EOL, $commandTester->getDisplay());
    }

    protected function ipFlagDisable()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['command' => $this->command->getName(), '--off' => 'd']);

        $this->assertEquals(
            strip_tags(
                MaintenanceCommand::DISABLED_MESSAGE .
                PHP_EOL .
                MaintenanceCommand::DELETED_IP_MESSAGE .
                PHP_EOL
            ),
            $commandTester->getDisplay()
        );
    }

    protected function ipFlagEnable()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['command' => $this->command->getName(), '--on' => '127.0.0.1,127.0.0.1']);

        $this->assertEquals(
            strip_tags(
                MaintenanceCommand::ENABLED_MESSAGE .
                PHP_EOL .
                MaintenanceCommand::WROTE_IP_MESSAGE .
                PHP_EOL
            ),
            $commandTester->getDisplay()
        );
    }
}
