<?php
namespace N98\Magento\Command\Developer\Module;

use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

class ListCommandTest extends TestCase
{
    const NONEXISTENT_VENDOR = 'FAKE_VENDOR';
    const MODULE_OCCURENCE_CHECK = 'Magento_Catalog';

    /**
     * @var $command ListCommand
     */
    protected $command = null;

    public function setUp()
    {
        $application = $this->getApplication();
        $application->add(new ListCommand);

        $this->command = $this->getApplication()->find('dev:module:list');
    }

    /**
     * Test whether the $moduleList property is filled
     */
    public function testBasicList()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array('command' => $this->command->getName()));

        $this->assertNotEmpty($this->command->getModuleList());
    }

    /**
     * Sanity test to check whether Magento_Core occurs in the output
     */
    public function testMagentoCatalogOccurs()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array('command' => $this->command->getName()));

        $this->assertNotFalse(strpos($commandTester->getDisplay(), self::MODULE_OCCURENCE_CHECK));
    }

    /**
     * Test whether we can filter on vendor (by checking a non-existent vendor, we should get an empty list)
     */
    public function testVendorList()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array('command' => $this->command->getName(), '--vendor' => self::NONEXISTENT_VENDOR));

        $this->assertEmpty($this->command->getModuleList());
    }
}
