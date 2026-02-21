<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Test\Integration;

use N98\Magento\Application;
use N98\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class FlushCommandTest
 * @package N98\Magento\Command\Test\Integration
 */
class FlushCommandTest extends TestCase
{
    /**
     * @var string[]
     */
    private $tempDirectories = [];

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();

        foreach ($this->tempDirectories as $directory) {
            if (is_dir($directory)) {
                $filesystem->recursiveRemoveDirectory($directory);
            }
        }

        $this->tempDirectories = [];
    }

    public function testExecuteWithoutMagentoRoot()
    {
        $tester = $this->createCommandTester('');
        $tester->execute(['command' => 'test:integration:flush']);

        $this->assertStringContainsString('Could not determine Magento root directory', $tester->getDisplay());
    }

    public function testExecuteWithoutIntegrationTests()
    {
        $magentoRoot = $this->createTempDirectory();

        $tester = $this->createCommandTester($magentoRoot);
        $tester->execute(['command' => 'test:integration:flush']);

        $this->assertStringContainsString('No integration tests directory found', $tester->getDisplay());
    }

    public function testExecuteWithEmptySandboxDir()
    {
        $magentoRoot = $this->createTempDirectory();
        $tempPath = $magentoRoot . '/dev/tests/integration/tmp';
        mkdir($tempPath, 0777, true);

        $tester = $this->createCommandTester($magentoRoot);
        $tester->execute(['command' => 'test:integration:flush']);

        $this->assertStringContainsString('No sandbox directories found', $tester->getDisplay());
    }

    private function createTempDirectory(): string
    {
        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'n98-magerun2-test-'
            . uniqid('', true);

        mkdir($directory, 0777, true);

        $this->tempDirectories[] = $directory;

        return $directory;
    }

    private function createCommandTester(string $magentoRoot): CommandTester
    {
        $application = $this->createMock(Application::class);
        $application->method('detectMagento');
        $application->method('getMagentoRootFolder')->willReturn($magentoRoot);
        $application->method('isMagentoEnterprise')->willReturn(false);
        $application->method('getMagentoMajorVersion')->willReturn(2);

        $command = new FlushCommand();
        $command->setApplication($application);
        $command->setHelperSet(new HelperSet());

        return new CommandTester($command);
    }
}
