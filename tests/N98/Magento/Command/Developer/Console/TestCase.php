<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use N98\Magento\Command\PHPUnit\TestCase as BaseTestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Psy\Context;
use Symfony\Component\Console\Tester\CommandTester;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param AbstractConsoleCommand $command
     * @return CommandTester
     */
    public function createCommandTester(AbstractConsoleCommand $command)
    {
        $di = $this->getApplication()->getObjectManager();

        $command->setContext(new Context());
        $command->setScopeVariable('di', $di);

        $commandTester = new CommandTester($command);

        return $commandTester;
    }

    /**
     * @param string $reference
     * @return PHPUnit_Framework_MockObject_MockObject|WriteInterface
     */
    protected function mockWriterFileCWriteFileAssertion($reference)
    {
        $path = sprintf(__DIR__ . '/_files/reference/%s.php', ucfirst($reference));

        $writerMock = $this->getMock(WriteInterface::class);
        $writerMock
            ->expects($this->once())
            ->method('writeFile')
            ->with(
                $this->anything(), // param1
                $this->callback(function ($subject) use ($path) {
                    // apply cs-fixes as the code generator is a mess
                    $replacements = [
                        // empty class/interface
                        '~\{\n\n\n\}\n\n$~' => "{\n}\n",
                        // fix end of file for class w content
                        '~    \}\n\n\n\}\n\n$~' => "    }\n}\n",
                        // fix beginning of class
                        '~^(class .*)\n{\n\n~m' => "\\1\n{\n"
                    ];
                    $buffer = preg_replace(array_keys($replacements), $replacements, $subject);
                    $expected = file_get_contents($path);

                    $this->assertEquals($buffer, $expected);

                    return $buffer === $expected;
                })
            );
        return $writerMock;
    }
}
