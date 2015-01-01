<?php

namespace N98\Magento\Command;

use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

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
        $edition = 'Community'; // @TODO Replace this if EE is available
        $this->assertContains('magento.edition: ' . $edition, $commandTester->getDisplay());

        $this->assertContains('magento.root: ' . $this->getApplication()->getMagentoRootFolder(), $commandTester->getDisplay());
        $this->assertContains('magento.version: ' . \Magento\Framework\AppInterface::VERSION, $commandTester->getDisplay());
        $this->assertContains('magerun.version: ' . $this->getApplication()->getVersion(), $commandTester->getDisplay());

        $this->assertContains('code', $commandTester->getDisplay());
        $this->assertContains('foo.sql', $commandTester->getDisplay());
        $this->assertContains('BAR: foo.sql.gz', $commandTester->getDisplay());
        $this->assertContains('Magento Websites', $commandTester->getDisplay());
        $this->assertContains('web/secure/base_url', $commandTester->getDisplay());
        $this->assertContains('web/seo/use_rewrites => 1', $commandTester->getDisplay());
    }
}
