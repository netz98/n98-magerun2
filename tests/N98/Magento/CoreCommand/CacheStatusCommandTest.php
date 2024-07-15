<?php

declare(strict_types=1);

namespace N98\Magento\CoreCommand;

class CacheStatusCommandTest extends AbstractMagentoCoreCommandTestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            'cache:status',
            'Current status:'
        );
    }
}
