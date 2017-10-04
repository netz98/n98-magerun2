<?php

namespace N98\Magento\Command\Developer\Console;

use Magento\Framework\App\State;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use N98\Magento\Command\TestCase as BaseTestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Psy\Context;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Tester\CommandTester;

abstract class TestCase extends BaseTestCase
{
    protected function setUp()
    {
        if ($this->runsInProductionMode()) {
            $this->markTestSkipped('Developer command is not available in production mode');
        }

        parent::setUp();
    }

    private function runsInProductionMode()
    {
        $di = $this->getApplication()->getObjectManager();

        $input = new ArgvInput();
        $output = new ConsoleOutput();

        /* @var $mode \Magento\Deploy\Model\Mode */
        $mode = $di->create(
            'Magento\Deploy\Model\Mode',
            [
                'input'  => $input,
                'output' => $output,
            ]
        );

        return $mode->getMode() === State::MODE_PRODUCTION;
    }

    /**
     * @param AbstractConsoleCommand $command
     * @return CommandTester
     */
    public function createCommandTester(AbstractConsoleCommand $command)
    {
        $di = $this->getApplication()->getObjectManager();

        $command->setContext(new Context());
        $command->setScopeVariable('di', $di);
        $command->setScopeVariable(
            'magentoVersion',
            $this->getApplication()->getObjectManager()->get(\Magento\Framework\App\ProductMetadataInterface::class)
        );

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
                        '~^(class .*)\n{\n\n~m' => "\\1\n{\n",
                    ];
                    $buffer = preg_replace(array_keys($replacements), $replacements, $subject);
                    $expected = file_get_contents($path);

                    $this->assertEquals($expected, $buffer);

                    return $buffer === $expected;
                })
            );

        return $writerMock;
    }
}
