<?php

namespace N98\Magento\Command\Developer\Theme;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('dev:theme:list', 'Magento/blank');
    }
}
