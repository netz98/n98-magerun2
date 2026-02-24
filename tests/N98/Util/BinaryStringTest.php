<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util;

/**
 * @covers \N98\Util\BinaryString
 */
class BinaryStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @param string $delimiter
     * @param string $string
     * @param array  $expected
     * @dataProvider trimExplodeEmptyProvider
     */
    public function trimExplodeEmpty($delimiter, $string, $expected)
    {
        $this->assertEqualsCanonicalizing($expected, BinaryString::trimExplodeEmpty($delimiter, $string));
    }

    /**
     * @return array
     */
    public static function trimExplodeEmptyProvider()
    {
        return [
            [
                ',',
                'Foo,Bar',
                ['Foo', 'Bar'],
            ],
            [
                '#',
                ' Foo# Bar',
                ['Foo', 'Bar'],
            ],
            [
                ',',
                ',,Foo, Bar,,',
                ['Foo', 'Bar'],
            ],
            [
                ',',
                '',
                [],
            ],
            [
                ',',
                '   ,   ',
                [],
            ],
            [
                ',',
                'Foo',
                ['Foo'],
            ]
        ];
    }

    /**
     * @test
     */
    public function trimExplodeEmptyPreservesKeys()
    {
        // This test documents the behavior that keys are preserved and gaps may exist.
        // Input: ',a,b,,'
        // Explode: ['', 'a', 'b', '', '']
        // Trim/Empty check removes 0, 3, 4.
        // Result: [1 => 'a', 2 => 'b']
        $result = BinaryString::trimExplodeEmpty(',', ',a,b,,');
        $this->assertEquals([1 => 'a', 2 => 'b'], $result);

        // Ensure keys are preserved
        $this->assertTrue(array_key_exists(1, $result));
        $this->assertTrue(array_key_exists(2, $result));
        $this->assertFalse(array_key_exists(0, $result));
    }

    /**
     * @test
     */
    public function startsWith()
    {
        $this->assertTrue(BinaryString::startsWith('Foo', 'Foo'));
        $this->assertTrue(BinaryString::startsWith('Foo123', 'Foo'));
        $this->assertFalse(BinaryString::startsWith(' Foo123', 'Foo'));

        // Edge cases
        $this->assertTrue(BinaryString::startsWith('Any', ''), 'Empty needle should return true');
        $this->assertFalse(BinaryString::startsWith('', 'Any'), 'Empty haystack with non-empty needle should return false');
        $this->assertTrue(BinaryString::startsWith('', ''), 'Empty haystack with empty needle should return true');
    }

    /**
     * @test
     */
    public function endsWith()
    {
        $this->assertTrue(BinaryString::endsWith('Foo', 'Foo'));
        $this->assertTrue(BinaryString::endsWith('Foo123', '123'));
        $this->assertFalse(BinaryString::endsWith(' Foo123 ', '123'));

        // Edge cases
        $this->assertTrue(BinaryString::endsWith('Any', ''), 'Empty needle should return true');
        $this->assertFalse(BinaryString::endsWith('', 'Any'), 'Empty haystack with non-empty needle should return false');
        $this->assertTrue(BinaryString::endsWith('', ''), 'Empty haystack with empty needle should return true');
    }
}
