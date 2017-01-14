<?php

namespace N98\Magento\Command\Config;

use N98\Magento\Command\TestCase;

class GetCommandTest extends TestCase
{
    public function testExecute()
    {
        /**
         * Add a new entry (to test for it)
         */
        $this->assertDisplayContains(
            array(
                'command' => 'config:set',
                'path'    => 'n98_magerun/foo/bar',
                'value'   => '1234',
            ),
            'n98_magerun/foo/bar => 1234'
        );

        $this->assertDisplayContains(
            array(
                'command' => 'config:get',
                'path'    => 'n98_magerun/foo/bar',
            ),
            '| n98_magerun/foo/bar | default | 0        | 1234  |'
        );

        $this->assertDisplayContains(
            array(
                'command'         => 'config:get',
                'path'            => 'n98_magerun/foo/bar',
                '--update-script' => true,
            ),
            "\$installer->setConfigData('n98_magerun/foo/bar', '1234');"
        );

        $this->assertDisplayContains(
            array(
                'command'          => 'config:get',
                'path'             => 'n98_magerun/foo/bar',
                '--magerun-script' => true,
            ),
            "config:set --scope-id=0 --scope=default -- 'n98_magerun/foo/bar' '1234'"
        );

        /**
         * Dump CSV
         */
        $input = array(
            'command'  => 'config:get',
            'path'     => 'n98_magerun/foo/bar',
            '--format' => 'csv',
        );
        $this->assertDisplayContains($input, 'Path,Scope,Scope-ID,Value');
        $this->assertDisplayContains($input, 'n98_magerun/foo/bar,default,0,1234');

        /**
         * Dump XML
         */
        $input = array(
            'command'  => 'config:get',
            'path'     => 'n98_magerun/foo/bar',
            '--format' => 'xml',
        );
        $this->assertDisplayContains($input, '<table>');
        $this->assertDisplayContains($input, '<Value>1234</Value>');

        /**
         * Dump JSON
         */
        $this->assertDisplayRegExp(
            array(
                'command'  => 'config:get',
                'path'     => 'n98_magerun/foo/bar',
                '--format' => 'json',
            ),
            '/"Value":\s*"1234"/'
        );
    }
}
