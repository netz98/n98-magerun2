<?php

namespace N98\Magento\Command\System\Url\Generator;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Symfony\Component\Console\Output\OutputInterface;

class ProductGenerator extends AbstractGenerator
{
    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * ProductGenerator constructor.
     *
     * @param UrlPersistInterface $urlPersist
     * @param StoreManagerInterface $storeManager
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        StoreManagerInterface $storeManager,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        ProductCollectionFactory $productCollectionFactory
    ) {
        parent::__construct($urlPersist, $storeManager);
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Regenerate URL rewrites for specific products
     *
     * @param array $entityIds
     * @param int $storeId
     * @param OutputInterface $output
     * @return int
     */
    public function regenerate(array $entityIds, int $storeId, OutputInterface $output): int
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);
        $collection->addAttributeToSelect('name');

        if (!empty($entityIds)) {
            $collection->addIdFilter($entityIds);
        }

        $collection->load();
        $progressBar = $this->createProgressBar($output, $collection->count());
        $progressBar->start();

        $counter = 0;
        foreach ($collection as $product) {
            $this->writeVerbose(
                $output,
                sprintf(
                    '<info>Regenerating product <comment>%s</comment> (%s)</info>',
                    $product->getSku(),
                    $product->getId()
                )
            );
            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $product->getId(),
                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::REDIRECT_TYPE => 0,
                UrlRewrite::STORE_ID => $storeId,
            ]);
            $urls = $this->productUrlRewriteGenerator->generate($product);
            $urls = array_filter($urls, function ($url) {
                return !empty($url->getRequestPath());
            });
            $this->urlPersist->replace($urls);
            $counter += count($urls);
            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln('');

        return $counter;
    }

    /**
     * Regenerate URL rewrites for all products with pagination
     *
     * @param int $storeId
     * @param OutputInterface $output
     * @return int
     */
    public function regenerateAll(int $storeId, OutputInterface $output): int
    {
        $counter = 0;
        $currentPage = 1;

        // First, get the total count of products
        $countCollection = $this->productCollectionFactory->create();
        $countCollection->setStoreId($storeId);
        $countCollection->addStoreFilter($storeId);
        $totalSize = $countCollection->getSize();

        // Create a progress bar for all products
        $progressBar = $this->createProgressBar($output, $totalSize);
        $progressBar->start();

        do {
            $collection = $this->productCollectionFactory->create();
            $collection->setStoreId($storeId);
            $collection->addStoreFilter($storeId);
            $collection->addAttributeToSelect('name');
            $collection->setPageSize($this->getBatchSize());
            $collection->setCurPage($currentPage);

            $collection->load();

            $collectionSize = $collection->getSize();
            $collectionCount = $collection->count();

            if ($collectionCount > 0) {
                $this->writeVerbose($output, sprintf(
                    '<info>Regenerating products batch <comment>%d/%d</comment> (store %d)</info>',
                    $currentPage,
                    ceil($collectionSize / $this->getBatchSize()),
                    $storeId
                ));

                foreach ($collection as $product) {
                    $this->writeVerbose($output, sprintf('<info>Regenerating product <comment>%s</comment> (%s)</info>', $product->getSku(), $product->getId()));
                    $this->urlPersist->deleteByData([
                        UrlRewrite::ENTITY_ID => $product->getId(),
                        UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                        UrlRewrite::REDIRECT_TYPE => 0,
                        UrlRewrite::STORE_ID => $storeId,
                    ]);
                    $urls = $this->productUrlRewriteGenerator->generate($product);
                    $urls = array_filter($urls, function ($url) {
                        return !empty($url->getRequestPath());
                    });
                    $this->urlPersist->replace($urls);
                    $counter += count($urls);
                    $progressBar->advance();
                }
            }

            $currentPage++;
            $collection->clear();
        } while ($collectionCount > 0 && $currentPage <= ceil($totalSize / $this->getBatchSize()));

        $progressBar->finish();
        $output->writeln('');

        return $counter;
    }
}
