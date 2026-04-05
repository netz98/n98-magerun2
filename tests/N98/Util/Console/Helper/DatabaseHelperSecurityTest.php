<?php

namespace N98\Util\Console\Helper;

use N98\Magento\Command\TestCase;
use PDO;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseHelperSecurityTest extends TestCase
{
    /**
     * @test
     */
    public function dropTablesWithVulnerableName()
    {
        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdoMock->expects($this->once())
            ->method('query')
            ->with($this->callback(function ($query) {
                // Check if the query contains ESCAPED backticks
                // The expected secure query is: DROP TABLE IF EXISTS `table``name`;
                return strpos($query, "DROP TABLE IF EXISTS `table``name`;") !== false;
            }));

        $helper = $this->getMockBuilder(DatabaseHelper::class)
            ->onlyMethods(['getTables', 'getConnection'])
            ->getMock();

        $helper->expects($this->once())
            ->method('getTables')
            ->willReturn(['table`name']);

        $helper->expects($this->any())
            ->method('getConnection')
            ->willReturn($pdoMock);

        $outputMock = $this->getMockBuilder(OutputInterface::class)->getMock();

        $helper->dropTables($outputMock);
    }

    /**
     * @test
     */
    public function dropViewsWithVulnerableName()
    {
        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Expect exec to be called with the escaped query string
        $pdoMock->expects($this->exactly(3))
            ->method('exec')
            ->withConsecutive(
                ['SET FOREIGN_KEY_CHECKS = 0;'],
                ["DROP VIEW IF EXISTS `view``name`;"],
                ['SET FOREIGN_KEY_CHECKS = 1;']
            );

        $helper = $this->getMockBuilder(DatabaseHelper::class)
            ->onlyMethods(['getViews', 'getConnection'])
            ->getMock();

        $helper->expects($this->once())
            ->method('getViews')
            ->willReturn(['view`name']);

        $helper->expects($this->any())
            ->method('getConnection')
            ->willReturn($pdoMock);

        $outputMock = $this->getMockBuilder(OutputInterface::class)->getMock();

        $helper->dropViews($outputMock);
    }

    /**
     * @test
     */
    public function dropDatabaseWithVulnerableName()
    {
        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdoMock->expects($this->once())
            ->method('query')
            ->with($this->callback(function ($query) {
                return strpos($query, "DROP DATABASE IF EXISTS `db``name`") !== false;
            }));

        $helper = $this->getMockBuilder(DatabaseHelper::class)
            ->onlyMethods(['getConnection', 'detectDbSettings'])
            ->getMock();

        $helper->expects($this->any())
            ->method('getConnection')
            ->willReturn($pdoMock);

        // Set dbSettings directly as it is protected
        $reflection = new \ReflectionClass(DatabaseHelper::class);
        $property = $reflection->getProperty('dbSettings');
        $property->setAccessible(true);
        $property->setValue($helper, ['dbname' => 'db`name']);

        $outputMock = $this->getMockBuilder(OutputInterface::class)->getMock();

        $helper->dropDatabase($outputMock);
    }

    /**
     * @test
     */
    public function createDatabaseWithVulnerableName()
    {
        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdoMock->expects($this->once())
            ->method('query')
            ->with($this->callback(function ($query) {
                return strpos($query, "CREATE DATABASE IF NOT EXISTS `db``name`") !== false;
            }));

        $helper = $this->getMockBuilder(DatabaseHelper::class)
            ->onlyMethods(['getConnection', 'detectDbSettings'])
            ->getMock();

        $helper->expects($this->any())
            ->method('getConnection')
            ->willReturn($pdoMock);

        // Set dbSettings directly as it is protected
        $reflection = new \ReflectionClass(DatabaseHelper::class);
        $property = $reflection->getProperty('dbSettings');
        $property->setAccessible(true);
        $property->setValue($helper, ['dbname' => 'db`name']);

        $outputMock = $this->getMockBuilder(OutputInterface::class)->getMock();

        $helper->createDatabase($outputMock);
    }
}
