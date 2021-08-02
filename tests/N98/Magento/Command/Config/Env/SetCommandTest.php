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

    public function dataProvider(): array
    {
        return [
            ['0'],
            ['1'],
            ['A'],
            ['B'],
            ['0A1B'],
        ];
    }

    /**
     * @dataProvider jsonDataProvider
     */
    public function testWithInputFormatJson($value)
    {
        $this->assertDisplayContains(
            [
                'command' => 'config:env:set',
                '--input-format' => 'json',
                'key' => 'magerun.test',
                'value' => $value
            ],
            'Config magerun.test successfully set to ' . $value
        );

        // Check for idempotency
        $this->assertDisplayContains(
            [
                'command' => 'config:env:set',
                '--input-format' => 'json',
                'key' => 'magerun.test',
                'value' => $value,
                '--verbose' => true // Add dummy option to force different input hash
            ],
            'Config was already set'
        );
    }

    public function jsonDataProvider(): array
    {
        return [
            [json_encode(20)],
            [json_encode('20')],
            [json_encode(1.0)],
            [json_encode('1.0')],
            [json_encode(true)],
            [json_encode('true')],
            [json_encode(['key' => 'value'])],
        ];
    }
}
