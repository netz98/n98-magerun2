<?php

namespace N98\Magento\Command\System\Store;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('sys:store:list', 'id');
        $this->assertDisplayContains('sys:store:list', 'code');
    }
}
