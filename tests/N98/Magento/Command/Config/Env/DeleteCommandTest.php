<?php

namespace N98\Magento\Command\Config\Env;

use N98\Magento\Command\TestCase;

/**
 * Class DeleteCommandTest
 * @package N98\Magento\Command\Config\Env
 */
class DeleteCommandTest extends TestCase
{
    public function testExecute()
    {
        // first add a dummy key
        $this->assertExecute(
            [
                'command' => 'config:env:set',
                'key' => 'magerun.test',
                'value' => 'test'
            ]
        );

        // Check if config gets removed
        $this->assertDisplayContains(
            [
                'command' => 'config:env:delete',
                'key' => 'magerun.test'
            ],
            'Config magerun.test successfully removed'
        );

        // Check for idempotency
        $this->assertDisplayContains(
            [
                'command' => 'config:env:delete',
                'key' => 'magerun.test',
                '--verbose' => true // Add dummy option to force different input hash
            ],
            'Config doesn\'t exists'
        );
    }
}
