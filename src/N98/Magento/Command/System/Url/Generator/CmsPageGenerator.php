<?php

namespace N98\Magento\Command\System\Url\Generator;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsPageCollectionFactory;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Symfony\Component\Console\Output\OutputInterface;

class CmsPageGenerator extends AbstractGenerator
{
    /**
     * @var CmsPageUrlRewriteGenerator
     */
    protected $cmsPageUrlRewriteGenerator;

    /**
     * @var CmsPageCollectionFactory
     */
    protected $cmsPageCollectionFactory;

    /**
     * CmsPageGenerator constructor.
     *
     * @param UrlPersistInterface $urlPersist
     * @param StoreManagerInterface $storeManager
     * @param CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator
     * @param CmsPageCollectionFactory $cmsPageCollectionFactory
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        StoreManagerInterface $storeManager,
        CmsPageUrlRewriteGenerator $cmsPageUrlRewriteGenerator,
        CmsPageCollectionFactory $cmsPageCollectionFactory
    ) {
        parent::__construct($urlPersist, $storeManager);
        $this->cmsPageUrlRewriteGenerator = $cmsPageUrlRewriteGenerator;
        $this->cmsPageCollectionFactory = $cmsPageCollectionFactory;
    }

    /**
     * Regenerate URL rewrites for specific CMS pages
     *
     * @param array $entityIds
     * @param int $storeId
     * @param OutputInterface $output
     * @return int
     */
    public function regenerate(array $entityIds, int $storeId, OutputInterface $output): int
    {
        $collection = $this->cmsPageCollectionFactory->create();

        if (!empty($entityIds)) {
            $collection->addFieldToFilter('page_id', ['in' => $entityIds]);
        }

        // Filter by store if not admin store (0)
        if ($storeId > 0) {
            $collection->addStoreFilter($storeId);
        }

        $collection->load();
        $progressBar = $this->createProgressBar($output, $collection->count());
        $progressBar->start();

        $counter = 0;
        foreach ($collection as $cmsPage) {
            $this->writeVerbose(
                $output,
                sprintf(
                    '<info>Regenerating CMS page <comment>%s</comment> (%s)</info>',
                    $cmsPage->getIdentifier(),
                    $cmsPage->getId()
                )
            );
            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $cmsPage->getId(),
                UrlRewrite::ENTITY_TYPE => CmsPageUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::REDIRECT_TYPE => 0,
                UrlRewrite::STORE_ID => $storeId,
            ]);
            $urls = $this->cmsPageUrlRewriteGenerator->generate($cmsPage);
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
     * Regenerate URL rewrites for all CMS pages with pagination
     *
     * @param int $storeId
     * @param OutputInterface $output
     * @return int
     */
    public function regenerateAll(int $storeId, OutputInterface $output): int
    {
        $counter = 0;
        $currentPage = 1;

        // First, get the total count of CMS pages
        $countCollection = $this->cmsPageCollectionFactory->create();

        // Filter by store if not admin store (0)
        if ($storeId > 0) {
            $countCollection->addStoreFilter($storeId);
        }

        $totalSize = $countCollection->getSize();

        // Create a progress bar for all CMS pages
        $progressBar = $this->createProgressBar($output, $totalSize);
        $progressBar->start();

        do {
            $collection = $this->cmsPageCollectionFactory->create();

            // Filter by store if not admin store (0)
            if ($storeId > 0) {
                $collection->addStoreFilter($storeId);
            }

            $collection->setPageSize($this->getBatchSize());
            $collection->setCurPage($currentPage);

            $collection->load();

            $collectionSize = $collection->getSize();
            $collectionCount = $collection->count();

            if ($collectionCount > 0) {
                $this->writeVerbose($output, sprintf(
                    '<info>Regenerating CMS pages batch <comment>%d/%d</comment> (store %d)</info>',
                    $currentPage,
                    ceil($collectionSize / $this->getBatchSize()),
                    $storeId
                ));

                foreach ($collection as $cmsPage) {
                    $this->writeVerbose(
                        $output,
                        sprintf(
                            '<info>Regenerating CMS page <comment>%s</comment> (%s)</info>',
                            $cmsPage->getIdentifier(),
                            $cmsPage->getId()
                        )
                    );
                    $this->urlPersist->deleteByData([
                        UrlRewrite::ENTITY_ID => $cmsPage->getId(),
                        UrlRewrite::ENTITY_TYPE => CmsPageUrlRewriteGenerator::ENTITY_TYPE,
                        UrlRewrite::REDIRECT_TYPE => 0,
                        UrlRewrite::STORE_ID => $storeId,
                    ]);
                    $urls = $this->cmsPageUrlRewriteGenerator->generate($cmsPage);
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
