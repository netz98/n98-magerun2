<?php

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
    public function setUp()
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

        $this->assertContains(
            'Successfully updated: "Magento_Customer"',
            $this->commandTester->getDisplay()
        );
    }

    /**
     * Ensure an exception is thrown when the module doesn't exist
     * @expectedException InvalidArgumentException
     */
    public function testExecuteShouldThrowExceptionWhenModuleDoesntExist()
    {
        $this->commandTester->execute(
            [
                'command' => $this->command->getName(),
                'module'  => 'non_existent_module',
                'version' => '2.0.0',
            ]
        );
    }
}
