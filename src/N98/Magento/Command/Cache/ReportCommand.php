<?php

namespace N98\Magento\Command\Cache;

use Magento\Framework\App\CacheInterface;
use Magento\PageCache\Model\Cache\Type as FullPageCache;
use N98\Magento\Command\AbstractMagentoCommand;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Exception\RuntimeException;

class ReportCommand extends AbstractMagentoCommand
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var FullPageCache
     */
    private $fpc;

    /**
     * @param CacheInterface $cache
     * @param FullPageCache $fpc
     */
    public function inject(
        CacheInterface $cache,
        FullPageCache $fpc
    ) {
        $this->cache = $cache;
        $this->fpc = $fpc;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cache:report')
            ->addOption(
                'fpc',
                null,
                InputOption::VALUE_NONE,
                'Use full page cache instead of core cache'
            )
            ->addOption(
                'tags',
                't',
                InputOption::VALUE_NONE,
                'Output tags'
            )
            ->addOption(
                'mtime',
                'm',
                InputOption::VALUE_NONE,
                'Output last modification time'
            )
            ->addOption(
                'filter-id',
                '',
                InputOption::VALUE_OPTIONAL,
                'Filter output by ID (substring)'
            )
            ->addOption(
                'filter-tag',
                '',
                InputOption::VALUE_OPTIONAL,
                'Filter output by TAG (separate multiple tags by comma)'
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(', ', RendererFactory::getFormats()) . ']'
            )
            ->setDescription('View inside the cache');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        /** @var \Zend_Cache_Core $lowLevelFrontend */
        if ($input->hasOption('fpc') && $input->getOption('fpc')) {
            $lowLevelFrontend = $this->fpc->getLowLevelFrontend();
        } else {
            $lowLevelFrontend = $this->cache->getFrontend()->getLowLevelFrontend();
        }

        $table = [];
        $cacheIds = $lowLevelFrontend->getIds();
        $filterId = $input->getOption('filter-id');
        $filterTag = $input->getOption('filter-tag');
        $mTime = $input->getOption('mtime');
        $tags = $input->getOption('tags');
        foreach ($cacheIds as $cacheId) {
            if ($filterId !== null &&
                !stristr($cacheId, $filterId)) {
                continue;
            }

            $metadata = $lowLevelFrontend->getMetadatas($cacheId);
            if ($filterTag !== null &&
                !$this->isTagFiltered($metadata, $input)) {
                continue;
            }

            $row = [
                $cacheId,
                date('Y-m-d H:i:s', $metadata['expire']),
            ];

            if ($mTime) {
                $row[] = date('Y-m-d H:i:s', $metadata['mtime']);
            }

            if ($tags) {
                $row[] = implode(', ', $metadata['tags']);
            }

            $table[] = $row;
        }

        $headers = ['ID', 'EXPIRE'];
        if ($mTime) {
            $headers[] = 'MTIME';
        }
        if ($tags) {
            $headers[] = 'TAGS';
        }

        $this
            ->getHelper('table')
            ->setHeaders($headers)
            ->renderByFormat($output, $table, $input->getOption('format'));
    }

    /**
     * @param array $metadata
     * @param InputInterface $input
     * @return bool
     */
    private function isTagFiltered($metadata, $input)
    {
        return (bool) count(
            array_intersect(
                $metadata['tags'],
                explode(',', $input->getOption('filter-tag'))
            )
        );
    }
}
