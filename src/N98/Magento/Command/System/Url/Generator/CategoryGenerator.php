<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Magento\Command\System\Url\Generator;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Symfony\Component\Console\Output\OutputInterface;

class CategoryGenerator extends AbstractGenerator
{
    /**
     * @var CategoryUrlRewriteGenerator
     */
    protected $categoryUrlRewriteGenerator;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * CategoryGenerator constructor.
     *
     * @param UrlPersistInterface $urlPersist
     * @param StoreManagerInterface $storeManager
     * @param CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        StoreManagerInterface $storeManager,
        CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        parent::__construct($urlPersist, $storeManager);
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Regenerate URL rewrites for specific categories
     *
     * @param array $entityIds
     * @param int $storeId
     * @param OutputInterface $output
     * @return int
     */
    public function regenerate(array $entityIds, int $storeId, OutputInterface $output): int
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(['name', 'url_path', 'url_key', 'path']);

        if (!empty($entityIds)) {
            $collection->addAttributeToFilter('entity_id', ['in' => $entityIds]);
        }

        $collection->load();
        $progressBar = $this->createProgressBar($output, $collection->count());
        $progressBar->start();

        $counter = 0;
        foreach ($collection as $category) {
            $this->writeVerbose(
                $output,
                sprintf(
                    '<info>Regenerating category <comment>%s</comment> (%s)</info>',
                    $category->getName(),
                    $category->getId()
                )
            );
            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $category->getId(),
                UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::REDIRECT_TYPE => 0,
                UrlRewrite::STORE_ID => $storeId,
            ]);
            $urls = $this->categoryUrlRewriteGenerator->generate($category);
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
     * Regenerate URL rewrites for all categories with pagination
     *
     * @param int $storeId
     * @param OutputInterface $output
     * @return int
     */
    public function regenerateAll(int $storeId, OutputInterface $output): int
    {
        $counter = 0;
        $currentPage = 1;

        // First, get the total count of categories
        $countCollection = $this->categoryCollectionFactory->create();
        $countCollection->setStoreId($storeId);
        $totalSize = $countCollection->getSize();

        // Create a progress bar for all categories
        $progressBar = $this->createProgressBar($output, $totalSize);
        $progressBar->start();

        do {
            $collection = $this->categoryCollectionFactory->create();
            $collection->setStoreId($storeId);
            $collection->addAttributeToSelect(['name', 'url_path', 'url_key', 'path']);
            $collection->setPageSize($this->getBatchSize());
            $collection->setCurPage($currentPage);

            $collection->load();

            $collectionSize = $collection->getSize();
            $collectionCount = $collection->count();

            if ($collectionCount > 0) {
                $this->writeVerbose($output, sprintf(
                    '<info>Regenerating categories batch <comment>%d/%d</comment> (store %d)</info>',
                    $currentPage,
                    ceil($collectionSize / $this->getBatchSize()),
                    $storeId
                ));

                foreach ($collection as $category) {
                    $this->writeVerbose(
                        $output,
                        sprintf(
                            '<info>Regenerating category <comment>%s</comment> (%s)</info>',
                            $category->getName(),
                            $category->getId()
                        )
                    );
                    $this->urlPersist->deleteByData([
                        UrlRewrite::ENTITY_ID => $category->getId(),
                        UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
                        UrlRewrite::REDIRECT_TYPE => 0,
                        UrlRewrite::STORE_ID => $storeId,
                    ]);
                    $urls = $this->categoryUrlRewriteGenerator->generate($category);
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
