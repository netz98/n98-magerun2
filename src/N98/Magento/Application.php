<?php

namespace N98\Magento;

use BadMethodCallException;
use Composer\Autoload\ClassLoader;
use Exception;
use Magento\Framework\Console\Cli;
use Magento\Framework\ObjectManagerInterface;
use N98\Magento\Application\ApplicationAwareInterface;
use N98\Magento\Application\Config;
use N98\Magento\Application\ConfigurationLoader;
use N98\Magento\Application\Console\Events;
use N98\Magento\Application\DetectionResult;
use N98\Magento\Application\Magento1Initializer;
use N98\Magento\Application\Magento2Initializer;
use N98\Magento\Application\MagentoDetector;
use N98\Magento\Command\DummyCommand;
use N98\Util\Console\Helper\TwigHelper;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use UnexpectedValueException;

/**
 * Class Application
 * @package N98\Magento
 */
class Application extends BaseApplication
{
    /**
     * @var string
     */
    const APP_NAME = '@application_name@';

    /**
     * @var string
     */
    const APP_VERSION = '6.0.1';

    /**
     * @var int
     */
    const MAGENTO_MAJOR_VERSION_1 = 1;
    const MAGENTO_MAJOR_VERSION_2 = 2;

    /**
     * @var string
     */
    private static $logo = "
     ___ ___                                       ___
 _ _/ _ ( _ )___ _ __  __ _ __ _ ___ _ _ _  _ _ _ |_  )
| ' \\_, / _ \\___| '  \\/ _` / _` / -_) '_| || | ' \\ / /
|_||_/_/\\___/   |_|_|_\\__,_\\__, \\___|_|  \\_,_|_||_/___|
                           |___/
