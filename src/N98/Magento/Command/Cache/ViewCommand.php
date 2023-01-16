<?php

namespace N98\Magento\Command\Cache;

use Magento\Framework\App\CacheInterface;
use Magento\PageCache\Model\Cache\Type as FullPageCache;
use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
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
     * @return int
     * @throws RuntimeException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return Command::FAILURE;
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
            return Command::FAILURE;
        }

        if ($input->getOption('unserialize')) {
            $cacheData = $this->decorateSerialized($cacheData);
        }

        $output->writeln($cacheData);

        return Command::SUCCESS;
    }

    /**
     * @param string $serialized
     * @return string
     */
    private function decorateSerialized($serialized)
    {
        if (version_compare(phpversion(), '7.0', '>=')) {
            $unserialized = \unserialize($serialized, false);
        } else {
            $unserialized = \unserialize($serialized);
        }

        if ($unserialized === false) {
            $buffer = $serialized;
        } else {
            $buffer = json_encode($unserialized, JSON_PRETTY_PRINT);
        }

        return $buffer;
    }
}
