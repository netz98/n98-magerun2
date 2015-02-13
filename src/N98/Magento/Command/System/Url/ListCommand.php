<?php

namespace N98\Magento\Command\System\Url;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends AbstractMagentoCommand
{

    /**
     * @var \Magento\Framework\Store\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sitemap\Model\Resource\Catalog\Category
     */
    protected $sitemapCategoryCollection;

    /**
     * @var \Magento\Sitemap\Model\Resource\Catalog\Product
     */
    protected $sitemapProductCollection;

    /**
     * @var \Magento\Sitemap\Model\Resource\Cms\Page
     */
    protected $sitemapPageCollection;

    protected function configure()
    {
        $this
            ->setName('sys:url:list')
            ->addOption('add-categories', null, InputOption::VALUE_NONE, 'Adds categories')
            ->addOption('add-products', null, InputOption::VALUE_NONE, 'Adds products')
            ->addOption('add-cmspages', null, InputOption::VALUE_NONE, 'Adds cms pages')
            ->addOption('add-all', null, InputOption::VALUE_NONE, 'Adds categories, products and cms pages')
            ->addArgument('stores', InputArgument::OPTIONAL, 'Stores (comma-separated list of store ids)')
            ->addArgument('linetemplate', InputArgument::OPTIONAL, 'Line template', '{url}')
            ->setDescription('Get all urls.')
        ;

        $help = <<<HELP
Examples:

- Create a list of product urls only:

   $ n98-magerun.phar sys:url:list --add-products 4

- Create a list of all products, categories and cms pages of store 4 and 5 separating host and path (e.g. to feed a jmeter csv sampler):

   $ n98-magerun.phar sys:url:list --add-all 4,5 '{host},{path}' > urls.csv

- The "linetemplate" can contain all parts "parse_url" return wrapped in '{}'. '{url}' always maps the complete url and is set by default
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Sitemap\Model\Resource\Catalog\Category $sitemapCategoryCollection
     * @param \Magento\Sitemap\Model\Resource\Catalog\Product $sitmapProductCollection
     * @param \Magento\Sitemap\Model\Resource\Cms\Page $sitemapPageCollection
     */
    public function inject(
        \Magento\Framework\Store\StoreManagerInterface $storeManager,
        \Magento\Sitemap\Model\Resource\Catalog\Category $sitemapCategoryCollection,
        \Magento\Sitemap\Model\Resource\Catalog\Product $sitemapProductCollection,
        \Magento\Sitemap\Model\Resource\Cms\Page $sitemapPageCollection
    )
    {
        $this->storeManager = $storeManager;
        $this->sitemapCategoryCollection = $sitemapCategoryCollection;
        $this->sitemapProductCollection= $sitemapProductCollection;
        $this->sitemapPageCollection = $sitemapPageCollection;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if ($input->getOption('add-all')) {
            $input->setOption('add-categories', true);
            $input->setOption('add-products', true);
            $input->setOption('add-cmspages', true);
        }

        $this->detectMagento($output, true);
        if ($this->initMagento()) {

            $stores = explode(',', $input->getArgument('stores'));

            $urls = array();

            foreach ($stores as $storeId) {

                try {
                    $currentStore = $this->storeManager->getStore($storeId);
                } catch(\Exception $e) {
                    throw new \RuntimeException("Store with id {$storeId} doesn´t exist");
                }

                // base url
                $urls[] = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

                $linkBaseUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);

                if ($input->getOption('add-categories')) {
                    $collection = $this->sitemapCategoryCollection->getCollection($storeId);
                    if ($collection) {
                        foreach ($collection as $item) { /* @var $item \Magento\Framework\Object */
                            $urls[] = $linkBaseUrl . $item->getUrl();
                        }
                        unset($collection);
                    }
                }

                if ($input->getOption('add-products')) {
                    $collection = $this->sitemapProductCollection->getCollection($storeId);
                    if ($collection) {
                        foreach ($collection as $item) { /* @var $item \Magento\Framework\Object */
                            $urls[] = $linkBaseUrl . $item->getUrl();
                        }
                        unset($collection);
                    }
                }

                if ($input->getOption('add-cmspages')) {
                    $collection = $this->sitemapPageCollection->getCollection($storeId);
                    if ($collection) {
                        foreach ($collection as $item) { /* @var $item \Magento\Framework\Object */
                            $urls[] = $linkBaseUrl . $item->getUrl();
                        }
                        unset($collection);
                    }
                }

            } // foreach ($stores as $storeId)

            if (count($urls) > 0) {
                foreach ($urls as $url) {

                    // pre-process
                    $line = $input->getArgument('linetemplate');
                    $line = str_replace('{url}', $url, $line);

                    $parts = parse_url($url);
                    foreach ($parts as $key => $value) {
                        $line = str_replace('{'.$key.'}', $value, $line);
                    }

                    // ... and output
                    $output->writeln($line);
                }
            }
        }
    }
}