<?php

namespace N98\Magento\Application;

use N98\Magento\TestApplication;
use Symfony\Component\Console\Tester\ApplicationTester;

class AddModuleDirArgumentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestApplication
     */
    private $application;

    /**
     * @var ApplicationTester
     */
    private $tester;

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new TestApplication();
        $app = $this->application->getApplication();
        $app->setAutoExit(false);

        // Use the wrapped Symfony application for the tester
        $this->tester = new ApplicationTester($app);
    }

    public function testCommandAndAutoloadingFromAddedModuleDir()
    {
        // Path to the test module created in the previous step
        // Adjust if path is different, ensure it's relative to project root
        $modulePath = 'tests/_files/custom_module_test_add_dir';

        // First, check if the command is listed
        $this->tester->run(
            [
                '--add-module-dir' => $modulePath,
                'command' => 'list',
                //'--skip-magento-compatibility-check' => true, // May be needed if Magento isn't fully bootstrapped
                //'--skip-config' => true, // Avoid loading other configs that might interfere
            ],
            ['decorated' => false] // No decoration for easier string matching
        );

        $listOutput = $this->tester->getDisplay();
        $this->assertStringContainsString('mytest:hello', $listOutput, 'The command mytest:hello should be listed.');

        // Now, execute the command itself
        $this->tester->run(
            [
                '--add-module-dir' => $modulePath,
                'command' => 'mytest:hello',
                //'--skip-magento-compatibility-check' => true,
                //'--skip-config' => true,
            ],
            ['decorated' => false]
        );

        $commandOutput = $this->tester->getDisplay();
        $this->assertStringContainsString(
            'Hello from MyTestHelloCommand! Autoloaded: AutoloadTestClass says hi!',
            $commandOutput,
            'The output from mytest:hello command is not as expected.'
        );
        $this->assertSame(0, $this->tester->getStatusCode(), 'Command execution should be successful.');
    }

    public function testCommandFromMultipleAddedModuleDirs()
    {
        // For this test, we'd ideally have a second minimal test module.
        // For now, we can reuse the same one twice, though it doesn't prove distinct module loading fully.
        // A more robust test would involve creating another module, e.g., custom_module_test_add_dir_2
        // with a different command.
        // For simplicity in this step, we'll just pass the option multiple times with the same path.
        // The underlying code supports multiple paths, so this at least tests the option parsing.

        $modulePath1 = 'tests/_files/custom_module_test_add_dir';
        // $modulePath2 = 'tests/_files/custom_module_test_add_dir_2'; // if we had a second one

        $this->tester->run(
            [
                '--add-module-dir' => [$modulePath1, $modulePath1], // Pass as an array for multiple options
                'command' => 'mytest:hello',
            ],
            ['decorated' => false]
        );

        $commandOutput = $this->tester->getDisplay();
        $this->assertStringContainsString(
            'Hello from MyTestHelloCommand! Autoloaded: AutoloadTestClass says hi!',
            $commandOutput,
            'The output from mytest:hello command with multiple --add-module-dir options is not as expected.'
        );
        $this->assertSame(0, $this->tester->getStatusCode(), 'Command execution should be successful with multiple --add-module-dir options.');
    }

    public function testNonExistentModuleDir()
    {
        $nonExistentPath = 'tests/_files/non_existent_module_dir_for_test';

        // We expect the application to run without error, but our command should not be available.
        // The ConfigurationLoader should log a warning (if verbose) but not crash.
        $this->tester->run(
            [
                '--add-module-dir' => $nonExistentPath,
                'command' => 'list',
                '-v' => true, // Enable verbosity to check for warnings (optional for this assertion)
            ],
            ['decorated' => false]
        );

        $listOutput = $this->tester->getDisplay();
        // Check that the command is NOT listed
        $this->assertStringNotContainsString('mytest:hello', $listOutput, 'The command mytest:hello should NOT be listed when path is invalid.');

        // Check for the warning message (optional, depends on exact logging implementation)
        // This assertion might be fragile if the warning message changes.
        // $this->assertStringContainsString("Warning: --add-module-dir: Could not resolve path", $listOutput);
        // Or, check the warning from ConfigurationLoader:
        // $this->assertStringContainsString("Provided additional module path is not a valid directory: ".realpath($nonExistentPath), $listOutput);
        // For now, just ensuring the command isn't loaded is the primary goal.

        $this->assertSame(0, $this->tester->getStatusCode(), 'Listing commands should be successful even with an invalid module path.');
    }
}
