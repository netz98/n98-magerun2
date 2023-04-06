<?php

namespace N98\Magento\Command;

use Magento\Deploy\Model\Mode;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use N98\Magento\Command\SubCommand\ConfigBag;
use N98\Magento\Command\SubCommand\SubCommandFactory;
use N98\Util\Console\Helper\InjectionHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
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
    protected $_deprecatedAlias = [];

    /**
     * @var array
     */
    protected $_websiteCodeMap = [];

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

        $output->writeln([
            '',
            $formatter->formatBlock($text, $style, true),
            '',
        ]);
    }

    /**
     * Bootstrap magento shop
     *
     * @return bool
     * @throws \Exception
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
     * @throws \Exception
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

        if ($this->_magentoRootFolder !== null) {
            return true;
        }

        throw new RuntimeException('Magento folder could not be detected');
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isSourceTypeRepository($type)
    {
        return in_array($type, ['git', 'hg']);
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
        return in_array(strtolower($value), ['y', 'yes', 1, 'true']);
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
        if (in_array($value, [1, 'true'])) {
            return 'active';
        }

        return 'inactive';
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Exception
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->getHelperSet()->setCommand($this);

        $this->injectObjects($output);

        return parent::run($input, $output);
    }

    /**
     * @param OutputInterface $output
     * @throws \ReflectionException
     * @throws \Exception
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
            $commandConfig = [];
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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    public function runsInProductionMode(
        InputInterface $input,
        OutputInterface $output
    ) {
        $mode = $this->getObjectManager()->create(
            Mode::class,
            [
                'input'  => $input,
                'output' => $output,
            ]
        );

        return $mode->getMode() === State::MODE_PRODUCTION;
    }
}
