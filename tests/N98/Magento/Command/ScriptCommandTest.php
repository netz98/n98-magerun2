<?php

namespace N98\Magento\Command;

use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

class ScriptCommandTest extends TestCase
{    
    /** @var null|\Magento\Framework\App\ProductMetadata  */
    protected $productMetadata = null;
    
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
        $this->assertContains('magento.root: ' . $this->getApplication()->getMagentoRootFolder(), $commandTester->getDisplay());
        $this->assertContains('magento.version: ' . $this->getMagentoVersion(), $commandTester->getDisplay());
        $this->assertContains('magento.edition: ' . $this->getMagentoEdition(), $commandTester->getDisplay());
        
        $this->assertContains('magerun.version: ' . $this->getApplication()->getVersion(), $commandTester->getDisplay());

        $this->assertContains('code', $commandTester->getDisplay());
        $this->assertContains('foo.sql', $commandTester->getDisplay());
        $this->assertContains('BAR: foo.sql.gz', $commandTester->getDisplay());
        $this->assertContains('Magento Websites', $commandTester->getDisplay());
        $this->assertContains('web/secure/base_url', $commandTester->getDisplay());
        $this->assertContains('web/seo/use_rewrites => 1', $commandTester->getDisplay());
    }

    /**
     * @return string
     */
    protected function getMagentoVersion()
    {
        return $this->getProductMetadata()->getVersion();
    }

    /**
     *
     * @return mixed
     */
    protected function getMagentoEdition()
    {
        return $this->getProductMetadata()->getEdition();
    }

    /**
     * @return \Magento\Framework\App\ProductMetadata
     */
    protected function getProductMetadata()
    {
        if(is_null($this->productMetadata)) {
            $objectManager         = $this->getApplication()->getObjectManager();
            $this->productMetadata = $objectManager->get('\Magento\Framework\App\ProductMetadata');
        }
        
        return $this->productMetadata;
    }
}
