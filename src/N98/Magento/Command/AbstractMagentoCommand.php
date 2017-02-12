<?php

namespace N98\Magento\Command;

use Composer\Factory as ComposerFactory;
use Composer\IO\ConsoleIO;
use Composer\Package\Loader\ArrayLoader as PackageLoader;
use Composer\Package\PackageInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use N98\Magento\Command\SubCommand\ConfigBag;
use N98\Magento\Command\SubCommand\SubCommandFactory;
use N98\Util\Console\Helper\InjectionHelper;
use N98\Util\Console\Helper\MagentoHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractMagentoCommand
 *
 * @package N98\Magento\Command
 *
 * @method \N98\Magento\Application getApplication() getApplication()
 */
abstract class AbstractMagentoCommand extends Command
{
    /**
     * @var int
     */
    const MAGENTO_MAJOR_VERSION_2 = 2;

    /**
     * @var string
     */
    const CONFIG_KEY_COMMANDS = 'commands';

    /**
     * @var string
     */
    protected $_magentoRootFolder = null;

    /**
     * @var int
     */
    protected $_magentoMajorVersion = self::MAGENTO_MAJOR_VERSION_2;

    /**
     * @var bool
     */
    protected $_magentoEnterprise = false;

    /**
     * @var array
     */
    protected $_deprecatedAlias = array();

    /**
     * @var array
     */
    protected $_websiteCodeMap = array();

