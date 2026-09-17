<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Util\Compatibility;

use PHPUnit\Framework\TestCase;

class VersionTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize(string $raw, string $expected): void
    {
        $this->assertSame($expected, Version::normalize($raw));
    }

    public function normalizeProvider(): array
    {
        return [
            'bare version' => ['2.4.8', '2.4.8.0'],
            'patch p1' => ['2.4.8-p1', '2.4.8.1'],
            'patch p12' => ['2.4.8-p12', '2.4.8.12'],
            'case insensitive patch marker' => ['2.4.8-P2', '2.4.8.2'],
            'mariadb vendor suffix' => ['10.11.6-MariaDB', '10.11.6.0'],
            'short version' => ['2.4', '2.4.0'],
        ];
    }

    public function testPatchReleasesOrderBetweenBaseAndNextVersion(): void
    {
        $base = new Version('2.4.8');
        $patch1 = new Version('2.4.8-p1');
        $patch12 = new Version('2.4.8-p12');
        $next = new Version('2.4.9');

        $this->assertTrue($patch1->isGreaterThanOrEqualTo($base));
        $this->assertSame(1, $patch1->compareTo($base));
        $this->assertSame(1, $patch12->compareTo($patch1));
        $this->assertSame(-1, $patch12->compareTo($next));
        $this->assertTrue($next->isGreaterThanOrEqualTo($patch12));
    }

    public function testEqualVersionsCompareToZero(): void
    {
        $this->assertSame(0, (new Version('2.4.8'))->compareTo(new Version('2.4.8')));
        $this->assertSame(0, (new Version('2.4.8-p1'))->compareTo(new Version('2.4.8-p1')));
    }
}
