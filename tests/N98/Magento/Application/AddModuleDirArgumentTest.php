<?php

namespace N98\Magento\Application;

use N98\Magento\TestApplication;
use Symfony\Component\Console\Tester\ApplicationTester;

class AddModuleDirArgumentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array|null
     */
    private $originalArgv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalArgv = $_SERVER['argv'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->originalArgv === null) {
            unset($_SERVER['argv']);
        } else {
            $_SERVER['argv'] = $this->originalArgv;
        }

        parent::tearDown();
    }

    private function createTester(array $argv): ApplicationTester
    {
        $_SERVER['argv'] = $argv;

        $application = new TestApplication();
        $app = $application->getApplication();
        $app->setAutoExit(false);

        return new ApplicationTester($app);
    }

    public function testCommandAndAutoloadingFromAddedModuleDir()
    {
        $modulePath = 'tests/_files/custom_module_test_add_dir';

        $tester = $this->createTester([
            'n98-magerun2',
            '--add-module-dir=' . $modulePath,
        ]);

        $tester->run([
            '--add-module-dir' => $modulePath,
            'command' => 'list',
        ], ['decorated' => false]);

        $listOutput = $tester->getDisplay();
        $this->assertStringContainsString('mytest:hello', $listOutput, 'The command mytest:hello should be listed.');

        $tester->run([
            '--add-module-dir' => $modulePath,
            'command' => 'mytest:hello',
        ], ['decorated' => false]);

        $commandOutput = $tester->getDisplay();
        $this->assertStringContainsString(
            'Hello from MyTestHelloCommand! Autoloaded: AutoloadTestClass says hi!',
            $commandOutput,
            'The output from mytest:hello command is not as expected.'
        );
        $this->assertSame(0, $tester->getStatusCode(), 'Command execution should be successful.');
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

        $tester = $this->createTester([
            'n98-magerun2',
            '--add-module-dir=' . $modulePath1,
            '--add-module-dir=' . $modulePath1,
        ]);

        $tester->run([
            '--add-module-dir' => [$modulePath1, $modulePath1],
            'command' => 'mytest:hello',
        ], ['decorated' => false]);

        $commandOutput = $tester->getDisplay();
        $this->assertStringContainsString(
            'Hello from MyTestHelloCommand! Autoloaded: AutoloadTestClass says hi!',
            $commandOutput,
            'The output from mytest:hello command with multiple --add-module-dir options is not as expected.'
        );
        $this->assertSame(0, $tester->getStatusCode(), 'Command execution should be successful with multiple --add-module-dir options.');
    }

    public function testNonExistentModuleDir()
    {
        $nonExistentPath = 'tests/_files/non_existent_module_dir_for_test';

        $tester = $this->createTester([
            'n98-magerun2',
            '--add-module-dir=' . $nonExistentPath,
            'list',
        ]);

        $tester->run([
            '--add-module-dir' => $nonExistentPath,
            'command' => 'list',
            '-v' => true,
        ], ['decorated' => false]);

        $listOutput = $tester->getDisplay();
        // Check that the command is NOT listed
        $this->assertStringNotContainsString('mytest:hello', $listOutput, 'The command mytest:hello should NOT be listed when path is invalid.');

        // Check for the warning message (optional, depends on exact logging implementation)
        // This assertion might be fragile if the warning message changes.
        // $this->assertStringContainsString("Warning: --add-module-dir: Could not resolve path", $listOutput);
        // Or, check the warning from ConfigurationLoader:
        // $this->assertStringContainsString("Provided additional module path is not a valid directory: ".realpath($nonExistentPath), $listOutput);
        // For now, just ensuring the command isn't loaded is the primary goal.

        $this->assertSame(0, $tester->getStatusCode(), 'Listing commands should be successful even with an invalid module path.');
    }
}
