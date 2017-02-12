<?php

namespace N98\Magento\Command\Developer;

use N98\Magento\Command\TestCase;

class SymlinksCommandTest extends TestCase
{
    public function testExecute()
    {
        $this->assertDisplayContains(
            array(
                'command'  => 'dev:symlinks',
                '--global' => true,
                '--on'     => true,
            ),
            'Symlinks allowed'
        );

        $this->assertDisplayContains(
            array(
                'command'  => 'dev:symlinks',
                '--global' => true,
                '--off'    => true,
            ),
            'Symlinks denied'
        );
    }
}
