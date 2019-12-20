<?php

namespace N98\Magento\Command\Config\Data;

use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AclCommandTest extends TestCase
{
    /**
     * @test
     * @outputBuffering off
     */
    public function itShouldLoadGlobalConfig()
    {
        $command = $this->getApplication()->find('config:data:acl');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => 'config:data:acl',
            ]
        );

        $this->assertContains('All Stores', $commandTester->getDisplay());
        $this->assertContains('Magento_Reports::salesroot_sales', $commandTester->getDisplay());
    }
}
