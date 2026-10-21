<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\Config\Store;

use Magento\Framework\App\Config\ConfigSourceAggregated;
use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\Config\InitialConfigSource;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Filesystem\DriverPool;
use N98\Magento\Command\TestCase;

class GetCommandTest extends TestCase
{
    public function testNativeFilePrecedenceCompactListingAndScopeFallback()
    {
        $application = $this->getApplication();
        $store = $application->getObjectManager()->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore(1);
        $website = $store->getWebsite();
        $directory = sys_get_temp_dir() . '/n98-issue-712-' . bin2hex(random_bytes(8));
        mkdir($directory . '/app/etc', 0700, true);
        $defaults = ['n98_issue712' => ['test' => [
            'precedence' => 'xml', 'xml_only' => 'xml', 'zero' => '0', 'empty' => '', 'null' => null,
        ]]];
        $database = $this->createMock(ConfigSourceInterface::class);
        $databaseValues = ['default' => ['n98_issue712' => ['test' => ['precedence' => '86400']]]];
        $database->method('get')->willReturnCallback(function () use (&$databaseValues) {
            return $databaseValues;
        });
        $modular = $this->createMock(ConfigSourceInterface::class);
        $modular->method('get')->willReturn(['default' => $defaults]);
        $config = ['system' => [
            'default' => ['n98_issue712' => ['test' => ['precedence' => 'config', 'config_only' => 'config']]],
            'websites' => [$website->getCode() => ['n98_issue712' => ['test' => ['website_only' => 'website']]]],
        ]];
        $secret = 'issue712 synthetic secret';
        $ciphertext = $application->getObjectManager()->get(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->encrypt($secret);
        $env = ['system' => [
            'default' => [
                'n98_issue712' => ['test' => ['precedence' => '31536000', 'env_only' => 'env']],
                'paypal' => ['wpp' => ['api_password' => $ciphertext]],
            ],
            'stores' => [$store->getCode() => ['n98_issue712' => ['test' => ['store_only' => 'store']]]],
        ]];
        $configFile = $directory . '/app/etc/config.php';
        $envFile = $directory . '/app/etc/env.php';
        try {
            file_put_contents($configFile, '<?php return ' . var_export($config, true) . ';');
            file_put_contents($envFile, '<?php return ' . var_export($env, true) . ';');
            $deployment = new InitialConfigSource(new Reader(
                new DirectoryList($directory),
                new DriverPool(),
                new ConfigFilePool()
            ), 'system');
            $source = new ConfigSourceAggregated([
                ['source' => $modular, 'sortOrder' => 10],
                ['source' => $database, 'sortOrder' => 100],
                ['source' => $deployment, 'sortOrder' => 1000],
            ]);
            $command = $this->getMockBuilder(GetCommand::class)->onlyMethods(['getConfigurationSource'])->getMock();
            $command->setApplication($application);
            $command->method('getConfigurationSource')->willReturn($source);
            $read = new \ReflectionMethod(GetCommand::class, 'getEffectiveRows');
            $rows = $read->invoke($command, null, null, 'n98_issue712/test/*');
            $values = array_column(array_filter($rows, function ($row) {
                return $row['scope'] === 'default';
            }), 'value', 'path');
            $this->assertSame('31536000', $values['n98_issue712/test/precedence']);
            $this->assertSame('xml', $values['n98_issue712/test/xml_only']);
            $this->assertSame('config', $values['n98_issue712/test/config_only']);
            $this->assertSame('env', $values['n98_issue712/test/env_only']);
            $this->assertSame('0', $values['n98_issue712/test/zero']);
            $this->assertSame('', $values['n98_issue712/test/empty']);
            $this->assertNull($values['n98_issue712/test/null']);
            $this->assertCount(count($values) + 2, $rows, 'Compact output must not duplicate inherited paths.');
            $this->assertCount(count($rows) + 1, $read->invoke($command, null, null, ''));
            foreach ($rows as $row) {
                $this->assertSame('', $row['updated_at']);
            }
            $inherited = $read->invoke($command, 'stores', $store->getId(), 'n98_issue712/test/*');
            $this->assertCount(count($values) + 2, $inherited);
            $this->assertSame('website', array_column($inherited, 'value', 'path')['n98_issue712/test/website_only']);
            $this->assertSame('31536000', array_column($inherited, 'value', 'path')['n98_issue712/test/precedence']);
            $this->assertCount(1, $read->invoke($command, null, null, '%/xml_onl_'));
            $this->assertSame([], $read->invoke($command, null, null, 'n98_issue712/missing/*'));
            $this->assertSame(
                $ciphertext,
                $read->invoke($command, 'default', 0, 'paypal/wpp/api_password')[0]['value'],
                'Encrypted backend values must remain encrypted without --decrypt.'
            );
            $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
            $this->assertSame(0, $tester->execute([
                'path' => 'paypal/wpp/api_password', '--scope' => 'default', '--decrypt' => true, '--magerun-script' => true,
            ]));
            $this->assertStringContainsString('--encrypt ', $tester->getDisplay());
            $this->assertStringContainsString(escapeshellarg($secret), $tester->getDisplay());

            unlink($envFile);
            $this->assertSame('config', $read->invoke($command, 'default', 0, 'n98_issue712/test/precedence')[0]['value']);
            unlink($configFile);
            $this->assertSame('86400', $read->invoke($command, 'default', 0, 'n98_issue712/test/precedence')[0]['value']);
            $databaseValues = [];
            $this->assertSame('xml', $read->invoke($command, 'default', 0, 'n98_issue712/test/precedence')[0]['value']);
        } finally {
            foreach ([$configFile, $envFile] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            rmdir($directory . '/app/etc');
            rmdir($directory . '/app');
            rmdir($directory);
        }
    }

    public function testXmlOnlyDefaultAndTrailingSlashSearch()
    {
        $path = 'web/secure/use_in_frontend';
        $expected = $this->getApplication()->getObjectManager()->get(\Magento\Framework\App\Config::class)
            ->getValue($path);
        $tester = $this->assertExecute([
            'command' => 'config:store:get', 'path' => 'web/secure/', '--scope' => 'default', '--format' => 'json',
        ]);
        $rows = json_decode($tester->getDisplay(), true);
        $this->assertEquals($expected, array_column($rows, 'Value', 'Path')[$path]);
    }

    public function provideDecryptedMagerunScriptValues()
    {
        return [
            'default scope and shell quoting' => ["synthetic 'secret' $; --value\\nsecond\\rline", 'default', '0', false],
            'website scope' => ['synthetic website secret', 'websites', '1', false],
            'store scope' => ['synthetic store secret', 'stores', '1', false],
            'encrypted literal NULL' => ['NULL', 'default', '0', false],
            'SQL NULL' => ['NULL', 'default', '0', true],
        ];
    }

    /**
     * @dataProvider provideDecryptedMagerunScriptValues
     */
    public function testDecryptedMagerunScriptPreservesEncryptionAndNullSemantics($value, $scope, $scopeId, $isNull)
    {
        $path = 'n98_magerun/issue_2184/' . md5($value . $scope . $isNull);
        $setInput = [
            'command' => 'config:store:set',
            'path' => $path,
            'value' => $value,
            '--scope' => $scope,
            '--scope-id' => $scopeId,
        ];
        if (!$isNull) {
            $setInput['--encrypt'] = true;
            $setInput['--no-null'] = true;
        }

        try {
            $this->assertExecute($setInput);
            $tester = $this->assertExecute([
                'command' => 'config:store:get',
                'path' => $path,
                '--decrypt' => true,
                '--magerun-script' => true,
            ]);
            $options = $isNull ? '' : '--encrypt ';
            if (!$isNull && $value === 'NULL') {
                $options .= '--no-null ';
            }
            $expectedValue = $isNull ? 'NULL' : escapeshellarg($value);
            $this->assertSame(
                sprintf(
                    'config:store:set %s--scope-id=%s --scope=%s -- %s %s',
                    $options,
                    $scopeId,
                    $scope,
                    escapeshellarg($path),
                    $expectedValue
                ),
                trim($tester->getDisplay())
            );
        } finally {
            $this->assertExecute([
                'command' => 'config:store:delete',
                'path' => $path,
                '--all' => true,
            ]);
        }
    }

    /**
     * @test
     */
    public function testThatAStringNullShouldBeReturnedAsString()
    {
        $this->assertDisplayContains(
            [
                'command'   => 'config:store:set',
                'path'      => 'n98_magerun/foo/bar',
                '--no-null' => null,
                'value'     => 'NULL',
            ],
            'n98_magerun/foo/bar => NULL'
        );

        $this->assertDisplayContains(
            [
                'command'          => 'config:store:get',
                '--magerun-script' => null,
                'path'             => 'n98_magerun/foo/bar',
            ],
            'config:store:set --no-null'
        );
    }

    /**
     * @test
     */
    public function testThatAUnknownValueShouldHandledAsNullValue()
    {
        $this->assertDisplayContains(
            [
                'command'   => 'config:store:set',
                'path'      => 'n98_magerun/foo/bar',
                'value'     => 'NULL',
            ],
            'n98_magerun/foo/bar => NULL'
        );

        $this->assertDisplayNotContains(
            [
                'command'          => 'config:store:get',
                '--magerun-script' => null,
                'path'             => 'n98_magerun/foo/bar',
            ],
            '--no-null'
        );
    }

    /**
     * @test
     */
    public function nullValues()
    {
        $this->assertDisplayContains(
            [
                'command' => 'config:store:set',
                'path'    => 'n98_magerun/foo/bar',
                'value'   => 'NULL',
            ],
            'n98_magerun/foo/bar => NULL (NULL/"unknown" value)'
        );

        $this->assertDisplayContains(
            [
                'command' => 'config:store:get',
                'path'    => 'n98_magerun/foo/bar',
            ],
            '| n98_magerun/foo/bar | default | 0        | NULL (NULL/"unknown" value) |'
        );

        $this->assertDisplayContains(
            [
                'command'          => 'config:store:get',
                '--magerun-script' => true, # needed to not use the previous output cache
                'path'             => 'n98_magerun/foo/bar',
            ],
            'config:store:set --scope-id=0 --scope=default -- \'n98_magerun/foo/bar\' NULL'
        );
    }

    public function provideFormatsWithNull()
    {
        return [
            [null, '| n98_magerun/foo/bar | default | 0        | NULL (NULL/"unknown" value) |'],
            ['csv', 'n98_magerun/foo/bar,default,0,NULL'],
            ['json', '"Value": null'],
            ['xml', '<Value>NULL</Value>'],
        ];
    }

    /**
     * @test
     * @dataProvider provideFormatsWithNull
     */
    public function nullWithFormat($format, $expected)
    {
        $this->assertDisplayContains(
            [
                'command' => 'config:store:set',
                'path'    => 'n98_magerun/foo/bar',
                'value'   => 'NULL',
            ],
            'n98_magerun/foo/bar => NULL (NULL/"unknown" value)'
        );

        $this->assertDisplayContains(
            [
                'command'  => 'config:store:get',
                '--format' => $format,
                'path'     => 'n98_magerun/foo/bar',
            ],
            $expected
        );
    }

    public function testExecute()
    {
        /**
         * Add a new entry (to test for it)
         */
        $this->assertDisplayContains(
            [
                'command' => 'config:store:set',
                'path'    => 'n98_magerun/foo/bar',
                'value'   => '1234',
            ],
            'n98_magerun/foo/bar => 1234'
        );

        $this->assertDisplayContains(
            [
                'command' => 'config:store:get',
                'path'    => 'n98_magerun/foo/bar',
            ],
            '| n98_magerun/foo/bar | default | 0        | 1234  |'
        );

        $this->assertDisplayContains(
            [
                'command'         => 'config:store:get',
                'path'            => 'n98_magerun/foo/bar',
                '--update-script' => true,
            ],
            "\$installer->setConfigData('n98_magerun/foo/bar', '1234');"
        );

        $this->assertDisplayContains(
            [
                'command'          => 'config:store:get',
                'path'             => 'n98_magerun/foo/bar',
                '--magerun-script' => true,
            ],
            "config:store:set --scope-id=0 --scope=default -- 'n98_magerun/foo/bar' '1234'"
        );

        /**
         * Dump CSV
         */
        $input = [
            'command'  => 'config:store:get',
            'path'     => 'n98_magerun/foo/bar',
            '--format' => 'csv',
        ];
        // normalize quotes for test
        $this->assertDisplayContains(str_replace('"', '', $input), 'Path,Scope,Scope-ID,Value,"Updated At"');
        $this->assertDisplayContains($input, 'n98_magerun/foo/bar,default,0,1234');

        /**
         * Dump XML
         */
        $input = [
            'command'  => 'config:store:get',
            'path'     => 'n98_magerun/foo/bar',
            '--format' => 'xml',
        ];
        $this->assertDisplayContains($input, '<table>');
        $this->assertDisplayContains($input, '<Value>1234</Value>');

        /**
         * Dump JSON
         */
        $this->assertDisplayRegExp(
            [
                'command'  => 'config:store:get',
                'path'     => 'n98_magerun/foo/bar',
                '--format' => 'json',
            ],
            '/"Value":\s*"1234"/'
        );
    }

    public function testItAddsScopeIdFilterOnZero()
    {
        $path = 'n98_magerun/issue712/scope_filter';
        try {
            $this->assertExecute(['command' => 'config:store:set', 'path' => $path, 'value' => 'default']);
            $this->assertExecute([
                'command' => 'config:store:set', 'path' => $path, 'value' => 'website',
                '--scope' => 'websites', '--scope-id' => '1',
            ]);
            $tester = $this->assertExecute([
                'command' => 'config:store:get', 'path' => $path, '--scope-id' => '0', '--format' => 'json',
            ]);
            $rows = json_decode($tester->getDisplay(), true);
            $this->assertCount(1, $rows);
            $this->assertSame('default', $rows[0]['Value']);
        } finally {
            $this->assertExecute(['command' => 'config:store:delete', 'path' => $path, '--all' => true]);
        }
    }
}
