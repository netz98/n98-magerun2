<?php

namespace N98\Magento\Command\Developer\Theme;

use N98\Magento\Command\TestCase;

class BuildHyvaThemeCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains('dev:theme:build-hyva', 'Rebuilding...');
    }
}
