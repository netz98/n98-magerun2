<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Website;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('sys:website:list', 'Magento Websites');
        $this->assertDisplayContains('sys:website:list', 'id');
        $this->assertDisplayContains('sys:website:list', 'code');
    }
}
