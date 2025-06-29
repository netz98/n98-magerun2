<?php

namespace N98\Magento\Command\System\Url;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegenerateCommand extends AbstractMagentoCommand
{
    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $productGenerator;

    /**
     * @var CategoryUrlRewriteGenerator
     */
    protected $categoryGenerator;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected function configure()
    {
        $this
            ->setName('sys:url:regenerate')
            ->setDescription('Regenerate product and category url rewrites')
            ->addOption('products', null, InputOption::VALUE_OPTIONAL, 'Comma separated product ids', '')
            ->addOption('categories', null, InputOption::VALUE_OPTIONAL, 'Comma separated category ids', '')
            ->addOption('store', null, InputOption::VALUE_OPTIONAL, 'Store id', 0);
    }

    public function inject(
        UrlPersistInterface $urlPersist,
        ProductUrlRewriteGenerator $productGenerator,
        CategoryUrlRewriteGenerator $categoryGenerator,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->urlPersist = $urlPersist;
        $this->productGenerator = $productGenerator;
        $this->categoryGenerator = $categoryGenerator;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
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

        $count = 0;
        foreach ($stores as $id) {
            if ($input->getOption('categories') !== '') {
                $count += $this->regenerateCategories($categoryIds, $id, $output);
            }
            if ($input->getOption('products') !== '') {
                $count += $this->regenerateProducts($productIds, $id, $output);
            }
        }

        $output->writeln(sprintf('<info>Generated %d url rewrites</info>', $count));

        return Command::SUCCESS;
    }

    private function regenerateCategories(array $categoryIds, $storeId, OutputInterface $output)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(['name', 'url_path', 'url_key', 'path']);
        if ($categoryIds) {
            $collection->addAttributeToFilter('entity_id', ['in' => $categoryIds]);
        }

        $counter = 0;
        foreach ($collection as $category) {
            $output->writeln(sprintf('Regenerating category %s (%s)', $category->getName(), $category->getId()));
            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $category->getId(),
                UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::REDIRECT_TYPE => 0,
                UrlRewrite::STORE_ID => $storeId,
            ]);
            $urls = $this->categoryGenerator->generate($category);
            $urls = array_filter($urls, function ($url) {
                return !empty($url->getRequestPath());
            });
            $this->urlPersist->replace($urls);
            $counter += count($urls);
        }

        return $counter;
    }

    private function regenerateProducts(array $productIds, $storeId, OutputInterface $output)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToSelect('name');
        if ($productIds) {
            $collection->addIdFilter($productIds);
        }

        $counter = 0;
        foreach ($collection as $product) {
            $output->writeln(sprintf('Regenerating product %s (%s)', $product->getSku(), $product->getId()));
            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $product->getId(),
                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::REDIRECT_TYPE => 0,
                UrlRewrite::STORE_ID => $storeId,
            ]);
            $urls = $this->productGenerator->generate($product);
            $urls = array_filter($urls, function ($url) {
                return !empty($url->getRequestPath());
            });
            $this->urlPersist->replace($urls);
            $counter += count($urls);
        }

        return $counter;
    }
}
