<?php

namespace N98\Magento\Command\Developer\Report;

use N98\Magento\Command\TestCase;

class CountCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayRegExp('dev:report:count', '~^\d+\s+$~');
    }
}
