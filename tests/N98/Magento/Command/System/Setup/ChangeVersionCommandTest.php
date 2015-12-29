<?php

namespace N98\Magento\Command\System\Setup;

use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

class ChangeVersionCommandTest extends TestCase
{
    /**
     * Ensure that the resource setters are called
     */
    public function testExecute()
    {
        $command = $this->getMockBuilder('\N98\Magento\Command\System\Setup\ChangeVersionCommand')
            ->setMethods(array('getModuleName', 'getResource'))
            ->getMock();

        $command
            ->expects($this->once())
            ->method('getModuleName')
            ->with('magento_customer')
            ->will($this->returnValue('Magento_Customer'));

        $resourceMock = $this->getMockBuilder('\Magento\Framework\Module\ModuleResource')
            ->setMethods(array('setDbVersion', 'setDataVersion'))
            ->getMock();

        $resourceMock
            ->expects($this->once())
            ->method('setDbVersion')
            ->with('Magento_Customer');

        $resourceMock
            ->expects($this->once())
            ->method('setDataVersion')
            ->with('Magento_Customer');

        $command
            ->expects($this->exactly(2))
            ->method('getResource')
            ->will($this->returnValue($resourceMock));

        $application = $this->getApplication();
        $application->add($command);
        $command = $this->getApplication()->find('sys:setup:change-version');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'module'  => 'magento_customer',
                'version' => '1.2.3'
            )
        );

        $this->assertContains(
            'Successfully updated: "Magento_Customer" to version: "1.2.3"',
            $commandTester->getDisplay()
        );
    }
}
