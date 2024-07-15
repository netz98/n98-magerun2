<?php

declare(strict_types=1);

namespace N98\Magento\CoreCommand;

class DeployModeShowCommandTest extends AbstractMagentoCoreCommandTestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            'deploy:mode:show',
            'Current application mode:'
        );
    }
}
