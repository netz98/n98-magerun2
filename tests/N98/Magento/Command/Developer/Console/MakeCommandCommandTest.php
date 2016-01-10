<?php

namespace N98\Magento\Command\Developer\Console;


use Magento\Framework\Filesystem\Directory\WriteInterface;
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

        $writerMock = $this->getMock(WriteInterface::class);
        $writerMock
            ->expects($this->once())
            ->method('writeFile')
            ->with(
                $this->anything(), // param 1
                $this->logicalAnd( // param 2
                    $this->stringContains('namespace N98\Dummy\Console\Command\Foo\Bar'),
                    $this->stringContains('class BazCommand'),
                    $this->stringContains('public function configure'),
                    $this->stringContains('public function execute')
                )
            );

        $command->setCurrentModuleDirectoryWriter($writerMock);

        $commandTester->execute(
            array(
                'classpath' => 'foo.bar.baz',
            )
        );
    }
}