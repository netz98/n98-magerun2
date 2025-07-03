<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Application;

use Composer\Autoload\ClassLoader;
use ErrorException;
use N98\Magento\Application;
use N98\Magento\Command\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class ConfigTest
 *
 * @covers  N98\Magento\Application\Config
 * @package N98\Magento\Application
 */
class ConfigTest extends TestCase
{
    /**
     * @test
     */
    public function creation()
    {
        $config = new Config();
        $this->assertInstanceOf(__NAMESPACE__ . '\\Config', $config);
    }

    /**
     * @test
     */
    public function loader()
    {
        $config = new Config();

        try {
            $config->load();
            $this->fail('An expected exception was not thrown');
        } catch (ErrorException $e) {
            $this->assertEquals('Configuration not yet fully loaded', $e->getMessage());
        }

        $this->assertEquals([], $config->getConfig());

        $loader = $config->getLoader();
        $this->assertInstanceOf(__NAMESPACE__ . '\\ConfigurationLoader', $loader);
        $this->assertSame($loader, $config->getLoader());

        $loader->loadStageTwo("");
        $config->load();

        $this->assertIsArray($config->getConfig());
        $this->assertGreaterThan(4, count($config->getConfig()));

        $config->setLoader($loader);
    }

    /**
     * config array setter is used in some tests on @see \N98\Magento\Application::setConfig()
     *
     * @test
     */
    public function setConfig()
    {
        $config = new Config();
        $config->setConfig([0, 1, 2]);
        $actual = $config->getConfig();
        $this->assertSame($actual[1], 1);
    }

    /**
     * @test
     */
    public function configCommandAlias()
    {
        $config = new Config();
        $input = new ArgvInput();
        $actual = $config->checkConfigCommandAlias($input);
        $this->assertInstanceOf('Symfony\Component\Console\Input\InputInterface', $actual);

        $saved = $_SERVER['argv'];
        {
            $config->setConfig(['commands' => ['aliases' => [['list-help' => 'list --help']]]]);
            $definition = new InputDefinition();
            $definition->addArgument(new InputArgument('command'));

            $argv = ['/path/to/command', 'list-help'];
            $_SERVER['argv'] = $argv;
            $input = new ArgvInput($argv, $definition);
            $this->assertSame('list-help', (string) $input);
            $actual = $config->checkConfigCommandAlias($input);
            $this->assertSame('list-help', $actual->getFirstArgument());
            $this->assertSame('list-help --help', (string) $actual);
        }
        $_SERVER['argv'] = $saved;

        $command = new Command('list');

        $config->registerConfigCommandAlias($command);

        $this->assertSame(['list-help'], $command->getAliases());
    }

    /**
     * @test
     */
    public function customCommands()
    {
        $array = [
            'commands' => [
                'customCommands' => [
                    'N98\Magento\Command\Config\Store\GetCommand',
                    ['name' => 'N98\Magento\Command\Config\Store\GetCommand'],
                ],
            ],
        ];

        $output = new BufferedOutput();
        $output->setVerbosity($output::VERBOSITY_DEBUG);

        $config = new Config([], false, $output);
        $config->setConfig($array);

        /** @var Application $application */
        $application = $this->createMock('N98\Magento\Application');
        $config->registerCustomCommands($application);
    }

    /**
     * @test
     */
    public function registerCustomAutoloaders()
    {
        $array = [
            'autoloaders'      => ['$prefix' => '$path'],
            'autoloaders_psr4' => ['$prefix\\' => '$path'],
        ];

        $output = new BufferedOutput();

        $config = new Config([], false, $output);
        $config->setConfig($array);

        $autloader = new ClassLoader();
        $config->registerCustomAutoloaders($autloader);

        $output->setVerbosity($output::VERBOSITY_DEBUG);
        $config->registerCustomAutoloaders($autloader);
    }

    /**
     * @test
     */
    public function loadPartialConfig()
    {
        $config = new Config();
        $this->assertEquals([], $config->getDetectSubFolders());
        $config->loadPartialConfig(false);
        $actual = $config->getDetectSubFolders();
        $this->assertIsArray($actual);
        $this->assertNotEquals([], $actual);
    }
}
