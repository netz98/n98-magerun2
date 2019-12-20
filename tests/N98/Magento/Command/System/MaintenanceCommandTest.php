<?php

namespace N98\Magento\Command\System;

use Magento\Framework\App\MaintenanceMode;
use N98\Magento\Command\TestCase;

class MaintenanceCommandTest extends TestCase
{
    /**
     * @var $command MaintenanceCommand
     */
    protected $command = null;

    protected $maintenanceFile;

    public function setUp()
    {
        $this->maintenanceFile =
            $this->getApplication()->getMagentoRootFolder() .
            '/' . MaintenanceMode::FLAG_DIR .
            '/' . MaintenanceMode::FLAG_FILENAME;
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
        $this->assertDisplayContains(
            ['command' => 'sys:maintenance', '--off'],
            strip_tags(MaintenanceCommand::DISABLED_MESSAGE)
        );
    }

    protected function simpleFlagEnable()
    {
        $this->assertDisplayContains(
            ['command' => 'sys:maintenance', '--on'],
            strip_tags(MaintenanceCommand::ENABLED_MESSAGE)
        );
    }

    protected function ipFlagDisable()
    {
        $this->assertDisplayContains(
            ['command' => 'sys:maintenance', '--off' => 'd'],
            strip_tags(
                MaintenanceCommand::DISABLED_MESSAGE . PHP_EOL .
                MaintenanceCommand::DELETED_IP_MESSAGE . PHP_EOL
            )
        );
    }

    protected function ipFlagEnable()
    {
        $this->assertDisplayContains(
            ['command' => 'sys:maintenance', '--on' => '127.0.0.1,127.0.0.1'],
            strip_tags(
                MaintenanceCommand::ENABLED_MESSAGE . PHP_EOL .
                MaintenanceCommand::WROTE_IP_MESSAGE . PHP_EOL
            )
        );
    }
}
