<?php

namespace N98\Magento\Command\Cache;

use N98\Magento\Command\TestCase;

class FlushCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('cache:flush', 'cache flushed');
    }
}
