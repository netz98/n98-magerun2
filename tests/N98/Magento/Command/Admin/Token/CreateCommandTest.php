<?php

namespace N98\Magento\Command\Admin\Token;

use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CreateCommandTest
 * @package N98\Magento\Command\Admin\Token
 */
class CreateCommandTest extends TestCase
{
    public function testExecute()
    {
        $command = $this->getApplication()->find('admin:token:create');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'username'     => 'admin',
            '--no-newline' => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertNotEmpty($output);
        // Magento token output can be 32 (older) or 151 characters (newer/verbose).
        $this->assertContains(strlen($output), [32, 151], "Token length should be either 32 or 151 characters.");
    }
}
