<?php

namespace N98\Magento\Command;

use Exception;
use N98\Util\Console\Helper\ParameterHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class AbstractMagentoStoreConfigCommand extends AbstractMagentoCommand
{
    /**
     * @var string
     */
    const SCOPE_STORE_VIEW = 'store';

    /**
     * @var string
     */
    const SCOPE_WEBSITE = 'website';

    /**
     * @var string
     */
    const SCOPE_GLOBAL = 'global';

    /**
     * Store view or global by additional option
     */
    const SCOPE_STORE_VIEW_GLOBAL = 'store_view_global';

    /**
     * @var string
     */
    protected $commandName = '';

    /**
     * @var string
     */
    protected $commandDescription = '';

    /**
     * @var string
     */
    protected $configPath = '';

    /**
     * @var string
     */
    protected $toggleComment = '';

    /**
     * @var string
     */
    protected $falseName = 'disabled';

    /**
     * @var string
     */
    protected $trueName = 'enabled';

    /**
     * Add admin store to interactive prompt
     *
     * @var bool
     */
    protected $withAdminStore = false;

    /**
     * @var string
     */
    protected $scope = self::SCOPE_STORE_VIEW;

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->addOption('on', null, InputOption::VALUE_NONE, 'Switch on')
            ->addOption('off', null, InputOption::VALUE_NONE, 'Switch off')
            ->setDescription($this->commandDescription)
        ;

        if ($this->scope == self::SCOPE_STORE_VIEW_GLOBAL) {
            $this->addOption('global', null, InputOption::VALUE_NONE, 'Set value on default scope');
        }

        if ($this->scope == self::SCOPE_STORE_VIEW || $this->scope == self::SCOPE_STORE_VIEW_GLOBAL) {
            $this->addArgument('store', InputArgument::OPTIONAL, 'Store code or ID');
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento()) {
            $runOnStoreView = false;
            if ($this->scope == self::SCOPE_STORE_VIEW
                || ($this->scope == self::SCOPE_STORE_VIEW_GLOBAL && !$input->getOption('global'))
            ) {
                $runOnStoreView = true;
            }

            if ($runOnStoreView) {
                $store = $this->_initStore($input, $output);
            } else {
                $storeManager = $this->getObjectManager()->get('Magento\Store\Model\StoreManagerInterface');
                /* @var $storeManager \Magento\Store\Model\StoreManagerInterface */
                $store = $storeManager->getStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
            }
        }

        if ($input->getOption('on')) {
            $isFalse = true;
        } elseif ($input->getOption('off')) {
            $isFalse = false;
        } else {
            $scopeConfig = $this->getObjectManager()->get('\Magento\Framework\App\Config\ScopeConfigInterface');
            /* @var $scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface */
            $isFalse = !$scopeConfig->isSetFlag(
                $this->configPath,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store->getCode()
            );
        }

        $this->_beforeSave($store, $isFalse);


        if ($store->getId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            $scope = 'default'; // @TODO Constant was removed in Magento2 ?
        } else {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        }

        $configSetCommands = [
            'command'    => 'config:set',
            'path'       => $this->configPath,
            'value'      => $isFalse ? 1 : 0,
            '--scope'    => $scope,
            '--scope-id' => $store->getId(),
        ];

        $input = new ArrayInput($configSetCommands);
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run($input, new NullOutput());

        $comment = '<comment>' . $this->toggleComment . '</comment> '
                    . '<info>' . (!$isFalse ? $this->falseName : $this->trueName) . '</info>'
                    . ($runOnStoreView ? ' <comment>for store</comment> <info>' . $store->getCode() . '</info>' : '');
        $output->writeln($comment);

        $this->_afterSave($store, $isFalse);

        $input = new StringInput('cache:flush');
        $this->getApplication()->run($input, new NullOutput());
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     * @throws Exception
     */
    protected function _initStore(InputInterface $input, OutputInterface $output)
    {
        /** @var $parameter ParameterHelper */
        $parameter = $this->getHelper('parameter');

        return $parameter->askStore($input, $output, 'store', $this->withAdminStore);
    }

    /**
     * @param \Magento\Store\Model\Store $store
     * @param bool $disabled
     */
    protected function _beforeSave(\Magento\Store\Model\Store $store, $disabled)
    {
    }

    /**
     * @param \Magento\Store\Model\Store $store
     * @param bool $disabled
     */
    protected function _afterSave(\Magento\Store\Model\Store $store, $disabled)
    {
    }
}
