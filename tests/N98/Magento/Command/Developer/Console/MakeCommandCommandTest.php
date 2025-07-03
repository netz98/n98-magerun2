<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\Component\ComponentRegistrar;
use N98\Magento\Command\Developer\Console\Util\Config\DiFileWriter;

class MakeCommandCommandTest extends TestCase
{
    public function testOutput()
    {
        // fake path because Magento checks every path...
        ComponentRegistrar::register(
            ComponentRegistrar::MODULE,
            'N98_Dummy',
            __DIR__
        );

        $diFileWriterMock = $this->getMockBuilder(DiFileWriter::class)
            ->onlyMethods(['save', 'saveFile'])
            ->getMock();
        $diFileWriterMock->loadXml('<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" />');
        $diFileWriterMock->expects($this->any())
            ->method('save')
            ->willReturn(1);
        $diFileWriterMock->expects($this->any())
            ->method('saveFile')
            ->willReturn(1);

        $command = $this->getMockBuilder(MakeCommandCommand::class)
            ->onlyMethods(['createDiFileWriter'])
            ->getMock();

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
