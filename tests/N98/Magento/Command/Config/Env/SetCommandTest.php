<?php

namespace N98\Magento\Command\Config\Env;

use N98\Magento\Command\TestCase;

/**
 * Class ShowCommandTest
 * @package N98\Magento\Command\Config\Env
 */
class SetCommandTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testExecute($value)
    {
        // Check if config gets set
        $this->assertDisplayContains(
            [
                'command' => 'config:env:set',
                'key' => 'magerun.test',
                'value' => $value
            ],
            'Config magerun.test successfully set to ' . $value
        );

        // Check for idempotency
        $this->assertDisplayContains(
            [
                'command' => 'config:env:set',
                'key' => 'magerun.test',
                'value' => $value,
                '--verbose' => true // Add dummy option to force different input hash
            ],
            'Config was already set'
        );
    }

    public function dataProvider()
    {
        return [
            ['0'],
            ['1'],
            ['A'],
            ['B'],
            ['0A1B'],
        ];
    }
}
