<?php

namespace N98\Magento\Command\Developer\Console\Structure;

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class ModuleNameStructureTest extends TestCase
{
    /**
     * @dataProvider validModuleNameProvider
     */
    public function testValidModuleName($fullModuleName, $expectedVendor, $expectedModule)
    {
        $structure = new ModuleNameStructure($fullModuleName);
        $this->assertEquals($expectedVendor, $structure->getVendorName());
        $this->assertEquals($expectedModule, $structure->getShortModuleName());
    }

    public function validModuleNameProvider()
    {
        return [
            ['Acme_Foo', 'Acme', 'Foo'],
            ['acme_foo', 'Acme', 'Foo'],
            ['Vendor123_Module456', 'Vendor123', 'Module456'],
        ];
    }

    /**
     * @dataProvider invalidModuleNameProvider
     */
    public function testInvalidModuleName($fullModuleName)
    {
        $this->expectException(InvalidArgumentException::class);
        new ModuleNameStructure($fullModuleName);
    }

    public function invalidModuleNameProvider()
    {
        return [
            ['Acme'],
            ['Acme_Foo_Bar'],
            ['../../_Foo'],
            ['Acme_../../Foo'],
            ['Vendor/../_Module'],
            ['Vendor_Module/..'],
            ['Vendor!_Module'],
            ['Vendor_Module!'],
            ['Vendor_Mod.ule'],
        ];
    }
}
