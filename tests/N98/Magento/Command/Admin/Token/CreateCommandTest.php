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

        // can be 32 in older version or > 150 in newer versions (JWT tokens)
        $this->assertGreaterThanOrEqual(
            32,
            strlen($output),
            'Token length should be at least 32 characters'
        );
    }
}
