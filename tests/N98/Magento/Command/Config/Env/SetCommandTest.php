<?php

namespace N98\Magento\Command\Config\Env;

use N98\Magento\Command\TestCase;

/**
 * Class ShowCommandTest
 * @package N98\Magento\Command\Config\Env
 */
class SetCommandTest extends TestCase
{
    public function testExecute()
    {
        /**
         * Install date should be found
         */
        $this->assertDisplayContains(
            [
                'command' => 'config:env:set',
                'key' => 'magerun.test',
                'value' => '1'
            ],
            'Config magerun.test successfully set to 1'
        );
    }
}
