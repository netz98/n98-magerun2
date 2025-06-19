<?php

namespace N98\Util\Console\Helper;

use InvalidArgumentException;
use N98\Magento\Command\TestCase;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionProperty;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DatabaseHelperTest
 *
 * @covers \N98\Util\Console\Helper\DatabaseHelper
 */
class DatabaseHelperTest extends TestCase
{
    /**
     * @return DatabaseHelper
     */
    protected function getHelper(): DatabaseHelper
    {
        $helperSet = $this->getApplication()->getHelperSet();

        /** @var DatabaseHelper $dbHelper */
        $dbHelper = $helperSet->get('database');

        return $dbHelper;
    }

    /**
     * @test
     */
    public function testHelperInstance()
    {
        $this->assertInstanceOf('\N98\Util\Console\Helper\DatabaseHelper', $this->getHelper());
    }

    /**
     * @test
     */
    public function getConnection()
    {
        $this->assertInstanceOf('\PDO', $this->getHelper()->getConnection());
    }

    /**
     * @test
     */
    public function dsn()
    {
        $this->assertStringStartsWith('mysql:', $this->getHelper()->dsn());
    }

    /**
     * @test
     */
    public function mysqlUserHasPrivilege()
    {
        $this->assertTrue($this->getHelper()->mysqlUserHasPrivilege('SELECT'));
    }

