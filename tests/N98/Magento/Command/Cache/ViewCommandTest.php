<?php

namespace N98\Magento\Command\Cache;

use N98\Magento\Command\TestCase;

class ViewCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            [
                'command' => 'cache:view',
                'id'      => 'NON_EXISTING_ID',
                '--fpc'   => true,
            ],
            "Cache id NON_EXISTING_ID does not exist (anymore)"
        );
    }
}
