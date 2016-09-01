<?php

namespace N98\Magento\Command\Developer\Console;

class MakeHelperCommandTest extends TestCase
{
    /**
     * @test
     */
    public function testOutput()
    {
        $command = new MakeHelperCommand();

        $commandTester = $this->createCommandTester($command);
        $command->setCurrentModuleName('N98_Dummy');

        $writerMock = $this->mockWriterFileCWriteFileAssertion('bazHelper');

        $command->setCurrentModuleDirectoryWriter($writerMock);
        $commandTester->execute(['classpath' => 'foo.bar.bazHelper']);
    }
}
