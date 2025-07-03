<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System;

use InvalidArgumentException;
use Magento\Backend\Setup\ConfigOptionsList as BackendConfigOptionsList;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DistributionMetadataInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Module\ModuleListInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use N98\Util\ProjectComposer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InfoCommand
 * @package N98\Magento\Command\System
 */
class InfoCommand extends AbstractMagentoCommand
{
    /**
     * @var array
     */
    protected $infos = [];

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Framework\App\Cache\Type\FrontendPool
     */
    protected $frontendPool;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    protected $userCollectionFactory;

    protected function configure()
    {
        $this
            ->setName('sys:info')
            ->setDescription('Prints infos about the current magento system.')
            ->addArgument(
                'key',
                InputArgument::OPTIONAL,
                'Only output value of named param like "version". Key is case insensitive.'
            )
            ->addOption('sort', '', InputOption::VALUE_NONE, 'Sort by name')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );
    }

    /**
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $frontendPool
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory
     */
    public function inject(
        ProductMetadataInterface $productMetadata,
        CustomerFactory $customerFactory,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        AttributeFactory $attributeFactory,
        FrontendPool $frontendPool,
        DeploymentConfig $deploymentConfig,
        ModuleListInterface $moduleList,
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $userCollectionFactory
    ) {
        $this->productMetadata = $productMetadata;
        $this->customerFactory = $customerFactory;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->attributeFactory = $attributeFactory;
        $this->frontendPool = $frontendPool;
        $this->deploymentConfig = $deploymentConfig;
        $this->moduleList = $moduleList;
        $this->scopeConfig = $scopeConfig;
        $this->userCollectionFactory = $userCollectionFactory;
    }

    public function hasInfo()
    {
        return !empty($this->infos);
    }

    public function getInfo($key = null)
    {
        if ($key === null) {
            return $this->infos;
        }

        return isset($this->infos[$key]) ? $this->infos[$key] : null;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('format') === null && $input->getArgument('key') === null) {
            $this->writeSection($output, 'Magento System Information');
        }

        $this->addVersionInfo();
        $this->addDeploymentInfo();
        $this->addSearchEngineInfo();
        $this->addCacheInfos();
        $this->addVendors();
        $this->addAttributeCount();
        $this->addCustomerCount();
        $this->addCategoryCount();
        $this->addProductCount();
        $this->addAdminUserInfos();
        $this->analyseComposer();

        $table = [];

        if ($input->getOption('sort')) {
            ksort($this->infos);
        }

        foreach ($this->infos as $key => $value) {
            $table[] = [$key, $value];
        }

        if (($settingArgument = $input->getArgument('key')) !== null) {
            $settingArgument = strtolower($settingArgument);
            $this->infos = array_change_key_case($this->infos, CASE_LOWER);
            if (!isset($this->infos[$settingArgument])) {
                throw new InvalidArgumentException('Unknown key: ' . $settingArgument);
            }
            $output->writeln((string)$this->infos[$settingArgument]);
        } else {
            $this->getHelper('table')
                ->setHeaders(['name', 'value'])
                ->renderByFormat($output, $table, $input->getOption('format'));
        }

        return Command::SUCCESS;
    }

    protected function analyseComposer()
    {
        $composerProjectUtil = new ProjectComposer(
            $this->getApplication()->getMagentoRootFolder()
        );

        if (!$composerProjectUtil->isLockFile()) {
            $this->infos['Composer Lock File'] = 'not found';

            return;
        }

        $this->infos['Composer Lock File'] = 'found';

        $installedPackages = $composerProjectUtil->getComposerLockPackages();
        $this->infos['Composer Package Count'] = count($installedPackages);

        $packagesToCheck = [
            'magento/composer-root-update-plugin' => 'Magento Composer Root Update Plugin',
            'magento/composer-dependency-version-audit-plugin' => 'Magento Composer Dependency Version Audit Plugin',
            'magento/magento-coding-standard' => 'Magento Coding Standard Package',
            'magento/magento2-functional-testing-framework' => 'Magento Functional Testing Framework',
            'magento/module-inventory' => 'MSI Packages',
            'magento/module-catalog-sample-data' => 'Sample Data Packages',
            'hyva-themes/magento2-default-theme' => 'Hyva Default Theme',
            'hyva-themes/magento2-theme-module' => 'Hyva Theme Module',
        ];

        foreach ($packagesToCheck as $packageToCheck => $label) {
            $isStandardPackage = isset($installedPackages[$packageToCheck]) ? 'installed' : 'not installed';
            $this->infos[$label] = $isStandardPackage;
        }
    }

    protected function addAdminUserInfos()
    {
        $adminUserCollection = $this->userCollectionFactory->create();
        $this->infos['Admin User Count'] = $adminUserCollection->getSize();
    }

    protected function addSearchEngineInfo()
    {
        $this->infos['Search Engine'] = $this->scopeConfig->getValue('catalog/search/engine');
    }

    /**
     * @todo there is also the product repository API...?!
     */
    protected function addProductCount()
    {
        $this->infos['Product Count'] = $this->productFactory
            ->create()
            ->getCollection()
            ->getSize();
    }

    protected function addCustomerCount()
    {
        $this->infos['Customer Count'] = $this->customerFactory->create()
            ->getCollection()
            ->getSize();
    }

    protected function addCategoryCount()
    {
        $this->infos['Category Count'] = $this->categoryFactory
            ->create()
            ->getCollection()
            ->getSize();
    }

    protected function addAttributeCount()
    {
        $this->infos['Attribute Count'] = $this->attributeFactory
            ->create()
            ->getCollection()
            ->getSize();
    }

    protected function addCacheInfos()
    {
        $cachePool = $this->frontendPool;

        $this->infos['Cache Backend'] = get_class($cachePool->get('config')->getBackend());

        switch (get_class($cachePool->get('config')->getBackend())) {
            case 'Zend_Cache_Backend_File':
            case 'Cm_Cache_Backend_File':
                // @TODO Where are the cache options?
                //$cacheDir = $cachePool->get('config')->getBackend()->getOptions()->getCacheDir();
                //$this->infos['Cache Directory'] = $cacheDir;
                break;

            default:
        }
    }

    protected function addDeploymentInfo()
    {
        $this->infos['Admin URI'] = $this->deploymentConfig->get(BackendConfigOptionsList::CONFIG_PATH_BACKEND_FRONTNAME);
        $this->infos['Application Mode'] = $this->deploymentConfig->get(AppState::PARAM_MODE);
        $this->infos['Session'] = $this->deploymentConfig->get('session/save');
        $this->infos['Crypt Key'] = $this->deploymentConfig->get('crypt/key');
        $this->infos['Install Date'] = $this->deploymentConfig->get('install/date');
    }

    protected function addVersionInfo()
    {
        $this->infos['Name'] = $this->productMetadata->getName();
        $this->infos['Version'] = $this->productMetadata->getVersion();
        $this->infos['Edition'] = $this->productMetadata->getEdition();
        $this->infos['Distribution'] = 'n/a';

        if ($this->productMetadata instanceof DistributionMetadataInterface) {
            $this->infos['Distribution'] = $this->productMetadata->getDistributionName();
            $this->infos['Distribution Version'] = $this->productMetadata->getDistributionVersion();
        }

        $this->infos['Root'] = $this->_magentoRootFolder;
    }

    protected function addVendors()
    {
        $vendors = [];

        $moduleList = $this->moduleList->getAll();

        foreach ($moduleList as $moduleName => $info) {
            // First index is (probably always) vendor
            $moduleNameData = explode('_', $moduleName);

            if (isset($moduleNameData[0])) {
                $vendors[] = $moduleNameData[0];
            }
        }

        $this->infos['Vendors'] = implode(', ', array_unique($vendors));
        $this->infos['Module Count'] = count($moduleList);
    }
}
