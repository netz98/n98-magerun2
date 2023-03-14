<?php
/*
 * @author Tom Klingenberg <https://github.com/ktomk>
 */

namespace N98\Magento\Application;

use Composer\Autoload\ClassLoader;
use InvalidArgumentException;
use N98\Magento\Application;
use N98\Util\ArrayFunctions;
use N98\Util\BinaryString;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatter;
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
    private $config = [];

    /**
     * @var array
     */
    private $partialConfig = [];

    /**
     * @var ConfigurationLoader
     */
    private $loader;

    /**
     * @var array|null
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
     *
     * @param array $initConfig
     * @param bool $isPharMode
     * @param OutputInterface $output [optional]
     */
    public function __construct(array $initConfig = [], $isPharMode = false, OutputInterface $output = null)
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
        foreach ($this->getArray(['commands', 'aliases']) as $alias) {
            if (!is_array($alias)) {
                continue;
            }
            $aliasCommandName = key($alias);
            if ($input->getFirstArgument() !== $aliasCommandName) {
                continue;
            }
            $aliasCommandParams = array_slice(
                BinaryString::trimExplodeEmpty(' ', $alias[$aliasCommandName]),
                1
            );
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

        return $input;
    }

    /**
     * @param Command $command
     */
    public function registerConfigCommandAlias(Command $command)
    {
        foreach ($this->getArray(['commands', 'aliases']) as $alias) {
            if (!is_array($alias)) {
                continue;
            }

            $aliasCommandName = key($alias);
            $commandString = $alias[$aliasCommandName];
            list($originalCommand) = explode(' ', $commandString, 2);
            if ($command->getName() !== $originalCommand) {
                continue;
            }

            $command->setAliases(array_merge($command->getAliases(), [$aliasCommandName]));
        }
    }

    /**
     * @param Application $application
     */
    public function registerCustomCommands(Application $application)
    {
        foreach ($this->getArray(['commands', 'customCommands']) as $commandClass) {
            $commandName = null;
            if (is_array($commandClass)) {
                // Support for key => value (name -> class)
                $commandName = key($commandClass);
                $commandClass = current($commandClass);
            }
            $command = $this->newCommand($commandClass, $commandName);
            $this->debugWriteln(
                sprintf(
                    '<debug>Add command </debug> <info>%s</info> -> <comment>%s</comment>',
                    $command->getName(),
                    get_class($command)
                )
            );
            $application->add($command);
        }
    }

    /**
     * @param string $className
     * @param mixed $commandName
     * @return Command
     * @throws InvalidArgumentException
     */
    private function newCommand($className, $commandName)
    {
        /** @var Command $command */
        if (!(is_string($className) || is_object($className))) {
            throw new InvalidArgumentException(
                sprintf('Command classname must be string, %s given', gettype($className))
            );
        }

        $command = new $className();
        if (null !== $commandName) {
            $command->setName($commandName);
        }

        return $command;
    }

    /**
     * Adds autoloader prefixes from user's config
     *
     * @param ClassLoader $autoloader
     */
    public function registerCustomAutoloaders(ClassLoader $autoloader)
    {
        $mask = '<debug>Registered %s autoloader </debug> <info>%s</info> -> <comment>%s</comment>';

        foreach ($this->getArray('autoloaders') as $prefix => $path) {
            $autoloader->add($prefix, $path);
            $this->debugWriteln(sprintf($mask, 'PSR-0', $prefix, $path));
        }

        foreach ($this->getArray('autoloaders_psr4') as $prefix => $path) {
            if (is_array($path)) {
                foreach ($path as $prefix => $subPath) {
                    $this->addPsr4Namespace($autoloader, $prefix, $subPath, $mask);
                }
            } else {
                $this->addPsr4Namespace($autoloader, $prefix, $path, $mask);
            }
        }

        /**
         * We use box.phar to create the phar file and to dump the autoloader.
         * Box will always enable Class Map Authoritative which does not allow to load classes on runtime.
         * We need this to support custom commands and modules in n98-magerun2.
         * So we disable the Class Map Authoritative setting on runtime.
         *
         * @link https://github.com/box-project/box/blob/master/doc/configuration.md#dumping-the-composer-autoloader-dump-autoload
         */
        $autoloader->setClassMapAuthoritative(false);
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
        trigger_error(__METHOD__ . ' use setLoader() instead', E_USER_DEPRECATED);

        $this->setLoader($configurationLoader);
    }

    /**
     * @param ConfigurationLoader $configurationLoader
     */
    public function setLoader(ConfigurationLoader $configurationLoader)
    {
        $this->loader = $configurationLoader;
    }

    /**
     * @return ConfigurationLoader
     */
    public function getLoader()
    {
        if (!$this->loader) {
            $this->loader = $this->createLoader((array) $this->initConfig, $this->isPharMode, $this->output);
            $this->initConfig = null;
        }

        return $this->loader;
    }

    /**
     * @throws \ErrorException
     */
    public function load()
    {
        $this->config = $this->getLoader()->toArray();
    }

    /**
     * @param bool $loadExternalConfig
     */
    public function loadPartialConfig($loadExternalConfig)
    {
        $loader = $this->getLoader();
        $this->partialConfig = $loader->getPartialConfig($loadExternalConfig);
    }

    /**
     * Get names of sub-folders to be scanned during Magento detection
     *
     * @return array
     */
    public function getDetectSubFolders()
    {
        if (isset($this->partialConfig['detect']['subFolders'])) {
            return $this->partialConfig['detect']['subFolders'];
        }

        return [];
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

    /**
     * @param string $message
     */
    private function debugWriteln($message)
    {
        $output = $this->output;
        if (OutputInterface::VERBOSITY_DEBUG <= $output->getVerbosity()) {
            $output->writeln($message);
        }
    }

    /**
     * Get array from config, default to an empty array if not set
     *
     * @param string|array $key
     * @param array $default [optional]
     * @return array
     */
    private function getArray($key, $default = [])
    {
        $result = $this->traverse((array) $key);
        if (null === $result) {
            return $default;
        }

        return $result;
    }

    /**
     * @param array $keys
     * @return array|mixed|void
     */
    private function traverse(array $keys)
    {
        $anchor = &$this->config;
        foreach ($keys as $key) {
            if (!is_array($anchor)) {
                return;
            }

            if (!isset($anchor[$key])) {
                return;
            }
            $anchor = &$anchor[$key];
        }

        return $anchor;
    }

    /**
     * @param ClassLoader $autoloader
     * @param $prefix
     * @param $subPath
     * @param string $mask
     * @return void
     */
    protected function addPsr4Namespace(ClassLoader $autoloader, $prefix, $subPath, string $mask): void
    {
        $autoloader->addPsr4($prefix, $subPath);
        $this->debugWriteln(sprintf($mask, 'PSR-4', OutputFormatter::escape($prefix), $subPath));
    }
}
