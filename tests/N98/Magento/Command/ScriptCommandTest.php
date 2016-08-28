<?php

namespace N98\Magento\Command;

use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ScriptCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new ScriptCommand());
        $application->setAutoExit(false);
        $command = $this->getApplication()->find('script');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command'   => $command->getName(),
                'filename'  => __DIR__ . '/_files/test.mr',
            )
        );

        // Check pre defined vars
        $buffer = $commandTester->getDisplay();
        $this->assertRegExp('~^\Qmagento.root: \E/.+\R$~m', $buffer);
        $this->assertRegExp('~^\Qmagento.edition: \E(Community|Enterprise)\R$~m', $buffer);
        $this->assertRegExp('~^\Qmagento.version: \E\d\.\d+\.\d+\R$~m', $buffer);

        $this->assertContains('magerun.version: ' . $application->getVersion(), $buffer);

        $this->assertContains('code', $buffer);
        $this->assertContains('foo.sql', $buffer);
        $this->assertContains('BAR: foo.sql.gz', $buffer);
        $this->assertContains('Magento Websites', $buffer);
        $this->assertContains('web/secure/base_url', $buffer);
        $this->assertContains('web/seo/use_rewrites => 1', $buffer);
    }
}