    /**
     * @var ObjectManager
     */
    protected $_objectManager = null;

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->checkDeprecatedAliases($input, $output);
    }

    /**
     * @return ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->getApplication()->getObjectManager();
    }

    /**
     * @param string|null $commandClass
     * @return array
     */
    protected function getCommandConfig($commandClass = null)
    {
        if ($commandClass === null) {
            $commandClass = get_class($this);
        }
        $configArray = $this->getApplication()->getConfig();
        if (isset($configArray[self::CONFIG_KEY_COMMANDS][$commandClass])) {
            return $configArray[self::CONFIG_KEY_COMMANDS][$commandClass];
        }

        return [];
    }

    /**
     * @param OutputInterface $output
     * @param string $text
     * @param string $style
     */
    protected function writeSection(OutputInterface $output, $text, $style = 'bg=blue;fg=white')
    {
        /** @var $formatter FormatterHelper */
        $formatter = $this->getHelper('formatter');

        $output->writeln(array(
            '',
            $formatter->formatBlock($text, $style, true),
            '',
        ));
    }

    /**
     * Bootstrap magento shop
     *
     * @return bool
     */
    protected function initMagento()
    {
        $init = $this->getApplication()->initMagento();
        if ($init) {
            $this->_magentoRootFolder = $this->getApplication()->getMagentoRootFolder();
        }

        return $init;
    }

    /**
     * Search for magento root folder
     *
     * @param OutputInterface $output
     * @param bool $silent print debug messages
     * @throws \RuntimeException
     */
    public function detectMagento(OutputInterface $output, $silent = true)
    {
        $this->getApplication()->detectMagento();

        $this->_magentoEnterprise = $this->getApplication()->isMagentoEnterprise();
        $this->_magentoRootFolder = $this->getApplication()->getMagentoRootFolder();
        $this->_magentoMajorVersion = $this->getApplication()->getMagentoMajorVersion();

        if (!$silent) {
            $editionString = ($this->_magentoEnterprise ? ' (Enterprise Edition) ' : '');
            $output->writeln(
                '<info>Found Magento ' . $editionString . 'in folder "' . $this->_magentoRootFolder . '"</info>'
            );
        }

        if (!empty($this->_magentoRootFolder)) {
            return;
        }

        throw new \RuntimeException('Magento folder could not be detected');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return \Composer\Downloader\DownloadManager
     */
    public function getComposerDownloadManager($input, $output)
    {
        return $this->getComposer($input, $output)->getDownloadManager();
    }

    /**
     * @param array $config
     * @return PackageInterface
     */
    public function createComposerPackageByConfig(array $config)
    {
        $packageLoader = new PackageLoader();

        return $packageLoader->load($config);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array|PackageInterface $config
     * @param string $targetFolder
     * @param bool $preferSource
     * @return \Composer\Package\PackageInterface
     */
    public function downloadByComposerConfig(
        InputInterface $input,
        OutputInterface $output,
        $config,
        $targetFolder,
        $preferSource = true
    ) {
        $dm = $this->getComposerDownloadManager($input, $output);
        if (!$config instanceof PackageInterface) {
            $package = $this->createComposerPackageByConfig($config);
        } else {
            $package = $config;
        }

        $helper = new MagentoHelper();
        $helper->detect($targetFolder);
        if ($this->isSourceTypeRepository($package->getSourceType()) && $helper->getRootFolder() == $targetFolder) {
            $package->setInstallationSource('source');
            $this->checkRepository($package, $targetFolder);
            $dm->update($package, $package, $targetFolder);
        } else {
            $dm->download($package, $targetFolder, $preferSource);
        }

        return $package;
    }

    /**
     * brings locally cached repository up to date if it is missing the requested tag
     *
     * @param PackageInterface $package
     * @param string $targetFolder
     */
    protected function checkRepository(PackageInterface $package, $targetFolder)
    {
        if ($package->getSourceType() == 'git') {
            $command = sprintf(
                'cd %s && git rev-parse refs/tags/%s',
                escapeshellarg($targetFolder),
                escapeshellarg($package->getSourceReference())
            );
            $existingTags = shell_exec($command);
            if (!$existingTags) {
                $command = sprintf('cd %s && git fetch', escapeshellarg($targetFolder));
                shell_exec($command);
            }
        } elseif ($package->getSourceType() == 'hg') {
            $command = sprintf(
                'cd %s && hg log --template "{tags}" -r %s',
                escapeshellarg($targetFolder),
                escapeshellarg($package->getSourceReference())
            );
            $existingTag = shell_exec($command);
            if ($existingTag === $package->getSourceReference()) {
                $command = sprintf('cd %s && hg pull', escapeshellarg($targetFolder));
                shell_exec($command);
            }
        }
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isSourceTypeRepository($type)
    {
        return in_array($type, array('git', 'hg'));
    }

    /**
     * obtain composer
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return \Composer\Composer
     */
    public function getComposer(InputInterface $input, OutputInterface $output)
    {
        $io = new ConsoleIO($input, $output, $this->getHelperSet());
        $config = array(
            'config' => array(
                'secure-http' => false,
            ),
        );

        return ComposerFactory::create($io, $config);
    }

    /**
     * @param string $alias
     * @param string $message
     * @return AbstractMagentoCommand
     */
    protected function addDeprecatedAlias($alias, $message)
    {
        $this->_deprecatedAlias[$alias] = $message;

        return $this;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function checkDeprecatedAliases(InputInterface $input, OutputInterface $output)
    {
        if (isset($this->_deprecatedAlias[$input->getArgument('command')])) {
            $output->writeln(
                '<error>Deprecated:</error> <comment>' . $this->_deprecatedAlias[$input->getArgument('command')] .
                '</comment>'
            );
        }
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function _parseBoolOption($value)
    {
        return in_array(strtolower($value), array('y', 'yes', 1, 'true'));
    }

    /**
     * @param string $value
     * @return bool
     */
    public function parseBoolOption($value)
    {
        return $this->_parseBoolOption($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function formatActive($value)
    {
        if (in_array($value, array(1, 'true'))) {
            return 'active';
        }

        return 'inactive';
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->getHelperSet()->setCommand($this);

        $this->injectObjects($output);

        return parent::run($input, $output);
    }

    /**
     * @param OutputInterface $output
     */
    public function injectObjects(OutputInterface $output)
    {
        /* @var $injectionHelper InjectionHelper */
        if (method_exists($this, 'inject')) {
            $this->detectMagento($output);
            $this->initMagento();

            /* @var $injectionHelper InjectionHelper */
            $injectionHelper = $this->getHelper('injection');
            $injectionHelper->methodInjection(
                $this,
                'inject',
                $this->getObjectManager()
            );
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $baseNamespace If this is set we can use relative class names.
     *
     * @return SubCommandFactory
     */
    protected function createSubCommandFactory(
        InputInterface $input,
        OutputInterface $output,
        $baseNamespace = ''
    ) {
        $configBag = new ConfigBag();

        $commandConfig = $this->getCommandConfig();
        if (empty($commandConfig)) {
            $commandConfig = array();
        }

        return new SubCommandFactory(
            $this,
            $baseNamespace,
            $input,
            $output,
            $commandConfig,
            $configBag
        );
    }
}
