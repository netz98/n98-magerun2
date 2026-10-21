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
                'unavailable' => ['disabled' => true],
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

class InspectorParent implements InspectorContract
{
    public function run()
    {
    }
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
