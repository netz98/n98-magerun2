<?php
/*
 * @author Tom Klingenberg <mot@fsfe.org>
 */

namespace N98\Magento\Application;

use Composer\Autoload\ClassLoader;
use N98\Magento\Application;
use N98\Magento\Application\ConfigurationLoader;
use N98\Util\ArrayFunctions;
use N98\Util\BinaryString;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Config
 *
 * Class representing the application configuration. Created to factor out configuration related application
 * functionality from @see N98\Magento\Application
 *
 * @package N98\Magento\Application
 */
class Config
{
    /**
     * @var array config data
     */
    private $config = array();

    /**
     * @var array
     */
    private $partialConfig = array();

    /**
     * @var ConfigurationLoader
     */
    private $loader;

    /**
     * @var array
     */
    private $initConfig;

    /**
     * @var boolean
     */
    private $isPharMode;

    /**
     * @var OutputInterface
     */
    private $output;


    /**
     * Config constructor.
     * @param array $initConfig
     * @param bool $isPharMode
     * @param OutputInterface $output [optional]
     */
    public function __construct(array $initConfig = array(), $isPharMode = false, OutputInterface $output = null)
    {
        $this->initConfig = $initConfig;
        $this->isPharMode = (bool) $isPharMode;
        $this->output = $output ?: new NullOutput();
    }

    /**
     * alias magerun command in input from config
     *
     * @param InputInterface $input
     * @return ArgvInput|InputInterface
     */
    public function checkConfigCommandAlias(InputInterface $input)
    {
        if ($this->hasConfigCommandAliases()) {
            foreach ($this->config['commands']['aliases'] as $alias) {
                if (is_array($alias)) {
                    $aliasCommandName = key($alias);
                    if ($input->getFirstArgument() == $aliasCommandName) {
                        $aliasCommandParams = array_slice(BinaryString::trimExplodeEmpty(' ',
                            $alias[$aliasCommandName]), 1);
                        if (count($aliasCommandParams) > 0) {
                            // replace with aliased data
                            $mergedParams = array_merge(
                                array_slice($_SERVER['argv'], 0, 2),
                                $aliasCommandParams,
                                array_slice($_SERVER['argv'], 2)
                            );
                            $input = new ArgvInput($mergedParams);
                        }
                    }
                }
            }

            return $input;
        }

        return $input;
    }

    /**
     * @param Command $command
     */
    public function registerConfigCommandAlias(Command $command)
    {
        if ($this->hasConfigCommandAliases()) {
            foreach ($this->config['commands']['aliases'] as $alias) {
                if (!is_array($alias)) {
                    continue;
                }

                $aliasCommandName = key($alias);
                $commandString    = $alias[$aliasCommandName];

                list($originalCommand) = explode(' ', $commandString);
                if ($command->getName() == $originalCommand) {
                    $currentCommandAliases   = $command->getAliases();
                    $currentCommandAliases[] = $aliasCommandName;
                    $command->setAliases($currentCommandAliases);
                }
            }
        }
    }

    /**
     * @return bool
     */
    private function hasConfigCommandAliases()
    {
        return isset($this->config['commands']['aliases']) && is_array($this->config['commands']['aliases']);
    }

    /**
     * @param Application $application
     */
    public function registerCustomCommands(Application $application)
    {
        if (isset($this->config['commands']['customCommands'])
            && is_array($this->config['commands']['customCommands'])
        ) {
            foreach ($this->config['commands']['customCommands'] as $commandClass) {
                if (is_array($commandClass)) { // Support for key => value (name -> class)
                    $resolvedCommandClass = current($commandClass);
                    /** @var Command $command */
                    $command = new $resolvedCommandClass();
                    $command->setName(key($commandClass));
                } else {
                    /** @var Command $command */
                    $command = new $commandClass();
                }
                $application->add($command);

                $output = $this->output;
                if (OutputInterface::VERBOSITY_DEBUG <= $output->getVerbosity()) {
                    $output->writeln(
                        '<debug>Added command </debug><comment>' . get_class($command) . '</comment>'
                    );
                }
            }
        }
    }

    /**
     * Adds autoloader prefixes from user's config
     *
     * @param ClassLoader $autoloader
     */
    public function registerCustomAutoloaders(ClassLoader $autoloader)
    {
        $output = $this->output;

        if (isset($this->config['autoloaders']) && is_array($this->config['autoloaders'])) {
            foreach ($this->config['autoloaders'] as $prefix => $path) {
                $autoloader->add($prefix, $path);

                if (OutputInterface::VERBOSITY_DEBUG <= $output->getVerbosity()) {
                    $output->writeln(
                        '<debug>Registrered PSR-2 autoloader </debug> <info>'
                        . $prefix . '</info> -> <comment>' . $path . '</comment>'
                    );
                }
            }
        }

        if (isset($this->config['autoloaders_psr4']) && is_array($this->config['autoloaders_psr4'])) {
            foreach ($this->config['autoloaders_psr4'] as $prefix => $path) {
                $autoloader->addPsr4($prefix, $path);

                if (OutputInterface::VERBOSITY_DEBUG <= $output->getVerbosity()) {
                    $output->writeln(
                        '<debug>Registrered PSR-4 autoloader </debug> <info>'
                        . $prefix . ' </info> -> <comment>' . $path . '</comment>'
                    );
                }
            }
        }
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ConfigurationLoader $configurationLoader
     */
    public function setConfigurationLoader(ConfigurationLoader $configurationLoader)
    {
        $this->loader = $configurationLoader;
    }

    /**
     * @return ConfigurationLoader
     */
    public function getLoader()
    {
        if (!$this->loader) {
            $this->loader = $this->createLoader($this->initConfig, $this->isPharMode, $this->output);
            $this->initConfig = null;
        }

        return $this->loader;
    }

    public function load()
    {
        $this->config = $this->getLoader()->toArray();
    }

    /**
     * @param bool $loadExternalConfig
     */
    public function loadPartialConfig($loadExternalConfig)
    {
        $loader              = $this->getLoader();
        $this->partialConfig = $loader->getPartialConfig($loadExternalConfig);
    }

    /**
     * Get names of sub-folders to be scanned during Magento detection
     * @return array
     */
    public function getDetectSubFolders()
    {
        if (isset($this->partialConfig['detect']['subFolders'])) {
            return $this->partialConfig['detect']['subFolders'];
        }

        return array();
    }

    /**
     * @param array $initConfig
     * @param bool $isPharMode
     * @param OutputInterface $output
     *
     * @return ConfigurationLoader
     */
    public function createLoader(array $initConfig, $isPharMode, OutputInterface $output)
    {
        $config = ArrayFunctions::mergeArrays($this->config, $initConfig);

        $loader = new ConfigurationLoader($config, $isPharMode, $output);

        return $loader;
    }
}
