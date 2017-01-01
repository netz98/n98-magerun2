<?php

namespace N98\Magento\Command\Design;

use N98\Magento\Command\TestCase;

class DemoNoticeCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            array(
                'command'  => 'design:demo-notice',
                '--global' => true,
                '--on'     => true,
            ),
            'Demo Notice enabled'
        );

        $this->assertDisplayContains(
            array(
                'command'  => 'design:demo-notice',
                '--global' => true,
                '--off'    => true,
            ),
            'Demo Notice disabled'
        );
    }
}
