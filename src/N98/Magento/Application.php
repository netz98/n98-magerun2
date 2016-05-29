<?php

namespace N98\Magento;

use Composer\Autoload\ClassLoader;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Mtf\EntryPoint\EntryPoint;
use N98\Magento\Application\Config;
use N98\Magento\Application\ConfigurationLoader;
use N98\Magento\Application\Console\Events;
use N98\Util\Console\Helper\MagentoHelper;
use N98\Util\Console\Helper\TwigHelper;
use N98\Util\OperatingSystem;
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
    const APP_VERSION = '1.1.14';

    /**
     * @var int
     */
    const MAGENTO_MAJOR_VERSION_1 = 1;

    /**
     * @var int
     */
    const MAGENTO_MAJOR_VERSION_2 = 2;

    /**
     * @var string
     */
    private static $logo = "
          ____  ____                                                   ___ 
   ____  / __ \\( __ )      ____ ___  ____ _____ ____  _______  ______ |__ \\
  / __ \\/ /_/ / __  |_____/ __ `__ \\/ __ `/ __ `/ _ \\/ ___/ / / / __ \\__/ /
 / / / /\\__, / /_/ /_____/ / / / / / /_/ / /_/ /  __/ /  / /_/ / / / / __/
/_/ /_//____/\\____/     /_/ /_/ /_/\\__,_/\\__, /\\___/_/   \\__,_/_/ /_/____/
                                        /____/                             
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
     * @var string
     */
    protected $_magentoRootFolder = null;

    /**
     * @var bool
     */
    protected $_magentoEnterprise = false;

    /**
     * @var int
     */
    protected $_magentoMajorVersion = self::MAGENTO_MAJOR_VERSION_2;

    /**
     * @var EntryPoint
     */
    protected $_magento2EntryPoint = null;

    /**
     * @var bool
     */
    protected $_isPharMode = false;

    /**
     * @var bool
     */
    protected $_magerunStopFileFound = false;

    /**
     * @var string
     */
    protected $_magerunStopFileFolder = null;

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
     * @var bool
     */
    protected $_magentoDetected = false;

    /**
     * @var ObjectManager
     */
    protected $_objectManager = null;

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
     * Search for magento root folder
     *
     * @param InputInterface|null $input [optional]
     * @param OutputInterface|null $output [optional]
     * @return void
     */
    public function detectMagento(InputInterface $input = null, OutputInterface $output = null)
    {
        // do not detect magento twice
        if ($this->_magentoDetected) {
            return;
        }

        if ($this->getMagentoRootFolder() === null) {
            $this->_checkRootDirOption();
            $folder = OperatingSystem::getCwd();
        } else {
            $folder = $this->getMagentoRootFolder();
        }

        $this->getHelperSet()->set(new MagentoHelper($input, $output), 'magento');
        $magentoHelper = $this->getHelperSet()->get('magento');
        /* @var $magentoHelper MagentoHelper */
        if (!$this->_directRootDir) {
            $subFolders = $this->config->getDetectSubFolders();
        } else {
            $subFolders = array();
        }

        $this->_magentoDetected = $magentoHelper->detect($folder, $subFolders);
        $this->_magentoRootFolder = $magentoHelper->getRootFolder();
        $this->_magentoEnterprise = $magentoHelper->isEnterpriseEdition();
        $this->_magentoMajorVersion = $magentoHelper->getMajorVersion();
        $this->_magerunStopFileFound = $magentoHelper->isMagerunStopFileFound();
        $this->_magerunStopFileFolder = $magentoHelper->getMagerunStopFileFolder();
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
        $twigBaseDirs = array(
            __DIR__ . '/../../../res/twig'
        );
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
        if ($this->getMagentoRootFolder()) {
            // Magento was found -> register core cli commands
            require_once $this->getMagentoRootFolder() . '/app/bootstrap.php';

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
    }

    /**
     * Override standard command registration. We want alias support.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     * @return \Symfony\Component\Console\Command\Command
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
        /* If magento is not installed yet, don't check */
        if ($this->_magentoRootFolder === null
            || !file_exists($this->_magentoRootFolder . '/app/etc/local.xml')
        ) {
            return;
        }

        try {
            $this->initMagento();
        } catch (\Exception $e) {
            $message = 'Cannot initialize Magento. Please check your configuration. '
                . 'Some n98-magerun command will not work. Got message: ';
            if (OutputInterface::VERBOSITY_VERY_VERBOSE <= $output->getVerbosity()) {
                $message .= $e->getTraceAsString();
            } else {
                $message .= $e->getMessage();
            }
            $output->writeln($message);

            return;
        }

        $configOptions = new \Mage_Core_Model_Config_Options();
        $currentVarDir = $configOptions->getVarDir();

        if ($currentVarDir == $tempVarDir) {
            $output->writeln(
                sprintf('<warning>Fallback folder %s is used in n98-magerun</warning>', $tempVarDir)
            );
            $output->writeln('');
            $output->writeln(
                'n98-magerun is using the fallback folder. If there is another folder configured for Magento, '
                . 'this can cause serious problems.'
            );
            $output->writeln(
                'Please refer to https://github.com/netz98/n98-magerun/wiki/File-system-permissions '
                . 'for more information.'
            );
            $output->writeln('');
        } else {
            $output->writeln(sprintf(
                '<warning>Folder %s found, but not used in n98-magerun</warning>',
                $tempVarDir
            ));
            $output->writeln('');
            $output->writeln(
                sprintf(
                    'This might cause serious problems. n98-magerun is using the configured var-folder '
                    . '<comment>%s</comment>',
                    $currentVarDir
                )
            );
            $output->writeln(
                'Please refer to https://github.com/netz98/n98-magerun/wiki/File-system-permissions '
                . 'for more information.'
            );
            $output->writeln('');

            return false;
        }
    }

    /**
     * Loads and initializes the Magento application
     *
     * @return bool false if magento root folder is not set, true otherwise
     */
    public function initMagento()
    {
        if ($this->getMagentoRootFolder() !== null) {
            if ($this->_magentoMajorVersion == self::MAGENTO_MAJOR_VERSION_2) {
                $this->_initMagento2();
            } else {
                $this->_initMagento1();
            }

            return true;
        }

        return false;
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
        return parent::getLongVersion() . ' by <info>netz98 new media GmbH</info>';
    }

    /**
     * @return boolean
     */
    public function isMagentoEnterprise()
    {
        return $this->_magentoEnterprise;
    }

    /**
     * @return string
     */
    public function getMagentoRootFolder()
    {
        return $this->_magentoRootFolder;
    }

    /**
     * @param string $magentoRootFolder
     */
    public function setMagentoRootFolder($magentoRootFolder)
    {
        $this->_magentoRootFolder = $magentoRootFolder;
    }

    /**
     * @return int
     */
    public function getMagentoMajorVersion()
    {
        return $this->_magentoMajorVersion;
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
     * @return boolean
     */
    public function isMagerunStopFileFound()
    {
        return $this->_magerunStopFileFound;
    }

    /**
     * Runs the current application with possible command aliases
     *
     * @param InputInterface $input An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return integer 0 if everything went fine, or an error code
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
            $this->init(array(), $input, $output);
        } catch (\Exception $e) {
            $output = new ConsoleOutput();
            $this->renderException($e, $output);
        }

        $return = parent::run($input, $output);

        // Fix for no return values -> used in interactive shell to prevent error output
        if ($return === null) {
            return 0;
        }

        return $return;
    }

    /**
     * @param array $initConfig
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function init(array $initConfig = array(), InputInterface $input = null, OutputInterface $output = null)
    {
        if ($this->_isInitialized) {
            return;
        }

        // Suppress DateTime warnings
        date_default_timezone_set(@date_default_timezone_get());

        // Initialize EventDispatcher early
        $this->dispatcher = new EventDispatcher();
        $this->setDispatcher($this->dispatcher);

        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        if (null !== $this->config) {
            throw new UnexpectedValueException(sprintf('Config already initialized'));
        }

        $loadExternalConfig = !$this->_checkSkipConfigOption();

        $this->config = $config = new Config($initConfig, $this->isPharMode(), $output);
        $configLoader = $config->getLoader();
        $config->loadPartialConfig($loadExternalConfig);
        $this->detectMagento($input, $output);
        $configLoader->loadStageTwo($this->_magentoRootFolder, $loadExternalConfig, $this->_magerunStopFileFolder);
        $config->load();

        if ($autoloader = $this->autoloader) {

            /**
             * Include commands shipped by Magento 2 core
             */
            if (!$this->_checkSkipMagento2CoreCommandsOption()) {
                $this->registerMagentoCoreCommands($output);
            }

            $this->config->registerCustomAutoloaders($autoloader);
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
    public function reinit($initConfig = array(), InputInterface $input = null, OutputInterface $output = null)
    {
        $this->_isInitialized = false;
        $this->config = null;
        $this->init($initConfig, $input, $output);
    }

    /**
     * @return void
     */
    protected function registerEventSubscribers()
    {
        foreach ($this->config->getConfig()['event']['subscriber'] as $subscriberClass) {
            $subscriber = new $subscriberClass();
            $this->dispatcher->addSubscriber($subscriber);
        }
    }

    /**
     * @return bool
     */
    protected function _checkSkipConfigOption()
    {
        $skipConfigOption = getopt('', array('skip-config'));

        return count($skipConfigOption) > 0;
    }

    /**
     * @return bool
     */
    protected function _checkSkipMagento2CoreCommandsOption()
    {
        $skipConfigOption = getopt('', array('skip-core-commands'));

        getenv('MAGERUN_SKIP_CORE_COMMANDS') && $skipConfigOption[] = 1;

        return count($skipConfigOption) > 0;
    }

    /**
     * @return string
     */
    protected function _checkRootDirOption()
    {
        $specialGlobalOptions = getopt('', array('root-dir:'));

        if (count($specialGlobalOptions) > 0) {
            if (isset($specialGlobalOptions['root-dir'][0])
                && $specialGlobalOptions['root-dir'][0] == '~'
            ) {
                $specialGlobalOptions['root-dir'] = OperatingSystem::getHomeDir() .
                    substr($specialGlobalOptions['root-dir'], 1);
            }
            $folder = realpath($specialGlobalOptions['root-dir']);
            $this->_directRootDir = true;
            if (is_dir($folder)) {
                chdir($folder);

                return;
            }
        }
    }

    /**
     * Show a hint that this is Magento 1 and how to obtain magerun for it
     */
    protected function _initMagento1()
    {
        $magento1Hint = <<<'MAGENTO1HINT'
You are running a Magento 1.x instance. This version of n98-magerun is not compatible
with Magento 1.x. Please use n98-magerun (version 1) for this shop.

A current version of the software can be downloaded on github.

<info>Download with curl
------------------</info>

    <comment>curl -O https://files.magerun.net/n98-magerun.phar</comment>

<info>Download with wget
------------------</info>

    <comment>wget https://files.magerun.net/n98-magerun.phar</comment>

MAGENTO1HINT;

        $output = new ConsoleOutput();

        /** @var $formatter FormatterHelper */
        $formatter = $this->getHelperSet()->get('formatter');

        $output->writeln(array(
            '',
            $formatter->formatBlock('Compatibility Notice', 'bg=blue;fg=white', true),
            '',
            $magento1Hint,
        ));

        throw new \RuntimeException('Incompatible Magento version');
    }

    /**
     * @return void
     */
    protected function _initMagento2()
    {
        require_once $this->getMagentoRootFolder() . '/app/bootstrap.php';

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
        $this->config->setConfigurationLoader($configurationLoader);
    }

    /**
     * @param OutputInterface $output
     */
    protected function _addOutputStyles(OutputInterface $output)
    {
        $output->getFormatter()->setStyle('debug', new OutputFormatterStyle('magenta', 'white'));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('red', 'yellow', array('bold')));
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->_objectManager;
    }
}
