<?php

namespace N98\Magento\Command\Config\Data;

use N98\Magento\Command\PHPUnit\TestCase;
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
                'command' => 'di:dump',
                '--scope' => 'global',
                'type'    => 'Magento\Framework\App\Response\Http'
            )
        );

        $this->assertContains('Magento\Framework\App\Response\Http', $commandTester->getDisplay());
    }

    /**
     * @test
     * @outputBuffering off
     */
    public function itShouldLoadFrontendConfig()
    {
        $command = $this->getApplication()->find('di:dump');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => 'di:dump',
                '--scope' => 'frontend',
                'type'    => '\Magento\Framework\App\FrontControllerInterface'
            )
        );

        $this->assertContains('Magento\Framework\App\FrontControllerInterface', $commandTester->getDisplay());
    }
}
