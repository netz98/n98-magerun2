<?php

namespace N98\Magento\Command\Developer\Console;

use N98\Magento\Command\Developer\Console\PHPUnit\TestCase;

class MakeBlockCommandTest extends TestCase
{
    /**
     * @test
     */
    public function testOutput()
    {
        $command = new MakeBlockCommand();

        $commandTester = $this->createCommandTester($command);
        $command->setCurrentModuleName('N98_Dummy');

        $path = __DIR__ . '/_files/reference/BazBlock.php';
        $writerMock = $this->mockWriterFileWriteFileAssertion($path);

        $command->setCurrentModuleDirectoryWriter($writerMock);
        $commandTester->execute(['classpath' => 'foo.bar.bazBlock']);
    }
}
