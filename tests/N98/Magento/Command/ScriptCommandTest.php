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
        
        if (defined('\Magento\Framework\AppInterface::VERSION')) {
            // Magento 2.0 compatibility
            $magentoVersion = \Magento\Framework\AppInterface::VERSION;
            $magentoEdition = 'Community'; // @TODO Replace this if EE is available
        }
        else {
            // Magento 2.1+ compatibility
            /** @var \Magento\Framework\App\ProductMetadata $productMetadata */
            $productMetadata = $this->getApplication()->getObjectManager()->get('\Magento\Framework\App\ProductMetadata');
            
            $magentoVersion = $productMetadata->getVersion();
            $magentoEdition = $productMetadata->getEdition();           
        }        

        // Check pre defined vars
        $this->assertContains('magento.root: ' . $this->getApplication()->getMagentoRootFolder(), $commandTester->getDisplay());
        $this->assertContains('magento.version: ' . $magentoVersion, $commandTester->getDisplay());
        $this->assertContains('magento.edition: ' . $magentoEdition, $commandTester->getDisplay());
        
        $this->assertContains('magerun.version: ' . $this->getApplication()->getVersion(), $commandTester->getDisplay());

        $this->assertContains('code', $commandTester->getDisplay());
        $this->assertContains('foo.sql', $commandTester->getDisplay());
        $this->assertContains('BAR: foo.sql.gz', $commandTester->getDisplay());
        $this->assertContains('Magento Websites', $commandTester->getDisplay());
        $this->assertContains('web/secure/base_url', $commandTester->getDisplay());
        $this->assertContains('web/seo/use_rewrites => 1', $commandTester->getDisplay());
    }
}
