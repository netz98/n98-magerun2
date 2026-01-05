<?php

namespace N98\Magento\Command\Database;

use N98\Magento\Application;
use N98\Magento\Command\Database\Compressor\Compressor;
use N98\Util\Console\Helper\DatabaseHelper;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommandUnitTest extends TestCase
{
    public function testIncludeOptionIsArray()
    {
        $command = new DumpCommand();
        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('include'));
        $option = $definition->getOption('include');
        $this->assertTrue($option->isArray(), 'The include option should accept multiple values (array)');
    }

    public function testMultipleIncludesAreRespectedInCommandGeneration()
    {
        // Mock Input
        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')->will($this->returnValueMap([
            ['include', ['table1', 'table2']],
            ['strip', null],
            ['exclude', null],
            ['no-views', false],
            ['compression', null],
            ['no-single-transaction', true], // Avoid adding --single-transaction
            ['human-readable', false],
            ['set-gtid-purged-off', false],
            ['add-routines', false],
            ['no-tablespaces', false],
            ['keep-column-statistics', false],
            ['git-friendly', false],
            ['keep-definer', false],
            ['mydumper', false],
            ['stdout', false],
            ['only-command', true],
            ['print-only-filename', false],
            ['dry-run', false],
            ['views', false],
            ['force', true], // Force to true to avoid interaction
            ['add-time', 'no'],
        ]));

        // Mock getArgument
        $input->method('getArgument')->will($this->returnValueMap([
            ['filename', 'dump.sql']
        ]));

        // Mock Output
        $output = $this->createMock(OutputInterface::class);
        $outputBuffer = [];
        $output->method('writeln')->will($this->returnCallback(function ($message) use (&$outputBuffer) {
            $outputBuffer[] = $message;
        }));

        // Mock DatabaseHelper
        $databaseHelper = $this->createMock(DatabaseHelper::class);
        $databaseHelper->method('getDbSettings')->willReturn([
            'host' => 'localhost',
            'username' => 'user',
            'password' => 'pass',
            'dbname' => 'magento',
            'prefix' => '',
        ]);
        $databaseHelper->method('getMysqlDumpBinary')->willReturn('mysqldump');
        $databaseHelper->method('getViews')->willReturn([]);
        $databaseHelper->method('getTables')->willReturn(['table1', 'table2', 'table3']);
        $databaseHelper->method('resolveTables')->will($this->returnCallback(function ($tables) {
            // resolveTables expects array, and since we plan to pass array from input OR string exploded
            // But strict mocking: in `DumpCommand.php`:
            // resolveDatabaseTables calls $database->resolveTables(explode(' ', $list)...) CURRENTLY
            // AFTER FIX: it should handle array.

            // If I fix logic to handle array inside DumpCommand, `resolveDatabaseTables` will likely pass array to `resolveTables`.
            // So return argument as is.
            if (is_string($tables)) {
                return explode(' ', $tables);
            }
            return $tables;
        }));
        $databaseHelper->method('getMysqlClientToolConnectionString')->willReturn('-h localhost -u user -p pass magento');
        $databaseHelper->method('getTableDefinitions')->willReturn([]);

        // Mock HelperSet
        $questionHelper = $this->createMock(QuestionHelper::class); // Mock QuestionHelper

        $helperSet = $this->createMock(HelperSet::class);
        $helperSet->method('get')->will($this->returnValueMap([
            ['database', $databaseHelper],
            ['question', $questionHelper]
        ]));

        // Mock Application
        $application = $this->createMock(Application::class);
        $application->method('getConfig')->willReturn(['commands' => []]);

        // System under test
        $command = new DumpCommand();
        $command->setApplication($application);
        $command->setHelperSet($helperSet);

        // We need to bypass `detectDbSettings` doing IO or logic we can't easily replicate?
        // `detectDbSettings` calls `$database->detectDbSettings` and sets `dbSettings`.
        // We mocked `$database`.

        // Execute
        // We call run which calls execute.
        // But run validates input interaction. We mocked InputInterface so bind may fail if we don't mock bind/validate?
        // We can call `run` but ignore validation if we mock `validate`? No.

        // Let's call protected execute via reflection or just rely on run.
        // run method is in Command class.

        $command->run($input, $output);

        // Verify Output
        // Expected behavior: table1 and table2 are INCLUDED. table3 is excluded.
        // Include logic: "excludes = all - includes". So table3 is in excludes.
        // ignoreTableList = excludes + strip.
        // So command should have --ignore-table=magento.table3
        // And SHOULD NOT have --ignore-table=magento.table1 or table2.

        $fullOutput = implode("\n", $outputBuffer);

        $this->assertStringContainsString('--ignore-table=magento.table3', $fullOutput, 'table3 should be ignored (excluded)');
        $this->assertStringNotContainsString('--ignore-table=magento.table1', $fullOutput, 'table1 should NOT be ignored (included)');
        $this->assertStringNotContainsString('--ignore-table=magento.table2', $fullOutput, 'table2 should NOT be ignored (included)');
    }

    public function testTildeExpansionInFilename()
    {
        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->with('filename')->willReturn('~/dump.sql');
        $input->method('getOption')->willReturnMap([
            ['stdout', false],
            ['add-time', 'no'],
            ['force', true],
            ['compression', null],
        ]);

        $output = $this->createMock(OutputInterface::class);

        $databaseHelper = $this->createMock(DatabaseHelper::class);
        $databaseHelper->method('getDbSettings')->willReturn(['dbname' => 'magento', 'prefix' => '']);

        // Mock HelperSet
        $helperSet = $this->createMock(HelperSet::class);
        $helperSet->method('get')->will($this->returnValueMap([
            ['database', $databaseHelper],
        ]));

        // Create Command via subclass to access protected getFileName or use reflection.
        // Or simply expose it.
        // It's easier to use ReflectionMethod to test protected method.

        $command = new DumpCommand();
        $command->setHelperSet($helperSet);

        $reflection = new ReflectionClass($command);
        $method = $reflection->getMethod('getFileName');
        $method->setAccessible(true);

        $compressor = $this->createMock(Compressor::class);
        $compressor->method('getFileName')->will($this->returnArgument(0));

        // We assume HOME is set or we mock it?
        // OperatingSystem::getHomeDir uses getenv('HOME').
        // We can set ENV for test.
        $home = getenv('HOME');

        $expanded = $method->invoke($command, $input, $output, $compressor);

        // If expansion works, it should not start with ~
        $this->assertStringStartsWith($home, $expanded);
        $this->assertStringNotContainsString('~', $expanded);
    }
}
