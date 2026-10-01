<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Mcp\Server;

use N98\Magento\Application;
use N98\Magento\Application\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;

class StartCommandTest extends TestCase
{
    public function testConfigure()
    {
        $command = new StartCommand();
        $this->assertEquals('mcp:server:start', $command->getName());
        $this->assertStringContainsString('Start an MCP server', $command->getDescription());
        $this->assertTrue($command->getDefinition()->hasOption('include'));
        $this->assertTrue($command->getDefinition()->hasOption('exclude'));
    }

    /**
     * @dataProvider disabledCommandsProvider
     */
    public function testConfiguredToolSelection(array $disabled, array $expected): void
    {
        $application = new Application();
        $configuration = new Config();
        $configuration->setConfig([
            'commands' => [
                'disabled' => ['db:query'],
                StartCommand::class => [
                    'disabled' => $disabled,
                    'command-groups' => [
                        ['id' => 'backup', 'commands' => 'db:dump db:import', 'description' => 'Backup commands'],
                    ],
                ],
            ],
        ]);
        $property = new \ReflectionProperty(Application::class, 'config');
        $property->setAccessible(true);
        $property->setValue($application, $configuration);
        $application->setIsInitialized(true);

        $command = new StartCommand();
        $application->add($command);
        foreach (['db:dump', 'db:import', 'db:info', 'db:status', 'db:query'] as $name) {
            $application->add(new Command($name));
        }

        $input = new ArrayInput(['--include' => ['db:*'], '--exclude' => ['db:status']], $command->getDefinition());
        $method = new \ReflectionMethod(StartCommand::class, 'getExposedCommands');
        $method->setAccessible(true);
        $exposed = $method->invoke($command, $input);

        $this->assertSame($expected, array_keys($exposed));
        $this->assertTrue($application->has('db:dump'));
        $this->assertTrue($application->has('db:import'));
        $this->assertFalse($application->has('db:query'));
    }

    public static function disabledCommandsProvider(): array
    {
        return [
            'enabled by default' => [[], ['db:dump', 'db:import', 'db:info']],
            'command names' => [['db:dump', 'db:import'], ['db:info']],
            'wildcards' => [['db:d*', 'db:im*'], ['db:info']],
            'single-character wildcards' => [['db:d?mp', 'db:impor?'], ['db:info']],
            'whole namespace' => [['db:*'], []],
            'command groups' => [['@backup'], ['db:info']],
        ];
    }
}
