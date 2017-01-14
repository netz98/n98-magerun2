<?php

namespace N98\Magento\Command\System\Cron;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('sys:cron:list', 'Cronjob List');
    }
}
