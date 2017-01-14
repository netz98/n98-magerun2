<?php

namespace N98\Magento\Command\Developer;

use N98\Magento\Command\TestCase;

class TemplateHintsCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            array(
                'command' => 'dev:template-hints',
                '--on'    => true,
                'store'   => 'admin',
            ),
            'Template Hints enabled'
        );

        $this->assertDisplayContains(
            array(
                'command' => 'dev:template-hints',
                '--off'   => true,
                'store'   => 'admin',
            ),
            'Template Hints disabled'
        );
    }
}
