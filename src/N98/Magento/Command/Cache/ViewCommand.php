<?php

namespace N98\Magento\Command\Cache;

use Magento\Framework\App\CacheInterface;
use Magento\PageCache\Model\Cache\Type as FullPageCache;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Exception\RuntimeException;

class ViewCommand extends AbstractMagentoCommand
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
            ->setName('cache:view')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'Cache-ID'
            )
            ->addOption(
                'fpc',
                null,
                InputOption::VALUE_NONE,
                'Use full page cache instead of core cache'
            )
            ->addOption(
                'unserialize',
                null,
                InputOption::VALUE_NONE,
                'Unserialize output'
            )
            ->setDescription('Prints a cache entry');
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

        /** CacheInterface|FullPageCache $cacheInstance */
        if ($input->hasOption('fpc') && $input->getOption('fpc')) {
            $cacheInstance = $this->fpc;
        } else {
            $cacheInstance = $this->cache;
        }

        $cacheId = $input->getArgument('id');
        $cacheData = $cacheInstance->load($cacheId);
        if ($cacheData === false) {
            $output->writeln('Cache id <info>' . $cacheId . '</info> does not exist (anymore)');
            return;
        }

        if ($input->getOption('unserialize')) {
            if (version_compare(phpversion(), '7.0', '>=')) {
                $cacheData = unserialize($cacheData, false);
            } else {
                $cacheData = unserialize($cacheData);
            }
            if ($cacheData !== false) {
                $cacheData = json_encode($cacheData, JSON_PRETTY_PRINT);
            }
        }

        $output->writeln($cacheData);
    }
}
