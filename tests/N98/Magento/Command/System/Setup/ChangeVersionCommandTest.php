<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Setup;

use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ChangeVersionCommandTest extends TestCase
{
    /**
     * @var ChangeVersionCommand;
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * Set up the test dependencies
     */
    protected function setUp(): void
    {
        $application = $this->getApplication();
        $application->add(new ChangeVersionCommand());

        $this->command = $this->getApplication()->find('sys:setup:change-version');

        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * Ensure that the version for a random Magento module can be changed
     */
    public function testShouldExecuteCorrectlyAndDisplaySuccessMessage()
    {
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'module'  => 'magento_customer',
                'version' => '2.0.0',
            ]
        );

        $this->assertStringContainsString(
            'Successfully updated: "Magento_Customer"',
            $this->commandTester->getDisplay()
        );
    }

    /**
     * Ensure an exception is thrown when the module doesn't exist
     */
    public function testExecuteShouldThrowExceptionWhenModuleDoesntExist()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'module'  => 'non_existent_module',
                'version' => '2.0.0',
            ]
        );
    }
}
