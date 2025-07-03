<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Setup;

use N98\Magento\Command\TestCase;

class AbstractSetupCommandTest extends TestCase
{
    /**
     * Mocked command
     * @var Mock_AbstractSetupCommand
     */
    protected $command;

    /**
     * Set up the mocked command for testing
     */
    protected function setUp(): void
    {
        $this->command = $this->getMockBuilder('N98\Magento\Command\System\Setup\AbstractSetupCommand')
            ->disableOriginalConstructor()
            ->setMethods(['getMagentoModuleList'])
            ->getMockForAbstractClass();

        $this->command
            ->expects($this->once())
            ->method('getMagentoModuleList')
            ->willReturn(['Magento_Catalog' => 'info', 'Magento_Customer' => 'info']);
    }

    /**
     * Test the getMagentoModuleName() method returns the actual module name when it exists
     * @param string $moduleName
     *
     * @dataProvider validModuleNameProvider
     */
    public function testShouldReturnModuleNameForExistingModule($moduleName)
    {
        $result = $this->command->getMagentoModuleName($moduleName);
        $this->assertStringStartsWith('Magento', $result);
    }

    /**
     * Provide some inconsistently cased module names
     * @return array
     */
    public function validModuleNameProvider()
    {
        return [
            ['magento_catalog'],
            ['magento_customer'],
            ['Magento_Catalog'],
            ['MaGeNtO_cUstOmeR'],
        ];
    }

    /**
     * Ensure that an exception is thrown when a module doesn't exist
     */
    public function testShouldThrowExceptionWhenModuleDoesntExist()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->command->getMagentoModuleName('Some_Module_That_Will_Never_Exist');
    }
}
