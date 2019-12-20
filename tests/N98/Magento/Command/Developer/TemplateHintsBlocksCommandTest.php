<?php

namespace N98\Magento\Command\Developer;

use N98\Magento\Command\TestCase;

class TemplateHintsBlocksCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            [
                'command' => 'dev:template-hints-blocks',
                '--on'    => true,
                'store'   => 'admin',
            ],
            'Template Hints Blocks enabled'
        );

        $this->assertDisplayContains(
            [
                'command' => 'dev:template-hints-blocks',
                '--off'   => true,
                'store'   => 'admin',
            ],
            'Template Hints Blocks disabled'
        );
    }
}
