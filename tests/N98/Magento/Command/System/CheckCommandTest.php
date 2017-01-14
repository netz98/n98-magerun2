<?php

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
