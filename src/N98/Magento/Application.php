<?php

namespace N98\Magento;

use BadMethodCallException;
use Composer\Autoload\ClassLoader;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\ObjectManagerInterface;
use N98\Magento\Application\AutoloaderDecorator;
use N98\Magento\Application\Config;
use N98\Magento\Application\ConfigurationLoader;
use N98\Magento\Application\Console\Events;
use N98\Magento\Application\DetectionResult;
use N98\Util\Console\Helper\MagentoHelper;
use N98\Util\Console\Helper\TwigHelper;
use N98\Util\OperatingSystem;
use RuntimeException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use UnexpectedValueException;

class Application extends BaseApplication
{
    /**
     * @var string
     */
    const APP_NAME = 'n98-magerun2';

    /**
     * @var string
     */
    const APP_VERSION = '2.3.1';

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
     * If root dir is set by root-dir option this flag is true
     *
     * @var bool
     */
    protected $_directRootDir = false;

    /**
     * @var DetectionResult of the Magento application (e.g. v1/v2, Enterprise/Community, root-path)
     */
    private $detectionResult;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;

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
        parent::__construct(self::APP_NAME, self::APP_VERSION);
    }

    /**
     * @return InputDefinition
     */
    protected function getDefaultInputDefinition()
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
            InputOption::VALUE_OPTIONAL,
            'Do not include Magento 2 core commands'
        );
        $inputDefinition->addOption($skipMagento2CoreCommands);

        return $inputDefinition;
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
     * Search for magento root folder
     *
     * @param InputInterface $input [optional]
     * @param OutputInterface $output [optional]
     * @return void
     */
    public function detectMagento(InputInterface $input = null, OutputInterface $output = null)
    {
        if ($this->detectionResult) {
            return;
        }

        $this->detectionResult = $result = $this->getDetectionResult($input, $output);

        if ($result->isDetected()) {
            $magentoMajorVersion = $result->getMajorVersion();
            if ($magentoMajorVersion !== $this->magerunMajorVersion) {
                $this->_initMagento1();
            }
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
     * Try to bootstrap magento 2 and load cli application
     *
     * @param OutputInterface $output
     */
    protected function registerMagentoCoreCommands(OutputInterface $output)
    {
        $magentoRootFolder = $this->getMagentoRootFolder();
        if (0 === strlen($magentoRootFolder)) {
            return;
        }

        // Magento was found -> register core cli commands
        try {
            $this->requireOnce($magentoRootFolder . '/app/bootstrap.php');
        } catch (Exception $ex) {
            $this->renderException($ex, $output);
            $output->writeln(
                "<info>Use --skip-core-commands to not require the Magento app/bootstrap.php which caused " .
                "the exception.</info>"
            );

            return;
        }

        $coreCliApplication = new \Magento\Framework\Console\Cli();
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
     * @param bool $mode
     */
    public function setPharMode($mode)
    {
        $this->_isPharMode = $mode;
    }

    /**
     * @return bool
     */
    public function isPharMode()
    {
        return $this->_isPharMode;
    }

    /**
     * @TODO Move logic into "EventSubscriber"
     *
     * @param OutputInterface $output
     * @return null|false
     */
    public function checkVarDir(OutputInterface $output)
    {
        $tempVarDir = sys_get_temp_dir() . '/magento/var';
        if (!OutputInterface::VERBOSITY_NORMAL <= $output->getVerbosity() && !is_dir($tempVarDir)) {
            return;
        }

        $this->detectMagento(null, $output);
        /** @var DirectoryList $directoryList */
        $directoryList = $this->_objectManager->get(DirectoryList::class);
        $configDir = rtrim($directoryList->getPath(DirectoryList::CONFIG), DIRECTORY_SEPARATOR);
        /* If magento is not installed yet, don't check */
        if ($this->detectionResult->getRootFolder() === null || !file_exists($configDir . '/env.php')) {
            return;
        }

        try {
            $this->initMagento();
        } catch (Exception $e) {
            $message = 'Cannot initialize Magento. Please check your configuration. '
            . 'Some n98-magerun command will not work. Got message: '
            . $e->getMessage();
            if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
                $message .= "\n" . $e->getTraceAsString();
            }
            $output->writeln($message);

            return;
        }

        $currentVarDir = $directoryList->getPath(DirectoryList::VAR_DIR);

        if ($currentVarDir === $tempVarDir) {
            $output->writeln([
                sprintf('<warning>Fallback folder %s is used in n98-magerun</warning>', $tempVarDir),
                '',
                'n98-magerun2 is using the fallback folder. If there is another folder configured for Magento, this ' .
                'can cause serious problems.',
                'Please refer to https://github.com/netz98/n98-magerun/wiki/File-system-permissions ' .
                'for more information.',
                '',
            ]);
        } else {
            $output->writeln([
                sprintf('<warning>Folder %s found, but not used in n98-magerun</warning>', $tempVarDir),
                '',
                "This might cause serious problems. n98-magerun2 is using the configured var-folder " .
                "<comment>$currentVarDir</comment>",
                'Please refer to https://github.com/netz98/n98-magerun/wiki/File-system-permissions ' .
                'for more information.',
                '',
            ]);

            return false;
        }
    }

    /**
     * Loads and initializes the Magento application
     *
     * @param bool $soft
     *
     * @return bool false if magento root folder is not set, true otherwise
     */
    public function initMagento($soft = false)
    {
        if ($this->getMagentoRootFolder(true) === null) {
            return false;
        }

        $isMagento2 = $this->detectionResult->getMajorVersion() === self::MAGENTO_MAJOR_VERSION_2;
        if ($isMagento2) {
            $this->_initMagento2();
        } else {
            $this->_initMagento1($soft);
        }

        return true;
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
        return parent::getLongVersion() . ' by <info>netz98 GmbH</info>';
    }

    /**
     * @return boolean
     */
    public function isMagentoEnterprise()
    {
        return $this->detectionResult->isEnterpriseEdition();
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
     * @deprecated 1.3.0
     * @return boolean
     */
    public function isMagerunStopFileFound()
    {
        trigger_error('Deprecated method "isMagerunStopFileFound" called.', E_USER_DEPRECATED);

        return $this->detectionResult->isMagerunStopFileFound();
    }

    /**
     * Runs the current application with possible command aliases
     *
     * @param InputInterface $input An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $event = new Application\Console\Event($this, $input, $output);
        $this->dispatcher->dispatch(Events::RUN_BEFORE, $event);

        /**
         * only for compatibility to old versions.
         */
        $event = new ConsoleEvent(new Command('dummy'), $input, $output);
        $this->dispatcher->dispatch('console.run.before', $event);

        $input = $this->config->checkConfigCommandAlias($input);
        if ($output instanceof ConsoleOutput) {
            $this->checkVarDir($output->getErrorOutput());
        }

        return parent::doRun($input, $output);
    }

    /**
     * @param InputInterface $input [optional]
     * @param OutputInterface $output [optional]
     *
     * @return int
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
            $this->renderException($e, $output->getErrorOutput());
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
     * @param array $initConfig [optional]
     * @param InputInterface $input [optional]
     * @param OutputInterface $output [optional]
     *
     * @return void
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

        $this->config = $config = new Config($initConfig, $this->isPharMode(), $output);
        if ($this->configurationLoaderInjected) {
            $config->setLoader($this->configurationLoaderInjected);
        }
        $config->loadPartialConfig($loadExternalConfig);
        $this->detectMagento($input, $output);

        $configLoader = $config->getLoader();
        $configLoader->loadStageTwo(
            $this->getMagentoRootFolder(true),
            $loadExternalConfig,
            $this->detectionResult->getMagerunStopFileFolder()
        );
        $config->load();

        if ($autoloader = $this->autoloader) {
            /**
             * Include commands shipped by Magento 2 core
             */
            if (!$this->_checkSkipMagento2CoreCommandsOption($input)) {
                $this->registerMagentoCoreCommands($output);
            }
            $config->registerCustomAutoloaders($autoloader);
            $this->registerEventSubscribers();
            $config->registerCustomCommands($this);
        }

        $this->registerHelpers();

        $this->_isInitialized = true;
    }

    /**
     * @param array $initConfig [optional]
     * @param InputInterface $input [optional]
     * @param OutputInterface $output [optional]
     */
    public function reinit($initConfig = [], InputInterface $input = null, OutputInterface $output = null)
    {
        $this->_isInitialized = false;
        $this->detectionResult = null;
        $this->config = null;
        $this->init($initConfig, $input, $output);
    }

    /**
     * @return void
     */
    protected function registerEventSubscribers()
    {
        $config = $this->config->getConfig();
        $subscriberClasses = $config['event']['subscriber'];
        foreach ($subscriberClasses as $subscriberClass) {
            $subscriber = new $subscriberClass();
            $this->dispatcher->addSubscriber($subscriber);
        }
    }

    /**
     * @param InputInterface $input
     * @return bool
     */
    protected function _checkSkipConfigOption(InputInterface $input)
    {
        return $input->hasParameterOption('--skip-config');
    }

    /**
     * @return bool
     */
    protected function _checkSkipMagento2CoreCommandsOption(InputInterface $input)
    {
        return $input->hasParameterOption('--skip-core-commands') || getenv('MAGERUN_SKIP_CORE_COMMANDS');
    }

    /**
     * @param InputInterface $input
     * @return void
     */
    protected function _checkRootDirOption(InputInterface $input)
    {
        $rootDir = $input->getParameterOption('--root-dir');
        if (is_string($rootDir)) {
            $this->setRootDir($rootDir);
        }
    }

    /**
     * Set root dir (chdir()) of magento directory
     *
     * @param string $path to Magento directory
     */
    private function setRootDir($path)
    {
        if (isset($path[0]) && '~' === $path[0]) {
            $path = OperatingSystem::getHomeDir() . substr($path, 1);
        }

        $folder = realpath($path);
        $this->_directRootDir = true;
        if (is_dir($folder)) {
            chdir($folder);
        }
    }

    /**
     * use require-once inside a function with it's own variable scope w/o any other variables
     * and $this unbound.
     *
     * @param string $path
     */
    private function requireOnce($path)
    {
        $requireOnce = function () {
            require_once func_get_arg(0);
        };
        if (50400 <= PHP_VERSION_ID) {
            $requireOnce->bindTo(null);
        }

        $requireOnce($path);
    }

    /**
     * @param bool $soft
     *
     * @return void
     */
    protected function _initMagento1($soft = false)
    {
        $this->outputMagerunCompatibilityNotice('1');
    }

    /**
     * @return void
     */
    protected function _initMagento2()
    {
        $this->requireOnce($this->getMagentoRootFolder() . '/app/bootstrap.php');

        $magentoAutoloader = AutoloaderRegistry::getAutoloader();

        // Prevent an infinite loop of autoloaders
        if (!$magentoAutoloader instanceof AutoloaderDecorator) {
            AutoloaderRegistry::registerAutoloader(
                new AutoloaderDecorator(
                    $magentoAutoloader,
                    $this->getAutoloader()
                )
            );
        }

        $params = $_SERVER;
        $params[\Magento\Store\Model\StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[\Magento\Store\Model\Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $params['entryPoint'] = basename(__FILE__);

        $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
        /** @var \Magento\Framework\App\Cron $app */
        $app = $bootstrap->createApplication('N98\Magento\Framework\App\Magerun', []);
        /* @var $app \N98\Magento\Framework\App\Magerun */
        $app->launch();

        $this->_objectManager = $app->getObjectManager();
    }

    /**
     * Show a hint that this is Magento incompatible with Magerun and how to obtain the correct Magerun for it
     *
     * @param string $version of Magento, "1" or "2", that is incompatible
     */
    private function outputMagerunCompatibilityNotice($version)
    {
        $file = $version === '2' ? $version : '';
        $magentoHint = <<<MAGENTOHINT
You are running a Magento $version.x instance. This version of n98-magerun is not compatible
with Magento $version.x. Please use n98-magerun$file (version $version) for this shop.

A current version of the software can be downloaded from the website:

<info>Download with curl
------------------</info>

    <comment>curl -O https://files.magerun.net/n98-magerun$file.phar</comment>

<info>Download with wget
------------------</info>

    <comment>wget https://files.magerun.net/n98-magerun$file.phar</comment>

MAGENTOHINT;

        $output = new ConsoleOutput();

        /** @var $formatter FormatterHelper */
        $formatter = $this->getHelperSet()->get('formatter');

        $output->writeln([
            '',
            $formatter->formatBlock('Compatibility Notice', 'bg=blue;fg=white', true),
            '',
            $magentoHint,
        ]);

        throw new RuntimeException('This version of n98-magerun is not compatible with Magento ' . $version);
    }

    /**
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
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
     * @param OutputInterface $output
     */
    protected function _addOutputStyles(OutputInterface $output)
    {
        $output->getFormatter()->setStyle('debug', new OutputFormatterStyle('magenta', 'white'));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('red', 'yellow', ['bold']));
    }

    /**
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }

    /**
     * @param InputInterface $input [optional]
     * @param OutputInterface $output [optional]
     * @return DetectionResult
     */
    private function getDetectionResult(InputInterface $input = null, OutputInterface $output = null)
    {
        $input = $input ?: new ArgvInput();
        $output = $output ?: new ConsoleOutput();

        $folder = $this->getMagentoRootFolder(true);
        if ($folder === null) {
            $this->_checkRootDirOption($input);
            $folder = OperatingSystem::getCwd();
        }
        if ($this->_directRootDir) {
            $subFolders = [$folder];
        } else {
            $subFolders = $this->config->getDetectSubFolders();
        }

        $this->getHelperSet()->set(new MagentoHelper($input, $output), 'magento');
        /* @var $magentoHelper MagentoHelper */
        $magentoHelper = $this->getHelperSet()->get('magento');

        $detection = new Application\DetectionResult($magentoHelper, $folder, $subFolders);

        return $detection;
    }
}
