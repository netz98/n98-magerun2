<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Class\Plugin;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PluginInspectorTest extends TestCase
{
    public function testAreaDefaultAndXmlMergePrecedence(): void
    {
        $this->assertSame('global', (new ListCommand())->getDefinition()->getOption('area')->getDefault());
        $calls = [];
        $scopes = [
            'primary' => [InspectorSubject::class => ['plugins' => ['shared' => ['instance' => InspectorPlugin::class, 'sortOrder' => 5]]]],
            'global' => [InspectorSubject::class => ['plugins' => ['shared' => ['sortOrder' => 10]]]],
            'frontend' => ['AreaPlugin' => ['type' => InspectorPlugin::class], InspectorSubject::class => ['plugins' => [
                'shared' => ['disabled' => true, 'sortOrder' => 20],
                'area-only' => ['instance' => 'AreaPlugin'],
            ]]],
        ];
        $read = static function (string $scope) use (&$calls, $scopes): array {
            $calls[] = $scope;

            return $scopes[$scope];
        };
        $inspector = new PluginInspector();
        $global = $inspector->readConfiguration($read);
        $this->assertSame(['primary', 'global'], $calls);
        $this->assertSame(['shared'], array_keys($global[InspectorSubject::class]['plugins']));
        $this->assertSame(10, $global[InspectorSubject::class]['plugins']['shared']['sortOrder']);
        $calls = [];
        $frontend = $inspector->readConfiguration($read, 'frontend');
        $this->assertSame(['primary', 'global', 'frontend'], $calls);
        $rows = $inspector->inspect(new ReflectionClass(InspectorSubject::class), 'run', $frontend, static function (string $type): string {
            return $type;
        });
        $this->assertCount(6, $rows);
        $this->assertSame('area-only', $rows[0][0]);
        $this->assertSame(['shared', InspectorPlugin::class, 'run', 'after', 20, 'disabled'], $rows[3]);
    }

    public function testUndeclaredInterfaceReferenceDoesNotOverrideParentPlugin(): void
    {
        $config = [
            InspectorContract::class => ['plugins' => ['qa' => ['instance' => InspectorPlugin::class, 'sortOrder' => 10]]],
            InspectorParent::class => ['plugins' => ['qa' => ['sortOrder' => 20]]],
            InspectorSpecific::class => ['plugins' => ['qa' => ['disabled' => true]]],
        ];
        $rows = (new PluginInspector())->inspect(new ReflectionClass(InspectorSpecificSubject::class), 'run', $config, static function (string $type): string {
            return $type;
        });
        $this->assertCount(3, $rows);
        foreach ($rows as $row) {
            $this->assertSame(20, $row[4]);
            $this->assertSame('active', $row[5]);
        }
    }

    public function testPublicStaticPluginMethodsAreIncluded(): void
    {
        $config = [InspectorSubject::class => ['plugins' => ['static' => ['instance' => InspectorStaticPlugin::class]]]];
        $rows = (new PluginInspector())->inspect(new ReflectionClass(InspectorSubject::class), 'run', $config, static function (string $type): string {
            return $type;
        });
        $this->assertSame([['static', InspectorStaticPlugin::class, 'run', 'before', 0, 'active']], $rows);
    }

    public function testClassAndMethodTargets(): void
    {
        $inspector = new PluginInspector();
        [$class, $method] = $inspector->parseTarget('\\' . InspectorSubject::class . '::rUn()');
        $this->assertSame(InspectorSubject::class, $class->getName());
        $this->assertSame('run', $method);
        $this->assertNull($inspector->parseTarget(InspectorSubject::class)[1]);
        $this->assertSame(InspectorContract::class, $inspector->parseTarget(InspectorContract::class)[0]->getName());
    }

    /** @dataProvider invalidTargets */
    public function testInvalidTargets(string $target): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PluginInspector())->parseTarget($target);
    }

    public function invalidTargets(): array
    {
        return array_map(static function (string $suffix): array {
            return [InspectorSubject::class . $suffix];
        }, ['::run', '::run(1)', '::missing()', '::hidden()', '::staticMethod()', '::finalMethod()', '::weird()()'])
            + [10 => ['NotARealPluginSubject'], 11 => [''], 12 => ['bad class']];
    }

    public function testInheritedDisabledPluginsAndDeterministicSorting(): void
    {
        $config = [
            InspectorContract::class => ['plugins' => ['inherited' => ['instance' => InspectorPlugin::class, 'sortOrder' => 20]]],
            InspectorParent::class => ['plugins' => ['inherited' => ['disabled' => true]]],
            InspectorSubject::class => ['plugins' => [
                'z-last' => ['instance' => InspectorPlugin::class, 'sortOrder' => 100],
                'b-tie' => ['instance' => 'VirtualPlugin', 'sortOrder' => 20],
                'a-default' => ['instance' => InspectorPlugin::class],
                'negative' => ['instance' => InspectorPlugin::class, 'sortOrder' => -5],
                'unavailable' => ['instance' => 'UnavailablePluginClass', 'disabled' => true],
            ]],
        ];
        $inspector = new PluginInspector();
        $resolve = static function (string $type): string {
            return $type === 'VirtualPlugin' ? InspectorPlugin::class : $type;
        };
        $rows = $inspector->inspect(new ReflectionClass(InspectorSubject::class), null, $config, $resolve);
        $this->assertCount(16, $rows);
        $this->assertSame(['negative', InspectorPlugin::class, 'run', 'after', -5, 'active'], $rows[0]);
        $inherited = array_values(array_filter($rows, static function (array $row): bool {
            return $row[0] === 'inherited';
        }));
        $this->assertCount(3, $inherited);
        $this->assertSame('disabled', $inherited[0][5]);
        $this->assertSame('b-tie', $rows[7][0]);
        $this->assertSame('z-last', $rows[15][0]);
        $methodRows = $inspector->inspect(new ReflectionClass(InspectorSubject::class), 'other', $config, $resolve);
        $this->assertSame([], $methodRows);
        $this->assertSame([], $inspector->inspect(new ReflectionClass(InspectorSubject::class), null, [], $resolve));
        $config[InspectorSubject::class]['plugins']['inherited'] = ['disabled' => false];
        $rows = $inspector->inspect(new ReflectionClass(InspectorSubject::class), 'run', $config, $resolve);
        $this->assertCount(15, $rows);
        $this->assertSame(['active'], array_values(array_unique(array_column($rows, 5))));
    }
}

interface InspectorContract
{
    public function run();
}

interface InspectorSpecific extends InspectorContract
{
}

class InspectorStaticPlugin
{
    public static function beforeRun()
    {
    }
}

class InspectorParent implements InspectorContract
{
    public function run()
    {
    }
}

class InspectorSpecificSubject extends InspectorParent implements InspectorSpecific
{
}

class InspectorSubject extends InspectorParent
{
    public function other()
    {
    }
    protected function hidden()
    {
    }
    public static function staticMethod()
    {
    }
    final public function finalMethod()
    {
    }
}

class InspectorPlugin
{
    public function __construct()
    {
        throw new \LogicException('Plugins must not be instantiated by the inspector.');
    }
    public function beforeRun()
    {
    }
    public function aroundRun()
    {
    }
    public function afterRun()
    {
    }
    public function beforeMissing()
    {
    }
    public function beforeHidden()
    {
    }
    public function beforeFinalMethod()
    {
    }
    public function beforeStaticMethod()
    {
    }
    protected function beforeOther()
    {
    }
}
