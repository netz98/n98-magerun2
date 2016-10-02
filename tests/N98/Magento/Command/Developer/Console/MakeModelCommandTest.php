<?php

namespace N98\Magento\Command\Developer\Console;

use N98\Magento\Command\Developer\Console\PHPUnit\TestCase;

class MakeModelCommandTest extends TestCase
{
    /**
     * @test
     */
    public function testOutput()
    {
        $command = new MakeModelCommand();

        $commandTester = $this->createCommandTester($command);
        $command->setCurrentModuleName('N98_Dummy');

        $path = __DIR__ . '/_files/reference/BazModel.php';
        $writerMock = $this->mockWriterFileWriteFileAssertion($path);

        $command->setCurrentModuleDirectoryWriter($writerMock);
        $commandTester->execute(['classpath' => 'foo.bar.bazModel']);
    }
}
