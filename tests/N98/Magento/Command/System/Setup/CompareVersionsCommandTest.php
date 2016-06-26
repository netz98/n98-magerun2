<?php

namespace N98\Magento\Command\System\Setup;

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

class CompareVersionsCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new CompareVersionsCommand());
        $command = $this->getApplication()->find('sys:setup:compare-versions');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName()
            )
        );

        $result = $commandTester->getDisplay();

        $this->assertRegExp('/Setup/', $result);
        $this->assertRegExp('/Module/', $result);
        $this->assertRegExp('/DB/', $result);
        $this->assertRegExp('/Data/', $result);
        $this->assertRegExp('/Status/', $result);
    }

    public function testJunit()
    {
        vfsStream::setup();
        $application = $this->getApplication();
        $application->add(new CompareVersionsCommand());
        $command = $this->getApplication()->find('sys:setup:compare-versions');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'     => $command->getName(),
                '--log-junit' => vfsStream::url('root/junit.xml'),
            )
        );

        $this->assertFileExists(vfsStream::url('root/junit.xml'));
    }
}
