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
    public function itShouldLoadGlobalConfig()
    {
        $command = $this->getApplication()->find('config:data:di');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => 'config:data:di',
                '--scope' => 'global',
                'type'    => 'Psr\Log\LoggerInterface'
            )
        );

        $this->assertContains('preference', $commandTester->getDisplay());
    }

    /**
     * @test
     * @outputBuffering off
     */
    public function itShouldLoadFrontendConfig()
    {
        $command = $this->getApplication()->find('config:data:di');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => 'config:data:dump',
                '--scope' => 'frontend',
                'type'    => '\Magento\Framework\App\FrontControllerInterface'
            )
        );

        $this->assertContains('Magento\Framework\App\FrontController', $commandTester->getDisplay());
    }
}
