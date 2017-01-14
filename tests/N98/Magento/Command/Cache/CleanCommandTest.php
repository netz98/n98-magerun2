<?php

namespace N98\Magento\Command\Cache;

use N98\Magento\Command\TestCase;

class CleanCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('cache:clean', 'config cache cleaned');
    }

    public function testItCanCleanMultipleCaches()
    {
        $input = array(
            'command' => 'cache:clean',
            'type'    => array('config', 'layout'),

        );
        $this->assertDisplayContains($input, 'config cache cleaned');
        $this->assertDisplayContains($input, 'layout cache cleaned');
    }
}
