<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

use N98\Util\Console\Helper\DatabaseHelper;
use N98\Util\Console\Helper\TableHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;

class VariablesCommandUnitTest extends TestCase
{
    public function testExecute()
    {
        $command = new VariablesCommand();

        // Mock DatabaseHelper
        $databaseHelper = $this->createMock(DatabaseHelper::class);
        $databaseHelper->method('getName')->willReturn('database');

        // Mock getGlobalVariables
        $databaseHelper->expects($this->any())
            ->method('getGlobalVariables')
            ->willReturn([
                'innodb_buffer_pool_size' => '123456',
                'have_query_cache' => 'YES',
            ]);

        // Mock TableHelper
        $tableHelper = $this->createMock(TableHelper::class);
        $tableHelper->method('getName')->willReturn('table');
        $tableHelper->method('setHeaders')->willReturnSelf();

        // Capture rows passed to renderByFormat
        $tableHelper->method('renderByFormat')
            ->willReturnCallback(function($output, $rows, $format) {
                foreach ($rows as $row) {
                    $line = implode('|', $row);
                    $output->writeln($line);
                }
            });

        $helperSet = new HelperSet([
            $databaseHelper,
            $tableHelper,
        ]);
        $command->setHelperSet($helperSet);

        // Mock Input
        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['search', null],
            ['command', 'db:variables'],
        ]);

        // Mock getOption with default values
        $input->method('getOption')->willReturnMap([
            ['format', null],
            ['rounding', 0],
            ['no-description', false],
            ['connection', 'default'],
        ]);

        $input->method('isInteractive')->willReturn(false);
        $input->method('hasArgument')->willReturn(true);
        $input->method('bind')->willReturn(true);
        $input->method('validate')->willReturn(true);

        $output = new BufferedOutput();

        // We run the command. Since we mocked helpers, it should work without Magento.
        $result = $command->run($input, $output);

        $display = $output->fetch();

        // Check for variable name
        $this->assertStringContainsString('innodb_buffer_pool_size', $display);

        // Check for description (might be wrapped)
        $this->assertStringContainsString('The size of the memory buffer InnoDB uses to cache data', $display);

        // Check for another variable description
        $this->assertStringContainsString('YES if mysqld supports the query cache, NO if not', $display);
    }
}
