<?php

namespace N98\Magento;

use N98\Magento\Application\ConfigurationLoader;
use N98\Magento\Command\TestCase;
use N98\Util\ArrayFunctions;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

class ApplicationTest extends TestCase
{
    /**
     * @test
     */
    public function versionAligned()
    {
        // constant must be same as version.txt and latest in CHANGELOG.md

        $projectDir = __DIR__ . '/../../..';

        $versionFromVersionTxt = trim(file_get_contents($projectDir . '/version.txt'));

        $buffer = file_get_contents($projectDir . '/CHANGELOG.md');

        $versionFromChangelog = preg_match('#([0-9]|[1-9][0-9]*)\.([0-9]|[1-9][0-9]*)\.([0-9]|[1-9][0-9]*)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+[0-9A-Za-z-]+)?#', $buffer, $matches) ? $matches[0] : null;

        $this->assertEquals(Application::APP_VERSION, $versionFromVersionTxt, 'version.txt same as APP_VERSION');
        $this->assertEquals(Application::APP_VERSION, $versionFromChangelog, 'CHANGELOG.md same as APP_VERSION');
    }

    public function testExecute()
    {
        /**
         * Check autoloading
         */

        /* @var $application Application */
        $application = require __DIR__ . '/../../../src/bootstrap.php';
        $application->setMagentoRootFolder($this->getTestMagentoRoot());

        $this->assertInstanceOf(\N98\Magento\Application::class, $application);
        $loader = $application->getAutoloader();
        $this->assertInstanceOf(\Composer\Autoload\ClassLoader::class, $loader);

        /* @var $loader \Composer\Autoload\ClassLoader */
        $prefixes = $loader->getPrefixes();
        $this->assertArrayHasKey('N98', $prefixes);

        $distConfigArray = Yaml::parse(file_get_contents(__DIR__ . '/../../../config.yaml'));

        $configArray = [
            'autoloaders' => [
                'N98MagerunTest' => __DIR__ . '/_ApplicationTest/Src',
            ],
            'commands' => [
                'customCommands' => [
                    0 => 'N98MagerunTest\TestDummyCommand',
                ],
                'aliases' => [
                    [
                        'ssl' => 'sys:store:list',
                    ],
                ],
            ],
            'init' => [
                'options' => [
                    'config_model' => 'N98MagerunTest\AlternativeConfigModel',
                ],
            ],
        ];

        $application->setAutoExit(false);
        $application->init(ArrayFunctions::mergeArrays($distConfigArray, $configArray));
        $application->run(new StringInput('list'), new NullOutput());

        // Check if autoloaders, commands and aliases are registered
        $prefixes = $loader->getPrefixes();
        $this->assertArrayHasKey('N98MagerunTest', $prefixes);

        $testDummyCommand = $application->find('n98mageruntest:test:dummy');
        $this->assertInstanceOf(\N98MagerunTest\TestDummyCommand::class, $testDummyCommand);

        $commandTester = new CommandTester($testDummyCommand);
        $commandTester->execute(
            [
                'command' => $testDummyCommand->getName(),
            ]
        );
        $this->assertStringContainsString('dummy', $commandTester->getDisplay());
        $this->assertTrue($application->getDefinition()->hasOption('root-dir'));

        // check alias
        $this->assertInstanceOf(\N98\Magento\Command\System\Store\ListCommand::class, $application->find('ssl'));
    }

    public function testPlugins()
    {
        $this->getApplication(); // bootstrap implicit

        /**
         * Check autoloading
         */
        $application = require __DIR__ . '/../../../src/bootstrap.php';
        $application->setMagentoRootFolder($this->getTestMagentoRoot());

        // Load plugin config
        $injectConfig = [
            'plugin' => [
                'folders' => [
                    __DIR__ . '/_ApplicationTest/Modules',
                ],
            ],
        ];
        $application->init($injectConfig);

        // Check for module command
        $this->assertInstanceOf('TestModule\FooCommand', $application->find('testmodule:foo'));
    }

    public function testComposer()
    {
        $magerunProjectConfig = <<<CONFIG
foo:
  bar:
    magerun: "rockz!"
CONFIG;

        vfsStream::setup('root');
        vfsStream::create(
            [
                'htdocs' => [
                    'app' => [
                        'bootstrap.php' => '',
                        'etc' => [
                            'n98-magerun2.yaml' => $magerunProjectConfig
                        ]
                    ],
                ],
                'vendor' => [
                    'acme' => [
                        'magerun-test-module' => [
                            'n98-magerun2.yaml' => file_get_contents(__DIR__ . '/_ApplicationTest/Composer/n98-magerun2.yaml'),
                            'src'               => [
                                'Acme' => [
                                    'FooCommand.php' => file_get_contents(__DIR__ . '/_ApplicationTest/Composer/FooCommand.php'),
                                ],
                            ],
                        ],
                    ],
                    'n98' => [
                        'magerun' => [
                            'src' => [
                                'N98' => [
                                    'Magento' => [
                                        'Command' => [
                                            'ConfigurationLoader.php' => '',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $configurationLoader = $this->getMockBuilder(ConfigurationLoader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurationLoaderDir'])
            ->getMock();

        // simulate non phar mode
        $reflection = new \ReflectionClass($configurationLoader);
        $isPharModeReflectionProperty = $reflection->getProperty('isPharMode');
        $isPharModeReflectionProperty->setAccessible(true);
        $isPharModeReflectionProperty->setValue($configurationLoader, false);

        $configurationLoader
            ->expects($this->any())
            ->method('getConfigurationLoaderDir')
            ->willReturn(vfsStream::url('root/vendor/n98/magerun/src/N98/Magento/Command'));

        /* @var $application Application */
        $application = require __DIR__ . '/../../../src/bootstrap.php';
        $application->setMagentoRootFolder(vfsStream::url('root/htdocs'));
        $application->setConfigurationLoader($configurationLoader);
        $application->init();

        // Check for module command
        $this->assertInstanceOf('Acme\FooCommand', $application->find('acme:foo'));

        $config = $application->getConfig();

        // Check for loaded project data
        $this->assertArraySubset(
            [
                'foo' => [
                    'bar' => [
                        'magerun' => 'rockz!'
                    ]
                ]
            ],
            $config
        );
    }
}
