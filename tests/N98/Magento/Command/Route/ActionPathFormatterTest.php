<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N98\Magento\Command\Route;

use PHPUnit\Framework\TestCase;

class ActionPathFormatterTest extends TestCase
{
    public function testFormat()
    {
        // Dummy examples
        $this->assertSame('foo/bar', ActionPathFormatter::format('foo/bar'));
        $this->assertSame('foo_bar/zoz', ActionPathFormatter::format('foo/bar/zoz'));
        $this->assertSame('foo_bar_zoz/zozzl', ActionPathFormatter::format('foo/bar/zoz/zozzl'));

        // Real world examples
        $this->assertSame(
            'checkout_address/editaddress',
            ActionPathFormatter::format('checkout/address/editaddress')
        );
        $this->assertSame('product/view', ActionPathFormatter::format('product/view'));
    }
}
