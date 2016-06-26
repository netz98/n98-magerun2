<?php

namespace N98\Magento;

use N98\Util\ArrayFunctions;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;
use Symfony\Component\Yaml\Yaml;
use org\bovigo\vfs\vfsStream;

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

        $versionFromChangelog = preg_match('~^\d+\.\d+\.\d+$~m', $buffer, $matches) ? $matches[0] : null;

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

        $this->assertInstanceOf('\N98\Magento\Application', $application);
        $loader = $application->getAutoloader();
        $this->assertInstanceOf('\Composer\Autoload\ClassLoader', $loader);

        /* @var $loader \Composer\Autoload\ClassLoader */
        $prefixes = $loader->getPrefixes();
        $this->assertArrayHasKey('N98', $prefixes);

        $distConfigArray = Yaml::parse(file_get_contents(__DIR__ . '/../../../config.yaml'));

        $configArray = array(
            'autoloaders' => array(
                'N98MagerunTest' => __DIR__ . '/_ApplicationTest/Src',
            ),
            'commands' => array(
                'customCommands' => array(
                    0 => 'N98MagerunTest\TestDummyCommand'
                ),
                'aliases' => array(
                    array(
                        'ssl' => 'sys:store:list'
                    )
                ),
            ),
            'init' => array(
                'options' => array(
                    'config_model' => 'N98MagerunTest\AlternativeConfigModel',
                )
            )
        );

        $application->setAutoExit(false);
        $application->init(ArrayFunctions::mergeArrays($distConfigArray, $configArray));
        $application->run(new StringInput('list'), new NullOutput());

        // Check if autoloaders, commands and aliases are registered
        $prefixes = $loader->getPrefixes();
        $this->assertArrayHasKey('N98MagerunTest', $prefixes);

        $testDummyCommand = $application->find('n98mageruntest:test:dummy');
        $this->assertInstanceOf('\N98MagerunTest\TestDummyCommand', $testDummyCommand);

        $commandTester = new CommandTester($testDummyCommand);
        $commandTester->execute(
            array(
                'command'    => $testDummyCommand->getName(),
            )
        );
        $this->assertContains('dummy', $commandTester->getDisplay());
        $this->assertTrue($application->getDefinition()->hasOption('root-dir'));

        // check alias
        $this->assertInstanceOf('\N98\Magento\Command\System\Store\ListCommand', $application->find('ssl'));
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
        $injectConfig = array(
            'plugin' => array(
                'folders' => array(
                    __DIR__ . '/_ApplicationTest/Modules'
                )
            )
        );
        $application->init($injectConfig);

        // Check for module command
        $this->assertInstanceOf('TestModule\FooCommand', $application->find('testmodule:foo'));
    }

    public function testComposer()
    {
        $this->markTestSkipped('Currently not working');

        vfsStream::setup('root');
        vfsStream::create(
            array(
                'htdocs' => array(
                    'app' => array(
                        'Mage.php' => ''
                    )
                ),
                'vendor' => array(
                    'acme' => array(
                        'magerun-test-module' => array(
                            'n98-magerun2.yaml' => file_get_contents(__DIR__ . '/_ApplicationTest/Composer/n98-magerun2.yaml'),
                            'src' => array(
                                'Acme' => array(
                                    'FooCommand.php' => file_get_contents(__DIR__ . '/_ApplicationTest/Composer/FooCommand.php'),
                                )
                            )
                        )
                    ),
                    'n98' => array(
                        'magerun' => array(
                            'src' => array(
                                'N98' => array(
                                    'Magento' => array(
                                        'Command' => array(
                                            'ConfigurationLoader.php' => '',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            )
        );

        $configurationLoader = $this->getMock(
            '\N98\Magento\Application\ConfigurationLoader',
            array('getConfigurationLoaderDir'),
            array(array(), false, new NullOutput())
        );
        $configurationLoader
            ->expects($this->any())
            ->method('getConfigurationLoaderDir')
            ->will($this->returnValue(vfsStream::url('root/vendor/n98/magerun/src/N98/Magento/Command')));

        /* @var $application Application */
        $application = require __DIR__ . '/../../../src/bootstrap.php';
        $application->setMagentoRootFolder(vfsStream::url('root/htdocs'));
        $application->setConfigurationLoader($configurationLoader);
        $application->init();

        // Check for module command
        $this->assertInstanceOf('Acme\FooCommand', $application->find('acme:foo'));
    }
}