    /**
     * @test
     */
    public function getMysqlVariableValue()
    {
        $this->markTestSkipped('skipped');
        $helper = $this->getHelper();

        // verify (complex) return value with existing global variable
        $actual = $helper->getMysqlVariableValue('version');

        $this->assertIsArray($actual);
        $this->assertCount(1, $actual);
        $key = '@@version';
        $this->assertArrayHasKey($key, $actual);
        $this->assertIsString($actual[$key]);

        // quoted
        $actual = $helper->getMysqlVariableValue('`version`');
        $this->assertEquals('@@`version`', key($actual));

        // non-existent global variable
        try {
            $helper->getMysqlVariableValue('nonexistent');
            $this->fail('An expected exception has not been thrown');
        } catch (RuntimeException $e) {
            $this->assertEquals("Failed to query mysql variable 'nonexistent'", $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function getMysqlVariable()
    {
        $helper = $this->getHelper();

        // behaviour with existing global variable
        $actual = $helper->getMysqlVariable('version');
        $this->assertIsString($actual);

        // behavior with existent session variable (INTEGER)
        $helper->getConnection()->query('SET @existent = 14;');
        $actual = $helper->getMysqlVariable('existent', '@');
        $this->assertSame("14", $actual);

        // behavior with non-existent session variable
        $actual = $helper->getMysqlVariable('nonexistent', '@');
        $this->assertNull($actual);

        // behavior with non-existent global variable
        try {
            $helper->getMysqlVariable('nonexistent');
            $this->fail('An expected Exception has not been thrown');
        } catch (RuntimeException $e) {
            // test against the mysql error message
            // unknown system variable error -> different exception message MySQL vs. MariaDB
            // so we test only that the error code is included
            $this->assertStringContainsString(
                "1193",
                $e->getMessage()
            );
        }

        // invalid variable type
        try {
            $helper->getMysqlVariable('nonexistent', '@@@');
            $this->fail('An expected Exception has not been thrown');
        } catch (InvalidArgumentException $e) {
            // test against the mysql error message
            $this->assertEquals(
                'Invalid mysql variable type "@@@", must be "@@" (system) or "@" (session)',
                $e->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function getTables()
    {
        $helper = $this->getHelper();

        $tables = $helper->getTables();
        $this->assertIsArray($tables);
        $this->assertContains('admin_user', $tables);
    }

    /**
     * @test
     */
    public function resolveTables()
    {
        $tables = $this->getHelper()->resolveTables(['catalog_*']);
        $this->assertContains('catalog_product_entity', $tables);
        $this->assertNotContains('catalogrule', $tables);

        $definitions = [
            'catalog_glob' => ['tables' => ['catalog_*']],
            'config_glob'  => ['tables' => ['core_config_dat?']],
            'directory'    => ['tables' => ['directory_country directory_country_format']],
        ];

        $tables = $this->getHelper()->resolveTables(
            ['@catalog_glob', '@config_glob', '@directory'],
            $definitions
        );
        $this->assertContains('catalog_product_entity', $tables);
        $this->assertContains('core_config_data', $tables);
        $this->assertContains('directory_country', $tables);
        $this->assertNotContains('catalogrule', $tables);
    }

    /**
     * @test
     */
    public function testGetViewsNoViewsFound()
    {
        $statementMock = $this->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementMock->expects($this->once())
            ->method('execute');
        $statementMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_COLUMN)
            ->willReturn([]);

        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT table_name FROM information_schema.VIEWS WHERE table_schema = :dbname'))
            ->willReturn($statementMock);

        $helper = $this->getMockBuilder(DatabaseHelper::class)
            ->onlyMethods(['getConnection']) // Mock only getConnection
            ->getMock();
        $helper->expects($this->once())
            ->method('getConnection')
            ->willReturn($pdoMock);

        // Manually set dbSettings as it's used directly
        $reflection = new ReflectionProperty(DatabaseHelper::class, 'dbSettings');
        $reflection->setAccessible(true);
        $reflection->setValue($helper, ['dbname' => 'test_db']);

        $this->assertEquals([], $helper->getViews());
    }

    /**
     * @test
     */
    public function testGetViewsSingleViewFound()
    {
        $expectedViews = ['my_single_view'];
        $statementMock = $this->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementMock->expects($this->once())->method('execute');
        $statementMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_COLUMN)
            ->willReturn($expectedViews);

        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($statementMock);

        $helper = $this->getMockBuilder(DatabaseHelper::class)
            ->onlyMethods(['getConnection'])
            ->getMock();
        $helper->expects($this->once())
            ->method('getConnection')
            ->willReturn($pdoMock);
        $reflection = new ReflectionProperty(DatabaseHelper::class, 'dbSettings');
        $reflection->setAccessible(true);
        $reflection->setValue($helper, ['dbname' => 'test_db']);
        $this->assertEquals($expectedViews, $helper->getViews());
    }

    /**
     * @test
     */
    public function testGetViewsMultipleViewsFound()
    {
        $expectedViews = ['view_one', 'view_two', 'view_three'];
        $statementMock = $this->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementMock->expects($this->once())->method('execute');
        $statementMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_COLUMN)
            ->willReturn($expectedViews);

        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($statementMock);

        $helper = $this->getMockBuilder(DatabaseHelper::class)
            ->onlyMethods(['getConnection'])
            ->getMock();
        $helper->expects($this->once())
            ->method('getConnection')
            ->willReturn($pdoMock);
        $reflection = new ReflectionProperty(DatabaseHelper::class, 'dbSettings');
        $reflection->setAccessible(true);
        $reflection->setValue($helper, ['dbname' => 'test_db']);
        $this->assertEquals($expectedViews, $helper->getViews());
    }

    /**
     * @test
     */
    public function testDropViewsNoViews()
    {
        $outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMock();
        $outputMock->expects($this->once())
            ->method('writeln')
            ->with('<comment>No views found to drop.</comment>');

        $helper = $this->getMockBuilder(DatabaseHelper::class)
            ->onlyMethods(['getViews', 'getConnection']) // Mock getViews and getConnection
            ->getMock();
        $helper->expects($this->once())
            ->method('getViews')
            ->willReturn([]);
        $helper->expects($this->never()) // getConnection should not be called if no views
            ->method('getConnection');

        $helper->dropViews($outputMock);
    }

    /**
     * @test
     */
    public function testDropViewsWithViews()
    {
        $viewsToDrop = ['view_alpha', 'view_beta'];
        $outputMessages = [];

        $outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMock();
        $outputMock->expects($this->any())
            ->method('writeln')
            ->will($this->returnCallback(function ($message) use (&$outputMessages) {
                $outputMessages[] = $message;
            }));

        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdoMock->expects($this->exactly(4)) // SET FOREIGN_KEY_CHECKS=0, DROP VIEW 1, DROP VIEW 2, SET FOREIGN_KEY_CHECKS=1
            ->method('exec')
            ->withConsecutive(
                [$this->equalTo('SET FOREIGN_KEY_CHECKS = 0;')],
                [$this->equalTo('DROP VIEW IF EXISTS `view_alpha`;')],
                [$this->equalTo('DROP VIEW IF EXISTS `view_beta`;')],
                [$this->equalTo('SET FOREIGN_KEY_CHECKS = 1;')]
            )
            ->willReturn(1); // Simulate successful execution

        $helper = $this->getMockBuilder(DatabaseHelper::class)
            ->onlyMethods(['getViews', 'getConnection'])
            ->getMock();
        $helper->expects($this->once())
            ->method('getViews')
            ->willReturn($viewsToDrop);
        $helper->expects($this->once()) // getConnection is called once
            ->method('getConnection')
            ->willReturn($pdoMock);

        $helper->dropViews($outputMock);

        $this->assertContains('<info>Dropping views...</info>', $outputMessages);
        $this->assertContains('<comment>Dropped view:</comment> view_alpha', $outputMessages);
        $this->assertContains('<comment>Dropped view:</comment> view_beta', $outputMessages);
        $this->assertContains('<info>Dropped 2 views.</info>', $outputMessages);
    }

    /**
     * @test
     */
    public function testDropViewsHandlesException()
    {
        $viewsToDrop = ['problem_view'];

        $outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMock();
        // We don't care about specific messages here, just that it might write something

        $pdoMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        // First exec (SET FOREIGN_KEY_CHECKS = 0) is fine
        // Second exec (DROP VIEW) throws exception
        // Third exec (SET FOREIGN_KEY_CHECKS = 1 in catch) should still be called
        $pdoMock->expects($this->exactly(3))
            ->method('exec')
            ->withConsecutive(
                [$this->equalTo('SET FOREIGN_KEY_CHECKS = 0;')],
                [$this->equalTo('DROP VIEW IF EXISTS `problem_view`;')],
                [$this->equalTo('SET FOREIGN_KEY_CHECKS = 1;')]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnValue(1),                            // SET FOREIGN_KEY_CHECKS = 0;
                $this->throwException(new PDOException('Drop failed')), // DROP VIEW problem_view
                $this->returnValue(1)                             // SET FOREIGN_KEY_CHECKS = 1;
            );

        $helper = $this->getMockBuilder(DatabaseHelper::class)
            ->onlyMethods(['getViews', 'getConnection'])
            ->getMock();
        $helper->expects($this->once())
            ->method('getViews')
            ->willReturn($viewsToDrop);
        $helper->expects($this->once())
            ->method('getConnection')
            ->willReturn($pdoMock);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error dropping views: Drop failed');

        $helper->dropViews($outputMock);
    }
}
