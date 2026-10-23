<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module\Preference;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Module\ModuleListInterface;
use N98\Magento\Command\TestCase;
use N98\Util\Console\Helper\TableHelper;
use RuntimeException;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class ConflictsCommandTest extends TestCase
{
    private function command(ConflictDetector $detector, ?string $path = '/modules/Enabled'): ConflictsCommand
    {
        $this->getApplication()->initMagento();
        $modules = $this->createMock(ModuleListInterface::class);
        $modules->method('getNames')->willReturn(['Example_Enabled']);
        $registrar = $this->createMock(ComponentRegistrarInterface::class);
        $registrar->expects($this->once())->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'Example_Enabled')->willReturn($path);
        $command = new ConflictsCommand();
        $command->inject($modules, $registrar, $detector);
        $command->setHelperSet(new HelperSet([new TableHelper()]));

        return $command;
    }

    private function executeCommand(ConflictsCommand $command, array $options = []): array
    {
        $input = new ArrayInput($options, $command->getDefinition());
        $output = new BufferedOutput();
        $execute = new \ReflectionMethod($command, 'execute');
        $status = $execute->invoke($command, $input, $output);

        return [$status, $output->fetch()];
    }

    public function testCleanResultExitsSuccessfully(): void
    {
        $detector = $this->createMock(ConflictDetector::class);
        $detector->expects($this->once())->method('detect')
            ->with(['Example_Enabled' => '/modules/Enabled'], null)->willReturn([]);
        [$status, $output] = $this->executeCommand($this->command($detector));
        $this->assertSame(0, $status);
        $this->assertStringContainsString('No preference conflicts found.', $output);
    }

    public function testFindingsExitSuccessfullyAndRenderStructuredSources(): void
    {
        $rows = [
            ['scope' => 'frontend', 'target' => 'Example\Target', 'type' => 'Example\A', 'module' => 'Example_Enabled', 'file' => '/modules/Enabled/etc/frontend/di.xml', 'line' => 2],
            ['scope' => 'frontend', 'target' => 'Example\Target', 'type' => 'Example\B', 'module' => 'Example_Enabled', 'file' => '/modules/Enabled/etc/frontend/di.xml', 'line' => 3],
        ];
        $detector = $this->createMock(ConflictDetector::class);
        $detector->expects($this->once())->method('detect')
            ->with(['Example_Enabled' => '/modules/Enabled'], 'frontend')->willReturn($rows);
        [$status, $output] = $this->executeCommand($this->command($detector), ['--area' => 'frontend', '--format' => 'json']);
        $this->assertSame(0, $status);
        $this->assertSame($rows, json_decode($output, true, 512, JSON_THROW_ON_ERROR));
    }

    public function testEmptyStructuredResultContainsNoProse(): void
    {
        $detector = $this->createMock(ConflictDetector::class);
        $detector->method('detect')->willReturn([]);
        [$status, $output] = $this->executeCommand($this->command($detector), ['--format' => 'json']);
        $this->assertSame(0, $status);
        $this->assertSame([], json_decode($output, true, 512, JSON_THROW_ON_ERROR));
    }

    public function testParseErrorExitsWithFailure(): void
    {
        $detector = $this->createMock(ConflictDetector::class);
        $detector->method('detect')->willThrowException(new RuntimeException('Invalid DI XML: example/di.xml'));
        [$status, $output] = $this->executeCommand($this->command($detector));
        $this->assertSame(1, $status);
        $this->assertStringContainsString('Invalid DI XML: example/di.xml', $output);
        $this->assertStringNotContainsString('No preference conflicts', $output);
    }

    public function testUnregisteredEnabledModuleIsAnError(): void
    {
        $detector = $this->createMock(ConflictDetector::class);
        $detector->expects($this->never())->method('detect');
        [$status, $output] = $this->executeCommand($this->command($detector, null));
        $this->assertSame(1, $status);
        $this->assertStringContainsString('Enabled module has no registered path: Example_Enabled', $output);
    }

    public function testCommandIsRegistered(): void
    {
        $command = $this->getApplication()->find('dev:module:preference:conflicts');
        $this->assertInstanceOf(ConflictsCommand::class, $command);
    }
}
