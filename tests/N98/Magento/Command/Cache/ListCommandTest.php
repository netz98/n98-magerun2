<?php
namespace N98\Magento\Command\Cache;

use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

class ListCommandTest extends TestCase
{
    /**
     * @var $command ListCommand
     */
    protected $command = null;

    public function setUp()
    {
        $application = $this->getApplication();
        $application->add(new ListCommand);

        $this->command = $this->getApplication()->find('cache:list');
    }

    /**
     * Test whether the $cacheTypes property is getting filled
     */
    public function testTypesIsNotEmpty()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array('command' => $this->command->getName()));

        $this->assertNotEmpty($this->command->getTypes());
    }

    /**
     * Test whether only enabled cache types are taken into account when --enabled=1
     */
    public function testEnabledFilter()
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array('command' => $this->command->getName(), '--enabled' => 1));

        $cacheTypes = $this->command->getTypes();
        $disabledCacheTypes = 0;

        foreach ($cacheTypes as $cacheType) {
            if (!$cacheType->getStatus()) {
                $disabledCacheTypes++;
            }
        }

        $this->assertEquals(0, $disabledCacheTypes);
    }
}
