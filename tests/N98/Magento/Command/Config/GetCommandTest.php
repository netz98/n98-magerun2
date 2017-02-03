<?php

namespace N98\Magento\Command\Config;

use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GetCommandTest extends TestCase
{
    /**
     * @test
     */
    public function nullValues()
    {
        $this->assertDisplayRegExp(
            array(
                'command'   => 'config:set',
                '--no-null' => null,
                'path'      => 'n98_magerun/foo/bar',
                'value'     => 'NULL',
            ),
            '~^n98_magerun/foo/bar => NULL$~'
        );

        $this->assertDisplayContains(
            array(
                'command'          => 'config:get',
                '--magerun-script' => null,
                'path'             => 'n98_magerun/foo/bar',
            ),
            'config:set --no-null --scope-id=0 --scope=default'
        );

        $this->assertDisplayContains(
            array(
                'command' => 'config:set',
                'path'    => 'n98_magerun/foo/bar',
                'value'   => 'NULL',
            ),
            'n98_magerun/foo/bar => NULL (NULL/"unkown" value)'
        );

        $this->assertDisplayContains(
            array(
                'command' => 'config:get',
                'path'    => 'n98_magerun/foo/bar',
            ),
            '| n98_magerun/foo/bar | default | 0        | NULL (NULL/"unkown" value) |'
        );

        $this->assertDisplayContains(
            array(
                'command'          => 'config:get',
                '--magerun-script' => true, # needed to not use the previous output cache
                'path'             => 'n98_magerun/foo/bar',
            ),
            'config:set --scope-id=0 --scope=default -- \'n98_magerun/foo/bar\' NULL'
        );
    }

    public function provideFormatsWithNull()
    {
        return array(
            array(null, '| n98_magerun/foo/bar | default | 0        | NULL (NULL/"unkown" value) |'),
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
                'command' => 'config:set',
                'path'    => 'n98_magerun/foo/bar',
                'value'   => 'NULL',
            ),
            'n98_magerun/foo/bar => NULL (NULL/"unkown" value)'
        );

        $this->assertDisplayContains(
            array(
                'command'  => 'config:get',
                '--format' => $format,
                'path'     => 'n98_magerun/foo/bar',
            ),
            $expected
        );
    }

    public function testExecute()
    {
        $application = $this->getApplication();
        $application->add(new GetCommand());
        $setCommand = $this->getApplication()->find('config:set');
        $getCommand = $this->getApplication()->find('config:get');

        /**
         * Add a new entry
         */
        $commandTester = new CommandTester($setCommand);
        $commandTester->execute(
            array(
                    'command' => $setCommand->getName(),
                    'path'    => 'n98_magerun/foo/bar',
                    'value'   => '1234',
            )
        );

        $commandTester = new CommandTester($getCommand);
        $commandTester->execute(
            array(
                    'command' => $getCommand->getName(),
                    'path'    => 'n98_magerun/foo/bar',
            )
        );
        $this->assertContains('| n98_magerun/foo/bar | default | 0        | 1234  |', $commandTester->getDisplay());

        $commandTester->execute(
            array(
                    'command'         => $getCommand->getName(),
                    'path'            => 'n98_magerun/foo/bar',
                    '--update-script' => true,
            )
        );
        $this->assertContains(
            "\$installer->setConfigData('n98_magerun/foo/bar', '1234');",
            $commandTester->getDisplay()
        );

        $commandTester->execute(
            array(
                    'command'          => $getCommand->getName(),
                    'path'             => 'n98_magerun/foo/bar',
                    '--magerun-script' => true,
            )
        );
        $this->assertContains(
            "config:set --scope-id=0 --scope=default -- 'n98_magerun/foo/bar' '1234'",
            $commandTester->getDisplay()
        );

        /**
         * Dump CSV
         */
        $commandTester->execute(
            array(
                'command'  => $getCommand->getName(),
                'path'     => 'n98_magerun/foo/bar',
                '--format' => 'csv',
            )
        );
        $this->assertContains('Path,Scope,Scope-ID,Value', $commandTester->getDisplay());
        $this->assertContains('n98_magerun/foo/bar,default,0,1234', $commandTester->getDisplay());

        /**
         * Dump XML
         */
        $commandTester->execute(
            array(
                'command'  => $getCommand->getName(),
                'path'     => 'n98_magerun/foo/bar',
                '--format' => 'xml',
            )
        );
        $this->assertContains('<table>', $commandTester->getDisplay());
        $this->assertContains('<Value>1234</Value>', $commandTester->getDisplay());

        /**
         * Dump XML
         */
        $commandTester->execute(
            array(
                'command'  => $getCommand->getName(),
                'path'     => 'n98_magerun/foo/bar',
                '--format' => 'json',
            )
        );
        $this->assertRegExp('/"Value":\s*"1234"/', $commandTester->getDisplay());
    }
}
