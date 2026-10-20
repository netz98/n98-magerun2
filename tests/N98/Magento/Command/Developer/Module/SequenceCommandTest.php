<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Module;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Module\ModuleList\Loader;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SequenceCommandTest extends TestCase
{
    /**
     * @dataProvider moduleConfigProvider
     */
    public function testRegeneration(array $config, array $expected, bool $write)
    {
        $this->getApplication()->initMagento();
        $loader = $this->createMock(Loader::class);
        $reader = $this->createMock(Reader::class);
        $writer = $this->createMock(Writer::class);
        $loader->expects($this->once())->method('load')->willReturn([
            'Example_Dependency' => [],
            'Example_Dependent' => [],
            'Example_New' => [],
        ]);
        $reader->expects($this->once())->method('load')->with(ConfigFilePool::APP_CONFIG)->willReturn($config);
        if ($write) {
            $writer->expects($this->once())->method('saveConfig')->with([
                ConfigFilePool::APP_CONFIG => ['modules' => $expected],
            ], true);
        } else {
            $writer->expects($this->never())->method('saveConfig');
        }

        $command = new SequenceCommand();
        $command->inject($loader, $reader, $writer);
        $execute = new \ReflectionMethod($command, 'execute');
        $execute->setAccessible(true);
        $output = new BufferedOutput();
        $this->assertSame(0, $execute->invoke($command, new ArrayInput([]), $output));
        $this->assertStringContainsString($write ? 'regenerated' : 'already up to date', $output->fetch());
    }

    public function moduleConfigProvider(): array
    {
        $expected = ['Example_Dependency' => 1, 'Example_Dependent' => 0, 'Example_New' => 1];

        return [
            'reorder, preserve disabled, add new, remove absent' => [
                ['modules' => ['Example_Dependent' => 0, 'Example_Removed' => 1, 'Example_Dependency' => 1],
                    'system' => ['default' => ['example' => 'unchanged']]],
                $expected,
                true,
            ],
            'already ordered' => [['modules' => $expected], $expected, false],
            'missing modules section' => [[], array_fill_keys(array_keys($expected), 1), true],
        ];
    }

    public function testRealCommandPreservesConfigAndDatabase()
    {
        $root = $this->getTestMagentoRoot();
        $file = $root . '/app/etc/config.php';
        $original = file_get_contents($file);
        $env = file_get_contents($root . '/app/etc/env.php');
        $config = include $file;
        $connection = $this->getDatabaseConnection();
        $table = $connection->getTableName('setup_module');
        $databaseBefore = $connection->fetchAll('SELECT * FROM ' . $connection->quoteIdentifier($table));
        $modules = $config['modules'];
        $modules['Magento_Weee'] = 0;
        unset($modules['Magento_Catalog']);
        $modules['Example_Removed'] = 0;
        $config['modules'] = array_reverse($modules, true);
        $config['sequence_regression'] = ['value' => 'preserve me'];

        try {
            file_put_contents($file, '<?php return ' . var_export($config, true) . ';');
            $this->assertExecute('dev:module:sequence');
            $result = include $file;
            $expected = $config;
            $expected['modules'] = [];
            $loader = $this->getApplication()->getObjectManager()->get(Loader::class);
            foreach (array_keys($loader->load()) as $module) {
                $expected['modules'][$module] = $modules[$module] ?? 1;
            }
            $this->assertSame($expected, $result);
            $this->assertSame(0, $result['modules']['Magento_Weee']);
            $this->assertSame(1, $result['modules']['Magento_Catalog']);
            $names = array_keys($result['modules']);
            $this->assertLessThan(
                array_search('Magento_Weee', $names, true),
                array_search('Magento_Catalog', $names, true),
                'Catalog must precede its dependent Weee module'
            );
            $this->assertSame($env, file_get_contents($root . '/app/etc/env.php'));
            $this->assertSame($databaseBefore, $connection->fetchAll('SELECT * FROM ' . $connection->quoteIdentifier($table)));

            $contents = file_get_contents($file);
            $command = $this->getApplication()->find('dev:module:sequence');
            $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
            $this->assertSame(0, $tester->execute([]));
            $this->assertStringContainsString('already up to date', $tester->getDisplay());
            $this->assertSame($contents, file_get_contents($file));
        } finally {
            file_put_contents($file, $original);
        }
    }
}
