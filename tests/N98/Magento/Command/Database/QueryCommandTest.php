<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Database;

use N98\Magento\Command\TestCase;
use N98\Util\Console\Helper\DatabaseHelper;
use RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class QueryCommandTest extends TestCase
{
    public function testDbQueryCsvOutput()
    {
        $application = $this->getApplication();
        $this->assertTrue($application->has('db:query'), 'Command db:query should be registered.');

        $this->mockDatabaseHelper('--host=localhost --user=test --password=test test_db');

        $this->assertExecute(['command' => 'db:query', 'query' => 'SELECT 1', '--format' => 'csv']);
    }

    public function testThrowsWhenNoQueryGivenNonInteractively()
    {
        $this->mockDatabaseHelper('--host=localhost --user=test --password=test test_db');

        $command = $this->getApplication()->find('db:query');
        $tester = new CommandTester($command);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No SQL query provided');

        $tester->execute(['command' => 'db:query'], ['interactive' => false]);
    }

    public function testFailingQuerySurfacesMysqlErrorInOutput()
    {
        // Use a bogus mysql CLI option so the client fails synchronously and
        // deterministically, without depending on a real database connection.
        $this->mockDatabaseHelper('--this-option-does-not-exist');

        $this->assertDisplayContains(
            ['command' => 'db:query', 'query' => 'SELECT 1'],
            'unknown option'
        );
    }

    private function mockDatabaseHelper(string $connectionString): void
    {
        $dbHelperMock = $this->getMockBuilder(DatabaseHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMysqlClientToolConnectionString', 'detectDbSettings'])
            ->getMock();

        $dbHelperMock->method('getMysqlClientToolConnectionString')->willReturn($connectionString);
        $dbHelperMock->method('detectDbSettings');

        $this->getApplication()->getHelperSet()->set($dbHelperMock, 'database');
    }
}
