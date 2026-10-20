<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util;

use PHPUnit\Framework\TestCase;

class ReflectionAccessTest extends TestCase
{
    public function testNonPublicMembersSupportReadWriteAndInvoke(): void
    {
        $this->assertSame(ReflectionAccessProbe::expected(), ReflectionAccessProbe::verify());
    }
}
