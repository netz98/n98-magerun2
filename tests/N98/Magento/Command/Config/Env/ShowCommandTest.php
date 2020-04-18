<?php

namespace N98\Magento\Command\Config\Env;

use N98\Magento\Command\TestCase;

/**
 * Class ShowCommandTest
 * @package N98\Magento\Command\Config\Env
 */
class ShowCommandTest extends TestCase
{
    public function testExecute()
    {
        /**
         * Install date should be found
         */
        $this->assertDisplayContains(
            [
                'command' => 'config:env:show',
            ],
            'install.date'
        );
    }
}
