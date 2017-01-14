<?php

namespace N98\Magento\Command\System\Cron;

use N98\Magento\Command\TestCase;

class HistoryCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('sys:cron:history', 'Last executed jobs');
    }
}
