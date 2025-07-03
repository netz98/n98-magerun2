<?php

namespace N98\Magento\Command\System\Url;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsPageCollectionFactory;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Magento\Command\System\Url\Generator\CategoryGenerator;
use N98\Magento\Command\System\Url\Generator\CmsPageGenerator;
use N98\Magento\Command\System\Url\Generator\ProductGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegenerateCommand extends AbstractMagentoCommand
{
    /**
     * @var ProductGenerator
     */
    protected $productGenerator;

    /**
     * @var CategoryGenerator
     */
    protected $categoryGenerator;

    /**
     * @var CmsPageGenerator
     */
    protected $cmsPageGenerator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected function configure()
    {
        $this
            ->setName('sys:url:regenerate')
            ->setDescription('Regenerate product, category and cms page url rewrites')
            ->addOption('products', null, InputOption::VALUE_OPTIONAL, 'Comma separated product ids', '')
            ->addOption('categories', null, InputOption::VALUE_OPTIONAL, 'Comma separated category ids', '')
            ->addOption('cms-pages', null, InputOption::VALUE_OPTIONAL, 'Comma separated cms page ids', '')
            ->addOption('store', null, InputOption::VALUE_OPTIONAL, 'Store id', 0)
            ->addOption('all-products', null, InputOption::VALUE_NONE, 'Regenerate all products')
            ->addOption('all-categories', null, InputOption::VALUE_NONE, 'Regenerate all categories')
            ->addOption('all-cms-pages', null, InputOption::VALUE_NONE, 'Regenerate all cms pages')
            ->addOption('batch-size', null, InputOption::VALUE_OPTIONAL, 'Batch size for pagination', 100);
    }

    public function inject(
        UrlPersistInterface $urlPersist,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        CmsPageCollectionFactory $cmsPageCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->productGenerator = new ProductGenerator(
            $urlPersist,
            $storeManager,
            $productUrlRewriteGenerator,
            $productCollectionFactory
        );

        $this->categoryGenerator = new CategoryGenerator(
            $urlPersist,
            $storeManager,
            $categoryUrlRewriteGenerator,
            $categoryCollectionFactory
        );

        $this->cmsPageGenerator = new CmsPageGenerator(
            $urlPersist,
            $storeManager,
            $cmsPageUrlRewriteGenerator,
            $cmsPageCollectionFactory
        );

        $this->storeManager = $storeManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
        }

        $storeId = (int) $input->getOption('store');
        $stores = $storeId ? [$storeId] : array_keys($this->storeManager->getStores());

        $productIds = array_filter(array_map('intval', explode(',', (string) $input->getOption('products'))));
        $categoryIds = array_filter(array_map('intval', explode(',', (string) $input->getOption('categories'))));
        $cmsPageIds = array_filter(array_map('intval', explode(',', (string) $input->getOption('cms-pages'))));
        $batchSize = (int) $input->getOption('batch-size');

        // Set batch size for pagination
        $this->productGenerator->setBatchSize($batchSize);
        $this->categoryGenerator->setBatchSize($batchSize);
        $this->cmsPageGenerator->setBatchSize($batchSize);

        $count = 0;
        foreach ($stores as $id) {
            // Regenerate specific categories if IDs are provided
            if ($input->getOption('categories') !== '') {
                $count += $this->categoryGenerator->regenerate($categoryIds, $id, $output);
            }

            // Regenerate all categories if --all-categories option is set
            if ($input->getOption('all-categories')) {
                if ($output->isVerbose()) {
                    $output->writeln('<info>Regenerating all categories...</info>');
                }
                $count += $this->categoryGenerator->regenerateAll($id, $output);
            }

            // Regenerate specific products if IDs are provided
            if ($input->getOption('products') !== '') {
                $count += $this->productGenerator->regenerate($productIds, $id, $output);
            }

            // Regenerate all products if --all-products option is set
            if ($input->getOption('all-products')) {
                if ($output->isVerbose()) {
                    $output->writeln('<info>Regenerating all products...</info>');
                }
                $count += $this->productGenerator->regenerateAll($id, $output);
            }

            // Regenerate specific CMS pages if IDs are provided
            if ($input->getOption('cms-pages') !== '') {
                $count += $this->cmsPageGenerator->regenerate($cmsPageIds, $id, $output);
            }

            // Regenerate all CMS pages if --all-cms-pages option is set
            if ($input->getOption('all-cms-pages')) {
                if ($output->isVerbose()) {
                    $output->writeln('<info>Regenerating all CMS pages...</info>');
                }
                $count += $this->cmsPageGenerator->regenerateAll($id, $output);
            }
        }

        $output->writeln(sprintf('<info>Generated %d url rewrites</info>', $count));

        return Command::SUCCESS;
    }
}
