<?php

namespace N98\Magento\Command\Developer\Console;

use N98\Magento\Command\Developer\Console\PHPUnit\TestCase;

class MakeClassCommandTest extends TestCase
{
    /**
     * @test
     */
    public function testOutput()
    {
        $command = new MakeClassCommand();

        $commandTester = $this->createCommandTester($command);
        $command->setCurrentModuleName('N98_Dummy');

        $path = __DIR__ . '/_files/reference/BazClass.php';
        $writerMock = $this->mockWriterFileWriteFileAssertion($path);

        $command->setCurrentModuleDirectoryWriter($writerMock);
        $commandTester->execute(['classpath' => 'foo.bar.bazClass']);
    }
}
