<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System;

use N98\Magento\Command\TestCase;

class CheckCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('sys:check', 'SETTINGS');
        $this->assertDisplayContains('sys:check', 'FILESYSTEM');
        $this->assertDisplayContains('sys:check', 'PHP');
        $this->assertDisplayContains('sys:check', 'MYSQL');
    }
}
