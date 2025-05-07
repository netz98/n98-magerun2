<?php

namespace N98\Magento\Command\Developer\Di\Preference;

use N98\Magento\Command\TestCase;

class ListCommandTest extends TestCase
{
    public function testGlobalList()
    {
        $this->assertDisplayContains(
            ['command' => 'dev:di:preferences:list', 'area' => 'global'],
            'Magento\Store\Api\Data\StoreInterface'
        );
    }

    public function testCrontabList()
    {
        $this->assertDisplayContains(
            ['command' => 'dev:di:preferences:list', 'area' => 'crontab'],
            'Magento\Backend\App\ConfigInterface'
        );
    }
}
