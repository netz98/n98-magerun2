<?php

namespace N98\Magento\Command\Config\Store;

use N98\Magento\Command\TestCase;

class GetCommandTest extends TestCase
{
    /**
     * @test
     */
    public function nullValues()
    {
        $this->assertDisplayRegExp(
            array(
                'command'   => 'config:store:set',
                '--no-null' => null,
                'path'      => 'n98_magerun/foo/bar',
                'value'     => 'NULL',
            ),
            '~^n98_magerun/foo/bar => NULL$~'
        );

        $this->assertDisplayContains(
            array(
                'command'          => 'config:store:get',
                '--magerun-script' => null,
                'path'             => 'n98_magerun/foo/bar',
            ),
            'config:store:set --no-null --scope-id=0 --scope=default'
        );

        $this->assertDisplayContains(
            array(
                'command' => 'config:store:set',
                'path'    => 'n98_magerun/foo/bar',
                'value'   => 'NULL',
            ),
            'n98_magerun/foo/bar => NULL (NULL/"unknown" value)'
        );

        $this->assertDisplayContains(
            array(
                'command' => 'config:store:get',
                'path'    => 'n98_magerun/foo/bar',
            ),
            '| n98_magerun/foo/bar | default | 0        | NULL (NULL/"unknown" value) |'
        );

        $this->assertDisplayContains(
            array(
                'command'          => 'config:store:get',
                '--magerun-script' => true, # needed to not use the previous output cache
                'path'             => 'n98_magerun/foo/bar',
            ),
            'config:store:set --scope-id=0 --scope=default -- \'n98_magerun/foo/bar\' NULL'
        );
    }

    public function provideFormatsWithNull()
    {
        return array(
            array(null, '| n98_magerun/foo/bar | default | 0        | NULL (NULL/"unknown" value) |'),
            array('csv', 'n98_magerun/foo/bar,default,0,NULL'),
            array('json', '"Value": null'),
            array('xml', '<Value>NULL</Value>'),
        );
    }

    /**
     * @test
     * @dataProvider provideFormatsWithNull
     */
    public function nullWithFormat($format, $expected)
    {
        $this->assertDisplayContains(
            array(
                'command' => 'config:store:set',
                'path'    => 'n98_magerun/foo/bar',
                'value'   => 'NULL',
            ),
            'n98_magerun/foo/bar => NULL (NULL/"unknown" value)'
        );

        $this->assertDisplayContains(
            array(
                'command'  => 'config:store:get',
                '--format' => $format,
                'path'     => 'n98_magerun/foo/bar',
            ),
            $expected
        );
    }

    public function testExecute()
    {
        /**
         * Add a new entry (to test for it)
         */
        $this->assertDisplayContains(
            array(
                'command' => 'config:store:set',
                'path'    => 'n98_magerun/foo/bar',
                'value'   => '1234',
            ),
            'n98_magerun/foo/bar => 1234'
        );

        $this->assertDisplayContains(
            array(
                'command' => 'config:store:get',
                'path'    => 'n98_magerun/foo/bar',
            ),
            '| n98_magerun/foo/bar | default | 0        | 1234  |'
        );

        $this->assertDisplayContains(
            array(
                'command'         => 'config:store:get',
                'path'            => 'n98_magerun/foo/bar',
                '--update-script' => true,
            ),
            "\$installer->setConfigData('n98_magerun/foo/bar', '1234');"
        );

        $this->assertDisplayContains(
            array(
                'command'          => 'config:store:get',
                'path'             => 'n98_magerun/foo/bar',
                '--magerun-script' => true,
            ),
            "config:store:set --scope-id=0 --scope=default -- 'n98_magerun/foo/bar' '1234'"
        );

        /**
         * Dump CSV
         */
        $input = array(
            'command'  => 'config:store:get',
            'path'     => 'n98_magerun/foo/bar',
            '--format' => 'csv',
        );
        $this->assertDisplayContains($input, 'Path,Scope,Scope-ID,Value');
        $this->assertDisplayContains($input, 'n98_magerun/foo/bar,default,0,1234');

        /**
         * Dump XML
         */
        $input = array(
            'command'  => 'config:store:get',
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
                'command'  => 'config:store:get',
                'path'     => 'n98_magerun/foo/bar',
                '--format' => 'json',
            ),
            '/"Value":\s*"1234"/'
        );
    }

    public function testItAddsScopeIdFilterOnZero()
    {
        $this->assertDisplayContains(
            array(
                'command' => 'config:store:set',
                'path'    => 'n98_magerun/foo/bar',
                'value'   => '1234',
            ),
            'n98_magerun/foo/bar => 1234'
        );

        $this->assertDisplayContains(
            array(
                'command'    => 'config:store:set',
                'path'       => 'n98_magerun/foo/bar',
                'value'      => '1234',
                '--scope-id' => '1',
            ),
            'n98_magerun/foo/bar => 1234'
        );

        $this->assertDisplayNotContains(
            array(
                'command'    => 'config:store:get',
                'path'       => 'n98_magerun/foo/bar',
                '--scope-id' => '0',
            ),
            'n98_magerun/foo/bar | default | 1        | 1234'
        );
    }
}
