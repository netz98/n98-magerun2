<?php

namespace N98\Magento\Command\System\Setup;

use N98\Magento\Command\PHPUnit\TestCase;

class AbstractSetupCommandTest extends TestCase
{
    /**
     * Test the getModuleName() method returns the actual module name when it exists
     * @param  string $moduleName
     * @dataProvider validModuleNameProvider
     */
    public function testShouldReturnModuleNameForExistingModule($moduleName)
    {
        $subjectMock = $this->getSubjectMock();

        $result = $subjectMock->getModuleName($moduleName);
        $this->assertStringStartsWith('Magento', $result);
    }

    /**
     * Provide some inconsistently cased module names
     * @return string
     */
    public function validModuleNameProvider()
    {
        return array(
            array('magento_catalog'),
            array('magento_customer'),
            array('Magento_Catalog'),
            array('MaGeNtO_cUstOmeR')
        );
    }

    /**
     * Ensure that an exception is thrown when a module doesn't exist
     * @expectedException InvalidArgumentException
     */
    public function testShouldThrowExceptionWhenModuleDoesntExist()
    {
        $this->getSubjectMock()->getModuleName('Some_Module_That_Will_Never_Exist');
    }

    /**
     * Return a mocked test subject
     * @return Mock_ChangeVersionCommand
     */
    protected function getSubjectMock()
    {
        $moduleListMock = $this->getMockBuilder('\Magento\Framework\Module\ModuleList')
            ->setMethods(array('getAll'))
            ->getMock();

        $moduleListMock
            ->expects($this->once())
            ->method('getAll')
            ->will($this->returnValue(array('Magento_Catalog' => 'info', 'Magento_Customer' => 'info')));

        // Test one its children since the framework expects to be testing commands
        $subjectMock = $this->getMockBuilder('\N98\Magento\Command\System\Setup\ChangeVersionCommand')
            ->setMethods(array('getModuleList'))
            ->getMock();

        $subjectMock
            ->expects($this->once())
            ->method('getModuleList')
            ->will($this->returnValue($moduleListMock));

        return $subjectMock;
    }
}
