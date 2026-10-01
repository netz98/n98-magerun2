<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento;

use N98\Magento\Application\Config;
use N98\Magento\Command\MagentoCoreProxyCommand;
use N98\Magento\Command\Mcp\Server\StartCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class ApplicationCommandRegistrationTest extends TestCase
{
    /**
     * @dataProvider disabledCommandsProvider
     */
    public function testDisabledCommandsAndTheirAliasesAreNotRegistered(array $disabled): void
    {
        $application = $this->createApplication([
            'disabled' => $disabled,
            'aliases' => [['backup' => 'db:dump']],
        ]);

        $this->assertNull($application->add(new StartCommand()));
        $this->assertNull($application->add((new Command('db:dump'))->setAliases(['dump'])));
        $this->assertNull($application->add(new MagentoCoreProxyCommand(
            '/magento',
            'setup:upgrade',
            [],
            '',
            '',
            ['arguments' => [], 'options' => []]
        )));

        foreach (['mcp:server:start', 'db:dump', 'dump', 'backup', 'setup:upgrade'] as $name) {
            $this->assertFalse($application->has($name));
            $this->assertArrayNotHasKey($name, $application->all());
        }

        $enabled = new Command('db:import');
        $this->assertSame($enabled, $application->add($enabled));
        $this->assertSame($enabled, $application->find('db:imp'));
    }

    public static function disabledCommandsProvider(): array
    {
        return [
            'exact names' => [['mcp:server:start', 'db:dump', 'setup:upgrade']],
            'wildcards' => [['mcp:*', 'db:d?mp', 'setup:*']],
        ];
    }

    /**
     * @dataProvider disabledAliasesProvider
     */
    public function testDisabledAliasesAreNotRegistered(array $disabled): void
    {
        $application = $this->createApplication([
            'disabled' => $disabled,
            'aliases' => [['backup' => 'db:dump']],
        ]);
        $command = (new Command('db:dump'))->setAliases(['dump', 'export']);
        $application->add($command);

        $this->assertTrue($application->has('db:dump'));
        $this->assertSame($command, $application->find('export'));
        $this->assertFalse($application->has('backup'));
        $this->assertFalse($application->has('dump'));
    }

    public static function disabledAliasesProvider(): array
    {
        return [
            'exact aliases' => [['backup', 'dump']],
            'wildcard aliases' => [['back*', 'd?mp']],
        ];
    }

    public function testCommandsAreEnabledByDefault(): void
    {
        $application = $this->createApplication([]);
        $command = new StartCommand();

        $this->assertSame($command, $application->add($command));
        $this->assertSame($command, $application->get('mcp:server:start'));
    }

    private function createApplication(array $commandsConfig): Application
    {
        $application = new Application();
        $configuration = new Config();
        $configuration->setConfig(['commands' => $commandsConfig]);
        $property = new \ReflectionProperty(Application::class, 'config');
        $property->setAccessible(true);
        $property->setValue($application, $configuration);
        $application->setIsInitialized(true);

        return $application;
    }
}
