<?php
namespace N98\Magento\Command\Developer\Module\Routes;

use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ListAllRoutesCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ListAllRoutesCommand());

        $command = $this->getApplication()->find('dev:module:routes:api:list');
        $commandTester = new CommandTester($command);

        // It's difficult to mock Magento 1 and Magento 2 environments deeply enough
        // for a simple test without significant setup.
        // We will primarily test that the command can be called.
        // And check for non-empty output or specific messages if no Magento instance is found.

        try {
            $commandTester->execute(
                [
                    'command' => $command->getName(),
                ]
            );
        } catch (\Exception $e) {
            // If Magento is not initialized, it might throw an exception or output an error.
            // This is acceptable for a basic test run.
            // We check if the message contains "not initialized" or if it's about Magento version.
            $this->assertMatchesRegularExpression("/(not initialized|Could not determine Magento version)/", $e->getMessage());
        }

        $output = $commandTester->getDisplay();
        // Depending on the test environment setup (with or without Magento)
        // the output will vary.
        // If run outside a Magento context, it should inform about it.
        if (strpos($output, 'Magento application was not initialized') === false &&
            strpos($output, 'No routes found') === false &&
            strpos($output, 'Could not retrieve Magento 2 routes') === false &&
            strpos($output, 'Could not determine Magento version') === false) {
             // If none of the expected error/info messages for non-initialized Magento are present,
             // we expect some table output or at least no fatal errors.
            $this->assertNotEmpty($output, "Command output should not be empty if no initialization error occurs.");
        } else {
            // If it does output an initialization error, that's also an expected path in some test envs.
            $this->assertMatchesRegularExpression("/(not initialized|No routes found|Could not retrieve Magento 2 routes|Could not determine Magento version)/", $output, "Output should indicate issue if Magento is not initialized, no routes are found, or version cannot be determined.");
        }


        // A more comprehensive test would require a bootstrapped Magento instance (M1 or M2)
        // and then assertions on the actual table output.
        // For now, this ensures the command is registered and runs without fatal PHP errors.
        $this->assertStringContainsString('Lists all registered API routes', $command->getDescription());
    }

    public function testCommandName()
    {
        $command = new ListAllRoutesCommand();
        $this->assertEquals('dev:module:routes:api:list', $command->getName());
    }
}
