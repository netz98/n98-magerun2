<?php

namespace N98\Magento\Command\Developer\Console;

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

        $writerMock = $this->mockWriterFileCWriteFileAssertion('bazModel');

        $command->setCurrentModuleDirectoryWriter($writerMock);
        $commandTester->execute(['classpath' => 'foo.bar.bazModel']);
    }
}
