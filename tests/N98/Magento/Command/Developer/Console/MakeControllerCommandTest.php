<?php

namespace N98\Magento\Command\Developer\Console;

class MakeControllerCommandTest extends TestCase
{
    /**
     * @test
     */
    public function testOutput()
    {
        $command = new MakeControllerCommand();

        $commandTester = $this->createCommandTester($command);
        $command->setCurrentModuleName('N98_Dummy');

        $writerMock = $this->mockWriterFileCWriteFileAssertion('bazController');

        $command->setCurrentModuleDirectoryWriter($writerMock);

        $commandTester->execute([
            'classpath' => 'foo.bar.bazController',
        ]);
    }
}
