<?php

namespace N98\Magento\Command\Installer\SubCommand;

use N98\Magento\Command\SubCommand\ConfigBag;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDatabaseTest extends TestCase
{
    /**
     * @test
     */
    public function validateDatabaseSettingsEscapesDatabaseName()
    {
        $inputMock = $this->createMock(InputInterface::class);
        $outputMock = $this->createMock(OutputInterface::class);

        $pdoMock = $this->createMock(PDO::class);
        $pdoStatementMock = $this->createMock(PDOStatement::class);

        // Mock query calls
        // 1. USE `db``name` -> returns false (triggering creation)
        // 2. CREATE DATABASE `db``name`
        // 3. USE `db``name`
        // 4. SELECT VERSION()

        $pdoMock->expects($this->exactly(4))
            ->method('query')
            ->withConsecutive(
                ['USE `db``name`'],
                ['CREATE DATABASE `db``name`'],
                ['USE `db``name`'],
                ['SELECT VERSION()']
            )
            ->willReturnOnConsecutiveCalls(
                false,
                $pdoStatementMock,
                $pdoStatementMock,
                $pdoStatementMock
            );

        $pdoStatementMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('5.7.0');

        $configBag = new ConfigBag();
        $configBag->setString('db_host', 'localhost');
        $configBag->setInt('db_port', 3306);
        $configBag->setString('db_user', 'user');
        $configBag->setString('db_pass', 'pass');
        $configBag->setString('db_name', 'db`name');

        /** @var CreateDatabase|\PHPUnit\Framework\MockObject\MockObject $subCommand */
        $subCommand = $this->getMockBuilder(CreateDatabase::class)
            ->onlyMethods(['createPdoConnection'])
            ->getMock();

        $subCommand->expects($this->once())
            ->method('createPdoConnection')
            ->willReturn($pdoMock);

        $subCommand->setConfig($configBag);

        $reflection = new \ReflectionClass(CreateDatabase::class);
        $method = $reflection->getMethod('validateDatabaseSettings');
        if (\PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $result = $method->invoke($subCommand, $inputMock, $outputMock);

        $this->assertSame($pdoMock, $result);
    }
}
