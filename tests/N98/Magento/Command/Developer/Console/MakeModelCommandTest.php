<?php

namespace N98\Magento\Command\Developer\Console;


use Magento\Framework\Filesystem\Directory\WriteInterface;

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

        $writerMock = $this->getMock(WriteInterface::class);
        $writerMock
            ->expects($this->once())
            ->method('writeFile')
            ->with(
                $this->anything(), // param1
                $this->equalTo(file_get_contents(__DIR__ . '/_files/reference_model.php'))
            );

        $command->setCurrentModuleDirectoryWriter($writerMock);
        $commandTester->execute(['classpath' => 'foo.bar.baz']);
    }
}