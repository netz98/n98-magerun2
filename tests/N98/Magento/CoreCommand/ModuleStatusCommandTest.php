<?php

declare(strict_types=1);

namespace N98\Magento\CoreCommand;

class ModuleStatusCommandTest extends AbstractMagentoCoreCommandTestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            'module:status',
            'List of enabled modules:'
        );
    }
}
