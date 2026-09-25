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

class VersionRangeTest extends TestCase
{
    public function testContainsWithinBounds(): void
    {
        $range = new VersionRange('2.4.7', '2.4.7-p6');

        $this->assertTrue($range->contains('2.4.7'));
        $this->assertTrue($range->contains('2.4.7-p1'));
        $this->assertTrue($range->contains('2.4.7-p6'));
        $this->assertFalse($range->contains('2.4.7-p7'));
        $this->assertFalse($range->contains('2.4.6-p12'));
        $this->assertFalse($range->contains('2.4.8'));
    }

    public function testOpenEndedRange(): void
    {
        $range = new VersionRange('2.4.9', null);

        $this->assertFalse($range->contains('2.4.8'));
        $this->assertTrue($range->contains('2.4.9'));
        $this->assertTrue($range->contains('3.0.0'));
    }

    public function testFromArrayHandlesMissingKeys(): void
    {
        $range = VersionRange::fromArray([]);

        $this->assertTrue($range->contains('0.0.1'));
        $this->assertTrue($range->contains('999.0.0'));
    }
}
