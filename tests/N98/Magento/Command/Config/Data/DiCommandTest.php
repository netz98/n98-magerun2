<?php

namespace N98\Magento\Command\Config\Data;

use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DiCommandTest extends TestCase
{
    /**
     * @test
     * @outputBuffering off
     */
    public function itShouldLoadPrimaryConfig()
    {
        $command = $this->getApplication()->find('config:data:di');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => 'config:data:di',
            ]
        );

        $this->assertContains('LoggerInterface', $commandTester->getDisplay());
    }

    /**
     * @test
     * @outputBuffering off
     */
    public function itShouldLoadGlobalConfigScope()
    {
        $command = $this->getApplication()->find('config:data:di');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => 'config:data:di',
                '--scope' => 'global',
            ]
        );

        $this->assertContains('Magento\Catalog\Api\Data\ProductInterface', $commandTester->getDisplay());
    }

    /**
     * @test
     * @outputBuffering off
     */
    public function itShouldLoadFrontendConfigScope()
    {
        $command = $this->getApplication()->find('config:data:di');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => 'config:data:dump',
                '--scope' => 'frontend',
                'type'    => '\Magento\Framework\App\FrontControllerInterface',
            ]
        );

        $this->assertContains('Magento\Framework\App\FrontController', $commandTester->getDisplay());
    }
}
