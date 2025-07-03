<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

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

        $this->assertStringContainsString('All Stores', $commandTester->getDisplay());
        $this->assertStringContainsString('Magento_Reports::salesroot_sales', $commandTester->getDisplay());
    }
}