";
    /**
     * @var ClassLoader
     */
    protected $autoloader;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var bool
     */
    protected $_isPharMode = false;

    /**
     * @var bool
     */
    protected $_isInitialized = false;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @see \N98\Magento\Application::setConfigurationLoader()
     * @var ConfigurationLoader
     */
    private $configurationLoaderInjected;

    /**
     * @var string [optional] root folder not detected, but set via public setter
     * @see setMagentoRootFolder()
     */
    private $magentoRootFolderInjected;

    /**
     * @var int Magento Major Version to operate on by this Magerun application
     */
    private $magerunMajorVersion = self::MAGENTO_MAJOR_VERSION_2;

    /**
     * @var DetectionResult of the Magento application (e.g. v1/v2, Enterprise/Community, root-path)
     */
    private $detectionResult;

    /**
     * @var boolean
     */
    private $autoExit = true;

    /**
     * @param ClassLoader $autoloader
     */
    public function __construct($autoloader = null)
    {
        $this->autoloader = $autoloader;

        $appName = self::APP_NAME;

        if (strpos($appName, 'application_name') !== false) {
            $appName = 'n98-magerun2';
        }

        parent::__construct($appName, self::APP_VERSION);

        $this->preloadClassesBeforeMagentoCore();
    }

    /**
     * Sets whether to automatically exit after a command execution or not.
     *
     * Implemented on this level to allow early exit on configuration exceptions
     *
     * @see run()
     *
     * @param bool $boolean Whether to automatically exit after a command execution or not
     */
    public function setAutoExit($boolean)
    {
        $this->autoExit = (bool) $boolean;
        parent::setAutoExit($boolean);
    }

    /**
     * @param bool $mode
     */
    public function setPharMode($mode)
    {
        $this->_isPharMode = $mode;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return self::$logo . parent::getHelp();
    }

    public function getLongVersion()
    {
        return parent::getLongVersion() . ' (commit: @git_commit_short@) by <info>netz98 GmbH</info>';
    }

    /**
     * @return boolean
     */
    public function isMagentoEnterprise()
    {
        return $this->detectionResult->isEnterpriseEdition();
    }

    /**
     * @param string $magentoRootFolder
     */
    public function setMagentoRootFolder($magentoRootFolder)
    {
        $this->magentoRootFolderInjected = $magentoRootFolder;
    }

    /**
     * @return int|null
     */
    public function getMagentoMajorVersion()
    {
        return $this->detectionResult ? $this->detectionResult->getMajorVersion() : null;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        // TODO(TK) getter for config / getter for config array
        return $this->config->getConfig();
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config->setConfig($config);
    }

    /**
     * Runs the current application with possible command aliases
     *
     * @param InputInterface $input An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Exception
     * @throws \Throwable
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $input = $this->config->checkConfigCommandAlias($input);

        $event = new ConsoleEvent(new DummyCommand(), $input, $output);
        $this->dispatcher->dispatch($event, Events::RUN_BEFORE);

        return parent::doRun($input, $output);
    }

    /**
     * Loads and initializes the Magento application
     *
     * @param bool $soft
     *
     * @return bool false if magento root folder is not set, true otherwise
     * @throws \Exception
     */
    public function initMagento($soft = false)
    {
        if ($this->getMagentoRootFolder(true) === null) {
            return false;
        }

        $isMagento2 = $this->detectionResult->getMajorVersion() === self::MAGENTO_MAJOR_VERSION_2;
        if ($isMagento2) {
            $magento2Initializer = new Magento2Initializer($this->getAutoloader());
            $app = $magento2Initializer->init($this->getMagentoRootFolder());
            $this->_objectManager = $app->getObjectManager();
        } else {
            $magento1Initializer = new Magento1Initializer($this->getHelperSet());
            $magento1Initializer->init();
        }

        return true;
    }

    /**
     * @param bool $preventException [optional] on uninitialized magento root folder (returns null then, caution!)
     * @return string|null
     */
    public function getMagentoRootFolder($preventException = false)
    {
        if (null !== $this->magentoRootFolderInjected) {
            return $this->magentoRootFolderInjected;
        }

        if ($preventException) {
            return $this->detectionResult ? $this->detectionResult->getRootFolder() : null;
        }

        if (!$this->detectionResult) {
            throw new BadMethodCallException('Magento-root-folder is not yet detected (nor set)');
        }

        return $this->detectionResult->getRootFolder();
    }

    /**
     * @return ClassLoader
     */
    public function getAutoloader()
    {
        return $this->autoloader;
    }

    /**
     * @param ClassLoader $autoloader
     */
    public function setAutoloader(ClassLoader $autoloader)
    {
        $this->autoloader = $autoloader;
    }

    /**
     * @param InputInterface $input [optional]
     * @param OutputInterface $output [optional]
     *
     * @return int
     * @throws \Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }
        $this->_addOutputStyles($output);
        if ($output instanceof ConsoleOutput) {
            $this->_addOutputStyles($output->getErrorOutput());
        }

        $this->configureIO($input, $output);

        try {
            $this->init([], $input, $output);
        } catch (Exception $e) {
            $output = new ConsoleOutput();
            $this->renderThrowable($e, $output->getErrorOutput());
            $exitCode = max(1, min(255, (int) $e->getCode()));
            if ($this->autoExit) {
                die($exitCode);
            }

            return $exitCode;
        }

        $return = parent::run($input, $output);

        // Fix for no return values -> used in interactive shell to prevent error output
        if ($return === null) {
            return 0;
        }

        return $return;
    }

    /**
     * @param OutputInterface $output
     */
    protected function _addOutputStyles(OutputInterface $output)
    {
        $output->getFormatter()->setStyle('debug', new OutputFormatterStyle('magenta', 'white'));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('red', 'yellow', ['bold']));
    }

    /**
     * @param array $initConfig [optional]
     * @param InputInterface $input [optional]
     * @param OutputInterface $output [optional]
     *
     * @return void
     * @throws \Exception
     */
    public function init(array $initConfig = [], InputInterface $input = null, OutputInterface $output = null)
    {
        if ($this->_isInitialized) {
            return;
        }

        // Suppress DateTime warnings
        date_default_timezone_set(@date_default_timezone_get());

        // Initialize EventDispatcher early
        $this->dispatcher = new EventDispatcher();
        $this->setDispatcher($this->dispatcher);

        $input = $input ?: new ArgvInput();
        $output = $output ?: new ConsoleOutput();

        if (null !== $this->config) {
            throw new UnexpectedValueException(sprintf('Config already initialized'));
        }

        $loadExternalConfig = !$this->_checkSkipConfigOption($input);

        $this->config = new Config($initConfig, $this->isPharMode(), $output);
        if ($this->configurationLoaderInjected) {
            $this->config->setLoader($this->configurationLoaderInjected);
        }
        $this->config->loadPartialConfig($loadExternalConfig);
        $this->detectMagento($input, $output);

        $configLoader = $this->config->getLoader();
        $configLoader->loadStageTwo(
            $this->getMagentoRootFolder(true),
            $loadExternalConfig,
            $this->detectionResult->getMagerunStopFileFolder()
        );
        $this->config->load();

        if ($autoloader = $this->autoloader) {
            /**
             * Include commands shipped by Magento 2 core
             */
            if (!$this->_checkSkipMagento2CoreCommandsOption($input)) {
                $this->registerMagentoCoreCommands($output);
            }
            $this->config->registerCustomAutoloaders($autoloader);
            $this->registerEventSubscribers($input, $output);
            $this->config->registerCustomCommands($this);
        }

        $this->registerHelpers();

        $this->_isInitialized = true;
    }

    /**
     * @param InputInterface $input
     * @return bool
     */
    protected function _checkSkipConfigOption(InputInterface $input): bool
    {
        return $input->hasParameterOption('--skip-config');
    }

    /**
     * @return bool
     */
    public function isPharMode(): bool
    {
        return $this->_isPharMode;
    }

    /**
     * Search for magento root folder
     *
     * @param InputInterface $input [optional]
     * @param OutputInterface $output [optional]
     * @return void
     * @throws \Exception
     */
    public function detectMagento(InputInterface $input = null, OutputInterface $output = null)
    {
        if ($this->detectionResult) {
            return;
        }

        $magentoRootDirectory = $this->getMagentoRootFolder(true);

        $detector = new MagentoDetector();
        $this->detectionResult = $detector->detect(
            $input,
            $output,
            $this->config,
            $this->getHelperSet(),
            $magentoRootDirectory
        );

        if ($this->detectionResult->isDetected()) {
            $magentoMajorVersion = $this->detectionResult->getMajorVersion();
            if ($magentoMajorVersion !== $this->magerunMajorVersion) {
                $magento1Initialiter = new Magento1Initializer($this->getHelperSet());
                $magento1Initialiter->init();
            }
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return bool
     */
    protected function _checkSkipMagento2CoreCommandsOption(InputInterface $input): bool
    {
        return $input->hasParameterOption('--skip-core-commands') || getenv('MAGERUN_SKIP_CORE_COMMANDS');
    }

    /**
     * Try to bootstrap magento 2 and load cli application
     *
     * @param OutputInterface $output
     */
    public function registerMagentoCoreCommands(OutputInterface $output): void
    {
        $magentoRootFolder = $this->getMagentoRootFolder();
        if (empty($magentoRootFolder)) {
            return;
        }

        // Magento was found -> register core cli commands
        try {
            \N98\Magento\Application\Magento2Initializer::loadMagentoBootstrap($magentoRootFolder);
        } catch (Exception $ex) {
            $this->renderThrowable($ex, $output);
            $output->writeln(
                '<info>Use --skip-core-commands to not require the Magento app/bootstrap.php which caused ' .
                'the exception.</info>'
            );

            return;
        }

        $coreCliApplication = new Cli();
        $coreCliApplicationCommands = $coreCliApplication->all();

        foreach ($coreCliApplicationCommands as $coreCliApplicationCommand) {
            if (OutputInterface::VERBOSITY_DEBUG <= $output->getVerbosity()) {
                $output->writeln(
                    sprintf(
                        '<debug>Add core command </debug> <info>%s</info> -> <comment>%s</comment>',
                        $coreCliApplicationCommand->getName(),
                        get_class($coreCliApplicationCommand)
                    )
                );
            }
            $this->add($coreCliApplicationCommand);
        }
    }

    /**
     * Override standard command registration. We want alias support.
     *
     * @param Command $command
     *
     * @return Command
     */
    public function add(Command $command)
    {
        if ($this->config) {
            $this->config->registerConfigCommandAlias($command);
        }

        return parent::add($command);
    }

    /**
     * @return void
     */
    protected function registerEventSubscribers(InputInterface $input, OutputInterface $output)
    {
        $config = $this->config->getConfig();

        if (!isset($config['event']['subscriber'])) {
            return;
        }

        $subscriberClasses = $config['event']['subscriber'];
        foreach ($subscriberClasses as $subscriberClass) {
            $subscriber = new $subscriberClass($this, $input, $output);

            if ($subscriber instanceof ApplicationAwareInterface) {
                $subscriber->setApplication($this);
            }

            $this->dispatcher->addSubscriber($subscriber);
        }
    }

    /**
     * Add own helpers to helperset.
     *
     * @return void
     */
    protected function registerHelpers()
    {
        $helperSet = $this->getHelperSet();
        $config = $this->config->getConfig();

        if (empty($config)) {
            return;
        }

        // Twig
        $twigBaseDirs = [
            __DIR__ . '/../../../res/twig',
        ];
        if (isset($config['twig']['baseDirs']) && is_array($config['twig']['baseDirs'])) {
            $twigBaseDirs = array_merge(array_reverse($config['twig']['baseDirs']), $twigBaseDirs);
        }
        $helperSet->set(new TwigHelper($twigBaseDirs), 'twig');

        foreach ($config['helpers'] as $helperName => $helperClass) {
            if (class_exists($helperClass)) {
                $helperSet->set(new $helperClass(), $helperName);
            }
        }
    }

    /**
     * @param array $initConfig [optional]
     * @param InputInterface $input [optional]
     * @param OutputInterface $output [optional]
     * @throws \Exception
     */
    public function reinit($initConfig = [], InputInterface $input = null, OutputInterface $output = null)
    {
        $this->_isInitialized = false;
        $this->detectionResult = null;
        $this->config = null;
        $this->init($initConfig, $input, $output);
    }

    /**
     * @param ConfigurationLoader $configurationLoader
     */
    public function setConfigurationLoader(ConfigurationLoader $configurationLoader)
    {
        if ($this->config) {
            $this->config->setLoader($configurationLoader);
        } else {
            /* inject loader to be used later when config is created in */
            /* @see \N98\Magento\Application::init() */
            $this->configurationLoaderInjected = $configurationLoader;
        }
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    /**
     * @return InputDefinition
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $inputDefinition = parent::getDefaultInputDefinition();

        /**
         * Root dir
         */
        $rootDirOption = new InputOption(
            '--root-dir',
            '',
            InputOption::VALUE_OPTIONAL,
            'Force magento root dir. No auto detection'
        );
        $inputDefinition->addOption($rootDirOption);

        /**
         * Skip config
         */
        $skipExternalConfig = new InputOption(
            '--skip-config',
            '',
            InputOption::VALUE_NONE,
            'Do not load any custom config.'
        );
        $inputDefinition->addOption($skipExternalConfig);

        /**
         * Skip root check
         */
        $skipExternalConfig = new InputOption(
            '--skip-root-check',
            '',
            InputOption::VALUE_NONE,
            'Do not check if n98-magerun runs as root'
        );
        $inputDefinition->addOption($skipExternalConfig);

        /**
         * Skip core commands
         */
        $skipMagento2CoreCommands = new InputOption(
            '--skip-core-commands',
            '',
            InputOption::VALUE_NONE,
            'Do not include Magento 2 core commands'
        );
        $inputDefinition->addOption($skipMagento2CoreCommands);

        /**
         * Skip Magento compatibility check
         */
        $skipMagentoCompatibilityCheck = new InputOption(
            '--skip-magento-compatibility-check',
            '',
            InputOption::VALUE_NONE,
            'Do not check for Magento version compatibility'
        );
        $inputDefinition->addOption($skipMagentoCompatibilityCheck);

        return $inputDefinition;
    }

    /**
     * Force to load some classes before the Magento core loads the classes
     * in a different version
     *
     * @return void
     */
    private function preloadClassesBeforeMagentoCore(): void
    {
        if ($this->autoloader instanceof ClassLoader) {
            $this->autoloader->loadClass('Symfony\Component\Console\Question\Question');
        }
    }
}
