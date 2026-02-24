<?php

namespace N98\Magento\Command\Developer\Console\Structure;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ThemeNameStructureTest extends TestCase
{
    /**
     * @dataProvider validThemeNameProvider
     */
    public function testValidThemeName($area, $package, $name, $expectedArea, $expectedPackage, $expectedName)
    {
        $structure = new ThemeNameStructure($area, $package, $name);
        $this->assertEquals($expectedArea, $structure->getArea());
        $this->assertEquals($expectedPackage, $structure->getPackage());
        $this->assertEquals($expectedName, $structure->getName());
    }

    public function validThemeNameProvider()
    {
        return [
            ['frontend', 'Acme', 'default', 'frontend', 'Acme', 'default'],
            ['adminhtml', 'acme', 'BLUE', 'adminhtml', 'Acme', 'blue'],
            ['base', 'My-Vendor', 'my_theme', 'base', 'My-Vendor', 'my_theme'],
        ];
    }

    /**
     * @dataProvider invalidThemeNameProvider
     */
    public function testInvalidThemeName($area, $package, $name)
    {
        $this->expectException(InvalidArgumentException::class);
        new ThemeNameStructure($area, $package, $name);
    }

    public function invalidThemeNameProvider()
    {
        return [
            ['../', 'Package', 'Name'],
            ['Area', '../', 'Name'],
            ['Area', 'Package', '../'],
            ['Area!', 'Package', 'Name'],
        ];
    }
}
