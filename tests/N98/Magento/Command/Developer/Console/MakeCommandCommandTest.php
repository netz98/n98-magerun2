<?php

namespace N98\Magento\Command\Developer\Console;

use N98\Magento\Command\Developer\Console\Util\Config\DiFileWriter;

class MakeCommandCommandTest extends TestCase
{
    /**
     * @test
     */
    public function testOutput()
    {
        $diFileWriterMock = $this->getMockBuilder(DiFileWriter::class)
            ->setMethods(['save'])
            ->getMock();
        $diFileWriterMock->loadXml('<config />');


        $command = $this->getMock(MakeCommandCommand::class, ['createDiFileWriter']);
        $command
            ->expects($this->once())
            ->method('createDiFileWriter')
            ->will($this->returnValue($diFileWriterMock));

        $commandTester = $this->createCommandTester($command);
        $command->setCurrentModuleName('N98_Dummy');

        $writerMock = $this->mockWriterFileCWriteFileAssertion('bazCommand');

        $command->setCurrentModuleDirectoryWriter($writerMock);
        $commandTester->execute(['classpath' => 'foo.bar.baz']);
    }
}
