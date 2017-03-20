<?php

namespace N98\Magento\Command\Config\Data;

use Magento\Deploy\Model\Mode;
use Magento\Framework\App\State;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;

class DiCommandTest extends TestCase
{
    /**
     * @return bool
     */
    private function runsInProductionMode()
    {
        $objectManager = $this->getApplication()->getObjectManager();
        $mode = $objectManager->create(
            Mode::class,
            [
                'input'  => new ArgvInput(),
                'output' => new ConsoleOutput(),
            ]
        );

        return $mode->getMode() === State::MODE_PRODUCTION;
    }

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
