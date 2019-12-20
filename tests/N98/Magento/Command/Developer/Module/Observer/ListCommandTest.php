<?php

namespace N98\Magento\Command\Developer\Module\Observer;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    const GLOBAL_EVENT_NAME = 'customer_address_save_before';
    const CRONTAB_OBSERVER_NAME = 'cron_observer';

    public function testGlobalList()
    {
        $this->assertDisplayContains(
            ['command' => 'dev:module:observer:list', 'area' => 'global'],
            self::GLOBAL_EVENT_NAME
        );
    }

    public function testCrontabList()
    {
        $this->assertDisplayContains(
            ['command' => 'dev:module:observer:list', 'area' => 'crontab'],
            self::CRONTAB_OBSERVER_NAME
        );
    }
}
