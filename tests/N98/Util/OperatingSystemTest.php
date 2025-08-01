<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * this file is part of magerun
 *
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Util;

/**
 * Class OperatingSystemTest
 *
 * @package N98\Util
 * @covers N98\Util\OperatingSystem
 */
class OperatingSystemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function osDetection()
    {
        $matrix = [
            OperatingSystem::isLinux(),
            OperatingSystem::isWindows(),
            OperatingSystem::isMacOs(),
            OperatingSystem::isNetware(),
        ];

        $this->assertCount(4, $matrix, 'Number of OSes to check for');
        $this->assertCount(1, array_filter($matrix), 'One OS must be detected');
    }

    /**
     * @requires OS Linux
     */
    public function testIsLinux()
    {
        $this->assertTrue(OperatingSystem::isLinux());
        $this->assertFalse(OperatingSystem::isWindows());
        $this->assertFalse(OperatingSystem::isMacOs());
        $this->assertFalse(OperatingSystem::isNetware());
    }

    /**
     * @requires OS WIN32|WINNT
     */
    public function testIsWindows()
    {
        $this->assertTrue(OperatingSystem::isWindows());
        $this->assertFalse(OperatingSystem::isLinux());
        $this->assertFalse(OperatingSystem::isMacOs());
        $this->assertFalse(OperatingSystem::isNetware());
    }

    /**
     * @requires OS Darwin|Mac
     */
    public function testIsMacOs()
    {
        $this->assertTrue(OperatingSystem::isMacOs());
        $this->assertFalse(OperatingSystem::isLinux());
        $this->assertFalse(OperatingSystem::isWindows());
        $this->assertFalse(OperatingSystem::isNetware());
    }

    /**
     * @requires OS netware
     */
    public function testIsNetware()
    {
        $this->assertTrue(OperatingSystem::isNetware());
        $this->assertFalse(OperatingSystem::isLinux());
        $this->assertFalse(OperatingSystem::isWindows());
        $this->assertFalse(OperatingSystem::isMacOs());
    }

    /**
     * @test
     */
    public function getCwd()
    {
        $expected = getcwd();
        $this->assertEquals($expected, OperatingSystem::getCwd());
    }
}
