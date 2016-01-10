<?php

namespace N98\Magento\Command\Developer\Console;


use Magento\Framework\Filesystem\Directory\WriteInterface;

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

        $writerMock = $this->getMock(WriteInterface::class);
        $writerMock
            ->expects($this->once())
            ->method('writeFile')
            ->with(
                $this->anything(), // param1
                $this->logicalAnd( // param 2
                    $this->stringContains('namespace N98\Dummy\Controller\Foo\Bar'),
                    $this->stringContains('class Baz'),
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